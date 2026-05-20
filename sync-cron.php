#!/usr/bin/env php
<?php
/**
 * SYNC IVOOX → WEAREPICCADILLY
 * Ejecutar semanalmente via cron:
 *   0 9 * * 1 /usr/bin/php /var/www/wearepiccadilly.com/sync-cron.php >> /var/log/ivoox-sync.log 2>&1
 *
 * Sube este archivo a la raíz de tu web (fuera de /api/ para que no sea público):
 *   /var/www/wearepiccadilly.com/sync-cron.php
 */
 
// ── Configuración ─────────────────────────────────────────────────────────────
define('DB_HOST',         'localhost');
define('DB_USER',         'wapUser');
define('DB_PASS',         'wapPass_6769');
define('DB_NAME',         'wapDB');
define('IVOOX_RSS',       'https://www.ivoox.com/feed_fg_f12547461_filtro_1.xml');
define('EPISODIO_MINIMO', 26);
 
// ── Logging ───────────────────────────────────────────────────────────────────
function log_msg(string $msg): void {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
}
 
// ── Paso 1: Descargar RSS de iVoox ───────────────────────────────────────────
log_msg('Descargando RSS de iVoox...');
 
$ctx = stream_context_create(['http' => [
    'timeout' => 20,
    'header'  => "User-Agent: Mozilla/5.0 (compatible; PiccadillySync/1.0)\r\n",
]]);
 
$xml_raw = @file_get_contents(IVOOX_RSS, false, $ctx);
 
if ($xml_raw === false) {
    log_msg('ERROR: No se pudo descargar el RSS de iVoox.');
    exit(1);
}
 
$xml = simplexml_load_string($xml_raw);
if (!$xml) {
    log_msg('ERROR: El RSS no es un XML válido.');
    exit(1);
}
 
$items = $xml->channel->item ?? [];
log_msg('Episodios encontrados en iVoox: ' . count($items));
 
// ── Paso 2: Parsear episodios ─────────────────────────────────────────────────
function extraer_numero(string $titulo): ?int {
    // Busca patrones como "Nº 57", "N° 57", "No 57", o simplemente el primer número
    if (preg_match('/[Nn][°ºo]?\s*(\d+)/', $titulo, $m)) return (int)$m[1];
    if (preg_match('/\b(\d+)\b/', $titulo, $m))            return (int)$m[1];
    return null;
}
 
function formatear_titulo(string $titulo_ivoox): string {
    return $titulo_ivoox . ' | EL CAMINO DEL ARTISTA | La Radio del Sur MX';
}
 
$episodios_ivoox = [];
foreach ($items as $item) {
    $titulo  = trim((string)$item->title);
    // Intentar obtener la URL del audio; si no, usar el enlace de la página
    $enclosure = $item->enclosure;
    $enlace = $enclosure ? (string)$enclosure['url'] : trim((string)$item->link);
    $numero = extraer_numero($titulo);
 
    if ($numero !== null && $numero >= EPISODIO_MINIMO && !empty($enlace)) {
        $episodios_ivoox[] = ['titulo' => $titulo, 'enlace' => $enlace, 'numero' => $numero];
    }
}
 
// Ordenar de menor a mayor número de episodio (el RSS viene del más nuevo al más antiguo)
usort($episodios_ivoox, fn($a, $b) => $a['numero'] - $b['numero']);
 
log_msg('Episodios desde Nº ' . EPISODIO_MINIMO . ' en adelante: ' . count($episodios_ivoox));
 
if (empty($episodios_ivoox)) {
    log_msg('Nada que sincronizar. Fin.');
    exit(0);
}
 
// ── Paso 3: Conectar a la BD ──────────────────────────────────────────────────
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$conn->set_charset('utf8mb4');
 
if ($conn->connect_error) {
    log_msg('ERROR BD: ' . $conn->connect_error);
    exit(1);
}
 
// ── Paso 4: Obtener los números de episodio ya existentes en la BD ───────────
// Comparamos por número extraído del título, no por URL (más fiable)
$numeros_en_bd = [];
$res = $conn->query('SELECT titulo FROM RADIO');
while ($row = $res->fetch_assoc()) {
    $num = extraer_numero($row['titulo']);
    if ($num !== null) $numeros_en_bd[] = $num;
}
log_msg('Episodios ya en la BD: ' . count($numeros_en_bd));
 
// ── Paso 5: Insertar los nuevos ───────────────────────────────────────────────
$stmt = $conn->prepare('INSERT INTO RADIO (titulo, enlace) VALUES (?, ?)');
$insertados = 0;
 
foreach ($episodios_ivoox as $ep) {
    if (in_array($ep['numero'], $numeros_en_bd)) {
        log_msg('  Ya existe: Nº' . $ep['numero'] . ' — ' . $ep['titulo']);
        continue;
    }
 
    $titulo_bd = formatear_titulo($ep['titulo']);
    $stmt->bind_param('ss', $titulo_bd, $ep['enlace']);
 
    if ($stmt->execute()) {
        log_msg('  ✅ Insertado: ' . $titulo_bd);
        $insertados++;
    } else {
        log_msg('  ❌ Error al insertar "' . $ep['titulo'] . '": ' . $stmt->error);
    }
}
 
$stmt->close();
$conn->close();
 
// ── Resumen ───────────────────────────────────────────────────────────────────
log_msg('─────────────────────────────────────────');
log_msg("Sincronización completada: $insertados episodio(s) nuevo(s) añadido(s).");
