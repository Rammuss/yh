<?php
include "../conexion/configv2.php";
header('Content-Type: application/json');

$query = "SELECT sc.id_cabecera AS id_solicitud, c.nombre AS cliente_nombre, c.ruc_ci AS cliente_ruc 
          FROM servicios_cabecera sc
          JOIN clientes c ON sc.id_cliente = c.id_cliente 
          WHERE sc.estado = 'pendiente'";
$result = pg_query($conn, $query);

if ($result) {
    $solicitudes = pg_fetch_all($result);
    echo json_encode(['success' => true, 'data' => $solicitudes]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al obtener las solicitudes aprobadas.']);
}

pg_close($conn);
?>
