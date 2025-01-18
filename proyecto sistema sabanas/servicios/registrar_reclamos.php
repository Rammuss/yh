<?php
include "../conexion/configv2.php";
header('Content-Type: application/json');

// Obtener los datos enviados desde el front-end
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id_cliente'], $data['descripcion'])) {
    $id_cliente = $data['id_cliente'];
    $descripcion = $data['descripcion'];
    $estado = isset($data['estado']) ? $data['estado'] : 'pendiente';
    $respuesta = isset($data['respuesta']) ? $data['respuesta'] : null;

    // Conexión a la base de datos
    include "../conexion/configv2.php";

    // Insertar el reclamo
    $query = "INSERT INTO reclamos_clientes (id_cliente, descripcion, fecha_reclamo, estado, respuesta) 
              VALUES ($1, $2, NOW(), $3, $4)";
    $result = pg_query_params($conn, $query, array($id_cliente, $descripcion, $estado, $respuesta));

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Reclamo registrado correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al registrar el reclamo.']);
    }

    // Cerrar la conexión
    pg_close($conn);
} else {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
}
?>
