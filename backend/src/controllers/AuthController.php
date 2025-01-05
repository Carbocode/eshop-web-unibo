<?php
namespace App\Controllers;
use App\Utils\ApiResponse;
use App\Middleware\Auth;
use Exception;
class AuthController extends BaseController {
    public function registerCustomer() {
        $data = ApiResponse::getRequestData();
        ApiResponse::validateRequest(['email', 'password', 'firstName', 'lastName', 'phone'], $data);
        
        // Check if email exists
        $exists = $this->fetchColumn(
            "SELECT COUNT(*) FROM customers WHERE email = ?",
            [$data['email']]
        );
        
        if ($exists) {
            ApiResponse::error('Email already registered', 400);
        }
        
        try {
            $passwordHash = Auth::hashPassword($data['password']);
            
            $this->executeQuery(
                "INSERT INTO customers (email, password_hash, first_name, last_name, phone) 
                VALUES (?, ?, ?, ?, ?)",
                [
                    $data['email'],
                    $passwordHash,
                    $data['firstName'],
                    $data['lastName'],
                    $data['phone']
                ]
            );
            
            ApiResponse::success(null, 'Registration successful', 201);
        } catch (Exception $e) {
            ApiResponse::error('Registration failed', 500);
        }
    }

    public function loginCustomer() {
        $data = ApiResponse::getRequestData();
        ApiResponse::validateRequest(['email', 'password'], $data);
        
        $user = $this->fetch(
            "SELECT customer_id, email, password_hash 
            FROM customers 
            WHERE email = ?",
            [$data['email']]
        );
        
        if (!$user || !Auth::verifyPassword($data['password'], $user['password_hash'])) {
            ApiResponse::error('Invalid credentials', 401);
        }
        
        $token = Auth::generateToken($user['customer_id'], 'customer');
        ApiResponse::success(['token' => $token]);
    }

    public function registerAdmin() {
        if (!Auth::isSuperAdmin()) {
            ApiResponse::error('Insufficient privileges', 403);
        }
        
        $data = ApiResponse::getRequestData();
        ApiResponse::validateRequest(['email', 'password', 'firstName', 'lastName', 'role'], $data);
        
        $exists = $this->fetchColumn(
            "SELECT COUNT(*) FROM admins WHERE email = ?",
            [$data['email']]
        );
        
        if ($exists) {
            ApiResponse::error('Email already registered', 400);
        }
        
        try {
            $passwordHash = Auth::hashPassword($data['password']);
            
            $this->executeQuery(
                "INSERT INTO admins (email, password_hash, first_name, last_name, role) 
                VALUES (?, ?, ?, ?, ?)",
                [
                    $data['email'],
                    $passwordHash,
                    $data['firstName'],
                    $data['lastName'],
                    $data['role']
                ]
            );
            
            ApiResponse::success(null, 'Admin registration successful', 201);
        } catch (Exception $e) {
            ApiResponse::error('Registration failed', 500);
        }
    }

    public function loginAdmin() {
        $data = ApiResponse::getRequestData();
        ApiResponse::validateRequest(['email', 'password'], $data);
        
        $admin = $this->fetch(
            "SELECT admin_id, email, password_hash, role 
            FROM admins 
            WHERE email = ?",
            [$data['email']]
        );
        
        if (!$admin || !Auth::verifyPassword($data['password'], $admin['password_hash'])) {
            ApiResponse::error('Invalid credentials', 401);
        }
        
        $token = Auth::generateToken($admin['admin_id'], 'admin', $admin['role'], 8);
        ApiResponse::success(['token' => $token]);
    }

    public function processRequest() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        $handlers = [
            '/auth/register' => [
                'POST' => [$this, 'registerCustomer']
            ],
            '/auth/login' => [
                'POST' => [$this, 'loginCustomer']
            ],
            '/auth/admin/register' => [
                'POST' => [$this, 'registerAdmin']
            ],
            '/auth/admin/login' => [
                'POST' => [$this, 'loginAdmin']
            ]
        ];

        if (!isset($handlers[$uri][$method])) {
            ApiResponse::error('Not found', 404);
        }

        $this->handleRequest($method, $handlers[$uri]);
    }
}