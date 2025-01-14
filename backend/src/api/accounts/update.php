<?php

require '../../../vendor/autoload.php';
require '../../middleware/preflight.php';
require '../../middleware/auth.php';


// Recupera i dati JSON dalla richiesta
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['error' => 'Invalid JSON input']);
    exit;
}

// Valida i campi richiesti
$requiredFields = ['email', 'full_name', 'phone', 'address', 'city', 'province', 'zip', 'country'];
foreach ($requiredFields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        echo json_encode(['error' => "Field '$field' is required"]);
        exit;
    }
}

// Prepara la query per l'aggiornamento
$sql = "UPDATE customers 
        SET email = ?, full_name = ?, phone = ?, address = ?, city = ?, province = ?, zip = ?, country = ?
        WHERE customer_id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => 'Failed to prepare database query']);
    exit;
}

// Associa i parametri
$stmt->bind_param(
    'ssssssssi',
    $data['email'],
    $data['full_name'],
    $data['phone'],
    $data['address'],
    $data['city'],
    $data['province'],
    $data['zip'],
    $data['country'],
    $_TOKEN['sub'] // Identifica l'utente autenticato
);

// Esegui la query
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => 'Customer details updated successfully']);
    } else {
        echo json_encode(['error' => 'No changes were made or customer not found']);
    }
} else {
    echo json_encode(['error' => 'Failed to update customer details']);
}

// Chiudi la connessione
$stmt->close();
$conn->close();
