<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

class Mailer {
    private $config;
    public function __construct($config) {
        $this->config = $config;
    }

    public function send($toEmail, $toName, $subject, $htmlBody, $altBody = '') {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $this->config->smtp['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->config->smtp['username'];
            $mail->Password   = $this->config->smtp['password'];
            $mail->SMTPSecure = $this->config->smtp['secure'];
            $mail->Port       = $this->config->smtp['port'];

            $mail->setFrom($this->config->site['from_email'], $this->config->site['from_name']);
            $mail->addAddress($toEmail, $toName);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = $altBody;

            $mail->send();
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $mail->ErrorInfo];
        }
    }
}
