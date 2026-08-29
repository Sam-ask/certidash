<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - CertiDash</title>
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

        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg-color);
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
            color: var(--text-main);
        }
        
        .login-card { 
            background-color: #ffffff; 
            padding: 2.5rem 3rem; 
            border-radius: 12px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--border-color);
            width: 100%; 
            max-width: 380px; 
            text-align: center; 
        }

        .logo-img { max-width: 160px; height: auto; margin-bottom: 1.5rem; }
        
        h2 { 
            margin-top: 0; 
            color: var(--certi-navy); 
            font-size: 1.25rem; 
            font-weight: 600; 
            margin-bottom: 1.5rem; 
        }

        .form-group { margin-bottom: 1.25rem; text-align: left; }
        
        label { 
            display: block; 
            margin-bottom: 0.35rem; 
            color: var(--text-main); 
            font-size: 0.875rem; 
            font-weight: 500; 
        }
        
        input { 
            width: 100%; 
            padding: 0.65rem 0.875rem; 
            border: 1px solid var(--border-color); 
            border-radius: 6px; 
            box-sizing: border-box; 
            font-size: 0.875rem;
            font-family: 'Inter', sans-serif;
            color: var(--certi-navy);
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        
        input:focus { 
            border-color: var(--dash-teal); 
            outline: none; 
            box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.15); 
        }
        
        button { 
            width: 100%; 
            padding: 0.75rem; 
            background-color: var(--dash-teal); 
            color: white; 
            border: none; 
            border-radius: 6px; 
            font-size: 0.875rem; 
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer; 
            margin-top: 1rem; 
            transition: background-color 0.2s ease;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        
        button:hover { background-color: #115e59; } /* Teal 800 */
        
        .alert-error { 
            color: #991b1b; 
            background-color: #fef2f2; 
            border: 1px solid #fecaca;
            padding: 0.75rem; 
            border-radius: 6px; 
            font-size: 0.875rem; 
            margin-bottom: 1.5rem; 
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <img src="../assets/logo.jpeg" alt="CertiDash Logo" class="logo-img" onerror="this.src='https://placehold.co/160x50?text=Logo'">
        
        <h2>Crear Nueva Contraseña</h2>
        
        <?php
        if (!isset($_GET['token']) || empty($_GET['token'])) {
            echo "<div class='alert-error'>El enlace es inválido o está incompleto. Por favor, solicita uno nuevo.</div>";
            exit();
        }
        $token = $_GET['token'];
        ?>

        <form action="../controllers/PasswordController.php" method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            
            <div class="form-group">
                <label for="nueva_password">Nueva Contraseña</label>
                <input type="password" id="nueva_password" name="nueva_password" required placeholder="Mínimo 6 caracteres" minlength="6">
            </div>
            
            <button type="submit" name="reset_password">Guardar Contraseña</button>
        </form>
    </div>
</body>
</html>