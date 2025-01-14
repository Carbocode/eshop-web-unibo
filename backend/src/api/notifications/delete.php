<?php

require '../../../vendor/autoload.php';
require '../../middleware/preflight.php';
require '../../middleware/auth.php';

// Get request body
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['notification_id'])) {
    echo json_encode(['error' => 'Notification ID is required']);
    http_response_code(400);
    exit;
}

$notification_id = $data['notification_id'];
$user_id = $_TOKEN['sub'];

// First verify the notification belongs to the user
$check_sql = "
    SELECT notification_id
    FROM notifications
    WHERE notification_id = ? AND user_id = ?
";

$check_stmt = $conn->prepare($check_sql);

if (!$check_stmt) {
    echo json_encode(['error' => 'Failed to prepare database query']);
    http_response_code(500);
    exit;
}

$check_stmt->bind_param('ii', $notification_id, $user_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Notification not found or unauthorized']);
    http_response_code(404);
    exit;
}

// Delete the notification
$delete_sql = "
    DELETE FROM notifications
    WHERE notification_id = ? AND user_id = ?
";

$delete_stmt = $conn->prepare($delete_sql);

if (!$delete_stmt) {
    echo json_encode(['error' => 'Failed to prepare delete query']);
    http_response_code(500);
    exit;
}

$delete_stmt->bind_param('ii', $notification_id, $user_id);

if (!$delete_stmt->execute()) {
    echo json_encode(['error' => 'Failed to delete notification']);
    http_response_code(500);
    exit;
}

echo json_encode([
    'success' => true,
    'notification_id' => $notification_id
]);

$check_stmt->close();
$delete_stmt->close();
$conn->close();