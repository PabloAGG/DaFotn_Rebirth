<?php
session_start();
require 'BACK/DB_connection.php';

// Inicializar variables de usuario
$user_id = null;
$user_name = null; // Esta variable será llenada por la consulta a la BD
$user_img_db = null;
$user_page = null;

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: DaFont_Log.php'); // Redirigir al inicio de sesión si no está autenticado
    exit();
} else {
    $user_id = $_SESSION['user_id'];
}

// Consultar la información más reciente del usuario desde la base de datos
$query_user_profile = "SELECT usuario, nombres, apellidos, natal, imgPath, pagina FROM Usuario WHERE idUsuario = ?";
$stmt_user_profile = mysqli_prepare($conn, $query_user_profile);

if ($stmt_user_profile) {
    mysqli_stmt_bind_param($stmt_user_profile, "i", $user_id);
    mysqli_stmt_execute($stmt_user_profile);
    $resultado_user_profile = mysqli_stmt_get_result($stmt_user_profile);
    $row_user_profile = mysqli_fetch_assoc($resultado_user_profile);

    if ($row_user_profile) {
        $user_name = $row_user_profile['usuario']; // Nombre de usuario de la BD (usado en el header)
        $user_img_db = $row_user_profile['imgPath'];
        $user_page = $row_user_profile['pagina'];

        // Sincronizar sesión si es necesario
        if (!isset($_SESSION['user_name']) || $_SESSION['user_name'] !== $user_name) {
            $_SESSION['user_name'] = $user_name;
        }
        if (!isset($_SESSION['user_img']) || $_SESSION['user_img'] !== $user_img_db) {
            $_SESSION['user_img'] = $user_img_db;
        }
    } else {
        session_unset();
        session_destroy();
        header('Location: DaFont_Log.php?error=user_not_found_in_db');
        exit();
    }
    mysqli_stmt_close($stmt_user_profile);
} else {
    error_log("Error al preparar la consulta para obtener datos del perfil: " . mysqli_error($conn));
    echo "Error al cargar el perfil. Por favor, inténtalo más tarde.";
    exit();
}

// --- Lógica para fuentes favoritas y publicadas (sin cambios respecto a tu código original) ---
$fav_fonts = [];
if ($user_id) {
    $sql_fav_fonts = "SELECT
                        f.idFont, f.fontName, f.fontFamilyCSS, f.fontStyleFallback,
                        f.descargas, f.licenciaDescripcion,
                        u.usuario AS nombreAutor, u.idUsuario AS idAutor,
                        (SELECT AVG(c.estrellas) FROM calificaciones c WHERE c.idFont = f.idFont) AS promedioEstrellas,
                        (SELECT COUNT(c.idCalf) FROM calificaciones c WHERE c.idFont = f.idFont) AS totalCalificaciones,
                        1 AS currentUserHasFavorited
                      FROM Fonts f
                      JOIN FavFonts ff ON f.idFont = ff.idFont
                      LEFT JOIN Usuario u ON f.fontAutor = u.idUsuario
                      WHERE ff.idUsuario = ?
                      ORDER BY ff.fechaAgregado DESC";
    $stmt_fav = mysqli_prepare($conn, $sql_fav_fonts);
    if ($stmt_fav) {
        mysqli_stmt_bind_param($stmt_fav, "i", $user_id);
        mysqli_stmt_execute($stmt_fav);
        $result_fav_fonts = mysqli_stmt_get_result($stmt_fav);
        while ($row_fav = mysqli_fetch_assoc($result_fav_fonts)) { // Cambiado $row a $row_fav para evitar conflicto
            $fav_fonts[] = $row_fav;
        }
        mysqli_stmt_close($stmt_fav);
    } else {
        error_log("Error al preparar la consulta de fuentes favoritas: " . mysqli_error($conn));
    }
}

