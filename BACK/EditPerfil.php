<?php

require 'DB_connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    // Redirigir o devolver error si el usuario no está logueado
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado.']);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Recoger datos del formulario ---
    $nombre = !empty($_POST['nombre']) ? mysqli_real_escape_string($conn, $_POST['nombre']) : null;
    $apellido = !empty($_POST['apellido']) ? mysqli_real_escape_string($conn, $_POST['apellido']) : null;
    $nombreUsuario = !empty($_POST['nombre_usuario']) ? mysqli_real_escape_string($conn, $_POST['nombre_usuario']) : null;
    $email = !empty($_POST['email_usuario']) ? mysqli_real_escape_string($conn, $_POST['email_usuario']) : null;
    $fechaNacimiento = !empty($_POST['fecha_usuario']) ? $_POST['fecha_usuario'] : null;
    $pagina = isset($_POST['Pagina_usuario']) ? mysqli_real_escape_string($conn, $_POST['Pagina_usuario']) : null; // Puede ser una cadena vacía si se borra

    // --- Comprobación de existencia de usuario/email (si se cambiaron) ---
    // Es importante hacer esto ANTES de construir el UPDATE si el nombre de usuario o email son campos que se pueden cambiar.
    // Asumimos que quieres permitir el cambio de 'usuario' y 'correo', y estos deben ser únicos.
    if ($nombreUsuario || $email) {
        $check_fields = [];
        $check_params_types = "";
        $check_params_values = [];

        if ($nombreUsuario) {
            $check_fields[] = "usuario = ?";
            $check_params_types .= "s";
            $check_params_values[] = $nombreUsuario;
        }
        if ($email) {
            $check_fields[] = "correo = ?";
            $check_params_types .= "s";
            $check_params_values[] = $email;
        }

        if (!empty($check_fields)) {
            $check_query_sql = "SELECT idUsuario FROM Usuario WHERE (" . implode(" OR ", $check_fields) . ") AND idUsuario != ?";
            $check_params_types .= "i";
            $check_params_values[] = $user_id;

            $check_stmt = mysqli_prepare($conn, $check_query_sql);
            mysqli_stmt_bind_param($check_stmt, $check_params_types, ...$check_params_values);
            mysqli_stmt_execute($check_stmt);
            mysqli_stmt_store_result($check_stmt);

            if (mysqli_stmt_num_rows($check_stmt) > 0) {
                $error = "";
        if (existeCampo($conn, 'usuario', $nombreUsuario)) {
            $error = "username_exists";
        } else {
            $error = "email_exists";
        }
        header("Location: ../DaFont_SignUp.php?error=$error");
        exit();
            }
            mysqli_stmt_close($check_stmt);
        }
    }


    // --- Construcción dinámica de la consulta UPDATE ---
    $update_fields = [];
    $params_types = "";
    $params_values = [];

    if ($nombre !== null) {
        $update_fields[] = "nombres = ?";
        $params_types .= "s";
        $params_values[] = $nombre;
    }
    if ($apellido !== null) {
        $update_fields[] = "apellidos = ?";
        $params_types .= "s";
        $params_values[] = $apellido;
    }
    if ($nombreUsuario !== null) {
        $update_fields[] = "usuario = ?";
        $params_types .= "s";
        $params_values[] = $nombreUsuario;
        $_SESSION['user_name'] = $nombreUsuario; // Actualizar el nombre de usuario en la sesión
    }
    if ($email !== null) {
        $update_fields[] = "correo = ?";
        $params_types .= "s";
        $params_values[] = $email;
    }
    if ($fechaNacimiento !== null) {
        $update_fields[] = "natal = ?";
        $params_types .= "s";
        $params_values[] = $fechaNacimiento;
    }
    if ($pagina !== null) { // Se actualiza incluso si es una cadena vacía (para borrarla)
        $update_fields[] = "pagina = ?";
        $params_types .= "s";
        $params_values[] = $pagina;
    }

    // --- Manejar nueva contraseña si se proporciona ---
    if (!empty($_POST['contraseña_usuario'])) {
        // Aquí deberías añadir validación para la contraseña, ej. que coincida con una confirmación.
        // if ($_POST['contraseña_usuario'] === $_POST['contraseña_Check']) {
        $nuevaContraseña = password_hash($_POST['contraseña_usuario'], PASSWORD_DEFAULT);
        $update_fields[] = "contraseña = ?";
        $params_types .= "s";
        $params_values[] = $nuevaContraseña;
        // } else {
        //     header("Location: ../DaFont_Editar.php?error=password_mismatch");
        //     exit();
        // }
    }

    // --- Manejar subida de imagen si se proporciona ---
    $rutaFinalImagen = null;
    if (isset($_FILES['imgRuta']) && $_FILES['imgRuta']['error'] == UPLOAD_ERR_OK) {
        $rutaDestino = "../IMG/Avatares/"; // Asegúrate que esta carpeta exista y tenga permisos de escritura
        if (!is_dir($rutaDestino)) {
            mkdir($rutaDestino, 0777, true);
        }
        $nombreArchivo = basename($_FILES["imgRuta"]["name"]);
        // Generar un nombre de archivo único para evitar colisiones
        $extension = pathinfo($nombreArchivo, PATHINFO_EXTENSION);
        $nombreUnico = uniqid('avatar_', true) . '.' . $extension;
        $rutaFinalImagen = $rutaDestino . $nombreUnico;

        if (move_uploaded_file($_FILES["imgRuta"]["tmp_name"], $rutaFinalImagen)) {
            $update_fields[] = "imgPath = ?";
            $params_types .= "s";
            $params_values[] = $rutaFinalImagen; // Guardas la ruta, no el contenido binario como en ModProfile
            $_SESSION['user_img'] = $rutaFinalImagen; // Actualizar imagen en la sesión
        } else {
            header("Location: ../DaFont_Editar.php?error=image_upload_failed");
            exit();
        }
    }

    // --- Ejecutar la consulta UPDATE solo si hay campos para actualizar ---
    if (!empty($update_fields)) {
        $query = "UPDATE Usuario SET " . implode(", ", $update_fields) . " WHERE idUsuario = ?";
        $params_types .= "i";
        $params_values[] = $user_id;

        $stmt = mysqli_prepare($conn, $query);

        // El operador "splat" (...) desempaqueta el array $params_values en argumentos individuales
        mysqli_stmt_bind_param($stmt, $params_types, ...$params_values);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            header('Location: ../Dafont_Profile.php?success=user_updated');
            exit();
        } else {
            $error_message = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            // Log el error real para ti: error_log("Error al actualizar perfil: " . $error_message);
            header('Location: ../DaFont_Editar.php?error=user_not_updated');
            exit();
        }
    } else {
        // No se proporcionaron campos para actualizar, pero no es necesariamente un error.
        // Podrías redirigir con un mensaje de "nada que actualizar" o simplemente a la página de perfil.
        mysqli_close($conn);
        header('Location: ../Dafont_Profile.php?success=no_changes'); // Opcional: un mensaje diferente
        exit();
    }

} else {
    // Si no es POST, redirigir o mostrar error
    header("Location: ../DaFont_Editar.php?error=invalid_request_method");
    exit();
}

// La función existeCampo debería estar definida o incluida si la usas para la comprobación de usuario/email.
// Si no la tienes globalmente, puedes copiarla aquí:
function existeCampo($conn, $campo, $valor) {
    $query = "SELECT idUsuario FROM Usuario WHERE $campo = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $valor);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    return mysqli_stmt_num_rows($stmt) > 0;
}

?>