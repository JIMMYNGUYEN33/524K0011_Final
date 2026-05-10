<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function send_mail($toEmail, $toName, $subject, $htmlBody)
{
    $config = require __DIR__ . '/../config/mail.php';

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $config['port'];

        $mail->CharSet = 'UTF-8';
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($toEmail, $toName ?: $toEmail);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;

        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}
