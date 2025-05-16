<?php
require_once "db.php"; // Använd rätt databasanslutning

// Läs in produkt-id från URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo "Ogiltigt produkt-ID.";
    exit;
}

$id = $_GET['id'];
$stmt = $db->prepare("SELECT name, description, price, image_url FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    $productHtml = "<p>Produkten kunde inte hittas.</p>";
} else {
    $productHtml = <<<HTML
<article>
  <img src="{$product['image_url']}" alt="{$product['name']}" class="product-image-large">
  <h1>{$product['name']}</h1>
  <p class="price">{$product['price']} kr</p>
  <p class="description">{$product['description']}</p>
</article>
HTML;
}

// Läs in HTML-mall och ersätt placeholder
$template = file_get_contents("product.html");
$output = str_replace("{{product-detail}}", $productHtml, $template);

// Visa sidan
echo $output;
