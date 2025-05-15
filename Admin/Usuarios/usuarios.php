<?php
session_start();
include("../../base de datos/con_db.php");

// Obtener filtros del formulario
$filtro_nombre = isset($_GET['filtro_nombre']) ? trim($_GET['filtro_nombre']) : '';
$filtro_codigo = isset($_GET['filtro_codigo']) ? trim($_GET['filtro_codigo']) : '';
$filtro_semestre = isset($_GET['filtro_semestre']) ? trim($_GET['filtro_semestre']) : '';

// Obtener los semestres distintos existentes en la base de datos (solo estudiantes)
$semestres = [];
$sql_semestres = "SELECT DISTINCT semestre FROM usuarios WHERE id_rol = '3' AND semestre IS NOT NULL AND semestre <> '' ORDER BY semestre ASC";
$result_semestres = $conex->query($sql_semestres);
if ($result_semestres && $result_semestres->num_rows > 0) {
    while ($row = $result_semestres->fetch_assoc()) {
        $semestres[] = $row['semestre'];
    }
}

// Consulta para estudiantes (rol = '3')
$where_estudiantes = "WHERE id_rol = '3'";
if ($filtro_nombre !== '') {
    $where_estudiantes .= " AND nombre LIKE '%" . $conex->real_escape_string($filtro_nombre) . "%'";
}
if ($filtro_codigo !== '') {
    $where_estudiantes .= " AND codigo_estudiantil LIKE '%" . $conex->real_escape_string($filtro_codigo) . "%'";
}
if ($filtro_semestre !== '') {
    $where_estudiantes .= " AND semestre = '" . $conex->real_escape_string($filtro_semestre) . "'";
}
$sql_estudiantes = "SELECT id, nombre, codigo_estudiantil, contraseña, id_rol, semestre FROM usuarios $where_estudiantes";
$result_estudiantes = $conex->query($sql_estudiantes);

