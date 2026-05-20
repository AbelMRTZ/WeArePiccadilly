<?php
include("conexion.php");

$resultado = $conexion->query("SELECT * FROM RADIO");

$radio = [];

while ($fila = $resultado->fetch_assoc()) {
    $radio[] = $fila;
}

header('Content-Type: application/json');
echo json_encode($radio);
?>