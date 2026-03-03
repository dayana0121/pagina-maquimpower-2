<?php
// includes/mailer.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ajusta las rutas si es necesario
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function enviarCorreoPedido($destinatario, $nombre, $pedidoId, $total, $carrito)
{
    $mail = new PHPMailer(true);

    try {
        // --- 1. CONFIGURACIÓN DEL SERVIDOR (DONWEB / FEROZO) ---
        $mail->isSMTP();
        $mail->Host = 'a0061241.ferozo.com'; // Servidor Oficial de Salida
        $mail->SMTPAuth = true;
        $mail->Username = 'ventas@maquimpower.com'; // CUENTA DE VENTAS
        $mail->Password = 'R@BCgqU6';  // <--- ¡CAMBIA ESTO!
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Certificado SSL: Sí
        $mail->Port = 465;

        // Bypass para evitar bloqueos de certificados en Ferozo
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // --- 2. CABECERAS ---
        $mail->setFrom('ventas@maquimpower.com', 'Maquimpower Ventas');
        $mail->addAddress($destinatario, $nombre);
        $mail->addBCC('burguillosdylan@gmail.com'); // Copia Oculta a ti

        // --- 3. CONTENIDO HTML ---
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = "Confirmación de Pedido #$pedidoId - Maquimpower";

        // Armar lista
        $lista = "";
        foreach ($carrito as $p) {
            $lista .= "<li>{$p['cantidad']}x <strong>{$p['nombre']}</strong> - S/ {$p['precio']}</li>";
        }

        $cuerpo = "
        <div style='font-family:Arial,sans-serif; color:#333; max-width:600px; margin:auto; border:1px solid #eee;'>
            <div style='background:#000; color:#fff; padding:20px; text-align:center;'>
                <h1 style='margin:0; color:#FF4500;'>MAQUIMPOWER</h1>
            </div>
            <div style='padding:30px;'>
                <h2 style='color:#FF4500;'>¡Gracias por tu compra, $nombre!</h2>
                <p>Tu pedido <strong>#$pedidoId</strong> ha sido registrado exitosamente.</p>
                <hr style='border:0; border-top:1px solid #eee; margin:20px 0;'>
                <h3>Resumen:</h3>
                <ul>$lista</ul>
                <h3 style='text-align:right;'>Total: S/ " . number_format($total, 2) . "</h3>
                <p style='font-size:12px; color:#999; margin-top:30px;'>Si pagaste con transferencia, envía el voucher al WhatsApp.</p>
            </div>
        </div>
        ";

        $mail->Body = $cuerpo;
        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Error al enviar correo: {$mail->ErrorInfo}");
        return false;
    }
}

function enviarCorreoBienvenida($destinatario, $nombre)
{
    $mail = new PHPMailer(true);

    try {
        // --- 1. CONFIGURACIÓN DEL SERVIDOR (DONWEB / FEROZO) ---
        $mail->isSMTP();
        $mail->Host = 'a0061241.ferozo.com'; // Servidor Oficial de Salida
        $mail->SMTPAuth = true;
        $mail->Username = 'info@maquimpower.com'; // CUENTA DE INFO
        $mail->Password = 'wS*2g@S7';  // <--- ¡CAMBIA ESTO!
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Certificado SSL: Sí
        $mail->Port = 465;

        // Bypass para evitar bloqueos de certificados en Ferozo
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // --- 2. CABECERAS ---
        $mail->setFrom('info@maquimpower.com', 'Bienvenido a Maquimpower');
        $mail->addAddress($destinatario, $nombre);

        // --- 3. CONTENIDO HTML ---
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = "¡Bienvenido a la familia Maquimpower!";

        $cuerpo = "
        <div style='font-family:Arial,sans-serif; color:#333; max-width:600px; margin:auto; border:1px solid #eee;'>
            <div style='background:#FF4500; padding:20px; text-align:center;'>
                <h1 style='margin:0; color:#fff;'>¡BIENVENIDO!</h1>
            </div>
            <div style='padding:30px; background:#fff;'>
                <h2 style='color:#333;'>Hola, $nombre</h2>
                <p>Gracias por registrarte en <strong>Maquimpower</strong>. Nos alegra tenerte con nosotros.</p>
                <p>Ahora puedes:</p>
                <ul>
                    <li>Realizar pedidos de forma rápida.</li>
                    <li>Acceder a ofertas exclusivas.</li>
                    <li>Consultar tu historial de compras.</li>
                </ul>
                <div style='text-align:center; margin:30px 0;'>
                    <a href='https://maquimpower.com/login.php' style='background:#000; color:#fff; padding:15px 25px; text-decoration:none; font-weight:bold; border-radius:5px;'>IR A MI CUENTA</a>
                </div>
                <hr style='border:0; border-top:1px solid #eee; margin:20px 0;'>
                <p style='font-size:12px; color:#999; text-align:center;'>Si tienes alguna duda, contáctanos a info@maquimpower.com</p>
            </div>
        </div>
        ";

        $mail->Body = $cuerpo;
        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Error al enviar correo bienvenida: {$mail->ErrorInfo}");
        return false;
    }
}
?>