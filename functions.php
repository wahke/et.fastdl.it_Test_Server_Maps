<?php
require_once 'db.php';

/**
 * Verfügbare Maps aus einer Datei abrufen.
 *
 * @param string $file
 * @return array
 */
function getAvailableMaps($file) {
    if (file_exists($file)) {
        return file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }
    return [];
}

/**
 * Konfigurationswert aus der Datenbank abrufen.
 *
 * @param string $key_name
 * @return string|null
 */
function getConfigValue($key_name) {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("SELECT value FROM config WHERE key_name = :key_name");
    $stmt->execute(['key_name' => $key_name]);
    $result = $stmt->fetch();
    return $result ? $result['value'] : null;
}

/**
 * Alle Server aus der Datenbank abrufen und mit IP und Port ergänzen.
 *
 * @return array
 */
function getAllServers() {
    $pdo = getDatabaseConnection();
    $servers = $pdo->query("SELECT * FROM servers")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($servers as &$server) {
        $details = getPterodactylServerDetails($server['uuid']);
        if ($details) {
            $server['ip'] = $details['attributes']['resources']['allocation']['ip'] ?? 'Unbekannt';
            $server['port'] = $details['attributes']['resources']['allocation']['port'] ?? 'Unbekannt';
        } else {
            $server['ip'] = 'Unbekannt';
            $server['port'] = 'Unbekannt';
        }
    }

    return $servers;
}

/**
 * Details eines Servers von der Pterodactyl API abrufen.
 *
 * @param string $uuid
 * @return array|null
 */
function getPterodactylServerDetails($uuid) {
    $apiToken = getConfigValue('client_api_token');
    $baseUrl = getConfigValue('client_api_base_url');
    $url = "{$baseUrl}/servers/{$uuid}/resources";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$apiToken}",
        "Accept: application/json"
    ]);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200) {
        return json_decode($response, true);
    }

    return null;
}

/**
 * Alle Mods aus der Datenbank abrufen.
 *
 * @return array
 */
function getAllMods() {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->query("SELECT * FROM mods");
    return $stmt->fetchAll();
}

/**
 * Nutzung des Servers protokollieren.
 *
 * @param int $server_id
 * @param string $map_name
 * @param string $mod_name
 */
function logServerUsage($server_id, $map_name, $mod_name) {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("
        INSERT INTO usage_logs (server_id, map_name, mod_name)
        VALUES (:server_id, :map_name, :mod_name)
    ");
    $stmt->execute([
        'server_id' => $server_id,
        'map_name' => $map_name,
        'mod_name' => $mod_name,
    ]);
}

/**
 * Serverstatus, Map und Mod basierend auf gespeicherten Dateien abrufen.
 *
 * @param int $server_id
 * @param int $runtime_limit
 * @return array
 */
function getServerStatusFromFiles($server_id, $runtime_limit) {
    $start_time_file = "start_time_{$server_id}.txt";
    $start_time = file_exists($start_time_file) ? (int)file_get_contents($start_time_file) : 0;
    $elapsed_time = time() - $start_time;
    $remaining_time = max(0, $runtime_limit - $elapsed_time);

    $current_map_file = "current_map_{$server_id}.txt";
    $current_mod_file = "current_mod_{$server_id}.txt";

    $current_map = file_exists($current_map_file) ? trim(file_get_contents($current_map_file)) : 'Keine Map';
    $current_mod = file_exists($current_mod_file) ? trim(file_get_contents($current_mod_file)) : 'Kein Mod';

    $status = $remaining_time > 0 ? 'Gestartet' : 'Gestoppt';

    return [
        'map' => $current_map,
        'mod' => $current_mod,
        'status' => $status,
        'remaining_time' => $remaining_time,
    ];
}
?>
