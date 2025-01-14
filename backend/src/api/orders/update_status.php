<?php

require '../../../vendor/autoload.php';
require '../../middleware/preflight.php';
require '../../middleware/auth.php';
require '../notifications/create_notification.php';

// Verify seller role
if ($_TOKEN['role'] !== 'seller') {
    echo json_encode(['error' => 'Unauthorized']);
    http_response_code(403);
    exit;
}

// Get request body
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['order_id']) || !isset($data['status_id'])) {
    echo json_encode(['error' => 'Missing required fields']);
    http_response_code(400);
    exit;
}

$order_id = $data['order_id'];
$status_id = $data['status_id'];
$seller_id = $_TOKEN['sub'];

// First get the current order details and verify seller ownership
$check_sql = "
    SELECT o.order_id, o.customer_id, o.status_id, os.status as status_name
    FROM orders o
    INNER JOIN order_status os ON o.status_id = os.status_id
    WHERE o.order_id = ? AND o.seller_id = ?
";

$check_stmt = $conn->prepare($check_sql);

if (!$check_stmt) {
    echo json_encode(['error' => 'Failed to prepare database query']);
    http_response_code(500);
    exit;
}

$check_stmt->bind_param('ii', $order_id, $seller_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Order not found or unauthorized']);
    http_response_code(404);
    exit;
}

$order = $result->fetch_assoc();

// Get the new status name
$status_sql = "SELECT status FROM order_status WHERE status_id = ?";
$status_stmt = $conn->prepare($status_sql);
$status_stmt->bind_param('i', $status_id);
$status_stmt->execute();
$status_result = $status_stmt->get_result();

if ($status_result->num_rows === 0) {
    echo json_encode(['error' => 'Invalid status ID']);
    http_response_code(400);
    exit;
}

$new_status = $status_result->fetch_assoc()['status'];

// Update the order status
$update_sql = "
    UPDATE orders
    SET status_id = ?,
        updated_at = NOW()
    WHERE order_id = ? AND seller_id = ?
";

$update_stmt = $conn->prepare($update_sql);

if (!$update_stmt) {
    echo json_encode(['error' => 'Failed to prepare update query']);
    http_response_code(500);
    exit;
}

$update_stmt->bind_param('iii', $status_id, $order_id, $seller_id);

if (!$update_stmt->execute()) {
    echo json_encode(['error' => 'Failed to update order status']);
    http_response_code(500);
    exit;
}

// Create notification for customer about status change
create_order_status_notification($order_id, $new_status, $order['customer_id']);

// Return success response
echo json_encode([
    'success' => true,
    'order_id' => $order_id,
    'new_status' => $new_status,
    'previous_status' => $order['status_name']
]);

// Close all statements
$check_stmt->close();
$status_stmt->close();
$update_stmt->close();
$conn->close();