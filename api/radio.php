<?php
include("conexion.php");

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

if ($conexion === null) {
    echo json_encode([
        ["id" => 1,  "titulo" => "Nº 26 - Inicio de temporada",                                    "enlace" => "#"],
        ["id" => 2,  "titulo" => "Nº 27 - Entrevista especial",                                    "enlace" => "#"],
        ["id" => 3,  "titulo" => "Nº 28 - El mundo del jazz",                                      "enlace" => "#"],
        ["id" => 4,  "titulo" => "Nº 29 - Músicos locales",                                        "enlace" => "#"],
        ["id" => 5,  "titulo" => "Nº 30 - Producción audiovisual hoy",                             "enlace" => "#"],
        ["id" => 6,  "titulo" => "Nº 31 - Entrevista a artistas emergentes",                       "enlace" => "#"],
        ["id" => 7,  "titulo" => "Nº 32 - Festivales de verano",                                   "enlace" => "#"],
        ["id" => 8,  "titulo" => "Nº 33 - Especial YECLA JAZZ FESTIVAL 2025 | EL CAMINO DEL ARTISTA | La Radio del Sur MX", "enlace" => "#"],
        ["id" => 9,  "titulo" => "Nº 34 - ENTREVISTA a PAT MCMANUS",                               "enlace" => "#"],
    ]);
    exit;
}

$resultado = $conexion->query("SELECT * FROM RADIO");

$radio = [];

while ($fila = $resultado->fetch_assoc()) {
    $radio[] = $fila;
}

echo json_encode($radio);
?>