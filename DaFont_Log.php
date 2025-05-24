
<?php
require_once 'BACK/DB_connection.php';
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DaFont</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/styleLog.css">   
    <script src="https://kit.fontawesome.com/093074d40c.js" crossorigin="anonymous"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Audiowide&family=Bonheur+Royale&family=Creepster&family=Eater&family=Henny+Penny&family=Iansui&family=Meddon&family=UnifrakturMaguntia&display=swap');
        </style>
  </head>
<body>

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
 
        <h1>Inicia Sesión para Guardar tus Fuentes Favoritas</h1><br>
 <div class="Login">

    <form action="BACK/Login.php" id="FormLogin" method="POST">
        <h3>Inicia Sesión en DaFont</h3><br>

          <div class="input-group">
            <input required="" type="text" name="user_name" autocomplete="off" class="input">
            <label class="user-label">Usuario</label>
          </div>
          <div class="input-group">
            <input required="" type="password" name="password_user" autocomplete="off" class="input">
            <label class="user-label">Contraseña</label>
          </div>
   <div id="notification-area" class="notification-area" style="display: none;">
    </div>

    <button>Ingresa</button>
    <span>¿Aún no tienes Cuenta?  <a href="Dafont-SignUp.php">Crea una ahora!</a></span>
      <!-- <span>O incia Sesion con
 
      <button><svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="20" height="20" viewBox="0 0 48 48">
         <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"></path><path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"></path><path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"></path><path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"></path>
         </svg></button></span> -->
    
    </form>
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
<!-- </div> -->  
 <script src="JS/script.js"></script>
  <script src="JS/Login.js"></script>
    <script src="JS/breadcrumbing.js"></script>
    <script src="JS/app.js"></script>
    <script src="JS/ALERTS.js"></script>
</body>
</html>
