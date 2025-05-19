<?php
require_once "db.php"; // Använd rätt databasanslutning

// Läs in produkt-id från URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo "Ogiltigt produkt-ID.";
    exit;
}

$id = $_GET['id'];
$stmt = $db->prepare("SELECT id, name, description, price, image_url FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    $productHtml = "<p>Produkten kunde inte hittas.</p>";
} else {
    // Skydda mot specialtecken i HTML och JavaScript
    $safeId = (int)$product['id'];
    $safeName = htmlspecialchars($product['name'], ENT_QUOTES);
    $safeDescription = htmlspecialchars($product['description'], ENT_QUOTES);
    $safeImage = htmlspecialchars($product['image_url'], ENT_QUOTES);
    $safePrice = (float)$product['price'];

    $productHtml = <<<HTML
<article>
  <img src="{$safeImage}" alt="{$safeName}" class="product-image-large">
  <h1>{$safeName}</h1>
  <p class="price">{$safePrice} kr</p>
  <p class="description">{$safeDescription}</p>
  <button class="btn" onclick="addToCart({$safeId}, '{$safeName}', '{$safeImage}', {$safePrice})">Lägg i kundvagn</button>
</article>
HTML;
}

// Läs in HTML-mall och ersätt placeholder
$template = file_get_contents("product.html");
$output = str_replace("{{product-detail}}", $productHtml, $template);

// Visa sidan
echo $output;
