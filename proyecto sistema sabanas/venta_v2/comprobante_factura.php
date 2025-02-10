<?php
// Conexión a la base de datos
include "../conexion/configv2.php";

// Obtener el ID de la venta desde el parámetro de la URL o formulario
$venta_id = isset($_GET['venta_id']) ? intval($_GET['venta_id']) : 0;

if ($venta_id === 0) {
    die("ID de venta no proporcionado.");
}

// Consulta para obtener los datos de la venta
$sql_venta = "
    SELECT 
        v.id AS id_venta,
        v.fecha,
        v.cliente_id,
        v.forma_pago,
        v.estado,
        v.cuotas,
        v.numero_factura,
        v.timbrado,
        v.solicitud_id,
        c.nombre AS cliente_nombre
    FROM 
        ventas v
    LEFT JOIN 
        clientes c ON v.cliente_id = c.id_cliente
    WHERE 
        v.id = $1
";

$result_venta = pg_query_params($conn, $sql_venta, [$venta_id]);

if (!$result_venta || pg_num_rows($result_venta) === 0) {
    die("No se encontró la venta con ese ID.");
}

$venta = pg_fetch_assoc($result_venta);

// Consulta para obtener los detalles de la venta
$sql_detalle_venta = "
    SELECT 
        dv.id AS id_detalle,
        dv.producto_id,
        dv.cantidad,
        dv.precio_unitario,
        p.nombre AS producto_nombre
    FROM 
        detalle_venta dv
    LEFT JOIN 
        producto p ON dv.producto_id = p.id_producto
    WHERE 
        dv.venta_id = $1
";

$result_detalle_venta = pg_query_params($conn, $sql_detalle_venta, [$venta_id]);

if (!$result_detalle_venta) {
    die("Error al obtener los detalles de la venta.");
}

$detalles_venta = pg_fetch_all($result_detalle_venta);

// Consulta para obtener los servicios relacionados (si existe solicitud_id)
$servicios = [];
if ($venta['solicitud_id']) {
    $sql_servicios = "
        SELECT 
            sd.id_detalle,
            sd.id_servicio,
            sd.costo_servicio,
            sd.id_promocion,
            sd.costo_promocion,
            s.nombre AS servicio_nombre,
            p.nombre AS promocion_nombre
        FROM 
            servicios_detalle sd
        LEFT JOIN 
            servicios s ON sd.id_servicio = s.id
        LEFT JOIN 
            promociones p ON sd.id_promocion = p.id_promocion
        WHERE 
            sd.id_cabecera = $1
    ";

    $result_servicios = pg_query_params($conn, $sql_servicios, [$venta['solicitud_id']]);

    if (!$result_servicios) {
        die("Error al obtener los servicios relacionados.");
    }

    $servicios = pg_fetch_all($result_servicios);
}

// Calcular monto total dinámicamente
$monto_total = 0;

// Sumar subtotales de productos
if ($detalles_venta) {
    foreach ($detalles_venta as $detalle) {
        $monto_total += $detalle['cantidad'] * $detalle['precio_unitario'];
    }
}

// Sumar costos de servicios
if ($servicios) {
    foreach ($servicios as $servicio) {
        $monto_total += $servicio['costo_servicio'] + $servicio['costo_promocion'];
    }
}

// Calcular IVA (10%)
$iva = $monto_total * 0.10;

// Calcular monto total final (con IVA)
$monto_total_final = $monto_total + $iva;

