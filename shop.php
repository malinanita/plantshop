<?php
require_once "db.php";
session_start();

// Ladda HTML-mallen
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

// Rendera produktlista
$productHtml = "";
foreach ($products as $product) {
  $productHtml .= "<article>";
  $productHtml .= "<a class='product-link' href='product.php?id={$product['id']}'>";
  $productHtml .= "<figure><img src='{$product['image_url']}' alt='{$product['name']}' /></figure>";
  $productHtml .= "<h3>{$product['name']}</h3>";
  $productHtml .= "<p>{$product['price']} kr</p>";
  $productHtml .= "</a>";
  $productHtml .= "</article>";
}

$template = str_replace("{{product-list}}", $productHtml, $template);

// Skriv ut hela sidan
echo $template;
