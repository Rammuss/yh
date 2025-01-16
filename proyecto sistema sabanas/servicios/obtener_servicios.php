<?php
include "../conexion/configv2.php";
header('Content-Type: application/json');



$query = "SELECT id, nombre ,costo FROM servicios";
$result = pg_query($conn, $query);

$services = [];
while ($row = pg_fetch_assoc($result)) {
    $services[] = $row;
}

pg_close($conn);
echo json_encode($services);
?>
