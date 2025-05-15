<?php
session_start();
require 'BACK/DB_connection.php';

// Inicializar variables de usuario
$user_id = null;
$user_name = null;
$user_img_db = null; // Para la imagen obtenida directamente de la BD
$user_page = null;

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: DaFont_Log.php'); // Redirigir al inicio de sesión si no está autenticado
    exit();
} else {
    $user_id = $_SESSION['user_id'];
    // $user_name = $_SESSION['user_name']; // Se obtendrá de la BD para asegurar que esté actualizado
    // $user_img_session = isset($_SESSION['user_img']) ? $_SESSION['user_img'] : null; // Se puede usar como fallback
}

// Consultar la información más reciente del usuario desde la base de datos
// Seleccionar solo las columnas necesarias para optimizar
$query = "SELECT usuario, nombres, apellidos, natal, imgPath, pagina FROM Usuario WHERE idUsuario = ?";
$stmt = mysqli_prepare($conn, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($resultado);

    if ($row) {
        $user_name = $row['usuario']; // Nombre de usuario de la BD
        $user_img_db = $row['imgPath'];   // Ruta de la imagen desde la BD (debería ser como 'IMG/Avatares/imagen.jpg')
        $user_page = $row['pagina'];

        // Opcional: Sincronizar la sesión si los datos de la BD son diferentes (especialmente útil si se cambian en otro lugar)
        if (!isset($_SESSION['user_name']) || $_SESSION['user_name'] !== $user_name) {
            $_SESSION['user_name'] = $user_name;
        }
        if (!isset($_SESSION['user_img']) || $_SESSION['user_img'] !== $user_img_db) {
            $_SESSION['user_img'] = $user_img_db;
        }

    } else {
        // El usuario de la sesión no existe en la BD (caso raro, podría ser por eliminación directa en BD)
        // Destruir sesión y redirigir
        session_unset(); // Elimina todas las variables de sesión
        session_destroy(); // Destruye la sesión
        header('Location: DaFont_Log.php?error=user_not_found_in_db');
        exit();
    }
    mysqli_stmt_close($stmt);
} else {
    // Error al preparar la consulta
    error_log("Error al preparar la consulta para obtener datos del perfil: " . mysqli_error($conn));
    // Mostrar un error genérico o redirigir
    echo "Error al cargar el perfil. Por favor, inténtalo más tarde.";
    exit();
}
mysqli_close($conn); // Cerrar la conexión a la BD aquí, ya que no se necesita más abajo.

?>

<!DOCTYPE html>
<html lang="es"> <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DaFont - Mi Perfil</title> <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/Perfil.css"> <style>
        @import url('https://fonts.googleapis.com/css2?family=Audiowide&family=Bonheur+Royale&family=Creepster&family=Eater&family=Henny+Penny&family=Iansui&family=Meddon&family=UnifrakturMaguntia&display=swap');
    </style>
    <script src="https://kit.fontawesome.com/093074d40c.js" crossorigin="anonymous"></script>
</head>
<body>
    
<header>
    <nav class="navbar">
        <div>
            <a href="DaFont_index.php" class="logo"><img id="navImg" src="Dafont1-Dark1.png" alt="Logo pagina Dafont"></a>
        </div>
      
        <ul class="nav-links" id="navMenu">
            <button id="closeMenu"><i class="fa fa-times"></i></button> <li class="dropdown">
                <button class="category-btn" name="Fantasia">Fantasia</button>
                <ul class="submenu">
                    <li><a href="#">Mágico</a></li>
                    <li><a href="#">Épico</a></li>
                    <li><a href="#">Oscuro</a></li>
                </ul>
            </li>
            <li class="dropdown">
                <button class="category-btn" name="Tecno" style="font-family: Audiowide, sans-serif;">Tecno</button>
                <ul class="submenu">
                    <li><a href="#">Mágico</a></li>
                    <li><a href="#">Épico</a></li>
                    <li><a href="#">Oscuro</a></li>
                </ul>
            </li>
            <li class="dropdown">
                <button class="category-btn" name="Gotico">Gotico</button>
                <ul class="submenu">
                    <li><a href="#">Europeo</a></li>
                    <li><a href="#">Latino</a></li>
                    <li><a href="#">Asiático</a></li>
                </ul>
            </li>
            <li class="dropdown">
                <button class="category-btn" name="Basico">Basico</button>
                <ul class="submenu">
                    <li><a href="#">Mágico</a></li>
                    <li><a href="#">Épico</a></li>
                    <li><a href="#">Oscuro</a></li>
                </ul>
            </li>
            <li class="dropdown">
                <button class="category-btn" name="Script">Script</button>
                <ul class="submenu">
                    <li><a href="#">Europeo</a></li>
                    <li><a href="#">Latino</a></li>
                    <li><a href="#">Asiático</a></li>
                </ul>
            </li>
            <li class="dropdown">
                <button class="category-btn" name="Dingbats">Display</button>
                <ul class="submenu">
                    <li><a href="#">Mágico</a></li>
                    <li><a href="#">Épico</a></li>
                    <li><a href="#">Oscuro</a></li>
                </ul>
            </li>
            <li>
                <div class="search-container">
                    <input type="text" class="search-bar" placeholder="Buscar...">
                    <button class="search-button"><i class="fa fa-search"></i></button>
                </div>
            </li>
        </ul>
        <button id="btnSesion" onclick="window.location.href='Dafont_Profile.php'">
            <?php echo htmlspecialchars($user_name); // Siempre mostrar el nombre de usuario de la BD/sesión actualizada ?> 
        </button>
        <div class="menu-hamburguesa">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>
