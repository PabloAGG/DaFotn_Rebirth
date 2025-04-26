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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DaFont</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/styleCards.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Audiowide&family=Bonheur+Royale&family=Creepster&family=Eater&family=Henny+Penny&family=Iansui&family=Meddon&family=UnifrakturMaguntia&display=swap');
        </style>
     <script src="https://kit.fontawesome.com/093074d40c.js" crossorigin="anonymous"></script></head>
<body>

    <!-- <div class="contenedorPrincipal"> -->
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

  <nav id="breadcrumb">
    <span><a href="DaFont_index.html">Inicio</a></span> 
</nav>
<button class="btn-filtros" id="btn-filtros">
<i class="fa-solid fa-sliders"></i>
  </button>

<aside class="Filtros">
    <button class="hideMenu"><i class="fa-solid fa-angles-left"></i></button>
 <div class="Ajustes">  
  <div>
  <label for="text-input">Texto de Prueba:</label>
  <input type="text" name="text-input" id="text-input" placeholder="Escribe algo..."></div><br>
  <div>
  <label for="font-select">Tamaño de Fuente:</label>
  <input type="range" class="slider" id="font-size-range" min="10" max="100"  value="24"></div><br>
  <button id="dkmode"><i class="fa-solid fa-circle-half-stroke"></i></button>
  <?php if($user_id !== null){ ?>
  <button onclick="window.location.href='BACK/LogOut.php'"><i class="fa-solid fa-right-from-bracket"></i></button>
  <?php } ?>
</div><br>
   </aside>


   <div class="FontContainer">
      <div class="font-card">
        <h2 class="font-name" onclick="window.location.href='Dafont_Log.html'">Henny Penny</h2>
        <div class="font-preview" style= "font-family:Henny Penny, system-ui;">Henny Penny</div>
        <div class="font-details">
          <span class="downloads">23.746 descargas (3.488 ayer)</span>
          <button class="btn"><i class="fa-solid fa-heart-circle-plus"></i></button>
          <span class="license">Gratis para uso personal</span>
        </div>
        <button class="download-btn" id="download-btn"><i class="fa-solid fa-download"></i></button>
      </div>
      
      <div class="font-card">
        <h2 class="font-name" onclick="window.location.href='Dafont_Log.html'">Iansui</h2>
        <div class="font-preview" style="font-family: Iansui, cursive;">Iansui</div>
        <div class="font-details">
          <span class="downloads">23.746 descargas (3.488 ayer)</span>
          <button class="btn"><i class="fa fa-heart-o" ></i></button>
          <span class="license">Gratis para uso personal</span>
        </div>
        <button class="download-btn" id="download-btn"><i class="fa fa-download"></i></button>
      </div>
      

      <div class="font-card">
        <h2 class="font-name" onclick="window.location.href='Dafont_Log.html'">Audiowide</h2>
        <div class="font-preview" style=" font-family: Audiowide, sans-serif;">Audiowide</div>
        <div class="font-details">
          <span class="downloads">23.746 descargas (3.488 ayer)</span>
          <button class="btn"><i class="fa fa-heart-o" ></i></button>
          <span class="license">Gratis para uso personal</span>
        </div>
        <button class="download-btn" id="download-btn"><i class="fa fa-download"></i></button>
      </div>
      

      <div class="font-card">
        <h2 class="font-name" onclick="window.location.href='Dafont_Log.html'">UnifrakturMaguntia</h2>
        <div class="font-preview" style="font-family: UnifrakturMaguntia, cursive;">UnifrakturMaguntia</div>
        <div class="font-details">
          <span class="downloads">23.746 descargas (3.488 ayer)</span>
          <button class="btn"><i class="fa fa-heart-o" ></i></button>
          <span class="license">Gratis para uso personal</span>
        </div>
        <button class="download-btn" id="download-btn"><i class="fa fa-download"></i></button>
      </div>
      
      <div class="font-card">
        <h2 class="font-name" onclick="window.location.href='Dafont_Log.html'">Times New Roman</h2>
        <div class="font-preview" style="font-family: 'Times New Roman', Times, serif;">Times New Roman</div>
        <div class="font-details">
          <span class="downloads">23.746 descargas (3.488 ayer)</span>
          <button class="btn"><i class="fa fa-heart-o" ></i></button>
          <span class="license">Gratis para uso personal</span>
        </div>
        <button class="download-btn" id="download-btn"><i class="fa fa-download"></i></button>
      </div>
      
      <div class="font-card">
        <h2 class="font-name" onclick="window.location.href='Dafont_Log.html'">Meddon</h2>
        <div class="font-preview" style=" font-family: Meddon, cursive">Meddon</div>
        <div class="font-details">
          <span class="downloads">23.746 descargas (3.488 ayer)</span>
          <button class="btn"><i class="fa fa-heart-o" ></i></button>
          <span class="license">Gratis para uso personal</span>
        </div>
        <button class="download-btn" id="download-btn"><i class="fa fa-download"></i></button>
      </div>
      

      <div class="font-card">
        <h2 class="font-name" onclick="window.location.href='Dafont_Log.html'">Eater</h2>
        <div class="font-preview" style="font-family: Eater, serif;">Eater</div>
        <div class="font-details">
          <span class="downloads">23.746 descargas (3.488 ayer)</span>
          <button class="btn"><i class="fa fa-heart-o" ></i></button>
          <span class="license">Gratis para uso personal</span>
        </div>
        <button class="download-btn" id="download-btn"><i class="fa fa-download"></i></button>
      </div>
      

      <div class="font-card">
        <h2 class="font-name" onclick="window.location.href='Dafont_Log.html'">Creepster</h2>
        <div class="font-preview" style="font-family: Creepster, system-ui;">Creepster</div>
        <div class="font-details">
          <span class="downloads">23.746 descargas (3.488 ayer)</span>
          <button class="btn"><i class="fa fa-heart-o" ></i></button>
          <span class="license">Gratis para uso personal</span>
        </div>
        <button class="download-btn" id="download-btn"><i class="fa fa-download"></i></button>
      </div>
</div>
</main>


<footer>
    <p> Las fuentes presentadas en este sitio web son propiedad de sus autores, y son freeware, shareware, demos o dominio público. La licencia mencionada encima del botón de descarga es sólo una indicación. Por favor, mira en los ficheros "Readme" en los zip o comprueba lo que se indica en la web del autor para los detalles, y contacta con él/ella en caso de duda. Si no hay autor/licencia indicados, es porque no tenemos la información, lo que no significa que sea gratis.
    </p> <p><a href="">FAQ</a></p>
  </footer>
<!-- </div> -->
<script src="JS/app.js"></script>
 <script src="JS/scriptIndex.js"></script>
    <script src="JS/script.js"></script>
    <script src="JS/scriptCards.js"></script>
    <script src="JS/breadcrumbing.js"></script>
</body>
</html>
