<?php
// Configuración de la conexión a la base de datos
include("base de datos/con_db.php");

// Recibir datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombres = $_POST["nombres"];
    $password = $_POST["contraseña"];
    $num_identificacion = $_POST["num_identificacion"];
    $tipo_usuario = $_POST["cboTipoUsuarios"];

// Validar si el número de documento ya existe en la base de datos
        $sql_num_identificacion_check = "SELECT * FROM usuarios WHERE codigo_estudiantil='$num_identificacion'";
        $result_num_identificacion_check = $conex->query($sql_num_identificacion_check);
        if ($result_num_identificacion_check->num_rows > 0) {
            session_start();
            $_SESSION['mensaje'] = 'El numero de identificación ya existe';
            header('Location: registro.php');
            exit;
        } else {
            // Preparar la consulta SQL de inserción
            $sql_insert = "INSERT INTO usuarios (nombre,contraseña, codigo_estudiantil, id_rol) 
                           VALUES ('$nombres', '$password', '$num_identificacion', '$tipo_usuario')";
            
            // Ejecutar la consulta SQL de inserción
            if ($conex->query($sql_insert) === TRUE) {
                session_start();
                $_SESSION['mensaje'] = 'Usuario registrado correctamente.';
                header('Location: registro.php');
                exit;
            } else {
                session_start();
                $_SESSION['mensaje'] = 'Error al registrar el usuario: ' . $conex->error;
                header('Location: registro.php');
                exit;
            }
        }
    }


// Cerrar conexión
$conex->close();
?>
