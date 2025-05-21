<?php
require 'DB_connection.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
    $apellido = mysqli_real_escape_string($conn, $_POST['apellido']);
    $nombreUsuario = mysqli_real_escape_string($conn, $_POST['nombre_usuario']);
    $email = mysqli_real_escape_string($conn, $_POST['email_usuario']);
    $contraseña = password_hash($_POST['contraseña_usuario'], PASSWORD_DEFAULT);
    $fechaNacimiento = $_POST['fecha_usuario'];


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
        header("Location: ../DaFont-SignUp.php?error=$error");
        exit();
    }
    


    // Insertar el nuevo usuario
    $query = "INSERT INTO Usuario 
          (usuario,correo,contraseña,nombres,apellidos,natal) 
          VALUES (?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param(
    $stmt, 
    "ssssss",  
    $nombreUsuario, 
     $email, 
      $contraseña, 
    $nombre,
    $apellido,
    $fechaNacimiento
);


    if (mysqli_stmt_execute($stmt)) {
        header('Location: ../DaFont_Log.php?success=user_created');
        exit();
    } else {
        header('Location: ../DaFont_Log.php?error=user_not_created');
    }
}
//} 
function existeCampo($conn, $campo, $valor) {
    $query = "SELECT idUsuario FROM Usuario WHERE $campo = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $valor);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    return mysqli_stmt_num_rows($stmt) > 0;
}
// Cerramos la conexión
mysqli_close($conn);
?>