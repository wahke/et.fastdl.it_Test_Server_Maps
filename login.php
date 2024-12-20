<?php
session_start();
require_once 'db.php';

// Überprüfen, ob ein Admin bereits eingeloggt ist
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit;
}

// Login-Prozess
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        header("Location: admin.php");
        exit;
    } else {
        $error = "Ungültiger Benutzername oder Passwort.";
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
</head>
<body>
    <h1>Admin Login</h1>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <label for="username">Benutzername:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Passwort:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
