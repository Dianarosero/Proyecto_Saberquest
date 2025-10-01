<?php
session_start();
include("../../base de datos/con_db.php");

// Validar que el usuario esté logueado y sea profesor
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'Administrador') {
    header('Location: ../../index.php');
    exit;
}

// Utilidad: eliminación segura de archivos dentro de un directorio base
function safe_unlink($path, $baseDir) {
    if (!$path || !$baseDir) return;
    $realBase = realpath($baseDir);
    $realPath = realpath($path);
    if ($realBase && $realPath && is_file($realPath) && strpos($realPath, $realBase) === 0) {
        @unlink($realPath);
    }
}

$formulario_id = $_GET['id'] ?? 0;
$mensaje = '';
$error = '';

// Si no hay ID, redirigir al listado
if ($formulario_id == 0) {
    header("Location: index.php");
    exit;
}

// Obtener datos del formulario
$stmt = $conex->prepare("SELECT titulo, descripcion, imagen, mostrar_respuestas FROM formularios WHERE id = ?");
$stmt->bind_param("i", $formulario_id);
$stmt->execute();
$stmt->bind_result($titulo, $descripcion, $imagen, $mostrar_respuestas);
$formulario_encontrado = $stmt->fetch();
$stmt->close();

if (!$formulario_encontrado) {
    header("Location: index.php?error=simulacro_no_encontrado");
    exit;
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo_nuevo = $_POST['titulo'] ?? '';
    $descripcion_nueva = $_POST['descripcion'] ?? '';
    $imagen_ruta = $imagen; // Mantener la imagen actual por defecto
    $mostrar_respuestas_nuevo = isset($_POST['mostrar_respuestas']) && $_POST['mostrar_respuestas'] == '1' ? 1 : 0; // 1 si está marcado, 0 si no

    // Procesar la imagen si se ha subido una nueva
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
            $error = "Solo se permiten imágenes JPG, JPEG, PNG, GIF o WEBP.";
        } else {
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_archivo)) {
                // Eliminar físicamente la imagen anterior si existía y es diferente
                if (!empty($imagen) && $imagen !== $ruta_archivo) {
                    $base_sim_dir = __DIR__ . "/../../assets/src_simulacros/img_simulacros/";
                    safe_unlink($imagen, $base_sim_dir);
                }
                $imagen_ruta = $ruta_archivo;
            } else {
                $error = "Error al subir la imagen.";
            }
        }
    }

    // Validar datos
    if (empty($titulo_nuevo)) {
        $error = "El título del simulacro no puede estar vacío.";
    } else if (empty($error)) { // Solo proceder si no hay errores
        // Actualizar formulario
        $stmt_update = $conex->prepare("UPDATE formularios SET titulo = ?, descripcion = ?, imagen = ?, mostrar_respuestas = ? WHERE id = ?");
        $stmt_update->bind_param("sssii", $titulo_nuevo, $descripcion_nueva, $imagen_ruta, $mostrar_respuestas_nuevo, $formulario_id);

        if ($stmt_update->execute()) {
            $mensaje = "Formulario actualizado correctamente.";
            $titulo = $titulo_nuevo;
            $descripcion = $descripcion_nueva;
            $imagen = $imagen_ruta;
            $mostrar_respuestas = $mostrar_respuestas_nuevo; // Actualizar el valor local para reflejar el cambio
        } else {
            $error = "Error al actualizar el formulario: " . $conex->error;
        }

        $stmt_update->close();
    }
}

// Obtener las preguntas del formulario
$preguntas = [];
$stmt_preguntas = $conex->prepare("SELECT id, enunciado, opciones, correcta, imagen FROM preguntas WHERE formulario_id = ?");
$stmt_preguntas->bind_param("i", $formulario_id);
$stmt_preguntas->execute();
$result_preguntas = $stmt_preguntas->get_result();

while ($pregunta = $result_preguntas->fetch_assoc()) {
    $preguntas[] = $pregunta;
}
$stmt_preguntas->close();

// Procesar edición de preguntas
if (isset($_POST['editar_pregunta'])) {
    $pregunta_id = intval($_POST['pregunta_id'] ?? 0);
    $enunciado = $_POST['enunciado'] ?? '';
    $opcion_a_texto = $_POST['opcion_a'] ?? '';
    $opcion_b_texto = $_POST['opcion_b'] ?? '';
    $opcion_c_texto = $_POST['opcion_c'] ?? '';
    $opcion_d_texto = $_POST['opcion_d'] ?? '';
    $correcta = $_POST['correcta'] ?? '';

    if (empty($enunciado) || empty($correcta)) {
        $error = "El enunciado y la respuesta correcta son obligatorios.";
    } else {
        // Obtener valores actuales para preservar imágenes si no se reemplazan
        $stmt_curr = $conex->prepare("SELECT opciones, imagen FROM preguntas WHERE id = ? AND formulario_id = ?");
        $stmt_curr->bind_param("ii", $pregunta_id, $formulario_id);
        $stmt_curr->execute();
        $stmt_curr->bind_result($opciones_json_curr, $imagenes_json_curr);
        $stmt_curr->fetch();
        $stmt_curr->close();

        $opciones_curr = json_decode($opciones_json_curr ?? '[]', true) ?: [];
        // Normalizar estructura por opción
        $getOpt = function($arr, $key){
            if (!isset($arr[$key])) return ['texto' => '', 'imagen' => null];
            return is_array($arr[$key]) ? array_merge(['texto'=>'','imagen'=>null], $arr[$key]) : ['texto'=>$arr[$key], 'imagen'=>null];
        };
        $optA = $getOpt($opciones_curr, 'a');
        $optB = $getOpt($opciones_curr, 'b');
        $optC = $getOpt($opciones_curr, 'c');
        $optD = $getOpt($opciones_curr, 'd');

        // Subida/gestión de imágenes de opciones
    $permitidos = ['jpg','jpeg','png','gif','webp'];
    $carpeta_op = "../../assets/src_simulacros/img_simulacros/img_opciones/";
    $base_opciones_dir = __DIR__ . "/../../assets/src_simulacros/img_simulacros/img_opciones/";
        if (!is_dir($carpeta_op)) @mkdir($carpeta_op, 0777, true);

        $procesarOpcion = function($letra, $texto, $opt, $inputName) use ($permitidos, $carpeta_op, $base_opciones_dir) {
            $final = ['texto' => $texto, 'imagen' => $opt['imagen'] ?? null];
            $eliminarFlag = isset($_POST['eliminar_imagen_opcion_'.$letra]);
            // Si se solicita eliminar, borra físicamente y limpia referencia
            if ($eliminarFlag && !empty($final['imagen'])) {
                safe_unlink($final['imagen'], $base_opciones_dir);
                $final['imagen'] = null;
            }
            // Si se sube una nueva imagen, reemplaza y borra la anterior automáticamente
            if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $permitidos)) {
                    $nombre = uniqid('op_edit_'.$letra.'_')."_".basename($_FILES[$inputName]['name']);
                    $dest = $carpeta_op.$nombre;
                    if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $dest)) {
                        // eliminar anterior si existía
                        if (!empty($final['imagen'])) {
                            safe_unlink($final['imagen'], $base_opciones_dir);
                        }
                        $final['imagen'] = $dest;
                    }
                }
            }
            return $final;
        };

        $optA = $procesarOpcion('a', $opcion_a_texto, $optA, 'imagen_opcion_a');
        $optB = $procesarOpcion('b', $opcion_b_texto, $optB, 'imagen_opcion_b');
        $optC = $procesarOpcion('c', $opcion_c_texto, $optC, 'imagen_opcion_c');
        $optD = $procesarOpcion('d', $opcion_d_texto, $optD, 'imagen_opcion_d');

        // Validar que cada opción tenga texto o imagen
        $hasA = (strlen(trim($optA['texto'])) > 0) || !empty($optA['imagen']);
        $hasB = (strlen(trim($optB['texto'])) > 0) || !empty($optB['imagen']);
        $hasC = (strlen(trim($optC['texto'])) > 0) || !empty($optC['imagen']);
        $hasD = (strlen(trim($optD['texto'])) > 0) || !empty($optD['imagen']);
        if (!$hasA || !$hasB || !$hasC || !$hasD) {
            $error = "Cada opción debe tener texto o imagen.";
        } else {
            $opciones = json_encode([
                'a' => $optA,
                'b' => $optB,
                'c' => $optC,
                'd' => $optD
            ], JSON_UNESCAPED_UNICODE);

            // Imágenes de la pregunta (múltiples)
            $imagenes_actuales = json_decode($imagenes_json_curr ?? '[]', true);
            if (!is_array($imagenes_actuales)) $imagenes_actuales = [];

            // Eliminar seleccionadas
            if (isset($_POST['eliminar_imagen_pregunta']) && is_array($_POST['eliminar_imagen_pregunta'])) {
                $a_eliminar = $_POST['eliminar_imagen_pregunta']; // valores: rutas
                $base_preg_dir = __DIR__ . "/../../assets/src_simulacros/img_simulacros/img_preguntas/";
                foreach ($imagenes_actuales as $ruta) {
                    if (in_array($ruta, $a_eliminar, true)) {
                        safe_unlink($ruta, $base_preg_dir);
                    }
                }
                $imagenes_actuales = array_values(array_filter($imagenes_actuales, function($ruta) use ($a_eliminar){
                    return !in_array($ruta, $a_eliminar, true);
                }));
            }

            // Agregar nuevas
            $carpeta_preg = "../../assets/src_simulacros/img_simulacros/img_preguntas/";
            if (!is_dir($carpeta_preg)) @mkdir($carpeta_preg, 0777, true);
            if (isset($_FILES['nuevas_imagenes_pregunta']) && isset($_FILES['nuevas_imagenes_pregunta']['name']) && is_array($_FILES['nuevas_imagenes_pregunta']['name'])) {
                $names = $_FILES['nuevas_imagenes_pregunta']['name'];
                $tmps = $_FILES['nuevas_imagenes_pregunta']['tmp_name'];
                $errs = $_FILES['nuevas_imagenes_pregunta']['error'];
                for ($k = 0; $k < count($names); $k++) {
                    if ($errs[$k] !== UPLOAD_ERR_OK) continue;
                    $ext = strtolower(pathinfo($names[$k], PATHINFO_EXTENSION));
                    if (!in_array($ext, $permitidos)) continue;
                    $nombre = uniqid('preg_edit_')."_".basename($names[$k]);
                    $dest = $carpeta_preg.$nombre;
                    if (move_uploaded_file($tmps[$k], $dest)) {
                        $imagenes_actuales[] = $dest;
                    }
                }
            }

            $imagenes_json_nuevo = !empty($imagenes_actuales) ? json_encode($imagenes_actuales, JSON_UNESCAPED_SLASHES) : null;

            $stmt_update_pregunta = $conex->prepare("UPDATE preguntas SET enunciado = ?, opciones = ?, correcta = ?, imagen = ? WHERE id = ? AND formulario_id = ?");
            $stmt_update_pregunta->bind_param("ssssii", $enunciado, $opciones, $correcta, $imagenes_json_nuevo, $pregunta_id, $formulario_id);

            if ($stmt_update_pregunta->execute()) {
                $mensaje = "Pregunta actualizada correctamente.";

                // Actualizar la lista de preguntas
                $stmt_preguntas = $conex->prepare("SELECT id, enunciado, opciones, correcta, imagen FROM preguntas WHERE formulario_id = ?");
                $stmt_preguntas->bind_param("i", $formulario_id);
                $stmt_preguntas->execute();
                $result_preguntas = $stmt_preguntas->get_result();

                $preguntas = [];
                while ($pregunta = $result_preguntas->fetch_assoc()) {
                    $preguntas[] = $pregunta;
                }
                $stmt_preguntas->close();
            } else {
                $error = "Error al actualizar la pregunta: " . $conex->error;
            }

            $stmt_update_pregunta->close();
        }
    }
}

