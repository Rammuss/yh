<?php
include "../conexion/configv2.php";

// Obtener los IDs de la orden y la solicitud de la URL
$id_orden = isset($_GET['id_orden']) ? $_GET['id_orden'] : '';
$id_solicitud = isset($_GET['id_solicitud']) ? $_GET['id_solicitud'] : '';

if ($id_orden && $id_solicitud) {
    // Consultar los datos de la orden de trabajo
    $query_orden = "SELECT os.id_orden, os.fecha, os.estado, os.observaciones, 
                            c.nombre AS cliente_nombre, c.ruc_ci AS cliente_ruc_ci, 
                            u.nombre_usuario AS trabajador_nombre
                     FROM orden_servicio os
                     JOIN servicios_cabecera sc ON os.id_solicitud = sc.id_cabecera
                     JOIN clientes c ON sc.id_cliente = c.id_cliente
                     JOIN usuarios u ON os.id_trabajador = u.id
                     WHERE os.id_orden = $1";
    $result_orden = pg_query_params($conn, $query_orden, array($id_orden));

    if ($result_orden) {
        $orden = pg_fetch_assoc($result_orden);

        // Consultar los detalles de los servicios
        $query_detalles = "SELECT sd.id_detalle, 
                                  COALESCE(s.nombre, p.nombre, 'N/A') AS servicio_insumo,
                                  CASE 
                                      WHEN sd.id_servicio IS NOT NULL THEN sd.costo_servicio
                                      WHEN sd.id_promocion IS NOT NULL THEN sd.costo_promocion
                                      ELSE 0
                                  END AS costo_unitario,
                                  1 AS cantidad, -- Asumiendo 1 unidad por detalle
                                  CASE 
                                      WHEN sd.id_servicio IS NOT NULL THEN sd.costo_servicio
                                      WHEN sd.id_promocion IS NOT NULL THEN sd.costo_promocion
                                      ELSE 0
                                  END AS total
                           FROM servicios_detalle sd
                           LEFT JOIN servicios s ON sd.id_servicio = s.id
                           LEFT JOIN promociones p ON sd.id_promocion = p.id_promocion
                           WHERE sd.id_cabecera = $1";
        $result_detalles = pg_query_params($conn, $query_detalles, array($id_solicitud));

        $detalles_html = "";
        if ($result_detalles) {
            while ($detalle = pg_fetch_assoc($result_detalles)) {
                $detalles_html .= "
                    <tr>
                        <td>{$detalle['servicio_insumo']}</td>
                        <td>{$detalle['cantidad']}</td>
                        <td>{$detalle['costo_unitario']}</td>
                        <td>{$detalle['total']}</td>
                    </tr>
                ";
            }
        } else {
            $detalles_html = "<tr><td colspan='4'>No se encontraron detalles de servicios.</td></tr>";
        }

        // Generar el HTML
        $html = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                h1 { text-align: center; }
                .container { margin: 20px; }
                .buttons { text-align: center; margin-top: 20px; }
                .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                .table th, .table td { border: 1px solid black; padding: 8px; text-align: left; }
            </style>
        </head>
        <body>
            <h1>Orden de Trabajo</h1>
            <div class='container'>
                <p><strong>ID Orden:</strong> {$orden['id_orden']}</p>
                <p><strong>Fecha:</strong> {$orden['fecha']}</p>
                <p><strong>Estado:</strong> {$orden['estado']}</p>
                <p><strong>Cliente:</strong> {$orden['cliente_nombre']} (RUC/CI: {$orden['cliente_ruc_ci']})</p>
                <p><strong>Trabajador Asignado:</strong> {$orden['trabajador_nombre']}</p>
                <p><strong>Observaciones:</strong> {$orden['observaciones']}</p>

                <h2>Detalles de Servicios</h2>
                <table class='table'>
                    <thead>
                        <tr>
                            <th>Servicio/Insumo</th>
                            <th>Cantidad</th>
                            <th>Costo Unitario</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        $detalles_html
                    </tbody>
                </table>

                <div class='buttons'>
                    <button onclick='window.history.back()'>Atrás</button>
                    <button onclick='window.print()'>Imprimir</button>
                </div>
            </div>
        </body>
        </html>
        ";

        echo $html;
    } else {
        echo "Error al obtener los datos de la orden de trabajo.";
    }

    pg_close($conn);
} else {
    echo "IDs de orden y/o solicitud no proporcionados.";
}
?>
