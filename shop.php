<?php
require_once "db.php";
session_start();

$template = file_get_contents("shop.html");

// Hantera filter
$selected = $_GET['category'] ?? [];
$categoryPlaceholders = [
  "Rankande" => "checked-rankande",
  "Lättskötta" => "checked-lattskotta",
  "Luftrenande" => "checked-luftrenande"
];

foreach ($categoryPlaceholders as $value => $placeholder) {
  $isChecked = in_array($value, $selected) ? 'checked' : '';
  $template = str_replace("{{{$placeholder}}}", $isChecked, $template);
}

// Hämta produkter
$sql = "SELECT id, name, description, price, image_url, category FROM products";
if (!empty($selected)) {
  $placeholders = implode(',', array_fill(0, count($selected), '?'));
  $sql .= " WHERE category IN ($placeholders)";
}

$stmt = $db->prepare($sql);
$stmt->execute($selected);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Bygg produktlistan
$productHtml = "";
$productTemplate = file_get_contents("templates/shop_product_card.html");

foreach ($products as $product) {
  $productHtml .= str_replace(
    ['{{id}}', '{{name}}', '{{price}}', '{{image_url}}'],
    [$product['id'], $product['name'], $product['price'], $product['image_url']],
    $productTemplate
  );
}

$template = str_replace("{{product-list}}", $productHtml, $template);
echo $template;
