<?php

require '../../../vendor/autoload.php';
require '../../middleware/auth.php';
require '../../middleware/preflight.php';

// Recupera l'ID dell'ordine dalla richiesta (ad esempio come parametro GET)
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : null;

if (!$order_id) {
    echo json_encode(['error' => 'Order ID is required']);
    exit;
}

// Query per ottenere i dettagli dell'ordine e gli stati
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
    WHERE o.order_id = ? AND o.customer_id = ?
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => 'Failed to prepare database query']);
    exit;
}

// Associa i parametri (ID ordine e ID cliente autenticato)
$stmt->bind_param('ii', $order_id, $_TOKEN['sub']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Order not found']);
    exit;
}

// Recupera i dettagli dell'ordine
$order = $result->fetch_assoc();

// Restituisci i dati in formato JSON
echo json_encode([
    'order_id' => $order['order_id'],
    'status_id' => $order['status_id'],
    'status' => $order['status'],
    'icon' => $order['icon'],
    'subtotal' => $order['subtotal'],
    'shipping_cost' => $order['shipping_cost'],
    'tax' => $order['tax'],
    'total' => $order['total'],
    'tracking_number' => $order['tracking_number'],
    'delivery' => $order['delivery'],
    'shipping_agent' => $order['shipping_agent'],
    'full_name' => $order['full_name'],
    'address' => $order['address'],
    'city' => $order['city'],
    'province' => $order['province'],
    'zip' => $order['zip'],
    'country' => $order['country']
]);

// Chiudi la connessione
$stmt->close();
$conn->close();