$published_fonts = [];
if ($user_id) {
    $sql_published_fonts = "SELECT
                                f.idFont, f.fontName, f.fontFamilyCSS, f.fontStyleFallback,
                                f.descargas, f.licenciaDescripcion,
                                u.usuario AS nombreAutor, u.idUsuario AS idAutor,
                                (SELECT AVG(c.estrellas) FROM calificaciones c WHERE c.idFont = f.idFont) AS promedioEstrellas,
                                (SELECT COUNT(c.idCalf) FROM calificaciones c WHERE c.idFont = f.idFont) AS totalCalificaciones,
                                (SELECT COUNT(*) FROM FavFonts ff WHERE ff.idFont = f.idFont AND ff.idUsuario = ?) AS currentUserHasFavorited
                              FROM Fonts f
                              LEFT JOIN Usuario u ON f.fontAutor = u.idUsuario
                              WHERE f.fontAutor = ?
                              ORDER BY f.fechaSubida DESC";
    $stmt_pub = mysqli_prepare($conn, $sql_published_fonts);
    if ($stmt_pub) {
        mysqli_stmt_bind_param($stmt_pub, "ii", $user_id, $user_id);
        mysqli_stmt_execute($stmt_pub);
        $result_published_fonts = mysqli_stmt_get_result($stmt_pub);
        while ($row_pub = mysqli_fetch_assoc($result_published_fonts)) { // Cambiado $row a $row_pub
            $published_fonts[] = $row_pub;
        }
        mysqli_stmt_close($stmt_pub);
    } else {
        error_log("Error al preparar la consulta de fuentes publicadas: " . mysqli_error($conn));
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DaFont - Mi Perfil</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/Perfil.css">
    <style>
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
            <button id="closeMenu"><i class="fa fa-close"></i></button>
            <?php
            // $conn ya está disponible desde el require 'BACK/DB_connection.php'; al inicio del archivo
            $sql_categories_header = "SELECT nombreCategoria FROM Categorias ORDER BY nombreCategoria";
            $result_categories_header = mysqli_query($conn, $sql_categories_header);
            $categories_for_header = [];
            if ($result_categories_header && mysqli_num_rows($result_categories_header) > 0) {
                while ($cat_row_header = mysqli_fetch_assoc($result_categories_header)) {
                    $categories_for_header[] = $cat_row_header['nombreCategoria'];
                }
            }

            $subcategories_map_header = [
                'Fantasia' => ['Mágico', 'Épico', 'Oscuro'],
                'Tecno' => ['Sci-Fi', 'Moderno', 'Futurista'],
                'Gotico' => ['Europeo', 'Medieval', 'Vampiro'],
                'Basico' => ['Serif', 'Sans-Serif', 'Monospace'],
                'Script' => ['Caligrafía', 'Manuscrito', 'Firma'],
                'Display' => ['Decorativa', 'Titular', 'Retro']
            ];

            foreach ($categories_for_header as $category_item_name_header) {
                echo '<li class="dropdown">';
                echo '<a href="DaFont_index.php?category=' . urlencode($category_item_name_header) . '" class="category-btn">' . htmlspecialchars($category_item_name_header) . '</a>';
                if (isset($subcategories_map_header[$category_item_name_header])) {
                    echo '<ul class="submenu">';
                    foreach ($subcategories_map_header[$category_item_name_header] as $subcategory_item_name_header) {
                        echo '<li><a href="DaFont_index.php?category=' . urlencode($category_item_name_header) . '&subcategory=' . urlencode($subcategory_item_name_header) . '">' . htmlspecialchars($subcategory_item_name_header) . '</a></li>';
                    }
                    echo '</ul>';
                }
                echo '</li>';
            }
            ?>
            <li>
                <form action="DaFont_index.php" method="GET" class="search-container-form">
                    <div class="search-container">
                        <input type="text" name="search_term" class="search-bar" placeholder="Buscar fuentes..." value="<?php echo isset($_GET['search_term']) ? htmlspecialchars($_GET['search_term']) : ''; ?>">
                        <button type="submit" class="search-button"><i class="fa fa-search"></i></button>
                    </div>
                </form>
            </li>
        </ul>
        <button id="btnSesion" onclick="window.location.href='Dafont_Profile.php'">
             <?php echo htmlspecialchars($user_name); // $user_name fue obtenido de la BD al inicio de esta página ?> 
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
        <button class="hideMenu"><i class="fa fa-solid fa-angles-left"></i></button> <div class="Ajustes">  
            <div>
                <label for="text-input">Texto de Prueba:</label>
                <input type="text" name="text-input" id="text-input" placeholder="Escribe algo...">
            </div><br>
            <div>
                <label for="font-size-range">Tamaño de Fuente:</label> <input type="range" class="slider" id="font-size-range" min="10" max="100" value="24">
            </div><br>
            <label for="dkmode">Modo Claro/Oscuro</label> <button id="dkmode" aria-label="Cambiar modo claro u oscuro"><i class="fa-solid fa-circle-half-stroke"></i></button><br>
            <button onclick="window.location.href='BACK/LogOut.php'" aria-label="Cerrar sesión"><i class="fa-solid fa-right-from-bracket"></i></button>
        </div>
        <br>
    </aside>

    <div class="ContDatos">
        <h1>Tu Perfil</h1>    
        <div class="DatosUs">
            <?php
            $imagen_final_a_mostrar = $user_img_db;
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
                <a href="<?php echo htmlspecialchars($user_page); ?>" target="_blank" rel="noopener noreferrer">Página oficial</a>
            <?php else: ?>
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

    <div id="seccionFavoritas" class="fuentes-listado active-listado"> <h3>Tus Fuentes Favoritas</h3>
            <?php if (!empty($fav_fonts)): ?>
                <?php foreach ($fav_fonts as $font_item): // Cambiado $font a $font_item para evitar conflicto con posible $font de detalles ?>
                    <div class="font-card">
                        <div class="presentacion">
                            <h2 class="font-name" onclick="window.location.href='Dafont_FontDetails.php?id=<?php echo $font_item['idFont']; ?>'"><?php echo htmlspecialchars($font_item['fontName']); ?></h2>
                            <span class="author">
                                Autor:
                                <?php if ($font_item['idAutor']): ?>
                                    <a id="autorPerfil" href="DaFont-AuthorProfile.php?id=<?php echo $font_item['idAutor']; ?>"><?php echo htmlspecialchars($font_item['nombreAutor'] ?? 'Desconocido'); ?></a>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($font_item['nombreAutor'] ?? 'N/A'); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        <br>
                        <div class="font-preview" style="font-family: '<?php echo htmlspecialchars($font_item['fontFamilyCSS']); ?>', <?php echo htmlspecialchars($font_item['fontStyleFallback']); ?>;"><?php echo htmlspecialchars($font_item['fontName']); ?></div>
                        <div class="font-details">
                            <span class="downloads"><?php echo number_format($font_item['descargas']); ?> descargas </span> <span class="license"><?php echo htmlspecialchars($font_item['licenciaDescripcion']); ?></span>
                            <div class="stars-display" data-font-id="<?php echo $font_item['idFont']; ?>">
                                <?php
                                $promedio = round($font_item['promedioEstrellas'] ?? 0);
                                $totalVotos = (int) ($font_item['totalCalificaciones'] ?? 0);
                                for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?php echo ($i <= $promedio) ? 'filled' : ''; ?>" data-value="<?php echo $i; ?>">&#9733;</span>
                                <?php endfor; ?>
                                <span class="rating-average">(<?php echo number_format($font_item['promedioEstrellas'] ?? 0, 1); ?> de <?php echo $totalVotos; ?> votos)</span>
                            </div>
                        </div>
                        <?php
                        $isFavorite = true; // Todas en esta lista son favoritas
                        $favIconClass = "fa-solid fa-heart";
                        ?>
                        <button class="btn btn-favorite" title="Quitar de favoritos" data-fontid="<?php echo $font_item['idFont']; ?>" data-isfavorite="true"><i class="<?php echo $favIconClass;?>"></i></button>
                        <button class="download-btn" title="Descargar fuente (ejemplo TXT)" data-font-id="<?php echo $font_item['idFont']; ?>"><i class="fa-solid fa-download"></i></button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aún no has añadido ninguna fuente a tus favoritas.</p>
            <?php endif; ?>
        </div>

        <div id="seccionPublicadas" class="fuentes-listado">
            <h3>Tus Fuentes Publicadas</h3>
            <?php if (!empty($published_fonts)): ?>
                <?php foreach ($published_fonts as $font_item): // Cambiado $font a $font_item ?>
                    <div class="font-card">
                        <div class="presentacion">
                            <h2 class="font-name" onclick="window.location.href='Dafont_FontDetails.php?id=<?php echo $font_item['idFont']; ?>'"><?php echo htmlspecialchars($font_item['fontName']); ?></h2>
                            <span class="author">Publicada por ti</span>
                        </div>
                        <br>
                        <div class="font-preview" style="font-family: '<?php echo htmlspecialchars($font_item['fontFamilyCSS']); ?>', <?php echo htmlspecialchars($font_item['fontStyleFallback']); ?>;"><?php echo htmlspecialchars($font_item['fontName']); ?></div>
                        <div class="font-details">
                             <span class="downloads"><?php echo number_format($font_item['descargas']); ?> descargas </span> <span class="license"><?php echo htmlspecialchars($font_item['licenciaDescripcion']); ?></span>
                            <div class="stars-display" data-font-id="<?php echo $font_item['idFont']; ?>">
                                <?php
                                $promedio = round($font_item['promedioEstrellas'] ?? 0);
                                $totalVotos = (int) ($font_item['totalCalificaciones'] ?? 0);
                                for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?php echo ($i <= $promedio) ? 'filled' : ''; ?>" data-value="<?php echo $i; ?>">&#9733;</span>
                                <?php endfor; ?>
                                <span class="rating-average">(<?php echo number_format($font_item['promedioEstrellas'] ?? 0, 1); ?> de <?php echo $totalVotos; ?> votos)</span>
                            </div>
                        </div>
                        <?php
                        $isFavorite = ($font_item['currentUserHasFavorited'] > 0);
                        $favIconClass = $isFavorite ? "fa-solid fa-heart" : "fa-regular fa-heart";
                        ?>
                        <button class="btn btn-favorite" title="<?php echo $isFavorite ? 'Quitar de favoritos' : 'Añadir a favoritos'; ?>" data-fontid="<?php echo $font_item['idFont']; ?>" data-isfavorite="<?php echo $isFavorite ? 'true' : 'false'; ?>"><i class="<?php echo $favIconClass;?>"></i></button>
                        <button class="download-btn" title="Descargar fuente (ejemplo TXT)" data-font-id="<?php echo $font_item['idFont']; ?>"><i class="fa-solid fa-download"></i></button>
                        </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aún no has publicado ninguna fuente.</p>
            <?php endif; ?>
        </div>
    </div> </main>

<footer>
    <p>Las fuentes presentadas en este sitio web son propiedad de sus autores...</p>
    <p><a href="DaFont_FAQ.php">FAQ</a></p> 
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