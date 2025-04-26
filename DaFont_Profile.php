<?php
session_start();
require 'BACK/DB_connection.php';
$user_id = null;
$user_name = null;
$user_img = null;
if (isset($_SESSION['user_id'])) {
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_img = $_SESSION['user_img'];}

// Verificar si el usuario ha iniciado sesión
if (!isset($user_id)) {
    header('Location: DaFont_Log.php'); // Redirigir al inicio de sesión si no está autenticado
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DaFont-Perfil</DaFont-Perfil></title>
    <link rel="stylesheet" href="CSS/style.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Audiowide&family=Bonheur+Royale&family=Creepster&family=Eater&family=Henny+Penny&family=Iansui&family=Meddon&family=UnifrakturMaguntia&display=swap');
        </style>
     <script src="https://kit.fontawesome.com/093074d40c.js" crossorigin="anonymous"></script>
</head>
<body>
    
<header>
    <nav class="navbar">
        <div>
     <a href="DaFont_index.php" class="logo"><img id="navImg"  src="Dafont1-Dark1.png" alt="Logo pagina Dafont" ></a></div>
      
            <ul class="nav-links" id="navMenu">
                <button id="closeMenu"><i class="fa fa-close"></i></button>
                <li class="dropdown">
                    <button  class="category-btn" name="Fantasia">Fantasia</button>
                    <ul class="submenu">
                        <li><a href="#">Mágico</a></li>
                        <li><a href="#">Épico</a></li>
                        <li><a href="#">Oscuro</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <button class="category-btn" id="Paises">Paises</button>
                    <ul class="submenu">
                        <li><a href="#">Europeo</a></li>
                        <li><a href="#">Latino</a></li>
                        <li><a href="#">Asiático</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <button class="category-btn" name="Tecno" style=" font-family: Audiowide, sans-serif;">Tecno</button>
                    <ul class="submenu">
                        <li><a href="#">Mágico</a></li>
                        <li><a href="#">Épico</a></li>
                        <li><a href="#">Oscuro</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <button class="category-btn" name="Gotico">Gotico</button>
                    <ul class="submenu">
                        <li><a href="#">Europeo</a></li>
                        <li><a href="#">Latino</a></li>
                        <li><a href="#">Asiático</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <button class="category-btn" name="Basico" >Basico</button>
                    <ul class="submenu">
                        <li><a href="#">Mágico</a></li>
                        <li><a href="#">Épico</a></li>
                        <li><a href="#">Oscuro</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <button class="category-btn" name="Script" >Script</button>
                    <ul class="submenu">
                        <li><a href="#">Europeo</a></li>
                        <li><a href="#">Latino</a></li>
                        <li><a href="#">Asiático</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <button class="category-btn" name="Dingbats">Glifos</button>
                    <ul class="submenu">
                        <li><a href="#">Mágico</a></li>
                        <li><a href="#">Épico</a></li>
                        <li><a href="#">Oscuro</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <button class="category-btn" name="Festividades">Festivo </button>
                    <ul class="submenu">
                        <li><a href="#">Europeo</a></li>
                        <li><a href="#">Latino</a></li>
                        <li><a href="#">Asiático</a></li>
                    </ul>
                </li>
                <li>
                    <div class="search-container">
                    <input type="text" class="search-bar" placeholder="Buscar...">
                    <button class="search-button"><i class="fa fa-search"></i></button>
                  </div>
                </li>

                <!-- <li><button id="dkmode"><i class="fa fa-adjust"></i></button></li> -->
   </ul>
       <button id="btnSesion" 
       <?php if($user_id===null){ ?>
        onclick="window.location.href='Dafont_Log.php'">
        <i class="fa-solid fa-circle-user"></i></button>
        <?php }else{ ?>
          onclick="window.location.href='Dafont_Profile.php'">
        <?php echo $user_name ?></button>
        <?php } ?>
     
        <div class="menu-hamburguesa">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>
</header>

<main>
<div class="ContDatos">
    <div class="DatosUs">
    <img src="" alt="">
    <h2>USUARIO</h2>
</div>
<button class="EditarDatos">Modificar Datos</button>
<div class="FuentesFav">

</div>

</div>
</main>

<footer>
       <p> Las fuentes presentadas en este sitio web son propiedad de sus autores, y son freeware, shareware, demos o dominio público. La licencia mencionada encima del botón de descarga es sólo una indicación. Por favor, mira en los ficheros "Readme" en los zip o comprueba lo que se indica en la web del autor para los detalles, y contacta con él/ella en caso de duda. Si no hay autor/licencia indicados, es porque no tenemos la información, lo que no significa que sea gratis.
    </p> <p><a href="">FAQ</a></p>


  </footer>
<!-- </div> -->  <script src="JS/script.js"></script>
    <script src="JS/scriptCards.js"></script>
    <script src="JS/breadcrumbing.js"></script>
    <script src="JS/app.js"></script>
</body>
</html>