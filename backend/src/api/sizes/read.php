<?php
require '../../../vendor/autoload.php';
require '../../middleware/preflight.php';

$sql = "
    SELECT 
        size_id,
        name
    FROM 
        sizes
    ORDER BY 
        FIELD(name, 'XS', 'S', 'M', 'L', 'XL', 'XXL')
";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode(["error" => "Server error: " . $conn->error]);
    $conn->close();
    exit;
}

$sizes = [];
while ($row = $result->fetch_assoc()) {
    $sizes[] = [
        'size_id' => $row['size_id'],
        'name' => $row['name']
    ];
}

echo json_encode($sizes);
$conn->close();