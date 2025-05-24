<?php
session_start();
require 'BACK/DB_connection.php';
$user_id = null;
$user_name = null;
$user_img = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'];
    $user_img = $_SESSION['user_img'];
}

$query = "SELECT * FROM Usuario WHERE idUsuario = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$userData = mysqli_fetch_assoc($resultado);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DaFont</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/stylesSign.css">
    <link rel="stylesheet" href="CSS/Perfil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Audiowide&family=Bonheur+Royale&family=Creepster&family=Eater&family=Henny+Penny&family=Iansui&family=Meddon&family=UnifrakturMaguntia&display=swap');
    </style>

    <script src="https://kit.fontawesome.com/093074d40c.js" crossorigin="anonymous"></script>
</head>

<body>

    <!-- <div class="contenedorPrincipal"> -->
<header>
    <nav class="navbar">
        <div>
            <a href="DaFont_index.php" class="logo"><img id="navImg" src="Dafont1-Dark1.png" alt="Logo pagina Dafont" loading="lazy"></a>
        </div>
        <ul class="nav-links" id="navMenu">
            <button id="closeMenu" aria-label="cerrar menu"><i class="fa fa-close"></i></button>
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
                        <button type="submit" title="Buscar" class="search-button" aria-label="Boton de busqueda"><i class="fa fa-search"></i></button>
                    </div>
                </form>
            </li>
        </ul>
         <?php if(isset($_SESSION['user_id'])){?>
        <button id="btnFav" onclick="window.location.href='Dafont_Profile.php'" aria-label="Boton pagina favoritos"><i class="fa-solid fa-heart"></i> Favoritas</button>
        <?php } ?>
        <button id="btnSesion" 
            <?php if(!isset($_SESSION['user_id'])){ ?>
            title="Iniciar Sesion" onclick="window.location.href='Dafont_Log.php'" aria-label="boton para iniciar sesion">
            <i class="fa-solid fa-circle-user"></i></button>
            <?php }else{ ?>
            title="Mi Perfil" onclick="window.location.href='Dafont_Editar.php'" aria-label="Boton Tu perfil">
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
        <div id="notification-area" class="notification-area" style="display: none;">
        </div>

 <div class="ContDatos">
        <h1>Tu Perfil</h1>    
        
            <?php
            if (empty($imagen_final_a_mostrar) && isset($_SESSION['user_img']) && !empty($_SESSION['user_img'])) {
                $imagen_final_a_mostrar = $_SESSION['user_img'];
            }

            if (empty($imagen_final_a_mostrar)): ?>
                <img loading="lazy" src="IMG/image_default.png" alt="Imagen de perfil por defecto">
            <?php else: ?>
                <img loading="lazy" src="<?php echo htmlspecialchars($imagen_final_a_mostrar); ?>" alt="Imagen de perfil de <?php echo htmlspecialchars($user_name); ?>">
            <?php endif; ?>
            <div class="">
            <h2><?php echo htmlspecialchars($user_name); ?></h2>
            <?php if(isset($userData['pagina']) && $userData['pagina'] !== ''): ?>
                <a href="<?php echo htmlspecialchars($userData['pagina']); ?>" target="_blank" rel="noopener noreferrer">Página oficial</a>
            <?php else: ?>
                <h6>Aún no tienes página oficial</h6>
            <?php endif; ?></div>
     
    </div>


        <form action="BACK/EditPerfil.php" id="FormLogin" method="post" enctype="multipart/form-data">

            <div>
                <h1>Tus Datos</h1><br>

                <div id="NombreUsuario">
                    <div class="input-group">
                        <input  type="text" name="nombre" autocomplete="off" class="input" value="<?php echo isset($userData['nombres']) ? htmlspecialchars($userData['nombres']) : ''; ?>">
                        <label class="user-label" for="nombre">Nombre(s)</label>
                    </div>

                    <div class="input-group">
                        <input  type="text" name="apellido" autocomplete="off" class="input" value="<?php echo isset($userData['apellidos']) ? htmlspecialchars($userData['apellidos']) : ''; ?>">
                        <label class="user-label" for="apellido">Apellidos</label>
                    </div>
                </div>
                <div class="input-group">
                    <input  type="email" name="email_usuario" autocomplete="off" class="input" value="<?php echo isset($userData['correo']) ? htmlspecialchars($userData['correo']) : ''; ?>">
                    <label class="user-label" for="email_usuario">Correo</label>
                </div>

                <div class="input-group">
                    <input  type="text" name="Pagina_usuario" autocomplete="off" class="input" value="<?php echo isset($userData['pagina']) ? htmlspecialchars($userData['pagina']) : ''; ?>">
                    <label class="user-label" for="Pagina_usuario">Pagina</label>
                </div>

                <div class="input-group">
                    <label for="fecha_usuario">Fecha de Nacimiento</label><br>
                    <input  type="date" name="fecha_usuario" autocomplete="off" class="input" value="<?php echo isset($userData['natal']) ? htmlspecialchars($userData['natal']) : ''; ?>">
                </div>

                <div class="input-group">
                    <input  type="text" name="nombre_usuario" autocomplete="off" class="input" value="<?php echo isset($userData['usuario']) ? htmlspecialchars($userData['usuario']) : ''; ?>">
                    <label class="user-label" for="nombre_usuario">Usuario</label>
                </div>
                <div class="input-group" id="psw-contenedor">
                    <input  type="Password" name="contraseña_usuario" autocomplete="off" class="input">
                    <label class="user-label" for="contraseña_usuario">Contraseña</label>
                </div>
                <div class="input-group" id="psw-contenedor2">
                    <input  type="Password" name="contraseña_Check" autocomplete="off" class="input">
                    <label class="user-label" aria-label="contraseña_Check">Confirma tu contraseña</label>
                </div>

                <div class="input-group">
                    <!-- <input  type="image" name="image" class="input" onchange="previewImage()"> -->
                    <label class="user-label" for="imgRuta">Imagen de perfil</label>
                    <br>
                    <img id="imgPerfil" src="#" alt="Vista previa de la imagen" style="display: none; width: 100px;">
                    <br>
                    <input class="input" type="file" id="imgRuta" name="imgRuta" accept="image/*" onchange="previewImage()">
                </div>
              <label class="switch-label" for="psw-change"> <input type="checkbox" name="psw-change" id="psw-change" onclick="togglePasswordVisibility()">
             <span class="slider round"></span>
  <span class="switch-text">Cambiar contraseña</span>
            </label>
                <br>
                <div id="botones"><button type="submit" id="btn-REG">Guardar</button> </div>
        </form>


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
        <script src="JS/ALERTS.js"></script>
    <script src="JS/EditPerfil.js"></script>
    <script src="JS/app.js"></script>
    <!-- </div> -->
    <!-- <script src="JS/scriptCards.js"></script> -->
    <script src="JS/breadcrumbing.js"></script>


    <script src="JS/script.js"></script>
</body>

</html>