<?php
// Iniciar la sesión
session_start();

// Conexión a la base de datos
require_once("DB_connection.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si el usuario está logueado
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para comentar']);
        exit();
    }

    // Obtener los valores del formulario
    $fontId = isset($_POST['font_id']) ? intval($_POST['font_id']) : 0;
    $comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';

    // Validar el comentario
    if (empty($comentario)) {
        echo json_encode(['success' => false, 'message' => 'Comentario vacío']);
        exit();
    }

    // Obtener el ID del usuario
    $usuarioId = $_SESSION['user_id']; // Asegurarse de que el usuario esté logueado

    // Insertar el comentario en la base de datos
    if ($fontId > 0 && $usuarioId > 0) {
        $stmt = $conn->prepare("INSERT INTO coment ( idUsuario,idFont, comentario) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $usuarioId,$fontId, $comentario );

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Comentario guardado correctamente']);
                // --- Lógica de Notificaciones para Comentarios ---
   
        } else 
        {
            echo json_encode(['success' => false, 'message' => 'Error al guardar el comentario']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}

?>