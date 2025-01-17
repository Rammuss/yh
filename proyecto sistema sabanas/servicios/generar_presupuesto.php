<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presupuesto de Servicio</title>
    <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.3/css/bulma.min.css" rel="stylesheet">
</head>
<body>
    <section class="section">
        <div class="container">

            <?php
            // Conexión a la base de datos PostgreSQL
            include "../conexion/configv2.php";

            // Recibir el ID del presupuesto desde la URL
            if (isset($_GET['id'])) {
                $id = $_GET['id'];

                // Consulta SQL para obtener los datos del presupuesto
                $sql = "
                    SELECT 
                        cab.id_cabecera, 
                        cab.fecha, 
                        cab.estado, 
                        cab.descuento, 
                        cab.monto_total, 
                        cli.nombre AS cliente_nombre, 
                        cli.apellido AS cliente_apellido, 
                        cli.direccion, 
                        cli.telefono, 
                        cli.ruc_ci
                    FROM public.servicios_cabecera cab
                    INNER JOIN public.clientes cli ON cab.id_cliente = cli.id_cliente
                    WHERE cab.id_cabecera = $1
                ";

                $result = pg_query_params($conn, $sql, array($id));

                // Verificar si se encontró la cabecera
                if (pg_num_rows($result) > 0) {
                    $cabecera = pg_fetch_assoc($result);

                    // Consulta para obtener los detalles de la solicitud
                    $sql_detalle = "
                        SELECT 
                            det.id_detalle, 
                            ser.nombre AS servicio, 
                            det.costo_servicio, 
                            pro.nombre AS promocion, 
                            det.costo_promocion
                        FROM public.servicios_detalle det
                        LEFT JOIN public.servicios ser ON det.id_servicio = ser.id
                        LEFT JOIN public.promociones pro ON det.id_promocion = pro.id_promocion
                        WHERE det.id_cabecera = $1
                    ";

                    $result_detalle = pg_query_params($conn, $sql_detalle, array($id));
                    $detalles = pg_fetch_all($result_detalle);
            ?>

            <h1 class="title">Presupuesto de Servicio</h1>
            <div class="box">
                <p><strong>Cliente:</strong> <?php echo $cabecera['cliente_nombre'] . " " . $cabecera['cliente_apellido']; ?></p>
                <p><strong>Dirección:</strong> <?php echo $cabecera['direccion']; ?></p>
                <p><strong>Teléfono:</strong> <?php echo $cabecera['telefono']; ?></p>
                <p><strong>RUC/CI:</strong> <?php echo $cabecera['ruc_ci']; ?></p>
                <p><strong>Fecha:</strong> <?php echo $cabecera['fecha']; ?></p>
                <p><strong>Estado:</strong> <?php echo $cabecera['estado']; ?></p>
                <p><strong>Descuento:</strong> <?php echo number_format($cabecera['descuento'], 2); ?></p>
                <p><strong>Monto Total:</strong> <?php echo number_format($cabecera['monto_total'], 2); ?></p>
            </div>

            <h2 class="subtitle">Detalles del Servicio</h2>
            <div class="table-container">
                <table class="table is-striped is-fullwidth">
                    <thead>
                        <tr>
                            <th>Servicio/Insumo</th>
                            <th>Promoción</th>
                            <th>Costo Servicio/Insumo</th>
                            <th>Costo Promoción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($detalles as $detalle) {
                            echo "<tr>
                                    <td>" . $detalle['servicio'] . "</td>
                                    <td>" . $detalle['promocion'] . "</td>
                                    <td>" . number_format($detalle['costo_servicio'], 2) . "</td>
                                    <td>" . number_format($detalle['costo_promocion'], 2) . "</td>
                                  </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="buttons">
                <button class="button is-info" onclick="window.history.back()">Atrás</button>
                <button class="button is-primary" onclick="window.print()">Imprimir</button>
            </div>

            <?php
                } else {
                    echo "<div class='notification is-danger'>No se encontró el presupuesto.</div>";
                }
            } else {
                echo "<div class='notification is-danger'>ID no proporcionado.</div>";
            }

            // Cerrar la conexión
            pg_close($conn);
            ?>

        </div>
    </section>
</body>
</html>
