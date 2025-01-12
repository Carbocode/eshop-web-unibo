<?php

require '../../../vendor/autoload.php';
require '../../middleware/auth.php';
require '../../middleware/preflight.php';

// Recupera il nome completo del cliente prima di anonimizzarlo
$sql = "SELECT full_name FROM customers WHERE customer_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => 'Failed to prepare database query']);
    exit;
}

$stmt->bind_param('i', $_TOKEN['sub']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Customer not found']);
    exit;
}

$customer = $result->fetch_assoc();
$full_name = $customer['full_name'];

$stmt->close();

// Pulisci i dati personali dell'utente
$sql = "UPDATE customers 
        SET email = '', 
            full_name = '', 
            phone = '', 
            address = '', 
            city = '', 
            province = '', 
            zip = '', 
            country = ''
        WHERE customer_id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => 'Failed to prepare database query']);
    exit;
}

$stmt->bind_param('i', $_TOKEN['sub']);

// Esegui la query
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        // Invia la mail di conferma utilizzando mail() di PHP
        $to = $_TOKEN['email'];
        $subject = "Account Anonymization Confirmation";
        $message = "
            <html>
            <head>
                <title>Account Anonymization Confirmation</title>
            </head>
            <body>
                <p>Ciao $full_name,</p>
                <p>Il tuo account è stato anonimizzato correttamente. Tutti i dati personali sono stati rimossi dai nostri sistemi.</p>
                <p>Grazie per aver usato i nostri servizi.</p>
            </body>
            </html>
        ";
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: no-reply@example.com" . "\r\n";

        // Invio dell'email
        if (mail($to, $subject, $message, $headers)) {
            echo json_encode(['success' => 'Customer data anonymized and confirmation email sent successfully']);
        } else {
            echo json_encode(['success' => 'Customer data anonymized, but failed to send confirmation email']);
        }
    } else {
        echo json_encode(['error' => 'Customer not found or already anonymized']);
    }
} else {
    echo json_encode(['error' => 'Failed to anonymize customer data']);
}

// Chiudi la connessione
$stmt->close();
$conn->close();
