<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class CartController {
    private $db;
    private $jwt_secret;
    private $customer_id;

    public function __construct() {
        $this->db = new PDO("mysql:host=localhost:". $_ENV['DB_PORT'] .";dbname=soccer_tshirt_shop", $_ENV['DB_USER'], $_ENV['DB_PWD']);
        $this->jwt_secret = $_ENV['JWT_SECRET'];
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

    public function getCartItems() {
        try {
            $this->customer_id = $this->authenticateCustomer();
            
            $query = "SELECT ci.cart_item_id, ci.quantity, 
                     t.tshirt_id, t.price, t.image_url, t.size,
                     tm.name as team_name, e.name as edition_name
                     FROM cart_items ci
                     JOIN tshirts t ON ci.tshirt_id = t.tshirt_id
                     JOIN teams tm ON t.team_id = tm.team_id
                     JOIN editions e ON t.edition_id = e.edition_id
                     WHERE ci.customer_id = ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$this->customer_id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $total = array_reduce($items, function($sum, $item) {
                return $sum + ($item['price'] * $item['quantity']);
            }, 0);
            
            http_response_code(200);
            echo json_encode([
                'items' => $items,
                'total' => $total
            ]);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function addToCart() {
        try {
            $this->customer_id = $this->authenticateCustomer();
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['tshirt_id']) || !isset($data['quantity'])) {
                throw new Exception('Missing required fields', 400);
            }

            // Check stock availability
            $stock_query = "SELECT stock_quantity FROM tshirts WHERE tshirt_id = ?";
            $stmt = $this->db->prepare($stock_query);
            $stmt->execute([$data['tshirt_id']]);
            $stock = $stmt->fetchColumn();

            if ($stock < $data['quantity']) {
                throw new Exception('Insufficient stock', 400);
            }

            // Check if item already exists in cart
            $check_query = "SELECT cart_item_id, quantity FROM cart_items 
                          WHERE customer_id = ? AND tshirt_id = ?";
            $stmt = $this->db->prepare($check_query);
            $stmt->execute([$this->customer_id, $data['tshirt_id']]);
            $existing_item = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing_item) {
                $new_quantity = $existing_item['quantity'] + $data['quantity'];
                if ($new_quantity > $stock) {
                    throw new Exception('Insufficient stock for requested quantity', 400);
                }

                $update_query = "UPDATE cart_items SET quantity = ? 
                               WHERE cart_item_id = ?";
                $stmt = $this->db->prepare($update_query);
                $stmt->execute([$new_quantity, $existing_item['cart_item_id']]);
            } else {
                $insert_query = "INSERT INTO cart_items (customer_id, tshirt_id, quantity) 
                               VALUES (?, ?, ?)";
                $stmt = $this->db->prepare($insert_query);
                $stmt->execute([$this->customer_id, $data['tshirt_id'], $data['quantity']]);
            }

            http_response_code(200);
            echo json_encode(['message' => 'Item added to cart successfully']);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function updateCartItem() {
        try {
            $this->customer_id = $this->authenticateCustomer();
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['cart_item_id']) || !isset($data['quantity'])) {
                throw new Exception('Missing required fields', 400);
            }

            // Verify ownership and get tshirt_id
            $check_query = "SELECT t.tshirt_id, t.stock_quantity 
                          FROM cart_items ci
                          JOIN tshirts t ON ci.tshirt_id = t.tshirt_id
                          WHERE ci.cart_item_id = ? AND ci.customer_id = ?";
            $stmt = $this->db->prepare($check_query);
            $stmt->execute([$data['cart_item_id'], $this->customer_id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                throw new Exception('Cart item not found', 404);
            }

            if ($data['quantity'] > $item['stock_quantity']) {
                throw new Exception('Insufficient stock', 400);
            }

            if ($data['quantity'] <= 0) {
                $delete_query = "DELETE FROM cart_items 
                               WHERE cart_item_id = ? AND customer_id = ?";
                $stmt = $this->db->prepare($delete_query);
                $stmt->execute([$data['cart_item_id'], $this->customer_id]);
            } else {
                $update_query = "UPDATE cart_items SET quantity = ? 
                               WHERE cart_item_id = ? AND customer_id = ?";
                $stmt = $this->db->prepare($update_query);
                $stmt->execute([$data['quantity'], $data['cart_item_id'], $this->customer_id]);
            }

            http_response_code(200);
            echo json_encode(['message' => 'Cart updated successfully']);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function removeFromCart() {
        try {
            $this->customer_id = $this->authenticateCustomer();
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['cart_item_id'])) {
                throw new Exception('Cart item ID is required', 400);
            }

            $query = "DELETE FROM cart_items 
                     WHERE cart_item_id = ? AND customer_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$data['cart_item_id'], $this->customer_id]);

            if ($stmt->rowCount() === 0) {
                throw new Exception('Cart item not found', 404);
            }

            http_response_code(200);
            echo json_encode(['message' => 'Item removed from cart']);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function getOrderSummary() {
        try {
            $this->customer_id = $this->authenticateCustomer();
            
            $query = "SELECT ci.quantity,
                     t.price,
                     tm.name as team_name,
                     e.name as edition_name,
                     t.size,
                     (t.price * ci.quantity) as subtotal
                     FROM cart_items ci
                     JOIN tshirts t ON ci.tshirt_id = t.tshirt_id
                     JOIN teams tm ON t.team_id = tm.team_id
                     JOIN editions e ON t.edition_id = e.edition_id
                     WHERE ci.customer_id = ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$this->customer_id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $subtotal = array_sum(array_column($items, 'subtotal'));
            // You can add tax calculation, shipping costs, etc. here
            $total = $subtotal;
            
            http_response_code(200);
            echo json_encode([
                'items' => $items,
                'summary' => [
                    'subtotal' => $subtotal,
                    'total' => $total,
                    'item_count' => count($items)
                ]
            ]);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}

// Route handling
$cart = new CartController();
$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

switch ($request) {
    case '/cart':
        switch ($method) {
            case 'GET':
                $cart->getCartItems();
                break;
            case 'POST':
                $cart->addToCart();
                break;
            case 'PUT':
                $cart->updateCartItem();
                break;
            case 'DELETE':
                $cart->removeFromCart();
                break;
        }
        break;
        
    case '/cart/summary':
        if ($method === 'GET') {
            $cart->getOrderSummary();
        }
        break;
}
?>