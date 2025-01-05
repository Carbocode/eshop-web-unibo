<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth {
    private static $jwt_secret;

    public static function init() {
        self::$jwt_secret = $_ENV['JWT_SECRET'];
    }

    public static function authenticateUser($requiredType = null) {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            throw new Exception('No authorization token provided', 401);
        }

        try {
            $token = explode(' ', $headers['Authorization'])[1];
            $decoded = JWT::decode($token, new Key(self::$jwt_secret, 'HS256'));

            if ($requiredType && $decoded->type !== $requiredType) {
                throw new Exception("Invalid user type. Required: {$requiredType}", 401);
            }

            return $decoded;
        } catch (Exception $e) {
            throw new Exception('Invalid token', 401);
        }
    }

    public static function generateToken($userId, $type, $role = null, $expHours = 24) {
        $payload = [
            'userId' => $userId,
            'type' => $type,
            'exp' => time() + ($expHours * 3600)
        ];

        if ($role) {
            $payload['role'] = $role;
        }

        return JWT::encode($payload, self::$jwt_secret, 'HS256');
    }

    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public static function isSuperAdmin($token = null) {
        try {
            if (!$token) {
                $headers = getallheaders();
                if (!isset($headers['Authorization'])) {
                    return false;
                }
                $token = explode(' ', $headers['Authorization'])[1];
            }
            
            $decoded = JWT::decode($token, new Key(self::$jwt_secret, 'HS256'));
            return $decoded->type === 'admin' && $decoded->role === 'super_admin';
        } catch (Exception $e) {
            return false;
        }
    }
}