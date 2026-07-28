<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


/**
 * EnvioCorreo.php
 * Clase para enviar correos electrónicos utilizando PHPMailer.
 * @package Spidersoftware
 * @version 1.0
 */

$errors = [];

/**
 * Clase que contiene todas las operaciones utilizadas sobre la base de datos
 * @author SPIDERSOFTWARE
 */
class EnvioCorreo
{


    public function __construct() {}


    public static function enviarCorreo($rqst)
    {

        $email = isset($rqst['email']) ? ($rqst['email']) : 'alexlondon07@gmail.com';
        $mensaje = isset($rqst['mensaje']) ? ($rqst['mensaje']) : '<p>Hola, este es un mensaje de prueba.</p>';
        $subject = isset($rqst['subject']) ? ($rqst['subject']) : 'Test de envío';

        // Configuración y envío del correo
        $mail = new PHPMailer(true);
        $emails_sent = []; // Arreglo para correos enviados
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.hostinger.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'envios@spidersoftware.co';
            $mail->Password = 'Martin3933++$$@@';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('envios@spidersoftware.co', "Team Spidersoftware");
            if (!empty($email) && Util::validate_email($email)) {
                $mail->addAddress($email);
                $emails_sent[] = $email;
            }

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $mensaje;
            $mail->send();

            return ['output' => ['valid' => true, 'response' => true, 'message' => 'Correo enviado correctamente.']];

        } catch (Exception $e) {
            return Util::error_general('Enviando el email al usuario', $mail->ErrorInfo);
        }
    }
}

