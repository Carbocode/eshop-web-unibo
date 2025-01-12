<?php
require '../../middleware/preflight.php';


// Controllo del parametro id_team
if (!isset($_GET['id_team'])) {
    echo json_encode(["error" => "Parametro 'id_team' mancante"]);
    $conn->close();
    exit;
}

$team_id = isset($_GET['id_team']) ? intval($_GET['id_team']) : 0;

if ($team_id > 0) {
    $query = "SELECT 
                  tshirts.tshirt_id,
                  teams.team_id,
                  teams.name AS team_name,
                  editions.edition_id,
                  editions.year,
                  editions.description,
                  tshirts.size,
                  tshirts.price,
                  warehouse.availability,
                  tshirts.image_url,
                  GROUP_CONCAT(DISTINCT versions.name) AS versions
              FROM 
                  tshirts
              INNER JOIN 
                  teams ON tshirts.team_id = teams.team_id
              INNER JOIN 
                  editions ON tshirts.edition_id = editions.edition_id
              INNER JOIN 
                  warehouse ON tshirts.tshirt_id = warehouse.tshirt_id
              LEFT JOIN 
                  tshirt_versions ON tshirts.tshirt_id = tshirt_versions.tshirt_id
              LEFT JOIN 
                  versions ON tshirt_versions.version_id = versions.version_id
              WHERE 
                  teams.team_id = :team_id
              GROUP BY 
                  tshirts.tshirt_id";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':team_id', $team_id);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Prepara la risposta JSON
        $response = [
            "tshirt_id" => $row['tshirt_id'],
            "team" => [
                "team_id" => $row['team_id'],
                "team_name" => $row['team_name']
            ],
            "edition" => [
                "edition_id" => $row['edition_id'],
                "year" => $row['year'],
                "description" => $row['description']
            ],
            "size" => $row['size'],
            "price" => (float)$row['price'],
            "availability" => (int)$row['availability'],
            "image_url" => $row['image_url'],
            "versions" => explode(',', $row['versions'])
        ];

        echo json_encode($response);
    } else {
        echo json_encode(["message" => "Nessuna t-shirt trovata per il team selezionato."]);
    }
} else {
    echo json_encode(["message" => "ID team non valido."]);
}
$stmt->close();
$conn->close();
?>
