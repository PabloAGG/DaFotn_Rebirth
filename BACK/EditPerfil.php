<?php

require 'DB_connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    // Redirigir o devolver error si el usuario no está logueado
    header('Content-Type: application/json');
    http_response_code(401); // No autorizado
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado.']);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Recoger datos del formulario ---
    $nombre = !empty($_POST['nombre']) ? mysqli_real_escape_string($conn, trim($_POST['nombre'])) : null;
    $apellido = !empty($_POST['apellido']) ? mysqli_real_escape_string($conn, trim($_POST['apellido'])) : null;
    $nombreUsuario = !empty($_POST['nombre_usuario']) ? mysqli_real_escape_string($conn, trim($_POST['nombre_usuario'])) : null;
    $email = !empty($_POST['email_usuario']) ? mysqli_real_escape_string($conn, trim($_POST['email_usuario'])) : null;
    $fechaNacimiento = !empty($_POST['fecha_usuario']) ? $_POST['fecha_usuario'] : null;
    $pagina = isset($_POST['Pagina_usuario']) ? mysqli_real_escape_string($conn, trim($_POST['Pagina_usuario'])) : null;

    // --- Comprobación de existencia de usuario/email (si se cambiaron) ---
    if (($nombreUsuario && $nombreUsuario !== $_SESSION['user_name']) || $email) { // Solo verificar si el nombre de usuario cambió
        $check_fields = [];
        $check_params_types = "";
        $check_params_values = [];

        // Comprobar nuevo nombre de usuario solo si es diferente al actual
        if ($nombreUsuario && $nombreUsuario !== $_SESSION['user_name']) {
            $check_fields[] = "usuario = ?";
            $check_params_types .= "s";
            $check_params_values[] = $nombreUsuario;
        }
        // Comprobar nuevo email (asumiendo que no tienes el email actual en sesión para comparar, o simplemente compruebas si ya existe otro con ese email)
        if ($email) { // Podrías añadir una condición para verificar si el email realmente cambió si lo tuvieras en sesión
            $query_email_actual = "SELECT correo FROM Usuario WHERE idUsuario = ?";
            $stmt_email_actual = mysqli_prepare($conn, $query_email_actual);
            mysqli_stmt_bind_param($stmt_email_actual, "i", $user_id);
            mysqli_stmt_execute($stmt_email_actual);
            $res_email_actual = mysqli_stmt_get_result($stmt_email_actual);
            $row_email_actual = mysqli_fetch_assoc($res_email_actual);
            mysqli_stmt_close($stmt_email_actual);

            if (!$row_email_actual || $email !== $row_email_actual['correo']) {
                $check_fields[] = "correo = ?";
                $check_params_types .= "s";
                $check_params_values[] = $email;
            }
        }


        if (!empty($check_fields)) {
            $check_query_sql = "SELECT idUsuario, usuario, correo FROM Usuario WHERE (" . implode(" OR ", $check_fields) . ") AND idUsuario != ?";
            $check_params_types .= "i";
            $check_params_values[] = $user_id;

            $check_stmt = mysqli_prepare($conn, $check_query_sql);
            mysqli_stmt_bind_param($check_stmt, $check_params_types, ...$check_params_values);
            mysqli_stmt_execute($check_stmt);
            $result_check = mysqli_stmt_get_result($check_stmt);
            
            if (mysqli_num_rows($result_check) > 0) {
                $existing_data = mysqli_fetch_assoc($result_check);
                $error = "";
                if ($nombreUsuario && $existing_data['usuario'] === $nombreUsuario) {
                    $error = "username_exists";
                } elseif ($email && $existing_data['correo'] === $email) {
                    $error = "email_exists";
                } else {
                     // Si hay múltiples campos que coinciden o un caso no cubierto
                    $error = "field_exists"; // Un error genérico si ambos existen para diferentes usuarios o algo así.
                }
                mysqli_stmt_close($check_stmt);
                // Redirigir a DaFont_Editar.php en lugar de DaFont_SignUp.php
                header("Location: ../DaFont_Editar.php?error=$error");
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
    if ($nombreUsuario !== null && $nombreUsuario !== $_SESSION['user_name']) { // Solo actualizar si es diferente
        $update_fields[] = "usuario = ?";
        $params_types .= "s";
        $params_values[] = $nombreUsuario;
    }
    if ($email !== null) { // Aquí también podrías comparar con el email actual si lo trajiste de la BD
        $update_fields[] = "correo = ?";
        $params_types .= "s";
        $params_values[] = $email;
    }
    if ($fechaNacimiento !== null) {
        $update_fields[] = "natal = ?";
        $params_types .= "s";
        $params_values[] = $fechaNacimiento;
    }
    if ($pagina !== null) {
        $update_fields[] = "pagina = ?";
        $params_types .= "s";
        $params_values[] = $pagina;
    }

    // --- Manejar nueva contraseña si se proporciona ---
    if (isset($_POST['psw-change']) && !empty($_POST['contraseña_usuario'])) { //Verificar si el checkbox está marcado
        if ($_POST['contraseña_usuario'] === $_POST['contraseña_Check']) {
            $nuevaContraseña = password_hash($_POST['contraseña_usuario'], PASSWORD_DEFAULT);
            $update_fields[] = "contraseña = ?";
            $params_types .= "s";
            $params_values[] = $nuevaContraseña;
        } else {
            header("Location: ../DaFont_Editar.php?error=password_mismatch");
            exit();
        }
    }

    // --- Manejar subida de imagen si se proporciona ---
    $rutaParaWeb = null; // Variable para la ruta que irá a la BD y a la sesión
    if (isset($_FILES['imgRuta']) && $_FILES['imgRuta']['error'] == UPLOAD_ERR_OK) {
        // Ruta física donde se guardará el archivo en el servidor
        // Este script está en BACK/, así que ../IMG/ lleva a la carpeta IMG en la raíz del proyecto
        $rutaDestinoEnServidor = "../IMG/Avatares/";

        if (!is_dir($rutaDestinoEnServidor)) {
            if (!mkdir($rutaDestinoEnServidor, 0777, true)) {
                // Error al crear directorio, importante manejarlo
                header("Location: ../DaFont_Editar.php?error=cant_create_folder");
                exit();
            }
        }

        $nombreArchivoOriginal = basename($_FILES["imgRuta"]["name"]);
        $extension = strtolower(pathinfo($nombreArchivoOriginal, PATHINFO_EXTENSION)); // Estandarizar a minúsculas
        $nombreUnico = uniqid('avatar_', true) . '.' . $extension;
        
        $rutaFisicaCompleta = $rutaDestinoEnServidor . $nombreUnico;

        // La ruta que se guardará en la base de datos y se usará en el HTML (relativa a la raíz del proyecto)
        $rutaParaWeb = "IMG/Avatares/" . $nombreUnico; // <--- AJUSTE CLAVE

        if (move_uploaded_file($_FILES["imgRuta"]["tmp_name"], $rutaFisicaCompleta)) {
            $update_fields[] = "imgPath = ?";
            $params_types .= "s";
            $params_values[] = $rutaParaWeb; // Guardar la ruta web correcta
            // No es necesario actualizar $_SESSION['user_img'] aquí directamente,
            // ya que la página de perfil lo leerá de la BD.
            // O si lo haces, asegúrate que sea $rutaParaWeb
            // $_SESSION['user_img'] = $rutaParaWeb;
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

        if (!$stmt) {
            // Error en la preparación de la consulta
            error_log("Error al preparar la consulta UPDATE: " . mysqli_error($conn));
            header('Location: ../DaFont_Editar.php?error=db_prepare_error');
            exit();
        }

        mysqli_stmt_bind_param($stmt, $params_types, ...$params_values);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);

            // Si se actualizó el nombre de usuario, actualizar la sesión
            if ($nombreUsuario !== null && $nombreUsuario !== $_SESSION['user_name']) {
                $_SESSION['user_name'] = $nombreUsuario;
            }
            // Si se actualizó la imagen, actualizar la sesión con la ruta web correcta
            if ($rutaParaWeb !== null) {
                 $_SESSION['user_img'] = $rutaParaWeb;
            }

            mysqli_close($conn);
            header('Location: ../Dafont_Profile.php?success=user_updated');
            exit();
        } else {
            $error_message = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            error_log("Error al actualizar perfil para user_id $user_id: " . $error_message);
            header('Location: ../DaFont_Editar.php?error=user_not_updated');
            exit();
        }
    } else {
        // No se proporcionaron campos para actualizar
        mysqli_close($conn);
        // Si solo se subió una imagen pero no otros datos, $update_fields podría estar vacío
        // si la lógica de la imagen no se hizo para agregar a $update_fields directamente.
        // Revisando, sí se agrega. Así que "no_changes" es si realmente nada cambió.
        header('Location: ../Dafont_Profile.php?success=no_changes');
        exit();
    }

} else {
    // Si no es POST
    header("Location: ../DaFont_Editar.php?error=invalid_request_method");
    exit();
}

// La función existeCampo debería estar definida o incluida
function existeCampo($conn, $campo, $valor_a_buscar) {
    // Asegurarse de que $campo es un nombre de columna válido para evitar inyección SQL si se construyera dinámicamente.
    // En este caso, lo estás usando con 'usuario' y 'correo' que son fijos, así que está bien.
    if ($valor_a_buscar === null || $valor_a_buscar === '') {
        return false; // No buscar si el valor es nulo o vacío
    }
    $query = "SELECT idUsuario FROM Usuario WHERE $campo = ? AND idUsuario != ?"; // Excluir al usuario actual
    $stmt = mysqli_prepare($conn, $query);
    global $user_id; // Necesitamos el id del usuario actual para la exclusión
    mysqli_stmt_bind_param($stmt, "si", $valor_a_buscar, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $num_rows = mysqli_stmt_num_rows($stmt);
    mysqli_stmt_close($stmt);
    return $num_rows > 0;
}

?>