<?php
// Simple mailer wrapper: intenta usar PHPMailer si está disponible, si no usa mail()
$config = require __DIR__ . '/config.php';

function sendOrderEmail($to, $subject, $htmlBody){
    $config = require __DIR__ . '/config.php';
    // Intentar PHPMailer si está instalado
    if (file_exists(__DIR__ . '/../../vendor/autoload.php')){
        require_once __DIR__ . '/../../vendor/autoload.php';
        if (class_exists('\PHPMailer\PHPMailer\PHPMailer')){
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            try{
                $smtp = $config['mail']['smtp'] ?? null;
                if ($smtp){
                    $mail->isSMTP();
                    $mail->Host = $smtp['host'];
                    $mail->SMTPAuth = true;
                    $mail->Username = $smtp['username'];
                    $mail->Password = $smtp['password'];
                    $mail->SMTPSecure = $smtp['secure'] ?? 'tls';
                    $mail->Port = $smtp['port'] ?? 587;
                }
                $mail->setFrom($config['mail']['from']);
                $mail->addAddress($to);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $htmlBody;
                $mail->send();
                return true;
            }catch(Exception $e){ error_log('Mailer error: '.$e->getMessage()); }
        }
    }
    // Fallback simple
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=utf-8\r\n";
    $headers .= "From: " . ($config['mail']['from'] ?? 'no-reply') . "\r\n";
    return mail($to, $subject, $htmlBody, $headers);
}
