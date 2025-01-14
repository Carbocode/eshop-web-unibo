<?php

require '../../../../vendor/autoload.php';
require '../../../middleware/auth.php';
require '../../../middleware/preflight.php';

// Recupera i dati JSON dalla richiesta
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['error' => 'Invalid JSON input']);
    exit;
}

// Controlla se il campo necessario è presente
if (!isset($_TOKEN['sub']) || empty($_TOKEN['sub'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

// ID utente autenticato
$userId = $_TOKEN['sub'];

// Prepara la query per contare gli oggetti nel carrello
$sql = "SELECT COUNT(*) AS item_count FROM cart WHERE user_id = ?";

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
        echo json_encode(['item_count' => (int)$row['item_count']]);
    } else {
        echo json_encode(['item_count' => 0]);
    }
} else {
    echo json_encode(['error' => 'Failed to fetch cart data']);
}

// Chiudi la connessione
$stmt->close();
$conn->close();
