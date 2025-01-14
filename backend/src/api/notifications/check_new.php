<?php

require '../../../vendor/autoload.php';
require '../../middleware/preflight.php';
require '../../middleware/auth.php';

$user_id = $_TOKEN['sub'];

// Get the last check timestamp from the request headers
$last_check = isset($_SERVER['HTTP_LAST_CHECK']) ? $_SERVER['HTTP_LAST_CHECK'] : null;

if (!$last_check) {
    // If no last check time provided, check for any unread notifications
    $sql = "
        SELECT COUNT(*) as count
        FROM notifications
        WHERE user_id = ?
        AND is_read = 0
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
} else {
    // Check for notifications newer than last check
    $sql = "
        SELECT COUNT(*) as count
        FROM notifications
        WHERE user_id = ?
        AND created_at > ?
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('is', $user_id, $last_check);
}

if (!$stmt) {
    echo json_encode(['error' => 'Failed to prepare database query']);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode([
    'hasNew' => $row['count'] > 0,
    'count' => $row['count']
]);

$stmt->close();
$conn->close();