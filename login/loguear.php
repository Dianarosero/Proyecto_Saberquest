<?php
session_start();
include("../base de datos/con_db.php");

// Obtener datos del formulario
$usuario = trim($_POST['usuario']);
$contraseña = trim($_POST['contraseña']);

// Consulta para obtener el usuario por código estudiantil
$stmt = $conex->prepare("SELECT * FROM usuarios WHERE codigo_estudiantil = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {
    $filas = $resultado->fetch_assoc();

    // Verificar la contraseña usando password_verify
    if (password_verify($contraseña, $filas['contraseña'])) {
        // Contraseña correcta: iniciar sesión
        $_SESSION['usuario'] = $usuario;
        $_SESSION['idusuario'] = $filas['id'];

        // Redirigir según el tipo de usuario
        switch ($filas['id_rol']) {
            case 1: // Admin
                header('Location: ../Admin/index_admin.php');
                exit();
            case 2: // Docente
                header('Location: ../Docente/index_docente.php');
                exit();
            case 3: // Estudiante
                header('Location: ../Estudiante/index_estudiante.php');
                exit();
            default:
                // Rol desconocido
                $_SESSION['mensaje'] = 'Rol de usuario no válido.';
                header('Location: ../index.php');
                exit();
        }
    } else {
        // Contraseña incorrecta
        $_SESSION['mensaje'] = 'Contraseña incorrecta. Intente nuevamente.';
        header('Location: ../index.php');
        exit();
    }
} else {
    // Usuario no encontrado
    $_SESSION['mensaje'] = 'Usuario no encontrado.';
    header('Location: ../index.php');
    exit();
}

// Liberar resultados y cerrar conexión
$stmt->close();
$conex->close();
?>
