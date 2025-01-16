<?php
header("Content-Type: application/json");

// Incluir la conexión a la base de datos
include "../../conexion/configv2.php";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos de la solicitud
    $input = json_decode(file_get_contents("php://input"), true);

    

    // Preparar y ejecutar la consulta
    $nombre = pg_escape_string($input['nombre']);
    $precio = pg_escape_string($input['precio']);
    $query = "INSERT INTO promociones (nombre, precio, estado) VALUES ($1, $2, 'inactivo')";
    $result = pg_query_params($conn, $query, [$nombre, $precio]);

    // Verificar si la inserción fue exitosa
    if ($result) {
        echo json_encode(["success" => "Promoción registrada con éxito."]);
    } else {
        echo json_encode(["error" => "Error al registrar la promoción: " . pg_last_error($conn)]);
    }

    // Cerrar la conexión
    pg_close($conn);
} else {
    echo json_encode(["error" => "Método no permitido."]);
}
?>
