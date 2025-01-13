<?php
require '../../middleware/preflight.php';
require '../../../vendor/autoload.php';
require '../../middleware/auth.php';

if (!isset($_GET['item_id'])) {
    echo json_encode(['error' => 'Missing item_id parameter']);
    exit;
}

$item_id = intval($_GET['item_id']);

// Verify item exists in user's cart
$check_sql = "SELECT quantity FROM carts WHERE customer_id = ? AND item_id = ?";
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

$check_stmt->close();

// Delete the item from cart
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
$conn->close();