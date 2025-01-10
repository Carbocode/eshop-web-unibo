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

// Query per ottenere tutte le squadre di tutte le leghe
$sql = "
    SELECT 
      t.team_id AS team_id,
      t.name AS team_name,
      t.logo AS team_logo,
      l.id_league AS league_id,
      l.logo AS league_logo,
      l.name AS league_name,
      c.name AS country_name
    FROM 
      teams t
    INNER JOIN 
      leagues l ON t.id_league = l.id_league
    INNER JOIN 
      countries c ON t.id_country = c.id_country
    ORDER BY l.name, t.name;
";

$result = $conn->query($sql);

if (!$result) {
  echo json_encode(["error" => "Errore del server: " . $conn->error]);
  $conn->close();
  exit;
}

$leagues = [];

while ($row = $result->fetch_assoc()) {
  $league_name = $row['league_name'];
  if (!isset($leagues[$league_name])) {
    $leagues[$league_name] = [
      'league_name' => $league_name,
      'league_id' => $row['league_id'],
      'league_logo' => $row['league_logo'],
      'teams' => []
    ];
  }
  $leagues[$league_name]['teams'][] = [
    'team_id' => $row['team_id'],
    'team_name' => $row['team_name'],
    'team_logo' => $row['team_logo'],
    'country_name' => $row['country_name']
  ];
}

// Riorganizza le leghe in un array numerico
$leagues = array_values($leagues);

echo json_encode($leagues);

$conn->close();
