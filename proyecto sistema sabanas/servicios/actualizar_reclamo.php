<?php
include "../conexion/configv2.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id_reclamo']) && isset($data['respuesta']) && isset($data['estado'])) {
    $id_reclamo = $data['id_reclamo'];
    $respuesta = $data['respuesta'];
    $estado = $data['estado'];

    $query = "UPDATE reclamos_clientes SET respuesta = $1, estado = $2 WHERE id_reclamo = $3";
    $result = pg_query_params($conn, $query, array($respuesta, $estado, $id_reclamo));

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Reclamo actualizado y marcado como resuelto.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el reclamo.']);
    }

    pg_close($conn);
} else {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
}
?>
