// navbar.js


//<div id="navbar-container"></div>

document.addEventListener("DOMContentLoaded", function() {
    // Cargar la barra de navegación
    fetch('/YH/proyecto sistema sabanas/venta_v2/navbarV.html')
        .then(response => response.text())
        .then(data => {
            document.getElementById('navbar-container').innerHTML = data;

            // Lógica para cargar la imagen del perfil
            if (!window.imagenPerfilCargada) {
                const script = document.createElement('script');
                script.src = '/YH/proyecto sistema sabanas/mantenimiento_seguridad/panel_usuario/cargar_imagen_perfil.js';
                script.onload = function() {
                    window.imagenPerfilCargada = true;
                    cargarImagenPerfil();
                };
                document.head.appendChild(script);
            } else {
                cargarImagenPerfil();
            }

            // Lógica para cargar el nombre de usuario
            if (!window.nombreUsuarioCargado) {
                const scriptNombre = document.createElement('script');
                scriptNombre.src = '/YH/proyecto sistema sabanas/mantenimiento_seguridad/panel_usuario/cargar_nombre_usuario.js';
                scriptNombre.onload = function() {
                    window.nombreUsuarioCargado = true;
                    cargarNombreUsuario(); // Llamar la función para mostrar el nombre de usuario
                };
                document.head.appendChild(scriptNombre);
            } else {
                cargarNombreUsuario();
            }
        })
        .catch(error => console.error('Error al cargar la navbar:', error));
});



