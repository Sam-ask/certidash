# CertiDash: Plataforma Analítica y Gestión de Cursos

CertiDash es una aplicación web full-stack desarrollada en PHP bajo la arquitectura MVC (Modelo-Vista-Controlador). La plataforma está diseñada para la gestión segura de usuarios, procesamiento de archivos de datos (Excel) y visualización dinámica de métricas de capacitación mediante dashboards analíticos.

## Tecnologías Utilizadas
- Backend: PHP 8.x
- Base de Datos: MySQL (Conexión segura vía PDO/MySQLi)
- Arquitectura: MVC (Modelo-Vista-Controlador)
- Frontend: HTML5, CSS3 (Dashboards interactivos)
- Gestión de Dependencias: Composer

## Características Principales

1. Módulo de Autenticación Segura:
   - Sistema de registro de usuarios y login.
   - Encriptación de contraseñas mediante algoritmos de hashing.
   - Recuperación de credenciales y protección de rutas mediante sesiones ($_SESSION).
2. Procesamiento de Archivos:
   - Interfaz para la carga estructurada de documentos.
   - Validación de formatos y gestión de directorios temporales y permanentes en el servidor.
3. Visualización y Reportes:
   - Dashboards dinámicos para el seguimiento de métricas de capacitación.
   - Exportación automatizada de datos a formatos de hoja de cálculo (Excel).
4. Arquitectura Escalable:
   - Separación lógica de responsabilidades (Controladores, Vistas y Configuración) para un código limpio y mantenible.

## Cómo ejecutarlo localmente

1. Clona este repositorio en la carpeta htdocs de tu servidor local:
   ```bash
   git clone [https://github.com/Sam-ask/certidash.git](https://github.com/Sam-ask/certidash.git)
