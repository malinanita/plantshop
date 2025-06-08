<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "db.php";

$profileContent = "";
$orderModal = "";

if (!isset($_SESSION['user_id'])) {
    $loginError = "";

    if (isset($_GET['error']) && $_GET['error'] === "invalid") {
        $loginError = file_get_contents("templates/profile_login_error.html");
    }

    $notLoggedInTemplate = file_get_contents("templates/profile_not_logged_in.html");
    $profileContent = str_replace("{{login-error}}", $loginError, $notLoggedInTemplate);

} else {
    $userId = $_SESSION['user_id'];
    $userName = htmlspecialchars($_SESSION['name'] ?? $_SESSION['email']);

    $stmt = $db->prepare("SELECT id, created_at, total_price FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Orderlista
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

    // Lyckad beställning
    $successMessage = "";
    if (isset($_GET['success']) && $_GET['success'] === 'order') {
        $successMessage = file_get_contents("templates/profile_order_success.html");
    }

    // Bygg profilsektionen
    $loggedInTemplate = file_get_contents("templates/profile_logged_in.html");
    $profileContent = str_replace(
        ['{{user}}', '{{order-success}}', '{{order-list}}'],
        [$userName, $successMessage, $orderList],
        $loggedInTemplate
    );

    // Visa ordermodal om ?order_id finns
    if (isset($_GET['order_id']) && is_numeric($_GET['order_id'])) {
        $orderId = $_GET['order_id'];
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$orderId, $userId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            $stmt = $db->prepare("SELECT oi.product_id, oi.quantity, oi.price_at_purchase, p.name 
                                  FROM order_items oi 
                                  JOIN products p ON oi.product_id = p.id 
                                  WHERE oi.order_id = ?");
            $stmt->execute([$orderId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $itemList = "";
            foreach ($items as $item) {
                $itemList .= "- {$item['name']}, {$item['quantity']} st á {$item['price_at_purchase']} kr \n";
            }

            $orderDate = (new DateTime($order['created_at']))->format("Y-m-d");

            $modalTemplate = file_get_contents("templates/profile_order_modal.html");
            $orderModal = str_replace(
                ['{{order_id}}', '{{order_date}}', '{{total_price}}', '{{item_list}}'],
                [$order['id'], $orderDate, $order['total_price'], $itemList],
                $modalTemplate
            );
        }
    }
}

// Ladda sidmall
$template = file_get_contents("profile.html");
$template = str_replace("{{profile-content}}", $profileContent, $template);
$template = str_replace("{{order-modal}}", $orderModal, $template);

echo $template;
