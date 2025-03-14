<?php
session_start();


?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Código</title>
    <link rel="stylesheet" href="styles.css"> <!-- Agrega tus estilos aquí -->
    <link rel="stylesheet" href="notificacion.css"> <!-- Agrega tus estilos aquí -->
    <link rel="stylesheet" href="../../login/login.css"> <!-- Agrega tus estilos aquí -->

</head>

<body>
    <div class="container">
        <h2>Ingrese Código de Verificación</h2>
        <form id="verificarForm">
            <input type="text" id="codigo" name="codigo" required>
            <button type="submit">Verificar</button>
        </form>
        <div id="notificacion" class="notificacion"></div> <!-- Notificación de respuesta -->
    </div>

    <script>
        document.getElementById('verificarForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Evitar el envío del formulario

            // Crear un objeto FormData para capturar los datos del formulario
            var formData = new FormData(this);

            // Usar fetch para enviar la solicitud POST
            fetch('verificar_codigo.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Error en la conexión");
                    }
                    return response.json(); // Parsear la respuesta JSON
                })
                .then(data => {
                    mostrarNotificacion(data.mensaje); // Mostrar mensaje de respuesta
                    if (data.success) {
                        setTimeout(() => {
                            window.location.href = "/YH/proyecto sistema sabanas/mantenimiento_seguridad/dashboard/dashboard.php"; // Cambia a la página deseada
                        }, 2000);
                    }
                })
                .catch(error => {
                    mostrarNotificacion(error.message); // Mostrar error si ocurre
                });
        });
    </script>
    <script src="notificacion.js"></script>
</body>

</html>