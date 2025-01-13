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

// Verify item exists in user's cart
$check_sql = "SELECT c.quantity, w.availability 
              FROM carts c 
              INNER JOIN warehouse w ON c.item_id = w.item_id 
              WHERE c.customer_id = ? AND c.item_id = ?";
$check_stmt = $conn->prepare($check_sql);

if (!$check_stmt) {
    echo json_encode(['error' => 'Failed to prepare check query']);
    exit;
}

$check_stmt->bind_param('ii', $_TOKEN['sub'], $item_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Item not found in cart']);
    $check_stmt->close();
    $conn->close();
    exit;
}

$row = $result->fetch_assoc();
$availability = $row['availability'];
$check_stmt->close();

if ($quantity > $availability) {
    echo json_encode(['error' => 'Requested quantity exceeds availability']);
    $conn->close();
    exit;
}

if ($quantity <= 0) {
    // If quantity is 0 or negative, remove the item
    $delete_sql = "DELETE FROM carts WHERE customer_id = ? AND item_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    
    if (!$delete_stmt) {
        echo json_encode(['error' => 'Failed to prepare delete query']);
        exit;
    }

    $delete_stmt->bind_param('ii', $_TOKEN['sub'], $item_id);
    
    if ($delete_stmt->execute()) {
        echo json_encode(['message' => 'Item removed from cart']);
    } else {
        echo json_encode(['error' => 'Failed to remove item from cart']);
    }
    $delete_stmt->close();
} else {
    // Update quantity
    $update_sql = "UPDATE carts SET quantity = ? WHERE customer_id = ? AND item_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    
    if (!$update_stmt) {
        echo json_encode(['error' => 'Failed to prepare update query']);
        exit;
    }

    $update_stmt->bind_param('iii', $quantity, $_TOKEN['sub'], $item_id);
    
    if ($update_stmt->execute()) {
        echo json_encode(['message' => 'Cart updated successfully']);
    } else {
        echo json_encode(['error' => 'Failed to update cart']);
    }
    $update_stmt->close();
}

$conn->close();