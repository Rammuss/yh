<?php
// Configuración de la conexión a PostgreSQL
include '../conexion/configv2.php';

// Consulta para obtener las solicitudes de servicios completados
$query = "SELECT sc.id_cabecera, c.nombre AS cliente_nombre, c.ruc_ci, sc.monto_total
          FROM servicios_cabecera sc
          JOIN clientes c ON sc.id_cliente = c.id_cliente
          WHERE sc.estado = 'completado'";

$result = pg_query($conn, $query);

$solicitudes = [];

if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $solicitudes[] = $row;
    }
}

echo json_encode($solicitudes);

// Cerrar la conexión
pg_close($conn);
?>
