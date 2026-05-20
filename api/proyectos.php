<?php
include("conexion.php");

$resultado = $conexion->query("SELECT * FROM PROYECTOS");

$proyectos = [];

while ($fila = $resultado->fetch_assoc()) {
    $proyectos[] = $fila;
}

header('Content-Type: application/json');
echo json_encode($proyectos);
?>
