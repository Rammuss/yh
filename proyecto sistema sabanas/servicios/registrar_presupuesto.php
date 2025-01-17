<?php
// Obtener los datos enviados desde el front-end
$data = json_decode(file_get_contents('php://input'), true);

// Extraer los valores del JSON
$idClienteSeleccionado = $data['idClienteSeleccionado'];
$fecha = $data['fecha'];
$descuento = isset($data['descuento']) ? $data['descuento'] : 0; // Si no hay descuento, se asigna 0
$detalles = $data['detalles']; // Detalles del presupuesto (servicio, promoción, costos, etc.)

// Conexión a la base de datos
include "../conexion/configv2.php";

$montoTotal = 0;

// Calcular el monto total basado en los costos en los detalles
foreach ($detalles as $detalle) {
    $montoTotal += isset($detalle['costo_servicio']) ? $detalle['costo_servicio'] : 0;
    $montoTotal += isset($detalle['costo_promocion']) ? $detalle['costo_promocion'] : 0;
}

// Aplicar el descuento si corresponde
if ($descuento > 0) {
    $montoTotal -= $montoTotal * ($descuento / 100);
}

// Iniciar la transacción
pg_query($conn, "BEGIN");

try {
    // Insertar la cabecera del presupuesto
    $queryCabecera = "INSERT INTO presupuesto_cabecera (id_cliente, fecha, descuento, monto_total) 
                      VALUES ($idClienteSeleccionado, '$fecha', $descuento, $montoTotal) RETURNING id_presupuesto";
    $resultCabecera = pg_query($conn, $queryCabecera);
    if (!$resultCabecera) {
        throw new Exception("Error al insertar cabecera: " . pg_last_error($conn));
    }
    $idPresupuesto = pg_fetch_result($resultCabecera, 0, 'id_presupuesto');

    // Insertar los detalles del presupuesto
    foreach ($detalles as $detalle) {
        $idServicio = isset($detalle['id_servicio']) ? $detalle['id_servicio'] : null;
        $costoServicio = isset($detalle['costo_servicio']) ? $detalle['costo_servicio'] : null;
        $idPromocion = isset($detalle['id_promocion']) ? $detalle['id_promocion'] : null;
        $costoPromocion = isset($detalle['costo_promocion']) ? $detalle['costo_promocion'] : null;

        $queryDetalle = "INSERT INTO presupuesto_detalle (id_presupuesto, id_servicio, costo_servicio, id_promocion, costo_promocion) 
                         VALUES ($idPresupuesto, 
                                 " . ($idServicio ? $idServicio : "NULL") . ", 
                                 " . ($costoServicio ? $costoServicio : "NULL") . ", 
                                 " . ($idPromocion ? $idPromocion : "NULL") . ", 
                                 " . ($costoPromocion ? $costoPromocion : "NULL") . ")";
        $resultDetalle = pg_query($conn, $queryDetalle);
        if (!$resultDetalle) {
            throw new Exception("Error al insertar detalle: " . pg_last_error($conn));
        }
    }

    // Confirmar la transacción
    pg_query($conn, "COMMIT");

    // Redirigir al archivo para generar el presupuesto
    echo json_encode(["status" => "success", "redirect" => "ruta_a_generar_presupuesto.php?id=$idPresupuesto"]);

} catch (Exception $e) {
    // Revertir la transacción en caso de error
    pg_query($conn, "ROLLBACK");
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

// Cerrar la conexión
pg_close($conn);
