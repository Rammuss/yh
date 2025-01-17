<?php
include "../conexion/configv2.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id_cabecera'])) {
    $id_cabecera = $data['id_cabecera'];
    
    $query = "UPDATE servicios_cabecera SET estado = 'rechazado' WHERE id_cabecera = $1";
    $result = pg_query_params($conn, $query, array($id_cabecera));

    if ($result) {
        echo json_encode(['message' => 'Estado actualizado a rechazado.']);
    } else {
        echo json_encode(['message' => 'Error al actualizar el estado.']);
    }

    pg_close($conn);
} else {
    echo json_encode(['message' => 'ID de cabecera no proporcionado.']);
}
?>
