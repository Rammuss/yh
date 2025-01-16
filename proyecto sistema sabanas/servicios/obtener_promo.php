<?php
header('Content-Type: application/json');

include "../conexion/configv2.php";

$query = "SELECT id_promocion, nombre, precio FROM promociones WHERE estado = 'activo'";
$result = pg_query($conn, $query);

$promotions = [];
while ($row = pg_fetch_assoc($result)) {
    $promotions[] = $row;
}

pg_close($conn);
echo json_encode($promotions);
?>
