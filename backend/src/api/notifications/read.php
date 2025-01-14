<?php

require '../../../vendor/autoload.php';
require '../../middleware/preflight.php';
require '../../middleware/auth.php';

// Get user info from token
$user_id = $_TOKEN['sub'];
$is_admin = $_TOKEN['role'] === 'ADMIN';

// Base query for notifications
$sql = "
    SELECT
        n.notification_id as id,
        n.type,
        n.message,
        n.created_at as timestamp,
        n.is_read as `read`
    FROM notifications n
    WHERE n.user_id = ? " .
    ($is_admin ? "OR n.user_id = -1 " : "") . // Include admin notifications if user is admin
    "ORDER BY n.created_at DESC
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => 'Failed to prepare database query']);
    exit;
}

$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    // Convert is_read to boolean
    $row['read'] = (bool)$row['read'];
    $notifications[] = $row;
}

echo json_encode($notifications);

$stmt->close();
$conn->close();