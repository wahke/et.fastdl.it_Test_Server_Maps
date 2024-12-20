<?php
require_once 'db.php';

$pdo = getDatabaseConnection();

// Beispiel-Benutzer
$username = "testuser";
$password = "testuser"; // Klartext-Passwort
$email = "mail@adress.com";

// Passwort hashen
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare("INSERT INTO admins (username, password, email) VALUES (:username, :password, :email)");
$stmt->execute([
    'username' => $username,
    'password' => $hashed_password,
    'email' => $email,
]);

echo "Admin-Benutzer erfolgreich hinzugefügt!";
?>
