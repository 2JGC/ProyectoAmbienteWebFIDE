<?php

declare(strict_types=1);

use Kreait\Firebase\Factory;

/**
 * FirebaseStorage
 * Sube archivos a Firebase Storage (motos, perfiles, galería) y devuelve
 * la URL pública de descarga. Credenciales tomadas de config/firebase.php.
 */
class FirebaseStorage
{
    /**
     * Sube un archivo local temporal a Firebase Storage bajo la carpeta indicada.
     * Devuelve la URL pública de descarga, o null si falla la subida.
     */
    public static function subir(string $rutaTmp, string $carpeta, string $nombreArchivo, string $mimeType): ?string
    {
        $config = require BASE_PATH . '/config/firebase.php';

        try {
            // Nos conectamos al proyecto de Firebase usando las credenciales
            // del archivo JSON, y pedimos el "bucket" (la carpeta raíz en
            // la nube) donde vamos a guardar el archivo.
            $bucket = (new Factory())
                ->withServiceAccount($config['credentials'])
                ->withDefaultStorageBucket($config['bucket'])
                ->createStorage()
                ->getBucket();

            // Armamos la ruta dentro del bucket, por ejemplo "motos/moto_3_123.jpg".
            $rutaObjeto = trim($carpeta, '/') . '/' . $nombreArchivo;
            // Firebase necesita un "token" para poder generar un enlace público
            // de descarga sin que el archivo quede completamente abierto a
            // cualquiera que adivine la ruta.
            $token = bin2hex(random_bytes(16));

            // fopen abre el archivo temporal que subió el usuario (el que
            // PHP guarda mientras procesa el formulario) para leerlo y
            // mandarlo a Firebase.
            $stream = fopen($rutaTmp, 'r');
            $bucket->upload($stream, [
                'name' => $rutaObjeto,
                'metadata' => [
                    'contentType' => $mimeType,
                    'metadata' => [
                        'firebaseStorageDownloadTokens' => $token,
                    ],
                ],
            ]);

            // Armamos a mano la URL pública con el formato que usa Firebase
            // Storage para servir archivos vía HTTP.
            return sprintf(
                'https://firebasestorage.googleapis.com/v0/b/%s/o/%s?alt=media&token=%s',
                $bucket->name(),
                rawurlencode($rutaObjeto),
                $token
            );
        } catch (\Throwable $e) {
            // Si algo falla (sin conexión, credenciales inválidas, etc.)
            // no tumbamos la aplicación: devolvemos null y quien llamó a
            // este método decide qué mensaje mostrarle al usuario.
            error_log('FirebaseStorage: error al subir archivo - ' . $e->getMessage());
            return null;
        }
    }
}
