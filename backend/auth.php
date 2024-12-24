<?php

require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController {
    private $db;
    private $jwt_secret;

    public function __construct() {
        $this->db = new PDO("mysql:host=localhost;dbname=soccer_tshirt_shop", "root", "your_password");
        $this->jwt_secret = getenv('JWT_SECRET');
    }

    public function registerCustomer() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $stmt = $this->db->prepare("SELECT email FROM customers WHERE email = ?");
        $stmt->execute([$data['email']]);
        
        if ($stmt->rowCount() > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Email already registered']);
            return;
        }
        
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("
            INSERT INTO customers (email, password_hash, first_name, last_name, phone) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        try {
            $stmt->execute([
                $data['email'],
                $passwordHash,
                $data['firstName'],
                $data['lastName'],
                $data['phone']
            ]);
            
            http_response_code(201);
            echo json_encode(['message' => 'Registration successful']);
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Registration failed']);
        }
    }

    public function loginCustomer() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $stmt = $this->db->prepare("
            SELECT customer_id, email, password_hash 
            FROM customers 
            WHERE email = ?
        ");
        $stmt->execute([$data['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !password_verify($data['password'], $user['password_hash'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }
        
        $token = JWT::encode([
            'userId' => $user['customer_id'],
            'type' => 'customer',
            'exp' => time() + (24 * 60 * 60)
        ], $this->jwt_secret, 'HS256');
        
        echo json_encode(['token' => $token]);
    }

    public function registerAdmin() {
        if (!$this->isSuperAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Insufficient privileges']);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $stmt = $this->db->prepare("SELECT email FROM admins WHERE email = ?");
        $stmt->execute([$data['email']]);
        
        if ($stmt->rowCount() > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Email already registered']);
            return;
        }
        
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("
            INSERT INTO admins (email, password_hash, first_name, last_name, role) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        try {
            $stmt->execute([
                $data['email'],
                $passwordHash,
                $data['firstName'],
                $data['lastName'],
                $data['role']
            ]);
            
            http_response_code(201);
            echo json_encode(['message' => 'Admin registration successful']);
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Registration failed']);
        }
    }

    public function loginAdmin() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $stmt = $this->db->prepare("
            SELECT admin_id, email, password_hash, role 
            FROM admins 
            WHERE email = ?
        ");
        $stmt->execute([$data['email']]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$admin || !password_verify($data['password'], $admin['password_hash'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }
        
        $token = JWT::encode([
            'userId' => $admin['admin_id'],
            'type' => 'admin',
            'role' => $admin['role'],
            'exp' => time() + (8 * 60 * 60)
        ], $this->jwt_secret, 'HS256');
        
        echo json_encode(['token' => $token]);
    }

    private function isSuperAdmin() {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            return false;
        }
        
        try {
            $token = explode(' ', $headers['Authorization'])[1];
            $decoded = JWT::decode($token, new Key($this->jwt_secret, 'HS256'));
            return $decoded->type === 'admin' && $decoded->role === 'super_admin';
        } catch(Exception $e) {
            return false;
        }
    }
}

// Routes
$auth = new AuthController();
$request = $_SERVER['REQUEST_URI'];

switch ($request) {
    case '/register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth->registerCustomer();
        }
        break;
        
    case '/login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth->loginCustomer();
        }
        break;
        
    case '/admin/register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth->registerAdmin();
        }
        break;
        
    case '/admin/login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth->loginAdmin();
        }
        break;
}
?>