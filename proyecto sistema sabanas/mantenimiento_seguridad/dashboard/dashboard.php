<?php
session_start();
if (!isset($_SESSION['nombre_usuario'])) {
    header('/YH/proyecto sistema sabanas/mantenimiento_seguridad/acceso/acceso.html');
    exit;
}

$rol = $_SESSION['rol']; // Acceder al rol del usuario

?>

<!DOCTYPE html>
<html>

<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        /* Estilos básicos */
body {
    font-family: 'Georgia', serif;
    background-color: #fbeff1; /* Fondo rosa pastel */
    margin: 0;
    padding: 20px;
}

.cerrar-sesion {
    border: 1px solid #f6ced8;
    display: inline-block;
    padding: 12px 25px;
    background-color: #ffe6e9; /* Fondo rosa claro */
    color: #d35b79; /* Texto rosa intenso */
    font-size: 16px;
    font-weight: bold;
    text-decoration: none;
    border-radius: 8px; /* Bordes redondeados */
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.cerrar-sesion:hover {
    background-color: #ffc0cb; /* Fondo rosa más oscuro */
    transform: scale(1.05);
}

.cerrar-sesion:active {
    background-color: #ff99aa; /* Fondo más intenso */
    transform: scale(1);
}

.dashboard-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    padding: 20px;
}

.card {
    background-color: #fff;
    border: 2px solid #f6ced8; /* Bordes rosa suave */
    border-radius: 12px; /* Bordes más suaves */
    box-shadow: 0 6px 8px rgba(0, 0, 0, 0.1);
    padding: 20px;
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: scale(1.08);
    box-shadow: 0 8px 10px rgba(0, 0, 0, 0.15);
}

.card h3 {
    margin: 10px 0;
    font-size: 18px;
    color: #d35b79; /* Texto rosa intenso */
}

.card a {
    display: block;
    margin-top: 10px;
    color: #d35b79;
    text-decoration: none;
    font-weight: bold;
}

.card a:hover {
    text-decoration: underline;
    color: #c64a6c; /* Texto más oscuro al pasar el mouse */
}

/* Estilos para pantallas pequeñas (menos de 600px) */
@media (max-width: 600px) {
    body {
        padding: 10px;
        font-size: 14px;
    }

    .cerrar-sesion {
        padding: 8px 16px;
        font-size: 14px;
        text-align: center;
    }

    .dashboard-container {
        grid-template-columns: 1fr; /* Una sola columna */
        gap: 10px;
        padding: 10px;
    }

    .card {
        padding: 15px;
    }

    .card h3 {
        font-size: 16px;
    }

    .card a {
        font-size: 14px;
    }
}

/* Estilos para pantallas medianas (entre 600px y 900px) */
@media (min-width: 601px) and (max-width: 900px) {
    .dashboard-container {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
    }

    .card h3 {
        font-size: 17px;
    }

    .cerrar-sesion {
        padding: 9px 18px;
        font-size: 15px;
    }
}

/* Estilos para pantallas grandes (más de 900px) */
@media (min-width: 901px) {
    .dashboard-container {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }

    .card h3 {
        font-size: 18px;
    }

    .cerrar-sesion {
        font-size: 16px;
        padding: 12px 25px;
    }
}

    </style>
</head>

<body>
    <h1>Bienvenido, <?php echo $_SESSION['nombre_usuario']; ?></h1>
    <a title="Cerrar Sesion" class="cerrar-sesion" href="logout.php"> <img  src="/YH/proyecto sistema sabanas/mantenimiento_seguridad/panel_usuario/iconosPerfil/cerrar-sesion.png" alt="">
    </a>
    <div class="dashboard-container">

        <div class="card">

            <a class="a_contenedor"
                href="/YH/proyecto sistema sabanas/mantenimiento_seguridad/panel_usuario/ui_panel_usuario.php"
                id="perfil-contenedor">

                <div class="p_perfil"><img style="border-radius: 50%;   /* Esto hace que la imagen sea circular */
    object-fit: cover;  " class="img_perfil" id="imagen-perfil"
                        src="/YH/proyecto sistema sabanas/mantenimiento_seguridad/panel_usuario/imagenes_perfil/default.png"
                        alt="Imagen de Perfil" width="50" height="50"></div>

                <div class="p2_perfil" id="nombre-usuario">Panel de Usuario</div>
            </a>

        </div>

        <?php if ($rol == 'admin'): ?>
            <div class="card">
                <h3>Administración</h3>
                <a href="/YH/proyecto sistema sabanas/menu/tabla_prespuestos.html">Ir al módulo de Administración</a>
            </div>
        <?php endif; ?>

        <?php if ($rol == 'admin' || $rol == 'compra'): ?>
            <div class="card">
                <h3>Módulo de Compra</h3>
                <a href="/YH/proyecto sistema sabanas/menu/tabla_prespuestos.html">Ir al módulo de Compra</a>
            </div>
        <?php endif; ?>

        <?php if ($rol == 'admin' || $rol == 'venta'): ?>
            <div class="card">
                <h3>Módulo de Ventas</h3>
                <a href="/ventas/dashboard.php">Ir al módulo de Ventas</a>
            </div>
        <?php endif; ?>

        <?php if ($rol == 'admin' || $rol == 'tesoreria'): ?>
            <div class="card">
                <h3>Módulo de Servicios</h3>
                <a href="/compras/dashboard.php">Ir al módulo de Servicios</a>
            </div>
        <?php endif; ?>
    </div>
</body>
<script src="/YH/proyecto sistema sabanas/mantenimiento_seguridad/panel_usuario/cargar_imagen_perfil.js"></script>

</html>