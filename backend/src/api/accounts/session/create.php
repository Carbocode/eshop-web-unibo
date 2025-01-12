<?php
// Include libreria JWT
require '../../../../vendor/autoload.php';
require '../../../middleware/preflight.php';

use Firebase\JWT\JWT;

// Chiave segreta per il JWT
$jwtSecret = 'tuasegretatokenkey';

// Controlla il metodo della richiesta
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Estrai i dati dal corpo della richiesta
    $data = json_decode(file_get_contents('php://input'), true);

    // Verifica che i campi richiesti siano presenti
    if (!isset($data['email']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode(["error" => "I campi email e password sono obbligatori."]);
        exit;
    }

    $email = $data['email'];
    $password = $data['password'];

    // Recupera l'utente dal database
    $stmt = $conn->prepare("SELECT `customer_id`, `password` FROM customers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($customerId, $hashedPassword);

    if ($stmt->fetch()) {
        // Verifica la password
        if (password_verify($password, $hashedPassword)) {
            // Genera il token JWT
            $payload = [
                'sub' => $customerId,
                'email' => $email,
                'iat' => time(),
                'exp' => time() + (60 * 60) // 1 ora di validità
            ];

            $jwt = JWT::encode($payload, $jwtSecret, 'HS256');

            http_response_code(200);
            echo json_encode(["message" => "Login effettuato con successo.", "token" => $jwt]);
        } else {
            http_response_code(401);
            echo json_encode(["error" => "Credenziali non valide."]);
        }
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Utente non trovato."]);
    }

    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(["error" => "Metodo non consentito. Usa POST."]);
}

$conn->close();
