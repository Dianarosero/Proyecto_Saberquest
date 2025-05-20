<?php
session_start();
include("../../base de datos/con_db.php");

// Validar que el usuario esté logueado y sea profesor
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'Estudiante') {
    header('Location: ../../index.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$formulario_id = $_GET['id'] ?? 0;

// Verificar si el usuario ya respondió este formulario
$stmt = $conex->prepare("SELECT COUNT(*) FROM respuestas WHERE formulario_id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $formulario_id, $usuario_id);
$stmt->execute();
$stmt->bind_result($ya_respondio);
$stmt->fetch();
$stmt->close();

if ($ya_respondio > 0) {
    // Mostrar mensaje y salir
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <title>Simulacro ya respondido</title>
        <link href='../../assets/img/favicon.png' rel='icon'>
        <link href='../../assets/img/favicon.png' rel='apple-touch-icon'>
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
        <style>
            body { font-family: Arial, sans-serif; background: #f5f5f5; }
            .container { max-width: 500px; margin: 100px auto; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.07);}
            h2 { color: #003366; }
            .btn { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #003366; color: #fff; border-radius: 8px; text-decoration: none;}
        </style>
    </head>
    <body>
        <div class='container'>
            <h2><i class='fas fa-exclamation-circle'></i> Ya has respondido este Simulacro</h2>
            <p>No puedes volver a realizar este simulacro.</p>
            <a href='formularios_estudiante.php' class='btn'><i></i> Volver a ver simulacros</a>
        </div>
    </body>
    </html>";
    exit;
}


// Obtener datos del formulario, incluyendo la configuración de mostrar_respuestas y la imagen de fondo
$stmt = $conex->prepare("SELECT titulo, descripcion, mostrar_respuestas, imagen FROM formularios WHERE id = ?");
$stmt->bind_param("i", $formulario_id);
$stmt->execute();
$stmt->bind_result($titulo, $descripcion, $mostrar_respuestas, $imagen_fondo);
if (!$stmt->fetch()) {
    die("Simulacro no encontrado.");
}
$stmt->close();

// Establecer imagen de fondo predeterminada si no hay una en la base de datos
$imagen_fondo = !empty($imagen_fondo) ? $imagen_fondo : '../../assets/src_simulacros/img_simulacros/predeterminadas/predeterminada2.png';

// Obtener preguntas con la respuesta correcta y opciones
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

// Procesar envío de respuestas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['final_submit'])) {
    $aciertos = 0;
    $total = count($preguntas);
    $respuestas_usuario = [];

    foreach ($preguntas as $pregunta) {
        $pid = $pregunta['id'];
        $respuesta_usuario = $_POST["respuesta_$pid"] ?? '';

        // Guardar respuesta en la base de datos
        $stmt = $conex->prepare("INSERT INTO respuestas (formulario_id, pregunta_id, usuario_id, respuesta) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $formulario_id, $pid, $usuario_id, $respuesta_usuario);
        $stmt->execute();
        $stmt->close();

        $correcta = $pregunta['correcta'];
        $es_correcta = ($respuesta_usuario == $correcta);
        if ($es_correcta) {
            $aciertos++;
        }

        $respuestas_usuario[] = [
            'enunciado' => $pregunta['enunciado'],
            'respuesta_usuario' => $respuesta_usuario,
            'correcta' => $correcta,
            'opciones' => $pregunta['opciones'],
            'es_correcta' => $es_correcta,
        ];
    }

    // Mostrar resultados
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Resultados - SABERQUEST</title>
        <link href='../../assets/img/favicon.png' rel='icon'>
        <link href='../../assets/img/favicon.png' rel='apple-touch-icon'>
        <link rel='preconnect' href='https://fonts.googleapis.com'>
        <link rel='preconnect' href='https://fonts.gstatic.com' crossorigin>
        <link href='https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap' rel='stylesheet'>
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
        <style>
            :root {
                --primary: #003366;
                --primary-light: #0056b3;
                --secondary: #B22222;
                --secondary-light: #d93636;
                --accent: #FFD700;
                --accent-light: #FFE44D;
                --background: #FFFFFF;
                --text: #333333;
                --text-light: #666666;
                --neutral: #E0E0E0;
                --neutral-light: #F7F7F7;
                --success: #27ae60;
                --error: #c62828;
                --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.05);
                --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
                --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
                --border-radius: 12px;
                --transition: all 0.3s ease;
                --gap: 1.5rem;
            }

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Montserrat', sans-serif;
                background: var(--neutral-light);
                color: var(--text);
                position: relative;
                display: flex;
                flex-direction: column;
                min-height: 100vh;
                line-height: 1.6;
            }
            
            .bg-container {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-image: url('" . htmlspecialchars($imagen_fondo) . "');
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                filter: blur(8px);
                opacity: 0.12;
                z-index: -1;
            }
            
            .header {
                background-color: var(--primary);
                color: white;
                padding: 1.2rem 12rem;
                width: 100%;
                box-shadow: var(--shadow-md);
                display: flex;
                justify-content: space-between;
                align-items: center;
                position: sticky;
                top: 0;
                z-index: 100;
                transition: var(--transition);
            }
            
            .header:hover {
                box-shadow: var(--shadow-lg);
            }
            
            .university-logo {
                font-size: 1.5rem;
                font-weight: 700;
                display: flex;
                align-items: center;
            }
            
            .university-logo i {
                margin-right: 10px;
                color: var(--accent);
            }
            
            .results-container {
                max-width: 800px;
                width: 100%;
                margin: 30px auto;
                background: var(--background);
                padding: 40px;
                border-radius: var(--border-radius);
                box-shadow: var(--shadow-md);
                transition: var(--transition);
                flex: 1;
            }
            
            .results-container:hover {
                box-shadow: var(--shadow-lg);
            }
            
            .form-header {
                margin-bottom: 2rem;
                border-bottom: 1px solid var(--neutral);
                padding-bottom: 1.5rem;
            }
            
            h2 {
                color: var(--primary);
                margin-bottom: 1rem;
                font-size: 2rem;
                position: relative;
                display: inline-block;
            }
            
            h2::after {
                content: '';
                position: absolute;
                bottom: -5px;
                left: 0;
                width: 40%;
                height: 3px;
                background-color: var(--secondary);
                border-radius: 3px;
            }
            
            h3 {
                color: var(--primary);
                margin: 1.5rem 0 1rem;
                font-size: 1.4rem;
                font-weight: 600;
            }
            
            .result-summary {
                background: #f0f7ff;
                padding: 1.2rem;
                border-radius: 8px;
                margin: 1.5rem 0;
                border-left: 4px solid var(--primary);
                font-size: 1.1rem;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 12px;
            }
            
            .result-summary i {
                font-size: 1.4rem;
                color: var(--primary);
            }
            
            .result-item {
                background: var(--neutral-light);
                padding: 25px;
                border-radius: var(--border-radius);
                border-left: 4px solid var(--primary);
                margin-bottom: 1.5rem;
                box-shadow: var(--shadow-sm);
                transition: var(--transition);
                position: relative;
                overflow: hidden;
            }
            
            .result-item:hover {
                box-shadow: var(--shadow-md);
                transform: translateY(-3px);
            }
            
            .result-item.correct-result {
                border-left-color: var(--success);
            }
            
            .result-item.incorrect-result {
                border-left-color: var(--secondary);
            }
            
            .result-item strong {
                font-size: 1.2rem;
                font-weight: 600;
                color: var(--primary);
                margin-bottom: 1.2rem;
                line-height: 1.4;
                display: flex;
                align-items: flex-start;
            }
            
            .pregunta-numero {
                font-size: 1.5rem;
                font-weight: 700;
                color: var(--primary);
                margin-right: 0.5rem;
            }
            
            .options-list {
                display: grid;
                gap: 12px;
                margin: 1.2rem 0;
            }
            
            .option {
                display: flex;
                align-items: center;
                padding: 12px 15px;
                border-radius: 8px;
                background: var(--background);
                border: 1px solid var(--neutral);
                transition: var(--transition);
                position: relative;
                overflow: hidden;
            }
            
            .option:hover {
                border-color: var(--primary-light);
                box-shadow: var(--shadow-sm);
            }
            
            .correct-option {
                background-color: rgba(39, 174, 96, 0.1);
                border-color: var(--success);
            }
            
            .correct-option::after {
                content: '\\f00c';
                font-family: 'Font Awesome 6 Free';
                font-weight: 900;
                position: absolute;
                right: 15px;
                color: var(--success);
            }
            
            .incorrect-option {
                background-color: rgba(178, 34, 34, 0.1);
                border-color: var(--secondary);
                text-decoration: line-through;
            }
            
            .incorrect-option::after {
                content: '\\f00d';
                font-family: 'Font Awesome 6 Free';
                font-weight: 900;
                position: absolute;
                right: 15px;
                color: var(--secondary);
            }
            
            .correct-answer {
                color: var(--success);
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }
            
            .incorrect-answer {
                color: var(--secondary);
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }
            
            .btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 10px 20px;
                border-radius: var(--border-radius);
                border: none;
                font-family: 'Montserrat', sans-serif;
                font-weight: 600;
                font-size: 0.95rem;
                cursor: pointer;
                transition: var(--transition);
                text-decoration: none;
                gap: 8px;
            }
            
            .btn-primary {
                background-color: var(--primary);
                color: white;
                border: 2px solid transparent;
            }
            
            .btn-primary:hover {
                background-color: var(--primary-light);
                box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.2);
                transform: translateY(-2px);
            }
            
            .action-container {
                text-align: center;
                margin-top: 2rem;
            }
            
            .footer {
                margin-top: auto;
                background-color: var(--primary);
                color: white;
                padding: 1.5rem;
                text-align: center;
                box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            }
            
            .footer a {
                color: var(--accent);
                text-decoration: none;
                transition: var(--transition);
            }
            
            .footer a:hover {
                color: var(--accent-light);
                text-decoration: underline;
            }
            
            @media (max-width: 768px) {
                .header {
                    padding: 1rem;
                }
                
                .results-container {
                    margin: 20px 15px;
                    padding: 25px;
                }
                
                h2 {
                    font-size: 1.6rem;
                }
                
                h3 {
                    font-size: 1.3rem;
                }
                
                .result-item {
                    padding: 20px;
                }
                
                .result-item strong {
                    font-size: 1rem;
                }
                
                .option {
                    padding: 10px;
                }
            }

            .nav-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            /* Centra el contenido */
        }

        .nav-list {
            display: flex;
            gap: 30px;
        }

        .nav-link {
            font-size: 1rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
            padding-bottom: 5px;
            position: relative;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: #FFFFFF;
            /* Color más brillante al hacer hover */
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: #FFFFFF;
            /* Blanco, como en la imagen */
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        a {
            text-decoration: none;
            color: inherit;
        }
        
        /* Add to existing <style> in the form section */
.description,
.question-card h3,
.option label,
.result-item p {
  text-align: justify;
}

.footer p {
  text-align: center;
}

        </style>
    </head>
    <body>
        <div class='bg-container'></div>
        
        <header class='header'>
            <div class='logo-space'>
                <img width='120' height='50' fill='none' src='../../assets/img/Logo_fondoazul.png'>
            </div>

            <div class='nav-controls'>
                <nav class='nav'>
                    <div class='nav-list'>
                        <a class='nav-link' href='../index_estudiante.php#projects'>Inicio</a>
                    </div>
                </nav>
            </div>
        </header>
        
        <div class='results-container'>
            <div class='form-header'>
                <h2>¡Simulacro enviado!</h2>
            </div>
            
            <div class='result-summary'>
                <i class='fas fa-chart-pie'></i>
                <p>Respuestas correctas: <b>$aciertos</b> de <b>$total</b></p>
            </div>";

    if ($mostrar_respuestas) {
        echo "<h3>Detalle de respuestas:</h3>";

        foreach ($respuestas_usuario as $idx => $resp) {
            $num = $idx + 1;
            $result_class = $resp['es_correcta'] ? 'correct-result' : 'incorrect-result';
            $icon = $resp['es_correcta'] ? '<i></i>' : '<i class="fas fa-times-circle"></i>';

            echo "<div class='result-item $result_class'>";
            echo "<strong><span class='pregunta-numero'>$num.</span> " . htmlspecialchars($resp['enunciado']) . "</strong>";
            echo "<div class='options-list'>";

            foreach (['a', 'b', 'c', 'd'] as $letra) {
                $texto_opcion = htmlspecialchars($resp['opciones'][$letra]);
                $option_class = '';

                if ($letra == $resp['correcta']) {
                    $option_class = "correct-option";
                }
                if ($letra == $resp['respuesta_usuario'] && !$resp['es_correcta']) {
                    $option_class = "incorrect-option";
                }

                echo "<div class='option $option_class'>$letra) $texto_opcion</div>";
            }

            echo "</div>";

            $answer_class = $resp['es_correcta'] ? 'correct-answer' : 'incorrect-answer';
            echo "<p class='$answer_class'>$icon Tu respuesta: <b>" . strtoupper(htmlspecialchars($resp['respuesta_usuario'])) . "</b> - " . ($resp['es_correcta'] ? "Correcta" : "Incorrecta") . "</p>";
            echo "</div>";
        }
    } else {
        echo '
<div style="max-width: 600px; margin: 20px auto; padding: 30px; background-color: #f9f9f9; border-radius: 12px; text-align: center; box-shadow: 0 2px 6px rgba(0,0,0,0.05); font-family: Arial, sans-serif;">
  <div style="font-size: 48px; color: #666; margin-bottom: 10px;">
    <i class="fas fa-lock"></i>
  </div>
  <div style="font-weight: bold; font-size: 18px; color: #003366; margin-bottom: 10px;">
    Detalle de respuestas no disponible
  </div>
  <div style="font-size: 16px; color: #555;">
    Se ha configurado este simulacro para no mostrar el detalle de respuestas después de enviarlo.
  </div>
</div>';
    }

    echo "</div>
    
    <footer class='footer'>
        <p>&copy; " . date('Y') . " SABERQUEST. Todos los derechos reservados.</p>
    </footer>
    
    </body>
    </html>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titulo); ?> - SABERQUEST</title>
    <link href="../../assets/img/favicon.png" rel="icon">
    <link href="../../assets/img/favicon.png" rel="apple-touch-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    :root {
        --primary: #003366;
        --primary-light: #0056b3;
        --secondary: #B22222;
        --secondary-light: #d93636;
        --accent: #FFD700;
        --accent-light: #FFE44D;
        --background: #FFFFFF;
        --text: #333333;
        --text-light: #666666;
        --neutral: #E0E0E0;
        --neutral-light: #F7F7F7;
        --success: #27ae60;
        --error: #c62828;
        --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
        --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
        --border-radius: 12px;
        --transition: all 0.3s ease;
        --gap: 1.5rem;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Montserrat', sans-serif;
        background: var(--neutral-light);
        color: var(--text);
        position: relative;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        line-height: 1.6;
    }

    .bg-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('<?php echo htmlspecialchars($imagen_fondo); ?>');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        filter: blur(8px);
        opacity: 0.12;
        z-index: -1;
    }

    .header {
        background-color: var(--primary);
        color: white;
        padding: 1.2rem 12rem;
        width: 100%;
        box-shadow: var(--shadow-md);
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        z-index: 100;
        transition: var(--transition);
    }

    .header:hover {
        box-shadow: var(--shadow-lg);
    }

    .university-logo {
        font-size: 1.5rem;
        font-weight: 700;
        display: flex;
        align-items: center;
    }

    .university-logo i {
        margin-right: 10px;
        color: var(--accent);
    }

    .header-actions {
        display: flex;
        gap: 10px;
    }

    .btn-outline {
        background-color: transparent;
        color: white;
        border: 2px solid white;
    }

    .btn-outline:hover {
        background-color: rgba(255, 255, 255, 0.1);
        transform: translateY(-2px);
    }

    .btn-disabled {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
    }

    .contenedor {
        max-width: 800px;
        width: 100%;
        margin: 30px auto;
        background: var(--background);
        padding: 40px;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-md);
        transition: var(--transition);
        flex: 1;
    }

    .contenedor:hover {
        box-shadow: var(--shadow-lg);
    }

    .form-header {
        margin-bottom: 2rem;
        border-bottom: 1px solid var(--neutral);
        padding-bottom: 1.5rem;
    }

    h1,
    h2 {
        color: var(--primary);
        margin-bottom: 1rem;
        font-size: 2rem;
        position: relative;
        display: inline-block;
    }

    h1::after,
    h2::after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 0;
        width: 40%;
        height: 3px;
        background-color: var(--secondary);
        border-radius: 3px;
    }

    .description {
        color: var(--text-light);
        font-size: 1.1rem;
        margin-top: 0.5rem;
    }

    #form-container {
        display: flex;
        flex-direction: column;
        gap: var(--gap);
    }

    .page {
        display: flex;
        flex-direction: column;
        gap: var(--gap);
    }

    .question-card {
        background: var(--neutral-light);
        padding: 25px;
        border-radius: var(--border-radius);
        border-left: 4px solid var(--primary);
        margin-bottom: 5px;
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .question-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-3px);
    }

    .question-card h3 {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 1.2rem;
        line-height: 1.4;
        display: flex;
        align-items: flex-start;
    }

    .pregunta-numero {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary);
        margin-right: 0.5rem;
    }

    .options {
        display: grid;
        gap: 12px;
        margin-top: 20px;
    }

    .option {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        border-radius: 8px;
        background: var(--background);
        border: 1px solid var(--neutral);
        transition: var(--transition);
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .option:hover {
        border-color: var(--primary-light);
        box-shadow: var(--shadow-sm);
    }

    .option input[type="radio"] {
        opacity: 0;
        position: absolute;
    }

    .option label {
        display: flex;
        align-items: center;
        cursor: pointer;
        width: 100%;
        font-size: 1rem;
        padding-left: 2.2rem;
        position: relative;
    }

    .option-letter {
        position: absolute;
        left: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        background: var(--neutral);
        border-radius: 50%;
        color: var(--text);
        margin-right: 10px;
        font-weight: 600;
        transition: var(--transition);
    }

    .option input[type="radio"]:checked+label .option-letter {
        background: var(--primary);
        color: var(--background);
    }

    .option input[type="radio"]:checked+label {
        font-weight: 500;
    }

    .option input[type="radio"]:focus+label .option-letter {
        box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.2);
    }

    /* Paginación */
    .pagination-controls {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin: 2rem 0;
        flex-wrap: wrap;
    }

    .pagination-controls a,
    .pagination-controls button,
    .pagination-controls .page-number {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: white;
        border: 1px solid var(--neutral);
        color: var(--text);
        font-weight: 600;
        transition: var(--transition);
        text-decoration: none;
        cursor: pointer;
    }

    .nav-anterior,
    .nav-siguiente {
        width: auto !important;
        padding: 0 15px;
        border-radius: 20px !important;
    }

    .pagination-controls .page-number.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .pagination-controls a:hover:not(:disabled),
    .pagination-controls button:hover:not(:disabled),
    .pagination-controls .page-number:hover:not(.active) {
        background: var(--primary-light);
        color: white;
        border-color: var(--primary-light);
        transform: translateY(-2px);
    }

    .pagination-controls button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .form-actions {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin: 2rem 0;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 20px;
        border-radius: var(--border-radius);
        border: none;
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
        gap: 8px;
    }

    .btn-primary {
        background-color: var(--primary);
        color: white;
        border: 2px solid transparent;
    }

    .btn-primary:hover,
    .btn-primary:focus {
        background-color: var(--primary-light);
        box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.2);
        transform: translateY(-2px);
    }

    .btn-secondary {
        background-color: var(--neutral);
        color: var(--text);
        border: 2px solid transparent;
    }

    .btn-secondary:hover,
    .btn-secondary:focus {
        background-color: var(--neutral-light);
        color: var(--primary);
        box-shadow: var(--shadow-sm);
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: none !important;
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 100;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .modal.show {
        opacity: 1;
        display: flex;
    }

    .modal-content {
        background-color: var(--background);
        padding: 2rem;
        border-radius: var(--border-radius);
        max-width: 90%;
        width: 600px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: var(--shadow-lg);
        transform: translateY(-20px);
        transition: transform 0.3s ease;
    }

    .modal.show .modal-content {
        transform: translateY(0);
    }

    .modal-content h2 {
        margin-bottom: 1.5rem;
        color: var(--primary);
        text-align: center;
        font-size: 1.5rem;
    }

    .modal-content h2::after {
        left: 50%;
        transform: translateX(-50%);
        width: 30%;
    }

    .modal-content p {
        text-align: center;
        margin: 1.5rem 0;
        font-weight: 600;
        color: var(--text);
    }

    .modal-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 1.5rem;
        gap: 1rem;
    }

    .footer {
        margin-top: auto;
        background-color: var(--primary);
        color: white;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
    }

    .footer a {
        color: var(--accent);
        text-decoration: none;
        transition: var(--transition);
    }

    .footer a:hover {
        color: var(--accent-light);
        text-decoration: underline;
    }

    #answers-summary {
        max-height: 300px;
        overflow-y: auto;
        margin: 1rem 0;
        padding: 1rem;
        background: var(--neutral-light);
        border-radius: 8px;
    }

    .summary-item {
        margin-bottom: 0.8rem;
        padding-bottom: 0.8rem;
        border-bottom: 1px solid var(--neutral);
    }

    .summary-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    /* Highlight element when validation fails */
    .highlight {
        animation: highlight-pulse 1.5s ease;
    }

    @keyframes highlight-pulse {

        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(178, 34, 34, 0);
        }

        50% {
            box-shadow: 0 0 0 8px rgba(178, 34, 34, 0.3);
        }
    }

    /* Responsive styles */
    @media (max-width: 768px) {
        .header {
            padding: 1rem;
        }

        .contenedor {
            margin: 20px 15px;
            padding: 25px 20px;
        }

        h1,
        h2 {
            font-size: 1.6rem;
        }

        .question-card {
            padding: 20px;
        }

        .option label {
            font-size: 0.95rem;
        }

        .modal-content {
            padding: 1.5rem;
        }

        .modal-actions {
            flex-direction: column;
        }

        .modal-actions .btn {
            width: 100%;
        }

        .pagination-controls {
            flex-wrap: wrap;
            gap: 0.8rem;
        }
    }

    @media (max-width: 480px) {
        .question-card {
            padding: 15px;
        }

        .option {
            padding: 10px;
        }

        .option-letter {
            width: 24px;
            height: 24px;
            font-size: 0.9rem;
        }

        .option label {
            padding-left: 1.8rem;
            font-size: 0.9rem;
        }
    }

    /* Add to existing <style> in the form section */