// Consulta para docentes (rol = '2')
$where_docentes = "WHERE id_rol = '2'";
if ($filtro_nombre !== '') {
    $where_docentes .= " AND nombre LIKE '%" . $conex->real_escape_string($filtro_nombre) . "%'";
}
if ($filtro_codigo !== '') {
    $where_docentes .= " AND codigo_estudiantil LIKE '%" . $conex->real_escape_string($filtro_codigo) . "%'";
}
// El filtro de semestre no aplica a docentes
$sql_docentes = "SELECT id, nombre, codigo_estudiantil, contraseña, id_rol FROM usuarios $where_docentes";
$result_docentes = $conex->query($sql_docentes);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestion de Usuarios</title>
    <link href="../../assets/img/favicon.png" rel="icon">
    <link href="../../assets/img/favicon.png" rel="apple-touch-icon">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f7f7f7;
        }
        a{
            text-decoration: none;
            color: inherit;
        }
        .container {
            --container-padding: 0 20px;
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--container-padding);
            }
        
        .header {
            --primary-color: #003366;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            padding: 20px 0;
            background-color: var(--primary-color);
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }

            .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            }

            .logo a {
            display: flex;
            align-items: center;
            color: white;
            }

            .logo-img {
            height: 50px;
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
            }

            .nav-link:hover {
            --accent-color: #ffffff;
            color: var(--accent-color);
            }

            .nav-link::after {
            --accent-color: #ffffff;
            --transition: all 0.3s ease;
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--accent-color);
            transition: var(--transition);
            }

            .nav-link:hover::after {
            width: 100%;
            }

            .hamburger {
            display: none;
            cursor: pointer;
            }

            .bar {
            display: block;
            width: 25px;
            height: 3px;
            margin: 5px auto;
            background-color: white;
            transition: var(--transition);
            }

        .footer {
            --primary-color: #003366;
            background-color: var(--primary-color);
            color: white;
            padding: 30px 0;
            text-align: center;
            }

            .footer-content {
            display: flex;
            justify-content: center;
            align-items: center;
            }

            .footer p {
            font-size: 0.9rem;
            opacity: 0.9;
            }

        main {
            padding: 30px 10vw;
            min-height: 70vh;
            background: #fff;
        }
        h2 {
            color: #003366;
            margin-top: 40px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
            background: #fafafa;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px 8px;
            text-align: left;
        }
        th {
            background: #e0e7ef;
            color: #003366;
        }
        tr:nth-child(even) {
            background: #f2f6fa;
        }
        .acciones {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 6px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1em;
            text-decoration: none;
            display: inline-block;
        }
        .btn-editar {
            background: #ffc107;
            color: #333;
        }
        .btn-eliminar {
            background: #dc3545;
            color: #fff;
        }
        .filtro-form {
            margin: 30px 0 10px 0;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            align-items: center;
        }
        .filtro-form label {
            font-weight: bold;
            color: #003366;
        }
        .filtro-form input[type="text"], .filtro-form select {
            padding: 6px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .filtro-form button {
            padding: 6px 16px;
            border: none;
            border-radius: 4px;
            background: #003366;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
        }
        @media (max-width: 700px) {
            main {
                padding: 20px 2vw;
            }
            th, td {
                font-size: 0.95em;
                padding: 8px 4px;
            }
            .filtro-form {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <header class="header" id="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="../index_admin.php">
                        <img src="../../assets/img/Logo_fondoazul.png" alt="Logo SaberQuest" class="logo-img">
                    </a>
                </div>                
                <nav class="nav">
                    <ul class="nav-list">
                        <li><a href="../index_admin.php" class="nav-link">Inicio</a></li>
                    </ul>
                </nav>
                <!-- Mobile menu toggle -->

            </div>
        </div>
    </header>
    <main>
        <br><br><br><br><br><br>
         <h1>Gestionar Usuarios</h1>
        <form class="filtro-form" method="get" action="">
            <label for="filtro_nombre">Nombre:</label>
            <input type="text" name="filtro_nombre" id="filtro_nombre" value="<?php echo htmlspecialchars($filtro_nombre); ?>" placeholder="Buscar por nombre">

            <label for="filtro_codigo">Código:</label>
            <input type="text" name="filtro_codigo" id="filtro_codigo" value="<?php echo htmlspecialchars($filtro_codigo); ?>" placeholder="Buscar por código">

            <label for="filtro_semestre">Semestre:</label>
            <select name="filtro_semestre" id="filtro_semestre">
                <option value="">Todos</option>
                <?php foreach ($semestres as $sem): ?>
                    <option value="<?php echo htmlspecialchars($sem); ?>" <?php if ($filtro_semestre === $sem) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($sem); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Filtrar</button>
            <a href="usuarios.php" style="margin-left:10px;color:#003366;text-decoration:underline;">Limpiar</a>
        </form>

        <h2>Usuarios Estudiantes</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Código Estudiantil</th>
                    <th>Semestre</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_estudiantes && $result_estudiantes->num_rows > 0): ?>
                    <?php while($row = $result_estudiantes->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($row['codigo_estudiantil']); ?></td>
                            <td><?php echo htmlspecialchars($row['semestre']); ?></td>
                            <td class="acciones">
                                <a href="editar_usuario.php?id=<?php echo $row['id']; ?>" class="btn btn-editar">Editar</a>
                                <a href="eliminar_usuario.php?id=<?php echo $row['id']; ?>" class="btn btn-eliminar" onclick="return confirm('¿Seguro que deseas eliminar este usuario?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">No hay estudiantes registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h2>Usuarios Docentes</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Código Docente</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_docentes && $result_docentes->num_rows > 0): ?>
                    <?php while($row = $result_docentes->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($row['codigo_estudiantil']); ?></td>
                            <td class="acciones">
                                <a href="editar_usuario.php?id=<?php echo $row['id']; ?>" class="btn btn-editar">Editar</a>
                                <a href="eliminar_usuario.php?id=<?php echo $row['id']; ?>" class="btn btn-eliminar" onclick="return confirm('¿Seguro que deseas eliminar este usuario?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4">No hay docentes registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <p>&copy; 2024 SABERQUEST. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

</body>
</html>

<?php
if (isset($conex)) {
    $conex->close();
}
?>
