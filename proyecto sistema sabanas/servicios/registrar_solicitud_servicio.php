<?php
// Obtener los datos enviados desde el front-end
$data = json_decode(file_get_contents('php://input'), true);

// Extraer los valores del JSON
$idClienteSeleccionado = $data['idClienteSeleccionado'];
$fecha = $data['fecha'];
$descuento = isset($data['descuento']) ? $data['descuento'] : 0; // Si no hay descuento, se asigna 0
$servicios = $data['servicios'];
$costos = $data['costos'];
$promociones = isset($data['promociones']) ? $data['promociones'] : [];
$precios = isset($data['precios']) ? $data['precios'] : [];

// Conexión a la base de datos
include "../conexion/configv2.php";

// Calcular el total de servicios antes de aplicar el descuento
$totalServicios = 0;
foreach ($costos as $costo) {
    $totalServicios += $costo;
}

// Aplicar el descuento si existe
$montoFinal = $totalServicios - ($totalServicios * ($descuento / 100));

// Iniciar la transacción
pg_query($conn, "BEGIN");

try {
    // Insertar la cabecera (servicios_cabecera)
    $query = "INSERT INTO servicios_cabecera (id_cliente, fecha, monto_total, descuento) 
              VALUES ($idClienteSeleccionado, '$fecha', $montoFinal, $descuento) RETURNING id_cabecera";
    $result = pg_query($conn, $query);
    if (!$result) {
        throw new Exception("Error al insertar cabecera: " . pg_last_error());
    }
    $idCabecera = pg_fetch_result($result, 0, 'id_cabecera');

    // Insertar los detalles (servicios_detalle) en filas separadas para cada servicio y promoción
    foreach ($servicios as $index => $servicio) {
        $idServicio = $servicio;
        $costoServicio = $costos[$index];
        $idPromocion = isset($promociones[$index]) ? $promociones[$index] : null;
        $costoPromocion = isset($precios[$index]) ? $precios[$index] : null;

        // Insertar el servicio normal (sin promoción) si tiene costo
        $queryDetalleServicio = "INSERT INTO servicios_detalle (id_cabecera, id_servicio, costo_servicio, costo_promocion) 
                                 VALUES ($idCabecera, $idServicio, $costoServicio, NULL)";
        $resultDetalleServicio = pg_query($conn, $queryDetalleServicio);
        if (!$resultDetalleServicio) {
            throw new Exception("Error al insertar detalle sin promoción: " . pg_last_error());
        }

        // Si hay promoción, insertar también la fila correspondiente con el costo promocional
        if ($idPromocion && $costoPromocion !== null) {
            $queryDetallePromocion = "INSERT INTO servicios_detalle (id_cabecera, id_servicio, costo_servicio, costo_promocion) 
                                      VALUES ($idCabecera, $idServicio, $costoServicio, '$costoPromocion')";
            $resultDetallePromocion = pg_query($conn, $queryDetallePromocion);
            if (!$resultDetallePromocion) {
                throw new Exception("Error al insertar detalle con promoción: " . pg_last_error());
            }
        }
    }

    // Confirmar la transacción
    pg_query($conn, "COMMIT");

    echo json_encode(["status" => "success", "message" => "Registros insertados correctamente"]);

} catch (Exception $e) {
    // En caso de error, revertir la transacción
    pg_query($conn, "ROLLBACK");
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

// Cerrar la conexión
pg_close($conn);
?>
