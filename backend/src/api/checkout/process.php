<?php
$start = microtime(true);
require '../../middleware/preflight.php';
require '../../../vendor/autoload.php';
require '../../middleware/auth.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header('Content-Type: application/json');
define('JWT_SECRET','tuasegretatokenkey');
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

function processCheckout($conn,  $id) {
    try {
        // Authenticate customer
        $customer_id = $id;        
        // Process checkout logic
        $cart_items = getCartItems($conn, $customer_id);
        if (empty($cart_items)) {
            throw new Exception("Cart is empty");
        }
        
        $subtotal = 0;
        $shipping_cost = 10.00; // Fixed shipping cost
        foreach ($cart_items as $item) {
            if (!checkStock($conn, $item['item_id'], $item['quantity'])) {
                throw new Exception("Insufficient stock for item: " . $item['item_id']);
            }
            $subtotal += $item['price'] * $item['quantity'];
        }
        
        // Calculate tax and total
        $tax = $subtotal * 0.22; // 22% IVA
        $total = $subtotal + $shipping_cost + $tax;
        
        // Create order with pending status (assuming status_id 1 is 'pending')
        $order_id = createOrder($conn, $customer_id, $subtotal, $shipping_cost, $tax, $total);
        
        foreach ($cart_items as $item) {
            createOrderItem($conn, $order_id, $item);
            updateStock($conn, $item['item_id'], $item['quantity']);
        }
        
        clearCart($conn, $customer_id);
        
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => 'Order created successfully',
            'order_id' => $order_id
        ]);
        
    } catch (Exception $e) {
        http_response_code($e->getCode() ?: 500);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}

function getCartItems($conn, $customer_id) {
    $query = "SELECT c.quantity,tm.name as team,e.`name` as edition, t.image_url, t.price, s.`name` as size, t.tshirt_id as item_id, c.item_id
              FROM carts c
              Inner JOIN warehouse w ON c.item_id = w.item_id
              Inner JOIN tshirts t ON w.tshirt_id = t.tshirt_id
              inner join editions e on e.edition_id = t.edition_id
              inner join teams tm on tm.team_id = t.team_id
              inner join sizes s on w.size_id = s.size_id
              WHERE c.customer_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function checkStock($conn, $item_id, $quantity) {
    $query = "SELECT availability FROM warehouse WHERE item_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row && $row['availability'] >= $quantity;
}

function createOrder($conn, $customer_id, $subtotal, $shipping_cost, $tax, $total) {
    $status_id = 1; // Assuming 1 is 'pending' status
    $query = "INSERT INTO orders (customer_id, status_id, subtotal, shipping_cost, tax, total) 
              VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iidddd", $customer_id, $status_id, $subtotal, $shipping_cost, $tax, $total);
    $stmt->execute();
    
    return $stmt->insert_id;
}

function createOrderItem($conn, $order_id, $item) {
    $query = "INSERT INTO order_items (order_id, item_id, quantity, paid_price) 
              VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiid", $order_id, $item['item_id'], $item['quantity'], $item['price']);
    $stmt->execute();
}

function updateStock($conn, $item_id, $quantity) {
    $query = "UPDATE warehouse 
              SET availability = availability - ? 
              WHERE item_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $quantity, $item_id);
    $stmt->execute();
}

function clearCart($conn, $customer_id) {
    $query = "DELETE FROM carts WHERE customer_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
}
if($_SERVER['REQUEST_METHOD']=='GET'){
    $out =getCartItems($conn, $_TOKEN['sub']);
    echo(json_encode($out)); //temporaneo perche quello di so non va
    $conn->close();
    exit;

}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Metodo non consentito. Usa POST."]);
    $conn->close();
    exit;
}
processCheckout($conn, $_TOKEN['sub']);

$conn->close();
