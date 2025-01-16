<?php
header('Content-Type: application/json');

// Incluir el archivo de configuración para la conexión a la base de datos
include "../conexion/configv2.php";
// Consulta para obtener los descuentos con estado 'activo'
$query = "SELECT id_descuento, nombre, porcentaje FROM descuentos WHERE estado = 'activo'";
$result = pg_query($conn, $query);

$discounts = [];
while ($row = pg_fetch_assoc($result)) {
    $discounts[] = $row;
}

// Cerrar la conexión a la base de datos
pg_close($conn);

// Devolver los descuentos en formato JSON
echo json_encode($discounts);
?>
