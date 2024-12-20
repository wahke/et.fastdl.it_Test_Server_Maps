<?php
session_start();
require_once 'functions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Admin-Login prüfen
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Konfigurationswerte abrufen
function getConfigValues() {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->query("SELECT * FROM config");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Konfigurationswert hinzufügen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $key_name = $_POST['key_name'] ?? '';
    $value = $_POST['value'] ?? '';

    try {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare("INSERT INTO config (key_name, value) VALUES (:key_name, :value)");
        $stmt->execute([
            'key_name' => $key_name,
            'value' => $value,
        ]);
        header("Location: manage_config.php");
        exit;
    } catch (Exception $e) {
        die("Fehler beim Hinzufügen des Konfigurationswerts: " . $e->getMessage());
    }
}

// Konfigurationswert bearbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['id'] ?? '';
    $key_name = $_POST['key_name'] ?? '';
    $value = $_POST['value'] ?? '';

    try {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare("UPDATE config SET key_name = :key_name, value = :value WHERE id = :id");
        $stmt->execute([
            'id' => $id,
            'key_name' => $key_name,
            'value' => $value,
        ]);
        header("Location: manage_config.php");
        exit;
    } catch (Exception $e) {
        die("Fehler beim Bearbeiten des Konfigurationswerts: " . $e->getMessage());
    }
}

// Konfigurationswert löschen
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare("DELETE FROM config WHERE id = :id");
        $stmt->execute(['id' => $id]);
        header("Location: manage_config.php");
        exit;
    } catch (PDOException $e) {
        die("Fehler beim Löschen des Konfigurationswerts: " . $e->getMessage());
    }
}

// Alle Konfigurationswerte abrufen
$config_values = getConfigValues();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfigurations-Verwaltung</title>
</head>
<body>
    <h1>Konfigurations-Verwaltung</h1>

    <h2>Neuen Konfigurationswert hinzufügen</h2>
    <form method="POST" action="">
        <input type="hidden" name="action" value="add">
        <label for="key_name">Schlüsselname:</label>
        <input type="text" id="key_name" name="key_name" required>
        <br>
        <label for="value">Wert:</label>
        <input type="text" id="value" name="value" required>
        <br>
        <button type="submit">Hinzufügen</button>
    </form>

    <h2>Bestehende Konfigurationswerte</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Schlüsselname</th>
                <th>Wert</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($config_values as $config): ?>
                <tr>
                    <td><?php echo htmlspecialchars($config['id']); ?></td>
                    <td><?php echo htmlspecialchars($config['key_name']); ?></td>
                    <td><?php echo htmlspecialchars($config['value']); ?></td>
                    <td>
                        <form method="POST" action="" style="display:inline;">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($config['id']); ?>">
                            <input type="text" name="key_name" value="<?php echo htmlspecialchars($config['key_name']); ?>" required>
                            <input type="text" name="value" value="<?php echo htmlspecialchars($config['value']); ?>" required>
                            <button type="submit">Bearbeiten</button>
                        </form>
                        <a href="manage_config.php?action=delete&id=<?php echo $config['id']; ?>" onclick="return confirm('Möchten Sie diesen Wert wirklich löschen?');">Löschen</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="admin.php">Zurück zum Admin-Dashboard</a>
</body>
</html>
