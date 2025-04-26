<?php

// Conexión a la base de datos
require 'DB_connection.php';
session_start(); // Iniciar la sesión para manejar la autenticación
// Verificamos si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Obtenemos los datos del formulario de inicio de sesión
    $usuarioOEmail = mysqli_real_escape_string($conn, $_POST['user_name']);
    $password = trim($_POST['password_user']);

    // Consulta para buscar el usuario por nombre de usuario o correo electrónico
    $query = "SELECT * FROM Usuario WHERE usuario = ? OR correo = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ss", $usuarioOEmail, $usuarioOEmail);
mysqli_stmt_execute($stmt);

// 2. Obtener resultados
$resultado = mysqli_stmt_get_result($stmt);


    if (mysqli_num_rows($resultado) > 0) {
        $row = mysqli_fetch_assoc($resultado);
        // Verificamos la contraseña usando password_verify
//         echo 'Ingresada: ' . $password . '<br>';
// echo 'En base de datos: ' . $row['contra'] . '<br>';
// var_dump(password_verify($password, $row['contra']));
// exit();
        if (password_verify($password, $row['contraseña'])) {
            $_SESSION['user_id'] = $row['idUsuario'];
            $_SESSION['user_name'] = $row['usuario'];
            $_SESSION['user_img'] = $row['imgPath']; // Guardamos el rol
        
                header('Location: ../DaFont_index.php');
                exit(); 
        }
         else {
            header('Location: ../DaFont_Log.php?error=password');
          exit(); // Salimos del script después de redirigir
        }
    } else {
        // Usuario o correo no encontrado
        header('Location: ../DaFont_Log.php?error=user_not_found');
        exit(); // Salimos del script después de redirigir
    }
}

// Cerramos la conexión
mysqli_close($conn);
?>
