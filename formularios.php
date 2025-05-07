<?php
session_start();
include("base de datos/con_db.php");

// Consulta para obtener todos los formularios
$sql = "SELECT id, titulo, descripcion FROM formularios ORDER BY id DESC";
$result = $conex->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formularios creados</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7f7; }
        .contenedor { max-width: 700px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px #ccc; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; }
        th { background: #3498db; color: #fff; }
        tr:hover { background: #f1f1f1; }
        a { color: #3498db; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="contenedor">
        <h2>Formularios creados</h2>
        <table>
            <tr>
                <th>Título</th>
                <th>Descripción</th>
                <th>Ver preguntas</th>
                <th>Responder</th>
            </tr>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['titulo']); ?></td>
                <td><?php echo nl2br(htmlspecialchars($row['descripcion'])); ?></td>
                <td><a href="ver_formulario.php?id=<?php echo $row['id']; ?>">Ver</a></td>
                <td><a href="responder_formulario.php?id=<?php echo $row['id']; ?>">Responder</a></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
