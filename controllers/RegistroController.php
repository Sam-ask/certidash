<?php
session_start();
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registrar_usuario'])) {
    
    $nombre = trim(preg_replace('/\s+/', ' ', $_POST['nombre'])); 
    $correo = strtolower(trim($_POST['correo'])); // Fuerza minúsculas en el servidor
    $institucion = trim($_POST['institucion']) ?: 'Independiente';
    $telefono = trim($_POST['telefono']);
    $edad = (int)$_POST['edad'];
    $medio = $_POST['medio'];
    $curso_deseado = $_POST['curso_deseado'];
    $ip_registro = $_SERVER['REMOTE_ADDR'];
    $usuario_id = 1;

    try {
        $stmtCheck = $pdo->prepare("
            SELECT COUNT(id) FROM certificaciones 
            WHERE correo_participante = :correo 
            AND MONTH(fecha_actualizacion) = MONTH(CURRENT_DATE()) 
            AND YEAR(fecha_actualizacion) = YEAR(CURRENT_DATE())
        ");
        $stmtCheck->execute(['correo' => $correo]);
        $cursos_este_mes = $stmtCheck->fetchColumn();

        if ($cursos_este_mes >= 3) {
            header("Location: ../views/registro.php?error=limite");
            exit();
        }

        $pdo->beginTransaction();

        $stmtPart = $pdo->prepare("INSERT IGNORE INTO participantes (usuario_id, nombre_completo, correo, institucion, telefono, edad, medio_contacto, ip_registro) VALUES (:uid, :nombre, :correo, :inst, :tel, :edad, :medio, :ip)");
        $stmtPart->execute([
            'uid' => $usuario_id, 'nombre' => $nombre, 'correo' => $correo, 'inst' => $institucion,
            'tel' => $telefono, 'edad' => $edad, 'medio' => $medio, 'ip' => $ip_registro
        ]);

        $stmtCert = $pdo->prepare("INSERT INTO certificaciones (correo_participante, curso_nombre, terminado) VALUES (:correo, :curso, 0)");
        $stmtCert->execute([
            'correo' => $correo,
            'curso' => $curso_deseado
        ]);

        $pdo->commit();
        header("Location: ../views/registro.php?exito=1");
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Error crítico: " . $e->getMessage());
    }
}
?>