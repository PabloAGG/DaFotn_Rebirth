<?php
session_start();
require 'BACK/DB_connection.php'; // Conexión a la BD

// Variables para el usuario que está visitando la página (desde la sesión)
$visitor_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$visitor_user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;

// Consulta SQL para obtener los autores y el número de fuentes que han subido
// Seleccionamos solo usuarios que aparecen como fontAutor en la tabla Fonts
// y contamos cuántas fuentes tienen.
$sql_authors = "SELECT
                    u.idUsuario,
                    u.usuario AS nombreAutor,
                    u.imgPath AS autorImgPath,
                    COUNT(f.idFont) AS numeroDeFuentes
                FROM Usuario u
                JOIN Fonts f ON u.idUsuario = f.fontAutor
                GROUP BY u.idUsuario, u.usuario, u.imgPath
                HAVING COUNT(f.idFont) > 0
                ORDER BY u.usuario ASC"; // Ordenar alfabéticamente por nombre de autor

$stmt_authors = mysqli_prepare($conn, $sql_authors);
$authors_list = [];

if ($stmt_authors) {
    mysqli_stmt_execute($stmt_authors);
    $result_authors = mysqli_stmt_get_result($stmt_authors);
    if ($result_authors && mysqli_num_rows($result_authors) > 0) {
        while ($row_author = mysqli_fetch_assoc($result_authors)) {
            $authors_list[] = $row_author;
        }
    }
    mysqli_stmt_close($stmt_authors);
} else {
    error_log("Error al preparar la consulta de lista de autores: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Autores - DaFont</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/authors.css"> 
  <link rel="stylesheet" href="CSS/authorlist.css"> 
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
        <span>Autores</span>
    </nav>

    <h1 class="page-title">Nuestros Autores</h1>

    <div class="authors-container">
        <?php if (!empty($authors_list)): ?>
            <?php foreach ($authors_list as $author_item): ?>
                <div class="author-card">
                    <a href="DaFont-AuthorProfile.php?id=<?php echo $author_item['idUsuario']; ?>">
                        <?php if (!empty($author_item['autorImgPath'])): ?>
                            <img src="<?php echo htmlspecialchars($author_item['autorImgPath']); ?>" alt="Avatar de <?php echo htmlspecialchars($author_item['nombreAutor']); ?>" loading="lazy">
                        <?php else: ?>
                            <img loading="lazy" src="IMG/DefaultProfile.png" alt="Avatar por defecto">
                        <?php endif; ?>
                    </a>
                    <h2><a href="DaFont-AuthorProfile.php?id=<?php echo $author_item['idUsuario']; ?>"><?php echo htmlspecialchars($author_item['nombreAutor']); ?></a></h2>
                    <p><?php echo $author_item['numeroDeFuentes']; ?> fuente(s)</p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; width: 100%;">No hay autores para mostrar en este momento.</p>
        <?php endif; ?>
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

<script src="JS/app.js"></script>
<script src="JS/script.js"></script>
<script src="JS/breadcrumbing.js"></script> </body>
</html>