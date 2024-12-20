<?php
require_once 'functions.php';

// Aktuelle verbleibende Zeit prüfen
$remaining_time_file = "remaining_time.txt";
$current_server_file = "current_server.txt";

if (file_exists($remaining_time_file)) {
    $remaining_time = (int)file_get_contents($remaining_time_file);
    if ($remaining_time <= 0 && file_exists($current_server_file)) {
        // Server stoppen
        $server_id = trim(file_get_contents($current_server_file));
        stopServer($server_id); // Funktion stopServer() muss in functions.php implementiert sein

        // Dateien zurücksetzen
        file_put_contents($remaining_time_file, 0);
        file_put_contents("current_map.txt", "Keine Map");
        file_put_contents("current_mod.txt", "Kein Mod");
        file_put_contents($current_server_file, "Kein Server");
    } else {
        // Zeit verringern, falls aktiv
        file_put_contents($remaining_time_file, max(0, $remaining_time - 60)); // Jede Minute
    }
}
?>
