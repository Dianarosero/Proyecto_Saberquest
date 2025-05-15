<?php
include("../base de datos/con_db.php");

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibir y sanitizar datos
    $nombres = trim($_POST["nombres"]);
    $password = $_POST["contraseña"];
    $num_identificacion = trim($_POST["num_identificacion"]);
    $tipo_usuario = $_POST["cboTipoUsuarios"];
    $semestre = isset($_POST["semestre"]) ? trim($_POST["semestre"]) : null;

    // Mapear tipo_usuario a id_rol
    $roles = [
        "student" => 3,
        "teacher" => 2
    ];
    $id_rol = isset($roles[$tipo_usuario]) ? $roles[$tipo_usuario] : null;

    if (!$id_rol) {
        $_SESSION['mensaje'] = 'Tipo de usuario inválido.';
        $_SESSION['mensaje_tipo'] = 'error';
        header('Location: form.php');
        exit;
    }

    // Validar si el código institucional ya existe
    $stmt_check = $conex->prepare("SELECT * FROM usuarios WHERE codigo_estudiantil = ?");
    $stmt_check->bind_param("s", $num_identificacion);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $_SESSION['mensaje'] = 'El código institucional ya existe.';
        $_SESSION['mensaje_tipo'] = 'error';
        header('Location: form.php');
        exit;
    }

    // Hashear contraseña
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Preparar consulta de inserción con semestre (si aplica)
    if ($id_rol == 3) { // Corregido: estudiante tiene id_rol = 3
        if (empty($semestre)) {
            $_SESSION['mensaje'] = 'El semestre es obligatorio para estudiantes.';
            $_SESSION['mensaje_tipo'] = 'error';
            header('Location: form.php');
            exit;
        }
        $stmt_insert = $conex->prepare("INSERT INTO usuarios (nombre, contraseña, codigo_estudiantil, id_rol, semestre) VALUES (?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("sssis", $nombres, $password_hash, $num_identificacion, $id_rol, $semestre);
    } else {
        // Para otros usuarios sin semestre
        $stmt_insert = $conex->prepare("INSERT INTO usuarios (nombre, contraseña, codigo_estudiantil, id_rol) VALUES (?, ?, ?, ?)");
        $stmt_insert->bind_param("sssi", $nombres, $password_hash, $num_identificacion, $id_rol);
    }

    if ($stmt_insert->execute()) {
        $_SESSION['mensaje'] = 'Usuario registrado correctamente.';
        $_SESSION['mensaje_tipo'] = 'success';
        header('Location: ../index.php');
        exit;
    } else {
        $_SESSION['mensaje'] = 'Error al registrar el usuario: ' . $stmt_insert->error;
        $_SESSION['mensaje_tipo'] = 'error';
        header('Location: form.php');
        exit;
    }

    // Cerrar statements
    $stmt_check->close();
    $stmt_insert->close();
}

$conex->close();
?>