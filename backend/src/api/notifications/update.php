<?php

require '../../../vendor/autoload.php';
require '../../middleware/preflight.php';
require '../../middleware/auth.php';

// Get request body
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['notification_id']) || !isset($data['action'])) {
    echo json_encode(['error' => 'Missing required fields']);
    http_response_code(400);
    exit;
}

$notification_id = $data['notification_id'];
$action = $data['action'];
$user_id = $_TOKEN['sub'];

// Verify the notification belongs to the user
$check_sql = "
    SELECT notification_id, is_read
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

$notification = $result->fetch_assoc();

if ($action === 'toggle_status') {
    // Toggle the read status
    $new_status = $notification['is_read'] ? 0 : 1;
    
    $update_sql = "
        UPDATE notifications
        SET is_read = ?
        WHERE notification_id = ? AND user_id = ?
    ";
    
    $update_stmt = $conn->prepare($update_sql);
    
    if (!$update_stmt) {
        echo json_encode(['error' => 'Failed to prepare update query']);
        http_response_code(500);
        exit;
    }
    
    $update_stmt->bind_param('iii', $new_status, $notification_id, $user_id);
    
    if (!$update_stmt->execute()) {
        echo json_encode(['error' => 'Failed to update notification']);
        http_response_code(500);
        exit;
    }
    
    $update_stmt->close();
} else {
    echo json_encode(['error' => 'Invalid action']);
    http_response_code(400);
    exit;
}

echo json_encode([
    'success' => true,
    'notification_id' => $notification_id,
    'is_read' => (bool)$new_status
]);

$check_stmt->close();
$conn->close();