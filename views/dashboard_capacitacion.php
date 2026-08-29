<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['usuario_id'])) { header("Location: login.php"); exit(); }


$kpi_usuarios = $pdo->query("SELECT COUNT(id) FROM participantes")->fetchColumn() ?: 0;
$kpi_terminados = $pdo->query("SELECT COUNT(id) FROM certificaciones WHERE terminado = 1")->fetchColumn() ?: 0;
$total_cert = $pdo->query("SELECT COUNT(id) FROM certificaciones")->fetchColumn() ?: 0;
$kpi_tasa = $total_cert > 0 ? round(($kpi_terminados / $total_cert) * 100, 1) : 0;

$stmtMes = $pdo->query("SELECT DATE_FORMAT(fecha_registro, '%Y-%m-%d') AS dia, COUNT(id) AS total FROM participantes GROUP BY dia ORDER BY dia ASC");
$dias = []; $registros_dia = [];
while($row = $stmtMes->fetch(PDO::FETCH_ASSOC)) { $dias[] = $row['dia']; $registros_dia[] = $row['total']; }

$stmtMedio = $pdo->query("SELECT medio_contacto, COUNT(id) AS total FROM participantes WHERE medio_contacto IS NOT NULL AND medio_contacto != '' GROUP BY medio_contacto");
$medios = []; $totales_medio = [];
while($row = $stmtMedio->fetch(PDO::FETCH_ASSOC)) { $medios[] = $row['medio_contacto']; $totales_medio[] = $row['total']; }


$promedio_edad = $pdo->query("SELECT ROUND(AVG(edad)) FROM participantes")->fetchColumn() ?: 0;
$total_instituciones = $pdo->query("SELECT COUNT(DISTINCT institucion) FROM participantes WHERE institucion != 'Independiente' AND institucion != ''")->fetchColumn() ?: 0;


$cursos_desempeno = []; 
$terminados_data = []; 
$progreso_data = [];

