<?php
include "../../conexion/configv2.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['name']) && isset($data['cost']) && isset($data['type'])) {
    $name = $data['name'];
    $cost = $data['cost'];
    $type = $data['type'];

    $query = "INSERT INTO servicios (nombre, costo, tipo) VALUES ($1, $2, $3)";
    $result = pg_query_params($conn, $query, array($name, $cost, $type));

    if ($result) {
        echo json_encode(['message' => 'Servicio registrado exitosamente.']);
    } else {
        echo json_encode(['message' => 'Error al registrar el servicio.']);
    }

    pg_close($conn);
} else {
    echo json_encode(['message' => 'Datos incompletos.']);
}
?>
