<?php
require '../../middleware/preflight.php';

// Get parameters
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : 'all'; // type can be 'all', 'national', or 'league'

// Define the base query
$sql = "
    SELECT 
        t.team_id,
        t.name as team_name,
        t.logo as team_logo,
        ts.tshirt_id,
        ts.price,
        ts.image_url,
        e.edition_id as edition_id,
        e.name as edition_name,
        e.year as edition_year
    FROM 
        teams t
    LEFT JOIN 
        tshirts ts ON t.team_id = ts.team_id
    LEFT JOIN 
        editions e ON ts.edition_id = e.edition_id
";

// Modify the query based on the type
if ($type === 'nationals') {
    $sql .= " WHERE t.league_id IS NULL";
} elseif ($type === 'league') {
    if ($id === 0) {
        echo json_encode(['error' => 'Invalid league ID']);
        exit;
    }
    $sql .= " WHERE t.league_id = ?";
}

$sql .= " GROUP BY t.team_id";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(['error' => 'Failed to prepare SQL statement']);
    exit;
}

// Bind parameter if needed
if (in_array($type, ['league', 'team'])) {
    $stmt->bind_param("i", $id);
}

$stmt->execute();
$result = $stmt->get_result();

// Prepare response array
$response = [];

// Check if result is empty
if ($result->num_rows === 0) {
    echo json_encode(['error' => 'No data found']);
    exit;
}

// Process query result
while ($row = $result->fetch_assoc()) {
    $response[] = [
        'team_id' => $row['team_id'],
        'team_name' => $row['team_name'],
        'team_logo' => $row['team_logo'],
        'tshirt' => [
            'tshirt_id' => $row['tshirt_id'],
            'price' => (float)$row['price'],
            'image_url' => $row['image_url'],
            'edition_id' => $row['edition_id'],
            'edition_name' => $row['edition_name'],
            'edition_year' => $row['edition_year']
        ]
    ];
}

echo json_encode($response);
$stmt->close();
$conn->close();
