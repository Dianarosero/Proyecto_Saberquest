<?php
session_start();
include("../../base de datos/con_db.php");

// 1. Validar y obtener datos del formulario
$titulo = trim($_POST['titulo'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

// Validación básica
if (empty($titulo) || empty($descripcion)) {
    die("Título y descripción son obligatorios.");
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
        die("Solo se permiten imágenes JPG, JPEG, PNG, GIF o WEBP.");
    }

    if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_archivo)) {
        die("Error al subir la imagen.");
    }

    $imagen_ruta = $ruta_archivo;
}



// 3. Guardar encabezado del formulario
$stmt = $conex->prepare("INSERT INTO formularios (titulo, descripcion, imagen) VALUES (?, ?, ?)");
if (!$stmt) {
    die("Error en la preparación: " . $conex->error);
}
$stmt->bind_param('sss', $titulo, $descripcion, $imagen_ruta);
if (!$stmt->execute()) {
    die("Error al guardar el formulario: " . $stmt->error);
}
$formulario_id = $conex->insert_id;
$stmt->close();

// 4. Procesar preguntas
if (
    isset($_POST['enunciado'], $_POST['option_a'], $_POST['option_b'], $_POST['option_c'], $_POST['option_d'], $_POST['correcta']) &&
    is_array($_POST['enunciado'])
) {
    $enunciados = $_POST['enunciado'];
    $opciones_a = $_POST['option_a']; // <-- aquí
    $opciones_b = $_POST['option_b'];
    $opciones_c = $_POST['option_c'];
    $opciones_d = $_POST['option_d'];
    $correctas  = $_POST['correcta'];

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
            die("Error en la preparación de pregunta: " . $conex->error);
        }
        $stmt_preg->bind_param("issss", $formulario_id, $tipo, $enunciado, $opciones_json, $correcta);
        if (!$stmt_preg->execute()) {
            die("Error al guardar la pregunta: " . $stmt_preg->error);
        }
        $stmt_preg->close();
    }
}

// 5. Redirigir con mensaje de éxito
session_start();
$_SESSION['mensaje'] = 'Formulario guardado exitosamente';
$_SESSION['mensaje_tipo'] = 'success'; // o 'error', 'warning', etc.
header('Location: create_formulario.php');
exit();
?>