// Generar el comprobante
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Factura</title>
    <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        .buttons {
            margin-top: 20px;
        }

        .buttons .button {
            margin-right: 10px;
        }

        h1,
        h2 {
            text-align: center;
        }

        .columns .column {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <div class="container">
        <!-- Título -->
        <h1 class="title">Comprobante de Factura</h1>

        <!-- Fila para Datos de la Empresa y Datos de la Venta -->
        <div class="columns">
            <!-- Datos de la Empresa -->
            <div class="column is-half">
                <div class="box">
                    <h2 class="subtitle">Datos de la Empresa</h2>
                    <p><strong>Nombre:</strong> Beauty Creations</p>
                    <p><strong>RUC:</strong> 1234567890</p>
                    <p><strong>Dirección:</strong> Av. Principal 1234</p>
                    <p><strong>Teléfono:</strong> (012) 345-6789</p>
                </div>
            </div>

            <!-- Datos de la Venta -->
            <div class="column is-half">
                <div class="box">
                    <h2 class="subtitle">Datos de la Venta</h2>
                    <p><strong>ID Venta:</strong> <?= htmlspecialchars($venta['id_venta'] ?? '') ?></p>
                    <p><strong>Fecha:</strong> <?= htmlspecialchars($venta['fecha'] ?? '') ?></p>
                    <p><strong>Cliente:</strong> <?= htmlspecialchars($venta['cliente_nombre'] ?? '') ?></p>
                    <p><strong>Forma de Pago:</strong> <?= htmlspecialchars($venta['forma_pago'] ?? '') ?></p>
                    <p><strong>Número de Factura:</strong> <?= htmlspecialchars($venta['numero_factura'] ?? '') ?></p>
                    <p><strong>Timbrado:</strong> <?= htmlspecialchars($venta['timbrado'] ?? '') ?></p>
                </div>
            </div>
        </div>

        <!-- Detalles de Productos -->
        <div class="box">
            <h2 class="subtitle">Detalles de Productos</h2>
            <?php if ($detalles_venta): ?>
                <table class="table is-bordered is-striped is-hoverable">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detalles_venta as $detalle): ?>
                            <tr>
                                <td><?= htmlspecialchars($detalle['producto_nombre'] ?? '') ?></td>
                                <td><?= htmlspecialchars($detalle['cantidad'] ?? '') ?></td>
                                <td><?= htmlspecialchars($detalle['precio_unitario'] ?? '') ?></td>
                                <td><?= htmlspecialchars(number_format($detalle['cantidad'] * $detalle['precio_unitario'], 2)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No hay productos en esta venta.</p>
            <?php endif; ?>
        </div>

        <!-- Detalles de Servicios -->
        <div class="box">
            <h2 class="subtitle">Detalles de Servicios</h2>
            <?php if ($servicios): ?>
                <table class="table is-bordered is-striped is-hoverable">
                    <thead>
                        <tr>
                            <th>Servicio</th>
                            <th>Promoción</th>
                            <th>Costo Servicio</th>
                            <th>Costo Promoción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($servicios as $servicio): ?>
                            <tr>

                                <td><?= htmlspecialchars($servicio['servicio_nombre'] ?? '') ?></td>
                                <td><?= htmlspecialchars($servicio['promocion_nombre'] ?? '') ?></td>
                                <td><?= htmlspecialchars(number_format($servicio['costo_servicio'] ?? 0.00, 2)) ?></td>
                                <td><?= htmlspecialchars(number_format($servicio['costo_promocion'] ?? 0.00, 2)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No hay servicios relacionados con esta venta.</p>
            <?php endif; ?>
        </div>

        <!-- Resumen del Pago -->
        <div class="box">
            <h2 class="subtitle">Resumen del Pago</h2>
            <p><strong>Monto Total:</strong> <?= htmlspecialchars(number_format($monto_total, 2)) ?></p>
            <p><strong>IVA:</strong> <?= htmlspecialchars(number_format($iva, 2)) ?></p>
            <p><strong>Monto Total Final:</strong> <?= htmlspecialchars(number_format($monto_total_final, 2)) ?></p>
        </div>


        <!-- Botones -->
        <div class="buttons is-centered">
            <button class="button is-primary" onclick="window.history.back()">Atrás</button>
            <button class="button is-info" onclick="window.print()">Imprimir</button>
        </div>
    </div>

</body>

</html>