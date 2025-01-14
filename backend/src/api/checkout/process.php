<?php

require '../../../vendor/autoload.php';
require '../../middleware/preflight.php';
require '../../middleware/auth.php';
require '../notifications/create_notification.php';

// Verify customer role
if ($_TOKEN['role'] !== 'customer') {
    echo json_encode(['error' => 'Unauthorized']);
    http_response_code(403);
    exit;
}

$customer_id = $_TOKEN['sub'];

// Get request body
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['cart_items']) || !isset($data['shipping_info'])) {
    echo json_encode(['error' => 'Missing required fields']);
    http_response_code(400);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Process each cart item
    foreach ($data['cart_items'] as $item) {
        // Check stock availability and update
        $stock_sql = "
            SELECT w.quantity, w.seller_id, t.name as product_name
            FROM warehouse w
            INNER JOIN tshirts t ON w.tshirt_id = t.tshirt_id
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
            throw new Exception("Insufficient stock for: " . $stock_data['product_name']);
        }
        
        // Update stock
        $new_quantity = $stock_data['quantity'] - $item['quantity'];
        $update_sql = "
            UPDATE warehouse
            SET quantity = ?
            WHERE item_id = ?
        ";
        
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param('ii', $new_quantity, $item['item_id']);
        $update_stmt->execute();
        
        // Check if stock is low and notify seller
        if (should_notify_low_stock($new_quantity)) {
            create_stock_alert_notification(
                $stock_data['seller_id'],
                $stock_data['product_name'],
                $new_quantity
            );
        }
    }
    
    // Create order
    $order_sql = "
        INSERT INTO orders (
            customer_id,
            seller_id,
            status_id,
            subtotal,
            shipping_cost,
            tax,
            total,
            shipping_address,
            created_at
        ) VALUES (?, ?, 1, ?, ?, ?, ?, ?, NOW())
    ";
    
    $order_stmt = $conn->prepare($order_sql);
    $order_stmt->bind_param(
        'iiddddss',
        $customer_id,
        $stock_data['seller_id'], // Using last seller_id from items
        $data['subtotal'],
        $data['shipping_cost'],
        $data['tax'],
        $data['total'],
        json_encode($data['shipping_info'])
    );
    $order_stmt->execute();
    $order_id = $conn->insert_id;
    
    // Create order items
    $items_sql = "
        INSERT INTO order_items (
            order_id,
            item_id,
            quantity,
            paid_price
        ) VALUES (?, ?, ?, ?)
    ";
    
    $items_stmt = $conn->prepare($items_sql);
    foreach ($data['cart_items'] as $item) {
        $items_stmt->bind_param(
            'iiid',
            $order_id,
            $item['item_id'],
            $item['quantity'],
            $item['price']
        );
        $items_stmt->execute();
    }
    
    // Notify seller about new order
    create_new_order_notification(
        $stock_data['seller_id'],
        $order_id,
        $data['total']
    );
    
    // Commit transaction
    $conn->commit();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'order_id' => $order_id
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
