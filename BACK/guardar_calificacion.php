<?php
require_once 'DB_connection.php'; // Ajusta la ruta si es necesario
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para calificar.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$font_id = isset($_POST['font_id']) ? intval($_POST['font_id']) : 0;
$estrellas = isset($_POST['estrellas']) ? intval($_POST['estrellas']) : 0;

if ($font_id <= 0 || $estrellas < 1 || $estrellas > 5) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos de calificación inválidos.']);
    exit;
}

// Verificar si ya existe una calificación para este usuario y fuente
$sql_check = "SELECT idCalf FROM calificaciones WHERE idUsuario = ? AND idFont = ?";
$stmt_check = mysqli_prepare($conn, $sql_check);
mysqli_stmt_bind_param($stmt_check, "ii", $user_id, $font_id);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);
$existing_rating = mysqli_fetch_assoc($result_check);
mysqli_stmt_close($stmt_check);

if ($existing_rating) {
    // Actualizar calificación existente
    $sql_update = "UPDATE calificaciones SET estrellas = ?, fechaCalificacion = CURRENT_TIMESTAMP WHERE idCalf = ?";
    $stmt_update = mysqli_prepare($conn, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "ii", $estrellas, $existing_rating['idCalf']);
    $success = mysqli_stmt_execute($stmt_update);
    mysqli_stmt_close($stmt_update);
    $action_message = 'Calificación actualizada.';
} else {
    // Insertar nueva calificación
    $sql_insert = "INSERT INTO calificaciones (idUsuario, idFont, estrellas) VALUES (?, ?, ?)";
    $stmt_insert = mysqli_prepare($conn, $sql_insert);
    mysqli_stmt_bind_param($stmt_insert, "iii", $user_id, $font_id, $estrellas);
    $success = mysqli_stmt_execute($stmt_insert);
    mysqli_stmt_close($stmt_insert);
    $action_message = 'Calificación guardada.';
}

if ($success) {
    // Recalcular y obtener el nuevo promedio de estrellas y total de votos
    $sql_new_avg = "SELECT
                        AVG(estrellas) AS nuevoPromedio,
                        COUNT(idCalf) AS nuevoTotalVotos
                    FROM calificaciones
                    WHERE idFont = ?";
    $stmt_avg = mysqli_prepare($conn, $sql_new_avg);
    mysqli_stmt_bind_param($stmt_avg, "i", $font_id);
    mysqli_stmt_execute($stmt_avg);
    $result_avg = mysqli_stmt_get_result($stmt_avg);
    $new_stats = mysqli_fetch_assoc($result_avg);
    mysqli_stmt_close($stmt_avg);

    echo json_encode([
        'success' => true,
        'message' => $action_message,
        'nuevoPromedio' => round($new_stats['nuevoPromedio'] ?? 0, 1),
        'nuevoTotalVotos' => (int)($new_stats['nuevoTotalVotos'] ?? 0),
        'tuCalificacion' => $estrellas // Devuelve la calificación que el usuario acaba de enviar
    ]);
} else {
    http_response_code(500);
    error_log("Error al guardar/actualizar calificación: " . mysqli_error($conn));
    echo json_encode(['success' => false, 'message' => 'Error al procesar la calificación.']);
}

mysqli_close($conn);
?>