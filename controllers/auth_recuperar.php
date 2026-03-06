<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../includes/db.php';
require '../includes/PHPMailer/src/Exception.php';
require '../includes/PHPMailer/src/PHPMailer.php';
require '../includes/PHPMailer/src/SMTP.php';

$email = $_POST['email'];
$token = bin2hex(random_bytes(32));
$expira = date("Y-m-d H:i:s", strtotime('+1 hour'));

// Guardar Token
$stmt = $pdo->prepare("UPDATE usuarios SET reset_token = ?, reset_expires = ? WHERE email = ?");
$stmt->execute([$token, $expira, $email]);

if ($stmt->rowCount() > 0) {
    $link = "http://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=$token";
    
    $mail = new PHPMailer(true);
    try {
        // Configuración SMTP (GMAIL)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
       $mail->Username   = 'burguillosdylan@gmail.com'; 
        $mail->Password   = 'twwu hsrs efnc pugh'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('burguillosdylan@gmail.com', 'Soporte MaquimPower');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Recuperar Contraseña - MaquimPower';
        $mail->Body    = "<h2>Recuperación de Acceso</h2><p>Haz clic abajo para cambiar tu clave:</p><a href='$link' style='background:#FF4500;color:white;padding:10px;text-decoration:none;'>CAMBIAR CONTRASEÑA</a>";

        $mail->send();
        echo "<script>alert('Correo enviado. Revisa tu bandeja.'); window.location.href='/pagina/login.php';</script>";
    } catch (Exception $e) {
        echo "Error al enviar: {$mail->ErrorInfo}";
    }
} else {
    echo "<script>alert('Correo no registrado.'); window.history.back();</script>";
}
?>