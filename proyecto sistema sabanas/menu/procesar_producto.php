<?php
// Conexión a la base de datos PostgreSQL
include("../conexion/configv2.php");



// Recibir datos del formulario
$id_producto = $_POST["id_producto"];
$nombre = $_POST["nombre"];
$precio_unitario = $_POST["precio_unitario"];
$precio_compra = $_POST["precio_compra"];
$tipo_iva = $_POST["tipo_iva"]; // Nuevo campo
$estado = $_POST["estado"] ?? 'Activo'; // Por defecto será 'Activo'

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    if ($action === 'insert') {
        // Lógica para la operación de inserción
        $sql = "INSERT INTO producto ( nombre, precio_unitario, precio_compra, tipo_iva, estado) 
                VALUES ( '$nombre', $precio_unitario, $precio_compra, '$tipo_iva', '$estado')";

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
                SET nombre = '$nombre', 
                    precio_unitario = $precio_unitario, 
                    precio_compra = $precio_compra, 
                    tipo_iva = '$tipo_iva', 
                    estado = '$estado'
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
