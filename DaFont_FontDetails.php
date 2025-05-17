<?php
session_start();
require 'BACK/DB_connection.php'; // Conexión a la BD

$user_id_session = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0; // ID de usuario de la sesión

$font_id_param = isset($_GET['id']) ? (int)$_GET['id'] : 0; // ID de la fuente desde la URL
$font = null; // Variable para almacenar los datos de la fuente

// Obtener parámetros de categoría y subcategoría de la URL (enviados desde DaFont_index.php)
$category_param = isset($_GET['category']) ? trim($_GET['category']) : null;
$subcategory_param = isset($_GET['subcategory']) ? trim($_GET['subcategory']) : null;

if ($font_id_param > 0) {
    // Consulta SQL para obtener detalles de la fuente
    $sql_font_detail = "SELECT
    f.idFont,
    f.fontName,
    f.fontFamilyCSS,
    f.fontStyleFallback,
    f.descargas,
    f.licenciaDescripcion,
    f.fechaSubida,
    u.usuario AS nombreAutor,
    u.idUsuario AS idAutor,
    u.imgPath AS autorImgPath,
    u.pagina AS autorPagina,
    (SELECT AVG(c.estrellas) FROM calificaciones c WHERE c.idFont = f.idFont) AS promedioEstrellas,
    (SELECT COUNT(c.idCalf) FROM calificaciones c WHERE c.idFont = f.idFont) AS totalCalificaciones,
    (SELECT c_user.estrellas FROM calificaciones c_user WHERE c_user.idFont = f.idFont AND c_user.idUsuario = ?) AS currentUserRating, /* Calificación del usuario actual */
    (SELECT COUNT(*) FROM FavFonts ff WHERE ff.idFont = f.idFont AND ff.idUsuario = ?) AS currentUserHasFavorited /* Asumo que tienes esto para favoritos */
FROM Fonts f
LEFT JOIN Usuario u ON f.fontAutor = u.idUsuario
WHERE f.idFont = ? ";

    $stmt = mysqli_prepare($conn, $sql_font_detail);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iii", $user_id_session, $user_id_session, $font_id_param);
        mysqli_stmt_execute($stmt);
        $result_font_detail = mysqli_stmt_get_result($stmt);
        if ($result_font_detail && mysqli_num_rows($result_font_detail) > 0) {
            $font = mysqli_fetch_assoc($result_font_detail); // Carga los datos de la fuente en $font
        }
        mysqli_stmt_close($stmt);
    } else {
        error_log("Error al preparar la consulta para detalles de fuente: " . mysqli_error($conn));
    }
}

// Variable para el nombre de usuario en el encabezado (si está en sesión)
$user_name_session = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;

