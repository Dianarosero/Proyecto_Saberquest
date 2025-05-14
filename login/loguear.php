<?php
session_start();
include("../base de datos/con_db.php");

// Obtener datos del formulario
$codigo_estudiantil = trim($_POST['usuario']);
$contraseña = trim($_POST['contraseña']);

// Buscar usuario por codigo_estudiantil
$stmt = $conex->prepare("SELECT * FROM usuarios WHERE codigo_estudiantil = ?");
$stmt->bind_param("s", $codigo_estudiantil);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {
    $filas = $resultado->fetch_assoc();

    // Validar contraseña según rol
    if ($filas['id_rol'] == 1) {
        // Admin: contraseña sin hash
        if ($contraseña === $filas['contraseña']) {
            // Guardar datos en sesión
            $_SESSION['usuario_id'] = $filas['id'];
            $_SESSION['codigo_estudiantil'] = $codigo_estudiantil;
            $_SESSION['id_rol'] = $filas['id_rol'];

            // Obtener nombre del rol
            $stmtRol = $conex->prepare("SELECT tipo_rol FROM roles WHERE id_rol = ?");
            $stmtRol->bind_param("i", $filas['id_rol']);
            $stmtRol->execute();
            $resultRol = $stmtRol->get_result();
            $rolData = $resultRol->fetch_assoc();
            $_SESSION['rol'] = $rolData['tipo_rol'];

            header('Location: ../Admin/index_admin.php');
            exit();
        } else {
            $_SESSION['mensaje'] = 'Contraseña incorrecta. Intente nuevamente.';
            header('Location: ../index.php');
            exit();
        }
    } else {
        // Docente y estudiante: contraseña con hash
        if (password_verify($contraseña, $filas['contraseña'])) {
            // Guardar datos en sesión
            $_SESSION['usuario_id'] = $filas['id'];
            $_SESSION['codigo_estudiantil'] = $codigo_estudiantil;
            $_SESSION['id_rol'] = $filas['id_rol'];

            // Obtener nombre del rol
            $stmtRol = $conex->prepare("SELECT tipo_rol FROM roles WHERE id_rol = ?");
            $stmtRol->bind_param("i", $filas['id_rol']);
            $stmtRol->execute();
            $resultRol = $stmtRol->get_result();
            $rolData = $resultRol->fetch_assoc();
            $_SESSION['rol'] = $rolData['tipo_rol'];

            if ($filas['id_rol'] == 2) {
                header('Location: ../Docente/index_docente.php');
                exit();
            } elseif ($filas['id_rol'] == 3) {
                header('Location: ../Estudiante/index_estudiante.php');
                exit();
            } else {
                $_SESSION['mensaje'] = 'Rol de usuario no válido.';
                header('Location: ../index.php');
                exit();
            }
        } else {
            $_SESSION['mensaje'] = 'Contraseña incorrecta. Intente nuevamente.';
            header('Location: ../index.php');
            exit();
        }
    }
} else {
    $_SESSION['mensaje'] = 'Usuario no encontrado.';
    header('Location: ../index.php');
    exit();
}

$stmt->close();
$conex->close();
?>
