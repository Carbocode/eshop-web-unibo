<?php
namespace App\Middleware;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use \Exception;

/**
 * Auth class handles all authentication and authorization related functionality.
 * Provides methods for JWT token management, password hashing, and user verification.
 */
class Auth {
    /** @var string The JWT secret key used for token signing and verification */
    private static $jwt_secret;

    /**
     * Initializes the Auth class with JWT secret from environment variables.
     * Must be called before using any other methods.
     * 
     * @return void
     */
    public static function init() {
        self::$jwt_secret = $_ENV['JWT_SECRET'];
    }

    /**
     * Authenticates a user based on JWT token in Authorization header.
     * Optionally verifies the user type matches the required type.
     * 
     * @param string|null $requiredType Optional user type to verify against
     * @throws Exception When no token provided, token is invalid, or user type doesn't match
     * @return object Decoded JWT token payload containing user information
     */
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

    /**
     * Generates a JWT token for a user.
     * 
     * @param int $userId The user's ID
     * @param string $type The user's type (e.g., 'customer', 'admin')
     * @param string|null $role Optional role for the user
     * @param int $expHours Token expiration time in hours (default: 24)
     * @return string The generated JWT token
     */
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

    /**
     * Hashes a password using PHP's password_hash function.
     * 
     * @param string $password The plain text password to hash
     * @return string The hashed password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verifies a password against its hash.
     * 
     * @param string $password The plain text password to verify
     * @param string $hash The hashed password to verify against
     * @return bool True if password matches, false otherwise
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Checks if the current user is a super admin.
     * Can either use the current request's Authorization header or a provided token.
     * 
     * @param string|null $token Optional JWT token to check
     * @return bool True if user is super admin, false otherwise
     */
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