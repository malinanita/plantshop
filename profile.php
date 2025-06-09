<?php
// profile.php (9 - Gesällprov)
// Visar användarens profilsida med eventuell orderhistorik och ev. orderdetaljer i en modal
// Om användaren inte är inloggad visas en inloggningsprompt

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); // Visa alla fel (för utveckling)

session_start();
require_once "db.php";

// Förbered variabler
$profileContent = "";
$orderModal = "";

// Om användaren inte är inloggad
if (!isset($_SESSION['user_id'])) {
    $loginError = "";

    // Visa ev. felmeddelande vid otillåten åtkomst
    if (isset($_GET['error']) && $_GET['error'] === "invalid") {
        $loginError = file_get_contents("templates/profile_login_error.html");
    }

    // Ladda mall för ej inloggad användare och ersätt plats för felmeddelande
    $notLoggedInTemplate = file_get_contents("templates/profile_not_logged_in.html");
    $profileContent = str_replace("{{login-error}}", $loginError, $notLoggedInTemplate);

} else {
    // Om användaren är inloggad
    $userId = $_SESSION['user_id'];
    $userName = htmlspecialchars($_SESSION['name'] ?? $_SESSION['email']);

    // Hämta orderhistorik från databasen
    $stmt = $db->prepare("SELECT id, created_at, total_price FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Bygg upp orderlistan eller visa meddelande om inga ordrar finns
    if (empty($orders)) {
        $orderList = file_get_contents("templates/profile_order_empty.html");
    } else {
        $orderList = "";
        foreach ($orders as $order) {
            $orderItemTemplate = file_get_contents("templates/profile_order_item.html");
            $orderList .= str_replace(
                ['{{order_id}}', '{{date}}', '{{total}}'],
                [$order['id'], (new DateTime($order['created_at']))->format("Y-m-d"), $order['total_price']],
                $orderItemTemplate
            );
        }
    }

    // Visa bekräftelsemeddelande om en order har genomförts
    $successMessage = "";
    if (isset($_GET['success']) && $_GET['success'] === 'order') {
        $successMessage = file_get_contents("templates/profile_order_success.html");
    }

    // Sätt samman profilsidans innehåll
    $loggedInTemplate = file_get_contents("templates/profile_logged_in.html");
    $profileContent = str_replace(
        ['{{user}}', '{{order-success}}', '{{order-list}}'],
        [$userName, $successMessage, $orderList],
        $loggedInTemplate
    );

    // Om en specifik order efterfrågas – visa ordermodal med detaljer
    if (isset($_GET['order_id']) && is_numeric($_GET['order_id'])) {
        $orderId = $_GET['order_id'];
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$orderId, $userId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            // Hämta alla produkter i ordern
            $stmt = $db->prepare("SELECT oi.product_id, oi.quantity, oi.price_at_purchase, p.name 
                                  FROM order_items oi 
                                  JOIN products p ON oi.product_id = p.id 
                                  WHERE oi.order_id = ?");
            $stmt->execute([$orderId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Bygg upp listan över produkter i textform
            $itemList = "";
            foreach ($items as $item) {
                $itemList .= "- {$item['name']}, {$item['quantity']} st á {$item['price_at_purchase']} kr \n";
            }

            // Formatordern datum
            $orderDate = (new DateTime($order['created_at']))->format("Y-m-d");

            // Fyll i och visa ordermodalsmallen
            $modalTemplate = file_get_contents("templates/profile_order_modal.html");
            $orderModal = str_replace(
                ['{{order_id}}', '{{order_date}}', '{{total_price}}', '{{item_list}}'],
                [$order['id'], $orderDate, $order['total_price'], $itemList],
                $modalTemplate
            );
        }
    }
}

// Ladda profilsidans huvudmall och ersätt innehåll
$template = file_get_contents("profile.html");
$template = str_replace("{{profile-content}}", $profileContent, $template);
$template = str_replace("{{order-modal}}", $orderModal, $template);

// Visa sidan
echo $template;
