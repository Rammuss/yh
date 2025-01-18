<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Orden de Servicio</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.3/css/bulma.min.css">
</head>

<body>
    <section class="section">
        <div class="container">
            <h1 class="title">Registrar Orden de Servicio</h1>
            <form id="orden-form">
                <div class="field">
                    <label class="label">Solicitudes Pendientes</label>
                    <div class="control">
                        <div class="select">
                            <select id="solicitud-aprobada" required>
                                <option value="" disabled selected>Seleccione una solicitud</option>
                                <!-- Aquí se insertarán las opciones de solicitudes aprobadas -->
                            </select>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Trabajador Asignado</label>
                    <div class="control">
                        <div class="select">
                            <select id="trabajador-asignado" required>
                                <option value="" disabled selected>Seleccione un trabajador</option>
                                <!-- Aquí se insertarán las opciones de trabajadores -->
                            </select>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Detalles de la Solicitud</label>
                    <div class="control">
                        <table class="table is-striped is-fullwidth">
                            <thead>
                                <tr>
                                    <th>Servicio/Insumo</th>
                                    <th>Cantidad</th>
                                    <th>Costo Unitario</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody id="detalles-solicitud">
                                <!-- Aquí se insertarán los detalles de la solicitud -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="control">
                    <button class="button is-primary" type="submit">Registrar Orden de Servicio</button>
                </div>
            </form>

            <div id="mensaje" class="notification is-hidden"></div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            // Cargar las solicitudes aprobadas
            const responseSolicitudes = await fetch('../servicios/obtener_solicitudes_aprobadas.php');
            const resultSolicitudes = await responseSolicitudes.json();
            const selectSolicitud = document.getElementById('solicitud-aprobada');

            if (resultSolicitudes.success) {
                resultSolicitudes.data.forEach(solicitud => {
                    const option = document.createElement('option');
                    option.value = solicitud.id_solicitud;
                    option.textContent = `Solicitud No: ${solicitud.id_solicitud} - Cliente: ${solicitud.cliente_nombre} - RUC: ${solicitud.cliente_ruc}`;
                    selectSolicitud.appendChild(option);
                });
            }

            // Cargar los trabajadores
            const responseTrabajadores = await fetch('../servicios/obtener_worker.php');
            const resultTrabajadores = await responseTrabajadores.json();
            const selectTrabajador = document.getElementById('trabajador-asignado');

            if (resultTrabajadores.success) {
                resultTrabajadores.data.forEach(trabajador => {
                    const option = document.createElement('option');
                    option.value = trabajador.id;
                    option.textContent = `${trabajador.nombre_usuario}`;
                    selectTrabajador.appendChild(option);
                });
            }
        });

        // Cargar detalles de la solicitud seleccionada
        document.getElementById('solicitud-aprobada').addEventListener('change', async (event) => {
            const id_solicitud = event.target.value;
            const response = await fetch(`../servicios/obtener_solicitud_detalle.php?id=${id_solicitud}`);
            const result = await response.json();
            const tbody = document.getElementById('detalles-solicitud');
            tbody.innerHTML = ''; // Limpiar tbody antes de insertar los nuevos datos

            if (result.success) {
                let totalGeneral = 0;

                result.data.forEach(detalle => {
                    const row = document.createElement('tr');
                    const totalDetalle = parseFloat(detalle.total);
                    totalGeneral += totalDetalle;

                    row.innerHTML = `
                <td>${detalle.servicio_insumo || 'N/A'}</td>
                <td>${detalle.cantidad || 1}</td> <!-- Asumiendo 1 unidad por detalle -->
                <td>${parseFloat(detalle.costo_unitario).toFixed(2)}</td>
                <td>${totalDetalle.toFixed(2)}</td>
            `;
                    tbody.appendChild(row);
                });

                // Agregar fila para el total general
                const totalRow = document.createElement('tr');
                totalRow.innerHTML = `
            <td colspan="3"><strong>Total General</strong></td>
            <td><strong>${totalGeneral.toFixed(2)}</strong></td>
        `;
                tbody.appendChild(totalRow);
            } else {
                tbody.innerHTML = '<tr><td colspan="4">No se encontraron detalles.</td></tr>';
            }
        });





        // Enviar formulario al servidor
        document.getElementById('orden-form').addEventListener('submit', async (event) => {
            event.preventDefault();

            const id_solicitud = document.getElementById('solicitud-aprobada').value;
            const id_trabajador = document.getElementById('trabajador-asignado').value;
            const response = await fetch('../servicios/registrar_orden_servicio.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_solicitud: id_solicitud,
                    id_trabajador: id_trabajador
                })
            });

            const result = await response.json();
            const mensajeDiv = document.getElementById('mensaje');

            if (result.success) {
                mensajeDiv.className = 'notification is-success';
                mensajeDiv.textContent = result.message;
                mensajeDiv.classList.remove('is-hidden');
                document.getElementById('orden-form').reset();
                document.getElementById('detalles-solicitud').innerHTML = '';
            } else {
                mensajeDiv.className = 'notification is-danger';
                mensajeDiv.textContent = result.message;
                mensajeDiv.classList.remove('is-hidden');
            }
        });
    </script>
</body>

</html>