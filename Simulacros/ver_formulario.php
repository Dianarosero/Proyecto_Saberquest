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

// Obtener preguntas
$stmt = $conex->prepare("SELECT enunciado, opciones, correcta FROM preguntas WHERE formulario_id = ?");
$stmt->bind_param("i", $formulario_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($titulo); ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7f7; }
        .contenedor { max-width: 700px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px #ccc; }
        .pregunta { margin-bottom: 20px; }
        .opcion { margin-left: 20px; }
        .correcta { color: #27ae60; font-weight: bold; }
    </style>
</head>
<body>
    <div class="contenedor">
        <h2><?php echo htmlspecialchars($titulo); ?></h2>
        <p><?php echo nl2br(htmlspecialchars($descripcion)); ?></p>
        <hr>
        <?php
        $num = 1;
        while($row = $result->fetch_assoc()):
            $opciones = json_decode($row['opciones'], true);
            $correcta = $row['correcta'];
        ?>
            <div class="pregunta">
                <strong><?php echo $num++; ?>. <?php echo htmlspecialchars($row['enunciado']); ?></strong>
                <div class="opcion<?php if($correcta == 'a') echo ' correcta'; ?>">a) <?php echo htmlspecialchars($opciones['a']); ?></div>
                <div class="opcion<?php if($correcta == 'b') echo ' correcta'; ?>">b) <?php echo htmlspecialchars($opciones['b']); ?></div>
                <div class="opcion<?php if($correcta == 'c') echo ' correcta'; ?>">c) <?php echo htmlspecialchars($opciones['c']); ?></div>
                <div class="opcion<?php if($correcta == 'd') echo ' correcta'; ?>">d) <?php echo htmlspecialchars($opciones['d']); ?></div>
            </div>
        <?php endwhile; ?>
        <a href="formularios.php">← Volver a la lista de formularios</a>
    </div>
</body>
</html>
