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
if (!isset($data['name']) || !isset($data['year'])) {
    echo json_encode(['error' => 'Name and year are required']);
    exit;
}

// Validate year format
if (!is_numeric($data['year']) || $data['year'] < 1900 || $data['year'] > 2100) {
    echo json_encode(['error' => 'Invalid year format']);
    exit;
}

// Validate name length
if (strlen($data['name']) > 100) {
    echo json_encode(['error' => 'Name must be 100 characters or less']);
    exit;
}

// Validate description length if provided
if (isset($data['description']) && strlen($data['description']) > 500) {
    echo json_encode(['error' => 'Description must be 500 characters or less']);
    exit;
}

$sql = "INSERT INTO editions (name, year, description) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(['error' => 'Failed to prepare SQL statement']);
    exit;
}

$stmt->bind_param("sis", 
    $data['name'],
    $data['year'],
    $data['description'] ?? null
);

if (!$stmt->execute()) {
    echo json_encode(['error' => 'Failed to create edition: ' . $stmt->error]);
    $stmt->close();
    $conn->close();
    exit;
}

$edition_id = $conn->insert_id;

echo json_encode([
    'edition_id' => $edition_id,
    'name' => $data['name'],
    'year' => (int)$data['year'],
    'description' => $data['description'] ?? null
]);

$stmt->close();
$conn->close();