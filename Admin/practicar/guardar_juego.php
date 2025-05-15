<?php
session_start();
include("../../base de datos/con_db.php");

// Validar que el usuario esté logueado y sea profesor
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'Administrador') {
    header('Location: ../../index.php');
    exit;
}

$conexion = $conex;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['gameName'] ?? '');
    $descripcion = trim($_POST['gameDescription'] ?? '');
    $url = trim($_POST['gameUrl'] ?? '');

    if (empty($nombre) || empty($descripcion) || empty($url) || !isset($_FILES['gameImage'])) {
        $_SESSION['error_message'] = "Por favor, completa todos los campos obligatorios.";
        header("Location: crear_juegos.php");
        exit();
    }

    $imagen = $_FILES['gameImage'];

    if ($imagen['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error_message'] = "Error al subir la imagen.";
        header("Location: crear_juegos.php");
        exit();
    }

    $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($imagen['type'], $tiposPermitidos)) {
        $_SESSION['error_message'] = "Formato de imagen no permitido. Usa JPG, PNG, GIF o WEBP.";
        header("Location: crear_juegos.php");
        exit();
    }

    $carpetaUploads = '../../assets/src_juegos/img/';
    if (!is_dir($carpetaUploads)) {
        mkdir($carpetaUploads, 0755, true);
    }

    $extension = pathinfo($imagen['name'], PATHINFO_EXTENSION);
    $nombreImagen = uniqid('img_') . '.' . $extension;
    $rutaFinal = $carpetaUploads . $nombreImagen;

    if (!move_uploaded_file($imagen['tmp_name'], $rutaFinal)) {
        $_SESSION['error_message'] = "No se pudo guardar la imagen.";
        header("Location: crear_juegos.php");
        exit();
    }

    $fechaRegistro = date('Y-m-d H:i:s');

    $sql = "INSERT INTO juegos (nombre, descripcion, imagen, url, fecha_registro) VALUES (?, ?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        $_SESSION['error_message'] = "Error en la preparación de la consulta: " . $conexion->error;
        header("Location: crear_juegos.php");
        exit();
    }

    $stmt->bind_param("sssss", $nombre, $descripcion, $rutaFinal, $url, $fechaRegistro);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Juego creado correctamente.";
        header("Location: crear_juegos.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error al guardar el juego: " . $stmt->error;
        header("Location: crear_juegos.php");
        exit();
    }

    $stmt->close();

} else {
    header("Location: crear_juegos.php");
    exit();
}

$conexion->close();
?>
