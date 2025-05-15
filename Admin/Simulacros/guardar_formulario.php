<?php
session_start();
include("../../base de datos/con_db.php");

// 1. Validar y obtener datos del formulario
$titulo = trim($_POST['titulo'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$mostrar_resultados = isset($_POST['mostrar_resultados']) ? 1 : 0; // 1 si está marcado, 0 si no

// Validación básica
if (empty($titulo) || empty($descripcion)) {
    $_SESSION['mensaje'] = 'Título y descripción son obligatorios.';
    $_SESSION['mensaje_tipo'] = 'success';
    header('Location: create_formulario.php');
    exit();
}

$imagen_ruta = '';

if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $carpeta_destino = "../../assets/src_simulacros/img_simulacros/";
    if (!is_dir($carpeta_destino)) {
        mkdir($carpeta_destino, 0777, true);
    }
    $nombre_archivo = uniqid() . "_" . basename($_FILES['imagen']['name']);
    $ruta_archivo = $carpeta_destino . $nombre_archivo;
    $tipo_archivo = strtolower(pathinfo($ruta_archivo, PATHINFO_EXTENSION));

    $tipos_permitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($tipo_archivo, $tipos_permitidos)) {
        $_SESSION['mensaje'] = 'Solo se permiten imágenes JPG, JPEG, PNG, GIF o WEBP.';
        $_SESSION['mensaje_tipo'] = 'error';
        header('Location: create_formulario.php');
        exit();
    }

    if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_archivo)) {
        $_SESSION['mensaje'] = 'Error al subir la imagen.';
        $_SESSION['mensaje_tipo'] = 'error';
        header('Location: create_formulario.php');
        exit();
    }

    $imagen_ruta = $ruta_archivo;
}

// 2. Guardar encabezado del formulario
$stmt = $conex->prepare("INSERT INTO formularios (titulo, descripcion, imagen, mostrar_respuestas) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    $_SESSION['mensaje'] = 'Error en la preparación: ' . $conex->error;
    $_SESSION['mensaje_tipo'] = 'error';
    header('Location: create_formulario.php');
    exit();
}
$stmt->bind_param('sssi', $titulo, $descripcion, $imagen_ruta, $mostrar_resultados);
if (!$stmt->execute()) {
    $_SESSION['mensaje'] = 'Error al guardar el simulacro: ' . $stmt->error;
    $_SESSION['mensaje_tipo'] = 'error';
    header('Location: create_formulario.php');
    exit();
}
$formulario_id = $conex->insert_id;
$stmt->close();

// 3. Procesar preguntas
if (
    isset($_POST['enunciado'], $_POST['option_a'], $_POST['option_b'], $_POST['option_c'], $_POST['option_d'], $_POST['correcta']) &&
    is_array($_POST['enunciado'])
) {
    $enunciados = $_POST['enunciado'];
    $opciones_a = $_POST['option_a'];
    $opciones_b = $_POST['option_b'];
    $opciones_c = $_POST['option_c'];
    $opciones_d = $_POST['option_d'];
    $correctas = $_POST['correcta'];

    $count = count($enunciados);
    for ($i = 0; $i < $count; $i++) {
        $enunciado = trim($enunciados[$i]);
        $op_a = trim($opciones_a[$i]);
        $op_b = trim($opciones_b[$i]);
        $op_c = trim($opciones_c[$i]);
        $op_d = trim($opciones_d[$i]);
        $correcta = $correctas[$i];

        if (empty($enunciado) || empty($op_a) || empty($op_b) || empty($op_c) || empty($op_d) || empty($correcta)) {
            continue;
        }

        $opciones_json = json_encode([
            'a' => $op_a,
            'b' => $op_b,
            'c' => $op_c,
            'd' => $op_d
        ]);

        $tipo = 'opcion_multiple';

        $stmt_preg = $conex->prepare("INSERT INTO preguntas (formulario_id, tipo, enunciado, opciones, correcta) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt_preg) {
            $_SESSION['mensaje'] = 'Error en la preparación de pregunta: ' . $conex->error;
            $_SESSION['mensaje_tipo'] = 'error';
            header('Location: create_formulario.php');
            exit();
        }
        $stmt_preg->bind_param("issss", $formulario_id, $tipo, $enunciado, $opciones_json, $correcta);
        if (!$stmt_preg->execute()) {
            $_SESSION['mensaje'] = 'Error al guardar la pregunta: ' . $stmt_preg->error;
            $_SESSION['mensaje_tipo'] = 'error';
            header('Location: create_formulario.php');
            exit();
        }
        $stmt_preg->close();
    }
}

// 4. Redirigir con mensaje de éxito
$_SESSION['mensaje'] = 'Simulacro guardado exitosamente';
$_SESSION['mensaje_tipo'] = 'success';
header('Location: create_formulario.php');
exit();
