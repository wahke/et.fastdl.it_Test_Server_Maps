<?php
session_start();
require_once 'functions.php';

// Admin-Login prüfen
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
// Alle Server anzeigen
$servers = getAllServers();

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin-Dashboard</title>
</head>
<body>
    <h1>Admin-Dashboard</h1>
    <p>Willkommen im Admin-Bereich. Wählen Sie eine der folgenden Optionen:</p>


    <ul>
        <?php foreach ($servers as $server): ?>
            <li><?php echo htmlspecialchars($server['name']); ?> (UUID: <?php echo htmlspecialchars($server['uuid']); ?>)</li>
        <?php endforeach; ?>
    </ul>



    <nav>
        <ul>
            <li><a href="manage_servers.php">Server-Verwaltung</a></li>
            <li><a href="manage_mods.php">Mods-Verwaltung</a></li>
            <li><a href="manage_config.php">Konfigurations-Verwaltung</a></li>
            <li><a href="usage_statistics.php">Nutzungs-Statistiken</a> (optional)</li>
        </ul>
    </nav>

    <p><a href="logout.php">Abmelden</a></p>
</body>
</html>