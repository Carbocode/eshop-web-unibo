<?php

require '../../../vendor/autoload.php';
require '../../middleware/preflight.php';
require '../../middleware/auth.php';

// Query per ottenere i dettagli di tutti gli ordini
$sql = "
    SELECT 
        o.order_id, 
        o.status_id, 
        os.status, 
        os.icon, 
        o.subtotal, 
        o.shipping_cost, 
        o.tax, 
        o.total,
        o.tracking_number,
        o.delivery,
        o.shipping_agent,
        c.full_name, 
        c.address, 
        c.city, 
        c.province, 
        c.zip, 
        c.country
    FROM orders o
    INNER JOIN order_status os ON o.status_id = os.status_id
    INNER JOIN customers c ON o.customer_id = c.customer_id
    WHERE o.customer_id = ?
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => 'Failed to prepare database query']);
    exit;
}

$stmt->bind_param('i', $_TOKEN['sub']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([]);
    exit;
}

$orders = $result->fetch_all(MYSQLI_ASSOC);

// Query per ottenere gli item dell'ordine, includendo taglia e edizione
$item_sql = "
    SELECT 
        oi.order_id,
        oi.item_id, 
        oi.quantity, 
        oi.paid_price, 
        tsh.tshirt_id, 
        tsh.price AS tshirt_price, 
        tsh.image_url AS tshirt_image, 
        tsh.team_id, 
        t.name AS team_name, 
        e.name AS edition_name, 
        s.name AS size_name
    FROM order_items oi
    INNER JOIN warehouse w ON oi.item_id = w.item_id
    INNER JOIN tshirts tsh ON w.tshirt_id = tsh.tshirt_id
    INNER JOIN teams t ON tsh.team_id = t.team_id
    INNER JOIN editions e ON tsh.edition_id = e.edition_id
    INNER JOIN sizes s ON w.size_id = s.size_id
    WHERE oi.order_id IN (" . implode(',', array_map(function($order) { return $order['order_id']; }, $orders)) . ")";

$item_stmt = $conn->prepare($item_sql);

if (!$item_stmt) {
    echo json_encode(['error' => 'Failed to prepare item query']);
    exit;
}

$item_stmt->execute();
$item_result = $item_stmt->get_result();

// Create a map of order_id to items
$items_map = [];
while ($row = $item_result->fetch_assoc()) {
    if (!isset($items_map[$row['order_id']])) {
        $items_map[$row['order_id']] = [];
    }
    $items_map[$row['order_id']][] = [
        'item_id' => $row['item_id'],
        'quantity' => $row['quantity'],
        'paid_price' => $row['paid_price'],
        'tshirt' => [
            'tshirt_id' => $row['tshirt_id'],
            'price' => $row['tshirt_price'],
            'image_url' => $row['tshirt_image'],
            'edition_name' => $row['edition_name'],
            'size_name' => $row['size_name']
        ],
        'team' => [
            'team_id' => $row['team_id'],
            'team_name' => $row['team_name']
        ]
    ];
}

// Add items to each order
$orders_with_items = array_map(function($order) use ($items_map) {
    $order['items'] = isset($items_map[$order['order_id']]) ? $items_map[$order['order_id']] : [];
    return $order;
}, $orders);

// Chiudi le connessioni
$item_stmt->close();
$stmt->close();
$conn->close();

// Restituisci i dati in formato JSON
echo json_encode($orders_with_items);