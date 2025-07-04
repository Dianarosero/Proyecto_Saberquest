<?php
session_start();
$mensaje = $_SESSION['mensaje'] ?? '';
$mensaje_tipo = $_SESSION['mensaje_tipo'] ?? '';
unset($_SESSION['mensaje'], $_SESSION['mensaje_tipo']);
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1" />
    <title>Registro de Usuarios</title>
    <meta name="description" content="Regístrate como estudiante o docente con nuestro formulario fácil de usar." />
    <!-- Favicons -->
  <link href="../assets/img/favicon.png" rel="icon">
  <link href="../assets/img/favicon.png" rel="apple-touch-icon">
    <style>
    /* Variables Globales según la paleta proporcionada */
    :root {
        --primary-color: #003366;
        /* Color primario: Azul oscuro */
        --secondary-color: #B22222;
        /* Color secundario: Rojo */
        --accent-color: #FFD700;
        /* Color de acento: Amarillo dorado */
        --background-color: #FFFFFF;
        /* Fondo principal: Blanco */
        --text-color: #333333;
        /* Texto principal: Gris oscuro */
        --text-inverted: #FFFFFF;
        /* Texto invertido: Blanco */
        --neutral-light: #E0E0E0;
        /* Neutro claro: Gris claro para bordes y tarjetas */

        /* Variaciones adicionales */
        --primary-dark: #002244;
        --primary-light: #004488;
        --secondary-dark: #901C1C;
        --secondary-light: #C42B2B;
        --accent-dark: #E6C200;
        --accent-light: #FFDF33;
        --text-muted: #666666;
        --text-light: #777777;

        /* Gradientes */
        --primary-gradient: linear-gradient(135deg, #003366, #002244);
        --secondary-gradient: linear-gradient(135deg, #B22222, #901C1C);
        --accent-gradient: linear-gradient(135deg, #FFD700, #E6C200);

        /* Estilos UI */
        --border-radius: 10px;
        --input-radius: 8px;
        --box-shadow: 0 8px 20px rgba(0, 51, 102, 0.1);
        --transition: all 0.3s ease-in-out;
        --error-color: #D32F2F;
        --success-color: #388E3C;

        /* Tipografía */
        --font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }

    /* Reset y Estilos Base */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: var(--font-family);
        background-color: var(--background-color);
        color: var(--text-color);
        line-height: 1.6;
        min-height: 100vh;
        display: flex;
        padding: 0;
        margin: 0;
        overflow-x: hidden;
    }

    /* Importar Montserrat */
    @font-face {
        font-family: 'Montserrat';
        font-style: normal;
        font-weight: 400;
        src: url(https://fonts.gstatic.com/s/montserrat/v25/JTUSjIg1_i6t8kCHKm459Wlhyw.woff2) format('woff2');
    }

    @font-face {
        font-family: 'Montserrat';
        font-style: normal;
        font-weight: 500;
        src: url(https://fonts.gstatic.com/s/montserrat/v25/JTUSjIg1_i6t8kCHKm459WRhyzbi.woff2) format('woff2');
    }

    @font-face {
        font-family: 'Montserrat';
        font-style: normal;
        font-weight: 600;
        src: url(https://fonts.gstatic.com/s/montserrat/v25/JTUSjIg1_i6t8kCHKm459W1hyzbi.woff2) format('woff2');
    }

    @font-face {
        font-family: 'Montserrat';
        font-style: normal;
        font-weight: 700;
        src: url(https://fonts.gstatic.com/s/montserrat/v25/JTUSjIg1_i6t8kCHKm459WZhyzbi.woff2) format('woff2');
    }

    /* Contenedor Principal */
    .container {
        display: flex;
        width: 100%;
        min-height: 100vh;
    }

    /* Columna de ilustración */
    .illustration-column {
        display: none;
        background-color: var(--primary-color);
        background-image: var(--primary-gradient);
        flex: 1;
        justify-content: center;
        align-items: center;
        padding: 3rem 2rem;
        position: relative;
        overflow: hidden;
    }

    .illustration-content {
        position: relative;
        z-index: 5;
        text-align: center;
    }

    .illustration {
        max-width: 80%;
        height: auto;
        filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.15));
    }

    .illustration-text {
        color: var(--text-inverted);
        margin-top: 2.5rem;
        text-align: center;
    }

    .illustration-text h2 {
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 1rem;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .illustration-text p {
        font-size: 1.1rem;
        opacity: 0.9;
        max-width: 90%;
        margin: 0 auto;
        line-height: 1.6;
    }

    /* Columna de formulario */
    .form-column {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 2rem;
        background-color: var(--background-color);
    }

    .form-container {
        background-color: var(--background-color);
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        width: 100%;
        max-width: 580px;
        padding: 2.8rem;
        position: relative;
        overflow: hidden;
        border: 1px solid var(--neutral-light);
    }

    /* Decoración de la forma */
    .form-decoration {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 6px;
        background-image: var(--primary-gradient);
    }

    /* Encabezado del formulario */
    .form-header {
        text-align: center;
        margin-bottom: 2.8rem;
    }

    .form-header h1 {
        font-size: 2.2rem;
        color: var(--primary-color);
        margin-bottom: 0.75rem;
        font-weight: 700;
        letter-spacing: -0.5px;
    }

    .form-header p {
        color: var(--text-muted);
        font-size: 1.05rem;
    }

    /* Estilos del formulario */
    .form-group {
        margin-bottom: 0.7rem;
        position: relative;
    }

    label {
        display: block;
        font-size: 0.95rem;
        font-weight: 600;
        margin-bottom: 0.6rem;
        color: var(--text-color);
    }

    input,
    select {
        width: 100%;
        padding: 0.95rem 1rem;
        font-size: 1rem;
        border: 2px solid var(--neutral-light);
        border-radius: var(--input-radius);
        background-color: var(--background-color);
        color: var(--text-color);
        transition: var(--transition);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
    }

    input::placeholder {
        color: var(--text-muted);
        font-size: 0.95rem;
    }

    input:hover,
    select:hover {
        border-color: #B8B8B8;
    }

    input:focus,
    select:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
    }

    select {
        appearance: none;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23333333' viewBox='0 0 16 16'%3E%3Cpath d='M8 12l-6-6h12l-6 6z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 16px 12px;
        cursor: pointer;
        padding-right: 2.5rem;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    /* Campo de contraseña */
    .password-container {
        position: relative;
    }

    #toggle-password {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        transition: color 0.2s ease;
    }

    #toggle-password:focus,
    #toggle-password:hover {
        outline: none;
        color: var(--accent-color);
    }

    .eye-icon {
        width: 20px;
        height: 20px;
    }

    .form-description {
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-top: 0.6rem;
    }

    /* Mensajes de error */
    .error-message {
        display: block;
        color: var(--secondary-color);
        font-size: 0.85rem;
        margin-top: 0.5rem;
        font-weight: 500;
        min-height: 1.2rem;
    }

    input.error,
    select.error {
        border-color: var(--secondary-color);
    }

    /* Botón de envío */
    .form-submit {
        margin-top: 1.0rem;
    }

    button[type="submit"] {
        width: 100%;
        padding: 1rem 1.5rem;
        background-color: var(--primary-color);
        color: var(--text-inverted);
        border: none;
        border-radius: var(--input-radius);
        font-size: 1.05rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        letter-spacing: 0.5px;
        box-shadow: 0 4px 12px rgba(0, 51, 102, 0.2);
        position: relative;
        overflow: hidden;
    }

    button[type="submit"]::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to right, transparent, rgba(255, 215, 0, 0.1), transparent);
        transform: translateX(-100%);
        transition: transform 0.6s ease;
    }

    button[type="submit"]:hover {
        background-color: var(--primary-dark);
        box-shadow: 0 6px 16px rgba(0, 51, 102, 0.25);
        transform: translateY(-2px);
    }

    button[type="submit"]:hover::after {
        transform: translateX(100%);
    }

    button[type="submit"]:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.3);
    }

    button[type="submit"]:active {
        transform: translateY(1px);
    }

    button[type="submit"]:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    /* Pie del formulario */
    .form-footer {
        text-align: center;
        margin-top: 2.2rem;
        font-size: 0.95rem;
        color: var(--text-muted);
    }

    .form-footer a {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 600;
        transition: color 0.2s ease;
    }

    .form-footer a:hover {
        color: var(--accent-color);
    }

    /* Notificación Toast */
    .toast {
        position: fixed;
        top: 1.5rem;
        right: 1.5rem;
        padding: 1.2rem 1.5rem;
        background-color: var(--background-color);
        border-radius: var(--input-radius);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        transform: translateX(150%);
        transition: transform 0.3s ease-in-out;
        z-index: 1000;
        max-width: 350px;
        font-weight: 500;
        border: 1px solid var(--neutral-light);
    }

    .toast.show {
        transform: translateX(0);
    }

    .toast.success {
        border-left: 4px solid var(--primary-color);
    }

    .toast.error {
        border-left: 4px solid var(--secondary-color);
    }

    /* Efecto de foco para accesibilidad */
    *:focus-visible {
        outline: 3px solid var(--accent-color);
        outline-offset: 2px;
    }

    /* Ajustes responsivos */
    @media (min-width: 992px) {
        .illustration-column {
            display: flex;
        }

        .form-container {
            margin-left: 2rem;
        }

        .form-row {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 768px) {
        .form-container {
            padding: 2.5rem 2rem;
        }
    }

    @media (max-width: 576px) {
        .form-container {
            padding: 2rem 1.5rem;
            margin: 1rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .form-header h1 {
            font-size: 1.8rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        input,
        select,
        button[type="submit"] {
            padding: 0.85rem;
        }
    }

    /* Accesibilidad */
    @media (prefers-reduced-motion: reduce) {
        * {
            transition: none !important;
        }

        button[type="submit"]::after {
            display: none;
        }
    }

    /* Modo de alto contraste */
    @media (prefers-contrast: high) {
        :root {
            --primary-color: #002244;
            --primary-gradient: none;
            --secondary-color: #990000;
            --accent-color: #cc9900;
            --text-color: #000000;
            --background-color: #FFFFFF;
            --text-inverted: #FFFFFF;
        }

        .illustration-column {
            background-image: none;
            background-color: var(--primary-color);
        }

        button[type="submit"] {
            background-image: none;
            background-color: var(--primary-color);
        }

        button[type="submit"]::after {
            display: none;
        }

        .form-decoration {
            height: 8px;
            background-image: none;
            background-color: var(--primary-color);
        }

        /* Mayor contraste en textos */
        input,
        select,
        label,
        .form-description {
            color: var(--text-color);
        }

        .form-header h1 {
            background: none;
            background-clip: initial;
            -webkit-background-clip: initial;
            -webkit-text-fill-color: var(--primary-color);
            font-weight: 800;
        }
    }
    </style>
</head>

<body>
    <div class="container">
        <!-- Columna de ilustración (visible en pantallas grandes) -->
        <div class="illustration-column">
            <div class="illustration-content">
                <img src="../assets/img/Logo_fondoazul.png" class="illustration" alt="Ilustración educativa">
                <div class="illustration-text">
                    <h2>Prepárate sin miedo para el Saber Pro</h2>
                </div>
            </div>
        </div>

        <!-- Columna del formulario -->
        <div class="form-column">
            <div class="form-container">
                <div class="form-decoration"></div>
                <div class="form-header">
                    <h1>Registro de Usuarios</h1>
                    <p>Crea tu cuenta para comenzar</p>
                </div>

                <form id="registration-form" action="crear_usuario.php" method="post" novalidate>
                    <div class="form-group">
                        <label for="fullName">Nombre Completo</label>
                        <input type="text" id="fullName" name="nombres" placeholder="Ingresa tu nombre completo"
                            required oninput="this.value = this.value.toUpperCase()"
                            style="text-transform: uppercase;" />
                        <span class="error-message" id="fullName-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="studentCode">Código Institucional</label>
                        <input type="text" id="studentCode" name="num_identificacion"
                            placeholder="Ingresa tu código institucional" required
                            oninput="this.value = this.value.toUpperCase()" style="text-transform: uppercase;" />
                        <span class="error-message" id="studentCode-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <div class="password-container">
                            <input type="password" id="password" name="contraseña" placeholder="CREA UNA CONTRASEÑA"
                                required style="text-transform: none;" />
                            <button type="button" id="toggle-password" aria-label="Mostrar/ocultar contraseña">
                                <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20"
                                    height="20" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                        <span class="error-message" id="password-error"></span>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="userType">Tipo de Usuario</label>
                            <select id="userType" name="cboTipoUsuarios" required onchange="handleUserTypeChange()"
                                style="text-transform: uppercase;">
                                <option value="" disabled selected>Selecciona tipo</option>
                                <option value="student">Estudiante</option>
                                <option value="teacher">Docente</option>
                            </select>
                            <span class="error-message" id="userType-error"></span>
                        </div>

                        <div class="form-group" id="semester-group" style="display:none;">
                            <label for="semester">Semestre</label>
                            <select id="semester" name="semestre" style="text-transform: uppercase;">
                                <option value="" disabled selected>Selecciona semestre</option>
                                <option value="OCTAVO">Octavo</option>
                                <option value="NOVENO">Noveno</option>
                                <option value="DÉCIMO">Décimo</option>
                            </select>
                            <span id="semester-error" class="error-message"></span>
                        </div>
                    </div>

                    <div class="form-submit">
                        <button type="submit" id="submit-button">Crear Cuenta</button>
                    </div>

                    <div class="form-footer">
                        <p>¿Ya tienes una cuenta? <a href="../index.php">Iniciar sesión</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="toast" class="toast">
        <div id="toast-content"></div>
    </div>

    <script>
    const togglePasswordButton = document.getElementById('toggle-password');
    const passwordInput = document.getElementById('password');

    togglePasswordButton.addEventListener('click', () => {
        const type = passwordInput.getAttribute('type');
        if (type === 'password') {
            passwordInput.setAttribute('type', 'text');
            // Opcional: cambiar el ícono para indicar que la contraseña está visible
            // Aquí podrías cambiar el SVG o agregar una clase para cambiar el estilo
        } else {
            passwordInput.setAttribute('type', 'password');
            // Opcional: volver a cambiar el ícono para indicar que la contraseña está oculta
        }
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    const mensaje = <?php echo json_encode($mensaje); ?>;
    const tipo = <?php echo json_encode($mensaje_tipo); ?>;

    if (mensaje) {
        Swal.fire({
            icon: tipo || 'info', // 'success', 'error', 'warning', 'info', 'question'
            title: mensaje,
            confirmButtonText: 'Aceptar'
        });
    }
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('registration-form');
        const fullNameInput = document.getElementById('fullName');
        const studentCodeInput = document.getElementById('studentCode');
        const passwordInput = document.getElementById('password');
        const userTypeSelect = document.getElementById('userType');
        const semesterSelect = document.getElementById('semester');
        const semesterGroup = document.getElementById('semester-group');
        
        // Función para controlar visibilidad y required del semestre
        function handleUserTypeChange() {
            const userType = userTypeSelect.value;
            if (userType === 'student') {
                semesterGroup.style.display = 'block';
                semesterSelect.setAttribute('required', 'required');
            } else {
                semesterGroup.style.display = 'none';
                semesterSelect.removeAttribute('required');
                semesterSelect.value = '';
                document.getElementById('semester-error').textContent = '';
                semesterSelect.classList.remove('error');
            }
        }

        // Ejecutar al cargar la página para ajustar visibilidad
        handleUserTypeChange();

        // Escuchar cambio en tipo de usuario
        userTypeSelect.addEventListener('change', handleUserTypeChange);

        // Validación al enviar el formulario
        form.addEventListener('submit', function(e) {
            let valid = true;

            // Limpiar mensajes previos
            document.getElementById('fullName-error').textContent = '';
            document.getElementById('studentCode-error').textContent = '';
            document.getElementById('password-error').textContent = '';
            document.getElementById('userType-error').textContent = '';
            document.getElementById('semester-error').textContent = '';

            // Validar nombre completo
            if (fullNameInput.value.trim() === '') {
                document.getElementById('fullName-error').textContent =
                    'El nombre completo es obligatorio.';
                fullNameInput.classList.add('error');
                valid = false;
            } else {
                fullNameInput.classList.remove('error');
            }

            // Validar código institucional
            if (studentCodeInput.value.trim() === '') {
                document.getElementById('studentCode-error').textContent =
                    'El código institucional es obligatorio.';
                studentCodeInput.classList.add('error');
                valid = false;
            } else {
                studentCodeInput.classList.remove('error');
            }

            // Validar contraseña (mínimo 8 caracteres)
            if (passwordInput.value.length < 8) {
                document.getElementById('password-error').textContent =
                    'La contraseña debe tener al menos 8 caracteres.';
                passwordInput.classList.add('error');
                valid = false;
            } else {
                passwordInput.classList.remove('error');
            }

            // Validar tipo de usuario
            if (!userTypeSelect.value) {
                document.getElementById('userType-error').textContent =
                    'Selecciona un tipo de usuario.';
                userTypeSelect.classList.add('error');
                valid = false;
            } else {
                userTypeSelect.classList.remove('error');
            }

            // Validar semestre solo si es estudiante
            if (userTypeSelect.value === 'student') {
                if (!semesterSelect.value) {
                    document.getElementById('semester-error').textContent = 'Selecciona el semestre.';
                    semesterSelect.classList.add('error');
                    valid = false;
                } else {
                    semesterSelect.classList.remove('error');
                }
            }

            // Prevenir envío si hay errores
            if (!valid) {
                e.preventDefault();
            }
        });
    });
    </script>
</body>

</html>