$stmtDesempeno = $pdo->query("
    SELECT curso_nombre, 
           SUM(CASE WHEN terminado = 1 OR terminado = '1' THEN 1 ELSE 0 END) as terminados,
           SUM(CASE WHEN terminado = 0 OR terminado = '0' THEN 1 ELSE 0 END) as en_progreso
    FROM certificaciones 
    GROUP BY curso_nombre 
    ORDER BY COUNT(*) DESC LIMIT 5
");

if ($stmtDesempeno) {
    while($row = $stmtDesempeno->fetch(PDO::FETCH_ASSOC)) { 
        $cursos_desempeno[] = $row['curso_nombre']; 
        $terminados_data[] = $row['terminados'];
        $progreso_data[] = $row['en_progreso'];
    }
}

$stmtInst = $pdo->query("SELECT institucion, COUNT(id) as total FROM participantes WHERE institucion != 'Independiente' AND institucion != '' GROUP BY institucion ORDER BY total DESC LIMIT 5");
$instituciones = []; $totales_inst = [];
while($row = $stmtInst->fetch(PDO::FETCH_ASSOC)) { $instituciones[] = $row['institucion']; $totales_inst[] = $row['total']; }

$stmtEdad = $pdo->query("
    SELECT 
        CASE 
            WHEN edad < 20 THEN 'Menores de 20'
            WHEN edad BETWEEN 20 AND 29 THEN '20 - 29 años'
            WHEN edad BETWEEN 30 AND 39 THEN '30 - 39 años'
            ELSE '40+ años' 
        END as rango, 
        COUNT(id) as total 
    FROM participantes GROUP BY rango ORDER BY MIN(edad)
");
$rangos_edad = []; $totales_edad = [];
while($row = $stmtEdad->fetch(PDO::FETCH_ASSOC)) { $rangos_edad[] = $row['rango']; $totales_edad[] = $row['total']; }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Analítico - CertiDash</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --certi-navy: #0f172a; --dash-teal: #0f766e; --success: #059669; --primary: #2563eb; 
            --warning: #f59e0b; --purple: #7c3aed; --bg-color: #f8fafc; --card-bg: #ffffff;
            --text-main: #334155; --text-muted: #64748b; --border-color: #e2e8f0;
        }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-color); margin: 0; padding: 0; color: var(--text-main); -webkit-font-smoothing: antialiased; }
        .navbar { background-color: var(--certi-navy); color: white; padding: 0.75rem 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid var(--dash-teal); position: sticky; top: 0; z-index: 100; }
        .navbar-brand { display: flex; align-items: center; gap: 12px; }
        .navbar-brand img { height: 36px; border-radius: 4px; }
        .navbar-brand h1 { margin: 0; font-size: 1.125rem; font-weight: 600; letter-spacing: 0.025em; }
        .nav-actions { display: flex; gap: 10px; }
        .btn-nav { background-color: transparent; color: #cbd5e1; text-decoration: none; padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.875rem; font-weight: 500; border: 1px solid var(--text-muted); transition: all 0.2s ease; display: flex; align-items: center; gap: 6px; }
        .btn-nav:hover { background-color: rgba(255,255,255,0.1); color: white; border-color: white; }
        .btn-teal { background-color: var(--dash-teal); color: white; border-color: var(--dash-teal); }
        .btn-teal:hover { background-color: #115e59; border-color: #115e59; }
        .dashboard-container { max-width: 1400px; margin: 0 auto 3rem; padding: 0 1.5rem; }
        .page-header { padding: 2rem 0 1.5rem 0; display: flex; justify-content: space-between; align-items: flex-end; }
        .page-header h2 { margin: 0; color: var(--certi-navy); font-size: 1.5rem; font-weight: 600; }
        .page-header p { margin: 0.25rem 0 0 0; color: var(--text-muted); font-size: 0.875rem; }
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem; }
        .charts-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem; }
        .card { background: var(--card-bg); padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); border: 1px solid var(--border-color); }
        .kpi-card { display: flex; align-items: center; justify-content: space-between; padding: 1.25rem; }
        .kpi-info { display: flex; flex-direction: column; gap: 4px; }
        .kpi-card h3 { margin: 0; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em; }
        .kpi-card .number { font-size: 1.75rem; font-weight: 700; color: var(--certi-navy); margin: 0; line-height: 1; }
        .kpi-icon { width: 42px; height: 42px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; }
        .icon-navy { background-color: #f1f5f9; color: var(--text-main); }
        .icon-blue { background-color: #eff6ff; color: var(--primary); }
        .icon-teal { background-color: #f0fdf4; color: var(--success); }
        .icon-orange { background-color: #fffbeb; color: var(--warning); }
        .icon-purple { background-color: #f5f3ff; color: var(--purple); }
        .chart-header { margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;}
        .chart-header h3 { color: var(--certi-navy); font-size: 1.05rem; margin: 0; font-weight: 600; }
        .chart-container { position: relative; width: 100%; height: 250px;}
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="navbar-brand">
            <img src="../assets/logo.jpeg" alt="Logo" onerror="this.src='https://placehold.co/100x100?text=Logo'">
            <h1>CertiDash Analítico</h1>
        </div>
        <div class="nav-actions">
            <a href="upload.php" class="btn-nav"><i class="fa-solid fa-cloud-arrow-up"></i> ETL</a>
            <a href="../controllers/exportar_excel.php" class="btn-nav"><i class="fa-solid fa-file-csv"></i> Exportar</a>
            <a href="dashboard.php" class="btn-nav btn-teal"><i class="fa-solid fa-house"></i> Menú</a>
        </div>
    </nav>

    <div class="dashboard-container">
        
        <div class="page-header">
            <div>
                <h2>Inteligencia de Negocios y Capacitación</h2>
                <p>Métricas avanzadas de desempeño corporativo y demografía.</p>
            </div>
        </div>
        
        <div class="kpi-grid">
            <div class="card kpi-card">
                <div class="kpi-info"><h3>Total Alumnos</h3><p class="number"><?php echo number_format($kpi_usuarios); ?></p></div>
                <div class="kpi-icon icon-navy"><i class="fa-solid fa-users"></i></div>
            </div>
            <div class="card kpi-card">
                <div class="kpi-info"><h3>Inscripciones</h3><p class="number"><?php echo number_format($total_cert); ?></p></div>
                <div class="kpi-icon icon-blue"><i class="fa-solid fa-clipboard-list"></i></div>
            </div>
            <div class="card kpi-card">
                <div class="kpi-info"><h3>Tasa Finalización</h3><p class="number"><?php echo $kpi_tasa; ?>%</p></div>
                <div class="kpi-icon icon-teal"><i class="fa-solid fa-chart-line"></i></div>
            </div>
            <div class="card kpi-card">
                <div class="kpi-info"><h3>Cursos Aprobados</h3><p class="number"><?php echo number_format($kpi_terminados); ?></p></div>
                <div class="kpi-icon icon-teal"><i class="fa-solid fa-certificate"></i></div>
            </div>
            <div class="card kpi-card">
                <div class="kpi-info"><h3>Edad Promedio</h3><p class="number"><?php echo $promedio_edad; ?></p></div>
                <div class="kpi-icon icon-orange"><i class="fa-solid fa-cake-candles"></i></div>
            </div>
            <div class="card kpi-card">
                <div class="kpi-info"><h3>Empresas B2B</h3><p class="number"><?php echo $total_instituciones; ?></p></div>
                <div class="kpi-icon icon-purple"><i class="fa-solid fa-building"></i></div>
            </div>
        </div>

        <div class="charts-grid">
            <div class="card">
                <div class="chart-header"><h3>Crecimiento de Matrícula (Tiempo)</h3></div>
                <div class="chart-container"><canvas id="lineChart"></canvas></div>
            </div>
            <div class="card">
                <div class="chart-header"><h3>Distribución por Edades</h3></div>
                <div class="chart-container"><canvas id="edadChart"></canvas></div>
            </div>
        </div>

        <div class="charts-grid">
            <div class="card" style="grid-column: span 2;">
                <div class="chart-header"><h3>Tasa de Éxito y Abandono por Curso</h3></div>
                <div class="chart-container"><canvas id="stackedBarChart"></canvas></div>
            </div>
        </div>

        <div class="charts-grid">
            <div class="card">
                <div class="chart-header"><h3>Top 5 - Instituciones de Origen</h3></div>
                <div class="chart-container"><canvas id="instChart"></canvas></div>
            </div>
            <div class="card">
                <div class="chart-header"><h3>Canales de Adquisición (Marketing)</h3></div>
                <div class="chart-container"><canvas id="doughnutChart"></canvas></div>
            </div>
        </div>
        
    </div>

    <script>
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#64748b';
        Chart.defaults.plugins.tooltip.backgroundColor = '#0f172a';
        Chart.defaults.plugins.tooltip.padding = 10;
        Chart.defaults.plugins.tooltip.cornerRadius = 6;
        const brandColors = ['#0f172a', '#0f766e', '#2563eb', '#059669', '#f59e0b', '#7c3aed'];

        new Chart(document.getElementById('lineChart'), {
            type: 'line',
            data: { labels: <?php echo json_encode($dias); ?>, datasets: [{ label: 'Registros', data: <?php echo json_encode($registros_dia); ?>, borderColor: '#0f766e', backgroundColor: 'rgba(15, 118, 110, 0.05)', borderWidth: 2, fill: true, tension: 0.3 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
        });

        new Chart(document.getElementById('edadChart'), {
            type: 'pie',
            data: { labels: <?php echo json_encode($rangos_edad); ?>, datasets: [{ data: <?php echo json_encode($totales_edad); ?>, backgroundColor: brandColors, borderWidth: 1 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
        });

        new Chart(document.getElementById('stackedBarChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($cursos_desempeno); ?>,
                datasets: [
                    { label: 'Aprobados (Terminado)', data: <?php echo json_encode($terminados_data); ?>, backgroundColor: '#059669', borderRadius: 4 },
                    { label: 'En Riesgo (En Progreso)', data: <?php echo json_encode($progreso_data); ?>, backgroundColor: '#cbd5e1', borderRadius: 4 }
                ]
            },
            options: { 
                responsive: true, maintainAspectRatio: false, 
                scales: { x: { stacked: true, grid: { display: false } }, y: { stacked: true, beginAtZero: true, grid: { color: '#f1f5f9' } } }
            }
        });

        new Chart(document.getElementById('instChart'), {
            type: 'bar',
            data: { labels: <?php echo json_encode($instituciones); ?>, datasets: [{ label: 'Alumnos', data: <?php echo json_encode($totales_inst); ?>, backgroundColor: '#2563eb', borderRadius: 4 }] },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true }, y: { grid: { display: false } } } }
        });

        new Chart(document.getElementById('doughnutChart'), {
            type: 'doughnut',
            data: { labels: <?php echo json_encode($medios); ?>, datasets: [{ data: <?php echo json_encode($totales_medio); ?>, backgroundColor: brandColors, borderWidth: 2, borderColor: '#ffffff' }] },
            options: { cutout: '70%', responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
        });
    </script>
</body>
</html>