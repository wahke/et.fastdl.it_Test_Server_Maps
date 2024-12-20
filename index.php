<?php
require_once 'functions.php';

// Alle Server aus der Datenbank abrufen
$servers = getAllServers();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Server-Management</title>
    <script>
        const countdowns = {}; // Countdown-Objekt für alle Server

        function updateCountdown(serverId) {
            if (countdowns[serverId] > 0) {
                const minutes = Math.floor(countdowns[serverId] / 60);
                const seconds = countdowns[serverId] % 60;
                document.getElementById(`countdown_${serverId}`).innerText = `${minutes}m ${seconds}s`;
                countdowns[serverId]--;
                setTimeout(() => updateCountdown(serverId), 1000);
            } else {
                document.getElementById(`countdown_${serverId}`).innerText = "Server wird gestoppt.";
            }
        }

        window.onload = function () {
            <?php foreach ($servers as $server): ?>
                countdowns[<?php echo $server['id']; ?>] = <?php echo getServerStatusFromFiles($server['id'], $server['runtime_limit'])['remaining_time']; ?>;
                if (countdowns[<?php echo $server['id']; ?>] > 0) {
                    updateCountdown(<?php echo $server['id']; ?>);
                }
            <?php endforeach; ?>
        };
    </script>
</head>
<body>
    <h1>Server-Management</h1>

    <h2>Server starten</h2>
    <form method="POST" action="start_server.php">
        <label for="server">Server auswählen:</label>
        <select id="server" name="server" required>
            <option value="">Bitte wählen</option>
            <?php foreach ($servers as $server): ?>
                <option value="<?php echo htmlspecialchars($server['id']); ?>">
                    <?php echo htmlspecialchars($server['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br>

        <label for="map">Map auswählen:</label>
        <select id="map" name="map" required>
            <option value="">Bitte wählen</option>
            <?php foreach (getAvailableMaps("map.txt") as $map): ?>
                <option value="<?php echo htmlspecialchars($map); ?>">
                    <?php echo htmlspecialchars($map); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br>

        <label for="mod">Mod auswählen:</label>
        <select id="mod" name="mod" required>
            <option value="">Bitte wählen</option>
            <?php foreach (getAllMods() as $mod): ?>
                <option value="<?php echo htmlspecialchars($mod['name']); ?>">
                    <?php echo htmlspecialchars($mod['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br>

        <button type="submit">Server starten</button>
    </form>

    <h2>Gestartete Server</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Server</th>
                <th>IP</th>
                <th>Aktuelle Map</th>
                <th>Aktueller Mod</th>
                <th>Status</th>
                <th>Verbleibende Zeit</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($servers as $server): ?>
                <?php $status = getServerStatusFromFiles($server['id'], $server['runtime_limit']); ?>
                <tr>
                    <td><?php echo htmlspecialchars($server['name']); ?></td>
                    <td>
                        <?php 
                        echo isset($server['ip'], $server['port']) 
                            ? htmlspecialchars($server['ip'] . ':' . $server['port']) 
                            : "Unbekannt"; 
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($status['map']); ?></td>
                    <td><?php echo htmlspecialchars($status['mod']); ?></td>
                    <td><?php echo htmlspecialchars($status['status']); ?></td>
                    <td>
                        <?php if ($status['remaining_time'] > 0): ?>
                            <span id="countdown_<?php echo $server['id']; ?>">Lädt...</span>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
