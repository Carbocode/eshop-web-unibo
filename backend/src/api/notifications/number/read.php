<?php

require '../../../../vendor/autoload.php';
require '../../../middleware/auth.php';
require '../../middleware/preflight.php';

// ID utente autenticato
$userId = $_TOKEN['sub'];

// Prepara la query per contare gli oggetti nel carrello
$sql = "SELECT COUNT(*) AS notifications_count FROM notifications WHERE user_id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => 'Failed to prepare database query']);
    exit;
}

// Associa i parametri
$stmt->bind_param('i', $userId);

// Esegui la query
if ($stmt->execute()) {
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(['notifications_count' => (int)$row['notifications_count']]);
    } else {
        echo json_encode(['notifications_count' => 0]);
    }
} else {
    echo json_encode(['error' => 'Failed to fetch cart data']);
}

// Chiudi la connessione
$stmt->close();
$conn->close();
