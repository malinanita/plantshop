<?php
// place_order.php (9 - Gesällprov)
// Hanterar beställning: validerar data, sparar order i databasen och skickar bekräftelsemail

session_start();
require 'db.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Kontrollera att användaren är inloggad
if (!isset($_SESSION['user_id'])) {
    header("Location: checkout.php?error=login");
    exit;
}

// Hämta data från beställningsformuläret
$name = $_POST['name'] ?? '';
$address = $_POST['address'] ?? '';
$email = $_POST['email'] ?? '';

// Kontrollera att alla fält är ifyllda
if (!$name || !$address || !$email) {
    header("Location: checkout.php?error=missing");
    exit;
}

// Kontrollera att kundvagnen inte är tom
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    header("Location: checkout.php?error=empty");
    exit;
}

try {
    // Beräkna totalsumma
    $total = 0;
    foreach ($cart as $item) {
        if (!isset($item['price'], $item['quantity'])) {
            throw new Exception("Ogiltiga värden i kundvagnen.");
        }
        $total += $item['price'] * $item['quantity'];
    }

    // Starta transaktion
    $db->beginTransaction();

    // Spara ordern i databasen
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

    // Spara varje produkt i ordern
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

    $db->commit(); // Bekräfta transaktionen
    $_SESSION['cart'] = []; // Töm kundvagnen

    // Skicka bekräftelsemail
    try {
        $mail = new PHPMailer(true);

        // Ställ in SMTP-inställningar
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'malinanitae@gmail.com';
        $mail->Password   = 'itiypexdxqkaqmbk'; // OBS! Byt ut vid publicering
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Sätt avsändare och mottagare
        $mail->setFrom('malinanitae@gmail.com', 'Elm Växtbutik');
        $mail->addAddress($email, $name);
        $mail->CharSet = 'UTF-8';

        // Ladda e-postmallar (HTML och alt-text)
        $orderDate = date("Y-m-d H:i");
        $replacements = [
            '{{name}}' => $name,
            '{{order_date}}' => $orderDate,
            '{{total}}' => $total
        ];

        $emailTemplate = file_get_contents("templates/email_receipt.html");
        $emailHtml = str_replace(array_keys($replacements), array_values($replacements), $emailTemplate);

        $altTemplate = file_get_contents("templates/email_receipt.txt");
        $altText = str_replace(array_keys($replacements), array_values($replacements), $altTemplate);

        // Skicka e-post
        $mail->isHTML(true);
        $mail->Subject = 'Tack för din beställning hos Elm 🌿';
        $mail->Body    = $emailHtml;
        $mail->AltBody = $altText;

        $mail->send();
    } catch (Exception $mailErr) {
        // Logga eventuella mailfel, men avbryt inte processen
        error_log("E-postfel: " . $mailErr->getMessage());
    }

    // Skicka användaren till profilsidan med lyckad beställning
    header("Location: profile.php?success=order");
    exit;

} catch (Exception $e) {
    // Återställ transaktionen vid fel
    if ($db && $db->inTransaction()) {
        $db->rollBack();
    }

    // Skicka användaren tillbaka till kassan med felmeddelande
    header("Location: checkout.php?error=server");
    exit;
}
