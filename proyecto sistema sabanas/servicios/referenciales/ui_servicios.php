<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Servicio</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.3/css/bulma.min.css">
</head>
<body>
    <section class="section">
        <div class="container">
            <h1 class="title">Registrar Servicio</h1>
            <form id="service-form">
                <div class="field">
                    <label class="label">Nombre del Servicio</label>
                    <div class="control">
                        <input class="input" type="text" id="service-name" placeholder="Nombre del Servicio" required>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Costo</label>
                    <div class="control">
                        <input class="input" type="number" id="service-cost" placeholder="Costo" required>
                    </div>
                </div>
                <div class="control">
                    <button class="button is-primary" type="submit">Registrar</button>
                </div>
            </form>
        </div>
    </section>

    <script>
        document.getElementById('service-form').addEventListener('submit', async (event) => {
            event.preventDefault();
            const serviceName = document.getElementById('service-name').value;
            const serviceCost = document.getElementById('service-cost').value;

            const response = await fetch('../referenciales/registrar_servicios.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ name: serviceName, cost: serviceCost })
            });

            const result = await response.json();
            alert(result.message);
        });
    </script>
</body>
</html>
