<?php
session_start();
require 'BACK/DB_connection.php';

// Variables para el usuario que está visitando la página (desde la sesión)
$visitor_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$visitor_user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;

// Obtener el ID del autor desde el parámetro GET de la URL
$author_id_from_url = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Variables para los datos del autor cuyo perfil se está viendo
$autor_id = null;
$autor_name = null;
$autor_img = null;
$autor_page = null;
$fonts = []; // Inicializar array de fuentes del autor

if ($author_id_from_url > 0) {
    // 1. Obtener información del autor
    $sql_author_details = "SELECT idUsuario, usuario, imgPath, pagina FROM Usuario WHERE idUsuario = ?";
    $stmt_author = mysqli_prepare($conn, $sql_author_details);

    if ($stmt_author) {
        mysqli_stmt_bind_param($stmt_author, "i", $author_id_from_url);
        mysqli_stmt_execute($stmt_author);
        $result_author = mysqli_stmt_get_result($stmt_author);
        $row_author = mysqli_fetch_assoc($result_author);

        if ($row_author) {
            $autor_id = $row_author["idUsuario"];
            $autor_name = $row_author["usuario"];
            $autor_img = $row_author["imgPath"];
            $autor_page = $row_author["pagina"];

            // 2. Obtener las fuentes de este autor
            // La variable $visitor_user_id se usa para determinar si el visitante actual ha marcado como favorita cada fuente.
            $sql_fonts_by_author = "SELECT
                f.idFont,
                f.fontName,
                f.fontFamilyCSS,
                f.fontStyleFallback,
                f.descargas,
                f.licenciaDescripcion,
                u.usuario AS nombreAutorOriginal, # Aunque aquí siempre será $autor_name
                u.idUsuario AS idAutorOriginal,   # Aunque aquí siempre será $autor_id
                (SELECT AVG(c.estrellas) FROM calificaciones c WHERE c.idFont = f.idFont) AS promedioEstrellas,
                (SELECT COUNT(c.idCalf) FROM calificaciones c WHERE c.idFont = f.idFont) AS totalCalificaciones,
                (SELECT COUNT(*) FROM FavFonts ff WHERE ff.idFont = f.idFont AND ff.idUsuario = ?) AS currentUserHasFavorited
            FROM Fonts f
            LEFT JOIN Usuario u ON f.fontAutor = u.idUsuario
            WHERE f.fontAutor = ?
            ORDER BY f.fechaSubida DESC";

            $stmt_fonts = mysqli_prepare($conn, $sql_fonts_by_author);
            if ($stmt_fonts) {
                mysqli_stmt_bind_param($stmt_fonts, "ii", $visitor_user_id, $autor_id);
                mysqli_stmt_execute($stmt_fonts);
                $result_fonts = mysqli_stmt_get_result($stmt_fonts);

                if ($result_fonts && mysqli_num_rows($result_fonts) > 0) {
                    while ($row_font = mysqli_fetch_assoc($result_fonts)) {
                        $fonts[] = $row_font;
                    }
                }
                mysqli_stmt_close($stmt_fonts);
            } else {
                error_log("Error al preparar la consulta de fuentes del autor: " . mysqli_error($conn));
            }
        } else {
            // No se encontró el autor con ese ID
            // Podrías redirigir a una página de error o mostrar un mensaje
            $autor_name = "Autor no encontrado";
        }
        mysqli_stmt_close($stmt_author);
    } else {
        error_log("Error al preparar la consulta de detalles del autor: " . mysqli_error($conn));
        $autor_name = "Error al cargar perfil";
    }
} else {
    // No se proporcionó un ID de autor válido en la URL
    $autor_name = "Perfil de autor no especificado";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?php echo $autor_name ? htmlspecialchars($autor_name) : 'Autor'; ?> - DaFont</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/styleCards.css">
    <link rel="stylesheet" href="CSS/authors.css"> 
    <script src="https://kit.fontawesome.com/093074d40c.js" crossorigin="anonymous"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Audiowide&family=Bonheur+Royale&family=Creepster&family=Eater&family=Henny+Penny&family=Iansui&family=Meddon&family=UnifrakturMaguntia&display=swap');
    </style>
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
        <button id="btnFav" onclick="window.location.href='Dafont_Profile.php'"><i class="fa-solid fa-heart"></i>Favoritas</button>
        <?php } ?>
        <button id="btnSesion" 
            <?php if(!isset($_SESSION['user_id'])){ ?>
            title="Iniciar Sesion" onclick="window.location.href='Dafont_Log.php'">
            <i class="fa-solid fa-circle-user"></i></button>
            <?php }else{ ?>
            title="Mi Perfil" onclick="window.location.href='Dafont_Editar.php'">
            <?php echo htmlspecialchars($visitor_user_name); ?></button>
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
        <span><a href="DaFont_AuthorsList.php">Autores</a></span> <?php
        if ($autor_name && $autor_name !== "Autor no encontrado" && $autor_name !== "Perfil de autor no especificado" && $autor_name !== "Error al cargar perfil") {
            echo '<span>' . htmlspecialchars($autor_name) . '</span>';
        } elseif($autor_id_from_url > 0) {
            echo '<span>Perfil de Autor</span>'; // Fallback genérico si el nombre no se pudo cargar pero se intentó
        }
        ?>
    </nav>

    <button class="btn-filtros" id="btn-filtros">
        <i class="fa fa-sliders"></i> 
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

    <?php if ($autor_id && $autor_name !== "Autor no encontrado" && $autor_name !== "Error al cargar perfil"): ?>
        <div class="author-id">
        <div class="presentacion">
            <?php if($autor_img): ?>
                <img src="<?php echo htmlspecialchars($autor_img); ?>" alt="Imagen de perfil de <?php echo htmlspecialchars($autor_name); ?>">
            <?php else: ?>
                <img src="IMG/DefaultProfile.png" alt="Imagen de perfil por defecto">
            <?php endif; ?>
            
            <div> <h2><?php echo htmlspecialchars($autor_name); ?></h2>
                <?php if($autor_page): ?>
                    <p class="author-page-link-inline"><a href="<?php echo htmlspecialchars($autor_page); ?>" target="_blank" rel="noopener noreferrer">Página oficial</a></p>
                <?php else: ?>
                    <p class="author-page-link-inline-empty"><em>Este autor no ha especificado una página oficial.</em></p>
                <?php endif; ?>
            </div>

        </div>
        <?php 
        // El bloque PHP para la página oficial que estaba aquí abajo se ha movido DENTRO del div anterior
        // Así que ya no es necesario aquí.
        ?>
    </div>

        <div class="FontContainer">
            <h1>Fuentes de <?php echo htmlspecialchars($autor_name); ?></h1><br>
            <?php if (!empty($fonts)): ?>
                <?php foreach ($fonts as $font_item): // Usar $font_item para evitar colisión si $font se usa globalmente ?>
                    <?php
                    // Construir enlace a detalles de la fuente
                    // Para el perfil del autor, no necesitamos pasar category/subcategory en el enlace a font_details
                    // a menos que esta página de perfil de autor también se pueda filtrar por categorías.
                    // Por simplicidad, aquí solo pasamos el id de la fuente.
                    $details_link = "Dafont_FontDetails.php?id=" . $font_item['idFont'];
                    if(isset($_GET['category'])) $details_link .= "&category=" . urlencode($_GET['category']); // Mantener el contexto si vienes de una categoría
                    if(isset($_GET['subcategory'])) $details_link .= "&subcategory=" . urlencode($_GET['subcategory']);

                    ?>
                    <div class="font-card" onclick="window.location.href='<?php echo htmlspecialchars($details_link); ?>'">
                        <div class="presentacion">
                            <h2 class="font-name" onclick="event.stopPropagation(); window.location.href='<?php echo htmlspecialchars($details_link); ?>'"><?php echo htmlspecialchars($font_item['fontName']); ?></h2>
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
                        <button class="btn btn-favorite" title="<?php echo $isFavorite ? 'Quitar de favoritos' : 'Añadir a favoritos'; ?>" data-fontid="<?php echo $font_item['idFont']; ?>" data-isfavorite="<?php echo $isFavorite ? 'true' : 'false'; ?>" onclick="event.stopPropagation(); handleFavoriteClick(this, '<?php echo $font_item['idFont']; ?>');"><i class="<?php echo $favIconClass;?>"></i></button>
                        <button class="download-btn" title="Descargar fuente (ejemplo TXT)" data-fontid="<?php echo $font_item['idFont']; ?>" onclick="event.stopPropagation(); downloadFontFile('<?php echo $font_item['idFont']; ?>', '<?php echo htmlspecialchars(addslashes($font_item['fontName'])); ?>');"><i class="fa-solid fa-download"></i></button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p><?php echo htmlspecialchars($autor_name); ?> aún no ha publicado fuentes.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 50px;">
            <h2><?php echo htmlspecialchars($autor_name); ?></h2>
            <p>No se pudo cargar la información de este autor o el perfil no existe.</p>
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
<script src="JS/scriptCards.js"></script>
<script src="JS/breadcrumbing.js"></script> <script src="JS/Favs.js"></script>
<script>
    // Estas funciones podrían estar en un archivo JS común si se usan en múltiples páginas
    async function handleFavoriteClick(buttonElement, fontId) {
        console.log("Favorite button clicked for font ID:", fontId);
        // Aquí iría la lógica de Favs.js
    }

    function downloadFontFile(fontId, fontName) {
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
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Lógica específica para la página de perfil de autor, si es necesaria
        // Por ejemplo, la funcionalidad del aside de filtros
        const botonAbrirFiltros = document.getElementById("btn-filtros");
        const botonCerrarFiltros = document.querySelector(".Filtros .hideMenu"); // Selector más específico
        const asideFiltros = document.querySelector("aside.Filtros");

        if(botonAbrirFiltros && asideFiltros) {
            botonAbrirFiltros.addEventListener("click", () => {
                asideFiltros.classList.add("active");
            });
        }
        if(botonCerrarFiltros && asideFiltros) {
            botonCerrarFiltros.addEventListener("click", () => {
                asideFiltros.classList.remove("active");
            });
        }
        // Cerrar al hacer clic fuera (opcional)
        document.addEventListener("click", function (e) {
            if (
                asideFiltros &&
                asideFiltros.classList.contains("active") &&
                !asideFiltros.contains(e.target) &&
                botonAbrirFiltros && !botonAbrirFiltros.contains(e.target)
            ) {
                asideFiltros.classList.remove("active");
            }
        });
    });
</script>
</body>
</html>