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


$fav_fonts = [];
if ($user_id) { // Solo si el usuario está logueado
    // La subconsulta para currentUserHasFavorited no es necesaria aquí, ya que todas estas son favoritas.
    $sql_fav_fonts = "SELECT
                        f.idFont, f.fontName, f.fontFamilyCSS, f.fontStyleFallback,
                        f.descargas, f.licenciaDescripcion,
                        u.usuario AS nombreAutor, u.idUsuario AS idAutor,
                        (SELECT AVG(c.estrellas) FROM calificaciones c WHERE c.idFont = f.idFont) AS promedioEstrellas,
                        (SELECT COUNT(c.idCalf) FROM calificaciones c WHERE c.idFont = f.idFont) AS totalCalificaciones,
                        1 AS currentUserHasFavorited -- Todas estas son favoritas del usuario actual
                      FROM Fonts f
                      JOIN FavFonts ff ON f.idFont = ff.idFont
                      LEFT JOIN Usuario u ON f.fontAutor = u.idUsuario
                      WHERE ff.idUsuario = ?
                      ORDER BY ff.fechaAgregado DESC"; // O por nombre de fuente, etc.

    $stmt_fav = mysqli_prepare($conn, $sql_fav_fonts);
    if ($stmt_fav) {
        mysqli_stmt_bind_param($stmt_fav, "i", $user_id);
        mysqli_stmt_execute($stmt_fav);
        $result_fav_fonts = mysqli_stmt_get_result($stmt_fav);
        while ($row = mysqli_fetch_assoc($result_fav_fonts)) {
            $fav_fonts[] = $row;
        }
        mysqli_stmt_close($stmt_fav);
    } else {
        error_log("Error al preparar la consulta de fuentes favoritas: " . mysqli_error($conn));
    }
}

