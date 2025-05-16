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
        (SELECT COUNT(*) FROM FavFonts ff WHERE ff.idFont = f.idFont AND ff.idUsuario = ?) AS currentUserHasFavorited
    FROM Fonts f
    LEFT JOIN Usuario u ON f.fontAutor = u.idUsuario
    WHERE f.idFont = ?";

    $stmt = mysqli_prepare($conn, $sql_font_detail);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ii", $user_id_session, $font_id_param);
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

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($font && isset($font['fontName'])) ? htmlspecialchars($font['fontName']) : 'Detalles de Fuente'; ?> - DaFont</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/styleCards.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Audiowide&family=Bonheur+Royale&family=Creepster&family=Eater&family=Henny+Penny&family=Iansui&family=Meddon&family=UnifrakturMaguntia&display=swap');
        <?php if ($font && isset($font['fontFamilyCSS'])): /* Solo si $font y fontFamilyCSS existen */ ?>
        @font-face {
            font-family: '<?php echo htmlspecialchars($font['fontFamilyCSS']); ?>';
            /* Si necesitas cargar el archivo de fuente: */
            /* src: url('ruta/a/fuente/<?php echo htmlspecialchars($font['fontFamilyCSS']); ?>.ttf'); */
        }
        <?php endif; ?>
        .font-detail-container { margin: 20px auto; padding: 20px; background: rgba(20,20,20,0.9); border-radius: 8px; max-width: 900px; }
        body.light-mode .font-detail-container { background: rgba(240,240,240,0.9); color: #333; }
        .font-preview-detailed {
            font-size: 48px;
            padding: 20px;
            border: 2px dashed #555;
            margin-bottom: 20px;
            min-height: 150px;
            width: 100%;
            box-sizing: border-box;
            color: #fff;
            background-color: #2c2c2c;
            line-height: 1.5;
            overflow-wrap: break-word;
            text-align: center;
        }
        body.light-mode .font-preview-detailed { color: #000; background-color: #f0f0f0; border-color: #ccc; }
        .details-section { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #444; }
        body.light-mode .details-section { border-bottom-color: #ddd; }
        .details-section:last-child { border-bottom: none; }
        .details-section h3 { margin-bottom: 8px; color: #ff7f50; font-size: 1.4em; }
        body.light-mode .details-section h3 { color: #d9534f; }
        .details-section p, .details-section span, .details-section a { font-size: 1.1em; line-height: 1.6; }
        .author-info img { width: 60px; height: 60px; border-radius: 50%; vertical-align: middle; margin-right: 12px; border: 2px solid #ff7f50;}
        .controls { margin-bottom: 25px; display: flex; gap: 20px; align-items: center; flex-wrap: wrap; padding: 15px; background-color: rgba(0,0,0,0.2); border-radius: 6px;}
        body.light-mode .controls { background-color: rgba(255,255,255,0.3); }
        .controls label { margin-right: 8px; font-weight: bold;}
        .controls input[type="text"] { padding: 10px; border-radius: 4px; border: 1px solid #555; background-color: #333; color: #fff; flex-grow: 1; min-width: 250px;}
        body.light-mode .controls input[type="text"] { background-color: #fff; color: #333; border-color: #ccc; }
        .controls input[type="range"] { cursor: pointer; width: 200px;}
        #font-size-value-detail { margin-left: 10px; font-weight: bold; min-width: 50px; text-align: right;}
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
            <h1><?php echo htmlspecialchars($font['fontName']); ?></h1>

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
                <h3>Calificación</h3>
                <div class="stars-display" data-font-id="<?php echo $font['idFont']; ?>">
                    <?php
                    $promedio_detail = round($font['promedioEstrellas'] ?? 0);
                    $totalVotos_detail = (int) ($font['totalCalificaciones'] ?? 0);
                    for ($i = 1; $i <= 5; $i++): ?>
                        <span class="star <?php echo ($i <= $promedio_detail) ? 'filled' : ''; ?>" data-value="<?php echo $i; ?>">&#9733;</span>
                    <?php endfor; ?>
                    <span class="rating-average">(<?php echo number_format($font['promedioEstrellas'] ?? 0, 1); ?> de <?php echo $totalVotos_detail; ?> votos)</span>
                </div>
            </div>
            
            <div class="actions">
                <?php
                $isFavorite_detail = ($font['currentUserHasFavorited'] > 0);
                $favIconClass_detail = $isFavorite_detail ? "fa-solid fa-heart" : "fa-regular fa-heart";
                ?>
                <button class="btn btn-favorite" title="<?php echo $isFavorite_detail ? 'Quitar de favoritos' : 'Añadir a favoritos'; ?>" data-fontid="<?php echo $font['idFont']; ?>" data-isfavorite="<?php echo $isFavorite_detail ? 'true' : 'false'; ?>"><i class="<?php echo $favIconClass_detail;?>"></i></button>
                <button class="download-btn" title="Descargar fuente (ejemplo TXT)" data-font-id="<?php echo $font['idFont']; ?>"><i class="fa-solid fa-download"></i></button>
            </div>

            <div class="details-section comments-section">
                <h3>Comentarios</h3>
                <p>La sección de comentarios aún no está implementada.</p>
            </div>

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
    <p> Las fuentes presentadas en este sitio web son propiedad de sus autores, y son freeware, shareware, demos o dominio público...</p>
    <p><a href="#">FAQ</a></p>
</footer>

<script src="JS/app.js"></script>
<script src="JS/script.js"></script>
<script src="JS/breadcrumbing.js"></script>
<script src="JS/Favs.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const textInputDetail = document.getElementById("text-input-detail");
    const fontSizeRangeDetail = document.getElementById("font-size-range-detail");
    const fontSizeValueDetail = document.getElementById("font-size-value-detail");
    const fontPreviewDetailed = document.getElementById("font-preview-detailed");

    function updateFontPreviewDetailed() {
        if (!fontPreviewDetailed || !textInputDetail || !fontSizeRangeDetail) return;
        const newText = textInputDetail.value;
        const newSize = fontSizeRangeDetail.value + "px";

        fontPreviewDetailed.textContent = newText || "<?php echo ($font && isset($font['fontName'])) ? htmlspecialchars(addslashes($font['fontName'])) : 'Vista Previa'; ?>";
        fontPreviewDetailed.style.fontSize = newSize;
        if(fontSizeValueDetail) fontSizeValueDetail.textContent = newSize;
    }

    if (textInputDetail) textInputDetail.addEventListener("input", updateFontPreviewDetailed);
    if (fontSizeRangeDetail) fontSizeRangeDetail.addEventListener("input", updateFontPreviewDetailed);

    if (fontPreviewDetailed) updateFontPreviewDetailed();

    const downloadBtnDetail = document.querySelector('.font-detail-container .download-btn');
    if (downloadBtnDetail) {
        downloadBtnDetail.addEventListener('click', function() {
            const fontId = this.dataset.fontId;
            const fontName = "<?php echo ($font && isset($font['fontName'])) ? htmlspecialchars(addslashes($font['fontName'])) : 'font'; ?>";
            
            console.log("Download clicked for font ID:", fontId, "Name:", fontName);
            const content = `Este es un archivo de ejemplo para la fuente: ${fontName}\nID: ${fontId}\n\nLorem ipsum dolor sit amet...`;
            const fileName = `${fontName.replace(/[^a-z0-9]/gi, '_')}_example.txt`;
            const blob = new Blob([content], { type: "text/plain" });
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = fileName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(link.href);
        });
    }
});
</script>
</body>
</html>