// Procesar nueva pregunta
if (isset($_POST['agregar_pregunta'])) {
    $enunciado = $_POST['nuevo_enunciado'] ?? '';
    $opcion_a_texto = $_POST['nueva_opcion_a'] ?? '';
    $opcion_b_texto = $_POST['nueva_opcion_b'] ?? '';
    $opcion_c_texto = $_POST['nueva_opcion_c'] ?? '';
    $opcion_d_texto = $_POST['nueva_opcion_d'] ?? '';
    $correcta = $_POST['nueva_correcta'] ?? '';

    if (empty($enunciado) || empty($correcta)) {
        $error = "El enunciado y la respuesta correcta son obligatorios.";
    } else {
        $permitidos = ['jpg','jpeg','png','gif','webp'];

        // Subir imágenes de opciones (una por opción, opcionales)
        $carpeta_op = "../../assets/src_simulacros/img_simulacros/img_opciones/";
        if (!is_dir($carpeta_op)) @mkdir($carpeta_op, 0777, true);
        $subirOpcion = function($texto, $inputName) use ($permitidos, $carpeta_op) {
            $imgRuta = null;
            if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $permitidos)) {
                    $nombre = uniqid('op_new_')."_".basename($_FILES[$inputName]['name']);
                    $dest = $carpeta_op.$nombre;
                    if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $dest)) {
                        $imgRuta = $dest;
                    }
                }
            }
            return ['texto' => $texto, 'imagen' => $imgRuta];
        };

        $optA = $subirOpcion($opcion_a_texto, 'nueva_imagen_opcion_a');
        $optB = $subirOpcion($opcion_b_texto, 'nueva_imagen_opcion_b');
        $optC = $subirOpcion($opcion_c_texto, 'nueva_imagen_opcion_c');
        $optD = $subirOpcion($opcion_d_texto, 'nueva_imagen_opcion_d');

        $hasA = (strlen(trim($optA['texto'])) > 0) || !empty($optA['imagen']);
        $hasB = (strlen(trim($optB['texto'])) > 0) || !empty($optB['imagen']);
        $hasC = (strlen(trim($optC['texto'])) > 0) || !empty($optC['imagen']);
        $hasD = (strlen(trim($optD['texto'])) > 0) || !empty($optD['imagen']);
        if (!$hasA || !$hasB || !$hasC || !$hasD) {
            $error = "Cada opción debe tener texto o imagen.";
        } else {
            $opciones = json_encode([
                'a' => $optA,
                'b' => $optB,
                'c' => $optC,
                'd' => $optD
            ], JSON_UNESCAPED_UNICODE);

            // Subir imágenes múltiples de la pregunta
            $imagenes_pregunta_json = null;
            if (isset($_FILES['imagen_pregunta_nueva']) && isset($_FILES['imagen_pregunta_nueva']['name']) && is_array($_FILES['imagen_pregunta_nueva']['name'])) {
                $carpeta_preg = "../../assets/src_simulacros/img_simulacros/img_preguntas/";
                if (!is_dir($carpeta_preg)) @mkdir($carpeta_preg, 0777, true);
                $names = $_FILES['imagen_pregunta_nueva']['name'];
                $tmps = $_FILES['imagen_pregunta_nueva']['tmp_name'];
                $errs = $_FILES['imagen_pregunta_nueva']['error'];
                $rutas = [];
                for ($k = 0; $k < count($names); $k++) {
                    if ($errs[$k] !== UPLOAD_ERR_OK) continue;
                    $ext = strtolower(pathinfo($names[$k], PATHINFO_EXTENSION));
                    if (!in_array($ext, $permitidos)) continue;
                    $nombre = uniqid('preg_new_')."_".basename($names[$k]);
                    $dest = $carpeta_preg.$nombre;
                    if (move_uploaded_file($tmps[$k], $dest)) {
                        $rutas[] = $dest;
                    }
                }
                if (!empty($rutas)) {
                    $imagenes_pregunta_json = json_encode($rutas, JSON_UNESCAPED_SLASHES);
                }
            }

            $tipo = 'opcion_multiple';

            $stmt_insert_pregunta = $conex->prepare("INSERT INTO preguntas (formulario_id, tipo, enunciado, opciones, correcta, imagen) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_insert_pregunta->bind_param("isssss", $formulario_id, $tipo, $enunciado, $opciones, $correcta, $imagenes_pregunta_json);

            if ($stmt_insert_pregunta->execute()) {
                $mensaje = "Pregunta agregada correctamente.";

                // Actualizar la lista de preguntas
                $stmt_preguntas = $conex->prepare("SELECT id, enunciado, opciones, correcta, imagen FROM preguntas WHERE formulario_id = ?");
                $stmt_preguntas->bind_param("i", $formulario_id);
                $stmt_preguntas->execute();
                $result_preguntas = $stmt_preguntas->get_result();

                $preguntas = [];
                while ($pregunta = $result_preguntas->fetch_assoc()) {
                    $preguntas[] = $pregunta;
                }
                $stmt_preguntas->close();
            } else {
                $error = "Error al agregar la pregunta: " . $conex->error;
            }

            $stmt_insert_pregunta->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Simulacro</title>
    <link href="../../assets/img/favicon.png" rel="icon">
    <link href="../../assets/img/favicon.png" rel="apple-touch-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
    :root {
        --primary: #003366;
        --primary-light: #0056b3;
        --secondary: #B22222;
        --secondary-light: #d93636;
        --accent: #FFD700;
        --accent-light: #FFE44D;
        --background: #FFFFFF;
        --text: #333333;
        --text-light: #666666;
        --neutral: #E0E0E0;
        --neutral-light: #F7F7F7;
        --success: #27ae60;
        --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
        --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
        --border-radius: 12px;
        --transition: all 0.3s ease;
        --gap: 1.5rem;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Montserrat', sans-serif;
        background: var(--neutral-light);
        color: var(--text);
        position: relative;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        line-height: 1.6;
    }

    .bg-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        filter: blur(8px);
        opacity: 0.12;
        z-index: -1;
        background-image: url('../../assets/src_simulacros/img_simulacros/predeterminadas/predeterminada2.png');
    }

    .header {
        background-color: var(--primary);
        color: white;
        padding: 1.2rem 12rem;
        width: 100%;
        box-shadow: var(--shadow-md);
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        z-index: 100;
        transition: var(--transition);
    }

    .header:hover {
        box-shadow: var(--shadow-lg);
    }

    .university-logo {
        font-size: 1.5rem;
        font-weight: 700;
        display: flex;
        align-items: center;
    }

    .university-logo i {
        margin-right: 10px;
        color: var(--accent);
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.72rem 1.1rem;
        border-radius: 10px;
        border: 1px solid transparent;
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: transform .18s ease, box-shadow .18s ease, background .18s ease, color .18s ease, border-color .18s ease;
        text-decoration: none;
        gap: 10px;
        line-height: 1.1;
        box-shadow: 0 2px 6px rgba(0, 0, 0, .04);
    }

    .btn:focus-visible {
        outline: none;
        box-shadow: 0 0 0 3px rgba(0, 102, 204, .15), 0 2px 6px rgba(0, 0, 0, .06);
    }

    .btn:active {
        transform: translateY(0);
        box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
    }

    .btn[disabled],
    .btn:disabled {
        opacity: .7;
        cursor: not-allowed;
        filter: grayscale(.1);
    }

    .btn-primary {
        color: #fff;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        border-color: rgba(0, 51, 102, .15);
    }

    .btn-primary:hover {
        transform: translateY(-1.5px);
        box-shadow: 0 6px 16px rgba(0, 51, 102, .25);
        filter: brightness(1.03);
    }

    .btn-success {
        color: #fff;
        background: linear-gradient(135deg, var(--success) 0%, #219653 100%);
        border-color: rgba(39, 174, 96, .2);
    }

    .btn-success:hover {
        transform: translateY(-1.5px);
        box-shadow: 0 6px 16px rgba(39, 174, 96, .25);
        filter: brightness(1.03);
    }

    .btn-secondary {
        background: #f6f8fb;
        color: var(--text);
        border-color: #dfe6ef;
    }

    .btn-secondary:hover {
        background: #eef2f7;
        transform: translateY(-1.5px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, .08);
    }

    .btn-outline {
        background-color: transparent;
        color: var(--primary);
        border-color: rgba(0, 51, 102, .35);
    }

    .btn-outline:hover {
        background: rgba(0, 51, 102, .08);
        transform: translateY(-1.5px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, .08);
    }

    .btn-danger {
        color: #fff;
        background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-light) 100%);
        border-color: rgba(178, 34, 34, .25);
    }

    .btn-danger:hover {
        transform: translateY(-1.5px);
        box-shadow: 0 6px 16px rgba(178, 34, 34, .25);
        filter: brightness(1.03);
    }

    .contenedor {
        max-width: 900px;
        width: 100%;
        margin: 30px auto;
        background: var(--background);
        padding: 40px;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-md);
        transition: var(--transition);
        flex: 1;
    }

    .contenedor:hover {
        box-shadow: var(--shadow-lg);
    }

    .form-header {
        margin-bottom: 2rem;
        border-bottom: 1px solid var(--neutral);
        padding-bottom: 1.5rem;
    }

    .title-actions {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        margin-bottom: 1rem;
        gap: 15px;
    }

    .form-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    h2 {
        color: var(--primary);
        margin-bottom: 1rem;
        font-size: 2rem;
        position: relative;
        display: inline-block;
    }

    h2::after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 0;
        width: 40%;
        height: 3px;
        background-color: #B22222;
        border-radius: 3px;
    }

    .form-description {
        color: var(--text-light);
        font-size: 1.1rem;
        margin-top: 1rem;
        margin-bottom: 0.5rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--primary);
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        font-size: 1rem;
        border: 1px solid var(--neutral);
        border-radius: 8px;
        transition: var(--transition);
        font-family: 'Montserrat', sans-serif;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-light);
        box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
    }

    .form-control-file {
        padding: 10px 0;
    }

    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }

    .image-upload-container {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .current-image {
        padding: 15px;
        border: 1px solid var(--neutral);
        border-radius: var(--border-radius);
        background-color: var(--neutral-light);
    }

    .current-image p {
        margin-bottom: 10px;
        font-weight: 600;
        color: var(--text-light);
    }

    .img-preview {
        max-width: 100%;
        max-height: 200px;
        border-radius: 8px;
        box-shadow: var(--shadow-sm);
    }

    .file-input-wrapper {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .accordion {
        margin-top: 2rem;
        border: 1px solid var(--neutral);
        border-radius: var(--border-radius);
        overflow: hidden;
    }

    .accordion-item {
        border-bottom: 1px solid var(--neutral);
    }

    .accordion-item:last-child {
        border-bottom: none;
    }

    .accordion-header {
        background: var(--neutral-light);
        padding: 15px 20px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 600;
        transition: var(--transition);
    }

    .accordion-header:hover {
        background-color: #e9e9e9;
    }

    .accordion-header.active {
        background-color: var(--primary);
        color: white;
    }

    .accordion-body {
        padding: 0;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
    }

    .accordion-body.active {
        padding: 20px;
        /* max-height is set dynamically via JS to fit content */
    }

    .option-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-bottom: 15px;
    }

    .radio-group {
        display: flex;
        gap: 20px;
        margin-top: 10px;
    }

    .radio-option {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .radio-option input[type="radio"] {
        margin: 0;
    }

    .section-title {
        margin: 2rem 0 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid var(--neutral);
    }

    .actions {
        display: flex;
        justify-content: space-between;
        margin-top: 2rem;
    }

    .form-actions-bottom {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin-top: 2.5rem;
        margin-bottom: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--neutral);
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 2rem;
        color: var(--primary);
        text-decoration: none;
        font-weight: 600;
        transition: var(--transition);
    }

    .back-link:hover {
        color: var(--primary-light);
        transform: translateX(-5px);
    }

    .footer {
        margin-top: auto;
        background-color: var(--primary);
        color: white;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
    }

    .footer a {
        color: var(--accent);
        text-decoration: none;
        transition: var(--transition);
    }

    .footer a:hover {
        color: var(--accent-light);
        text-decoration: underline;
    }

    .alerta {
        padding: 15px;
        border-radius: var(--border-radius);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        position: relative;
        animation: slideDown 0.3s ease-out forwards;
    }

    @keyframes slideDown {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .alerta i:first-child {
        margin-right: 10px;
        font-size: 1.2rem;
    }

    .alerta-exito {
        background-color: rgba(39, 174, 96, 0.1);
        border: 1px solid var(--success);
        color: var(--success);
    }

    .alerta-error {
        background-color: rgba(178, 34, 34, 0.1);
        border: 1px solid var(--secondary);
        color: var(--secondary);
    }

    .cerrar-alerta {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: inherit;
        opacity: 0.7;
        transition: var(--transition);
    }

    .cerrar-alerta:hover {
        opacity: 1;
    }

    .card {
        background: var(--neutral-light);
        border-radius: var(--border-radius);
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
    }

    .card:hover {
        box-shadow: var(--shadow-md);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .card-title {
        font-weight: 600;
        font-size: 1.1rem;
        color: var(--primary);
    }

    .card-actions {
        display: flex;
        gap: 10px;
    }

    .btn-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--background);
        border: 1px solid var(--neutral);
        color: var(--text-light);
        transition: var(--transition);
        cursor: pointer;
    }

    .btn-icon:hover {
        transform: translateY(-2px);
    }

    .btn-icon.edit {
        color: var(--primary);
        border-color: rgba(0, 51, 102, 0.3);
    }

    .btn-icon.edit:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .btn-icon.delete {
        color: var(--secondary);
        border-color: rgba(178, 34, 34, 0.3);
    }

    .btn-icon.delete:hover {
        background: var(--secondary);
        color: white;
        border-color: var(--secondary);
    }

    @media (max-width: 768px) {
        .header {
            padding: 1rem;
        }

        .university-logo {
            font-size: 1.2rem;
        }

        .contenedor {
            margin: 15px;
            padding: 25px;
            border-radius: 10px;
        }

        h2 {
            font-size: 1.6rem;
        }

        .option-grid {
            grid-template-columns: 1fr;
        }

        .radio-group {
            flex-direction: column;
            gap: 10px;
        }

        .actions {
            flex-direction: column;
            gap: 15px;
        }

        .actions .btn {
            width: 100%;
        }
    }

    .nav-controls {
        display: flex;
        align-items: center;
        justify-content: center;
        /* Centra el contenido */
    }

    .nav-list {
        display: flex;
        gap: 30px;
    }

    .nav-link {
        font-size: 1rem;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.9);
        padding-bottom: 5px;
        position: relative;
        transition: color 0.3s ease;
    }

    .nav-link:hover {
        color: #FFFFFF;
        /* Color más brillante al hacer hover */
    }

    .nav-link::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 2px;
        background-color: #FFFFFF;
        /* Blanco, como en la imagen */
        transition: width 0.3s ease;
    }

    .nav-link:hover::after {
        width: 100%;
    }

    a {
        text-decoration: none;
        color: inherit;
    }

    .form-group label[for="mostrar_respuestas"] {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 1rem;
        color: var(--text);
        cursor: pointer;
    }

    .form-group input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: var(--primary);
        /* Color del checkbox cuando está marcado */
    }

    .form-group input[type="checkbox"]:focus {
        outline: 2px solid var(--primary);
        outline-offset: 2px;
    }

    .swal2-title::after {
        content: none !important;
    }

    /* File input enhanced UI */
    .hidden-input {
        display: none !important;
    }

    .file-input-row {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 6px;
    }

    .file-name {
        font-size: 0.9rem;
        color: var(--text-light);
        background: #f6f8fb;
        border: 1px solid #e3eaf3;
        padding: 6px 10px;
        border-radius: 8px;
        max-width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Previews for newly selected images */
    .preview-multiple,
    .preview-single {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 6px;
    }

    .preview-multiple img,
    .preview-single img {
        height: 80px;
        border-radius: 6px;
        box-shadow: var(--shadow-sm);
        cursor: pointer;
        background: #fff;
        border: 1px solid var(--neutral);
        padding: 2px;
    }

    /* Simple Lightbox */
    .lightbox-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.85);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 10000;
    }

    .lightbox-overlay.active {
        display: flex;
    }

    .lightbox-content {
        position: relative;
        max-width: 92vw;
        max-height: 92vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .lightbox-image {
        max-width: 90vw;
        max-height: 85vh;
        border-radius: 10px;
        box-shadow: var(--shadow-lg);
    }

    .lightbox-btn {
        position: absolute;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.5);
        color: #fff;
        border: none;
        cursor: pointer;
        transition: background 0.2s ease, transform 0.2s ease;
        backdrop-filter: blur(2px);
    }

    .lightbox-btn:hover {
        background: rgba(0, 0, 0, 0.65);
        transform: translateY(-1px);
    }

    .lightbox-close {
        top: -10px;
        right: -10px;
        font-size: 22px;
        line-height: 1;
    }

    .lightbox-prev {
        left: -56px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 24px;
    }

    .lightbox-next {
        right: -56px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 24px;
    }
    </style>

    <script>
    // JavaScript para manejar los acordeones
    document.addEventListener('DOMContentLoaded', function() {
        // Función para manejar la apertura/cierre de los acordeones con altura dinámica
        function handleAccordion() {
            const headers = document.querySelectorAll('.accordion-header');

            headers.forEach(header => {
                header.addEventListener('click', function() {
                    const body = this.nextElementSibling;
                    const isOpen = body.classList.contains('active');
                    if (isOpen) {
                        // Cerrar
                        body.style.maxHeight = '0px';
                        body.classList.remove('active');
                        this.classList.remove('active');
                    } else {
                        // Abrir
                        body.classList.add('active');
                        this.classList.add('active');
                        body.style.maxHeight = body.scrollHeight + 'px';
                    }
                });
            });

            // Recalcular altura al redimensionar
            window.addEventListener('resize', function() {
                document.querySelectorAll('.accordion-body.active').forEach(b => {
                    b.style.maxHeight = b.scrollHeight + 'px';
                });
            });
        }

        // Función para cerrar las alertas
        function setupAlertClosing() {
            const closeButtons = document.querySelectorAll('.cerrar-alerta');

            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    this.parentElement.style.display = 'none';
                });
            });
        }

        // Inicializar las funciones
        handleAccordion();
        setupAlertClosing();
    });
    </script>
