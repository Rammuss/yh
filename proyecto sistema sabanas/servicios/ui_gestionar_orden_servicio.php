<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Órdenes de Servicio</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.3/css/bulma.min.css">
    <link rel="stylesheet" href="../servicios/styles_venta.css">
    <script src="../servicios/navbar.js"></script>
</head>

<body>
<div id="navbar-container"></div>
    <section class="section">
        <div class="container">
            <h1 class="title">Gestionar Órdenes de Servicio</h1>

            <!-- Formulario de Búsqueda -->
            <form id="buscar-form">
                <div class="field">
                    <label class="label">Buscar por Cliente</label>
                    <div class="control">
                        <input class="input" type="text" id="buscar-cliente" placeholder="Ingrese nombre del cliente o RUC/CI">
                    </div>
                </div>

                <div class="field">
                    <label class="label">Buscar por Estado</label>
                    <div class="control">
                        <div class="select">
                            <select id="buscar-estado">
                                <option value="">Seleccione un estado</option>
                                <option value="en proceso">En Proceso</option>
                                <option value="cancelado">Cancelado</option>
                                <option value="completado">Completado</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Buscar por Fecha</label>
                    <div class="control">
                        <input class="input" type="date" id="buscar-fecha">
                    </div>
                </div>

                <div class="control">
                    <button class="button is-primary" type="submit">Buscar</button>
                </div>
            </form>

            <!-- Tabla de Órdenes de Servicio -->
            <div class="table-container">
                <table class="table is-striped is-fullwidth">
                    <thead>
                        <tr>
                            <th>ID Orden</th>
                            <th>ID Solicitud</th>
                            <th>Cliente</th>
                            <th>RUC/CI</th>
                            <th>Trabajador</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-ordenes">
                        <!-- Aquí se insertarán las órdenes de servicio -->
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <script>
        // Evento para el formulario de búsqueda
        // Evento para el formulario de búsqueda
        document.getElementById('buscar-form').addEventListener('submit', async (event) => {
            event.preventDefault();

            const cliente = document.getElementById('buscar-cliente').value;
            const estado = document.getElementById('buscar-estado').value;
            const fecha = document.getElementById('buscar-fecha').value;

            const filtros = {
                cliente: cliente,
                estado: estado,
                fecha: fecha
            };

            // Cargar las órdenes de servicio desde el backend
            const response = await fetch('../servicios/obtener_ordenes_servicio.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(filtros)
            });
            const result = await response.json();

            const tablaOrdenes = document.getElementById('tabla-ordenes');
            tablaOrdenes.innerHTML = ''; // Limpiar la tabla antes de insertar los nuevos datos

            if (result.success) {
                result.data.forEach(orden => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                <td>${orden.id_orden}</td>
                <td>${orden.id_solicitud}</td>
                <td>${orden.cliente}</td>
                <td>${orden.cliente_ruc_ci}</td>
                <td>${orden.trabajador}</td>
                <td>${orden.fecha}</td>
                <td>${orden.estado}</td>
                <td>
                    <button class="button is-small is-info" onclick="window.location.href='../servicios/pdf_generar_orden_trabajo.php?id_orden=${orden.id_orden}&id_solicitud=${orden.id_solicitud}'">Generar Orden de Trabajo</button>
                    <button class="button is-small is-success" onclick="completarOrden(${orden.id_orden})">Completar</button>
                    <button class="button is-small is-danger" onclick="cancelarOrden(${orden.id_orden})">Cancelar</button>
                </td>
            `;
                    tablaOrdenes.appendChild(row);
                });
            } else {
                tablaOrdenes.innerHTML = '<tr><td colspan="8">No se encontraron órdenes de servicio.</td></tr>';
            }
        });

        // Función para completar una orden de servicio
        async function completarOrden(id_orden) {
            const response = await fetch('../servicios/completar_orden_servicio.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_orden: id_orden
                })
            });
            const result = await response.json();

            if (result.success) {
                alert(result.message);
                // Recargar la tabla de órdenes de servicio
                document.getElementById('buscar-form').dispatchEvent(new Event('submit'));
            } else {
                alert(result.message);
            }
        }

        // Función para cancelar una orden de servicio
        async function cancelarOrden(id_orden) {
            const response = await fetch('../servicios/cancelar_orden_servicio.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_orden: id_orden
                })
            });
            const result = await response.json();

            if (result.success) {
                alert(result.message);
                // Recargar la tabla de órdenes de servicio
                document.getElementById('buscar-form').dispatchEvent(new Event('submit'));
            } else {
                alert(result.message);
            }
        }
    </script>
</body>

</html>