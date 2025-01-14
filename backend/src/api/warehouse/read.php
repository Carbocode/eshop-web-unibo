<?php
require '../../../vendor/autoload.php';
require '../../middleware/preflight.php';
require '../../middleware/auth.php';

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
    ORDER BY 
        tm.name,
        e.name,
        FIELD(s.name, 'XS', 'S', 'M', 'L', 'XL', 'XXL')
";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode(["error" => "Server error: " . $conn->error]);
    $conn->close();
    exit;
}

$inventory = [];
while ($row = $result->fetch_assoc()) {
    $inventory[] = [
        'item_id' => $row['item_id'],
        'tshirt_id' => $row['tshirt_id'],
        'size_id' => $row['size_id'],
        'availability' => (int)$row['availability'],
        'price' => (float)$row['price'],
        'image_url' => $row['image_url'],
        'team_name' => $row['team_name'],
        'edition_name' => $row['edition_name'],
        'size_name' => $row['size_name']
    ];
}

echo json_encode($inventory);
$conn->close();