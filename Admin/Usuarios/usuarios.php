<?php
session_start();
include("../../base de datos/con_db.php");

// Validar que el usuario esté logueado y sea profesor
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'Administrador') {
    header('Location: ../../index.php');
    exit;
}

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link href="../../assets/img/favicon.png" rel="icon">
    <link href="../../assets/img/favicon.png" rel="apple-touch-icon">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/src_usuarios/css/styles.css">
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
                        <li><a href="../index_admin.php#projects" class="nav-link">Inicio</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <h1>Gestión de Usuarios</h1>
        
        <form class="filtro-form" method="get" action="">
            <div class="form-group">
                <label for="filtro_nombre" class="form-label">Nombre:</label>
                <input type="text" name="filtro_nombre" id="filtro_nombre" class="form-control search-input" data-table="estudiantes-table" data-column="2" value="<?php echo htmlspecialchars($filtro_nombre); ?>" placeholder="Buscar por nombre">
            </div>

            <div class="form-group">
                <label for="filtro_codigo" class="form-label">Código:</label>
                <input type="text" name="filtro_codigo" id="filtro_codigo" class="form-control search-input" data-table="estudiantes-table" data-column="3" value="<?php echo htmlspecialchars($filtro_codigo); ?>" placeholder="Buscar por código">
            </div>

            <div class="form-group">
                <label for="filtro_semestre" class="form-label">Semestre:</label>
                <select name="filtro_semestre" id="filtro_semestre" class="form-control">
                    <option value="">Todos</option>
                    <?php foreach ($semestres as $sem): ?>
                        <option value="<?php echo htmlspecialchars($sem); ?>" <?php if ($filtro_semestre === $sem) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($sem); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filtro-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search btn-icon"></i>Filtrar
                </button>
                <a href="usuarios.php" class="reset-link">
                    <i class="fas fa-times-circle"></i> Limpiar
                </a>
            </div>
        </form>

        <h2>Usuarios Estudiantes</h2>
        <div class="table-container">
            <table class="table table-striped" id="estudiantes-table">
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
                                <td class="actions">
                                    <a href="editar_usuarios.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-edit">
                                        <i class="fas fa-edit btn-icon"></i>Editar
                                    </a>
                                    <a href="eliminar_usuarios.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-delete">
                                        <i class="fas fa-trash-alt btn-icon"></i>Eliminar
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="empty-state">No hay estudiantes registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="pagination" id="estudiantes-pagination"></div>

        <h2>Usuarios Docentes</h2>
        <div class="table-container">
            <table class="table table-striped" id="docentes-table">
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
                                <td class="actions">
                                    <a href="editar_usuarios.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-edit">
                                        <i class="fas fa-edit btn-icon"></i>Editar
                                    </a>
                                    <a href="eliminar_usuarios.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-delete">
                                        <i class="fas fa-trash-alt btn-icon"></i>Eliminar
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="empty-state">No hay docentes registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="pagination" id="docentes-pagination"></div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <p>&copy; 2024 SABERQUEST. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="../../assets/src_usuarios/js/scripts.js"></script>
</body>
</html>
