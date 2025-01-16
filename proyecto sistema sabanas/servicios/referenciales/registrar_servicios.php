<?php
include "../../conexion/configv2.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['name']) && isset($data['cost'])) {
    $name = $data['name'];
    $cost = $data['cost'];

    

    $query = "INSERT INTO servicios (nombre, costo) VALUES ($1, $2)";
    $result = pg_query_params($conn, $query, array($name, $cost));

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
