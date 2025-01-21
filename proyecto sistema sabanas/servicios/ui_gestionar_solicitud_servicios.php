<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Solicitudes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
</head>

<body>
    <!-- Encabezado -->
    <section class="hero is-primary">
        <div class="hero-body">
            <p class="title">Gestión de Solicitudes</p>
            <p class="subtitle">Visualiza y gestiona las solicitudes registradas</p>
        </div>
    </section>

    <!-- Contenido principal -->
    <section class="section">
        <div class="container">

            <!-- Botón para nueva solicitud -->
            <div class="buttons">
                <a href="../servicios//ui_registrar_solicitudes_servicios.php" class="button is-primary">Nueva Solicitud</a>
            </div>

            <!-- Filtros de búsqueda -->
            <div class="box">
                <div class="columns is-multiline">
                    <!-- Filtro por fecha -->
                    <div class="column is-one-third">
                        <div class="field">
                            <label class="label">Fecha</label>
                            <div class="control">
                                <input class="input" type="date" id="fechaFiltro">
                            </div>
                        </div>
                    </div>

                    <!-- Filtro por estado -->
                    <div class="column is-one-third">
                        <div class="field">
                            <label class="label">Estado</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select id="estadoFiltro">
                                        <option value="">Todos</option>
                                        <option value="pendiente">Pendiente</option>
                                        <option value="en proceso">En proceso</option>
                                        <option value="rechazado">Rechazado</option>
                                        <option value="completado">Completado</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtro por CI/RUC -->
                    <div class="column is-one-third">
                        <div class="field">
                            <label class="label">RUC/CI</label>
                            <div class="control">
                                <input class="input" type="text" placeholder="Buscar por RUC o CI" id="ciFiltro">
                            </div>
                        </div>
                    </div>

                    <!-- Botón de buscar -->
                    <div class="column is-full">
                        <div class="field">
                            <div class="control">
                                <button class="button is-info" id="buscarButton">Buscar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de solicitudes -->
            <table class="table is-striped is-fullwidth is-hoverable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>RUC/CI</th>
                        <th>Fecha</th>
                        <th>Monto Total</th>
                        <th>Descuento</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>


                </tbody>
            </table>
        </div>
    </section>



    <script>
        document.getElementById("buscarButton").addEventListener("click", async () => {
            // Capturar los valores de los filtros
            const fecha = document.getElementById("fechaFiltro").value;
            const estado = document.getElementById("estadoFiltro").value;
            const ci = document.getElementById("ciFiltro").value;

            try {
                // Realizar la solicitud al backend
                const response = await fetch("../servicios/obtener_solicitudes_servicios.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({
                        fecha,
                        estado,
                        ci
                    }),
                });

                if (!response.ok) {
                    throw new Error("Error al obtener los datos");
                }

                // Procesar los datos recibidos
                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.error || "Ocurrió un error desconocido");
                }

                const data = result.data;

                // Actualizar la tabla
                const tableBody = document.querySelector("table tbody");
                tableBody.innerHTML = ""; // Limpiar los resultados anteriores

                if (data.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="7" class="has-text-centered">No se encontraron resultados</td></tr>`;
                    return;
                }

                // Renderizar las filas de la tabla
                data.forEach((solicitud) => {
                    const row = `
                <tr>
                    <td>${solicitud.id}</td>
                    <td>${solicitud.cliente}</td>
                    <td>${solicitud.ci}</td>
                    <td>${solicitud.fecha}</td>
                    <td>${solicitud.monto_total ? parseFloat(solicitud.monto_total).toFixed(2) : "0.00"}</td>
                    <td>${solicitud.descuento ? parseFloat(solicitud.descuento).toFixed(2) : "0.00"}</td>
                    <td>${solicitud.estado}</td>
                    <td>
                        <button class="button is-small is-info" data-id="${solicitud.id}">Ver</button>
                        <button class="button is-small is-danger" data-id="${solicitud.id}">Rechazar</button>
                    </td>
                </tr>
            `;
                    tableBody.innerHTML += row;
                });
            } catch (error) {
                console.error(error);
                alert("Ocurrió un error al obtener los datos.");
            }
        });

        // Delegación de eventos para los botones
        document.querySelector("table tbody").addEventListener("click", async (event) => {
            const target = event.target;
            const solicitudId = target.getAttribute("data-id");

            if (solicitudId) {
                // Si el clic fue en el botón "Presupuesto"
                if (target.classList.contains("is-info")) {
                    window.location.href = `../servicios/pdf_solicitud.php?id=${solicitudId}`;
                }
                // Si el clic fue en el botón "Orden de servicio"
                else if (target.classList.contains("is-warning")) {
                    // Aquí podrías agregar la lógica para la orden de servicio
                    alert("Generar orden de servicio para la solicitud con ID: " + solicitudId);
                }
                // Si el clic fue en el botón "Rechazar"
                else if (target.classList.contains("is-danger")) {
                    try {
                        const response = await fetch('../servicios/rechazar_solicitud_servicio.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                id_cabecera: solicitudId
                            })
                        });
                        const result = await response.json();
                        if (response.ok) {
                            alert(result.message);
                            // Opcional: Actualizar la interfaz para reflejar el estado cambiado
                            target.closest("tr").remove(); // Eliminar la fila de la tabla
                        } else {
                            alert(result.message);
                        }
                    } catch (error) {
                        console.error("Error al rechazar la solicitud:", error);
                    }
                }
            }
        });
    </script>




</body>

</html>