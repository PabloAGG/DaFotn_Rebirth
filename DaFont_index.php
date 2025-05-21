<?php
session_start();
require 'BACK/DB_connection.php';

$user_id = null;
$user_name = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'];
}

$user_id_actual_seguro = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

// --- INICIO DE LÓGICA PARA OBTENER PARÁMETROS DE URL ---
// Estas variables deben estar definidas ANTES del bucle de fuentes
$category_name = isset($_GET['category']) ? trim($_GET['category']) : null;
$subcategory_name = isset($_GET['subcategory']) ? trim($_GET['subcategory']) : null;
$search_term = isset($_GET['search_term']) ? trim($_GET['search_term']) : null;
// --- FIN DE LÓGICA PARA OBTENER PARÁMETROS DE URL ---

$sql_fonts_select = "SELECT
    f.idFont, f.fontName, f.fontFamilyCSS, f.fontStyleFallback, f.descargas, f.licenciaDescripcion,
    u.usuario AS nombreAutor, u.idUsuario AS idAutor,
    (SELECT AVG(c.estrellas) FROM calificaciones c WHERE c.idFont = f.idFont) AS promedioEstrellas,
    (SELECT COUNT(c.idCalf) FROM calificaciones c WHERE c.idFont = f.idFont) AS totalCalificaciones,
    (SELECT COUNT(*) FROM FavFonts ff WHERE ff.idFont = f.idFont AND ff.idUsuario = ?) AS currentUserHasFavorited
FROM Fonts f
LEFT JOIN Usuario u ON f.fontAutor = u.idUsuario";

$sql_fonts_where = [];
$sql_params_types = "i";
$sql_params_values = [$user_id_actual_seguro];
$sql_fonts_order = "ORDER BY f.fechaSubida DESC";
$sql_fonts_joins = "";

if ($category_name) {
    $sql_fonts_joins .= " INNER JOIN FontCategorias fc ON f.idFont = fc.idFont INNER JOIN Categorias cat ON fc.idCategoria = cat.idCategoria";
    $sql_fonts_where[] = "cat.nombreCategoria = ?";
    $sql_params_types .= "s";
    $sql_params_values[] = $category_name;
    // Si tienes subcategorías como entidades separadas en la BD, aquí iría el filtro
    // if ($subcategory_name) {
    //     $sql_fonts_where[] = "f.subcategoria = ?"; // O la columna que sea
    //     $sql_params_types .= "s";
    //     $sql_params_values[] = $subcategory_name;
    // }
}

if ($search_term) {
    $search_like = "%" . $search_term . "%";
    $sql_fonts_where[] = "(f.fontName LIKE ? OR u.usuario LIKE ?)";
    $sql_params_types .= "ss";
    $sql_params_values[] = $search_like;
    $sql_params_values[] = $search_like;
}

$sql_final = $sql_fonts_select . " " . $sql_fonts_joins;
if (!empty($sql_fonts_where)) {
    $sql_final .= " WHERE " . implode(" AND ", $sql_fonts_where);
}
$sql_final .= " " . $sql_fonts_order;

