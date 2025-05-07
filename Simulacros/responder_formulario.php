<?php
session_start();
include("base de datos/con_db.php");

$formulario_id = $_GET['id'] ?? 0;

// Obtener datos del formulario
$stmt = $conex->prepare("SELECT titulo, descripcion FROM formularios WHERE id = ?");
$stmt->bind_param("i", $formulario_id);
$stmt->execute();
$stmt->bind_result($titulo, $descripcion);
$stmt->fetch();
$stmt->close();

// Obtener preguntas
$stmt = $conex->prepare("SELECT id, enunciado, opciones FROM preguntas WHERE formulario_id = ?");
$stmt->bind_param("i", $formulario_id);
$stmt->execute();
$result = $stmt->get_result();

$preguntas = [];
while ($row = $result->fetch_assoc()) {
    $row['opciones'] = json_decode($row['opciones'], true);
    $preguntas[] = $row;
}
$stmt->close();

// Procesar envío de respuestas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aciertos = 0;
    foreach ($preguntas as $pregunta) {
        $pid = $pregunta['id'];
        $respuesta_usuario = $_POST["respuesta_$pid"] ?? '';
        // Guardar respuesta en la base de datos (opcional)
        $stmt = $conex->prepare("INSERT INTO respuestas (formulario_id, pregunta_id, respuesta) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $formulario_id, $pid, $respuesta_usuario);
        $stmt->execute();
        $stmt->close();

        // Validar respuesta
        $stmt = $conex->prepare("SELECT correcta FROM preguntas WHERE id = ?");
        $stmt->bind_param("i", $pid);
        $stmt->execute();
        $stmt->bind_result($correcta);
        $stmt->fetch();
        $stmt->close();
        if ($respuesta_usuario == $correcta) {
            $aciertos++;
        }
    }
    $total = count($preguntas);
    echo "<div style='max-width:600px;margin:40px auto;background:#eafaf1;padding:20px;border-radius:8px;text-align:center;'>
            <h2>¡Formulario enviado!</h2>
            <p>Respuestas correctas: <b>$aciertos</b> de <b>$total</b></p>
            <a href='formularios.php'>Volver a formularios</a>
          </div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Responder formulario</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7f7; }
        .contenedor { max-width: 700px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px #ccc; }
        .pregunta { margin-bottom: 20px; }
        .opcion { margin-left: 20px; }
        .enviar-btn { background: #27ae60; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        .enviar-btn:hover { background: #1e874b; }
    </style>
</head>
<body>
    <div class="contenedor">
        <h2><?php echo htmlspecialchars($titulo); ?></h2>
        <p><?php echo nl2br(htmlspecialchars($descripcion)); ?></p>
        <form method="post">
            <?php $num = 1; foreach ($preguntas as $pregunta): ?>
                <div class="pregunta">
                    <strong><?php echo $num++; ?>. <?php echo htmlspecialchars($pregunta['enunciado']); ?></strong><br>
                    <?php foreach (['a','b','c','d'] as $letra): ?>
                        <label class="opcion">
                            <input type="radio" name="respuesta_<?php echo $pregunta['id']; ?>" value="<?php echo $letra; ?>" required>
                            <?php echo "$letra) " . htmlspecialchars($pregunta['opciones'][$letra]); ?>
                        </label><br>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
            <button type="submit" class="enviar-btn">Enviar respuestas</button>
        </form>
    </div>
</body>
</html>
