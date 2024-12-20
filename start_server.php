<?php
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $server_id = $_POST['server'] ?? null;
    $map = $_POST['map'] ?? null;
    $mod = $_POST['mod'] ?? null;

    if (!$server_id || !$map || !$mod) {
        die("Bitte wählen Sie einen Server, eine Map und einen Mod aus.");
    }

    try {
        $pdo = getDatabaseConnection();

        // Server-Daten abrufen
        $stmt = $pdo->prepare("SELECT * FROM servers WHERE id = :server_id");
        $stmt->execute(['server_id' => $server_id]);
        $server = $stmt->fetch();

        if (!$server) {
            die("Ungültiger Server.");
        }

        $uuid = $server['uuid'];
        $pterodactyl_id = $server['pterodactyl_id'];
        $runtime_limit = $server['runtime_limit'];

        // Admin-API-Aufruf zum Ändern der Map und Mod
        $admin_api_base_url = getConfigValue('admin_api_base_url');
        $admin_api_token = getConfigValue('admin_api_token');
        $endpoint = $admin_api_base_url . "/servers/" . $pterodactyl_id . "/startup";

        $headers = [
            "Authorization: Bearer $admin_api_token",
            "Content-Type: application/json",
            "Accept: application/json",
        ];

        $data = [
            "startup" => "./etlded +set net_port {{SERVER_PORT}} +map {{MAP}} +set fs_game {{MOD}} +exec etl_server.cfg",
            "egg" => 31,
            "image" => "ghcr.io/wahke/steamcmd:debian",
            "skip_scripts" => false,
            "environment" => [
                "MAP" => $map,
                "MOD" => $mod,
                "ET_VERSION" => "32",
                "OMNIBOT" => "0",
            ]
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200) {
            die("Fehler beim Aktualisieren der Map und Mod. API-Antwort: $response");
        }

        // Client-API-Aufruf zum Starten des Servers
        $client_api_base_url = getConfigValue('client_api_base_url');
        $client_api_token = getConfigValue('client_api_token');
        $start_endpoint = $client_api_base_url . "/servers/" . $uuid . "/power";

        $headers = [
            "Authorization: Bearer $client_api_token",
            "Content-Type: application/json",
            "Accept: application/json",
        ];

        $start_data = [
            "signal" => "start",
        ];

        $ch = curl_init($start_endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($start_data));

        $start_response = curl_exec($ch);
        $start_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($start_http_code !== 204) {
            die("Fehler beim Starten des Servers. API-Antwort: $start_response");
        }

        // Dateien aktualisieren
        $start_time = time();
        file_put_contents("current_map_{$server_id}.txt", $map);
        file_put_contents("current_mod_{$server_id}.txt", $mod);
        file_put_contents("remaining_time_{$server_id}.txt", $runtime_limit);

        // Nutzung protokollieren
        $stmt = $pdo->prepare("INSERT INTO usage_logs (server_id, map_name, mod_name) VALUES (:server_id, :map_name, :mod_name)");
        $stmt->execute([
            'server_id' => $server_id,
            'map_name' => $map,
            'mod_name' => $mod,
        ]);

        header("Location: index.php");
        exit;
    } catch (Exception $e) {
        die("Fehler: " . $e->getMessage());
    }
} else {
    die("Ungültige Anfrage.");
}
?>
