<?php
include("../conexion/config.php");

// Realiza la conexión
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Error en la conexión a la base de datos: " . pg_last_error());
}

// Inicia la transacción
pg_query($conn, "BEGIN");

try {
    // Recibir datos del formulario
    $proveedor = $_POST["proveedor"];
    $fecha_registro = $_POST["fecha_registro"];
    $fecha_vencimiento = $_POST["fecha_vencimiento"];
    $estado = $_POST["state"];

    // Prepara la consulta SQL para insertar el documento y otros detalles del presupuesto
    $sql = "INSERT INTO presupuestos (id_proveedor, fecharegistro, fechavencimiento, estado)
        VALUES ('$proveedor', '$fecha_registro', '$fecha_vencimiento', '$estado') RETURNING id_presupuesto";

    // Ejecuta la consulta SQL
    $result = pg_query($conn, $sql);

    if (!$result) {
        throw new Exception("Error al insertar datos generales: " . pg_last_error($conn));
    }

    $row = pg_fetch_assoc($result);
    $id_presupuesto = intval($row['id_presupuesto']);

    $productos = json_decode($_POST['productos'], true);

    if (!empty($productos) && is_array($productos)) {
        foreach ($productos as $producto) {
            $id_producto = intval($producto['producto_id']);
            $cantidad = intval($producto['cantidad']);
            $precioUnitario = floatval($producto['precioUnitario']);

            // Inserta los detalles del presupuesto
            $sql = "INSERT INTO presupuesto_detalle (id_presupuesto, id_producto, cantidad, precio_unitario, precio_total) 
                    VALUES ($id_presupuesto, $id_producto, $cantidad, $precioUnitario, $cantidad * $precioUnitario)";

            $result = pg_query($conn, $sql);
            if (!$result) {
                throw new Exception("Error al insertar detalles: " . pg_last_error($conn));
            }
        }
    } else {
        throw new Exception("No se recibieron productos para insertar.");
    }

    // Si todo ha ido bien, se confirma la transacción
    pg_query($conn, "COMMIT");

    // Redirige a una página de éxito o muestra un mensaje de éxito al usuario
    header('Location: registrar_presupuesto_proveedor.html?status=success');
    exit;
} catch (Exception $e) {
    // Si ocurre un error, se realiza un rollback
    pg_query($conn, "ROLLBACK");
    echo $e->getMessage();
}

// Cierra la conexión a la base de datos
pg_close($conn);
?>
