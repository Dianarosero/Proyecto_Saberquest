<?php
include("base de datos/con_db.php");
$formulario_id = $_GET['id'] ?? 0;

// Obtener datos del formulario
$stmt = $conex->prepare("SELECT titulo, descripcion FROM formularios WHERE id = ?");
$stmt->bind_param("i", $formulario_id);
$stmt->execute();
$stmt->bind_result($titulo, $descripcion);
$stmt->fetch();
$stmt->close();

// Obtener preguntas del formulario
$stmt = $conex->prepare("SELECT id, enunciado, opciones, correcta FROM preguntas WHERE formulario_id = ?");
$stmt->bind_param("i", $formulario_id);
$stmt->execute();
$result = $stmt->get_result();

$preguntas = [];
while ($row = $result->fetch_assoc()) {
    $row['opciones'] = json_decode($row['opciones'], true);
    $preguntas[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados del formulario</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7f7; }
        .contenedor { max-width: 700px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px #ccc; }
        .pregunta { margin-bottom: 25px; }
        .opcion { margin-left: 20px; }
        .barra { display: inline-block; height: 18px; background: #3498db; color: #fff; border-radius: 4px; padding: 0 6px; min-width: 30px; }
        .correcta { color: #27ae60; font-weight: bold; }
        .sin-respuestas { color: #e67e22; font-style: italic; }
    </style>
</head>
<body>
    <div class="contenedor">
        <h2>Resultados: <?php echo htmlspecialchars($titulo); ?></h2>
        <p><?php echo nl2br(htmlspecialchars($descripcion)); ?></p>
        <hr>
        <?php
        if (empty($preguntas)) {
            echo "<p class='sin-respuestas'>Este formulario no tiene preguntas.</p>";
        } else {
            foreach ($preguntas as $num => $pregunta):
                // Inicializar conteo de respuestas
                $respuestas = ['a' => 0, 'b' => 0, 'c' => 0, 'd' => 0];
                $stmt = $conex->prepare("SELECT respuesta, COUNT(*) as total FROM respuestas WHERE pregunta_id = ? GROUP BY respuesta");
                $stmt->bind_param("i", $pregunta['id']);
                $stmt->execute();
                $res = $stmt->get_result();
                $total_respuestas = 0;
                while ($row = $res->fetch_assoc()) {
                    $letra = $row['respuesta'];
                    if (isset($respuestas[$letra])) {
                        $respuestas[$letra] = $row['total'];
                        $total_respuestas += $row['total'];
                    }
                }
                $stmt->close();
        ?>
            <div class="pregunta">
                <strong><?php echo ($num+1) . ". " . htmlspecialchars($pregunta['enunciado']); ?></strong><br>
                <?php
                foreach (['a','b','c','d'] as $letra):
                    $cantidad = $respuestas[$letra];
                    $porcentaje = $total_respuestas > 0 ? round(($cantidad / $total_respuestas) * 100) : 0;
                ?>
                    <div class="opcion<?php if($pregunta['correcta'] == $letra) echo ' correcta'; ?>">
                        <?php echo "$letra) " . htmlspecialchars($pregunta['opciones'][$letra]); ?>
                        <span class="barra" style="width:<?php echo max(30, $porcentaje * 2); ?>px;">
                            <?php echo $cantidad; ?> (<?php echo $porcentaje; ?>%)
                        </span>
                        <?php if($pregunta['correcta'] == $letra) echo " ← Correcta"; ?>
                    </div>
                <?php endforeach; ?>
                <?php if ($total_respuestas == 0): ?>
                    <div class="sin-respuestas">No hay respuestas registradas para esta pregunta.</div>
                <?php endif; ?>
            </div>
        <?php
            endforeach;
        }
        ?>
        <a href="formularios.php">← Volver a la lista de formularios</a>
    </div>
</body>
</html>
