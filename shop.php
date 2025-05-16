<?php
require_once "db.php"; // Din databasanslutning

$selectedCategories = $_GET['category'] ?? [];
$productHtml = "";
$checked = ["Rankande" => "", "Lättskötta" => "", "Luftrenande" => ""];

// Markera checkboxar som valda vid refresh
foreach ($selectedCategories as $cat) {
    if (isset($checked[$cat])) {
        $checked[$cat] = 'checked';
    }
}

// Hämta produkter
$sql = "SELECT id, name, price, image_url FROM products";
$params = [];

if (!empty($selectedCategories)) {
    $placeholders = implode(',', array_fill(0, count($selectedCategories), '?'));
    $sql .= " WHERE category IN ($placeholders)";
    $params = $selectedCategories;
}

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generera produkt-HTML
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

// Läs in HTML-mall och ersätt placeholders
$template = file_get_contents("shop.html");
$template = str_replace("{{product-list}}", $productHtml, $template);
$template = str_replace("{{checked-rankande}}", $checked["Rankande"], $template);
$template = str_replace("{{checked-lattskotta}}", $checked["Lättskötta"], $template);
$template = str_replace("{{checked-luftrenande}}", $checked["Luftrenande"], $template);

echo $template;
