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
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/shepherd.js@VERSION/dist/css/shepherd.css"/>
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
<script type="module">
import Shepherd from "./node_modules/shepherd.js/dist/esm/shepherd.mjs";
const tour = new Shepherd.Tour({
  useModalOverlay: true,
  defaultStepOptions: {
    scrollTo: false,
    cancelIcon:{
        enabled:false,
    },
     canClickTarget: false,
  },
});


tour.addSteps([
  {
    id: "step-1-navbar",
    text: "¡Bienvenido! En la barra de navegación superior encontrarás las principales herramientas para explorar las fuentes.",
    classes:"shadow-md bg-red-dark",
    attachTo: {
      element: ".navbar",
      on: "bottom"
    },
    highlightClass: 'elemento-iluminado-custom',
    buttons: [
      {
        text: ">",
        action: tour.next,
      }
    ]
  },
  {
    id: "step-2-categories",
    text: "Aquí puedes navegar por las diferentes categorías de fuentes. Haz clic en una para ver las fuentes correspondientes.",
    classes:"shadow-md bg-red-dark",
    attachTo: {
      // Targets the first category link in the navigation
      element: ".nav-links .category-btn", 
      on: "bottom"
    },
    highlightClass: 'elemento-iluminado-custom',
    buttons: [
      {
        text:"<",
        action: tour.back,
      },
      {
        text: ">",
        action: tour.next,
      }
    ]
  },
  {
    id: "step-3-search",
    text: "Utiliza esta barra para buscar fuentes por nombre o autor. Escribe tu término y presiona el botón de la lupa.",
    classes:"shadow-md bg-red-dark",
    attachTo: {
      // Targets the container of the search bar and button
      element: ".search-container", 
      on: "bottom" 
    },
    highlightClass: 'elemento-iluminado-custom',
    buttons: [
      {
        text:"<",
        action: tour.back,
      },
      {
        text: ">",
        action: tour.next,
      }
    ]
  },
  {
    id: "step-4-btnFav",
    text: "Si has iniciado sesión, este botón te lleva directamente a tu colección de fuentes favoritas.",
    classes:"shadow-md bg-red-dark",
    attachTo: {
      element: "#btnFav", // Button for favorites page
      on: "bottom"
    },
    // Note: This step will only work if the #btnFav element exists (i.e., user is logged in).
    // You might need to add JavaScript logic to conditionally add this step
    // or ensure the tour is appropriate for the user's login state.
    when: { // Shepherd's built-in way to conditionally show a step
      show: () => document.querySelector('#btnFav') !== null
    },
    highlightClass: 'elemento-iluminado-custom',
    buttons: [
      {
        text:"<",
        action: tour.back,
      },
      {
        text: ">",
        action: tour.next,
      }
    ]
  },
  {
    id: "step-5-btnSesion",
    text: "Desde aquí puedes iniciar sesión, registrarte, o si ya has accedido, ir a tu perfil para editar tus datos.",
    classes:"shadow-md bg-red-dark",
    attachTo: {
      element: "#btnSesion", // Login/Profile button
      on: "bottom" // Or "left" if it visually fits better
    },
    highlightClass: 'elemento-iluminado-custom',
    buttons: [
      {
        text:"<",
        action: tour.back,
      },
      {
        text: ">",
        action: tour.next,
      }
    ]
  },
  {
    id: "step-6-btnFiltros",
    text: "Este botón abre el panel de filtros. Podrás cambiar el texto de previsualización, ajustar el tamaño de la fuente y activar el modo oscuro/claro.",
    classes:"shadow-md bg-red-dark",
    attachTo: {
      element: "#btn-filtros", // Filters button
      on: "left" 
    },
    highlightClass: 'elemento-iluminado-custom',
    buttons: [
      {
        text:"<",
        action: tour.back,
      },
      {
        text: ">",
        action: tour.next,
      }
    ]
  },
  {
    id: "step-7-fontCards",
    text: "Aquí se muestran las fuentes disponibles. Cada tarjeta ofrece una vista previa de la fuente, su nombre y el autor.",
    classes:"shadow-md bg-red-dark",
    attachTo: {
      // Targets the first font card. Assumes at least one card is present.
      element: ".font-card", 
      on: "top" 
    },
    when: { 
      show: () => document.querySelector('.font-card') !== null
    },
    highlightClass: 'elemento-iluminado-custom',
    buttons: [
      {
        text:"<",
        action: tour.back,
      },
      {
        text: ">",
        action: tour.next,
      }
    ]
  },
  {
    id: "step-8-fontCard-details",
    text: "Haz clic en cualquier parte de la tarjeta de una fuente para ver más detalles, como todos sus caracteres, información de licencia y más opciones.",
    classes:"shadow-md bg-red-dark",
    attachTo: {
      // Targets the presentation area of the first font card
      element: ".font-card .presentacion", 
      on: "top"
    },
    when: {
      show: () => document.querySelector('.font-card .presentacion') !== null
    },
    highlightClass: 'elemento-iluminado-custom',
    buttons: [
      {
        text:"<",
        action: tour.back,
      },
      {
        text: ">",
        action: tour.next,
      }
    ]
  },
  {
    id: "step-9-fontCard-favBtn",
    text: "Con el botón del corazón puedes añadir o quitar una fuente de tu lista personal de 'Favoritas' (requiere iniciar sesión).",
    classes:"shadow-md bg-red-dark",
    attachTo: {
      // Targets the favorite button of the first font card
      element: ".font-card .btn-favorite", 
      on: "left" 
    },
    when: {
      show: () => document.querySelector('.font-card .btn-favorite') !== null
    },
    highlightClass: 'elemento-iluminado-custom',
    buttons: [
      {
        text:"<",
        action: tour.back,
      },
      {
        text: ">",
        action: tour.next,
      }
    ]
  },
  {
    id: "step-10-fontCard-dlBtn",
    text: "Usa este botón para descargar directamente el archivo de la fuente a tu dispositivo.",
    classes:"shadow-md bg-red-dark",
    attachTo: {
      // Targets the download button of the first font card
      element: ".font-card .download-btn", 
      on: "left"
    },
    when: {
      show: () => document.querySelector('.font-card .download-btn') !== null
    },
    highlightClass: 'elemento-iluminado-custom',
    buttons: [
      {
        text:"<",
        action: tour.back,
      },
      {
        text: ">", // Next step or complete
        action: tour.next,
      }
    ]
  },
  {
    id: "step-11-end",
    text: "¡Has completado el tour! Ya estás listo para descubrir y descargar miles de fuentes. ¡Disfruta tu experiencia!",
    classes:"shadow-md bg-red-dark",
    highlightClass: 'elemento-iluminado-custom',
    buttons: [
      {
        text:"Finalizar",
        action: tour.complete,
      }
    ]
  }
]);

// ¡No olvides iniciar el tour!
 tour.start();

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