</head>

<body>
    <div class="bg-container"></div>

    <header class="header">
        <div class="logo-space">
            <img width="120" height="50" fill="none" src="../../assets/img/Logo_fondoazul.png" alt="" srcset="">
        </div>
        <div class="nav-controls">
            <nav class="nav">
                <div class="nav-list">
                    <a class="nav-link" href="../index_admin.php#projects">Inicio</a>
                </div>
            </nav>
        </div>
    </header>

    <div class="contenedor">
        <?php if (!empty($mensaje)): ?>
        <div class="alerta alerta-exito">
            <i class="fas fa-check-circle"></i>
            <span><?php echo $mensaje; ?></span>
            <button type="button" class="cerrar-alerta">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
        <div class="alerta alerta-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $error; ?></span>
            <button type="button" class="cerrar-alerta">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php endif; ?>

        <div class="form-header">
            <div class="title-actions">
                <h2>Editar Simulacro</h2>
                <div class="form-actions">
                    <a href="ver_formulario.php?id=<?php echo $formulario_id; ?>" class="btn btn-primary">
                        <i class="fas fa-eye"></i> Ver Simulacro
                    </a>
                </div>
            </div>
            <p class="form-description">Modifica el título, descripción, imagen y las preguntas del simulacro.</p>
        </div>

        <!-- Formulario para editar los datos generales -->
        <form method="post" action="" id="form-datos-generales" enctype="multipart/form-data">
            <div class="form-group">
                <label for="titulo">Título del simulacro</label>
                <input type="text" class="form-control" id="titulo" name="titulo"
                    value="<?php echo htmlspecialchars($titulo); ?>" required>
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion"
                    rows="4"><?php echo htmlspecialchars($descripcion); ?></textarea>
            </div>

            <div class="form-group">
                <label for="mostrar_respuestas">
                    <input type="checkbox" id="mostrar_respuestas" name="mostrar_respuestas" value="1"
                        <?php echo $mostrar_respuestas == 1 ? 'checked' : ''; ?>>
                    Mostrar resultados estudiante
                </label>
            </div>

            <div class="form-group">
                <label for="imagen">Imagen</label>
                <div class="image-upload-container">
                    <?php if (!empty($imagen)): ?>
                    <div class="current-image">
                        <p>Imagen actual:</p>
                        <img src="<?php echo htmlspecialchars($imagen); ?>" alt="Imagen actual" class="img-preview">
                    </div>
                    <?php endif; ?>
                    <div class="file-input-wrapper">
                        <input type="file" class="form-control-file hidden-input" id="imagen" name="imagen"
                            accept="image/*">
                        <div class="file-input-row">
                            <button type="button" class="btn btn-secondary btn-upload-file" data-target="imagen"><i
                                    class="fas fa-upload"></i> Subir imagen</button>
                            <button type="button" class="btn btn-outline btn-clear-file" data-target="imagen"
                                aria-label="Limpiar selección"><i class="fas fa-times"></i></button>
                            <span class="file-name" data-for="imagen">Ningún archivo seleccionado</span>
                        </div>
                        <small class="form-text text-muted">Selecciona una imagen para usarla como fondo del simulacro.
                            Formatos permitidos: JPG, JPEG, PNG, GIF, WEBP.</small>
                    </div>
                </div>
            </div>

            <h3 class="section-title">Preguntas del simulacro</h3>

            <!-- Lista de preguntas existentes -->
            <div class="accordion">
                <?php foreach ($preguntas as $index => $pregunta):
                    $opciones_raw = json_decode($pregunta['opciones'], true) ?: [];
                    $norm = function($opt){ return is_array($opt) ? array_merge(['texto'=>'','imagen'=>null], $opt) : ['texto'=>($opt ?? ''), 'imagen'=>null]; };
                    $optA = isset($opciones_raw['a']) ? $norm($opciones_raw['a']) : ['texto'=>'','imagen'=>null];
                    $optB = isset($opciones_raw['b']) ? $norm($opciones_raw['b']) : ['texto'=>'','imagen'=>null];
                    $optC = isset($opciones_raw['c']) ? $norm($opciones_raw['c']) : ['texto'=>'','imagen'=>null];
                    $optD = isset($opciones_raw['d']) ? $norm($opciones_raw['d']) : ['texto'=>'','imagen'=>null];
                    $imgs_preg = [];
                    if (!empty($pregunta['imagen'])) {
                        $decoded = json_decode($pregunta['imagen'], true);
                        if (is_array($decoded)) { $imgs_preg = $decoded; }
                    }
                ?>
                <div class="accordion-item">
                    <div class="accordion-header">
                        <span><?php echo ($index + 1) . '. ' . htmlspecialchars(substr($pregunta['enunciado'], 0, 60)) . (strlen($pregunta['enunciado']) > 60 ? '...' : ''); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="accordion-body">
                        <form method="post" action="" enctype="multipart/form-data">
                            <input type="hidden" name="pregunta_id" value="<?php echo $pregunta['id']; ?>">

                            <div class="form-group">
                                <label for="enunciado_<?php echo $pregunta['id']; ?>">Enunciado de la pregunta</label>
                                <textarea class="form-control" id="enunciado_<?php echo $pregunta['id']; ?>"
                                    name="enunciado" rows="2"
                                    required><?php echo htmlspecialchars($pregunta['enunciado']); ?></textarea>
                            </div>

                            <?php if (!empty($imgs_preg)): ?>
                            <div class="form-group">
                                <label>Imágenes actuales de la pregunta</label>
                                <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-start;">
                                    <?php foreach ($imgs_preg as $idx => $ruta): ?>
                                    <div
                                        style="border:1px solid var(--neutral);background:#fff;border-radius:8px;padding:8px;">
                                        <img src="<?php echo htmlspecialchars($ruta); ?>" alt="Imagen pregunta"
                                            class="img-preview lightbox-img"
                                            data-lightbox-group="preg_<?php echo $pregunta['id']; ?>"
                                            data-lightbox-index="<?php echo $idx; ?>"
                                            style="max-height:120px;display:block;cursor:zoom-in;">
                                        <label
                                            style="display:flex;align-items:center;gap:6px;margin-top:6px;font-size:.9rem;color:#b22222;">
                                            <input type="checkbox" name="eliminar_imagen_pregunta[]"
                                                value="<?php echo htmlspecialchars($ruta); ?>"> Eliminar
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="form-group">
                                <label for="nuevas_imagenes_pregunta_<?php echo $pregunta['id']; ?>">Agregar imágenes a
                                    la pregunta</label>
                                <input type="file" class="form-control-file hidden-input"
                                    id="nuevas_imagenes_pregunta_<?php echo $pregunta['id']; ?>"
                                    name="nuevas_imagenes_pregunta[]" accept="image/*" multiple>
                                <div class="file-input-row">
                                    <button type="button" class="btn btn-secondary btn-upload-file"
                                        data-target="nuevas_imagenes_pregunta_<?php echo $pregunta['id']; ?>"><i
                                            class="fas fa-upload"></i> Subir imágenes</button>
                                    <button type="button" class="btn btn-outline btn-clear-file"
                                        data-target="nuevas_imagenes_pregunta_<?php echo $pregunta['id']; ?>"
                                        aria-label="Limpiar selección"><i class="fas fa-times"></i></button>
                                    <span class="file-name"
                                        data-for="nuevas_imagenes_pregunta_<?php echo $pregunta['id']; ?>">Ningún
                                        archivo seleccionado</span>
                                </div>
                                <div class="preview-multiple"
                                    data-preview-for="nuevas_imagenes_pregunta_<?php echo $pregunta['id']; ?>"></div>
                            </div>

                            <div class="option-grid">
                                <div class="form-group">
                                    <label for="opcion_a_<?php echo $pregunta['id']; ?>">Opción A</label>
                                    <input type="text" class="form-control" id="opcion_a_<?php echo $pregunta['id']; ?>"
                                        name="opcion_a" value="<?php echo htmlspecialchars($optA['texto']); ?>">
                                    <?php if (!empty($optA['imagen'])): ?>
                                    <div class="current-image" style="margin-top:8px;">
                                        <p>Imagen actual:</p>
                                        <img src="<?php echo htmlspecialchars($optA['imagen']); ?>" alt="Opción A"
                                            class="img-preview lightbox-img"
                                            data-lightbox-group="op_<?php echo $pregunta['id']; ?>_a"
                                            style="cursor:zoom-in;">
                                        <label
                                            style="display:flex;align-items:center;gap:6px;margin-top:6px;font-size:.9rem;color:#b22222;">
                                            <input type="checkbox" name="eliminar_imagen_opcion_a" value="1"> Eliminar
                                            imagen
                                        </label>
                                    </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control-file hidden-input"
                                        id="imagen_opcion_a_<?php echo $pregunta['id']; ?>" name="imagen_opcion_a"
                                        accept="image/*">
                                    <div class="file-input-row">
                                        <button type="button" class="btn btn-secondary btn-upload-file"
                                            data-target="imagen_opcion_a_<?php echo $pregunta['id']; ?>"><i
                                                class="fas fa-upload"></i> Subir imagen</button>
                                        <button type="button" class="btn btn-outline btn-clear-file"
                                            data-target="imagen_opcion_a_<?php echo $pregunta['id']; ?>"
                                            aria-label="Limpiar selección"><i class="fas fa-times"></i></button>
                                        <span class="file-name"
                                            data-for="imagen_opcion_a_<?php echo $pregunta['id']; ?>">Ningún archivo
                                            seleccionado</span>
                                    </div>
                                    <div class="preview-single" data-preview-for="imagen_opcion_a"></div>
                                </div>

                                <div class="form-group">
                                    <label for="opcion_b_<?php echo $pregunta['id']; ?>">Opción B</label>
                                    <input type="text" class="form-control" id="opcion_b_<?php echo $pregunta['id']; ?>"
                                        name="opcion_b" value="<?php echo htmlspecialchars($optB['texto']); ?>">
                                    <?php if (!empty($optB['imagen'])): ?>
                                    <div class="current-image" style="margin-top:8px;">
                                        <p>Imagen actual:</p>
                                        <img src="<?php echo htmlspecialchars($optB['imagen']); ?>" alt="Opción B"
                                            class="img-preview lightbox-img"
                                            data-lightbox-group="op_<?php echo $pregunta['id']; ?>_b"
                                            style="cursor:zoom-in;">
                                        <label
                                            style="display:flex;align-items:center;gap:6px;margin-top:6px;font-size:.9rem;color:#b22222;">
                                            <input type="checkbox" name="eliminar_imagen_opcion_b" value="1"> Eliminar
                                            imagen
                                        </label>
                                    </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control-file hidden-input"
                                        id="imagen_opcion_b_<?php echo $pregunta['id']; ?>" name="imagen_opcion_b"
                                        accept="image/*">
                                    <div class="file-input-row">
                                        <button type="button" class="btn btn-secondary btn-upload-file"
                                            data-target="imagen_opcion_b_<?php echo $pregunta['id']; ?>"><i
                                                class="fas fa-upload"></i> Subir imagen</button>
                                        <button type="button" class="btn btn-outline btn-clear-file"
                                            data-target="imagen_opcion_b_<?php echo $pregunta['id']; ?>"
                                            aria-label="Limpiar selección"><i class="fas fa-times"></i></button>
                                        <span class="file-name"
                                            data-for="imagen_opcion_b_<?php echo $pregunta['id']; ?>">Ningún archivo
                                            seleccionado</span>
                                    </div>
                                    <div class="preview-single" data-preview-for="imagen_opcion_b"></div>
                                </div>

                                <div class="form-group">
                                    <label for="opcion_c_<?php echo $pregunta['id']; ?>">Opción C</label>
                                    <input type="text" class="form-control" id="opcion_c_<?php echo $pregunta['id']; ?>"
                                        name="opcion_c" value="<?php echo htmlspecialchars($optC['texto']); ?>">
                                    <?php if (!empty($optC['imagen'])): ?>
                                    <div class="current-image" style="margin-top:8px;">
                                        <p>Imagen actual:</p>
                                        <img src="<?php echo htmlspecialchars($optC['imagen']); ?>" alt="Opción C"
                                            class="img-preview lightbox-img"
                                            data-lightbox-group="op_<?php echo $pregunta['id']; ?>_c"
                                            style="cursor:zoom-in;">
                                        <label
                                            style="display:flex;align-items:center;gap:6px;margin-top:6px;font-size:.9rem;color:#b22222;">
                                            <input type="checkbox" name="eliminar_imagen_opcion_c" value="1"> Eliminar
                                            imagen
                                        </label>
                                    </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control-file hidden-input"
                                        id="imagen_opcion_c_<?php echo $pregunta['id']; ?>" name="imagen_opcion_c"
                                        accept="image/*">
                                    <div class="file-input-row">
                                        <button type="button" class="btn btn-secondary btn-upload-file"
                                            data-target="imagen_opcion_c_<?php echo $pregunta['id']; ?>"><i
                                                class="fas fa-upload"></i> Subir imagen</button>
                                        <button type="button" class="btn btn-outline btn-clear-file"
                                            data-target="imagen_opcion_c_<?php echo $pregunta['id']; ?>"
                                            aria-label="Limpiar selección"><i class="fas fa-times"></i></button>
                                        <span class="file-name"
                                            data-for="imagen_opcion_c_<?php echo $pregunta['id']; ?>">Ningún archivo
                                            seleccionado</span>
                                    </div>
                                    <div class="preview-single" data-preview-for="imagen_opcion_c"></div>
                                </div>

                                <div class="form-group">
                                    <label for="opcion_d_<?php echo $pregunta['id']; ?>">Opción D</label>
                                    <input type="text" class="form-control" id="opcion_d_<?php echo $pregunta['id']; ?>"
                                        name="opcion_d" value="<?php echo htmlspecialchars($optD['texto']); ?>">
                                    <?php if (!empty($optD['imagen'])): ?>
                                    <div class="current-image" style="margin-top:8px;">
                                        <p>Imagen actual:</p>
                                        <img src="<?php echo htmlspecialchars($optD['imagen']); ?>" alt="Opción D"
                                            class="img-preview lightbox-img"
                                            data-lightbox-group="op_<?php echo $pregunta['id']; ?>_d"
                                            style="cursor:zoom-in;">
                                        <label
                                            style="display:flex;align-items:center;gap:6px;margin-top:6px;font-size:.9rem;color:#b22222;">
                                            <input type="checkbox" name="eliminar_imagen_opcion_d" value="1"> Eliminar
                                            imagen
                                        </label>
                                    </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control-file hidden-input"
                                        id="imagen_opcion_d_<?php echo $pregunta['id']; ?>" name="imagen_opcion_d"
                                        accept="image/*">
                                    <div class="file-input-row">
                                        <button type="button" class="btn btn-secondary btn-upload-file"
                                            data-target="imagen_opcion_d_<?php echo $pregunta['id']; ?>"><i
                                                class="fas fa-upload"></i> Subir imagen</button>
                                        <button type="button" class="btn btn-outline btn-clear-file"
                                            data-target="imagen_opcion_d_<?php echo $pregunta['id']; ?>"
                                            aria-label="Limpiar selección"><i class="fas fa-times"></i></button>
                                        <span class="file-name"
                                            data-for="imagen_opcion_d_<?php echo $pregunta['id']; ?>">Ningún archivo
                                            seleccionado</span>
                                    </div>
                                    <div class="preview-single" data-preview-for="imagen_opcion_d"></div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Respuesta correcta</label>
                                <div class="radio-group">
                                    <div class="radio-option">
                                        <input type="radio" id="correcta_a_<?php echo $pregunta['id']; ?>"
                                            name="correcta" value="a"
                                            <?php if ($pregunta['correcta'] == 'a') echo 'checked'; ?> required>
                                        <label for="correcta_a_<?php echo $pregunta['id']; ?>">A</label>
                                    </div>

                                    <div class="radio-option">
                                        <input type="radio" id="correcta_b_<?php echo $pregunta['id']; ?>"
                                            name="correcta" value="b"
                                            <?php if ($pregunta['correcta'] == 'b') echo 'checked'; ?>>
                                        <label for="correcta_b_<?php echo $pregunta['id']; ?>">B</label>
                                    </div>

                                    <div class="radio-option">
                                        <input type="radio" id="correcta_c_<?php echo $pregunta['id']; ?>"
                                            name="correcta" value="c"
                                            <?php if ($pregunta['correcta'] == 'c') echo 'checked'; ?>>
                                        <label for="correcta_c_<?php echo $pregunta['id']; ?>">C</label>
                                    </div>

                                    <div class="radio-option">
                                        <input type="radio" id="correcta_d_<?php echo $pregunta['id']; ?>"
                                            name="correcta" value="d"
                                            <?php if ($pregunta['correcta'] == 'd') echo 'checked'; ?>>
                                        <label for="correcta_d_<?php echo $pregunta['id']; ?>">D</label>
                                    </div>
                                </div>
                            </div>

                            <div class="actions">
                                <button type="submit" name="editar_pregunta" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Guardar Pregunta
                                </button>

                                <a href="eliminar_pregunta.php?id=<?php echo $pregunta['id']; ?>&formulario_id=<?php echo $formulario_id; ?>"
                                    class="btn btn-danger link-eliminar-pregunta">
                                    <i class="fas fa-trash-alt"></i> Eliminar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Formulario para agregar nueva pregunta -->
            <h3 class="section-title">Añadir nueva pregunta</h3>
            <div class="card">
                <form method="post" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="nuevo_enunciado">Enunciado de la pregunta</label>
                        <textarea class="form-control" id="nuevo_enunciado" name="nuevo_enunciado" rows="2"
                            required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="imagen_pregunta_nueva">Imágenes de la pregunta (opcional, múltiples)</label>
                        <input type="file" class="form-control-file hidden-input" id="imagen_pregunta_nueva"
                            name="imagen_pregunta_nueva[]" accept="image/*" multiple>
                        <div class="file-input-row">
                            <button type="button" class="btn btn-secondary btn-upload-file"
                                data-target="imagen_pregunta_nueva"><i class="fas fa-upload"></i> Subir
                                imágenes</button>
                            <button type="button" class="btn btn-outline btn-clear-file"
                                data-target="imagen_pregunta_nueva" aria-label="Limpiar selección"><i
                                    class="fas fa-times"></i></button>
                            <span class="file-name" data-for="imagen_pregunta_nueva">Ningún archivo seleccionado</span>
                        </div>
                        <div class="preview-multiple" id="preview_preg_nueva"></div>
                    </div>

                    <div class="option-grid">
                        <div class="form-group">
                            <label for="nueva_opcion_a">Opción A</label>
                            <input type="text" class="form-control" id="nueva_opcion_a" name="nueva_opcion_a" required>
                            <input type="file" class="form-control-file hidden-input" id="nueva_imagen_opcion_a"
                                name="nueva_imagen_opcion_a" accept="image/*">
                            <div class="file-input-row">
                                <button type="button" class="btn btn-secondary btn-upload-file"
                                    data-target="nueva_imagen_opcion_a"><i class="fas fa-upload"></i> Subir
                                    imagen</button>
                                <button type="button" class="btn btn-outline btn-clear-file"
                                    data-target="nueva_imagen_opcion_a" aria-label="Limpiar selección"><i
                                        class="fas fa-times"></i></button>
                                <span class="file-name" data-for="nueva_imagen_opcion_a">Ningún archivo
                                    seleccionado</span>
                            </div>
                            <div class="preview-single" id="preview_op_nueva_a"></div>
                        </div>

                        <div class="form-group">
                            <label for="nueva_opcion_b">Opción B</label>
                            <input type="text" class="form-control" id="nueva_opcion_b" name="nueva_opcion_b" required>
                            <input type="file" class="form-control-file hidden-input" id="nueva_imagen_opcion_b"
                                name="nueva_imagen_opcion_b" accept="image/*">
                            <div class="file-input-row">
                                <button type="button" class="btn btn-secondary btn-upload-file"
                                    data-target="nueva_imagen_opcion_b"><i class="fas fa-upload"></i> Subir
                                    imagen</button>
                                <button type="button" class="btn btn-outline btn-clear-file"
                                    data-target="nueva_imagen_opcion_b" aria-label="Limpiar selección"><i
                                        class="fas fa-times"></i></button>
                                <span class="file-name" data-for="nueva_imagen_opcion_b">Ningún archivo
                                    seleccionado</span>
                            </div>
                            <div class="preview-single" id="preview_op_nueva_b"></div>
                        </div>

                        <div class="form-group">
                            <label for="nueva_opcion_c">Opción C</label>
                            <input type="text" class="form-control" id="nueva_opcion_c" name="nueva_opcion_c" required>
                            <input type="file" class="form-control-file hidden-input" id="nueva_imagen_opcion_c"
                                name="nueva_imagen_opcion_c" accept="image/*">
                            <div class="file-input-row">
                                <button type="button" class="btn btn-secondary btn-upload-file"
                                    data-target="nueva_imagen_opcion_c"><i class="fas fa-upload"></i> Subir
                                    imagen</button>
                                <button type="button" class="btn btn-outline btn-clear-file"
                                    data-target="nueva_imagen_opcion_c" aria-label="Limpiar selección"><i
                                        class="fas fa-times"></i></button>
                                <span class="file-name" data-for="nueva_imagen_opcion_c">Ningún archivo
                                    seleccionado</span>
                            </div>
                            <div class="preview-single" id="preview_op_nueva_c"></div>
                        </div>

                        <div class="form-group">
                            <label for="nueva_opcion_d">Opción D</label>
                            <input type="text" class="form-control" id="nueva_opcion_d" name="nueva_opcion_d" required>
                            <input type="file" class="form-control-file hidden-input" id="nueva_imagen_opcion_d"
                                name="nueva_imagen_opcion_d" accept="image/*">
                            <div class="file-input-row">
                                <button type="button" class="btn btn-secondary btn-upload-file"
                                    data-target="nueva_imagen_opcion_d"><i class="fas fa-upload"></i> Subir
                                    imagen</button>
                                <button type="button" class="btn btn-outline btn-clear-file"
                                    data-target="nueva_imagen_opcion_d" aria-label="Limpiar selección"><i
                                        class="fas fa-times"></i></button>
                                <span class="file-name" data-for="nueva_imagen_opcion_d">Ningún archivo
                                    seleccionado</span>
                            </div>
                            <div class="preview-single" id="preview_op_nueva_d"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Respuesta correcta</label>
                        <div class="radio-group">
                            <div class="radio-option">
                                <input type="radio" id="nueva_correcta_a" name="nueva_correcta" value="a" required>
                                <label for="nueva_correcta_a">A</label>
                            </div>

                            <div class="radio-option">
                                <input type="radio" id="nueva_correcta_b" name="nueva_correcta" value="b">
                                <label for="nueva_correcta_b">B</label>
                            </div>

                            <div class="radio-option">
                                <input type="radio" id="nueva_correcta_c" name="nueva_correcta" value="c">
                                <label for="nueva_correcta_c">C</label>
                            </div>

                            <div class="radio-option">
                                <input type="radio" id="nueva_correcta_d" name="nueva_correcta" value="d">
                                <label for="nueva_correcta_d">D</label>
                            </div>
                        </div>
                    </div>

                    <div class="actions">
                        <button type="submit" name="agregar_pregunta" class="btn btn-success">
                            <i class="fas fa-plus"></i> Añadir Pregunta
                        </button>
                    </div>
                </form>
            </div>

            <!-- Botones de guardar cambios y cancelar -->
            <div class="form-actions-bottom">
                <a href="ver_formulario.php?id=<?php echo $formulario_id; ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" form="form-datos-generales" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>

            <a href="ver_formulario.php?id=<?php echo $formulario_id; ?>" class="back-link">
                <i class="fas fa-arrow-left"></i> Volver al simulacro
            </a>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> SABERQUEST - Todos los derechos reservados</p>
    </footer>
    <script>
    // Interceptar eliminación de pregunta para usar SweetAlert2
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('a.link-eliminar-pregunta').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const href = this.getAttribute('href');
                if (window.Swal) {
                    Swal.fire({
                        title: '¿Eliminar pregunta?',
                        text: 'Esta acción no se puede deshacer.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((res) => {
                        if (res.isConfirmed) window.location.href = href;
                    });
                } else {
                    if (confirm('¿Estás seguro de que deseas eliminar esta pregunta?')) window
                        .location.href = href;
                }
            });
        });
    });
    </script>

    <!-- Lightbox básico local -->
    <div class="lightbox-overlay" id="lightbox">
        <div class="lightbox-content">
            <button class="lightbox-btn lightbox-close" id="lightboxClose" aria-label="Cerrar">&times;</button>
            <button class="lightbox-btn lightbox-prev" id="lightboxPrev" aria-label="Anterior">
                <i class="fas fa-chevron-left"></i>
            </button>
            <img id="lightboxImg" class="lightbox-image" alt="preview" />
            <button class="lightbox-btn lightbox-next" id="lightboxNext" aria-label="Siguiente">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>

    <script>
    // Previsualización de inputs de imagen y lightbox local
    document.addEventListener('DOMContentLoaded', function() {
        // Helper para renderizar previews
        function renderPreview(container, files, multiple = false) {
            container.innerHTML = '';
            const list = Array.from(files).slice(0, multiple ? 12 : 1); // límite defensivo
            list.forEach(file => {
                if (!file.type || !file.type.startsWith('image/')) return;
                const img = document.createElement('img');
                img.alt = file.name;
                img.loading = 'lazy';
                const reader = new FileReader();
                reader.onload = e => {
                    img.src = e.target.result;
                    img.className = 'preview-img lightbox-img';
                };
                reader.readAsDataURL(file);
                container.appendChild(img);
            });
            // Si está dentro de un acordeón abierto, recalcula altura
            const body = container.closest('.accordion-body.active');
            if (body) {
                // Pequeño delay para asegurar render
                setTimeout(() => {
                    body.style.maxHeight = body.scrollHeight + 'px';
                }, 0);
            }
        }

        // Vincular previews para inputs de preguntas existentes (múltiple)
        document.querySelectorAll('input[type="file"][id^="nuevas_imagenes_pregunta_"]').forEach(input => {
            const preview = document.querySelector('.preview-multiple[data-preview-for="' + input.id +
                '"]');
            if (!preview) return;
            input.addEventListener('change', () => renderPreview(preview, input.files, true));
        });

        // Vincular previews para opciones de pregunta (single)
        ['imagen_opcion_a', 'imagen_opcion_b', 'imagen_opcion_c', 'imagen_opcion_d'].forEach(name => {
            document.querySelectorAll('input[type="file"][name="' + name + '"]').forEach(input => {
                const preview = input.parentElement.querySelector(
                    '.preview-single[data-preview-for="' + name + '"]');
                if (!preview) return;
                input.addEventListener('change', () => renderPreview(preview, input.files,
                    false));
            });
        });

        // Previews para formulario de nueva pregunta
        const inputPregNueva = document.getElementById('imagen_pregunta_nueva');
        const prevPregNueva = document.getElementById('preview_preg_nueva');
        if (inputPregNueva && prevPregNueva) {
            inputPregNueva.addEventListener('change', () => renderPreview(prevPregNueva, inputPregNueva.files,
                true));
        }
        const mapNew = [
            ['nueva_imagen_opcion_a', 'preview_op_nueva_a'],
            ['nueva_imagen_opcion_b', 'preview_op_nueva_b'],
            ['nueva_imagen_opcion_c', 'preview_op_nueva_c'],
            ['nueva_imagen_opcion_d', 'preview_op_nueva_d']
        ];
        mapNew.forEach(([name, previewId]) => {
            const input = document.querySelector('input[type="file"][name="' + name + '"]');
            const preview = document.getElementById(previewId);
            if (input && preview) {
                input.addEventListener('change', () => renderPreview(preview, input.files, false));
            }
        });

        // Lightbox simple
        const overlay = document.getElementById('lightbox');
        const imgEl = document.getElementById('lightboxImg');
        const btnClose = document.getElementById('lightboxClose');
        const btnPrev = document.getElementById('lightboxPrev');
        const btnNext = document.getElementById('lightboxNext');

        let currentGroup = [];
        let currentIndex = 0;

        function openLightbox(group, index) {
            currentGroup = group;
            currentIndex = index;
            updateLightboxImage();
            overlay.classList.add('active');
        }

        function updateLightboxImage() {
            if (!currentGroup.length) return;
            imgEl.src = currentGroup[currentIndex].src;
        }

        function closeLightbox() {
            overlay.classList.remove('active');
        }

        function nav(delta) {
            if (!currentGroup.length) return;
            currentIndex = (currentIndex + delta + currentGroup.length) % currentGroup.length;
            updateLightboxImage();
        }

        // Clicks en imágenes existentes y previews con clase .lightbox-img
        function collectGroup(selectorValue) {
            return Array.from(document.querySelectorAll('.lightbox-img' + selectorValue));
        }

        document.body.addEventListener('click', function(e) {
            const target = e.target;
            if (!(target instanceof Element)) return;
            if (target.classList.contains('lightbox-img')) {
                // Agrupar por data-lightbox-group si existe
                const groupName = target.getAttribute('data-lightbox-group');
                let groupNodes;
                if (groupName) {
                    groupNodes = Array.from(document.querySelectorAll(
                        '.lightbox-img[data-lightbox-group="' + groupName + '"]'));
                } else {
                    // Fallback: solo la imagen actual
                    groupNodes = [target];
                }
                const idx = groupNodes.indexOf(target);
                openLightbox(groupNodes, Math.max(0, idx));
            }
        });

        btnClose.addEventListener('click', closeLightbox);
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closeLightbox();
        });
        document.addEventListener('keydown', function(e) {
            if (!overlay.classList.contains('active')) return;
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft') nav(-1);
            if (e.key === 'ArrowRight') nav(1);
        });
        btnPrev.addEventListener('click', () => nav(-1));
        btnNext.addEventListener('click', () => nav(1));

        // Botones de subir/limpiar archivos y actualización de etiquetas de nombre
        function bindFileUI() {
            // Abrir selector
            document.querySelectorAll('.btn-upload-file').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.getAttribute('data-target');
                    const input = document.getElementById(id);
                    if (input) input.click();
                });
            });

            // Botones de subir/limpiar archivos y actualización de etiquetas de nombre

            // Limpiar selección
            document.querySelectorAll('.btn-clear-file').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.getAttribute('data-target');
                    const input = document.getElementById(id);
                    const nameChip = document.querySelector('.file-name[data-for="' + id +
                    '"]');

                    // Vaciar input
                    if (input) {
                        input.value = '';
                        try {
                            input.dispatchEvent(new Event('change', {
                                bubbles: true
                            }));
                        } catch (e) {}
                    }

                    // Resetear etiqueta de archivo
                    if (nameChip) nameChip.textContent = 'Ningún archivo seleccionado';

                    // Limpiar previews dentro del mismo bloque (form-group)
                    const group = btn.closest('.form-group') || (input ? input.closest(
                        '.form-group') : null);
                    if (group) {
                        group.querySelectorAll('.preview-single, .preview-multiple').forEach(
                            p => p.innerHTML = '');
                    }

                    // Ajustar altura del acordeón si está abierto
                    const body = btn.closest('.accordion-body.active') || (input ? input
                        .closest('.accordion-body.active') : null);
                    if (body) setTimeout(() => {
                        body.style.maxHeight = body.scrollHeight + 'px';
                    }, 0);
                });
            });

            // Actualizar etiqueta con nombre de archivo(s)
            document.querySelectorAll('input[type="file"]').forEach(input => {
                input.addEventListener('change', () => {
                    const id = input.id;
                    if (!id) return; // algunos inputs de opciones se seleccionan por name
                    const chip = document.querySelector('.file-name[data-for="' + id + '"]');
                    if (!chip) return;
                    const files = Array.from(input.files || []);
                    if (files.length === 0) {
                        chip.textContent = 'Ningún archivo seleccionado';
                        // Limpiar previews dentro del mismo form-group
                        const group = input.closest('.form-group');
                        if (group) {
                            group.querySelectorAll('.preview-single, .preview-multiple')
                                .forEach(p => p.innerHTML = '');
                        }
                        const body = input.closest('.accordion-body.active');
                        if (body) setTimeout(() => {
                            body.style.maxHeight = body.scrollHeight + 'px';
                        }, 0);
                    } else if (files.length === 1) {
                        chip.textContent = files[0].name;
                    } else {
                        chip.textContent = files.length + ' archivos seleccionados';
                    }
                });
            });

            // Al marcar "Eliminar imagen" de una opción, limpiar también preview de nueva selección
            document.querySelectorAll('input[type="checkbox"][name^="eliminar_imagen_opcion_"]').forEach(
                chk => {
                    chk.addEventListener('change', () => {
                        const group = chk.closest('.form-group');
                        if (group) {
                            group.querySelectorAll('.preview-single').forEach(p => p.innerHTML =
                                '');
                            const fileInput = group.querySelector('input[type="file"]');
                            if (fileInput) {
                                fileInput.value = '';
                                const chip = document.querySelector('.file-name[data-for="' +
                                    fileInput.id + '"]');
                                if (chip) chip.textContent = 'Ningún archivo seleccionado';
                            }
                            const body = chk.closest('.accordion-body.active');
                            if (body) setTimeout(() => {
                                body.style.maxHeight = body.scrollHeight + 'px';
                            }, 0);
                        }
                    });
                });
        }

        bindFileUI();
    });
    </script>
</body>

</html>