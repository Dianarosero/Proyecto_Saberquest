<?php
session_start();
include("base de datos/con_db.php");

// 1. Guardar encabezado del formulario
$titulo = $_POST['titulo'];
$descripcion = $_POST['descripcion'];

$stmt = $conex->prepare("INSERT INTO formularios (titulo, descripcion) VALUES (?, ?)");
if (!$stmt) {
    die("Error en la preparación: " . $conex->error);
}
$stmt->bind_param('ss', $titulo, $descripcion);
if (!$stmt->execute()) {
    die("Error al guardar el formulario: " . $stmt->error);
}
$formulario_id = $conex->insert_id;
$stmt->close();

// 2. Procesar preguntas
if (
    isset($_POST['enunciado']) &&
    isset($_POST['opcion_a']) &&
    isset($_POST['opcion_b']) &&
    isset($_POST['opcion_c']) &&
    isset($_POST['opcion_d']) &&
    isset($_POST['correcta'])
) {
    $enunciados = $_POST['enunciado'];
    $opciones_a = $_POST['opcion_a'];
    $opciones_b = $_POST['opcion_b'];
    $opciones_c = $_POST['opcion_c'];
    $opciones_d = $_POST['opcion_d'];
    $correctas  = $_POST['correcta'];

    for ($i = 0; $i < count($enunciados); $i++) {
        $enunciado = $enunciados[$i];
        $opciones = json_encode([
            'a' => $opciones_a[$i],
            'b' => $opciones_b[$i],
            'c' => $opciones_c[$i],
            'd' => $opciones_d[$i]
        ]);
        $tipo = 'opcion_multiple';
        $correcta = $correctas[$i]; // 'a', 'b', 'c' o 'd'

        $stmt_preg = $conex->prepare("INSERT INTO preguntas (formulario_id, tipo, enunciado, opciones, correcta) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt_preg) {
            die("Error en la preparación de pregunta: " . $conex->error);
        }
        $stmt_preg->bind_param("issss", $formulario_id, $tipo, $enunciado, $opciones, $correcta);
        if (!$stmt_preg->execute()) {
            die("Error al guardar la pregunta: " . $stmt_preg->error);
        }
        $stmt_preg->close();
    }
}

header("Location: index.php?exito=1");
exit();
?>
