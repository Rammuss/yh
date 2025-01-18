<?php
include "../conexion/configv2.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$ci = $data['ci'] ?? null;
$fecha = $data['fecha'] ?? null;
$estado = $data['estado'] ?? null;

$query = "SELECT * FROM reclamos_clientes WHERE 1=1";
$params = [];
$paramIndex = 1;

if ($ci) {
    $query .= " AND id_cliente IN (SELECT id_cliente FROM clientes WHERE ruc_ci ILIKE $" . $paramIndex++ . "::varchar)";
    $params[] = '%' . $ci . '%';
}
if ($fecha) {
    $query .= " AND fecha_reclamo = $" . $paramIndex++ . "::date";
    $params[] = $fecha;
}
if ($estado) {
    $query .= " AND estado = $" . $paramIndex++ . "::varchar";
    $params[] = $estado;
}

$result = pg_query_params($conn, $query, $params);

if ($result) {
    $reclamos = pg_fetch_all($result);
    echo json_encode(['success' => true, 'data' => $reclamos]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al obtener los reclamos.']);
}

pg_close($conn);
?>
