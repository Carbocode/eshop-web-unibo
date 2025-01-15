<?php
require '../../middleware/preflight.php';

// Query per ottenere i gruppi, le nazioni e i team
$sql = "
    SELECT 
  g.group_id AS group_id,
  g.name AS group_name,
  c.country_id AS country_id,
  c.name AS country_name,
  c.flag AS country_flag,
  t.team_id AS team_id,
  t.logo AS team_logo
FROM 
  groups g
JOIN 
  groups_nations gn ON g.group_id = gn.group_id
JOIN 
  countries c ON gn.country_id = c.country_id
LEFT JOIN 
  teams t ON c.name = t.name
ORDER BY 
  g.group_id, c.name;

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
  
  // Se il gruppo non esiste nell'array, crealo
  if (!isset($groups[$group_id])) {
    $groups[$group_id] = [
      'group_id' => $group_id,
      'group_name' => $row['group_name'],
      'countries' => []
    ];
  }
  
  // Aggiungi la nazione al gruppo
  $groups[$group_id]['countries'][] = [
    'country_id' => $row['country_id'],
    'country_name' => $row['country_name'],
    'country_flag' => $row['country_flag'],
    'team_id' => $row['team_id'],
    'team_logo' => $row['team_logo']
  ];
  }

// Riorganizza i gruppi in un array numerico
$groups = array_values($groups);

echo json_encode($groups);

$conn->close();
