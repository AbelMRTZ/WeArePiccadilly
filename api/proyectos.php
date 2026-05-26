<?php
include("conexion.php");

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

if ($conexion === null) {
    echo json_encode([
        ["titulo" => "Recreación Histórica",       "subtitulo" => "Grabación de evento",    "foto_rojo" => "imgBD/1_red.png",  "enlace" => "#"],
        ["titulo" => "Brigada del Arte",            "subtitulo" => "Producción audiovisual", "foto_rojo" => "imgBD/2_red.png",  "enlace" => "#"],
        ["titulo" => "Museo Rico López",            "subtitulo" => "Documental",             "foto_rojo" => "imgBD/3_red.png",  "enlace" => "#"],
        ["titulo" => "Lido Rico - Genoarquitecturas","subtitulo" => "Corporativo",           "foto_rojo" => "imgBD/4_red.png",  "enlace" => "#"],
        ["titulo" => "Sónar 2018",                  "subtitulo" => "Cobertura de evento",    "foto_rojo" => "imgBD/5_red.png",  "enlace" => "#"],
        ["titulo" => "Monte Arabí",                 "subtitulo" => "Documental",             "foto_rojo" => "imgBD/6_red.png",  "enlace" => "#"],
        ["titulo" => "Alcaraz Renacentista",        "subtitulo" => "Grabación de evento",    "foto_rojo" => "imgBD/7_red.png",  "enlace" => "#"],
        ["titulo" => "Festival de Jazz",            "subtitulo" => "Cobertura audiovisual",  "foto_rojo" => "imgBD/8_red.png",  "enlace" => "#"],
        ["titulo" => "Scrambled",                   "subtitulo" => "Videoclip",              "foto_rojo" => "imgBD/9_red.png",  "enlace" => "#"],
        ["titulo" => "Yoko",                        "subtitulo" => "Cortometraje",           "foto_rojo" => "imgBD/10_red.png", "enlace" => "#"],
        ["titulo" => "Palace",                      "subtitulo" => "Producción musical",     "foto_rojo" => "imgBD/11_red.png", "enlace" => "#"],
        ["titulo" => "Mujer en Tránsito",           "subtitulo" => "Documental",             "foto_rojo" => "imgBD/12_red.jpg", "enlace" => "#"],
        ["titulo" => "Bar Tenis",                   "subtitulo" => "Corporativo",            "foto_rojo" => "imgBD/13_red.png", "enlace" => "#"],
        ["titulo" => "ITF Tenis",                   "subtitulo" => "Cobertura deportiva",    "foto_rojo" => "imgBD/14_red.jpg", "enlace" => "#"],
        ["titulo" => "Lisboa",                      "subtitulo" => "Producción audiovisual", "foto_rojo" => "imgBD/15_red.png", "enlace" => "#"],
        ["titulo" => "Pepe Sánchez - Jazz Festival","subtitulo" => "Grabación de concierto", "foto_rojo" => "imgBD/16_red.png", "enlace" => "#"],
        ["titulo" => "Audiotec",                    "subtitulo" => "Corporativo",            "foto_rojo" => "imgBD/17_red.png", "enlace" => "#"],
        ["titulo" => "Volvo Ocean Race",            "subtitulo" => "Cobertura de evento",    "foto_rojo" => "imgBD/18_red.png", "enlace" => "#"],
        ["titulo" => "We Are Piccadilly DJs",       "subtitulo" => "Producción audiovisual", "foto_rojo" => "imgBD/19_red.png", "enlace" => "#"],
        ["titulo" => "Brain Session Project",       "subtitulo" => "Videoclip",              "foto_rojo" => "imgBD/20_red.png", "enlace" => "#"],
        ["titulo" => "Loft 113",                    "subtitulo" => "Producción musical",     "foto_rojo" => "imgBD/21_red.png", "enlace" => "#"],
    ]);
    exit;
}

$resultado = $conexion->query("SELECT * FROM PROYECTOS");

$proyectos = [];

while ($fila = $resultado->fetch_assoc()) {
    $proyectos[] = $fila;
}

echo json_encode($proyectos);
?>
