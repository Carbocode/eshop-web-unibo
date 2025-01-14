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
if (!isset($data['team_id']) || !isset($data['edition_id']) || !isset($data['price'])) {
    echo json_encode(['error' => 'Team, edition, and price are required']);
    exit;
}

// Validate price
if (!is_numeric($data['price']) || $data['price'] < 0) {
    echo json_encode(['error' => 'Invalid price']);
    exit;
}

// Validate image URL if provided
if (isset($data['image_url']) && !filter_var($data['image_url'], FILTER_VALIDATE_URL)) {
    echo json_encode(['error' => 'Invalid image URL']);
    exit;
}

// Verify team_id exists
$stmt = $conn->prepare("SELECT team_id FROM teams WHERE team_id = ?");
$stmt->bind_param("i", $data['team_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Invalid team ID']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Verify edition_id exists
$stmt = $conn->prepare("SELECT edition_id FROM editions WHERE edition_id = ?");
$stmt->bind_param("i", $data['edition_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Invalid edition ID']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Insert new t-shirt
$sql = "INSERT INTO tshirts (team_id, edition_id, price, image_url) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(['error' => 'Failed to prepare SQL statement']);
    exit;
}

$image_url = $data['image_url'] ?? 'https://www.gravatar.com/avatar/';
$stmt->bind_param("iisd", 
    $data['team_id'],
    $data['edition_id'],
    $data['price'],
    $image_url
);

if (!$stmt->execute()) {
    echo json_encode(['error' => 'Failed to create t-shirt: ' . $stmt->error]);
    $stmt->close();
    $conn->close();
    exit;
}

$tshirt_id = $conn->insert_id;

echo json_encode([
    'tshirt_id' => $tshirt_id,
    'team_id' => $data['team_id'],
    'edition_id' => $data['edition_id'],
    'price' => (float)$data['price'],
    'image_url' => $image_url
]);

$stmt->close();
$conn->close();