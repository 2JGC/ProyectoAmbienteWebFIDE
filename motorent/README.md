# MotoRent Costa Rica

Aplicación web para el alquiler de motocicletas en Costa Rica. Permite a los clientes explorar el catálogo, reservar motos, compartir fotos en una galería comunitaria y contactar al negocio; y le da al administrador un panel para gestionar motos, reservas, usuarios, mensajes y publicaciones.

## Tecnologías utilizadas

- **PHP 8** (tipado estricto, `declare(strict_types=1)` en todo el proyecto)
- **MySQL** como motor de base de datos, accedido con **PDO** (prepared statements) bajo un patrón Singleton (`config/Database.php`)
- **Arquitectura MVC hecha a mano**, sin framework: un front controller (`public/index.php`) enruta manualmente hacia Controllers, Models y Views
- Librerías de terceros incluidas directamente en `vendor/` (sin `composer.json` en el repo por ahora)
  - `kreait/firebase-php` — subida de imágenes (motos, fotos de perfil, galería) a **Firebase Storage**
  - **PHPMailer** (`vendor/phpmailer`) — envío de correos SMTP vía Gmail (recuperación de contraseña, confirmación de reserva)
- **Bootstrap 5** + **Bootstrap Icons** (vía CDN) para la interfaz
- **JavaScript** plano para validaciones e interacciones en el cliente (`public/assets/js`)
- **XAMPP** (Apache + MySQL) como entorno de desarrollo local



### Flujo de una petición

1. Toda petición llega a `public/index.php` (front controller).
2. Se calcula la ruta solicitada y se busca en la tabla de rutas (`$rutas`) definida ahí mismo.
3. Se instancia el Controller correspondiente y se ejecuta el método (acción).
4. El Controller usa los Models (`models/`) para consultar/actualizar la base de datos vía PDO.
5. El Controller incluye la View (`views/`) correspondiente para renderizar el HTML.

## Qué se implementó

- **Autenticación de usuarios**: registro, inicio y cierre de sesión, recuperación de contraseña por correo (token con expiración, tabla `password_resets`) y contraseñas hasheadas con `password_hash` (bcrypt).
- **Catálogo de motocicletas**: listado y detalle de cada moto (marca, modelo, año, cilindraje, categoría, precio por día, estado).
- **Reservas**: los clientes reservan una moto por rango de fechas, consultan su historial y pueden cancelar reservas.
- **Perfil de usuario**: edición de datos personales y foto de perfil (subida a Firebase Storage).
- **Galería comunitaria**: los usuarios publican fotos, que quedan pendientes de aprobación por un administrador.
- **Formulario de contacto**: los mensajes enviados quedan almacenados y visibles para el administrador.
- **Panel de administración** (rol `administrador`): dashboard general, y CRUD/gestión de motos, reservas, usuarios, mensajes de contacto y publicaciones de la galería.
- **Seguridad**: protección CSRF en formularios (`csrf_token()` / `csrf_verify()`), sesiones PHP, control de acceso por rol (`requiere_login()`, `requiere_admin()`) y escape de salida HTML (`e()`).

## Usuarios de prueba

El script `database/motorent.sql` crea automáticamente una cuenta de administrador de ejemplo:

| Rol            | Email               | Contraseña  |
|----------------|----------------------|-------------|
| Administrador  | `admin@motorent.cr`  | `Admin123*` |

No se pre-cargan cuentas de tipo cliente: para probar el flujo de cliente, hay que registrar un usuario nuevo desde `/registro` (queda con rol `cliente` por defecto).

## Cómo ejecutar el proyecto (con XAMPP)

1. **Instalar XAMPP** (si no lo tienes) y colocar este proyecto dentro de `C:\xampp\htdocs\motorent`.
2. **Iniciar Apache y MySQL** desde el Panel de Control de XAMPP.
3. **Crear la base de datos**:
   - Abrir [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
   - Ir a la pestaña **SQL** e importar/ejecutar el contenido de `database/motorent.sql` (crea la base `motorent`, sus tablas, el usuario administrador y algunas motos de ejemplo).
4. **Revisar configuración** en `config/Database.php` (usuario `root` sin contraseña, tal como viene por defecto en XAMPP) y ajustar si tu instalación es distinta.

5. **Correo y Firebase**: copiar `config/mail.example.php` como `config/mail.php`, y `config/firebase-credentials.example.json` como `config/firebase-credentials.json`, y completar ambos con credenciales reales. Estos dos archivos están en `.gitignore` (no se suben al repositorio) porque contienen datos sensibles; sin ellos, el sitio funciona igual, pero fallan la recuperación de contraseña, el correo de confirmación de reserva y la subida de imágenes.
6. **Abrir la aplicación** en el navegador:
   ```
   http://localhost/motorent/public
   ```
7. Iniciar sesión con el usuario administrador de prueba (ver tabla arriba) para acceder al panel en `/admin/dashboard`, o registrarte como cliente nuevo desde `/registro`.
