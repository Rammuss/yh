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

$totalServicios = 0;

// Sumar los costos
foreach ($costos as $costo) {
    $totalServicios += $costo;
}

// Sumar los precios, si existen
foreach ($precios as $precio) {
    $totalServicios += $precio;
}

// Aplicar el descuento si no hay precios
if (empty($precios) && count($costos) > 0) {
    $montoFinal = $totalServicios - ($totalServicios * ($descuento / 100));
} else {
    $montoFinal = $totalServicios;
}

// Iniciar la transacción
pg_query($conn, "BEGIN");

try {
    // Insertar la cabecera (presupuesto_cabecera_servicio)
    $query = "INSERT INTO presupuesto_cabecera_servicio (id_cliente, fecha, monto_total, descuento) 
              VALUES ($1, $2, $3, $4) RETURNING id_cabecera";
    $result = pg_query_params($conn, $query, array($idClienteSeleccionado, $fecha, $montoFinal, $descuento));
    if (!$result) {
        throw new Exception("Error al insertar cabecera: " . pg_last_error($conn));
    }
    $idCabecera = pg_fetch_result($result, 0, 'id_cabecera');

    // Insertar los detalles (presupuesto_detalle_servicio) en filas separadas para cada servicio y promoción
    foreach ($servicios as $index => $servicio) {
        $idServicio = isset($servicio) ? $servicio : null;
        $costoServicio = isset($costos[$index]) ? $costos[$index] : null;
        $idPromocion = isset($promociones[$index]) ? $promociones[$index] : null;
        $costoPromocion = isset($precios[$index]) ? $precios[$index] : null;

        // Insertar el servicio normal (sin promoción) si tiene costo
        if ($idServicio !== null && $costoServicio !== null) {
            $queryDetalleServicio = "INSERT INTO presupuesto_detalle_servicio (id_cabecera, id_servicio, costo_servicio, id_promocion, costo_promocion) 
                                     VALUES ($1, $2, $3, NULL, NULL)";
            $resultDetalleServicio = pg_query_params($conn, $queryDetalleServicio, array($idCabecera, $idServicio, $costoServicio));
            if (!$resultDetalleServicio) {
                throw new Exception("Error al insertar detalle sin promoción: " . pg_last_error($conn));
            }
        }

        // Insertar la promoción si existe
        if ($idPromocion !== null && $costoPromocion !== null) {
            $queryDetallePromocion = "INSERT INTO presupuesto_detalle_servicio (id_cabecera, id_servicio, costo_servicio, id_promocion, costo_promocion) 
                                      VALUES ($1, NULL, NULL, $2, $3)";
            $resultDetallePromocion = pg_query_params($conn, $queryDetallePromocion, array($idCabecera, $idPromocion, $costoPromocion));
            if (!$resultDetallePromocion) {
                throw new Exception("Error al insertar detalle con promoción: " . pg_last_error($conn));
            }
        }
    }

    // Insertar promociones que no tienen un servicio asociado
    foreach ($promociones as $index => $promocion) {
        if (!isset($servicios[$index])) {
            $idPromocion = $promocion;
            $costoPromocion = isset($precios[$index]) ? $precios[$index] : null;

            if ($costoPromocion !== null) {
                $queryDetallePromocion = "INSERT INTO presupuesto_detalle_servicio (id_cabecera, id_servicio, costo_servicio, id_promocion, costo_promocion) 
                                          VALUES ($1, NULL, NULL, $2, $3)";
                $resultDetallePromocion = pg_query_params($conn, $queryDetallePromocion, array($idCabecera, $idPromocion, $costoPromocion));
                if (!$resultDetallePromocion) {
                    throw new Exception("Error al insertar detalle de solo promoción: " . pg_last_error($conn));
                }
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
