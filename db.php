<?php
// db.php (9 - Gesällprov)
// Skapar en PDO-anslutning till databasen "plant_shop" med felhantering

$host = 'localhost'; 
$dbname = 'plant_shop';
$username = 'root'; 
$password = '';

try {
    // Skapa PDO-anslutning med UTF-8 som teckenuppsättning
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    // Ställ in att fel ska hanteras som undantag
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Visa felmeddelande om anslutningen misslyckas
    die("Fel vid anslutning till databasen: " . $e->getMessage());
}
?>