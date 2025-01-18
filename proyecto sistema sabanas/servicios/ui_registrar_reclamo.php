<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Reclamo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.3/css/bulma.min.css">
</head>

<body>



    <section class="section">
        <div class="container">
            <h1 class="title">Registrar Reclamo</h1>
            <!-- Modal Nuevo Cliente -->
            <div id="modalNuevoCliente" class="modal">
                <div class="modal-background"></div>
                <div class="modal-content">
                    <div class="box">
                        <span class="modal-close is-large" aria-label="close">&times;</span>
                        <h2 class="title is-4">Registrar Nuevo Cliente</h2>
                        <form id="formNuevoCliente">
                            <div class="field">
                                <label class="label" for="nombre_cliente">Nombre:</label>
                                <div class="control">
                                    <input class="input" type="text" id="nombre_cliente" name="nombre" required>
                                </div>
                            </div>

                            <div class="field">
                                <label class="label" for="apellido_cliente">Apellido:</label>
                                <div class="control">
                                    <input class="input" type="text" id="apellido_cliente" name="apellido" required>
                                </div>
                            </div>

                            <div class="field">
                                <label class="label" for="direccion_cliente">Dirección:</label>
                                <div class="control">
                                    <input class="input" type="text" id="direccion_cliente" name="direccion">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label" for="telefono_cliente">Teléfono:</label>
                                <div class="control">
                                    <input class="input" type="text" id="telefono_cliente" name="telefono">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label" for="ruc_ci_cliente">RUC/CI:</label>
                                <div class="control">
                                    <input class="input" type="text" id="ruc_ci_cliente" name="ruc_ci" required>
                                </div>
                            </div>

                            <div class="field">
                                <div class="control">
                                    <button class="button is-primary" type="submit">Guardar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <form id="reclamo-form">
                <!-- Cliente -->
                <div class="field">
                    <label class="label" for="cliente">Cliente:</label>
                    <div class="control">
                        <input required class="input" type="text" id="buscarCliente" placeholder="Buscar por nombre, apellido o RUC/CI...">
                    </div>

                    <!-- Contenedor para las sugerencias -->
                    <ul id="listaSugerencias" class="sugerencias-lista" style="list-style-type: none; padding-left: 0;"></ul>
                </div>
                <button type="button" class="button is-link" id="btnNuevoCliente">+ Nuevo Cliente</button>
                <!-- Campo oculto para guardar el id del cliente -->
                <input type="hidden" id="idClienteSeleccionado" name="idClienteSeleccionado">


                <div class="field">
                    <label class="label">Descripción</label>
                    <div class="control">
                        <textarea class="textarea" id="descripcion" placeholder="Describa el reclamo" required></textarea>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Estado</label>
                    <div class="control">
                        <div class="select">
                            <select id="estado" required>
                                <option value="pendiente">Pendiente</option>
                                <option value="resuelto">Resuelto</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Respuesta</label>
                    <div class="control">
                        <textarea class="textarea" id="respuesta" placeholder="Ingrese la respuesta al reclamo (opcional)"></textarea>
                    </div>
                </div>

                <div class="control">
                    <button class="button is-primary" type="submit">Registrar Reclamo</button>
                </div>
            </form>

            <div id="mensaje" class="notification is-hidden"></div>
        </div>
    </section>

    <script>
        //enviar form
        document.getElementById('reclamo-form').addEventListener('submit', async function(event) {
            event.preventDefault();

            const id_cliente = document.getElementById('idClienteSeleccionado').value;
            const descripcion = document.getElementById('descripcion').value;
            const estado = document.getElementById('estado').value;
            const respuesta = document.getElementById('respuesta').value;

            const data = {
                id_cliente: id_cliente,
                descripcion: descripcion,
                estado: estado,
                respuesta: respuesta
            };

            const response = await fetch('../servicios/registrar_reclamos.php', {
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
                document.getElementById('reclamo-form').reset();
            } else {
                mensajeDiv.className = 'notification is-danger';
                mensajeDiv.textContent = result.message;
                mensajeDiv.classList.remove('is-hidden');
            }
        });


        // Evento del botón Nuevo Cliente
        document.getElementById("nuevoCliente").addEventListener("click", () => {
            const clienteInput = document.getElementById("cliente");
            const nuevoCliente = prompt("Ingrese el nombre del nuevo cliente:");

            if (nuevoCliente) {
                clienteInput.value = nuevoCliente;
            }
        });
    </script>

    <script>
        // CARGAR LOS CLIENTES EN EL SELECT
        const buscarCliente = document.getElementById('buscarCliente');
        const listaSugerencias = document.getElementById('listaSugerencias');
        const idClienteSeleccionado = document.getElementById('idClienteSeleccionado');

        buscarCliente.addEventListener('input', async () => {
            const termino = buscarCliente.value.trim();

            if (termino.length > 2) { // Realiza la búsqueda solo si hay más de 2 caracteres
                const response = await fetch(`../servicios/obtener_cliente.php?search=${termino}`);
                const data = await response.json();

                listaSugerencias.innerHTML = '';

                if (data.success) {
                    data.clientes.forEach(cliente => {
                        const li = document.createElement('li');
                        li.textContent = `${cliente.nombre} ${cliente.apellido} - RUC/CI: ${cliente.ruc_ci}`;
                        li.dataset.id = cliente.id_cliente;
                        li.addEventListener('click', () => seleccionarCliente(cliente.id_cliente, cliente.nombre, cliente.apellido, cliente.ruc_ci));
                        listaSugerencias.appendChild(li);
                    });
                }
            }
        });

        function seleccionarCliente(id, nombre, apellido, ruc_ci) {
            console.log(`Cliente seleccionado: ${nombre} ${apellido} (RUC/CI: ${ruc_ci})`);
            buscarCliente.value = `${nombre} ${apellido} - ${ruc_ci}`;
            listaSugerencias.innerHTML = ''; // Limpia las sugerencias
            idClienteSeleccionado.value = id; // Guarda el id del cliente en el campo oculto
        }
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const modal = document.getElementById('modalNuevoCliente');
            const btnNuevoCliente = document.getElementById('btnNuevoCliente');
            const spanClose = document.querySelector('.modal-close'); // Asegúrate de que la clase sea 'modal-close'

            // Abrir el modal
            btnNuevoCliente.onclick = function() {
                modal.classList.add('is-active'); // Usamos 'is-active' en lugar de manipular display directamente
            };

            // Cerrar el modal
            spanClose.onclick = function() {
                modal.classList.remove('is-active'); // Eliminamos 'is-active' para cerrar el modal
            };

            // Cerrar al hacer clic fuera del modal
            window.onclick = function(event) {
                if (event.target === modal) {
                    modal.classList.remove('is-active'); // Eliminamos 'is-active' para cerrar el modal
                }
            };
        });
    </script>

    <script>
        // para nuevo cliente
        formNuevoCliente.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Obtener los datos del formulario
            const formData = new FormData(formNuevoCliente);

            // Enviar al backend
            const response = await fetch('../servicios/insertar_cliente.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert('Cliente registrado exitosamente.');
                // Aquí puedes agregar el nuevo cliente al dropdown si es necesario
            } else {
                alert('Error al registrar el cliente: ' + data.message);
            }
        });
    </script>
</body>

</html>