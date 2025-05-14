<?php
session_start();
include("../../base de datos/con_db.php");

// Validar que el usuario esté logueado y sea profesor
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'profesor') {
    header('Location: ../index.php');
    exit;
}

// Verificar que se haya enviado el contenido del PDF
if (!isset($_POST['pdf_content']) || !isset($_POST['form_id'])) {
    die('Error: Datos insuficientes para generar el PDF.');
}

$usuario_id = $_SESSION['usuario_id'];
$formulario_id = $_POST['form_id'];
$pdf_content = $_POST['pdf_content'];

// Verificar que el formulario existe y pertenece al profesor
$stmt = $conex->prepare("
    SELECT titulo
    FROM formularios 
    WHERE id = ? AND creador_id = ?
");
$stmt->bind_param("ii", $formulario_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Formulario no encontrado o no tienes permiso para acceder a estos resultados.");
}

$formulario = $result->fetch_assoc();
$stmt->close();

// Generar un nombre de archivo limpio para el PDF
$filename = 'resultados_' . preg_replace('/[^a-zA-Z0-9]/', '_', $formulario['titulo']) . '_' . date('Y-m-d') . '.pdf';

// Configurar las cabeceras para descargar el PDF
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Utilizar una solución de conversión HTML a PDF simple sin frameworks
// Utilizamos dompdf en el servidor pero aquí emulamos la salida para el propósito de este ejemplo
require_once 'html_to_pdf.php';
echo html_to_pdf($pdf_content);
?>