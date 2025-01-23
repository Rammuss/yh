<?php
include "../conexion/configv2.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$cliente = isset($data['cliente']) ? $data['cliente'] : '';
$estado = isset($data['estado']) ? $data['estado'] : '';
$fecha = isset($data['fecha']) ? $data['fecha'] : '';

// Construir la consulta con filtros opcionales
$query = "SELECT os.id_orden, sc.id_cabecera AS id_solicitud, c.nombre AS cliente, c.ruc_ci AS cliente_ruc_ci, 
                 u.nombre_usuario AS trabajador, os.fecha, os.estado
          FROM orden_servicio os
          JOIN servicios_cabecera sc ON os.id_solicitud = sc.id_cabecera
          JOIN clientes c ON sc.id_cliente = c.id_cliente
          JOIN usuarios u ON os.id_trabajador = u.id
          WHERE 1=1";

$params = [];
$paramIndex = 1; // Usamos un índice para los placeholders ($1, $2, etc.)

// Filtro por cliente
if (!empty($cliente)) {
    $query .= " AND (c.nombre ILIKE $".$paramIndex." OR c.ruc_ci ILIKE $".($paramIndex + 1).")";
    $params[] = "%$cliente%"; // Usar % dentro del valor del parámetro
    $params[] = "%$cliente%";
    $paramIndex += 2;
}

// Filtro por estado
if (!empty($estado)) {
    $query .= " AND os.estado = $".$paramIndex;
    $params[] = $estado;
    $paramIndex++;
}

// Filtro por fecha
if (!empty($fecha)) {
    $query .= " AND os.fecha::date = $".$paramIndex."::date";
    $params[] = $fecha;
    $paramIndex++;
}

// Ejecutar la consulta con los parámetros
$result = pg_query_params($conn, $query, $params);

if ($result) {
    $ordenes = pg_fetch_all($result);
    echo json_encode(['success' => true, 'data' => $ordenes]);
} else {
    // Capturar y devolver el error de PostgreSQL para depuración
    echo json_encode(['success' => false, 'message' => 'Error al obtener las órdenes de servicio: ' . pg_last_error($conn)]);
}

// Cerrar la conexión
pg_close($conn);
?>
