<?php

require '../../../vendor/autoload.php';
require '../../middleware/preflight.php';
require '../../middleware/auth.php';
require '../notifications/create_notification.php';



$customer_id = $_TOKEN['sub'];

// Get request body
$data = json_decode(file_get_contents('php://input'), true);

// Start transaction
$conn->begin_transaction();

try {
    // Process each cart item
    $cart_items = getCartItems($conn, $customer_id);
    foreach ($cart_items as $item) {
        // Check stock availability and update
        $stock_sql = "
            SELECT w.availability as quantity
            FROM warehouse w
            WHERE w.item_id = ? FOR UPDATE
        ";
        
        $stock_stmt = $conn->prepare($stock_sql);
        $stock_stmt->bind_param('i', $item['item_id']);
        $stock_stmt->execute();
        $stock_result = $stock_stmt->get_result();
        
        if ($stock_result->num_rows === 0) {
            throw new Exception("Item not found: " . $item['item_id']);
        }
        
        $stock_data = $stock_result->fetch_assoc();
        
        if ($stock_data['quantity'] < $item['quantity']) {
            throw new Exception("Insufficient stock for: " . $item['team'] . " " . $item['edition']);
        }
        
        // Update stock using the dedicated function
        updateStock($conn, $item['item_id'], $item['quantity']);
        
        // Check if stock is low and notify admins
        $new_quantity = $stock_data['quantity'] - $item['quantity'];
        if (should_notify_low_stock($new_quantity)) {
            $product_name = $item['team'] . " " . $item['edition'];
            create_stock_alert_notification(
                $product_name,
                $new_quantity
            );
        }
    }
    
    // Calculate order totals
    $subtotal = 0;
    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    
    $shipping_cost = 10.00; // Fixed shipping cost
    $tax_rate = 0.22; // 22% VAT
    $tax = $subtotal * $tax_rate;
    $total = $subtotal + $shipping_cost + $tax;

    // Create order with tracking info
    $tracking_number = generateTrackingNumber();
    $delivery_date = date('Y-m-d H:i:s', strtotime('+5 days'));
    $shipping_agent = 'DHL Express'; // Default shipping agent
    
    $order_sql = "
        INSERT INTO orders (
            customer_id,
            status_id,
            subtotal,
            shipping_cost,
            tax,
            total,
            tracking_number,
            delivery,
            shipping_agent
        ) VALUES (?, 1, ?, ?, ?, ?, ?, ?, ?)
    ";
    
    $order_stmt = $conn->prepare($order_sql);
    $order_stmt->bind_param(
        'iddddsss',  // i for customer_id (status_id is in VALUES), d for amounts, s for strings
        $customer_id,
        $subtotal,
        $shipping_cost,
        $tax,
        $total,
        $tracking_number,
        $delivery_date,
        $shipping_agent
    );
    $order_stmt->execute();
    $order_id = $conn->insert_id;
    
    // Create order items using the cart items we already fetched
    foreach ($cart_items as $item) {
        createOrderItem($conn, $order_id, $item);
    }
    
    // Notify admins about new order
    create_new_order_notification(
        $order_id,
        $total
    );
    
    // Commit transaction
    $conn->commit();
    
    // Return success response with order details
    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'order' => [
            'subtotal' => $subtotal,
            'shipping_cost' => $shipping_cost,
            'tax' => $tax,
            'total' => $total,
            'tracking_number' => $tracking_number,
            'delivery_date' => $delivery_date,
            'shipping_agent' => $shipping_agent
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    echo json_encode([
        'error' => $e->getMessage()
    ]);
    http_response_code(400);
}

// Close connection
$conn->close();
function getCartItems($conn, $customer_id) {
    $query = "SELECT c.quantity,tm.name as team,e.`name` as edition, t.image_url, t.price, s.`name` as size, t.tshirt_id as item_id, c.item_id
              FROM carts c
              Inner JOIN warehouse w ON c.item_id = w.item_id
              Inner JOIN tshirts t ON w.tshirt_id = t.tshirt_id
              inner join editions e on e.edition_id = t.edition_id
              inner join teams tm on tm.team_id = t.team_id
              inner join sizes s on w.size_id = s.size_id
              WHERE c.customer_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
function createOrder($conn, $customer_id, $subtotal, $shipping_cost, $tax, $total) {
    $status_id = 1; // Assuming 1 is 'pending' status
    $query = "INSERT INTO orders (customer_id, status_id, subtotal, shipping_cost, tax, total) 
              VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iidddd", $customer_id, $status_id, $subtotal, $shipping_cost, $tax, $total);
    $stmt->execute();
    
    return $stmt->insert_id;
}

function createOrderItem($conn, $order_id, $item) {
    $query = "INSERT INTO order_items (order_id, item_id, quantity, paid_price) 
              VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiid", $order_id, $item['item_id'], $item['quantity'], $item['price']);
    $stmt->execute();
}

function updateStock($conn, $item_id, $quantity) {
    $query = "UPDATE warehouse
              SET availability = availability - ?
              WHERE item_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $quantity, $item_id);
    $stmt->execute();
}

function generateTrackingNumber() {
    // Generate a unique tracking number with year prefix and random alphanumeric string
    $year = date('Y');
    $random = bin2hex(random_bytes(8)); // 16 characters of random hex
    return $year . '-' . strtoupper($random);
}
