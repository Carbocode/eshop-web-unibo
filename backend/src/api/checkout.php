<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class CheckoutController {
    private $db;
    private $jwt_secret;
    private $customer_id;
    
    public function __construct() {
        $this->db = new PDO("mysql:host=localhost:3306;dbname=soccer_tshirt_shop", "root", "toor");
        $this->jwt_secret = getenv('JWT_SECRET');
    }
    
    private function authenticateCustomer() {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            throw new Exception('No authorization token provided', 401);
        }
        
        try {
            $token = explode(' ', $headers['Authorization'])[1];
            $decoded = JWT::decode($token, new Key($this->jwt_secret, 'HS256'));
            if ($decoded->type !== 'customer') {
                throw new Exception('Invalid user type', 401);
            }
            return $decoded->userId;
        } catch(Exception $e) {
            throw new Exception('Invalid token', 401);
        }
    }
    
    public function processCheckout() {
        try {
            // Authenticate customer
            $this->customer_id = $this->authenticateCustomer();
            
            // Get and validate input
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['address_id'])) {
                throw new Exception('Address ID is required', 400);
            }
            
            // Start transaction
            $this->db->beginTransaction();
            
            // Process checkout logic
            $cart_items = $this->getCartItems();
            if (empty($cart_items)) {
                throw new Exception("Cart is empty");
            }
            
            $total_amount = 0;
            foreach ($cart_items as $item) {
                if (!$this->checkStock($item['tshirt_id'], $item['quantity'])) {
                    throw new Exception("Insufficient stock for item: " . $item['tshirt_id']);
                }
                $total_amount += $item['price'] * $item['quantity'];
            }
            
            $order_id = $this->createOrder($data['address_id'], $total_amount);
            
            foreach ($cart_items as $item) {
                $this->createOrderItem($order_id, $item);
                $this->updateStock($item['tshirt_id'], $item['quantity']);
            }
            
            $this->clearCart();
            
            $this->db->commit();
            
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Order created successfully',
                'order_id' => $order_id
            ]);
            
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            http_response_code($e->getCode() ?: 500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
    
    private function getCartItems() {
        $query = "SELECT ci.*, t.price, t.stock_quantity 
                 FROM cart_items ci
                 JOIN tshirts t ON ci.tshirt_id = t.tshirt_id
                 WHERE ci.customer_id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$this->customer_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function checkStock($tshirt_id, $quantity) {
        $query = "SELECT stock_quantity FROM tshirts WHERE tshirt_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$tshirt_id]);
        $stock = $stmt->fetchColumn();
        
        return $stock >= $quantity;
    }
    
    private function createOrder($address_id, $total_amount) {
        $query = "INSERT INTO orders (customer_id, address_id, order_status, total_amount) 
                 VALUES (?, ?, 'pending', ?)";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$this->customer_id, $address_id, $total_amount]);
        
        return $this->db->lastInsertId();
    }
    
    private function createOrderItem($order_id, $item) {
        $query = "INSERT INTO order_items (order_id, tshirt_id, quantity, unit_price) 
                 VALUES (?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            $order_id,
            $item['tshirt_id'],
            $item['quantity'],
            $item['price']
        ]);
    }
    
    private function updateStock($tshirt_id, $quantity) {
        $query = "UPDATE tshirts 
                 SET stock_quantity = stock_quantity - ? 
                 WHERE tshirt_id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$quantity, $tshirt_id]);
    }
    
    private function clearCart() {
        $query = "DELETE FROM cart_items WHERE customer_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$this->customer_id]);
    }
}

// Route handling
$checkout = new CheckoutController();
$request = $_SERVER['REQUEST_URI'];

if ($request === '/checkout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $checkout->processCheckout();
}
?>