// --- Obtener Fuentes Publicadas por el Usuario ---
$published_fonts = [];
if ($user_id) { // Solo si el usuario está logueado
    // Para las fuentes publicadas por el usuario, necesitamos saber si ÉL MISMO las ha marcado como favoritas.
    $sql_published_fonts = "SELECT
                                f.idFont, f.fontName, f.fontFamilyCSS, f.fontStyleFallback,
                                f.descargas, f.licenciaDescripcion,
                                u.usuario AS nombreAutor, u.idUsuario AS idAutor, -- Aquí idAutor siempre será $user_id
                                (SELECT AVG(c.estrellas) FROM calificaciones c WHERE c.idFont = f.idFont) AS promedioEstrellas,
                                (SELECT COUNT(c.idCalf) FROM calificaciones c WHERE c.idFont = f.idFont) AS totalCalificaciones,
                                (SELECT COUNT(*) FROM FavFonts ff WHERE ff.idFont = f.idFont AND ff.idUsuario = ?) AS currentUserHasFavorited
                              FROM Fonts f
                              LEFT JOIN Usuario u ON f.fontAutor = u.idUsuario -- El join es para mantener la estructura, pero u.idUsuario será $user_id
                              WHERE f.fontAutor = ? -- Fuentes donde el autor es el usuario actual
                              ORDER BY f.fechaSubida DESC";

    $stmt_pub = mysqli_prepare($conn, $sql_published_fonts);
    if ($stmt_pub) {
        mysqli_stmt_bind_param($stmt_pub, "ii", $user_id, $user_id); // El $user_id se usa dos veces
        mysqli_stmt_execute($stmt_pub);
        $result_published_fonts = mysqli_stmt_get_result($stmt_pub);
        while ($row = mysqli_fetch_assoc($result_published_fonts)) {
            $published_fonts[] = $row;
        }
        mysqli_stmt_close($stmt_pub);
    } else {
        error_log("Error al preparar la consulta de fuentes publicadas: " . mysqli_error($conn));
    }
}


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

    </div>

    <div class="contenedor-fuentes">
         <div class="radio-inputs">
            <label class="radio">
                <input type="radio" name="radio" id="radioFacil" value="MisFavs" checked />
                <span class="name">Favoritas</span>
            </label>
            <label class="radio">
                <input type="radio" name="radio" id="radioDificil" value="MisPub" />
                <span class="name">Mis fuentes</span>
            </label>
        </div>
    </div>

    <div id="seccionFavoritas" class="fuentes-listado">
            <h3>Tus Fuentes Favoritas</h3>
            <?php if (!empty($fav_fonts)): ?>
                <?php foreach ($fav_fonts as $font): ?>
                    <div class="font-card">
                        <div class="presentacion">
                            <h2 class="font-name" onclick="window.location.href='Dafont_FontDetails.php?id=<?php echo $font['idFont']; ?>'"><?php echo htmlspecialchars($font['fontName']); ?></h2>
                            <span class="author">
                                Autor:
                                <?php if ($font['idAutor']): ?>
                                    <a id="autorPerfil" href="DaFont-AuthorProfile.php?id=<?php echo $font['idAutor']; ?>"><?php echo htmlspecialchars($font['nombreAutor'] ?? 'Desconocido'); ?></a>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($font['nombreAutor'] ?? 'N/A'); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        <br>
                        <div class="font-preview" style="font-family: '<?php echo htmlspecialchars($font['fontFamilyCSS']); ?>', <?php echo htmlspecialchars($font['fontStyleFallback']); ?>;"><?php echo htmlspecialchars($font['fontName']); ?></div>
                        <div class="font-details">
                            <span class="downloads"><?php echo number_format($font['descargas']); ?> descargas </span> <span class="license"><?php echo htmlspecialchars($font['licenciaDescripcion']); ?></span>
                            <div class="stars-display" data-font-id="<?php echo $font['idFont']; ?>">
                                <?php
                                $promedio = round($font['promedioEstrellas'] ?? 0);
                                $totalVotos = (int) ($font['totalCalificaciones'] ?? 0);
                                for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?php echo ($i <= $promedio) ? 'filled' : ''; ?>" data-value="<?php echo $i; ?>">&#9733;</span>
                                <?php endfor; ?>
                                <span class="rating-average">(<?php echo number_format($font['promedioEstrellas'] ?? 0, 1); ?> de <?php echo $totalVotos; ?> votos)</span>
                            </div>
                        </div>
                        <?php
                     
                        $isFavorite = true;
                        $favIconClass = "fa-solid fa-heart";
                        ?>
                        <button class="btn btn-favorite" title="Quitar de favoritos" data-fontid="<?php echo $font['idFont']; ?>" data-isfavorite="true"><i class="<?php echo $favIconClass;?>"></i></button>
                        <button class="download-btn" title="Descargar fuente (ejemplo TXT)" data-font-id="<?php echo $font['idFont']; ?>"><i class="fa-solid fa-download"></i></button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aún no has añadido ninguna fuente a tus favoritas.</p>
            <?php endif; ?>
        </div>

        <div id="seccionPublicadas" class="fuentes-listado">
            <h3>Tus Fuentes Publicadas</h3>
            <?php if (!empty($published_fonts)): ?>
                <?php foreach ($published_fonts as $font): ?>
                    <div class="font-card">
                        <div class="presentacion">
                            <h2 class="font-name" onclick="window.location.href='Dafont_FontDetails.php?id=<?php echo $font['idFont']; ?>'"><?php echo htmlspecialchars($font['fontName']); ?></h2>
                            <span class="author">Publicada por ti</span>
                        </div>
                        <br>
                        <div class="font-preview" style="font-family: '<?php echo htmlspecialchars($font['fontFamilyCSS']); ?>', <?php echo htmlspecialchars($font['fontStyleFallback']); ?>;"><?php echo htmlspecialchars($font['fontName']); ?></div>
                        <div class="font-details">
                             <span class="downloads"><?php echo number_format($font['descargas']); ?> descargas </span> <span class="license"><?php echo htmlspecialchars($font['licenciaDescripcion']); ?></span>
                            <div class="stars-display" data-font-id="<?php echo $font['idFont']; ?>">
                                <?php
                                $promedio = round($font['promedioEstrellas'] ?? 0);
                                $totalVotos = (int) ($font['totalCalificaciones'] ?? 0);
                                for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?php echo ($i <= $promedio) ? 'filled' : ''; ?>" data-value="<?php echo $i; ?>">&#9733;</span>
                                <?php endfor; ?>
                                <span class="rating-average">(<?php echo number_format($font['promedioEstrellas'] ?? 0, 1); ?> de <?php echo $totalVotos; ?> votos)</span>
                            </div>
                        </div>
                        <?php
                  
                        $isFavorite = ($font['currentUserHasFavorited'] > 0);
                        $favIconClass = $isFavorite ? "fa-solid fa-heart" : "fa-regular fa-heart";
                        ?>
                        <!-- <button class="btn btn-favorite" title="<?php echo $isFavorite ? 'Quitar de favoritos' : 'Añadir a favoritos'; ?>" data-fontid="<?php echo $font['idFont']; ?>" data-isfavorite="<?php echo $isFavorite ? 'true' : 'false'; ?>"><i class="<?php echo $favIconClass;?>"></i></button> -->
                        <button class="download-btn" title="Descargar fuente (ejemplo TXT)" data-font-id="<?php echo $font['idFont']; ?>"><i class="fa-solid fa-download"></i></button>
                        </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aún no has publicado ninguna fuente.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<footer>
    <p>Las fuentes presentadas en este sitio web son propiedad de sus autores, y son freeware, shareware, demos o dominio público. La licencia mencionada encima del botón de descarga es sólo una indicación. Por favor, mira en los ficheros "Readme" en los zip o comprueba lo que se indica en la web del autor para los detalles, y contacta con él/ella en caso de duda. Si no hay autor/licencia indicados, es porque no tenemos la información, lo que no significa que sea gratis.</p>
    <p><a href="#">FAQ</a></p> 
</footer>

<script src="JS/script.js"></script>
<script src="JS/scriptCards.js"></script>
<script src="JS/breadcrumbing.js"></script>
<script src="JS/perfil.js"></script>
<script src="JS/app.js"></script>
<script src="JS/scriptIndex.js"></script> 
<script src="JS/ALERTS.js"></script>

</body>
</html>