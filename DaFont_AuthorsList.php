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
    <link rel="stylesheet" href="CSS/authors.css"> <style>
        @import url('https://fonts.googleapis.com/css2?family=Audiowide&family=Bonheur+Royale&family=Creepster&family=Eater&family=Henny+Penny&family=Iansui&family=Meddon&family=UnifrakturMaguntia&display=swap');
        .authors-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            padding: 20px;
        }
        .author-card {
            background-color: rgba(30, 30, 30, 0.8); /* Similar a .font-card pero podría ser diferente */
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
            padding: 20px;
            width: calc(33.333% - 40px); /* Tres tarjetas por fila, ajusta según sea necesario */
            min-width: 280px; /* Ancho mínimo para tarjetas */
            box-sizing: border-box;
            text-align: center;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        body.light-mode .author-card {
            background-color: #f9f9f9;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .author-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(255, 0, 0, 0.3);
        }
        body.light-mode .author-card:hover {
            box-shadow: 0 8px 20px rgba(200, 0, 0, 0.2);
        }
        .author-card img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 15px;
            border: 3px solid #ff7f50; /* Coral */
            object-fit: cover;
        }
        .author-card h2 {
            margin-bottom: 8px;
            font-size: 1.5em;
        }
        .author-card h2 a {
            text-decoration: none;
            color: #ffaf7a; /* Un tono más claro de coral o naranja */
        }
        body.light-mode .author-card h2 a {
            color: #d9534f;
        }
        .author-card h2 a:hover {
            text-decoration: underline;
            color: #ff7f50;
        }
        .author-card p {
            font-size: 0.9em;
            color: #ccc;
            margin-bottom: 0;
        }
        body.light-mode .author-card p {
            color: #555;
        }
        .page-title {
            text-align: center;
            font-size: 2em;
            margin-bottom: 30px;
            color: #ff7f50;
        }
        body.light-mode .page-title {
            color: #d9534f;
        }
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
            $sql_categories_header = "SELECT nombreCategoria FROM Categorias ORDER BY nombreCategoria";
            $result_categories_header = mysqli_query($conn, $sql_categories_header);
            $categories_for_header = [];
            if ($result_categories_header && mysqli_num_rows($result_categories_header) > 0) {
                while ($cat_row_header = mysqli_fetch_assoc($result_categories_header)) {
                    $categories_for_header[] = $cat_row_header['nombreCategoria'];
                }
            }
            $subcategories_map_header = [
                'Fantasia' => ['Mágico', 'Épico', 'Oscuro'], 'Tecno' => ['Sci-Fi', 'Moderno', 'Futurista'],
                'Gotico' => ['Europeo', 'Medieval', 'Vampiro'], 'Basico' => ['Serif', 'Sans-Serif', 'Monospace'],
                'Script' => ['Caligrafía', 'Manuscrito', 'Firma'], 'Display' => ['Decorativa', 'Titular', 'Retro']
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
        <button id="btnSesion" 
            <?php if($visitor_user_id === 0): ?>
            onclick="window.location.href='Dafont_Log.php'">
            <i class="fa-solid fa-circle-user"></i></button>
            <?php else: ?>
            onclick="window.location.href='Dafont_Profile.php'">
            <?php echo htmlspecialchars($visitor_user_name); ?></button>
            <?php endif; ?>
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
                            <img src="<?php echo htmlspecialchars($author_item['autorImgPath']); ?>" alt="Avatar de <?php echo htmlspecialchars($author_item['nombreAutor']); ?>">
                        <?php else: ?>
                            <img src="IMG/DefaultProfile.png" alt="Avatar por defecto">
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