$comentarios_para_fuente = []; 
if ($font_id_param > 0) { 
   
    $sql_comentarios = "SELECT
                            c.idComentario,
                            c.idUsuario,
                            c.idFont,
                            c.comentario AS textoComentario, 
                            FormatearFecha(c.fechaComen) AS fechaComen,
                            u.usuario AS nombreUsuarioComentador,
                            u.imgPath AS imgPathComentador

                        FROM coment c
                        JOIN Usuario u ON c.idUsuario = u.idUsuario
                        WHERE c.idFont = ?
                        ORDER BY c.fechaComen DESC"; // Mostrar los más recientes primero

    $stmt_comentarios = mysqli_prepare($conn, $sql_comentarios);
    if ($stmt_comentarios) {
        mysqli_stmt_bind_param($stmt_comentarios, "i", $font_id_param);
        mysqli_stmt_execute($stmt_comentarios);
        $result_comentarios = mysqli_stmt_get_result($stmt_comentarios);
        while ($row_comentario = mysqli_fetch_assoc($result_comentarios)) {
            // Formatear la fecha aquí si es necesario
            $row_comentario['fecha_formateada'] = $row_comentario['fechaComen'];
            $comentarios_para_fuente[] = $row_comentario;
        }
        mysqli_stmt_close($stmt_comentarios);
    } else {
        error_log("Error al preparar la consulta de comentarios para la fuente: " . mysqli_error($conn));
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($font && isset($font['fontName'])) ? htmlspecialchars($font['fontName']) : 'Detalles de Fuente'; ?> - DaFont</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/styleCards.css">
    <link rel="stylesheet" href="CSS/fontD.css">
    <style>
      
        <?php if ($font && isset($font['fontFamilyCSS'])): /* Solo si $font y fontFamilyCSS existen */ ?>
        @font-face {
            font-family: '<?php echo htmlspecialchars($font['fontFamilyCSS']); ?>';
         <?php echo htmlspecialchars($font['fontFamilyCSS']); ?>.ttf'); */
        }
        <?php endif; ?>
        
        .actions button { margin-right: 10px; }
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
            // Menú del encabezado (idéntico a DaFont_index.php, considera un include)
            $sql_categories_header = "SELECT nombreCategoria FROM Categorias ORDER BY nombreCategoria";
            $result_categories_header = mysqli_query($conn, $sql_categories_header);
            $categories_for_header = [];
            if ($result_categories_header && mysqli_num_rows($result_categories_header) > 0) {
                while ($cat_row_header = mysqli_fetch_assoc($result_categories_header)) {
                    $categories_for_header[] = $cat_row_header['nombreCategoria'];
                }
            }
            $subcategories_map_header = [ /* ... tu mapa de subcategorías ... */ ];
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
        <button id="btnSesion" 
            <?php if(!isset($user_id_session) || $user_id_session === 0){ ?>
            onclick="window.location.href='Dafont_Log.php'">
            <i class="fa-solid fa-circle-user"></i></button>
            <?php } else { ?>
            onclick="window.location.href='Dafont_Profile.php'">
            <?php echo htmlspecialchars($user_name_session); ?></button>
            <?php } ?>
        <div class="menu-hamburguesa"><span></span><span></span><span></span></div>
    </nav>
</header>

<main>
    <nav id="breadcrumb">
        <span><a href="DaFont_index.php">Inicio</a></span>
        <?php
        // ** LÓGICA DEL BREADCRUMB CORREGIDA Y DETALLADA **
        
        // 1. Mostrar Categoría si está presente en la URL
        if ($category_param) {
            echo '<span><a href="DaFont_index.php?category=' . urlencode($category_param) . '">' . htmlspecialchars(urldecode($category_param)) . '</a></span>';
        }

        // 2. Mostrar Subcategoría si está presente (y la categoría también)
        if ($category_param && $subcategory_param) {
            echo '<span><a href="DaFont_index.php?category=' . urlencode($category_param) . '&subcategory=' . urlencode($subcategory_param) . '">' . htmlspecialchars(urldecode($subcategory_param)) . '</a></span>';
        }
        
        // 3. Mostrar el nombre de la fuente actual o "Fuente no encontrada"
        // Esta es la parte crucial para el problema que mencionas.
        if ($font && isset($font['fontName']) && !empty(trim($font['fontName']))) {
            // Si la variable $font existe, tiene un 'fontName' y no está vacío, lo mostramos.
            // No es un enlace porque ya estás en la página de esta fuente.
            echo '<span>' . htmlspecialchars($font['fontName']) . '</span>';
        } elseif ($font_id_param > 0) { 
            // Si se pasó un ID de fuente en la URL, pero $font está vacío (no se encontró la fuente),
            // entonces mostramos "Fuente no encontrada".
            echo '<span>Fuente no encontrada</span>';
        }
        // Si no se pasó un ID de fuente válido en la URL (font_id_param no es > 0),
        // el breadcrumb solo mostrará "Inicio" (y categoría/subcategoría si estaban en la URL por alguna razón).
        ?>
    </nav>

    <?php if ($font): // Solo mostrar el resto si la fuente fue encontrada ?>
        <div class="font-detail-container">
            <div class="font-header">
            <h1><?php echo htmlspecialchars($font['fontName']); ?></h1>
 <div class="actions">
                <?php
                $isFavorite_detail = ($font['currentUserHasFavorited'] > 0);
                $favIconClass_detail = $isFavorite_detail ? "fa-solid fa-heart" : "fa-regular fa-heart";
                ?>
                <button class="btn btn-favorite" title="<?php echo $isFavorite_detail ? 'Quitar de favoritos' : 'Añadir a favoritos'; ?>" data-fontid="<?php echo $font['idFont']; ?>" data-isfavorite="<?php echo $isFavorite_detail ? 'true' : 'false'; ?>"><i class="<?php echo $favIconClass_detail;?>"></i></button>
                <button class="download-btn" title="Descargar fuente (ejemplo TXT)" data-font-id="<?php echo $font['idFont']; ?>"><i class="fa-solid fa-download"></i></button>
            </div>
        </div>
            <div class="controls">
                <div>
                    <label for="text-input-detail">Texto de Prueba:</label>
                    <input type="text" id="text-input-detail" placeholder="Escribe algo..." value="<?php echo htmlspecialchars($font['fontName']); ?>">
                </div>
                <div>
                    <label for="font-size-range-detail">Tamaño:</label>
                    <input type="range" class="slider" id="font-size-range-detail" min="10" max="150" value="48">
                    <span id="font-size-value-detail">48px</span>
                </div>
                 <button id="dkmode"><i class="fa-solid fa-circle-half-stroke"></i></button>
            </div>

            <div id="font-preview-detailed" class="font-preview-detailed" style="font-family: '<?php echo htmlspecialchars($font['fontFamilyCSS']); ?>', <?php echo htmlspecialchars($font['fontStyleFallback']); ?>;">
                <?php echo htmlspecialchars($font['fontName']); ?>
            </div>

            <div class="details-section author-info">
                <h3>Autor</h3>
                <p>
                    <?php if ($font['idAutor']): ?>
                        <?php if ($font['autorImgPath']): ?>
                            <img src="<?php echo htmlspecialchars($font['autorImgPath']); ?>" alt="Avatar de <?php echo htmlspecialchars($font['nombreAutor']); ?>">
                        <?php else: ?>
                             <img src="IMG/DefaultProfile.png" alt="Avatar por defecto">
                        <?php endif; ?>
                        <a href="DaFont-AuthorProfile.php?id=<?php echo $font['idAutor']; ?>"><?php echo htmlspecialchars($font['nombreAutor'] ?? 'Desconocido'); ?></a>
                        <?php if ($font['autorPagina']): ?>
                            (<a href="<?php echo htmlspecialchars($font['autorPagina']); ?>" target="_blank" rel="noopener noreferrer">Página Web</a>)
                        <?php endif; ?>
                    <?php else: ?>
                        Autor Desconocido
                    <?php endif; ?>
                </p>
                
            </div>

            <div class="details-section">
                <h3>Licencia</h3>
                <p><?php echo htmlspecialchars($font['licenciaDescripcion']); ?></p>
            </div>

            <div class="details-section">
                <h3>Descargas</h3>
                <p><?php echo number_format($font['descargas']); ?></p>
            </div>
            
            <div class="details-section">
                <h3>Subida el</h3>
                <p><?php echo date("d/m/Y", strtotime($font['fechaSubida'])); ?></p>
            </div>

           <div class="details-section">
    <section id="stars">
        <div class="stars-display" data-font-id="<?php echo $font['idFont']; ?>"> <h3>Calificación</h3>
            <?php
            $promedio_detail = round($font['promedioEstrellas'] ?? 0);
            $totalVotos_detail = (int) ($font['totalCalificaciones'] ?? 0);
            for ($i = 1; $i <= 5; $i++): ?>
                <span class="star <?php echo ($i <= $promedio_detail) ? 'filled' : ''; ?>" data-value="<?php echo $i; ?>">&#9733;</span>
            <?php endfor; ?>
            <span class="rating-average">(<?php echo number_format($font['promedioEstrellas'] ?? 0, 1); ?> de <?php echo $totalVotos_detail; ?> votos)</span> </div>
       <?php if (isset($_SESSION['user_id'])): ?>
    <div class="RateUs">
        <h3>¡Califica esta fuente!</h3>
       <div class="rating-interactive"
             data-font-id="<?php echo $font['idFont']; ?>"
             data-current-rating="<?php echo isset($font['currentUserRating']) ? (int)$font['currentUserRating'] : 0; ?>">
              <span class="interactive-star" data-value="1" title="Mala">&#9733;</span>
            <span class="interactive-star" data-value="2" title="Regular">&#9733;</span>
            <span class="interactive-star" data-value="3" title="Buena">&#9733;</span>
            <span class="interactive-star" data-value="4" title="Muy Buena">&#9733;</span>
            <span class="interactive-star" data-value="5" title="Excelente!">&#9733;</span>
        </div>
        <div id="mensaje-calificacion" class="mensaje-ajax" style="display:none;"></div>
        <p id="user-current-rating" style="font-size: 0.9em; margin-top: 5px;"></p> </div>
<?php else: ?>
    <p style="margin-top:15px;"><a href="DaFont_Log.php">Inicia sesión</a> para calificar esta fuente.</p>
<?php endif; ?>
    </section>
</div>
            
           
                 <section class="comentarios-seccion">
        <h3>Comentarios</h3>
        <form id="form-comentario" onsubmit="guardarComentario(event); return false;">
            <input type="hidden" name="font_id_comentario" value="<?php echo $font_id_param; ?>">
            <textarea name="comentario" placeholder="Escribe un comentario..." required aria-label="Escribe un comentario"></textarea>
            <br>
            <div id="mensaje-comentario" class="mensaje-ajax" style="display:none;"></div>
            <button type="submit" class="btn-ver-mas"><i class="fa-solid fa-paper-plane"></i></button>
        </form>

        <div id="lista-comentarios">
                <?php if (empty($comentarios_para_fuente)): ?>
                    <p id="no-comments-message">Sé el primero en comentar.</p>
                <?php else: ?>
                    <?php foreach ($comentarios_para_fuente as $coment): ?>
                        <div class="comentario-item" id="comentario-<?php echo $coment['idComentario']; ?>">
                            <div class="comenPresent">
                                <div>
                                <?php if (!empty($coment['imgPathComentador'])): ?>
                                    <img class="img-cirUs" src="<?php echo htmlspecialchars($coment['imgPathComentador']); ?>" alt="Avatar de <?php echo htmlspecialchars($coment['nombreUsuarioComentador']); ?>">
                                <?php else: ?>
                                    <img src="IMG/image_default.png" alt="Avatar por defecto" class="img-cirUs"> <?php endif; ?>
                                <strong><?php echo htmlspecialchars($coment['nombreUsuarioComentador']); ?></strong></div>
                                <span> (<?php echo htmlspecialchars($coment['fecha_formateada']); ?>):</span>
                            </div>
                            <p><?php echo nl2br(htmlspecialchars($coment['textoComentario'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
    </section>
        

        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 50px;">
            <h2>Fuente no encontrada</h2>
            <p>Lo sentimos, la fuente que estás buscando no existe o el enlace es incorrecto.</p>
            <a href="DaFont_index.php" class="button-primary" style="display: inline-block; padding: 10px 20px; background-color: #ff7f50; color: white; text-decoration: none; border-radius: 5px;">Volver al inicio</a>
        </div>
    <?php endif; ?>
</main>

<footer>
           <div class="dat-Page">
        <p>DaFont es un sitio web de descarga de fuentes tipográficas...</p>
        <p>© 2023 DaFont. Todos los derechos reservados.</p>
    <p> Las fuentes presentadas en este sitio web son propiedad de sus autores...</p>
    <p><a href="DaFont_FAQ.php">FAQ</a></p>
    <p><a href="DaFont_AuthorsList.php">Autores</a></p>
</div>
</footer>

<script src="JS/app.js"></script>
<script src="JS/script.js"></script>
<script src="JS/breadcrumbing.js"></script>
<script src="JS/Favs.js"></script>
<script src="JS/fontDetail.js"></script>
</body>
</html>