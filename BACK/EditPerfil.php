<?php

// Conexión a la base de datos
require 'DB_connection.php';
session_start(); // Iniciar la sesión 


// Verificamos si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
    $apellido = mysqli_real_escape_string($conn, $_POST['apellido']);
    $nombreUsuario = mysqli_real_escape_string($conn, $_POST['nombre_usuario']);
    $email = mysqli_real_escape_string($conn, $_POST['email_usuario']);
    $contraseña = password_hash($_POST['contraseña_usuario'], PASSWORD_DEFAULT);
    $fechaNacimiento = $_POST['fecha_usuario'];
$pagina = mysqli_real_escape_string($conn, $_POST['Pagina_usuario']);

    $check_query = "SELECT idUsuario FROM Usuario WHERE usuario = ? OR correo = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "ss", $nombreUsuario, $email);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
   

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        $error = "";
        if (existeCampo($conn, 'usuario', $nombreUsuario)) {
            $error = "username_exists";
        } else {
            $error = "email_exists";
        }
        header("Location: ../DaFont_Editar.php?error=$error");
        exit();
    }

    $rutaDestino = "../IMG/Avatares/";
    $nombreArchivo = basename($_FILES["imgRuta"]["name"]);
    $rutaFinal = $rutaDestino . uniqid() . "_" . $nombreArchivo;

    if (move_uploaded_file($_FILES["imgRuta"]["tmp_name"], $rutaFinal)) {
    $user_id = $_SESSION['user_id'];
   
    $query = "UPDATE Usuario SET 
    nombres = ?, 
    apellidos = ?,
    usuario = ?, 
    correo = ?, 
    natal = ?, 
    pagina = ?,
    imgPath = ?
  WHERE idUsuario = ?";  

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "sssssssi", $nombre, $apellido, $nombreUsuario, $email, $fechaNacimiento, $pagina, $rutaFinal, $user_id);
$result = mysqli_stmt_get_result($stmt);

    if ($result) {
        header('Location: ../Dafont_Profile.php?success=user_updated');
        exit();
    } else {
        header('Location: ../DaFont_Editar.php?error=user_not_updated');
        exit(); // Salimos del script después de redirigir
    }

  exit(); // Salimos del script después de redirigir
    }else {
    // Si no se envió el formulario, redirigimos a la página de edición de datos
    header('Location: ../DaFont_Editar.php?error=image_upload_error');
    exit();
}
} else {
    // Si no se envió el formulario, redirigimos a la página de edición de datos
    header('Location: ../DaFont_Editar.php?error=invalid_request');
    exit();
}

// Cerramos la conexión
mysqli_close($conn);
?>
