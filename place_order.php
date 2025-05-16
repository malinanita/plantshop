<?php
session_start();
require 'db.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Måste vara inloggad
if (!isset($_SESSION['user_id'])) {
    header("Location: checkout.php?error=login");
    exit;
}

// Läs POST-data (vanligt formulär)
$name = $_POST['name'] ?? '';
$address = $_POST['address'] ?? '';
$email = $_POST['email'] ?? '';

// Validering
if (!$name || !$address || !$email) {
    header("Location: checkout.php?error=missing");
    exit;
}

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    header("Location: checkout.php?error=empty");
    exit;
}

try {
    $total = 0;
    foreach ($cart as $item) {
        if (!isset($item['price'], $item['quantity'])) {
            throw new Exception("Ogiltiga värden i kundvagnen.");
        }
        $total += $item['price'] * $item['quantity'];
    }

    $db->beginTransaction();

    // Spara order
    $stmt = $db->prepare("INSERT INTO orders (user_id, customer_name, email, address, total_price, status, created_at) 
                          VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([
        $_SESSION['user_id'],
        $name,
        $email,
        $address,
        $total,
        'bekräftad'
    ]);
    $order_id = $db->lastInsertId();

    // Spara varje produkt
    $itemStmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) 
                              VALUES (?, ?, ?, ?)");
    foreach ($cart as $item) {
        $itemStmt->execute([
            $order_id,
            $item['id'],
            $item['quantity'],
            $item['price']
        ]);
    }

    $db->commit();
    $_SESSION['cart'] = [];

    // SKICKA MEJL
    try {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'malinanitae@gmail.com';
        $mail->Password   = 'itiypexdxqkaqmbk';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('malinanitae@gmail.com', 'Elm Växtbutik');
        $mail->addAddress($email, $name);

        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);
        $mail->Subject = 'Tack för din beställning hos Elm 🌿';
        $mail->Body    = "
            <h2>Hej $name! 💛🌿</h2>
            <p>Tack för att du beställde från Elm – din grönaste växtbutik på nätet!</p>
            <p>Vi har mottagit din order den " . date("Y-m-d H:i") . ".</p>
            <p><strong>Totalt:</strong> $total kr</p>
            <p>Vi uppdaterar dig när din order har skickats!</p>
            <br>
            <p>Varma hälsningar,<br><strong>Elm-teamet</strong></p>
        ";
        $mail->AltBody = "Hej $name!\n\nTack för din beställning på $total kr.\nVi har mottagit din order den " . date("Y-m-d H:i") . ".\n\n/ Elm-teamet";

        $mail->send();
    } catch (Exception $mailErr) {
        error_log("E-postfel: " . $mailErr->getMessage());
    }

    // Redirect till profil med lyckad beställning
    header("Location: profile.php?success=order");
    exit;

} catch (Exception $e) {
    if ($db && $db->inTransaction()) {
        $db->rollBack();
    }
    header("Location: checkout.php?error=server");
    exit;
}
