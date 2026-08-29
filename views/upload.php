<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Datos ETL - CertiDash</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
        
        .btn-back { background-color: transparent; color: #cbd5e1; text-decoration: none; padding: 0.5rem 1rem; border-radius: 6px; font-weight: 500; font-size: 0.875rem; border: 1px solid var(--text-muted); transition: all 0.2s ease; display: flex; align-items: center; gap: 6px; }
        .btn-back:hover { background-color: rgba(255,255,255,0.1); color: white; border-color: white; }
        
        .container { max-width: 1100px; margin: 2rem auto; padding: 0 1.5rem; }
        .page-header { margin-bottom: 2rem; }
        .page-header h2 { margin: 0; color: var(--certi-navy); font-size: 1.5rem; font-weight: 600; }
        .page-header p { margin: 0.25rem 0 0 0; color: var(--text-muted); font-size: 0.95rem; }
        
        .dashboard-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; align-items: start; }
        @media (max-width: 850px) { .dashboard-layout { grid-template-columns: 1fr; } }
        
        .card { background-color: #ffffff; padding: 2rem; border-radius: 8px; border: 1px solid var(--border-color); box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); }
        .card h3 { margin-top: 0; color: var(--certi-navy); font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem; }
        
        .upload-grid { display: grid; grid-template-columns: 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
        .file-drop-area { border: 2px dashed #cbd5e1; border-radius: 8px; padding: 2rem 1.5rem; background-color: #f8fafc; position: relative; transition: all 0.2s ease; display: flex; align-items: center; gap: 1rem; }
        .file-drop-area:hover, .file-drop-area.dragover { background-color: #f1f5f9; border-color: var(--dash-teal); }
        .file-drop-area input[type="file"] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
        
        .icon-box { background: white; width: 48px; height: 48px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: var(--dash-teal); box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid var(--border-color); }
        .file-info { display: flex; flex-direction: column; }
        .file-title { font-weight: 600; color: var(--certi-navy); font-size: 0.95rem; }
        .file-msg { font-size: 0.85rem; color: var(--text-muted); margin-top: 2px; }
        
        .btn-submit { width: 100%; padding: 0.85rem; background-color: var(--certi-navy); color: white; border: none; border-radius: 6px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: 0.2s ease; font-family: 'Inter', sans-serif; display: flex; justify-content: center; align-items: center; gap: 8px; }
        .btn-submit:hover { background-color: #1e293b; }
        
        .instructions-list { list-style: none; padding: 0; margin: 0; }
        .instructions-list li { margin-bottom: 1rem; font-size: 0.875rem; color: var(--text-main); display: flex; gap: 10px; align-items: flex-start; line-height: 1.4; }
        .instructions-list li i { color: var(--dash-teal); margin-top: 3px; }
        
        .alert { padding: 1rem; border-radius: 6px; font-size: 0.875rem; font-weight: 500; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 8px; }
        .alert-success { background-color: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .alert-error { background-color: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="navbar-brand">
            <img src="../assets/logo.jpeg" alt="Logo CertiDash" onerror="this.src='https://placehold.co/100x100?text=Logo'">
            <h1>CertiDash</h1>
        </div>
        <a href="dashboard.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Volver al Menú</a>
    </nav>

    <div class="container">
        
        <div class="page-header">
            <h2>Módulo de Extracción y Carga (ETL)</h2>
            <p>Sincroniza los datos de inscripciones y progreso académico de forma masiva.</p>
        </div>

        <?php if(isset($_GET['exito'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> Los datos han sido procesados y actualizados correctamente.</div>
        <?php endif; ?>
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-error"><i class="fa-solid fa-triangle-exclamation"></i> Hubo un error al procesar los archivos. Verifica el formato.</div>
        <?php endif; ?>

        <div class="dashboard-layout">
            
            <div class="card">
                <h3>Archivos de Origen</h3>
                <form action="../controllers/DataController.php" method="POST" enctype="multipart/form-data">
                    <div class="upload-grid">
                        
                        <div class="file-drop-area" id="drop-participantes">
                            <div class="icon-box"><i class="fa-solid fa-users-viewfinder"></i></div>
                            <div class="file-info">
                                <span class="file-title">1. Padrón de Participantes</span>
                                <span class="file-msg" id="msg-participantes">Arrastra tu archivo o haz clic para buscar</span>
                            </div>
                            <input type="file" name="excel_participantes" accept=".xlsx, .csv" required id="input-participantes">
                        </div>
                        
                        <div class="file-drop-area" id="drop-certificados">
                            <div class="icon-box"><i class="fa-solid fa-file-signature"></i></div>
                            <div class="file-info">
                                <span class="file-title">2. Estatus de Certificaciones</span>
                                <span class="file-msg" id="msg-certificados">Arrastra tu archivo o haz clic para buscar</span>
                            </div>
                            <input type="file" name="excel_certificaciones" accept=".xlsx, .csv" required id="input-certificados">
                        </div>
                        
                    </div>
                    <button type="submit" name="upload_dual_data" class="btn-submit">
                        <i class="fa-solid fa-server"></i> Procesar y Cruzar Datos
                    </button>
                </form>
            </div>

            <div class="card">
                <h3>Instrucciones de Carga</h3>
                <ul class="instructions-list">
                    <li>
                        <i class="fa-solid fa-file-excel"></i>
                        <span><strong>Formato requerido:</strong> Los archivos deben estar en formato Excel (.xlsx) o delimitados por comas (.csv).</span>
                    </li>
                    <li>
                        <i class="fa-solid fa-heading"></i>
                        <span><strong>Cabeceras intactas:</strong> Asegúrate de que la primera fila contenga los nombres exactos de las columnas, sin celdas combinadas.</span>
                    </li>
                    <li>
                        <i class="fa-solid fa-link"></i>
                        <span><strong>Llave de cruce:</strong> El sistema utilizará el <em>Correo Electrónico</em> para enlazar a los alumnos con sus respectivos certificados.</span>
                    </li>
                    <li>
                        <i class="fa-solid fa-weight-hanging"></i>
                        <span><strong>Límite de tamaño:</strong> El peso máximo permitido por archivo es de 10 MB.</span>
                    </li>
                </ul>
            </div>

        </div>
    </div>

    <script>
        function configurarInputAvanzado(inputId, msgId, dropId) {
            const input = document.getElementById(inputId);
            const msg = document.getElementById(msgId);
            const dropArea = document.getElementById(dropId);

            input.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    msg.innerHTML = '<span style="color: #059669; font-weight: 600;"><i class="fa-solid fa-check"></i> ' + this.files[0].name + ' cargado</span>';
                    dropArea.style.borderColor = '#0f766e';
                    dropArea.style.backgroundColor = '#f0fdf4';
                }
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                input.addEventListener(eventName, () => dropArea.classList.add('dragover'), false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                input.addEventListener(eventName, () => dropArea.classList.remove('dragover'), false);
            });
        }

        configurarInputAvanzado('input-participantes', 'msg-participantes', 'drop-participantes');
        configurarInputAvanzado('input-certificados', 'msg-certificados', 'drop-certificados');
    </script>
</body>
</html>