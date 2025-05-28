<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "db.php";

$errorMessage = "";
$profileContent = "";
$orderModal = "";

// Om användaren inte är inloggad
if (!isset($_SESSION['user_id'])) {
    if (isset($_GET['error']) && $_GET['error'] === "invalid") {
        $errorMessage = "<p class='error-msg'>Fel e-post eller lösenord. Försök igen.</p>";
    }

    $profileContent = <<<HTML
        <section id="user-panel">
            $errorMessage
            <form method="POST" action="login.php">
                <input type="email" name="email" placeholder="E-post" required>
                <input type="password" name="password" placeholder="Lösenord" required>
                <button type="submit">Logga in</button>
                <p>Har du inget konto? <a href="register.php">Registrera dig här</a>.</p>
            </form>
        </section>
    HTML;

} else {
    $userId = $_SESSION['user_id'];
    $userName = htmlspecialchars($_SESSION['name'] ?? $_SESSION['email']);

    $stmt = $db->prepare("SELECT id, created_at, total_price FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $orderList = "";
    if (empty($orders)) {
        $orderList = "<li>Du har inga tidigare ordrar.</li>";
    } else {
        foreach ($orders as $order) {
            $orderId = $order['id'];
            $date = (new DateTime($order['created_at']))->format("Y-m-d");
            $orderList .= "<li><a href='profile.php?order_id=$orderId'>🧾 Order #$orderId – $date – {$order['total_price']} kr</a></li>";
        }
    }

    $successMessage = "";
    if (isset($_GET['success']) && $_GET['success'] === 'order') {
        $successMessage = "<p class='success-msg'>Tack för din beställning! 🌿<br>En orderbekräftelse har skickats till din mail.</p>";
    }

    $profileContent = <<<HTML
        <section>
            $successMessage
            <section class="profile-header">
                <p>Välkommen, $userName!</p>
                <a href="logout.php" class="logout-btn">Logga ut</a>
            </section>
        </section>

        <section>
            <h2>Mina tidigare ordrar</h2>
            <ul class="order-list">
                $orderList
            </ul>
        </section>
    HTML;

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
                $itemList .= "- {$item['name']}, {$item['quantity']} st á {$item['price_at_purchase']} kr<br>";
            }

            $orderDate = (new DateTime($order['created_at']))->format("Y-m-d");

            $orderModal = <<<HTML
                <section class="modal">
                    <article class="modal-content">
                        <a href="profile.php" class="close-btn">&#10005;</a>
                        <h3>Orderdetaljer</h3>
                        <p><strong>Order #: </strong>{$order['id']}</p>
                        <p><strong>Datum: </strong>{$orderDate}</p>
                        <p><strong>Totalt: </strong>{$order['total_price']} kr</p>
                        <p><strong>Produkter:</strong><br>$itemList</p>
                    </article>
                </section>
            HTML;
        }
    }
}

$template = file_get_contents("profile.html");
$template = str_replace("{{profile-content}}", $profileContent, $template);
$template = str_replace("{{order-modal}}", $orderModal, $template);

echo $template;
