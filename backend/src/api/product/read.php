<?php
require '../../middleware/preflight.php';

// Get team ID
$team_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Check if team_id is valid
if ($team_id === 0) {
    echo json_encode(['error' => 'Invalid team ID']);
    exit;
}

// Simple query to get tshirts for a team
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
        e.year as edition_year,
        s.size_id,
        s.name as size_name,
        w.availability,
        w.item_id
    FROM 
        teams t
    JOIN 
        tshirts ts ON t.team_id = ts.team_id
    JOIN 
        editions e ON ts.edition_id = e.edition_id
    LEFT JOIN 
        warehouse w ON ts.tshirt_id = w.tshirt_id
    LEFT JOIN 
        sizes s ON w.size_id = s.size_id
    WHERE 
        t.team_id = ?
";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(['error' => 'Failed to prepare SQL statement']);
    exit;
}

$stmt->bind_param("i", $team_id);
$stmt->execute();
$result = $stmt->get_result();

// Prepare response array
$response = [
    'team_id' => '',
    'team_name' => '',
    'team_logo' => '',
    'tshirts' => []
];

// Check if result is empty
if ($result->num_rows === 0) {
    echo json_encode(['error' => 'No t-shirts found for this team']);
    exit;
}

// Process query result
while ($row = $result->fetch_assoc()) {
    // Set team info if not set
    if (!$response['team_id']) {
        $response['team_id'] = $row['team_id'];
        $response['team_name'] = $row['team_name'];
        $response['team_logo'] = $row['team_logo'];
    }

    // Find if t-shirt already exists in array
    $tshirt_exists = false;
    foreach ($response['tshirts'] as &$tshirt) {
        if ($tshirt['tshirt_id'] == $row['tshirt_id']) {
            $tshirt_exists = true;
            
            $tshirt['sizes'][] = [
                'size_id' => $row['size_id'],
                'size_name' => $row['size_name'],
                'item_id' => $row['item_id'],
                'availability' => (int)$row['availability']
            ];
            break;
        }
    }

    // If t-shirt doesn't exist, add it
    if (!$tshirt_exists) {
        $response['tshirts'][] = [
            'tshirt_id' => $row['tshirt_id'],
            'price' => (float)$row['price'],
            'image_url' => $row['image_url'],
            'edition_id' => $row['edition_id'],
            'edition_name' => $row['edition_name'],
            'edition_year' => $row['edition_year'],
            'sizes' => [[
                'size_id' => $row['size_id'],
                'size_name' => $row['size_name'],
                'item_id' => $row['item_id'],
                'availability' => (int)$row['availability']
            ]]
        ];
    }
}

echo json_encode($response);
$stmt->close();
$conn->close();
