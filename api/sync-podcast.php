<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
/**
 * ENDPOINT: wearepiccadilly.com/api/sync-podcast.php
 *
 * Recibe episodios nuevos desde Cowork y los inserta en la BD.
 * Sube este archivo a la carpeta /api/ de tu web.
 */
 
// ── Configuración ─────────────────────────────────────────────────────────────
define('DB_HOST',    'localhost');
define('DB_USER',    'wapUser');
define('DB_PASS',    'wapPass_6769');
define('DB_NAME',    'wapDB');
define('SYNC_TOKEN', 'Piccadilly_Sync_2024_xK9mQ'); // Token secreto compartido con Cowork
 
// ── Cabeceras ─────────────────────────────────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
 
// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}
 
// ── Verificar token de seguridad ──────────────────────────────────────────────
$token = $_SERVER['HTTP_X_SYNC_TOKEN'] ?? '';
if ($token !== SYNC_TOKEN) {
    http_response_code(403);
    echo json_encode(['error' => 'Token inválido']);
    exit;
}
 
// ── Leer body JSON ────────────────────────────────────────────────────────────
$body = json_decode(file_get_contents('php://input'), true);
 
if (!isset($body['episodios']) || !is_array($body['episodios']) || count($body['episodios']) === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'No se recibieron episodios']);
    exit;
}
 
// ── Conectar a la BD ──────────────────────────────────────────────────────────
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$conn->set_charset('utf8mb4');
 
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión: ' . $conn->connect_error]);
    exit;
}
 
// ── Procesar episodios ────────────────────────────────────────────────────────
$insertados = [];
$omitidos   = [];
 
$stmt_check  = $conn->prepare('SELECT id FROM RADIO WHERE enlace = ?');
$stmt_insert = $conn->prepare('INSERT INTO RADIO (titulo, enlace) VALUES (?, ?)');
 
foreach ($body['episodios'] as $ep) {
    $titulo = trim($ep['titulo'] ?? '');
    $enlace = trim($ep['enlace'] ?? '');
 
    if (empty($titulo) || empty($enlace)) {
        $omitidos[] = ['titulo' => $titulo, 'motivo' => 'Faltan campos'];
        continue;
    }
 
    // ¿Ya existe este enlace en la BD?
    $stmt_check->bind_param('s', $enlace);
    $stmt_check->execute();
    $stmt_check->store_result();
 
    if ($stmt_check->num_rows > 0) {
        $omitidos[] = ['titulo' => $titulo, 'motivo' => 'Ya existe'];
        continue;
    }
 
    // Insertar nuevo episodio
    $stmt_insert->bind_param('ss', $titulo, $enlace);
    if ($stmt_insert->execute()) {
        $insertados[] = $titulo;
        error_log("[sync-podcast] Insertado: $titulo");
    } else {
        $omitidos[] = ['titulo' => $titulo, 'motivo' => 'Error al insertar: ' . $stmt_insert->error];
    }
}
 
$stmt_check->close();
$stmt_insert->close();
$conn->close();
 
// ── Respuesta ─────────────────────────────────────────────────────────────────
echo json_encode([
    'ok'         => true,
    'insertados' => $insertados,
    'omitidos'   => $omitidos,
    'resumen'    => count($insertados) . ' nuevos, ' . count($omitidos) . ' omitidos',
]);
