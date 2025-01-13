<?php
require '../../middleware/preflight.php';
require '../../../vendor/autoload.php';
require '../../middleware/auth.php';

// Get request body
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['item_id']) || !isset($data['quantity'])) {
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

$item_id = intval($data['item_id']);
$quantity = intval($data['quantity']);

// Check if item exists and is available
$check_sql = "SELECT availability FROM warehouse WHERE item_id = ?";
$check_stmt = $conn->prepare($check_sql);

if (!$check_stmt) {
    echo json_encode(['error' => 'Failed to prepare availability check query']);
    exit;
}

$check_stmt->bind_param('i', $item_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Item not found']);
    $check_stmt->close();
    $conn->close();
    exit;
}

$availability = $result->fetch_assoc()['availability'];
$check_stmt->close();

if ($quantity > $availability) {
    echo json_encode(['error' => 'Requested quantity exceeds availability']);
    $conn->close();
    exit;
}

// Check if item already exists in cart
$existing_sql = "SELECT quantity FROM carts WHERE customer_id = ? AND item_id = ?";
$existing_stmt = $conn->prepare($existing_sql);

if (!$existing_stmt) {
    echo json_encode(['error' => 'Failed to prepare cart check query']);
    exit;
}

$existing_stmt->bind_param('ii', $_TOKEN['sub'], $item_id);
$existing_stmt->execute();
$existing_result = $existing_stmt->get_result();

if ($existing_result->num_rows > 0) {
    // Update existing cart item
    $new_quantity = $existing_result->fetch_assoc()['quantity'] + $quantity;
    $existing_stmt->close();
    
    if ($new_quantity > $availability) {
        echo json_encode(['error' => 'Total quantity would exceed availability']);
        $conn->close();
        exit;
    }

    $update_sql = "UPDATE carts SET quantity = ? WHERE customer_id = ? AND item_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    
    if (!$update_stmt) {
        echo json_encode(['error' => 'Failed to prepare update query']);
        exit;
    }

    $update_stmt->bind_param('iii', $new_quantity, $_TOKEN['sub'], $item_id);
    
    if ($update_stmt->execute()) {
        echo json_encode(['message' => 'Cart updated successfully']);
    } else {
        echo json_encode(['error' => 'Failed to update cart']);
    }
    $update_stmt->close();
} else {
    $existing_stmt->close();
    // Add new cart item
    $insert_sql = "INSERT INTO carts (customer_id, item_id, quantity) VALUES (?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    
    if (!$insert_stmt) {
        echo json_encode(['error' => 'Failed to prepare insert query']);
        exit;
    }

    $insert_stmt->bind_param('iii', $_TOKEN['sub'], $item_id, $quantity);
    
    if ($insert_stmt->execute()) {
        echo json_encode(['message' => 'Item added to cart successfully']);
    } else {
        echo json_encode(['error' => 'Failed to add item to cart']);
    }
    $insert_stmt->close();
}

$conn->close();