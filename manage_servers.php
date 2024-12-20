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

// Verfügbare Admins abrufen
function getAllAdmins() {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->query("SELECT id, username FROM admins");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$admins = getAllAdmins();

// Server hinzufügen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = $_POST['name'] ?? '';
    $uuid = $_POST['uuid'] ?? '';
    $pterodactyl_id = $_POST['pterodactyl_id'] ?? '';
    $admin_id = $_POST['admin_id'] ?? '';
    $runtime_limit = $_POST['runtime_limit'] ?? 1800;

    try {
        $pdo = getDatabaseConnection();

        // Überprüfen, ob die Admin-ID existiert
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE id = :admin_id");
        $stmt->execute(['admin_id' => $admin_id]);
        if ($stmt->fetchColumn() == 0) {
            throw new Exception("Ungültige Admin-ID.");
        }

        $stmt = $pdo->prepare("INSERT INTO servers (name, uuid, pterodactyl_id, admin_id, runtime_limit) VALUES (:name, :uuid, :pterodactyl_id, :admin_id, :runtime_limit)");
        $stmt->execute([
            'name' => $name,
            'uuid' => $uuid,
            'pterodactyl_id' => $pterodactyl_id,
            'admin_id' => $admin_id,
            'runtime_limit' => $runtime_limit,
        ]);
        header("Location: manage_servers.php");
        exit;
    } catch (Exception $e) {
        die("Fehler beim Hinzufügen des Servers: " . $e->getMessage());
    }
}

// Server bearbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $uuid = $_POST['uuid'] ?? '';
    $pterodactyl_id = $_POST['pterodactyl_id'] ?? '';
    $admin_id = $_POST['admin_id'] ?? '';
    $runtime_limit = $_POST['runtime_limit'] ?? 1800;

    try {
        $pdo = getDatabaseConnection();

        $stmt = $pdo->prepare("UPDATE servers SET name = :name, uuid = :uuid, pterodactyl_id = :pterodactyl_id, admin_id = :admin_id, runtime_limit = :runtime_limit WHERE id = :id");
        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'uuid' => $uuid,
            'pterodactyl_id' => $pterodactyl_id,
            'admin_id' => $admin_id,
            'runtime_limit' => $runtime_limit,
        ]);
        header("Location: manage_servers.php");
        exit;
    } catch (Exception $e) {
        die("Fehler beim Bearbeiten des Servers: " . $e->getMessage());
    }
}

// Server löschen
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare("DELETE FROM servers WHERE id = :id");
        $stmt->execute(['id' => $id]);
        header("Location: manage_servers.php");
        exit;
    } catch (PDOException $e) {
        die("Fehler beim Löschen des Servers: " . $e->getMessage());
    }
}

// Alle Server abrufen
$servers = getAllServers();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server-Verwaltung</title>
</head>
<body>
    <h1>Server-Verwaltung</h1>

    <h2>Server hinzufügen</h2>
    <form method="POST" action="">
        <input type="hidden" name="action" value="add">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
        <br>
        <label for="uuid">UUID:</label>
        <input type="text" id="uuid" name="uuid" required>
        <br>
        <label for="pterodactyl_id">Pterodactyl-ID:</label>
        <input type="text" id="pterodactyl_id" name="pterodactyl_id" required>
        <br>
        <label for="admin_id">Admin:</label>
        <select id="admin_id" name="admin_id" required>
            <option value="">Bitte wählen</option>
            <?php foreach ($admins as $admin): ?>
                <option value="<?php echo htmlspecialchars($admin['id']); ?>">
                    <?php echo htmlspecialchars($admin['username']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br>
        <label for="runtime_limit">Laufzeitlimit (Sekunden):</label>
        <input type="number" id="runtime_limit" name="runtime_limit" value="1800" required>
        <br>
        <button type="submit">Server hinzufügen</button>
    </form>

    <h2>Bestehende Server</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>UUID</th>
                <th>Pterodactyl-ID</th>
                <th>Admin</th>
                <th>Laufzeitlimit</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($servers as $server): ?>
                <tr>
                    <td><?php echo htmlspecialchars($server['id']); ?></td>
                    <td><?php echo htmlspecialchars($server['name']); ?></td>
                    <td><?php echo htmlspecialchars($server['uuid']); ?></td>
                    <td><?php echo htmlspecialchars($server['pterodactyl_id']); ?></td>
                    <td><?php echo htmlspecialchars($server['admin_id']); ?></td>
                    <td><?php echo htmlspecialchars($server['runtime_limit']); ?></td>
                    <td>
                        <form method="POST" action="" style="display:inline;">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($server['id']); ?>">
                            <input type="text" name="name" value="<?php echo htmlspecialchars($server['name']); ?>" required>
                            <input type="text" name="uuid" value="<?php echo htmlspecialchars($server['uuid']); ?>" required>
                            <input type="text" name="pterodactyl_id" value="<?php echo htmlspecialchars($server['pterodactyl_id']); ?>" required>
                            <input type="number" name="runtime_limit" value="<?php echo htmlspecialchars($server['runtime_limit']); ?>" required>
                            <select name="admin_id" required>
                                <?php foreach ($admins as $admin): ?>
                                    <option value="<?php echo htmlspecialchars($admin['id']); ?>" <?php echo $admin['id'] == $server['admin_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($admin['username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit">Bearbeiten</button>
                        </form>
                        <a href="manage_servers.php?action=delete&id=<?php echo $server['id']; ?>" onclick="return confirm('Möchten Sie diesen Server wirklich löschen?');">Löschen</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="admin.php">Zurück zum Admin-Dashboard</a>
</body>
</html>
