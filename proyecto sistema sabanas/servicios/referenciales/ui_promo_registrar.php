<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Promociones</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="../../servicios/styles_venta.css">
    <script src="../../servicios/navbar.js"></script>
</head>

<body>
<div id="navbar-container"></div>
    <section class="section">
        <div class="container">
            <h1 class="title">Registrar Promoción</h1>

            <form id="formPromocion">
                <!-- Nombre de la Promoción -->
                <div class="field">
                    <label class="label">Nombre</label>
                    <div class="control">
                        <input class="input" type="text" id="nombre" placeholder="Nombre de la promoción" required>
                    </div>
                </div>

                <!-- Precio con Promoción -->
                <div class="field">
                    <label class="label">Precio Total</label>
                    <div class="control">
                        <input class="input" type="number" id="precio" placeholder="Precio total con promoción" required>
                    </div>
                </div>

                <!-- Estado -->
                <div class="field">
                    <label class="label">Estado</label>
                    <div class="control">
                        <input class="input" type="text" id="estado" value="inactivo" disabled>
                    </div>
                </div>

                <!-- Botón de Enviar -->
                <div class="field">
                    <div class="control">
                        <button class="button is-primary" type="submit">Registrar Promoción</button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <script>
        document.getElementById('formPromocion').addEventListener('submit', function(event) {
            event.preventDefault();

            // Obtener los valores del formulario
            const promocion = {
                nombre: document.getElementById('nombre').value,
                precio: document.getElementById('precio').value,
                estado: "inactivo",
            };

            // Enviar los datos con fetch de manera asíncrona
            fetch('../referenciales/registrar_promo.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(promocion), // Convertir el objeto en una cadena JSON
                })
                .then(response => response.json()) // Parsear la respuesta JSON
                .then(data => {
                    if (data.success) {
                        // Si la respuesta es exitosa, mostramos un mensaje
                        alert('Promoción registrada con éxito');
                    } else {
                        // Si hay un error, mostramos el mensaje de error
                        alert('Error al registrar la promoción: ' + data.error);
                    }
                })
                .catch(error => {
                    // Si hay un error en la solicitud fetch
                    console.error('Error en la solicitud:', error);
                    alert('Hubo un error en la solicitud');
                });
        });
    </script>

</body>

</html>