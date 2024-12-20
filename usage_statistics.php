<?php
session_start();
require_once 'functions.php';

// Admin-Login prüfen
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Nutzungsliste abrufen
function getUsageLogs() {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->query("SELECT * FROM usage_logs ORDER BY timestamp DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Mods-Statistik abrufen
function getModStatistics() {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->query(
        "SELECT mod_name, COUNT(*) AS usage_count 
         FROM usage_logs 
         GROUP BY mod_name 
         ORDER BY usage_count DESC"
    );
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Maps-Statistik abrufen
function getMapStatistics() {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->query(
        "SELECT map_name, COUNT(*) AS usage_count 
         FROM usage_logs 
         GROUP BY map_name 
         ORDER BY usage_count DESC"
    );
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Server-Statistik abrufen
function getServerStatistics() {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->query(
        "SELECT server_id, COUNT(*) AS usage_count 
         FROM usage_logs 
         GROUP BY server_id 
         ORDER BY usage_count DESC"
    );
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$usage_logs = getUsageLogs();
$mod_statistics = getModStatistics();
$map_statistics = getMapStatistics();
$server_statistics = getServerStatistics();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nutzungsstatistiken</title>
</head>
<body>
    <h1>Nutzungsstatistiken</h1>

    <h2>Letzte Nutzungen</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Server ID</th>
                <th>Map</th>
                <th>Mod</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usage_logs as $log): ?>
                <tr>
                    <td><?php echo htmlspecialchars($log['id']); ?></td>
                    <td><?php echo htmlspecialchars($log['server_id']); ?></td>
                    <td><?php echo htmlspecialchars($log['map_name']); ?></td>
                    <td><?php echo htmlspecialchars($log['mod_name']); ?></td>
                    <td><?php echo htmlspecialchars($log['timestamp']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Mod-Statistiken</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Mod</th>
                <th>Anzahl</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($mod_statistics as $mod): ?>
                <tr>
                    <td><?php echo htmlspecialchars($mod['mod_name']); ?></td>
                    <td><?php echo htmlspecialchars($mod['usage_count']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Map-Statistiken</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Map</th>
                <th>Anzahl</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($map_statistics as $map): ?>
                <tr>
                    <td><?php echo htmlspecialchars($map['map_name']); ?></td>
                    <td><?php echo htmlspecialchars($map['usage_count']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Server-Statistiken</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Server ID</th>
                <th>Anzahl</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($server_statistics as $server): ?>
                <tr>
                    <td><?php echo htmlspecialchars($server['server_id']); ?></td>
                    <td><?php echo htmlspecialchars($server['usage_count']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="admin.php">Zurück zum Admin-Dashboard</a>
</body>
</html>
