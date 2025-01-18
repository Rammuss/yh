<?php
include "../conexion/configv2.php";
header('Content-Type: application/json');

$id_solicitud = $_GET['id'];

$query = "
    SELECT sd.id_detalle, 
           COALESCE(s.nombre, p.nombre, 'N/A') AS servicio_insumo,
           CASE 
               WHEN sd.id_servicio IS NOT NULL THEN sd.costo_servicio
               WHEN sd.id_promocion IS NOT NULL THEN sd.costo_promocion
               ELSE 0
           END AS costo_unitario,
           1 AS cantidad, -- Asumiendo 1 unidad por detalle
           CASE 
               WHEN sd.id_servicio IS NOT NULL THEN sd.costo_servicio
               WHEN sd.id_promocion IS NOT NULL THEN sd.costo_promocion
               ELSE 0
           END AS total
    FROM servicios_detalle sd
    LEFT JOIN servicios s ON sd.id_servicio = s.id
    LEFT JOIN promociones p ON sd.id_promocion = p.id_promocion
    WHERE sd.id_cabecera = $1";
$result = pg_query_params($conn, $query, array($id_solicitud));

if ($result) {
    $detalles = pg_fetch_all($result);
    echo json_encode(['success' => true, 'data' => $detalles]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al obtener los detalles de la solicitud.']);
}

pg_close($conn);
?>
