
<?php
require_once 'BACK/DB_connection.php';
session_start();
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DaFont</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/stylesSign.css">
    <script src="https://kit.fontawesome.com/093074d40c.js" crossorigin="anonymous"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Audiowide&family=Bonheur+Royale&family=Creepster&family=Eater&family=Henny+Penny&family=Iansui&family=Meddon&family=UnifrakturMaguntia&display=swap');
        </style>
       
</head>
<body>

    <!-- <div class="contenedorPrincipal"> -->

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

    <form action="BACK/Registro.php" method="POST" id="FormLogin">
        <div>
        <h1>Crea tu cuenta Dafont</h1><br>
        <p>Solo necesitas de tu cuenta para acceder a todas las funciones.</p></div>
        <div id="NombreUsuario">
        <div class="input-group">
            <input required="" type="text" name="nombre" autocomplete="off" class="input">
            <label class="user-label">Nombre(s)</label>
          </div>
          <div class="input-group">
            <input required="" type="text" name="apellido" autocomplete="off" class="input">
            <label class="user-label">Apellidos</label>
          </div>
        </div>
          <div class="input-group">
            <input required="" type="email" name="email_usuario" autocomplete="off" class="input">
            <label class="user-label">Correo</label>
          </div>
          <div class="input-group">
            <label >Fecha de Nacimiento</label><br>
             <input required="" type="date" name="fecha_usuario" autocomplete="off" class="input">
           
          </div>
        <div class="input-group">
            <input required="" type="text" name="nombre_usuario" autocomplete="off" class="input">
            <label class="user-label">Usuario</label>
          </div>
          <div class="input-group">
            <input required="" type="Password" name="contraseña_usuario" autocomplete="off" class="input">
            <label class="user-label">Contraseña</label>
          </div>
          <div class="input-group">
            <input required="" type="Password" name="contraseña_Check" autocomplete="off" class="input">
            <label class="user-label">Confirma tu contraseña</label>
          </div>

          <div id="botones"><button id="btn-REG">Registrarme</button> <button type="button" onclick="window.location.href='DaFont_Log.php'" id="btn-Cnl">Cancelar</button></div>
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
<!-- </div> -->  <script src="JS/script.js"></script>
 <script src="JS/SingUp.js"></script>
    <script src="JS/scriptCards.js"></script>
    <script src="JS/breadcrumbing.js"></script>
    <script src="JS/app.js"></script>
        <script src="JS/ALERTS.js"></script>
</body>
</html>
