<?php

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
            header('Location: /src/pages/login/');
            exit;
        }
    } catch (Exception $e) {
        // Gestione degli errori durante la decodifica del token
        header('Location: /src/pages/login/');
        exit;
    }
} else {
    header('Location: /src/pages/login/');
    exit;
}
