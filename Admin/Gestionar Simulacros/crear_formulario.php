<?php
session_start();
// Si quieres restringir solo a admins, descomenta estas líneas:
// if ($_SESSION['tipo_usuario'] != 'admin') {
//     header('Location: index.php');
//     exit();
// }
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Crear nuevo formulario</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background: #f7f7f7;
    }

    form {
        background: #fff;
        max-width: 650px;
        margin: 40px auto;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 8px #ccc;
    }

    h2 {
        text-align: center;
    }

    .pregunta {
        margin: 18px 0;
        padding: 15px;
        border: 1px solid #e0e0e0;
        border-radius: 5px;
        background: #fafafa;
    }

    .pregunta input[type="text"] {
        width: 90%;
        padding: 8px;
        margin-bottom: 5px;
    }

    .opciones {
        margin-left: 20px;
    }

    .opciones label {
        width: 20px;
        display: inline-block;
    }

    .eliminar-btn {
        background: #e74c3c;
        color: #fff;
        border: none;
        padding: 5px 10px;
        border-radius: 3px;
        cursor: pointer;
    }

    .eliminar-btn:hover {
        background: #c0392b;
    }

    .add-btn {
        background: #3498db;
        color: #fff;
        border: none;
        padding: 8px 16px;
        border-radius: 3px;
        cursor: pointer;
    }

    .add-btn:hover {
        background: #217dbb;
    }

    .guardar-btn {
        background: #27ae60;
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        float: right;
    }

    .guardar-btn:hover {
        background: #1e874b;
    }

    label {
        font-weight: bold;
    }
    </style>
</head>

<body>
    <form method="POST" action="guardar_formulario.php">
        <h2>Crear nuevo formulario</h2>
        <label for="titulo">Título del formulario</label><br>
        <input type="text" id="titulo" name="titulo" required><br><br>

        <label for="descripcion">Descripción</label><br>
        <textarea id="descripcion" name="descripcion" rows="3" style="width:90%;"></textarea><br><br>

        <label>Preguntas</label>
        <div id="preguntas-container"></div>
        <button type="button" class="add-btn" onclick="agregarPregunta()">Añadir pregunta</button>
        <br><br>
        <button type="submit" class="guardar-btn">Guardar formulario</button>
    </form>

    <script>
    function agregarPregunta() {
        const contenedor = document.getElementById('preguntas-container');
        const nuevaPregunta = document.createElement('div');
        nuevaPregunta.className = 'pregunta';
        nuevaPregunta.innerHTML = `
            <input type="text" name="enunciado[]" placeholder="Escribe la pregunta" required>
            <button type="button" class="eliminar-btn" onclick="this.parentNode.remove()">Eliminar</button>
            <div class="opciones">
    <label>a)</label><input type="text" name="opcion_a[]" placeholder="Opción a" required><br>
    <label>b)</label><input type="text" name="opcion_b[]" placeholder="Opción b" required><br>
    <label>c)</label><input type="text" name="opcion_c[]" placeholder="Opción c" required><br>
    <label>d)</label><input type="text" name="opcion_d[]" placeholder="Opción d" required><br>
    <label>Respuesta correcta:</label>
    <select name="correcta[]" required>
        <option value="a">a</option>
        <option value="b">b</option>
        <option value="c">c</option>
        <option value="d">d</option>
    </select>
</div>

        `;
        contenedor.appendChild(nuevaPregunta);
    }
    // Opcional: agrega una pregunta por defecto al cargar la página
    window.onload = agregarPregunta;
    </script>
</body>

</html>