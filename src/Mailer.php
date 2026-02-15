<?php
require_once __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    public static function sendVerification($toEmail, $toName, $token) {
        $mail = new PHPMailer(true);

        try {
            // SMTP Settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = '';
            $mail->Password   = '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom('noreply@yourcompany.com', 'Capstone SESE');
            $mail->addAddress($toEmail, $toName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Verify Your Email Address';
            
            // Adjust this URL to your actual domain/path
            $link = "http://localhost/Capstone_SESE/src/verify.php?token=" . $token;
            
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; border: 1px solid #ddd; padding: 20px;'>
                    <h2 style='color: #1e88ff;'>Welcome to Our Website!</h2>
                    <p>Hi $toName,</p>
                    <p>Please click the button below to verify your account:</p>
                    <a href='$link' style='display: inline-block; padding: 10px 20px; background-color: #1e88ff; color: #fff; text-decoration: none; border-radius: 5px;'>Verify Email</a>
                    <p>If the button doesn't work, copy and paste this link: <br> $link</p>
                </div>";

            return $mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }
}