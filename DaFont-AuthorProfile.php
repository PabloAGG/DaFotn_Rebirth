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

$query="SELECT * FROM Usuario where idUsuario=1";
$result = $conn->query($query);
$row = $result->fetch_assoc();
        $autor_id = $row["idUsuario"];
        $autor_name = $row["usuario"];
        $autor_img = $row["imgPath"];
        $autor_page = $row["pagina"];
 
$user_id_actual_seguro = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0; 
        $sql_fonts = "SELECT
    f.idFont,
    f.fontName,
    f.fontFamilyCSS,
    f.fontStyleFallback,
    f.descargas,
    f.licenciaDescripcion,
    u.usuario AS nombreAutor,
    u.idUsuario AS idAutor,
    (SELECT AVG(c.estrellas) FROM calificaciones c WHERE c.idFont = f.idFont) AS promedioEstrellas,
    (SELECT COUNT(c.idCalf) FROM calificaciones c WHERE c.idFont = f.idFont) AS totalCalificaciones,
    (SELECT COUNT(*) FROM FavFonts ff WHERE ff.idFont = f.idFont AND ff.idUsuario = $user_id_actual_seguro) AS currentUserHasFavorited
FROM Fonts f 
LEFT JOIN Usuario u ON f.fontAutor = u.idUsuario where f.fontAutor = 1
ORDER BY f.fechaSubida DESC";

// Ejecutar la consulta
$result_fonts = mysqli_query($conn, $sql_fonts);

$fonts = [];
if ($result_fonts && mysqli_num_rows($result_fonts) > 0) {
    while ($row = mysqli_fetch_assoc($result_fonts)) {
        $fonts[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DaFont-Perfil</title>
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
                    <button class="category-btn" name="Dingbats">Display</button>
                    <ul class="submenu">
                        <li><a href="#">Mágico</a></li>
                        <li><a href="#">Épico</a></li>
                        <li><a href="#">Oscuro</a></li>
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
    <button class="btn-filtros" id="btn-filtros">
        <i class="fa fa-sliders"></i> 
      </button>
    
    <aside class="Filtros">
        <button class="hideMenu"><i class="fa fa-close"></i></button>
     <div class="Ajustes">  
      <div>
      <label for="text-input">Texto de Prueba:</label>
      <input type="text" name="text-input" id="text-input" placeholder="Escribe algo..."></div><br>
      <div>
      <label for="font-select">Tamaño de Fuente:</label>
      <input type="range" class="slider" id="font-size-range" min="10" max="100"  value="24"></div><br>
      <button id="dkmode"><i class="fa fa-adjust"></i></button>
    </div><br>
       </aside>
<div class="author-id">
    <div class="presentacion">
      <?php if($autor_img!==null){ ?>
<img src="<?php echo htmlspecialchars($autor_img)?>" alt="imagen del perfil del autor">
<?php }else{ ?>
<img src="img/DefaultProfile.png" alt="imagen del perfil del autor">S
<?php } ?>
<h2><?php echo$autor_name?></h2></div>
<a href=">}<?php echo$autor_page;?>">Pagina oficial</a>
</div>
<div class="FontContainer">
    <h1>Sus Fuentes...</h1><br>
    <?php if (!empty($fonts)): ?>
        <?php foreach ($fonts as $font): ?>
            <div class="font-card">
              <div class="presentacion">
                <h2 class="font-name" onclick="window.location.href='Dafont_FontDetails.php?id=<?php echo $font['idFont']; ?>'"><?php echo htmlspecialchars($font['fontName']); ?></h2>
              </div>
                    <br>
                    <div class="font-preview" style="font-family: '<?php echo htmlspecialchars($font['fontFamilyCSS']); ?>', <?php echo htmlspecialchars($font['fontStyleFallback']); ?>;"><?php echo htmlspecialchars($font['fontName']); // O el texto de prueba del input ?></div>
                <div class="font-details">
                
                    <span class="downloads"><?php echo number_format($font['descargas']); ?> descargas </span> <span class="license"><?php echo htmlspecialchars($font['licenciaDescripcion']); ?></span>

                    <div class="stars-display" data-font-id="<?php echo $font['idFont']; ?>">
                        <?php
                        $promedio = round($font['promedioEstrellas'] ?? 0);
                        $totalVotos = (int) ($font['totalCalificaciones'] ?? 0);
                        for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?php echo ($i <= $promedio) ? 'filled' : ''; ?>" data-value="<?php echo $i; ?>">&#9733;</span>
                        <?php endfor; ?>
                        <span class="rating-average">(<?php echo number_format($font['promedioEstrellas'] ?? 0, 1); ?> de <?php echo $totalVotos; ?> votos)</span>
                    </div>
                    </div>
                <?php
                $isFavorite= ($font['currentUserHasFavorited'] > 0)?true:false; // Verifica si el usuario actual ya ha marcado la fuente como favorita
                $favIconClass = $isFavorite ? "fa-solid fa-heart" : "fa-regular fa-heart";
                ?>
                <button class="btn btn-favorite" title="Añadir a favoritos" data-fontid="<?php echo $font['idFont']; ?>" ><i class="<?php echo $favIconClass;?>"></i></button>
                <button class="download-btn" id="download-btn" title="Descargar fuente (ejemplo TXT)" data-font-id="<?php echo $font['idFont']; ?>"><i class="fa-solid fa-download"></i></button>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No hay fuentes disponibles para mostrar.</p>
    <?php endif; ?>
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
    <script src="JS/Favs.js"></script>
</body>
</html>