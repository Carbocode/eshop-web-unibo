<?php
require '../../middleware/preflight.php';

// Controlla il metodo della richiesta
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Estrai i dati dal corpo della richiesta
    $data = json_decode(file_get_contents('php://input'), true);

    // Verifica che i campi richiesti siano presenti
    if (!isset($data['email']) || !isset($data['password']) || !isset($data['full_name']) || !isset($data['phone'])) {
        http_response_code(400);
        echo json_encode(["error" => "I campi email, password, full_name e phone sono obbligatori."]);
        exit;
    }

    // Estrai i campi dall'input
    $email = $data['email'];
    $password = $data['password'];
    $fullName = $data['full_name'];
    $phone = $data['phone'];
    $address = $data['address'];
    $city = $data['city'];
    $province = $data['province'];
    $zip = $data['zip'];
    $country = $data['country'];

    // Verifica se l'email è già registrata
    $stmt = $conn->prepare("SELECT COUNT(*) FROM customers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        http_response_code(409);
        echo json_encode(["error" => "L'email è già registrata."]);
        exit;
    }

    // Hash e salatura della password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Inserisci il nuovo utente nel database
    $stmt = $conn->prepare("
        INSERT INTO customers (email, password, full_name, phone, address, city, province, zip, country) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssssssss", $email, $hashedPassword, $fullName, $phone, $address, $city, $province, $zip, $country);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(["message" => "Account creato con successo."]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Errore durante la creazione dell'account: " . $stmt->error]);
    }

    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(["error" => "Metodo non consentito. Usa POST."]);
}

$conn->close();