.description,
.question-card h3,
.option label,
.result-item p {
  text-align: justify;
}

.footer p {
  text-align: center;
}
    
    </style>
</head>

<body>
    <div class="bg-container"></div>

    <header class="header">
        <div class="logo-space">
                <img width="120" height="50" fill="none" src="../../assets/img/Logo_fondoazul.png" alt="" srcset="">
            </div>

        <div class="header-actions">
            <a href="../index_estudiante.php" class="btn btn-outline btn-disabled">
                <i></i> Inicio
            </a>
        </div>
    </header>

    <div class="contenedor">
        <div class="form-header">
            <h1><?php echo htmlspecialchars($titulo); ?></h1>
            <p class="description"><?php echo nl2br(htmlspecialchars($descripcion)); ?></p>
        </div>

        <form id="questionForm" method="post">
            <div id="form-container">
                <?php
                $num_preguntas = count($preguntas);
                $necesita_paginacion = $num_preguntas > 8;
                $preguntas_por_pagina = 8;
                $num_paginas = ceil($num_preguntas / $preguntas_por_pagina);

                for ($pagina = 0; $pagina < $num_paginas; $pagina++) {
                    $display = $pagina === 0 ? '' : 'display: none;';
                    echo "<div class='page' id='page-$pagina' style='$display'>";

                    $inicio = $pagina * $preguntas_por_pagina;
                    $fin = min($inicio + $preguntas_por_pagina, $num_preguntas);

                    for ($i = $inicio; $i < $fin; $i++) {
                        $pregunta = $preguntas[$i];
                        $num = $i + 1;

                        echo "<div class='question-card'>";
                        echo "<h3><span class='pregunta-numero'>$num.</span> " . htmlspecialchars($pregunta['enunciado']) . "</h3>";
                        echo "<div class='options'>";

                        foreach (['a', 'b', 'c', 'd'] as $letra) {
                            $option_id = "q{$pregunta['id']}_$letra";
                            echo "<div class='option'>";
                            echo "<input type='radio' id='$option_id' name='respuesta_{$pregunta['id']}' value='$letra' required>";
                            echo "<label for='$option_id'><span class='option-letter'>$letra</span>" . htmlspecialchars($pregunta['opciones'][$letra]) . "</label>";
                            echo "</div>";
                        }

                        echo "</div>";
                        echo "</div>";
                    }

                    echo "</div>";
                }
                ?>
            </div>

            <div class="pagination-controls">
                <?php if ($necesita_paginacion): ?>
                <button type="button" id="prev-btn" class="nav-anterior" disabled>
                    <i class="fas fa-chevron-left"></i> Anterior
                </button>

                <?php for ($i = 1; $i <= $num_paginas; $i++): ?>
                <div id="page-number-<?php echo $i; ?>" class="page-number <?php echo ($i === 1) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </div>
                <?php endfor; ?>

                <button type="button" id="next-btn" class="nav-siguiente">
                    Siguiente <i class="fas fa-chevron-right"></i>
                </button>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="button" id="submit-btn" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Enviar respuestas
                </button>
            </div>

            <input type="hidden" name="final_submit" value="1">
        </form>

        <!-- Modal de confirmación -->
        <div id="confirmation-modal" class="modal">
            <div class="modal-content">
                <h2>Confirmar envío</h2>
                <div id="answers-summary"></div>
                <p>¿Estás seguro de enviar tus respuestas?</p>
                <div class="modal-actions">
                    <button type="button" id="edit-btn" class="btn btn-secondary">
                        <i class="fas fa-edit"></i> Volver a editar
                    </button>
                    <button type="button" id="confirm-btn" class="btn btn-primary">
                        <i class="fas fa-check"></i> Confirmar envío
                    </button>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> SABERQUEST. Todos los derechos reservados.</p>
    </footer>

    <script src="../../assets/js_responder/form-handler.js"></script>
</body>

</html>