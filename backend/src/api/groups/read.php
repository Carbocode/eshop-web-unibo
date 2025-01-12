<?php
require '../config/middleware.php';

// Query per ottenere i gruppi e le nazioni
$sql = "
    SELECT 
      g.group_id AS group_id,
      g.name AS group_name,
      c.country_id AS country_id,
      c.name AS country_name,
      c.flag AS country_flag
    FROM 
      groups g
    JOIN 
      groups_nations gn ON g.group_id = gn.group_id
    JOIN 
      countries c ON gn.country_id = c.country_id
    ORDER BY g.group_id, c.name;
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
    'country_name' => $row['country_name'],
    'country_flag' => $row['country_flag']
  ];
}

// Riorganizza i gruppi in un array numerico
$groups = array_values($groups);

echo json_encode($groups);

$conn->close();
