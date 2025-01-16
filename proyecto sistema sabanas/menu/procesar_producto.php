<?php
// Conexión a la base de datos PostgreSQL
include("../conexion/config.php");

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Error de conexión: " . pg_last_error());
}

// Recibir datos del formulario
$id_producto = $_POST["id_producto"];
$nombre = $_POST["nombre"];
$precio_unitario = $_POST["precio_unitario"];
$precio_compra = $_POST["precio_compra"];
$estado = $_POST["estado"] ?? 'Activo'; // Por defecto será 'Activo'

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    if ($action === 'insert') {
        // Lógica para la operación de inserción
        $sql = "INSERT INTO producto (id_producto, nombre, precio_unitario, precio_compra, estado) 
                VALUES ($id_producto, '$nombre', $precio_unitario, $precio_compra, '$estado')";

        $result = pg_query($conn, $sql);

        if ($result !== false) {
            $respuesta = "true";
        } else {
            $respuesta = "false";
        }
        header("Location: producto.html?respuesta=$respuesta");
    } elseif ($action === 'update') {
        // Lógica para la operación de actualización
        $sql = "UPDATE producto
                SET nombre = '$nombre', precio_unitario = $precio_unitario, precio_compra = $precio_compra, estado = '$estado'
                WHERE id_producto = $id_producto";

        $result = pg_query($conn, $sql);

        if ($result !== false) {
            $respuesta = "true";
        } else {
            $respuesta = "false";
        }
        header("Location: producto.html?respuesta=$respuesta");
    } elseif ($action === 'delete') {
        // Lógica para la operación de eliminación
        $sql = "DELETE FROM producto WHERE id_producto = $id_producto";

        $result = pg_query($conn, $sql);

        if ($result !== false) {
            $respuesta = "true";
        } else {
            $respuesta = "false";
        }
        header("Location: producto.html?respuesta=$respuesta");
    }
}

// Cerrar conexión
pg_close($conn);
?>
