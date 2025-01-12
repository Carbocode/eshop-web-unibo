<?php
require '../../middleware/preflight.php';
require '../../../vendor/autoload.php';
require '../../middleware/auth.php';


// Recupera i dettagli del cliente dal database
$sql = "SELECT customer_id, email, full_name, phone, admin, address, city, province, zip, country FROM customers WHERE customer_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => 'Failed to prepare database query']);
    exit;
}

// Associa il parametro e esegui la query
$stmt->bind_param('i', $_TOKEN['sub']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $customer = $result->fetch_assoc();
    echo json_encode($customer);
} else {
    echo json_encode(['error' => 'Customer not found']);
}

// Chiudi la connessione
$stmt->close();
$conn->close();
