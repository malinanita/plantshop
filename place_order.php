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

// Läs POST-data
$name = $_POST['name'] ?? '';
$address = $_POST['address'] ?? '';
$email = $_POST['email'] ?? '';

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

        // Ladda e-postmallar
        $orderDate = date("Y-m-d H:i");
        $replacements = [
            '{{name}}' => $name,
            '{{order_date}}' => $orderDate,
            '{{total}}' => $total
        ];

        // HTML-body
        $emailTemplate = file_get_contents("templates/email_receipt.html");
        $emailHtml = str_replace(array_keys($replacements), array_values($replacements), $emailTemplate);

        // Alt-text
        $altTemplate = file_get_contents("templates/email_receipt.txt");
        $altText = str_replace(array_keys($replacements), array_values($replacements), $altTemplate);

        $mail->isHTML(true);
        $mail->Subject = 'Tack för din beställning hos Elm 🌿';
        $mail->Body    = $emailHtml;
        $mail->AltBody = $altText;

        $mail->send();
    } catch (Exception $mailErr) {
        error_log("E-postfel: " . $mailErr->getMessage());
    }

    header("Location: profile.php?success=order");
    exit;

} catch (Exception $e) {
    if ($db && $db->inTransaction()) {
        $db->rollBack();
    }
    header("Location: checkout.php?error=server");
    exit;
}
