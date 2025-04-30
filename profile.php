<?php
session_start();
require 'db.php';
?>

<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8">
  <title>Min profil</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <h1>Min profil</h1>

  <?php if (!isset($_SESSION['user_id'])): ?>
    <form method="POST" action="login.php">
      <label for="email">E-post:</label>
      <input type="email" name="email" id="email" required><br>

      <label for="password">Lösenord:</label>
      <input type="password" name="password" id="password" required><br>

      <button type="submit">Logga in</button>
    </form>
    <p>Har du inget konto? <a href="register.php">Registrera dig här</a>.</p>

  <?php else: ?>
    <p>Inloggad som <strong><?= htmlspecialchars($_SESSION['email']) ?></strong></p>
    <ul>
      <li><a href="order_history.php">Visa mina ordrar</a></li>
      <li><a href="logout.php">Logga ut</a></li>
    </ul>
  <?php endif; ?>

</body>
</html>
