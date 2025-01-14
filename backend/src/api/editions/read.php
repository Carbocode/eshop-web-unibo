<?php
require '../../../vendor/autoload.php';
require '../../middleware/preflight.php';

$sql = "
    SELECT 
        edition_id,
        name,
        year,
        description
    FROM 
        editions
    ORDER BY 
        year DESC,
        name
";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode(["error" => "Server error: " . $conn->error]);
    $conn->close();
    exit;
}

$editions = [];
while ($row = $result->fetch_assoc()) {
    $editions[] = [
        'edition_id' => $row['edition_id'],
        'name' => $row['name'],
        'year' => (int)$row['year'],
        'description' => $row['description']
    ];
}

echo json_encode($editions);
$conn->close();