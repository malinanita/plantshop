<?php
require_once "db.php";

// Hämta produkter
$stmt = $db->prepare("SELECT id, name, price, image_url FROM products");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generera HTML för varje produkt
$productHtml = "";
foreach ($products as $p) {
  $productHtml .= <<<HTML
<article>
  <figure>
    <a href="product.php?id={$p['id']}">
      <img src="{$p['image_url']}" alt="{$p['name']}">
    </a>
  </figure>
  <h3>
    <a href="product.php?id={$p['id']}" class="product-link">{$p['name']}</a>
  </h3>
  <p><strong>{$p['price']} kr</strong></p>
  <button class="btn" onclick="addToCart({$p['id']}, '{$p['name']}', '{$p['image_url']}', {$p['price']})">Lägg i kundvagn</button>
</article>
HTML;
}

// Läs in HTML-mallen
$template = file_get_contents("shop.html");

// Ersätt {{product-list}} med produkterna
$output = str_replace("{{product-list}}", $productHtml, $template);

// Visa sidan
echo $output;
