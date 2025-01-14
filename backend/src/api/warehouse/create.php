<?php
require '../../../vendor/autoload.php';
require '../../middleware/preflight.php';
require '../../middleware/auth.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['tshirt_id']) || !isset($data['size_id']) || !isset($data['availability'])) {
    echo json_encode(['error' => 'T-shirt ID, size ID, and availability are required']);
    exit;
}

// Validate availability
if (!is_numeric($data['availability']) || $data['availability'] < 0) {
    echo json_encode(['error' => 'Invalid availability value']);
    exit;
}

// Verify tshirt_id exists
$stmt = $conn->prepare("SELECT tshirt_id FROM tshirts WHERE tshirt_id = ?");
$stmt->bind_param("i", $data['tshirt_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Invalid T-shirt ID']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Verify size_id exists
$stmt = $conn->prepare("SELECT size_id FROM sizes WHERE size_id = ?");
$stmt->bind_param("i", $data['size_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Invalid size ID']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Check if combination already exists
$stmt = $conn->prepare("SELECT item_id FROM warehouse WHERE tshirt_id = ? AND size_id = ?");
$stmt->bind_param("ii", $data['tshirt_id'], $data['size_id']);
$stmt->execute();
$result = $stmt->get_result();
$existing = $result->fetch_assoc();
$stmt->close();

if ($existing) {
    // Update existing record
    $sql = "UPDATE warehouse SET availability = ? WHERE item_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $data['availability'], $existing['item_id']);
    
    if (!$stmt->execute()) {
        echo json_encode(['error' => 'Failed to update inventory: ' . $stmt->error]);
        $stmt->close();
        $conn->close();
        exit;
    }
    
    $item_id = $existing['item_id'];
} else {
    // Create new record
    $sql = "INSERT INTO warehouse (tshirt_id, size_id, availability) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $data['tshirt_id'], $data['size_id'], $data['availability']);
    
    if (!$stmt->execute()) {
        echo json_encode(['error' => 'Failed to create inventory: ' . $stmt->error]);
        $stmt->close();
        $conn->close();
        exit;
    }
    
    $item_id = $conn->insert_id;
}
$stmt->close();

// Get the full details of the created/updated item
$sql = "
    SELECT 
        w.item_id,
        w.tshirt_id,
        w.size_id,
        w.availability,
        t.price,
        t.image_url,
        tm.name as team_name,
        e.name as edition_name,
        s.name as size_name
    FROM 
        warehouse w
    JOIN 
        tshirts t ON w.tshirt_id = t.tshirt_id
    JOIN 
        teams tm ON t.team_id = tm.team_id
    JOIN 
        editions e ON t.edition_id = e.edition_id
    JOIN 
        sizes s ON w.size_id = s.size_id
    WHERE 
        w.item_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

echo json_encode([
    'item_id' => $item['item_id'],
    'tshirt_id' => $item['tshirt_id'],
    'size_id' => $item['size_id'],
    'availability' => (int)$item['availability'],
    'price' => (float)$item['price'],
    'image_url' => $item['image_url'],
    'team_name' => $item['team_name'],
    'edition_name' => $item['edition_name'],
    'size_name' => $item['size_name']
]);

$stmt->close();
$conn->close();