<?php
session_start();
require_once '../config/db.php'; 

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

try {
    $total_usuarios = $pdo->query("SELECT COUNT(id) FROM participantes")->fetchColumn() ?: 0;
    
    $cursos_terminados = $pdo->query("SELECT COUNT(id) FROM certificaciones WHERE terminado = 1")->fetchColumn() ?: 0;
    
    $stmtUltimos = $pdo->query("
        SELECT nombre_completo, DATE_FORMAT(fecha_registro, '%d/%m/%Y') as fecha 
        FROM participantes 
        ORDER BY fecha_registro DESC 
        LIMIT 3
    ");
    $ultimos_registros = $stmtUltimos->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $total_usuarios = 0;
    $cursos_terminados = 0;
    $ultimos_registros = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - CertiDash</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --certi-navy: #0f172a; 
            --dash-teal: #0f766e;  
            --bg-color: #f8fafc;   
            --border-color: #e2e8f0;
            --text-main: #334155;
            --text-muted: #64748b;
        }

        body { font-family: 'Inter', sans-serif; background-color: var(--bg-color); margin: 0; padding: 0; color: var(--text-main); }
        
        .navbar { background-color: var(--certi-navy); color: white; padding: 0.75rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid var(--dash-teal); }
        .navbar-brand { display: flex; align-items: center; gap: 12px; }
        .navbar-brand img { height: 36px; border-radius: 4px; } 
        .navbar-brand h1 { margin: 0; font-size: 1.125rem; font-weight: 600; letter-spacing: 0.025em; }
        .btn-logout { background-color: transparent; color: #cbd5e1; text-decoration: none; padding: 0.5rem 1rem; border-radius: 6px; font-weight: 500; font-size: 0.875rem; border: 1px solid transparent; transition: all 0.2s ease; }
        .btn-logout:hover { color: #ef4444; background-color: #fef2f2; border-color: #fecaca; }
        
        .container { max-width: 1100px; margin: 3rem auto; padding: 0 1.5rem; }
        
        .welcome-card { background-color: #ffffff; padding: 2rem 2.5rem; border-radius: 8px; border: 1px solid var(--border-color); box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;}
        .welcome-text h2 { margin-top: 0; margin-bottom: 0.5rem; color: var(--certi-navy); font-size: 1.5rem; font-weight: 600; }
        .welcome-text p { margin: 0; color: var(--text-muted); font-size: 0.95rem; }
        
        .btn-outline { border: 1px solid var(--border-color); color: var(--text-main); padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.875rem; font-weight: 500; transition: 0.2s; background: white;}
        .btn-outline:hover { background-color: var(--bg-color); border-color: #cbd5e1; }

        .dashboard-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; }
        @media (max-width: 850px) { .dashboard-layout { grid-template-columns: 1fr; } }

        .tools-grid { display: grid; grid-template-columns: 1fr; gap: 1.5rem; }
        .tool-card { background-color: #ffffff; padding: 1.5rem 2rem; border-radius: 8px; border: 1px solid var(--border-color); box-shadow: 0 1px 3px 0 rgba(0,0,0,0.1); position: relative; transition: 0.2s; display: flex; flex-direction: column; align-items: flex-start;}
        .tool-card::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; border-radius: 8px 0 0 8px; }
        .card-upload::before { background-color: var(--certi-navy); }
        .card-charts::before { background-color: var(--dash-teal); }
        
        .tool-card h3 { color: var(--certi-navy); font-size: 1.125rem; font-weight: 600; margin-top: 0; margin-bottom: 0.5rem; }
        .tool-card p { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem; line-height: 1.5; }
        
        .btn-module { padding: 0.5rem 1rem; color: white; text-decoration: none; border-radius: 6px; font-weight: 500; font-size: 0.875rem; transition: 0.2s; margin-top: auto;}
        .btn-upload { background-color: var(--certi-navy); }
        .btn-upload:hover { background-color: #1e293b; }
        .btn-charts { background-color: var(--dash-teal); }
        .btn-charts:hover { background-color: #115e59; }

        .sidebar { display: flex; flex-direction: column; gap: 1.5rem; }
        
        .widget-card { background-color: #ffffff; padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border-color); box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); }
        .widget-card h4 { margin: 0 0 1rem 0; font-size: 0.875rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; }
        
        .stat-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--bg-color); }
        .stat-row:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
        .stat-label { font-size: 0.9rem; font-weight: 500; }
        .stat-value { font-size: 1.25rem; font-weight: 700; color: var(--certi-navy); }

        .activity-list { list-style: none; padding: 0; margin: 0; }
        .activity-item { display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px dashed var(--border-color); font-size: 0.875rem; }
        .activity-item:last-child { border-bottom: none; padding-bottom: 0; }
        .activity-name { font-weight: 500; color: var(--text-main); }
        .activity-date { color: var(--text-muted); font-size: 0.8rem;}

    </style>
</head>
<body>

    <nav class="navbar">
        <div class="navbar-brand">
            <img src="../assets/logo.jpeg" alt="Logo CertiDash">
            <h1>CertiDash</h1>
        </div>
        <a href="../controllers/logout.php" class="btn-logout">Cerrar sesión</a>
    </nav>

    <div class="container">
        
        
     <div class="welcome-card">
    <div class="welcome-text">
        <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h2>
        <p>Centro de administración y cruce de datos.</p>
    </div>
    
    <!-- Botones de atajo rápido -->
    <div class="d-flex gap-2 align-items-center" style="display: flex; gap: 10px; flex-wrap: wrap;">
        
        <a href="registro.php" target="_blank" class="btn-outline">Ver portal de alumnos ↗</a>
        
        <!-- Botón al nuevo Directorio -->
        <a href="reporte_cursos.php" class="btn-outline" style="color: #2563eb; border-color: #2563eb;">
            <i class="fa-solid fa-address-book"></i> Directorio
        </a>

        <!-- Botón de Excel -->
        <a href="../controllers/ExcelController.php?action=inventario" target="_blank" class="btn-outline" style="color: #198754; border-color: #198754;">
            <i class="bi bi-file-earmark-excel"></i> Exportar Excel
        </a>
        
    </div>
</div>

        <div class="dashboard-layout">
            
            <div class="tools-grid">
                <div class="tool-card card-upload">
                    <h3>Consolidación de Datos (ETL)</h3>
                    <p>Actualiza la base de datos cruzando los archivos de inscripciones y certificados aprobados.</p>
                    <a href="upload.php" class="btn-module btn-upload">Procesar Archivos</a>
                </div>
                
                <div class="tool-card card-charts">
                    <h3>Inteligencia de Negocios</h3>
                    <p>Visualiza el rendimiento de los cursos, tasas de aprobación y demografía de los alumnos inscritos.</p>
                    <a href="dashboard_capacitacion.php" class="btn-module btn-charts">Abrir Panel Analítico</a>
                </div>
            </div>

            <div class="sidebar">
                
                <div class="widget-card">
                    <h4>Resumen Global</h4>
                    <div class="stat-row">
                        <span class="stat-label">Total Alumnos</span>
                        <span class="stat-value"><?php echo number_format($total_usuarios); ?></span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Cursos Aprobados</span>
                        <span class="stat-value" style="color: var(--dash-teal);"><?php echo number_format($cursos_terminados); ?></span>
                    </div>
                </div>

                <div class="widget-card">
                    <h4>Últimos Registros</h4>
                    <?php if (empty($ultimos_registros)): ?>
                        <p style="font-size: 0.85rem; color: var(--text-muted);">No hay alumnos registrados aún.</p>
                    <?php else: ?>
                        <ul class="activity-list">
                            <?php foreach($ultimos_registros as $reg): ?>
                                <li class="activity-item">
                                    <span class="activity-name"><?php echo htmlspecialchars($reg['nombre_completo']); ?></span>
                                    <span class="activity-date"><?php echo $reg['fecha']; ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

            </div>

        </div>
    </div>

</body>
</html>