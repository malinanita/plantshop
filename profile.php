<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    $profileContent = <<<HTML
<section id="user-panel">
  <p>Du är inte inloggad.</p>
  <form method="POST" action="login.php">
    <input type="email" name="email" placeholder="E-post" required>
    <input type="password" name="password" placeholder="Lösenord" required>
    <button type="submit">Logga in</button>
  </form>
</section>
HTML;

    $orderModal = "";

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
        $successMessage = "<p class='success-msg'>Tack för din beställning! 🌿 </br> En orderbekräftelse har skickats till din mail. </p>";
    }

    $profileContent = <<<HTML
    <section>
      $successMessage
      <p>Välkommen, $userName!</p>
      <a href="logout.php" class="logout-btn">Logga ut</a>
    </section>


<section>
  <h2>Mina tidigare ordrar</h2>
  <ul>
    $orderList
  </ul>
</section>
HTML;

    $orderModal = "";
    if (isset($_GET['order_id']) && is_numeric($_GET['order_id'])) {
        $orderId = $_GET['order_id'];
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$orderId, $userId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            $stmt = $db->prepare("SELECT name, quantity, price_at_purchase FROM order_items WHERE order_id = ?");
            $stmt->execute([$orderId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $itemList = "";
            foreach ($items as $item) {
                $itemList .= "- {$item['name']}, {$item['quantity']} st á {$item['price_at_purchase']} kr<br>";
            }

            $orderDate = (new DateTime($order['created_at']))->format("Y-m-d");

            $orderModal = <<<HTML
<section class="modal">
  <div class="modal-content">
    <a href="profile.php" class="close-btn">❌</a>
    <h3>Orderdetaljer</h3>
    <p><strong>Order #: </strong>{$order['id']}</p>
    <p><strong>Datum: </strong>{$orderDate}</p>
    <p><strong>Totalt: </strong>{$order['total_price']} kr</p>
    <p><strong>Produkter:</strong><br>$itemList</p>
  </div>
</section>
HTML;
        }
    }
}

// Visa felmeddelande om inloggningen misslyckades
$errorMessage = "";
if (isset($_GET['error']) && $_GET['error'] === "invalid") {
    $errorMessage = "<p class='error-msg'>Fel e-post eller lösenord. Försök igen.</p>";
}

// Lägg in felmeddelandet i formuläret om det finns
if (!isset($_SESSION['user_id'])) {
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
    $orderModal = "";
}

// Ladda HTML-mall
$template = file_get_contents("profile.html");

// Ersätt placeholders
$template = str_replace("{{profile-content}}", $profileContent, $template);
$template = str_replace("{{order-modal}}", $orderModal, $template);

echo $template;
