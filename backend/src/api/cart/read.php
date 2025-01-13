<?php
require '../../middleware/preflight.php';
require '../../../vendor/autoload.php';
require '../../middleware/auth.php';

$sql = "SELECT c.item_id, c.quantity, w.tshirt_id, t.price, t.image_url, 
        tm.name as team_name, s.name as size_name
        FROM carts c
        INNER JOIN warehouse w ON c.item_id = w.item_id
        INNER JOIN tshirts t ON w.tshirt_id = t.tshirt_id
        INNER JOIN teams tm ON t.team_id = tm.team_id
        INNER JOIN sizes s ON w.size_id = s.size_id
        WHERE c.customer_id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => 'Failed to prepare database query']);
    exit;
}

$stmt->bind_param('i', $_TOKEN['sub']);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
while ($row = $result->fetch_assoc()) {
    $cart_items[] = [
        'item_id' => $row['item_id'],
        'quantity' => $row['quantity'],
        'tshirt' => [
            'tshirt_id' => $row['tshirt_id'],
            'price' => $row['price'],
            'image_url' => $row['image_url'],
            'team_name' => $row['team_name'],
            'size' => $row['size_name']
        ]
    ];
}

if (empty($cart_items)) {
    echo json_encode(['message' => 'Cart is empty']);
} else {
    echo json_encode($cart_items);
}

$stmt->close();
$conn->close();
