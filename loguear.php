<?php
// Iniciar sesión para poder usar variables de sesión
session_start();

// Conexión a la base de datos
include("base de datos/con_db.php");

// Obtener datos del formulario de inicio de sesión
$usuario = trim($_POST['usuario']);
$contraseña = trim($_POST['contraseña']);

// Consulta para verificar la combinación de email y contraseña
$consulta = "SELECT * FROM usuarios WHERE codigo_estudiantil='$usuario' AND contraseña='$contraseña'";
$resultado = mysqli_query($conex, $consulta);

// Verificar si se encontró algún resultado
if (mysqli_num_rows($resultado) > 0) {
    $filas = mysqli_fetch_assoc($resultado);

    // Iniciar sesión y establecer variables de sesión
    $_SESSION['usuario'] = $usuario;
    $_SESSION['idusuario'] = $filas['id']; // Suponiendo que 'id' es el nombre de la columna que contiene el ID del usuario

    // Redirigir según el tipo de usuario (id_cargo)
    if ($filas['id_rol'] == 1) { // Admin
        header('Location: inicio_administrador.php');
        exit();
    } elseif ($filas['id_rol'] == 2) { // Docente
        header('Location: inicio_docente.php');
        exit();
    }
    elseif ($filas['id_rol'] == 3 ) { // Estudiante
        header('Location: inicio_estudiante.php');
        exit();
    }
} else {
    session_start();
    $_SESSION['mensaje'] = 'Error en la autenticación, ingrese nuevamente los datos.';
    header('Location: index.php');
    exit;
}

// Liberar resultados y cerrar la conexión
mysqli_free_result($resultado);
mysqli_close($conex);
?>