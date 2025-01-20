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

        // Actualizar el estado de la orden de servicio a 'cancelado'
        $query_cancelar_orden = "UPDATE orden_servicio SET estado = 'cancelado' WHERE id_orden = $1";
        $result_cancelar_orden = pg_query_params($conn, $query_cancelar_orden, array($id_orden));

        if (!$result_cancelar_orden) {
            throw new Exception("Error al cancelar la orden de servicio.");
        }

        // Actualizar el estado de la solicitud de servicio a 'cancelado'
        $query_cancelar_solicitud = "UPDATE servicios_cabecera SET estado = 'cancelado' WHERE id_cabecera = $1";
        $result_cancelar_solicitud = pg_query_params($conn, $query_cancelar_solicitud, array($id_solicitud));

        if (!$result_cancelar_solicitud) {
            throw new Exception("Error al cancelar la solicitud de servicio.");
        }

        // Confirmar la transacción
        pg_query($conn, "COMMIT");

        echo json_encode(['success' => true, 'message' => 'Orden de servicio y solicitud canceladas correctamente.']);
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
