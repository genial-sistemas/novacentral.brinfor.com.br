<?php

require_once __DIR__ . '/../bibliotecas/phpmailer/class.phpmailer.php';

function enviarEmailHtml($destinatario, $assunto, $html, $texto) {
    try {
        $smtpHost = trim((string)getenv('SMTP_HOST'));
        $smtpPort = (int)getenv('SMTP_PORT');
        $smtpUsername = trim((string)getenv('SMTP_USERNAME'));
        $smtpPassword = (string)getenv('SMTP_PASSWORD');
        $smtpEncryption = strtolower(trim((string)getenv('SMTP_ENCRYPTION')));
        $fromEmail = trim((string)getenv('SMTP_FROM_EMAIL'));
        $fromName = trim((string)getenv('SMTP_FROM_NAME'));

        if (
            $smtpHost === ''
            || $smtpPort <= 0
            || $smtpUsername === ''
            || $smtpPassword === ''
            || !in_array($smtpEncryption, array('tls', 'ssl'), true)
            || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)
            || $fromName === ''
        ) {
            throw new RuntimeException('Configure SMTP_HOST, SMTP_PORT, SMTP_USERNAME, SMTP_PASSWORD, SMTP_ENCRYPTION, SMTP_FROM_EMAIL e SMTP_FROM_NAME.');
        }

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->Port = $smtpPort;
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUsername;
        $mail->Password = $smtpPassword;
        $mail->SMTPSecure = $smtpEncryption;
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($destinatario);
        $mail->Subject = $assunto;
        $mail->Body = $html;
        $mail->AltBody = $texto;
        $mail->send();
        return array('enviado' => true, 'erro' => null);
    } catch (Throwable $erro) {
        error_log('Falha ao enviar e-mail para ' . $destinatario . ': ' . $erro->getMessage());
        return array('enviado' => false, 'erro' => $erro->getMessage());
    }
}
