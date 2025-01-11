<?php

// Imposta gli header per evitare problemi di CORS
header('Access-Control-Allow-Origin: *'); // Permette l'accesso da qualsiasi dominio
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS'); // Permette solo POST e OPTIONS
header('Access-Control-Allow-Headers: Content-Type, Authorization'); // Permette gli header specifici
header('Content-Type: application/json'); // Imposta il tipo di contenuto della risposta

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Configurazione database
$host = 'localhost:3306';
$dbname = 'elprimerofootballer';
$username = 'root';
$password = '';

// Connessione al database
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Errore di connessione al database: " . $conn->connect_error]);
    exit;
}

/**
 * Restituisce l'output delle Eccezioni non gestite
 *
 * @param \Throwable $e Eccezione richiamata
 *
 * @return void
 */
function log_exception(\Throwable $e)
{

    $message = [
        'timestamp' => gmdate("Y-m-d\TH:i:s\Z"),
        'message'   => $e->getMessage(),
        'body'      => [
            'type' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ],
    ];
    http_response_code(500);

    echo (json_encode($message));

    die();
}

/**
 * Trasforma un errore in una eccezione da passare alla funzione di logging
 * @param  int $num gravità dell'errore
 * @param  string $str messaggio d'errore
 * @param  string $file File di origine dell'errore
 * @param  int $line Linea d'errore
 *
 * @return bool
 */
function log_error($num, $str, $file, $line)
{
    log_exception(new \ErrorException($str, INTERNAL_SERVER_ERROR, $num, $file, $line));

    return true;
}

/**
 * Trasforma un errore FATALE in una eccezione da passare alla funzione di logging
 *
 * @return void
 */
function check_for_fatal()
{
    $error = error_get_last();
    if ($error != null) {
        if ($error['type'] == E_ERROR) {
            log_exception(new \ErrorException($error['message'], INTERNAL_SERVER_ERROR, $error['type'], $error['file'], $error['line']));
        }
    }
}

//Imposto le mie funzioni per mostrare gli errori
set_exception_handler('log_exception');
set_error_handler('log_error');
register_shutdown_function('check_for_fatal');

ini_set('display_errors', 'off');
error_reporting(E_ALL);
