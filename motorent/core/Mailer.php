<?php

declare(strict_types=1);

require_once BASE_PATH . '/vendor/phpmailer/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * Mailer
 * Envío de correos vía SMTP de Gmail usando PHPMailer.
 * Credenciales tomadas de config/mail.php.
 */
class Mailer
{
    /**
     * Envía un correo en formato HTML.
     * Devuelve true si se envió correctamente, false en caso de error
     * (el detalle del error queda en $this->ultimoError).
     */
    public static function enviar(string $destinatario, string $asunto, string $cuerpoHtml): bool
    {
        // Cargamos las credenciales SMTP cada vez que se manda un correo,
        // así no hay que reiniciar nada si se cambian en config/mail.php.
        $config = require BASE_PATH . '/config/mail.php';

        // El "true" le dice a PHPMailer que lance excepciones si algo
        // falla, para poder atraparlas en el catch de abajo.
        $mail = new PHPMailer(true);

        try {
            // Configuramos el envío por SMTP (el servidor de correo de Gmail),
            // no el mail() nativo de PHP, porque Gmail exige autenticación.
            $mail->isSMTP();
            $mail->Host       = $config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['usuario'];
            // Quitamos espacios porque Google muestra la contraseña de
            // aplicación separada en grupos de 4 caracteres, y si se
            // copia y pega tal cual quedan espacios de más.
            $mail->Password   = str_replace(' ', '', $config['password']);
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $config['port'];
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($config['from_email'], $config['from_name']);
            $mail->addAddress($destinatario);

            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = $cuerpoHtml;
            // AltBody es el texto plano que ven los clientes de correo
            // que no pueden mostrar HTML. strip_tags() nos ahorra tener
            // que escribir el mensaje dos veces.
            $mail->AltBody = strip_tags($cuerpoHtml);

            $mail->send();
            return true;
        } catch (PHPMailerException $e) {
            // Si el correo no se pudo enviar (credenciales mal puestas,
            // sin internet, etc.) no queremos que la aplicación se caiga:
            // solo dejamos el error en el log y avisamos con un false.
            error_log('Mailer: error al enviar correo - ' . $mail->ErrorInfo);
            return false;
        }
    }
}
