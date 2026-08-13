<?php

declare(strict_types=1);

/**
 * mail.example.php
 * Plantilla de config/mail.php. Copiar este archivo como "mail.php"
 * (en la misma carpeta) y completar con credenciales reales.
 * mail.php NO se sube al repositorio (ver .gitignore).
 *
 * Cómo generar la contraseña de aplicación de Gmail:
 * 1. Activar verificación en 2 pasos: https://myaccount.google.com/security
 * 2. Generar contraseña de aplicación: https://myaccount.google.com/apppasswords
 */

return [
    'host'       => 'smtp.gmail.com',
    'port'       => 587,
    'usuario'    => 'tu-correo@gmail.com',
    'password'   => 'tu-contraseña-de-aplicación',
    'from_email' => 'tu-correo@gmail.com',
    'from_name'  => 'MotoRent Costa Rica',
];
