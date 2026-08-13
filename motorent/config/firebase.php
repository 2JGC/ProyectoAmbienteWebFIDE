<?php

declare(strict_types=1);

/**
 * firebase.php
 * Credenciales y bucket de Firebase Storage para el guardado de imágenes
 * (motos, perfiles, galería).
 *
 * IMPORTANTE: no subir config/firebase-credentials.json a un repositorio público.
 */

// 'credentials' apunta al archivo JSON que Google nos da para autenticar
// la aplicación contra nuestro proyecto de Firebase (es como una llave).
// 'bucket' es el nombre del "almacén" en la nube donde se guardan las
// imágenes que suben los usuarios (fotos de perfil, motos, galería).
return [
    'credentials' => BASE_PATH . '/config/firebase-credentials.json',
    'bucket'      => 'goridemoto.firebasestorage.app',
];
