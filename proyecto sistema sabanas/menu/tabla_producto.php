<?php
include("../conexion/config.php");

// Realiza la conexión
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");


if (!$conn) {
    die("Error en la conexión a la base de datos: " . pg_last_error());
}




$sql = "SELECT id_producto, nombre, precio_unitario, precio_compra, tipo_iva FROM producto WHERE estado = 'Activo'";



$result = pg_query($conn, $sql);

$data = array();

while ($row = pg_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);

pg_close($conn);

?>
