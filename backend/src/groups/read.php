<?php
// Configurazione del database
$host = 'localhost:3306';
$user = 'root';
$password = '';
$database = 'elprimerofootballer';

// Connessione al database
$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Errore di connessione: " . $conn->connect_error);
}

header('Content-Type: application/json');

// Query per ottenere i gruppi e le nazioni
$sql = "
    SELECT 
      g.id_group AS group_id,
      g.name AS group_name,
      c.id_country AS country_id,
      c.name AS country_name
    FROM 
      groups g
    JOIN 
      groups_nations gn ON g.id_group = gn.id_group
    JOIN 
      countries c ON gn.id_country = c.id_country
    ORDER BY g.id_group, c.name;
";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode(["error" => "Errore del server: " . $conn->error]);
    $conn->close();
    exit;
}

$groups = [];

while ($row = $result->fetch_assoc()) {
    $group_id = $row['group_id'];
    if (!isset($groups[$group_id])) {
        $groups[$group_id] = [
            'group_id' => $group_id,
            'group_name' => $row['group_name'],
            'countries' => []
        ];
    }
    $groups[$group_id]['countries'][] = [
        'country_id' => $row['country_id'],
        'country_name' => $row['country_name']
    ];
}

// Riorganizza i gruppi in un array numerico
$groups = array_values($groups);

echo json_encode($groups);

$conn->close();
