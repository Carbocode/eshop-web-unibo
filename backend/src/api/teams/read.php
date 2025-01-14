<?php
require '../../../vendor/autoload.php';
require '../../middleware/preflight.php';

$sql = "
    SELECT 
        team_id,
        name,
        logo
    FROM 
        teams
    ORDER BY 
        name
";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode(["error" => "Server error: " . $conn->error]);
    $conn->close();
    exit;
}

$teams = [];
while ($row = $result->fetch_assoc()) {
    $teams[] = [
        'team_id' => $row['team_id'],
        'name' => $row['name'],
        'logo' => $row['logo']
    ];
}

echo json_encode($teams);
$conn->close();