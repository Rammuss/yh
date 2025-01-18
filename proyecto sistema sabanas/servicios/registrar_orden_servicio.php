<?php
include "../conexion/configv2.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id_solicitud']) && isset($data['id_trabajador'])) {
    $id_solicitud = $data['id_solicitud'];
    $id_trabajador = $data['id_trabajador'];

    // Iniciar la transacción
    pg_query($conn, "BEGIN");

    try {
        // Insertar en la tabla orden_servicio con estado 'en proceso'
        $query_orden = "INSERT INTO orden_servicio (id_solicitud, id_trabajador, estado) 
                        VALUES ($1, $2, 'en proceso') RETURNING id_orden";
        $result_orden = pg_query_params($conn, $query_orden, array($id_solicitud, $id_trabajador));

        if (!$result_orden) {
            throw new Exception("Error al registrar la orden de servicio.");
        }

        $id_orden = pg_fetch_result($result_orden, 0, 'id_orden');

        // Actualizar el estado de la solicitud a 'en proceso'
        $query_solicitud = "UPDATE servicios_cabecera SET estado = 'en proceso' WHERE id_cabecera = $1";
        $result_solicitud = pg_query_params($conn, $query_solicitud, array($id_solicitud));

        if (!$result_solicitud) {
            throw new Exception("Error al actualizar el estado de la solicitud.");
        }

        // Confirmar la transacción
        pg_query($conn, "COMMIT");

        echo json_encode(['success' => true, 'message' => 'Orden de servicio registrada y solicitud actualizada correctamente.', 'id_orden' => $id_orden]);
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        pg_query($conn, "ROLLBACK");
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    pg_close($conn);
} else {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
}
?>
