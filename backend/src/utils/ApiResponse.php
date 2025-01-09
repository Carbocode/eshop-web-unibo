<?php

namespace App\Utils;

/**
 * ApiResponse provides utility methods for handling API responses.
 * Standardizes JSON response format and includes methods for
 * request validation, data parsing, and CORS header management.
 */
class ApiResponse {
    /**
     * Sends a success response with optional data and message.
     * 
     * @param mixed|null $data The data to include in the response
     * @param string|null $message Optional success message
     * @param int $code HTTP status code (default: 200)
     * @return void
     */
    public static function success($data = null, $message = null, $code = 200) {
        http_response_code($code);
        echo json_encode([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }

    /**
     * Sends an error response with message and optional error details.
     * 
     * @param string $message Error message
     * @param int $code HTTP status code (default: 500)
     * @param mixed|null $errors Optional additional error details
     * @return void
     */
    public static function error($message, $code = 500, $errors = null) {
        http_response_code($code);
        echo json_encode([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors
        ]);
        exit;
    }

    /**
     * Validates request data against required fields.
     * If any required fields are missing, sends an error response.
     * 
     * @param array $requiredFields Array of field names that must be present
     * @param array $data Request data to validate
     * @return void
     */
    public static function validateRequest($requiredFields, $data) {
        $missing = [];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            self::error(
                'Missing required fields',
                400,
                ['missing_fields' => $missing]
            );
        }
    }

    /**
     * Parses and returns JSON request data from the request body.
     * If JSON is invalid, sends an error response.
     * 
     * @throws void Sends error response if JSON is invalid
     * @return array Parsed JSON data as associative array
     */
    public static function getRequestData() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            self::error('Invalid JSON data', 400);
        }
        return $data;
    }

    /**
     * Sets CORS headers for cross-origin requests.
     * Handles preflight OPTIONS requests automatically.
     * 
     * @return void
     */
    public static function setCorsHeaders() {
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Credentials: true");
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
            header("Access-Control-Allow-Headers: Content-Type, Authorization");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit(0);
        }
    }
}