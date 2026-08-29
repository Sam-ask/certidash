<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$stmtCursos = $pdo->query("SELECT DISTINCT curso_nombre FROM certificaciones ORDER BY curso_nombre");
$lista_cursos = $stmtCursos->fetchAll(PDO::FETCH_COLUMN);

$stmtMeses = $pdo->query("SELECT DISTINCT DATE_FORMAT(fecha_registro, '%Y-%m') as mes_val, DATE_FORMAT(fecha_registro, '%M %Y') as mes_label FROM participantes ORDER BY mes_val DESC");
$lista_meses = $stmtMeses->fetchAll(PDO::FETCH_ASSOC);

$where = [];
$params = [];

$filtro_curso = $_GET['curso'] ?? '';
$filtro_mes = $_GET['mes'] ?? '';

if ($filtro_curso !== '') {
    $where[] = "c.curso_nombre = :curso";
    $params['curso'] = $filtro_curso;
}

if ($filtro_mes !== '') {
    $where[] = "DATE_FORMAT(p.fecha_registro, '%Y-%m') = :mes";
    $params['mes'] = $filtro_mes;
}

$whereClause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

$sql = "SELECT p.nombre_completo, p.correo, p.institucion, p.fecha_registro, c.curso_nombre, c.terminado 
        FROM participantes p
        JOIN certificaciones c ON p.correo = c.correo_participante
        $whereClause
        ORDER BY p.fecha_registro DESC";

$stmtResultados = $pdo->prepare($sql);
$stmtResultados->execute($params);
$resultados = $stmtResultados->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Inscripciones - CertiDash</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --certi-navy: #0f172a; --dash-teal: #0f766e; --bg-color: #f8fafc; 
            --border-color: #e2e8f0; --text-main: #334155; --text-muted: #64748b;
        }
        
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-color); margin: 0; padding: 0; color: var(--text-main); -webkit-font-smoothing: antialiased;}
        
        .navbar { background-color: var(--certi-navy); color: white; padding: 0.75rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid var(--dash-teal); }
        .navbar-brand { display: flex; align-items: center; gap: 12px; }
        .navbar-brand img { height: 36px; border-radius: 4px; }
        .navbar-brand h1 { margin: 0; font-size: 1.125rem; font-weight: 600; letter-spacing: 0.025em; }
        .nav-actions { display: flex; gap: 10px; }
        .btn-nav { background-color: transparent; color: #cbd5e1; text-decoration: none; padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.875rem; font-weight: 500; border: 1px solid var(--text-muted); transition: all 0.2s ease; display: flex; align-items: center; gap: 6px; }
        .btn-nav:hover { background-color: rgba(255,255,255,0.1); color: white; border-color: white; }
        
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 1.5rem; }
        
        .page-header { margin-bottom: 2rem; }
        .page-header h2 { margin: 0; color: var(--certi-navy); font-size: 1.5rem; font-weight: 600; }
        .page-header p { margin: 0.25rem 0 0 0; color: var(--text-muted); font-size: 0.95rem; }
        
        .card { background-color: #ffffff; padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border-color); box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); margin-bottom: 1.5rem; }
        
        .filter-form { display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap; }
        .form-group { display: flex; flex-direction: column; gap: 5px; flex: 1; min-width: 200px;}
        .form-group label { font-size: 0.85rem; font-weight: 600; color: var(--text-main); }
        .form-group select { padding: 0.65rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: 'Inter', sans-serif; font-size: 0.9rem; color: var(--certi-navy); background-color: white; outline: none; }
        .form-group select:focus { border-color: var(--dash-teal); box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.15); }
        .btn-filter { padding: 0.65rem 1.5rem; background-color: var(--dash-teal); color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: 0.2s; height: 38px;}
        .btn-filter:hover { background-color: #115e59; }
        .btn-clear { padding: 0.65rem 1.5rem; background-color: white; color: var(--text-muted); border: 1px solid var(--border-color); border-radius: 6px; font-weight: 500; cursor: pointer; text-decoration: none; display: flex; align-items: center; transition: 0.2s; height: 38px; box-sizing: border-box;}
        .btn-clear:hover { background-color: var(--bg-color); color: var(--certi-navy); }

        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem; }
        th { background-color: var(--bg-color); color: var(--text-muted); font-weight: 600; padding: 1rem; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; border-bottom: 1px solid var(--border-color); }
        td { padding: 1rem; border-bottom: 1px solid var(--border-color); color: var(--text-main); }
        tr:last-child td { border-bottom: none; }
        tr:hover { background-color: #f8fafc; }
        
        .badge { padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        
        .empty-state { text-align: center; padding: 3rem 1rem; color: var(--text-muted); }
        .empty-state i { font-size: 3rem; margin-bottom: 1rem; color: #cbd5e1; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="navbar-brand">
            <img src="../assets/logo.jpeg" alt="Logo CertiDash" onerror="this.src='https://placehold.co/100x100?text=Logo'">
            <h1>CertiDash</h1>
        </div>
        <div class="nav-actions">
            <a href="dashboard_capacitacion.php" class="btn-nav"><i class="fa-solid fa-chart-pie"></i> Dashboards</a>
            <a href="dashboard.php" class="btn-nav"><i class="fa-solid fa-house"></i> Menú Principal</a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Directorio de Alumnos por Curso</h2>
            <p>Filtra y visualiza el padrón de estudiantes inscritos en la plataforma.</p>
        </div>

        <div class="card">
            <form action="reporte_cursos.php" method="GET" class="filter-form">
                <div class="form-group">
                    <label for="curso">Filtrar por Curso</label>
                    <select name="curso" id="curso">
                        <option value="">Todos los cursos</option>
                        <?php foreach($lista_cursos as $c): ?>
                            <option value="<?php echo htmlspecialchars($c); ?>" <?php if($filtro_curso === $c) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($c); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="mes">Filtrar por Mes de Inscripción</label>
                    <select name="mes" id="mes">
                        <option value="">Todos los meses</option>
                        <?php foreach($lista_meses as $m): ?>
                            <option value="<?php echo $m['mes_val']; ?>" <?php if($filtro_mes === $m['mes_val']) echo 'selected'; ?>>
                                <?php echo $m['mes_val']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn-filter"><i class="fa-solid fa-filter"></i> Aplicar Filtros</button>
                <a href="reporte_cursos.php" class="btn-clear">Limpiar</a>
            </form>
        </div>

        <div class="card" style="padding: 0;">
            <div class="table-container">
                <?php if(count($resultados) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Alumno</th>
                                <th>Correo Electrónico</th>
                                <th>Institución</th>
                                <th>Curso Inscrito</th>
                                <th>Fecha Registro</th>
                                <th>Estatus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($resultados as $row): ?>
                                <tr>
                                    <td style="font-weight: 500;"><?php echo htmlspecialchars($row['nombre_completo']); ?></td>
                                    <td><?php echo htmlspecialchars($row['correo']); ?></td>
                                    <td><?php echo htmlspecialchars($row['institucion']); ?></td>
                                    <td><?php echo htmlspecialchars($row['curso_nombre']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['fecha_registro'])); ?></td>
                                    <td>
                                        <?php if($row['terminado'] == 1 || $row['terminado'] === '1'): ?>
                                            <span class="badge badge-success">Terminado</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">En progreso</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-folder-open"></i>
                        <h3>No se encontraron registros</h3>
                        <p>No hay alumnos que coincidan con los filtros seleccionados.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
    </div>

</body>
</html>