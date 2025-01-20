<?php
include "../conexion/configv2.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$cliente = isset($data['cliente']) ? $data['cliente'] : '';
$estado = isset($data['estado']) ? $data['estado'] : '';
$fecha = isset($data['fecha']) ? $data['fecha'] : '';

// Construir la consulta con filtros opcionales
$query = "SELECT os.id_orden, sc.id_cabecera AS id_solicitud, c.nombre AS cliente, c.ruc_ci AS cliente_ruc_ci, u.nombre_usuario AS trabajador, os.fecha, os.estado
          FROM orden_servicio os
          JOIN servicios_cabecera sc ON os.id_solicitud = sc.id_cabecera
          JOIN clientes c ON sc.id_cliente = c.id_cliente
          JOIN usuarios u ON os.id_trabajador = u.id
          WHERE 1=1";

$params = array();

if (!empty($cliente)) {
    $query .= " AND (c.nombre ILIKE $1 OR c.ruc_ci ILIKE $2)";
    $params[] = '%'.$cliente.'%';
    $params[] = '%'.$cliente.'%';
}
if (!empty($estado)) {
    $query .= " AND os.estado = $3";
    $params[] = $estado;
}
if (!empty($fecha)) {
    $query .= " AND os.fecha::date = $4::date";
    $params[] = $fecha;
}

$result = pg_query_params($conn, $query, $params);

if ($result) {
    $ordenes = pg_fetch_all($result);
    echo json_encode(['success' => true, 'data' => $ordenes]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al obtener las órdenes de servicio.']);
}

pg_close($conn);
?>
