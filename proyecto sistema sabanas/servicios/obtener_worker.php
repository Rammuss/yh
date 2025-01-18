<?php
include "../conexion/configv2.php";
header('Content-Type: application/json');

$query = "SELECT id, nombre_usuario FROM usuarios WHERE rol = 'worker'";
$result = pg_query($conn, $query);

if ($result) {
    $trabajadores = pg_fetch_all($result);
    echo json_encode(['success' => true, 'data' => $trabajadores]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al obtener los trabajadores.']);
}

pg_close($conn);
?>
