<?php  
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header('Content-Type: application/json');

// Configurazione del database
$host = 'localhost:3306';
$user = 'root';
$password = '';
$database = 'elprimerofootballer';

// Abilita la visualizzazione degli errori PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connessione al database
$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    echo json_encode(["error" => "Errore di connessione: " . $conn->connect_error]);
    exit;
}

// Decodifica del token JWT
require 'vendor/autoload.php';  // Assicurati di aver installato firebase/php-jwt
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$jwtSecret = 'tuasegretatokenkey';
$headers = getallheaders();

if (!isset($headers['Authorization'])) {
    echo json_encode(["error" => "Token non presente"]);
    exit;
}

$authHeader = $headers['Authorization'];
$jwt = str_replace('Bearer ', '', $authHeader);

try {
    $decoded = JWT::decode($jwt, new Key($jwtSecret, 'HS256'));
    
    // Recupera il customer_id dal payload del token
    $customer_id = intval($decoded->sub);
    
    if ($decoded->exp < time()) {
        echo json_encode(["error" => "Token scaduto"]);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(["error" => "Token non valido"]);
    exit;
}


// Determina il metodo della richiesta (GET, POST, PUT, DELETE)
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $sql = "SELECT ci.cart_item_id, ci.quantity, ts.tshirt_id, ts.price, ts.image_url, t.name AS team_name
                FROM cart_tshirts ci
                INNER JOIN tshirts ts ON ci.tshirt_id = ts.tshirt_id
                INNER JOIN teams t ON ts.team_id = t.team_id
                WHERE ci.customer_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $cart_items = [];
        while ($row = $result->fetch_assoc()) {
            $cart_items[] = [
                'cart_item_id' => $row['cart_item_id'],
                'quantity' => $row['quantity'],
                'tshirt' => [
                    'tshirt_id' => $row['tshirt_id'],
                    'price' => $row['price'],
                    'image_url' => $row['image_url'],
                    'team_name' => $row['team_name']
                ]
            ];
        }

        if (empty($cart_items)) {
            echo json_encode(["message" => "Nessun articolo nel carrello"]);
        } else {
            echo json_encode($cart_items);
        }
        break;

    case 'POST':
        // Aggiungi un nuovo articolo al carrello
        $data = json_decode(file_get_contents("php://input"), true);
        $customer_id = intval($data['customer_id']);

        $sql = "INSERT INTO cart_tshirts (customer_id, tshirt_id, quantity) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $customer_id, $tshirt_id, $quantity);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Articolo aggiunto al carrello"]);
        } else {
            echo json_encode(["error" => "Errore nell'aggiunta dell'articolo"]);
        }
        break;

    case 'PUT':
        // Aggiorna la quantità di un articolo nel carrello
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['cart_item_id'], $data['quantity'])) {
            echo json_encode(["error" => "Parametri mancanti"]);
            break;
        }

        $cart_item_id = intval($data['cart_item_id']);
        $quantity = intval($data['quantity']);

        $sql = "UPDATE cart_tshirts SET quantity = ? WHERE cart_item_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $quantity, $cart_item_id);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Quantità aggiornata"]);
        } else {
            echo json_encode(["error" => "Errore nell'aggiornamento della quantità"]);
        }
        break;

    case 'DELETE':
        // Rimuovi un articolo dal carrello
        if (!isset($_GET['cart_item_id'])) {
            echo json_encode(["error" => "Parametro 'cart_item_id' mancante"]);
            break;
        }

        $cart_item_id = intval($_GET['cart_item_id']);
        $sql = "DELETE FROM cart_tshirts WHERE cart_item_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $cart_item_id);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Articolo rimosso dal carrello"]);
        } else {
            echo json_encode(["error" => "Errore nella rimozione dell'articolo"]);
        }
        break;

    default:
        echo json_encode(["error" => "Metodo non supportato"]);
        break;
}

$conn->close();
?>
