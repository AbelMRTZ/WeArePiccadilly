<?php
// v8.0.0
$host = "localhost";
$usuario = "wapUser"; // wapUser // root
$contrasena = "wapPass_6769"; // wapPass_6769
$bd = "wapDB";
error_log("Conetandose a la bd");
$conexion = new mysqli($host, $usuario, $contrasena, $bd);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
} else {
    error_log("conexion correcta a la BD");
}


/* 
sudo mysql -u root
USE RADIO;
INSERT INTO RADIO (titulo, enlace) VALUES ('Nº 34 - ENTREVISTA a PAT MCMANUS','https://www.ivoox.com/34-entrevista-pat-mcmanus-audios-mp3_rf_161018178_1.html'); 
UPDATE RADIO SET titulo = 'Nº 33 - Especial YECLA JAZZ FESTIVAL 2025 | EL CAMINO DEL ARTISTA | La Radio del Sur MX' WHERE id=8;
*/