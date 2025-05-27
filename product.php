<?php
require_once "db.php";
session_start();

if (!isset($_GET['id'])) {
  header("Location: shop.php");
  exit;
}

$id = (int) $_GET['id'];
$stmt = $db->prepare("SELECT name, description, price, image_url FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

$template = file_get_contents("product.html");

if (!$product) {
  $productHtml = "<p>Produkten kunde inte hittas.</p>";
} else {
  $productHtml = "<article>";
  $productHtml .= "<figure><img class='product-image-large' src='{$product['image_url']}' alt='{$product['name']}'></figure>";
  $productHtml .= "<h2>{$product['name']}</h2>";
  $productHtml .= "<p>{$product['description']}</p>";
  $productHtml .= "<p><strong>{$product['price']} kr</strong></p>";
  $productHtml .= "<button class='btn' data-product-id='{$id}' data-product-name='{$product['name']}' data-product-image='{$product['image_url']}' data-product-price='{$product['price']}'>Lägg i kundvagn</button>";
  $productHtml .= "</article>";
}

$template = str_replace("{{product-detail}}", $productHtml, $template);
echo $template;
