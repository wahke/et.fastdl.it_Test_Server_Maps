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

// Mod hinzufügen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $mod_name = $_POST['name'] ?? '';

    try {
        $pdo = getDatabaseConnection();

        $stmt = $pdo->prepare("INSERT INTO mods (name) VALUES (:name)");
        $stmt->execute(['name' => $mod_name]);

        header("Location: manage_mods.php");
        exit;
    } catch (Exception $e) {
        echo "<p>Fehler beim Hinzufügen des Mods: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// Mod bearbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $mod_id = $_POST['id'] ?? '';
    $mod_name = $_POST['name'] ?? '';

    try {
        $pdo = getDatabaseConnection();

        $stmt = $pdo->prepare("UPDATE mods SET name = :name WHERE id = :id");
        $stmt->execute(['name' => $mod_name, 'id' => $mod_id]);

        header("Location: manage_mods.php");
        exit;
    } catch (Exception $e) {
        echo "<p>Fehler beim Bearbeiten des Mods: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// Mod löschen
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $mod_id = $_GET['id'];

    try {
        $pdo = getDatabaseConnection();

        $stmt = $pdo->prepare("DELETE FROM mods WHERE id = :id");
        $stmt->execute(['id' => $mod_id]);

        header("Location: manage_mods.php");
        exit;
    } catch (PDOException $e) {
        echo "<p>Fehler beim Löschen des Mods: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// Alle Mods abrufen
$mods = getAllMods();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mod-Verwaltung</title>
</head>
<body>
    <h1>Mod-Verwaltung</h1>

    <h2>Mod hinzufügen</h2>
    <form method="POST" action="">
        <input type="hidden" name="action" value="add">
        <label for="name">Mod-Name:</label>
        <input type="text" id="name" name="name" required>
        <br>
        <button type="submit">Mod hinzufügen</button>
    </form>

    <h2>Bestehende Mods</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($mods)): ?>
                <tr>
                    <td colspan="3">Keine Mods gefunden.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($mods as $mod): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($mod['id']); ?></td>
                        <td><?php echo htmlspecialchars($mod['name']); ?></td>
                        <td>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($mod['id']); ?>">
                                <input type="text" name="name" value="<?php echo htmlspecialchars($mod['name']); ?>" required>
                                <button type="submit">Bearbeiten</button>
                            </form>
                            <a href="manage_mods.php?action=delete&id=<?php echo $mod['id']; ?>" onclick="return confirm('Möchten Sie diesen Mod wirklich löschen?');">Löschen</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="admin.php">Zurück zum Admin-Dashboard</a>
</body>
</html>
