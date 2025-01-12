<?php

require '../../../../vendor/autoload.php';
require '../../../middleware/preflight.php';

// Query per ottenere tutti gli stati possibili dell'ordine
$sql = "SELECT status_id, status, icon FROM order_status";
$result = $conn->query($sql);

if ($result === false) {
    echo json_encode(['error' => 'Failed to fetch order statuses']);
    exit;
}

// Recupera i risultati
$statuses = [];
while ($row = $result->fetch_assoc()) {
    $statuses[] = $row;
}

// Restituisci i dati in formato JSON
echo json_encode($statuses);

// Chiudi la connessione
$conn->close();