</header>

<main>
    <div id="notification-area" class="notification-area" style="display: none;">
        </div>

    <button class="btn-filtros" id="btn-filtros">
        <i class="fa-solid fa-sliders"></i> 
    </button>
    
    <aside class="Filtros">
        <button class="hideMenu"><i class="fa fa-times"></i></button> <div class="Ajustes">  
            <div>
                <label for="text-input">Texto de Prueba:</label>
                <input type="text" name="text-input" id="text-input" placeholder="Escribe algo...">
            </div><br>
            <div>
                <label for="font-size-range">Tamaño de Fuente:</label> <input type="range" class="slider" id="font-size-range" min="10" max="100" value="24">
            </div><br>
            <label for="dkmode">Modo Claro/Oscuro</label> <button id="dkmode" aria-label="Cambiar modo claro u oscuro"><i class="fa-solid fa-circle-half-stroke"></i></button><br> <?php if($user_id !== null): // Estilo de sintaxis alternativo para if en plantillas ?>
                <button onclick="window.location.href='BACK/LogOut.php'" aria-label="Cerrar sesión"><i class="fa-solid fa-right-from-bracket"></i></button>
            <?php endif; ?>
        </div>
        <br>
    </aside>

    <div class="ContDatos">
        <h1>Tu Perfil</h1>    
        <div class="DatosUs">
            <?php
            $imagen_final_a_mostrar = $user_img_db; // La imagen de la BD tiene prioridad

            // Fallback a la imagen de sesión si la de la BD está vacía (aunque no debería si la lógica es correcta)
            if (empty($imagen_final_a_mostrar) && isset($_SESSION['user_img']) && !empty($_SESSION['user_img'])) {
                $imagen_final_a_mostrar = $_SESSION['user_img'];
            }

            if (empty($imagen_final_a_mostrar)): ?>
                <img src="IMG/image_default.png" alt="Imagen de perfil por defecto">
            <?php else: ?>
                <img src="<?php echo htmlspecialchars($imagen_final_a_mostrar); ?>" alt="Imagen de perfil de <?php echo htmlspecialchars($user_name); ?>">
            <?php endif; ?>

         
            <h2><?php echo htmlspecialchars($user_name); ?></h2>

            <?php if(isset($user_page) && $user_page !== ''): ?>
                <a href="<?php echo htmlspecialchars($user_page); ?>" target="_blank" rel="noopener noreferrer">Página oficial</a> <?php else: ?>
                <h6>Aún no tienes página oficial</h6>
            <?php endif; ?>
            <button class="EditarDatos" onclick="window.location.href='DaFont_Editar.php'">Modificar Datos</button>

        </div>
        
            <h3>Mis Fuentes Favoritas</h3>
            <p><em>Próximamente...</em></p>
        </div>
    </div>
</main>

<footer>
    <p>Las fuentes presentadas en este sitio web son propiedad de sus autores, y son freeware, shareware, demos o dominio público. La licencia mencionada encima del botón de descarga es sólo una indicación. Por favor, mira en los ficheros "Readme" en los zip o comprueba lo que se indica en la web del autor para los detalles, y contacta con él/ella en caso de duda. Si no hay autor/licencia indicados, es porque no tenemos la información, lo que no significa que sea gratis.</p>
    <p><a href="#">FAQ</a></p> </footer>

<script src="JS/script.js"></script>
<script src="JS/scriptCards.js"></script>
<script src="JS/breadcrumbing.js"></script>
<script src="JS/app.js"></script>
<script src="JS/scriptIndex.js"></script> <script src="JS/ALERTS.js"></script>
</body>
</html>