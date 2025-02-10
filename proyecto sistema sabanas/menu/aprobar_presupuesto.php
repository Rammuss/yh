<?php
// Configuración de conexión a PostgreSQL
include("../conexion/config.php");

// Crear una cadena de conexión
$conn_string = "host=$host dbname=$dbname user=$user password=$password";

// Establecer la conexión a la base de datos
$conn = pg_connect($conn_string);

if (!$conn) {
    // Manejo de errores de conexión
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error en la conexión a la base de datos']);
    exit;
}

// Obtener el ID del presupuesto desde la consulta GET
$id_presupuesto = $_GET['id_presupuesto'];

// 🔹 Corrección: Pasar la conexión explícitamente
$id_presupuesto = pg_escape_string($conn, $id_presupuesto);

// Preparar la consulta SQL para actualizar el estado
$sql = "
    UPDATE public.presupuestos
    SET estado = 'Aprobado'
    WHERE id_presupuesto = $1
";

// Preparar la consulta
$result = pg_prepare($conn, "update_presupuesto", $sql);
if (!$result) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error en la preparación de la consulta SQL']);
    pg_close($conn);
    exit;
}

// Ejecutar la consulta con el parámetro
$result = pg_execute($conn, "update_presupuesto", array($id_presupuesto));

if (!$result) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error al actualizar el presupuesto']);
    pg_close($conn);
    exit;
}

// Confirmación de éxito
header('Content-Type: application/json');
echo json_encode(['success' => true]);

// Cerrar la conexión
pg_close($conn);
?>
