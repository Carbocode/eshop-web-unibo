<?php
// Include libreria JWT
require '../../../vendor/autoload.php';
require '../../config/middleware.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Chiave segreta per il JWT
$jwtSecret = 'tuasegretatokenkey';

// Recupera il token dal cookie
if (isset($_COOKIE['auth_token'])) {
    $jwt = $_COOKIE['auth_token'];

    try {
        // Decodifica il token
        $decoded = JWT::decode($jwt, new Key($jwtSecret, 'HS256'));

        // Verifica che il token non sia scaduto
        if ($decoded->exp < time()) {
            http_response_code(401);
            echo json_encode(["error" => "Token scaduto."]);
            exit;
        }

        // Recupera i dati del payload
        $customerId = $decoded->sub;
        $email = $decoded->email;

        // Controllo facoltativo: verifica che l'utente esista nel database
        $stmt = $conn->prepare("SELECT `customer_id` FROM customers WHERE customer_id = ? AND email = ?");
        $stmt->bind_param("is", $customerId, $email);
        $stmt->execute();
        $stmt->bind_result($dbCustomerId);

        if ($stmt->fetch()) {
            http_response_code(200);
            echo json_encode(["message" => "Token valido.", "customer_id" => $customerId, "email" => $email]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Utente non trovato."]);
        }

        $stmt->close();
    } catch (Exception $e) {
        // Gestione degli errori durante la decodifica del token
        http_response_code(401);
        echo json_encode(["error" => "Token non valido.", "details" => $e->getMessage()]);
        exit;
    }
} else {
    http_response_code(401);
    echo json_encode(["error" => "Token non presente."]);
    exit;
}

$conn->close();
