<?php
session_start();
require_once '../config/db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['nombre'] = $user['nombre'];
        
        header("Location: ../views/dashboard.php");
        exit();
    } else {
        echo "<script>
                alert('Correo o contraseña incorrectos.');
                window.location.href = '../views/login.php';
              </script>";
        exit();
    }
}
?>