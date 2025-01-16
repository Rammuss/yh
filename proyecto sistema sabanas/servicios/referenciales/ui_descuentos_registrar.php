<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrar Descuento</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
</head>

<body>
  <section class="section">
    <div class="container">
      <h1 class="title">Registrar Descuento</h1>

      <form id="formDescuento">
        <!-- Nombre del Descuento -->
        <div class="field">
          <label class="label">Nombre</label>
          <div class="control">
            <input class="input" type="text" id="nombre" placeholder="Nombre del descuento" required>
          </div>
        </div>

        <!-- Porcentaje del Descuento -->
        <div class="field">
          <label class="label">Porcentaje (%)</label>
          <div class="control">
            <input class="input" type="number" id="valor" placeholder="Porcentaje de descuento" min="0" max="100" required>
          </div>
          <p class="help">Ingrese un valor entre 0 y 100.</p>
        </div>

        <!-- Estado -->
        <div class="field">
          <label class="label">Estado</label>
          <div class="control">
            <input class="input" type="text" id="estado" value="Inactivo" disabled>
          </div>
        </div>

        <!-- Botón de Enviar -->
        <div class="field">
          <div class="control">
            <button class="button is-primary" type="submit">Registrar Descuento</button>
          </div>
        </div>
      </form>
    </div>
  </section>

  <script>
    document.getElementById('formDescuento').addEventListener('submit', function(event) {
      event.preventDefault();

      // Capturar los valores ingresados
      const nombre = document.getElementById('nombre').value;
      const valor = document.getElementById('valor').value;

      // Validar el porcentaje (entre 0 y 100)
      if (valor < 0 || valor > 100) {
        alert('Porcentaje inválido. Debe ser un número entre 0 y 100.');
        return;
      }

      // Construir el objeto para enviar
      const descuento = {
        nombre: nombre,
        valor: `${valor}`, // Formato de porcentaje
        estado: "inactivo",
      };

      // Enviar los datos con fetch de manera asíncrona
      fetch('../referenciales/registrar_descuento.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(descuento), // Convertir el objeto en una cadena JSON
        })
        .then(response => response.json()) // Parsear la respuesta JSON
        .then(data => {
          if (data.success) {
            // Si la respuesta es exitosa, mostramos un mensaje
            alert('Descuento registrado con éxito');
          } else {
            // Si hay un error, mostramos el mensaje de error
            alert('Error al registrar el descuento: ' + data.error);
          }
        })
        .catch(error => {
          // Si hay un error en la solicitud fetch
          console.error('Error en la solicitud:', error);
          alert('Hubo un error en la solicitud');
        });

      // Reiniciar el formulario
      document.getElementById('formDescuento').reset();
    });
  </script>

</body>

</html>