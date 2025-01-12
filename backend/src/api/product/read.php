<?php 
header("Access-Control-Allow-Origin:*");
header("Access-Control-Allow-Headers:*");
// Configurazione del database
$host = 'localhost:3306';
$user = 'root';
$password = '';
$database = 'elprimerofootballer';

// Abilita la visualizzazione degli errori PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Imposta l'header della risposta JSON
header('Content-Type: application/json');

// Connessione al database
$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    echo json_encode(["error" => "Errore di connessione: " . $conn->connect_error]);
    exit;
}

// Controllo se il parametro id_team è stato passato
if (!isset($_GET['id_team'])) {
    echo json_encode(["error" => "Parametro 'id_team' mancante"]);
    $conn->close();
    exit;
}

$id_team = intval($_GET['id_team']);

// Query per ottenere le t-shirt della squadra specificata
$sql = "
    SELECT 
        ts.tshirt_id,
        ts.size,
        ts.price,
        ts.image_url,
        t.team_id,
        t.name AS team_name,
        e.edition_id,
        e.year AS edition_year,
        e.description AS edition_description
    FROM 
        tshirts ts
    INNER JOIN 
        teams t ON ts.team_id = t.team_id
    INNER JOIN 
        editions e ON ts.edition_id = e.edition_id
    WHERE 
        t.team_id = ?
    ORDER BY 
        e.year, ts.size;
";

// Prepara la query
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_team);
$stmt->execute();
$result = $stmt->get_result();

$tshirts = [];

while ($row = $result->fetch_assoc()) {
    $tshirts[] = [
        'tshirt_id' => $row['tshirt_id'],
        'size' => $row['size'],
        'price' => $row['price'],
        'stock_quantity' => $row['stock_quantity'],
        'image_url' => $row['image_url'],
        'team' => [
            'team_id' => $row['team_id'],
            'team_name' => $row['team_name'],
        ],
        'edition' => [
            'edition_id' => $row['edition_id'],
            'year' => $row['edition_year'],
            'description' => $row['edition_description']
        ]
    ];
}

// Controlla se sono state trovate t-shirt
if (empty($tshirts)) {
    echo json_encode(["message" => "Nessuna t-shirt trovata per questa squadra"]);
} else {
    echo json_encode($tshirts);
}

$stmt->close();
$conn->close();
