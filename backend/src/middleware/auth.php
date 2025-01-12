<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Chiave segreta per il JWT
$jwtSecret = 'tuasegretatokenkey';

$headers = getallheaders();

// Recupera il token dal cookie
if (isset($headers['Authorization'])) {
    $authHeader = $headers['Authorization'];
    $jwt = str_replace('Bearer ', '', $authHeader);

    try {
        // Decodifica il token
        $decoded = JWT::decode($jwt, new Key($jwtSecret, 'HS256'));

        // Verifica che il token non sia scaduto
        if ($decoded->exp < time()) {
            die("Token scaduto");
        }

        // Se il token è valido, imposta la variabile $_TOKEN con il payload del token
        $_TOKEN = [
            'sub' => $decoded->sub,
            'email' => $decoded->email,
            'iat' => $decoded->iat,
            'exp' => $decoded->exp
        ];
    } catch (Exception $e) {
        // Gestione degli errori durante la decodifica del token
        die("Token non valido");
    }
} else {
    die("Token non presente");
}
