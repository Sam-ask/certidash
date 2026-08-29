<?php
session_start();
require '../vendor/autoload.php';
require_once '../config/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_reset'])) {
    $email = trim($_POST['email']);

    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expire = date("Y-m-d H:i:s", time() + 3600); 

        $update = $pdo->prepare("UPDATE usuarios SET reset_token = :token, reset_token_expire = :expire WHERE email = :email");
        $update->execute(['token' => $token, 'expire' => $expire, 'email' => $email]);

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = ''; 
            $mail->Password   = ''; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            
            $mail->setFrom('digispark2026soporte@gmail.com', 'DIGSPARK Web');
            $mail->addAddress($email);

            $enlace = "http://localhost/digispark_web/views/nueva_password.php?token=" . $token;
            
            $mail->isHTML(true);
            $mail->Subject = 'Recuperacion de contrasena - DIGISPARK';
            $mail->Body    = "Hola,<br><br>Has solicitado restablecer tu contraseña. Haz clic en el siguiente enlace para crear una nueva (este enlace caduca en 1 hora):<br><br><a href='$enlace'>$enlace</a><br><br>Si no solicitaste esto, ignora este correo.";

            $mail->send();
            echo "<script>alert('Te hemos enviado un correo con las instrucciones.'); window.location.href='../views/login.php';</script>";
        } catch (Exception $e) {
            echo "Error al enviar el correo. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "<script>alert('Si el correo existe en nuestro sistema, te habremos enviado un enlace.'); window.location.href='../views/login.php';</script>";
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_password'])) {
    $token = trim($_POST['token']);
    $nueva_password = trim($_POST['nueva_password']);

    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE reset_token = :token AND reset_token_expire > NOW() LIMIT 1");
    $stmt->execute(['token' => $token]);
    $user = $stmt->fetch();

    if ($user) {
        $hash_password = password_hash($nueva_password, PASSWORD_DEFAULT);

        $update = $pdo->prepare("UPDATE usuarios SET password = :password, reset_token = NULL, reset_token_expire = NULL WHERE id = :id");
        $update->execute([
            'password' => $hash_password,
            'id' => $user['id']
        ]);

        echo "<script>
                alert('¡Tu contraseña ha sido actualizada exitosamente! Ya puedes iniciar sesión.');
                window.location.href = '../views/login.php';
              </script>";
    } else {
        echo "<script>
                alert('El enlace de recuperación es inválido o ha caducado. Por favor, solicita uno nuevo.');
                window.location.href = '../views/recuperar.php';
              </script>";
    }
}
?>