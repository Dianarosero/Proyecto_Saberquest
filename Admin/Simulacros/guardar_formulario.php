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

    // Guardar ruta relativa para servir desde web
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
    // Imágenes de preguntas: nombre imagen_pregunta[index][]
    $imagenes_pregunta = $_FILES['imagen_pregunta'] ?? null;
    // Imágenes de opciones
    $img_op_a = $_FILES['imagen_opcion_a'] ?? null;
    $img_op_b = $_FILES['imagen_opcion_b'] ?? null;
    $img_op_c = $_FILES['imagen_opcion_c'] ?? null;
    $img_op_d = $_FILES['imagen_opcion_d'] ?? null;

    $count = count($enunciados);
    for ($i = 0; $i < $count; $i++) {
        $enunciado = trim($enunciados[$i]);
        $op_a = trim($opciones_a[$i]);
        $op_b = trim($opciones_b[$i]);
        $op_c = trim($opciones_c[$i]);
        $op_d = trim($opciones_d[$i]);
        $correcta = $correctas[$i];

        // Validaciones: enunciado y correcta obligatorios
        if (empty($enunciado) || empty($correcta)) {
            continue;
        }

        // Subir imágenes de opciones (una por opción, opcionales)
        $ruta_img_op_a = subirImagenOpcion($img_op_a, $i, 'a');
        $ruta_img_op_b = subirImagenOpcion($img_op_b, $i, 'b');
        $ruta_img_op_c = subirImagenOpcion($img_op_c, $i, 'c');
        $ruta_img_op_d = subirImagenOpcion($img_op_d, $i, 'd');

        // Cada opción debe tener texto o imagen
        $has_a = (strlen($op_a) > 0) || $ruta_img_op_a;
        $has_b = (strlen($op_b) > 0) || $ruta_img_op_b;
        $has_c = (strlen($op_c) > 0) || $ruta_img_op_c;
        $has_d = (strlen($op_d) > 0) || $ruta_img_op_d;
        if (!$has_a || !$has_b || !$has_c || !$has_d) {
            continue;
        }

        // Si una opción tiene imagen, ignorar texto vacío en validación (ya manejado en front), aquí permitimos vacío.
        $opciones_json = json_encode([
            'a' => ['texto' => $op_a, 'imagen' => $ruta_img_op_a],
            'b' => ['texto' => $op_b, 'imagen' => $ruta_img_op_b],
            'c' => ['texto' => $op_c, 'imagen' => $ruta_img_op_c],
            'd' => ['texto' => $op_d, 'imagen' => $ruta_img_op_d],
        ], JSON_UNESCAPED_UNICODE);

        $tipo = 'opcion_multiple';

        // Subir imágenes de la pregunta i (pueden ser múltiples)
        $rutas_imgs_preg = subirImagenesPregunta($imagenes_pregunta, $i);
        $imgs_preg_json = !empty($rutas_imgs_preg) ? json_encode($rutas_imgs_preg, JSON_UNESCAPED_SLASHES) : null;

        // Se agregó columna 'imagen' tipo texto en preguntas (guardamos JSON de rutas o null)
        $stmt_preg = $conex->prepare("INSERT INTO preguntas (formulario_id, tipo, enunciado, opciones, correcta, imagen) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt_preg) {
            $_SESSION['mensaje'] = 'Error en la preparación de pregunta: ' . $conex->error;
            $_SESSION['mensaje_tipo'] = 'error';
            header('Location: create_formulario.php');
            exit();
        }
        $stmt_preg->bind_param("isssss", $formulario_id, $tipo, $enunciado, $opciones_json, $correcta, $imgs_preg_json);
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

// ==================== Helpers ====================
function subirImagenesPregunta($imagenes_pregunta, $indexPregunta) {
    $rutas = [];
    if (!$imagenes_pregunta) return $rutas;
    $carpeta = "../../assets/src_simulacros/img_simulacros/img_preguntas/";
    if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);

    // Estructura esperada: $_FILES['imagen_pregunta']['name'][$indexPregunta][$k]
    if (!isset($imagenes_pregunta['name'][$indexPregunta])) return $rutas;
    $names = $imagenes_pregunta['name'][$indexPregunta];
    $tmps = $imagenes_pregunta['tmp_name'][$indexPregunta];
    $errors = $imagenes_pregunta['error'][$indexPregunta];

    $permitidos = ['jpg','jpeg','png','gif','webp'];
    for ($k = 0; $k < count($names); $k++) {
        if ($errors[$k] !== UPLOAD_ERR_OK) continue;
        $ext = strtolower(pathinfo($names[$k], PATHINFO_EXTENSION));
        if (!in_array($ext, $permitidos)) continue;
        $nombre = uniqid('preg_'.$indexPregunta.'_')."_".basename($names[$k]);
        $dest = $carpeta.$nombre;
        if (move_uploaded_file($tmps[$k], $dest)) {
            $rutas[] = $dest; // puedes convertir a ruta relativa si lo prefieres
        }
    }
    return $rutas;
}

function subirImagenOpcion($filesGroup, $indexPregunta, $letra) {
    // Estructura de $_FILES simple por opción: ['name'][$indexPregunta]
    if (!$filesGroup || !isset($filesGroup['name'][$indexPregunta])) return null;
    if ($filesGroup['error'][$indexPregunta] !== UPLOAD_ERR_OK) return null;
    $ext = strtolower(pathinfo($filesGroup['name'][$indexPregunta], PATHINFO_EXTENSION));
    $permitidos = ['jpg','jpeg','png','gif','webp'];
    if (!in_array($ext, $permitidos)) return null;
    $carpeta = "../../assets/src_simulacros/img_simulacros/img_opciones/";
    if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);
    $nombre = uniqid('op_'.$indexPregunta.'_'.$letra.'_')."_".basename($filesGroup['name'][$indexPregunta]);
    $dest = $carpeta.$nombre;
    if (move_uploaded_file($filesGroup['tmp_name'][$indexPregunta], $dest)) {
        return $dest;
    }
    return null;
}
