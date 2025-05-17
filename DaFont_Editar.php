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
                <a href="DaFont_index.php" class="logo"><img id="navImg" src="Dafont1-Dark1.png" alt="Logo pagina Dafont"></a>
            </div>

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
                <?php if ($user_id === null) { ?>
                onclick="window.location.href='Dafont_Log.php'">
                <i class="fa-solid fa-circle-user"></i></button>
        <?php } else { ?>
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
        <div id="notification-area" class="notification-area" style="display: none;">
        </div>

        <form action="BACK/EditPerfil.php" id="FormLogin" method="post" enctype="multipart/form-data">

            <div>
                <h1>Modifica tus Datos</h1><br>

                <div id="NombreUsuario">
                    <div class="input-group">
                        <input  type="text" name="nombre" autocomplete="off" class="input" value="<?php echo isset($userData['nombres']) ? htmlspecialchars($userData['nombres']) : ''; ?>">
                        <label class="user-label">Nombre(s)</label>
                    </div>

                    <div class="input-group">
                        <input  type="text" name="apellido" autocomplete="off" class="input" value="<?php echo isset($userData['apellidos']) ? htmlspecialchars($userData['apellidos']) : ''; ?>">
                        <label class="user-label">Apellidos</label>
                    </div>
                </div>
                <div class="input-group">
                    <input  type="email" name="email_usuario" autocomplete="off" class="input" value="<?php echo isset($userData['correo']) ? htmlspecialchars($userData['correo']) : ''; ?>">
                    <label class="user-label">Correo</label>
                </div>

                <div class="input-group">
                    <input  type="text" name="Pagina_usuario" autocomplete="off" class="input" value="<?php echo isset($userData['pagina']) ? htmlspecialchars($userData['pagina']) : ''; ?>">
                    <label class="user-label">Pagina</label>
                </div>

                <div class="input-group">
                    <label>Fecha de Nacimiento</label><br>
                    <input  type="date" name="fecha_usuario" autocomplete="off" class="input" value="<?php echo isset($userData['natal']) ? htmlspecialchars($userData['natal']) : ''; ?>">
                </div>

                <div class="input-group">
                    <input  type="text" name="nombre_usuario" autocomplete="off" class="input" value="<?php echo isset($userData['usuario']) ? htmlspecialchars($userData['usuario']) : ''; ?>">
                    <label class="user-label">Usuario</label>
                </div>
                <div class="input-group" id="psw-contenedor">
                    <input  type="Password" name="contraseña_usuario" autocomplete="off" class="input">
                    <label class="user-label">Contraseña</label>
                </div>
                <div class="input-group" id="psw-contenedor2">
                    <input  type="Password" name="contraseña_Check" autocomplete="off" class="input">
                    <label class="user-label">Confirma tu contraseña</label>
                </div>

                <div class="input-group">
                    <!-- <input  type="image" name="image" class="input" onchange="previewImage()"> -->
                    <label class="user-label">Imagen de perfil</label>
                    <br>
                    <img id="imgPerfil" src="#" alt="Vista previa de la imagen" style="display: none; width: 100px;">
                    <br>
                    <input class="input" type="file" id="imgRuta" name="imgRuta" accept="image/*" onchange="previewImage()">
                </div>
                <label for="#"> <input type="checkbox" name="psw-change" id="psw-change" onclick="togglePasswordVisibility()">Cambiar contraseña</label><br>
                <div id="botones"><button type="submit" id="btn-REG">Completar</button> <button type="button" id="btn-Cnl" onclick="location.href='DaFont_Profile.php'">Regresar</button></div>
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