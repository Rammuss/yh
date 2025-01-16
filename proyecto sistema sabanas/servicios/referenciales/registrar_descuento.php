<?php
header("Content-Type: application/json");

// Incluir la conexión a la base de datos
include "../../conexion/configv2.php";

// Validar el método de solicitud
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Leer los datos JSON enviados en el cuerpo de la solicitud
    $input = json_decode(file_get_contents("php://input"), true);

    // Validar que los datos necesarios estén presentes
    if (!isset($input['nombre']) || !isset($input['valor'])) {
        echo json_encode(["error" => "El nombre y el porcentaje son obligatorios."]);
        exit;
    }

    // Escapar los datos para evitar inyección SQL
    $nombre = pg_escape_string($conn, $input['nombre']);
    $porcentaje = pg_escape_string($conn, $input['valor']);

    // Preparar y ejecutar la consulta
    $query = "INSERT INTO descuentos (nombre, porcentaje, estado) VALUES ($1, $2, 'inactivo')";
    $result = pg_query_params($conn, $query, [$nombre, $porcentaje]);

    // Verificar si la inserción fue exitosa
    if ($result) {
        echo json_encode(["success" => "Descuento registrado con éxito."]);
    } else {
        echo json_encode(["error" => "Error al registrar el descuento: " . pg_last_error($conn)]);
    }

    // Cerrar la conexión
    pg_close($conn);
} else {
    // Responder con un error si el método no es POST
    echo json_encode(["error" => "Método no permitido."]);
}
?>
