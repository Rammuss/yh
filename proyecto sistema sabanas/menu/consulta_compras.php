<?php
// Incluir el archivo de configuración
include("../conexion/config.php");

// Conexión a la base de datos PostgreSQL
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Error al conectar a la base de datos: " . pg_last_error());
}

// Ejecutar la consulta para obtener los id_compra
$query = "SELECT id_compra FROM compras ORDER BY id_compra DESC";
$result = pg_query($conn, $query);

if (!$result) {
    die("Error en la consulta: " . pg_last_error());
}

// Crear un array para almacenar los resultados
$compras = [];

while ($row = pg_fetch_assoc($result)) {
    $compras[] = $row;
}

// Devolver los resultados en formato JSON
echo json_encode($compras);

// Cerrar la conexión
pg_close($conn);
?>
