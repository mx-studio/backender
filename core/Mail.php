<?php
namespace adjai\backender\core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mail {

    public static function send($recipient, $subject, $message, $attachments = []) {
        $recipients = is_array($recipient) ? $recipient : [$recipient];
        $mail = new PHPMailer(true);
        try {
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;
            if (SEND_MAIL_METHOD === 'SMTP') {
                if (!SMTP_VERIFY_HOST) {
                    $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );
                }
                $mail->isSMTP();
                $mail->Host = SMTP_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = MAIL_FROM;
                $mail->Password = SMTP_PASSWORD;
                if (SMTP_SECURE) {
                    $mail->SMTPSecure = SMTP_SECURE;
                }
                $mail->Port = SMTP_PORT;
            }

            $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
            foreach ($recipients as $recipient) {
                $mail->addAddress($recipient);
            }

            foreach ($attachments as $name => $file) {
                $mail->addAttachment($file, is_numeric($name) ? '' : $name);
            }

            $mail->CharSet = 'utf-8';
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;
            $mail->AltBody = stripslashes($message);

            return $mail->send();
        } catch (Exception $e) {
            return false;
            // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }

    public static function sendUsingTemplate($template, $recipient, $subject = null, $data = [], $attachments = []) {
        $message = self::getTemplate($template, $data);
        if (is_null($subject)) {
            if (preg_match('|^<!--\s*#SUBJECT:\s*(.+)-->|', $message, $match)) {
                $subject = $match[1];
            }
        }

        if ($message !== false) {
            return self::send($recipient, $subject, $message, $attachments);
        } else {
            return false;
        }
    }

    private static function getTemplate($template, $data) {
        $templateFileName = EMAIL_TEMPLATES_DIRECTORY . "$template.html";
        if (file_exists($templateFileName)) {
            $content = file_get_contents($templateFileName);
            foreach (Utils::arrayFlatten($data) as $key => $value) {
                if (is_numeric($value) || is_string($value)) {
                    $content = str_replace("%$key%", $value, $content);
                }
            }
        } else {
            return false;
        }
        return $content;
    }

}
