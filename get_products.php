<?php
// Skapa en anslutning till databasen
$host = 'localhost'; 
$db = 'plant_shop';
$user = 'root';
$pass = ''; 

try {
    $db = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit;
}

// Hämta kategori från URL
$category = isset($_GET['category']) ? $_GET['category'] : ''; // Hämtar kategori från GET-parametern

// Skapa SQL-frågan
$query = 'SELECT id, name, description, price, image_url, category FROM products ORDER BY (id = 2), id';
if ($category) {
    // Om kategori är angiven, lägg till filter i frågan
    $query .= ' WHERE category IN (' . implode(',', array_map(function($cat) { return "'" . addslashes($cat) . "'"; }, explode(',', $category))) . ')';
}

$stmt = $db->query($query);

// Hämta alla resultat som en associerad array
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Returnera produkterna som JSON
echo json_encode($products);
?>