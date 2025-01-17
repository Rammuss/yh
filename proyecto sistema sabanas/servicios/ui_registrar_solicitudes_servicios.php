<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Solicitud de Servicios</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
</head>

<body>
    <section class="section">
        <div class="container">
            <h1 class="title">Registrar Solicitud de Servicios</h1>

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



            <form id="formSolicitud">
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

                <!-- Fecha -->
                <div class="field">
                    <label class="label">Fecha</label>
                    <div class="control">
                        <input class="input" type="date" name="fecha" value="2025-01-14">
                    </div>
                </div>

                <!-- Servicios -->
                <div class="field">
                    <label class="label">Servicios</label>
                    <div class="control">
                        <div class="select">
                            <select id="servicio" name="servicio">
                                <option value="" disabled selected>Selecciona un servicio</option>
                            </select>
                        </div>
                        <button class="button is-link is-light ml-2" type="button" id="agregarServicio">Agregar Servicio</button>
                    </div>
                </div>
                <!-- Insumos -->
                <div class="field"> <label class="label">Seleccionar Insumo</label>
                    <div class="control">
                        <div class="select"> <select id="insumo">
                                <option value="" disabled selected>Selecciona un insumo</option>
                            </select> </div>
                    </div> <button class="button is-primary" id="agregarInsumo" type="button">Agregar Insumo</button>
                </div>

                <!-- Promociones -->
                <div class="field">
                    <label class="label">Promociones</label>
                    <div class="control">
                        <div class="select">
                            <select id="promocion" name="promocion">
                                <option value="" disabled selected>Promociones Disponibles</option>
                            </select>
                        </div>
                        <button class="button is-link is-light ml-2" type="button" id="agregarPromocion">Agregar Promoción</button>
                    </div>
                </div>


                <!-- Descuentos -->
                <div class="field">
                    <label class="label">Descuentos</label>
                    <div class="control">
                        <div class="select">
                            <select id="descuento" name="descuento">
                                <option value="" disabled selected>Selecciona un descuento</option>
                            </select>
                        </div>
                    </div>
                </div>






                <!-- Detalle de Servicios -->
                <table class="table is-fullwidth" id="detalleServicios">
                    <thead>
                        <tr>
                            <th>Servicio</th>
                            <th>Costo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Servicios añadidos -->
                    </tbody>
                </table>

                <!-- Botón de Envío -->
                <div class="field">
                    <div class="control">
                        <button class="button is-primary" type="submit">Registrar Solicitud</button>
                    </div>

                </div>


            </form>
        </div>
    </section>

    <script>
        //Agregar servicios al detalle 
        document.getElementById("agregarServicio").addEventListener("click", () => {
            const select = document.getElementById("servicio");
            const servicioSeleccionado = select.options[select.selectedIndex];
            const tbody = document.getElementById("detalleServicios").querySelector("tbody");
            if (servicioSeleccionado.value) { // Crear fila con datos 
                const fila = document.createElement("tr");
                const costo = servicioSeleccionado.getAttribute('data-costo'); // Obtener el costo del atributo de datos
                fila.innerHTML = ` <td>${servicioSeleccionado.textContent.split(' - ')[0]}</td>
                 <td>${costo}</td> 
                 <td> 
                 <button class="button is-danger is-light eliminarServicio" type="button">Eliminar</button> 
                 </td>
                  <input type="hidden" name="servicio[]" value="${servicioSeleccionado.value}"> <input type="hidden" name="costo[]" value="${costo}"> `;
                tbody.appendChild(fila);
            }
        });

        // Eliminar servicios del detalle
        document.getElementById("detalleServicios").addEventListener("click", (event) => {
            if (event.target.classList.contains("eliminarServicio")) {
                event.target.closest("tr").remove();
            }
        });


        // Enviar formulario al servidor
        document.getElementById("formSolicitud").addEventListener("submit", async (event) => {
            event.preventDefault();
            const formData = new FormData(event.target);
            const data = Object.fromEntries(formData.entries());
            const servicios = [];
            const costos = [];
            const insumos = [];
            const insumoCostos = [];
            const promociones = [];
            const precios = [];
            document.querySelectorAll("#detalleServicios tbody tr").forEach(row => {
                const servicio = row.querySelector('input[name="servicio[]"]');
                const costo = row.querySelector('input[name="costo[]"]');
                const insumo = row.querySelector('input[name="insumo[]"]');
                const insumoCosto = row.querySelector('input[name="costo[]"]');
                const promocion = row.querySelector('input[name="promocion[]"]');
                const precio = row.querySelector('input[name="precio[]"]');
                if (servicio && costo) {
                    servicios.push(servicio.value);
                    costos.push(costo.value);
                }
                if (insumo && insumoCosto) {
                    insumos.push(insumo.value);
                    insumoCostos.push(insumoCosto.value);
                }
                if (promocion && precio) {
                    promociones.push(promocion.value);
                    precios.push(precio.value);
                }
            });
            if (servicios.length > 0) {
                data.servicios = servicios;
                data.costos = costos;
            }
            if (insumos.length > 0) {
                data.insumos = insumos;
                data.insumoCostos = insumoCostos;
            }
            if (promociones.length > 0) {
                data.promociones = promociones;
                data.precios = precios;
            }
            // Eliminar claves no necesarias
            delete data["servicio"];
            delete data["servicio[]"];
            delete data["costo[]"];
            delete data["insumo"];
            delete data["insumo[]"];
            delete data["insumoCosto[]"];
            delete data["promocion"];
            delete data["promocion[]"];
            delete data["precio[]"];
            console.log("Payload enviado:", data);
            // Verificar el payload
            try {
                const response = await fetch('../servicios/registrar_solicitud_servicio.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                if (response.ok) {
                    alert("Solicitud registrada con éxito");
                    event.target.reset();
                    document.querySelector("#detalleServicios tbody").innerHTML = "";
                } else {
                    alert("Error al registrar la solicitud");
                }
            } catch (error) {
                console.error("Error al enviar el formulario:", error);
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

        // Cargar servicios al cargar la página
        window.addEventListener("DOMContentLoaded", cargarServicios);
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

    <script>
        //obetener servicios
        document.addEventListener('DOMContentLoaded', async () => {
            const response = await fetch('../servicios/obtener_servicios.php');
            const services = await response.json();

            const select = document.getElementById('servicio');
            services.forEach(service => {
                const option = document.createElement('option');
                option.value = service.id;
                option.textContent = `${service.nombre} - Gs${service.costo}`;
                option.setAttribute('data-costo', service.costo); // Agregar el costo como un atributo de datos
                select.appendChild(option);
            });
        });
    </script>

    <script>
        //obtener promos
        document.addEventListener('DOMContentLoaded', async () => {
            // Cargar las promociones dinámicamente
            const response = await fetch('../servicios/obtener_promo.php');
            const promotions = await response.json();

            const select = document.getElementById('promocion');
            promotions.forEach(promotion => {
                const option = document.createElement('option');
                option.value = promotion.id_promocion;
                option.textContent = `${promotion.nombre} - ${promotion.precio} Gs`;
                option.setAttribute('data-precio', promotion.precio); // Agregar el precio como un atributo de datos
                select.appendChild(option);
            });
        });

        // Agregar promociones al detalle
        document.getElementById("agregarPromocion").addEventListener("click", () => {
            const select = document.getElementById("promocion");
            const promocionSeleccionada = select.options[select.selectedIndex];
            const tbody = document.getElementById("detalleServicios").querySelector("tbody");

            if (promocionSeleccionada.value) {
                // Crear fila con datos
                const fila = document.createElement("tr");
                const precio = promocionSeleccionada.getAttribute('data-precio'); // Obtener el precio del atributo de datos

                fila.innerHTML = `
                <td>${promocionSeleccionada.textContent.split(' - ')[0]}</td>
                <td>${precio}</td>
                <td>
                    <button class="button is-danger is-light eliminarServicio" type="button">Eliminar</button>
                </td>
                <input type="hidden" name="promocion[]" value="${promocionSeleccionada.value}">
                <input type="hidden" name="precio[]" value="${precio}">
            `;
                tbody.appendChild(fila);
            }
        });
    </script>

    <script>
        //obtener insumos
        document.addEventListener('DOMContentLoaded', async () => {
            // Cargar los insumos dinámicamente
            const response = await fetch('../servicios/obtener_insumos.php');
            const insumos = await response.json();

            const select = document.getElementById('insumo');
            insumos.forEach(insumo => {
                const option = document.createElement('option');
                option.value = insumo.id;
                option.textContent = `${insumo.nombre} - ${insumo.costo} Gs`;
                option.setAttribute('data-costo', insumo.costo); // Agregar el costo como un atributo de datos
                select.appendChild(option);
            });
        });

        // Agregar insumos al detalle
        document.getElementById("agregarInsumo").addEventListener("click", () => {
            const select = document.getElementById("insumo");
            const insumoSeleccionado = select.options[select.selectedIndex];
            const tbody = document.getElementById("detalleServicios").querySelector("tbody");
            if (insumoSeleccionado.value) { // Crear fila con datos
                const fila = document.createElement("tr");
                const costo = insumoSeleccionado.getAttribute('data-costo');
                fila.innerHTML = ` <td>${insumoSeleccionado.textContent.split(' - ')[0]}</td>
                 <td>${costo}</td> 
                 <td> <button class="button is-danger is-light eliminarServicio" type="button">Eliminar</button> </td>
                  <input type="hidden" name="servicio[]" value="${insumoSeleccionado.value}"> <input type="hidden" name="costo[]" value="${costo}"> `;
                tbody.appendChild(fila);
            }
        })
    </script>


    <script>
        // Deshabilitar campos de selección de acuerdo a la selección
        document.getElementById('descuento').addEventListener('change', () => {
            const selectPromotions = document.getElementById('promocion');
            const agregarPromocionBtn = document.getElementById('agregarPromocion');
            if (document.getElementById('descuento').value) {
                selectPromotions.disabled = true;
                agregarPromocionBtn.disabled = true;
            } else {
                selectPromotions.disabled = false;
                agregarPromocionBtn.disabled = false;
            }
        });

        document.getElementById('promocion').addEventListener('change', () => {
            const selectDiscounts = document.getElementById('descuento');
            if (document.getElementById('promocion').value) {
                selectDiscounts.disabled = true;
            } else {
                selectDiscounts.disabled = false;
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            // Cargar los descuentos dinámicamente
            const responseDescuentos = await fetch('../servicios/obtener_descuentos.php');
            const discounts = await responseDescuentos.json();

            const selectDiscounts = document.getElementById('descuento');
            discounts.forEach(discount => {
                const option = document.createElement('option');
                option.value = discount.porcentaje;
                option.textContent = `${discount.nombre} - ${discount.porcentaje}%`;
                selectDiscounts.appendChild(option);
            });
        });
    </script>



</body>

</html>
<script>

</script>