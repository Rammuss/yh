<?php
// Configuración de la conexión a PostgreSQL
include '../conexion/configv2.php';

// Leer el cuerpo JSON enviado por el frontend
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Verificar si el JSON es válido
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "El formato del JSON es inválido: " . json_last_error_msg()
    ]);
    exit;
}

// Verificar que los parámetros obligatorios están presentes en el JSON
if (
    empty($data['cabecera']['id_cliente']) ||
    empty($data['cabecera']['forma_pago']) ||
    !isset($data['cabecera']['cantidad_cuotas']) || // Permitir 0 como valor válido
    empty($data['detalle']) ||
    empty($data['cabecera']['fecha_venta']) ||
    empty($data['cabecera']['metodo_pago']) // Verificar el nuevo campo
) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Faltan parámetros obligatorios en el JSON."
    ]);
    exit;
}

// Acceder a los valores dentro de 'cabecera'
$cliente_id = (int) $data['cabecera']['id_cliente'];
$forma_pago = $data['cabecera']['forma_pago'];
$cuotas = (int) $data['cabecera']['cantidad_cuotas'];
$fecha = $data['cabecera']['fecha_venta'];
$metodo_pago = $data['cabecera']['metodo_pago']; // Nuevo campo
$solicitud_id = isset($data['cabecera']['solicitud_id']) ? (int) $data['cabecera']['solicitud_id'] : null; // Nuevo campo opcional
$monto_total_servicios = isset($data['cabecera']['monto_total_servicios']) ? (float) $data['cabecera']['monto_total_servicios'] : null; // Nuevo campo opcional
$nota_credito_id = isset($data['cabecera']['nota_credito_id']) ? (int) $data['cabecera']['nota_credito_id'] : null;

// Validar el formato de la fecha
if (!strtotime($fecha)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "El formato de la fecha es inválido."
    ]);
    exit;
}

// Si se proporciona una nota de crédito, validar su estado
if ($nota_credito_id !== null) {
    $query_nota = "SELECT estado FROM notas_credito_debito WHERE id = $1";
    $result_nota = pg_query_params($conn, $query_nota, [$nota_credito_id]);

    if ($result_nota) {
        $nota_estado = pg_fetch_result($result_nota, 0, 'estado');

        if ($nota_estado === 'aplicada') {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "La nota de crédito ya ha sido aplicada y no puede usarse nuevamente."
            ]);
            pg_close($conn);
            exit;
        }
    } else {
        $error = pg_last_error($conn);
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Error al verificar la nota de crédito: $error"
        ]);
        pg_close($conn);
        exit;
    }
}

// Acceder a los detalles de la venta y convertir a JSON
$detalles_pg = json_encode($data['detalle']);
if ($detalles_pg === false) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Error al convertir los detalles a JSON: " . json_last_error_msg()
    ]);
    exit;
}

// Preparar la llamada al SP
$query = "
    SELECT public.generar_venta(
        $1, $2, $3, $4::jsonb, $5::timestamp, $6, $7, $8, $9
    ) AS venta_id
";

$params = [
    $cliente_id,          // $1
    $forma_pago,          // $2
    $cuotas,              // $3
    $detalles_pg,         // $4
    $fecha,               // $5
    $metodo_pago,         // $6
    $nota_credito_id,     // $7
    $solicitud_id,        // $8
    $monto_total_servicios // $9
];

// Ajustar los parámetros opcionales para la llamada al SP
if ($solicitud_id === null) {
    $params[7] = null;
}
if ($monto_total_servicios === null) {
    $params[8] = null;
}

// Ejecutar la consulta
$result = pg_query_params($conn, $query, $params);

if ($result) {
    // Obtener el `venta_id` del resultado del SP
    $venta_id = pg_fetch_result($result, 0, 'venta_id');

    echo json_encode([
        "success" => true,
        "message" => "Venta procesada correctamente. $venta_id",
        "venta_id" => $venta_id
    ]);
} else {
    $error = pg_last_error($conn);
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error al procesar la venta: $error"
    ]);
}

// Cerrar la conexión
pg_close($conn);
?>
