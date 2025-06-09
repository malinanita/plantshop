<?php
// shop.php (9 - Gesällprov)
// Visar butikssidan med möjlighet att filtrera produkter efter kategori

require_once "db.php";
session_start();

// Ladda HTML-mallen för butiken
$template = file_get_contents("shop.html");

// Hantera filterval (t.ex. Rankande, Lättskötta, Luftrenande)
$selected = $_GET['category'] ?? [];

// Mappning mellan kategori och motsvarande checkbox-placeholder i HTML
$categoryPlaceholders = [
  "Rankande" => "checked-rankande",
  "Lättskötta" => "checked-lattskotta",
  "Luftrenande" => "checked-luftrenande"
];

// Ersätt checkboxarnas platsmarkörer med "checked" om de är valda
foreach ($categoryPlaceholders as $value => $placeholder) {
  $isChecked = in_array($value, $selected) ? 'checked' : '';
  $template = str_replace("{{{$placeholder}}}", $isChecked, $template);
}

// SQL-fråga: hämta alla produkter eller filtrera efter kategori
$sql = "SELECT id, name, description, price, image_url, category FROM products";
if (!empty($selected)) {
  // Förbered dynamiska placeholders i WHERE-satsen
  $placeholders = implode(',', array_fill(0, count($selected), '?'));
  $sql .= " WHERE category IN ($placeholders)";
}

$stmt = $db->prepare($sql);
$stmt->execute($selected);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ladda HTML-mall för produktkort
$productHtml = "";
$productTemplate = file_get_contents("templates/shop_product_card.html");

// Fyll produktmallen för varje produkt
foreach ($products as $product) {
  $productHtml .= str_replace(
    ['{{id}}', '{{name}}', '{{price}}', '{{image_url}}'],
    [$product['id'], $product['name'], $product['price'], $product['image_url']],
    $productTemplate
  );
}

// Ersätt plats för produktlistan i huvudmallen
$template = str_replace("{{product-list}}", $productHtml, $template);

// Skriv ut hela sidan
echo $template;


