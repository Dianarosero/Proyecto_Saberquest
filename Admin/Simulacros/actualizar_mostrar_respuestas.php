<?php
header('Content-Type: application/json'); // Indicar que la respuesta será en JSON

// Incluir la conexión a la base de datos
include("../../base de datos/con_db.php");

$response = ['success' => false, 'error' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos enviados por AJAX
    $formulario_id = isset($_POST['formulario_id']) ? (int)$_POST['formulario_id'] : 0;
    $mostrar_respuestas = isset($_POST['mostrar_respuestas']) ? (int)$_POST['mostrar_respuestas'] : 0;

    // Validar los datos
    if ($formulario_id <= 0) {
        $response['error'] = 'ID de formulario no válido';
        echo json_encode($response);
        exit;
    }

    if ($mostrar_respuestas !== 0 && $mostrar_respuestas !== 1) {
        $response['error'] = 'Valor de mostrar_respuestas no válido';
        echo json_encode($response);
        exit;
    }

    // Actualizar el valor en la base de datos
    $stmt = $conex->prepare("UPDATE formularios SET mostrar_respuestas = ? WHERE id = ?");
    $stmt->bind_param("ii", $mostrar_respuestas, $formulario_id);

    if ($stmt->execute()) {
        $response['success'] = true;
    } else {
        $response['error'] = 'Error al actualizar en la base de datos: ' . $conex->error;
    }

    $stmt->close();
} else {
    $response['error'] = 'Método no permitido';
}

$conex->close();
echo json_encode($response); // Corregimos esta línea