$stmt = mysqli_prepare($conn, $sql_final);
$fonts = [];
if ($stmt) {
    mysqli_stmt_bind_param($stmt, $sql_params_types, ...$sql_params_values);
    mysqli_stmt_execute($stmt);
    $result_fonts = mysqli_stmt_get_result($stmt);
    if ($result_fonts && mysqli_num_rows($result_fonts) > 0) {
        while ($row = mysqli_fetch_assoc($result_fonts)) {
            $fonts[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
} else {
    error_log("Error preparing statement: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DaFont</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/styleCards.css">
    <link rel="stylesheet" href="CSS/fontD.css">
<<<<<<< Updated upstream
=======
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/shepherd.js@VERSION/dist/css/shepherd.css"/> 
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/introjs.min.css"  crossorigin="anonymous" referrerpolicy="no-referrer" />
>>>>>>> Stashed changes
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
            $sql_categories = "SELECT nombreCategoria FROM Categorias ORDER BY nombreCategoria";
            $result_categories_nav = mysqli_query($conn, $sql_categories); // Usar un nombre de variable diferente para el resultado de esta consulta
            $categories_for_header = [];
            if ($result_categories_nav && mysqli_num_rows($result_categories_nav) > 0) {
                while ($cat_row_nav = mysqli_fetch_assoc($result_categories_nav)) { // Usar un nombre de variable diferente para la fila
                    $categories_for_header[] = $cat_row_nav['nombreCategoria'];
                }
            }

            foreach ($categories_for_header as $category_item_name_nav) { // Usar un nombre de variable diferente
                echo '<li class="dropdown">';
                echo '<a href="DaFont_index.php?category=' . urlencode($category_item_name_nav) . '" class="category-btn">' . htmlspecialchars($category_item_name_nav) . '</a>';
                if (isset($subcategories_map[$category_item_name_nav])) {
                    echo '<ul class="submenu">';
                    foreach ($subcategories_map[$category_item_name_nav] as $subcategory_item_name_nav) { // Usar un nombre de variable diferente
                        echo '<li><a href="DaFont_index.php?category=' . urlencode($category_item_name_nav) . '&subcategory=' . urlencode($subcategory_item_name_nav) . '">' . htmlspecialchars($subcategory_item_name_nav) . '</a></li>';
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
                        <button type="submit" title="Buscar" class="search-button"><i class="fa fa-search"></i></button>
                    </div>
                </form>
            </li>
        </ul>
         <?php if(isset($_SESSION['user_id'])){?>
        <button id="btnFav" onclick="window.location.href='Dafont_Profile.php'"><i class="fa-solid fa-heart"></i> Favoritas</button>
        <?php } ?>
        <button id="btnSesion" 
            <?php if(!isset($_SESSION['user_id'])){ ?>
            title="Iniciar Sesion" onclick="window.location.href='Dafont_Log.php'">
            <i class="fa-solid fa-circle-user"></i></button>
            <?php }else{ ?>
            title="Mi Perfil" onclick="window.location.href='Dafont_Editar.php'">
            <?php echo htmlspecialchars($user_name); ?></button>
            <?php } ?>
        <div class="menu-hamburguesa">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>
</header>


<main>
    <nav id="breadcrumb">
        <span><a href="DaFont_index.php">Inicio</a></span>
        <?php
        // El breadcrumb aquí usa las variables $category_name, $subcategory_name, $search_term
        // definidas al principio del script a partir de $_GET
        if ($category_name) {
            echo '<span><a href="DaFont_index.php?category=' . urlencode($category_name) . '">' . htmlspecialchars($category_name) . '</a></span>';
        }
        if ($subcategory_name) { // Corregido: debe ser $subcategory_name, no $category_name dos veces
            echo '<span><a href="DaFont_index.php?category=' . urlencode($category_name) . '&subcategory=' . urlencode($subcategory_name) . '">' . htmlspecialchars($subcategory_name) . '</a></span>';
        }
        if ($search_term) {
             echo '<span>Búsqueda: "' . htmlspecialchars($search_term) . '"</span>';
        }
        ?>
    </nav>
    <button class="btn-filtros" id="btn-filtros" title="Abrir menú de filtros" aria-label="Abrir menú de filtros">
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
            <label for="dkmode">Modo Claro/Oscuro</label> <button id="dkmode" title="Cambio de modo" aria-label="Cambiar modo claro u oscuro"><i class="fa-solid fa-circle-half-stroke"></i></button><br>
            <?php if(isset($_SESSION['user_id'])){?>
            <button onclick="window.location.href='BACK/LogOut.php'" aria-label="Cerrar sesión"><i class="fa-solid fa-right-from-bracket"></i></button>
      <?php } ?>
        </div>
        <br>
    </aside>

    <div class="FontContainer">
        <?php if (!empty($fonts)): ?>
            <?php foreach ($fonts as $font): ?>
                <?php
           
          
                $details_link = "Dafont_FontDetails.php?id=" . $font['idFont'];

                // Usamos las variables $category_name y $subcategory_name que se obtuvieron de $_GET
                // al principio de ESTE MISMO SCRIPT (DaFont_index.php)
                if (isset($category_name) && $category_name) {
                    $details_link .= "&category=" . urlencode($category_name);
                }
                if (isset($subcategory_name) && $subcategory_name) {
                    $details_link .= "&subcategory=" . urlencode($subcategory_name);
                }
             
                ?>
                <div class="font-card" onclick="window.location.href='<?php echo htmlspecialchars($details_link); ?>'">
                    <div class="presentacion">
                        <h2 class="font-name" onclick="event.stopPropagation(); window.location.href='<?php echo htmlspecialchars($details_link); ?>'"><?php echo htmlspecialchars($font['fontName']); ?></h2>
                        <span class="author">
                            Autor:
                            <?php if ($font['idAutor']): ?>
                                <a id="autorPerfil" href="DaFont-AuthorProfile.php?id=<?php echo $font['idAutor']; ?>" onclick="event.stopPropagation();"><?php echo htmlspecialchars($font['nombreAutor'] ?? 'Desconocido'); ?></a>
                            <?php else: ?>
                                <?php echo htmlspecialchars($font['nombreAutor'] ?? 'N/A'); ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    <br>
                    <div class="font-preview" style="font-family: '<?php echo htmlspecialchars($font['fontFamilyCSS']); ?>', <?php echo htmlspecialchars($font['fontStyleFallback']); ?>;"><?php echo htmlspecialchars($font['fontName']); ?></div>
                    <div class="font-details">
                        <span class="downloads"><?php echo number_format($font['descargas']); ?> descargas</span> <span class="license"><?php echo htmlspecialchars($font['licenciaDescripcion']); ?></span>
                        <div class="stars-display" data-font-id="<?php echo $font['idFont']; ?>">
                            <?php
                            $promedio = round($font['promedioEstrellas'] ?? 0);
                            $totalVotos = (int) ($font['totalCalificaciones'] ?? 0);
                            for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?php echo ($i <= $promedio) ? 'filled' : ''; ?>" data-value="<?php echo $i; ?>">&#9733;</span>
                            <?php endfor; ?>
        
                        </div>
                    </div>
                    <?php
                    $isFavorite = ($font['currentUserHasFavorited'] > 0);
                    $favIconClass = $isFavorite ? "fa-solid fa-heart" : "fa-regular fa-heart";
                    ?>
                    <button class="btn btn-favorite" title="<?php echo $isFavorite ? 'Quitar de favoritos' : 'Añadir a favoritos'; ?>" data-fontid="<?php echo $font['idFont']; ?>" data-isfavorite="<?php echo $isFavorite ? 'true' : 'false'; ?>" onclick="event.stopPropagation(); handleFavoriteClick(this, '<?php echo $font['idFont']; ?>');"><i class="<?php echo $favIconClass;?>"></i></button>
                    <button class="download-btn" id="download-btn-<?php echo $font['idFont']; ?>" title="Descargar fuente (ejemplo TXT)" data-font-id="<?php echo $font['idFont']; ?>" onclick="event.stopPropagation(); downloadFontFile('<?php echo $font['idFont']; ?>', '<?php echo htmlspecialchars(addslashes($font['fontName'])); ?>');"><i class="fa-solid fa-download"></i></button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay fuentes disponibles para mostrar que coincidan con tu búsqueda o filtro.</p>
        <?php endif; ?>
    </div>

    <div class="info-area" id="info-area">

    </div>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/intro.min.js"  crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script> 
document.addEventListener('DOMContentLoaded', function() {

const introSteps = [
        {
            element: document.querySelector('.navbar'),
            intro: "¡Bienvenido! Esta es la barra de navegación principal donde encontrarás todas las opciones importantes.",
            position: 'bottom'
        },
        {
            element: document.querySelector('.nav-links .category-btn'), // Targets the first category button
            intro: "Navega por las diferentes categorías de fuentes haciendo clic en sus nombres.",
            position: 'bottom'
        },
        {
            element: document.querySelector('.search-container'),
            intro: "Usa esta barra para buscar fuentes por nombre o autor. Escribe y presiona el botón de la lupa.",
            position: 'bottom'
        },
        {
            element: document.querySelector('#btnFav'),
            intro: "Si has iniciado sesión, este botón te lleva directamente a tu colección de fuentes favoritas.",
            position: 'bottom'
        },
        {
            element: document.querySelector('#btnSesion'),
            intro: "Desde aquí puedes iniciar sesión, registrarte, o si ya has accedido, ir a tu perfil.",
            position: 'bottom' // Or 'left' depending on layout
        },
        {
            element: document.querySelector('#btn-filtros'),
            intro: "Este botón abre el panel de filtros. Podrás cambiar el texto de previsualización, el tamaño de la fuente y más.",
            position: 'right'
        },
        {
            element: document.querySelector('.font-card'), // Targets the first font card
            intro: "Aquí se muestran las fuentes. Cada tarjeta ofrece una vista previa, nombre y autor.",
            position: 'top'
        },
        {
            element: document.querySelector('.font-card .presentacion'), // Targets presentation area of the first font card
            intro: "Haz clic en cualquier parte de la tarjeta de una fuente para ver más detalles como todos sus caracteres y opciones de descarga.",
            position: 'top'
        },
        {
            element: document.querySelector('.font-card .btn-favorite'), // Targets favorite button of the first font card
            intro: "Con el botón del corazón puedes añadir o quitar esta fuente de tu lista personal de 'Favoritas' (requiere iniciar sesión).",
            position: 'left'
        },
        {
            element: document.querySelector('.font-card .download-btn'), // Targets download button of the first font card
            intro: "Usa este botón para descargar directamente el archivo de la fuente a tu dispositivo.",
            position: 'left'
        },
        {
            intro: "¡Has completado el tour! Ya estás listo para descubrir y descargar miles de fuentes. ¡Disfruta tu experiencia!",
            // No element needed for a general final message
        }
    ];

    // Filter out steps where the element might not exist on the page
    const activeIntroSteps = introSteps.filter(step => {
        if (step.element) {
            return step.element !== null; // Check if querySelector found the element
        }
        return true; // Keep steps that don't have an 'element' (like general intro/outro messages)
    });

    if (activeIntroSteps.length > 0) {
        introJs().setOptions({
            steps: activeIntroSteps,
            nextLabel: 'Siguiente &rarr;',
            prevLabel: '&larr; Anterior',
            doneLabel: 'Finalizar',
            tooltipClass: 'introjs-custom-tooltip', // Optional: for custom styling
            showBullets: false, // Optional: hide step bullets
            // exitOnOverlayClick: false, // Optional: prevent closing by clicking overlay
        }).start();
    }

    // --- END INTRO.JS TOUR ---
});
</script>

<script src="JS/app.js"></script>
<script src="JS/script.js"></script>
<script src="JS/scriptCards.js"></script>
<script src="JS/scriptIndex.js"></script>
<script src="JS/breadcrumbing.js"></script>
<script src="JS/Favs.js"></script>
<script src="JS/fontDetail.js"></script>
</body>
</html>