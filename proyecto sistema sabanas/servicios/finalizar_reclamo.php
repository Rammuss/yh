<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Reclamo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.3/css/bulma.min.css">
</head>
<body>
    <section class="section">
        <div class="container">
            <h1 class="title">Gestionar Reclamo</h1>

            <?php
            include "../conexion/configv2.php";
            if (isset($_GET['id'])) {
                $id = $_GET['id'];
                $query = "SELECT * FROM reclamos_clientes WHERE id_reclamo = $1";
                $result = pg_query_params($conn, $query, array($id));

                if (pg_num_rows($result) > 0) {
                    $reclamo = pg_fetch_assoc($result);
            ?>

            <form id="respuesta-form">
                <input type="hidden" id="id_reclamo" value="<?php echo $reclamo['id_reclamo']; ?>">
                
                <div class="field">
                    <label class="label">Descripción del Reclamo</label>
                    <div class="control">
                        <textarea class="textarea" disabled><?php echo $reclamo['descripcion']; ?></textarea>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Respuesta</label>
                    <div class="control">
                        <textarea class="textarea" id="respuesta" placeholder="Ingrese la respuesta al reclamo"></textarea>
                    </div>
                </div>

                <div class="control">
                    <button class="button is-primary" type="submit">Guardar y Resuelto</button>
                </div>
            </form>

            <div class="buttons mt-4">
                <button class="button is-info" onclick="window.history.back()">Atrás</button>
            </div>

            <div id="mensaje" class="notification is-hidden"></div>

            <?php
                } else {
                    echo "<div class='notification is-danger'>No se encontró el reclamo.</div>";
                }
            } else {
                echo "<div class='notification is-danger'>ID no proporcionado.</div>";
            }
            pg_close($conn);
            ?>

        </div>
    </section>

    <script>
        document.getElementById('respuesta-form').addEventListener('submit', async function(event) {
            event.preventDefault();

            const id_reclamo = document.getElementById('id_reclamo').value;
            const respuesta = document.getElementById('respuesta').value;

            const data = {
                id_reclamo: id_reclamo,
                respuesta: respuesta,
                estado: 'resuelto'
            };

            const response = await fetch('../servicios/actualizar_reclamo.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            const mensajeDiv = document.getElementById('mensaje');

            if (result.success) {
                mensajeDiv.className = 'notification is-success';
                mensajeDiv.textContent = result.message;
                mensajeDiv.classList.remove('is-hidden');
                setTimeout(() => {
                    window.location.href = '../servicios/ui_gestionar_reclamos.php'; // Redirigir a la página de gestión de reclamos
                }, 2000);
            } else {
                mensajeDiv.className = 'notification is-danger';
                mensajeDiv.textContent = result.message;
                mensajeDiv.classList.remove('is-hidden');
            }
        });
    </script>
</body>
</html>
