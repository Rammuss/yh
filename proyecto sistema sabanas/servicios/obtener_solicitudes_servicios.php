<?php
// Conexión a la base de datos
include "../conexion/configv2.php";

// Establecer el tipo de contenido como JSON
header("Content-Type: application/json");

try {
    // Leer los datos enviados desde el frontend
    $data = json_decode(file_get_contents('php://input'), true);

    // Validar que se haya recibido un JSON válido
    if (!$data) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Datos no válidos."]);
        exit;
    }

    // Extraer los valores de los filtros
    $fecha = $data['fecha'] ?? null;
    $estado = $data['estado'] ?? null;
    $ci = $data['ci'] ?? null;

    // Construir la consulta SQL dinámica
    $query = "SELECT sc.id_cabecera AS id, 
                     c.nombre AS cliente, 
                     c.ruc_ci AS ci, 
                     sc.fecha, 
                     sc.monto_total, 
                     sc.descuento, 
                     sc.estado 
              FROM servicios_cabecera sc
              JOIN clientes c ON sc.id_cliente = c.id_cliente 
              WHERE 1=1";

    $params = [];
    $paramIndex = 1; // Usaremos índices dinámicos para los parámetros

    if ($fecha) {
        $query .= " AND sc.fecha = $" . $paramIndex++ . "::date"; // Casting explícito a DATE
        $params[] = $fecha;
    }
    if ($estado) {
        $query .= " AND sc.estado = $" . $paramIndex++ . "::varchar"; // Casting explícito a VARCHAR
        $params[] = $estado;
    }
    if ($ci) {
        $query .= " AND c.ruc_ci ILIKE $" . $paramIndex++ . "::varchar"; // Casting explícito a VARCHAR
        $params[] = '%' . $ci . '%';
    }

    // Ejecutar la consulta de manera segura con parámetros
    $result = pg_query_params($conn, $query, $params);

    if (!$result) {
        throw new Exception("Error en la consulta: " . pg_last_error($conn));
    }

    // Construir el array de resultados
    $solicitudes = [];
    while ($row = pg_fetch_assoc($result)) {
        $solicitudes[] = $row;
    }

    // Retornar los datos en formato JSON
    echo json_encode(["success" => true, "data" => $solicitudes]);

} catch (Exception $e) {
    // Enviar error al frontend
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
} finally {
    // Cerrar la conexión
    pg_close($conn);
}
?>
