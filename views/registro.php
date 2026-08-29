<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscripción a Cursos - CertiDash</title>
    
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
            display: flex; justify-content: center; align-items: center; 
            min-height: 100vh; margin: 0; padding: 2rem 1rem; box-sizing: border-box;
            color: var(--text-main);
        }
        
        .register-card { 
            background-color: #ffffff; 
            padding: 2.5rem 3rem; 
            border-radius: 12px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--border-color);
            width: 100%; max-width: 550px; position: relative;
            border-top: 4px solid var(--dash-teal);
        }

        .logo-container { text-align: center; margin-bottom: 1.5rem; }
        .logo-img { max-width: 150px; height: auto; }
        
        h2 { color: var(--certi-navy); text-align: center; margin-top: 0; margin-bottom: 0.5rem; font-size: 1.5rem; font-weight: 600; }
        p.subtitle { color: var(--text-muted); text-align: center; font-size: 0.9rem; margin-bottom: 2rem; }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
        .form-group { display: flex; flex-direction: column; }
        .full-width { grid-column: span 2; }
        
        label { margin-bottom: 0.35rem; color: var(--text-main); font-size: 0.875rem; font-weight: 500; }
        
        input, select { 
            width: 100%; padding: 0.65rem 0.875rem; 
            border: 1px solid var(--border-color); border-radius: 6px; 
            box-sizing: border-box; font-size: 0.875rem; font-family: 'Inter', sans-serif; 
            color: var(--certi-navy); transition: all 0.2s ease;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            background-color: #ffffff;
        }
        
        input:focus, select:focus { 
            border-color: var(--dash-teal); outline: none; 
            box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.15); 
        }
        
        input:not(:placeholder-shown):invalid { border-color: #ef4444; }
        
        button { 
            width: 100%; padding: 0.75rem; background-color: var(--dash-teal); color: white; 
            border: none; border-radius: 6px; font-size: 0.875rem; font-weight: 600; 
            font-family: 'Inter', sans-serif; cursor: pointer; 
            margin-top: 0.5rem; transition: background-color 0.2s ease; grid-column: span 2;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        button:hover { background-color: #115e59; }
        
        /* Alertas rediseñadas */
        .alert-success { 
            color: #15803d; background-color: #f0fdf4; border: 1px solid #bbf7d0; 
            padding: 1rem; border-radius: 6px; font-size: 0.875rem; margin-bottom: 1.5rem; font-weight: 500; text-align: center;
        }
        .alert-error { 
            color: #991b1b; background-color: #fef2f2; border: 1px solid #fecaca; 
            padding: 1rem; border-radius: 6px; font-size: 0.875rem; margin-bottom: 1.5rem; font-weight: 500; text-align: center; display: none;
        }

        .login-link {
            grid-column: span 2; text-align: center; margin-top: 1rem;
        }
        .login-link a {
            color: var(--text-muted); text-decoration: none; font-weight: 500; font-size: 0.875rem; transition: color 0.2s ease;
        }
        .login-link a:hover { color: var(--certi-navy); }
        
    </style>
</head>
<body>
    <div class="register-card">
        <div class="logo-container">
            <img src="../assets/logo.jpeg" alt="CertiDash Logo" class="logo-img" onerror="this.src='https://placehold.co/150x50?text=Logo'">
        </div>
        
        <h2>Registro de Participantes</h2>
        <p class="subtitle">Completa tus datos para inscribirte a nuestros cursos de capacitación.</p>
        
        <?php if(isset($_GET['exito'])): ?>
            <div class="alert-success">¡Registro exitoso! Tus datos han sido guardados.</div>
        <?php endif; ?>
        <?php if(isset($_GET['error']) && $_GET['error'] == 'limite'): ?>
            <div class="alert-error" style="display:block;">Has alcanzado el límite máximo de 3 inscripciones este mes.</div>
        <?php endif; ?>

        <div id="js-error" class="alert-error"></div>

        <form id="registroForm" action="../controllers/RegistroController.php" method="POST" class="form-grid">
            
            <div class="form-group full-width">
                <label for="nombre">Nombre Completo</label>
                <input type="text" id="nombre" name="nombre" required placeholder="Ej. Juan Pérez" pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$" minlength="3" title="Solo letras." onblur="this.value = this.value.trim().replace(/\s+/g, ' ')">
            </div>
            
            <div class="form-group full-width">
                <label for="correo">Correo Electrónico</label>
                <input type="email" id="correo" name="correo" required placeholder="tu-correo@ejemplo.com" oninput="this.value = this.value.toLowerCase().replace(/\s/g, '')">
            </div>

            <div class="form-group full-width">
                <label for="institucion">Institución o Empresa de procedencia</label>
                <input type="text" id="institucion" name="institucion" placeholder="Ej. Universidad Autónoma, o 'Ninguna'" maxlength="100">
            </div>
            
            <div class="form-group">
                <label for="telefono">Teléfono (10 dígitos)</label>
                <input type="tel" id="telefono" name="telefono" required placeholder="Ej. 8112345678" pattern="^[0-9]{10}$" title="Debe contener exactamente 10 números.">
            </div>
            
            <div class="form-group">
                <label for="edad">Edad</label>
                <input type="number" id="edad" name="edad" min="15" max="99" required placeholder="Ej. 25">
            </div>

            <div class="form-group full-width">
                <label for="medio">¿Cómo te enteraste de nosotros?</label>
                <select id="medio" name="medio" required>
                    <option value="" disabled selected>Selecciona una opción</option>
                    <option value="Facebook">Facebook</option>
                    <option value="Instagram">Instagram</option>
                    <option value="LinkedIn">LinkedIn</option>
                    <option value="Búsqueda Web">Búsqueda en Google</option>
                    <option value="Recomendación">Recomendación de un amigo</option>
                </select>
            </div>

            <div class="form-group full-width">
                <label for="curso_deseado">Curso a inscribir</label>
                <select id="curso_deseado" name="curso_deseado" required>
                    <option value="" disabled selected>Selecciona el curso</option>
                    <option value="Excel Avanzado">Excel Avanzado</option>
                    <option value="Power BI">Power BI</option>
                    <option value="Python Básico">Python Básico</option>
                    <option value="Marketing Digital">Marketing Digital</option>
                </select>
            </div>
            
            <div class="form-group full-width" style="display: flex; flex-direction: row; align-items: start; gap: 10px; margin-top: 5px;">
                <input type="checkbox" id="terminos" name="terminos" required style="width: auto; margin-top: 3px; cursor: pointer; box-shadow: none;">
                <label for="terminos" style="font-size: 0.8rem; color: var(--text-muted); font-weight: 400; line-height: 1.4; cursor: pointer; margin: 0;">
                    Acepto los términos y condiciones, y consiento el uso y publicación de mis datos para fines de validación de certificados y métricas de la plataforma.
                </label>
            </div>
            
            <button type="submit" name="registrar_usuario">Inscribirme al curso</button>
            
            <div class="login-link">
                <a href="login.php">← Regresar al login de administración</a>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('registroForm').addEventListener('submit', function(event) {
            const telefono = document.getElementById('telefono').value;
            let nombreInput = document.getElementById('nombre');
            
            nombreInput.value = nombreInput.value.trim().replace(/\s+/g, ' '); 
            const nombre = nombreInput.value;
            
            const errorDiv = document.getElementById('js-error');
            let errores = [];

            const nombreRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
            if (!nombreRegex.test(nombre)) {
                errores.push("El nombre solo debe contener letras.");
            }

            const telefonoRegex = /^[0-9]{10}$/;
            if (!telefonoRegex.test(telefono.trim())) {
                errores.push("El teléfono debe tener exactamente 10 números.");
            }

            if (errores.length > 0) {
                event.preventDefault(); 
                errorDiv.innerHTML = errores.join("<br>"); 
                errorDiv.style.display = "block";
                window.scrollTo({ top: 0, behavior: 'smooth' }); 
            } else {
                errorDiv.style.display = "none";
            }
        });
    </script>
</body>
</html>