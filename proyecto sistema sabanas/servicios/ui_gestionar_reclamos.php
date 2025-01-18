<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Reclamos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.3/css/bulma.min.css">
</head>
<body>
    <section class="section">
        <div class="container">
            <h1 class="title">Gestión de Reclamos</h1>

            <!-- Buscador -->
            <div class="field">
                <label class="label">Buscar por CI/RUC</label>
                <div class="control">
                    <input class="input" type="text" id="buscador-ci" placeholder="Ingrese CI o RUC">
                </div>
            </div>
            <div class="field">
                <label class="label">Buscar por Fecha</label>
                <div class="control">
                    <input class="input" type="date" id="buscador-fecha">
                </div>
            </div>
            <div class="field">
                <label class="label">Buscar por Estado</label>
                <div class="control">
                    <div class="select">
                        <select id="buscador-estado">
                            <option value="">Seleccione un estado</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="resuelto">Resuelto</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="control">
                <button class="button is-primary" id="buscar-btn">Buscar</button>
            </div>

            <!-- Tabla de Reclamos -->
            <div class="table-container">
                <table class="table is-striped is-fullwidth">
                    <thead>
                        <tr>
                            <th>ID Reclamo</th>
                            <th>ID Cliente</th>
                            <th>Descripción</th>
                            <th>Fecha Reclamo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-reclamos">
                        <!-- Aquí se insertarán los registros de reclamos -->
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <script>
        // Función para cargar los reclamos desde el backend
        async function cargarReclamos(filtros = {}) {
            const response = await fetch('../servicios/obtener_reclamos.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(filtros)
            });
            const result = await response.json();

            const tablaReclamos = document.getElementById('tabla-reclamos');
            tablaReclamos.innerHTML = ''; // Limpiar la tabla antes de insertar los nuevos datos

            if (result.success) {
                result.data.forEach(reclamo => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${reclamo.id_reclamo}</td>
                        <td>${reclamo.id_cliente}</td>
                        <td>${reclamo.descripcion}</td>
                        <td>${reclamo.fecha_reclamo}</td>
                        <td>${reclamo.estado}</td>
                        <td>
                            ${reclamo.estado === 'pendiente' ? `<button class="button is-success resolver-btn" data-id="${reclamo.id_reclamo}">Finalizar</button>` : '<button class="button" disabled>Resuelto</button>'}
                        </td>
                    `;
                    tablaReclamos.appendChild(row);
                });
            }
        }

        // Evento para gestionar los botones "Finalizar"
        document.addEventListener('click', (event) => {
            if (event.target.classList.contains('resolver-btn')) {
                const id_reclamo = event.target.getAttribute('data-id');
                window.location.href = `../servicios/finalizar_reclamo.php?id=${id_reclamo}`;
            }
        });

        // Evento para el botón de buscar
        document.getElementById('buscar-btn').addEventListener('click', () => {
            const ci = document.getElementById('buscador-ci').value;
            const fecha = document.getElementById('buscador-fecha').value;
            const estado = document.getElementById('buscador-estado').value;

            const filtros = {
                ci: ci,
                fecha: fecha,
                estado: estado
            };

            cargarReclamos(filtros);
        });

        // Cargar los reclamos al cargar la página
        document.addEventListener('DOMContentLoaded', () => cargarReclamos());
    </script>
</body>
</html>
