<?php
// product.php (9 - Gesällprov)
// Visar detaljerad information om en specifik produkt baserat på dess ID
// Om ID saknas eller produkten inte hittas visas felmeddelande

require_once "db.php";
session_start();

// Kontrollera att ett produkt-ID har skickats via GET
if (!isset($_GET['id'])) {
  header("Location: shop.php"); // Skicka tillbaka till butikssidan om inget ID anges
  exit;
}

// Hämta produktdata från databasen
$id = (int) $_GET['id']; // Typomvandla ID till heltal
$stmt = $db->prepare("SELECT name, description, price, image_url FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// Ladda huvudmallen
$template = file_get_contents("product.html");

// Om produkten inte hittas – visa felmeddelande
if (!$product) {
  $productHtml = file_get_contents("templates/product_not_found.html");
} else {
  // Om produkten finns – ladda och fyll i produktmallen
  $productTemplate = file_get_contents("templates/product_detail.html");
  $productHtml = str_replace(
    ['{{id}}', '{{name}}', '{{description}}', '{{price}}', '{{image_url}}'],
    [$id, $product['name'], $product['description'], $product['price'], $product['image_url']],
    $productTemplate
  );
}

// Ersätt platsmarkör i huvudmallen med det genererade produktinnehållet
$template = str_replace("{{product-detail}}", $productHtml, $template);

// Skriv ut färdig HTML-sida
echo $template;

