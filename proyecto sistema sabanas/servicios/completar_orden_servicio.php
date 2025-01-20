<?php
include "../conexion/configv2.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id_orden'])) {
    $id_orden = $data['id_orden'];

    // Iniciar la transacción
    pg_query($conn, "BEGIN");

    try {
        // Obtener el id_solicitud de la orden de servicio
        $query_obtener_solicitud = "SELECT id_solicitud FROM orden_servicio WHERE id_orden = $1";
        $result_obtener_solicitud = pg_query_params($conn, $query_obtener_solicitud, array($id_orden));

        if (!$result_obtener_solicitud) {
            throw new Exception("Error al obtener la solicitud de servicio.");
        }

        $id_solicitud = pg_fetch_result($result_obtener_solicitud, 0, 'id_solicitud');

        // Actualizar el estado de la orden de servicio a 'completado'
        $query_completar_orden = "UPDATE orden_servicio SET estado = 'completado' WHERE id_orden = $1";
        $result_completar_orden = pg_query_params($conn, $query_completar_orden, array($id_orden));

        if (!$result_completar_orden) {
            throw new Exception("Error al completar la orden de servicio.");
        }

        // Actualizar el estado de la solicitud de servicio a 'completado'
        $query_completar_solicitud = "UPDATE servicios_cabecera SET estado = 'completado' WHERE id_cabecera = $1";
        $result_completar_solicitud = pg_query_params($conn, $query_completar_solicitud, array($id_solicitud));

        if (!$result_completar_solicitud) {
            throw new Exception("Error al completar la solicitud de servicio.");
        }

        // Confirmar la transacción
        pg_query($conn, "COMMIT");

        echo json_encode(['success' => true, 'message' => 'Orden de servicio y solicitud completadas correctamente.']);
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
