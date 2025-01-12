<?php

require '../../../vendor/autoload.php';
require '../../middleware/preflight.php';

// Recupera l'ID della lega dalla richiesta (ad esempio come parametro GET)
$league_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$league_id) {
    echo json_encode(['error' => 'League ID is required']);
    exit;
}

// Query per ottenere le squadre della lega e una sola maglietta per ogni squadra
$sql = "
    SELECT 
        t.team_id, 
        t.name AS team_name, 
        t.logo AS team_logo, 
        tsh.tshirt_id, 
        tsh.price AS tshirt_price, 
        tsh.image_url AS tshirt_image
    FROM teams t
    LEFT JOIN tshirts tsh ON t.team_id = tsh.team_id
    WHERE t.league_id = ?
    GROUP BY t.team_id
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => 'Failed to prepare database query']);
    exit;
}

// Associa i parametri
$stmt->bind_param('i', $league_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'No teams found for this league']);
    exit;
}

// Recupera i dati
$teams = [];
while ($row = $result->fetch_assoc()) {
    $teams[] = [
        'team_id' => $row['team_id'],
        'team_name' => $row['team_name'],
        'team_logo' => $row['team_logo'],
        'tshirt' => [
            'tshirt_id' => $row['tshirt_id'],
            'price' => $row['tshirt_price'],
            'image_url' => $row['tshirt_image']
        ]
    ];
}

// Restituisci i dati in formato JSON
echo json_encode($teams);

// Chiudi la connessione
$stmt->close();
$conn->close();
