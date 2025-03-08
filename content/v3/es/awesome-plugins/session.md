# FlightPHP Sesión - Controlador de Sesiones Basado en Archivos Liviano

Este es un plugin liviano de controlador de sesiones basado en archivos para el [Flight PHP Framework](https://docs.flightphp.com/). Proporciona una solución simple pero poderosa para gestionar sesiones, con características como lecturas de sesiones no bloqueantes, cifrado opcional, funcionalidad de auto-confirmación y un modo de prueba para el desarrollo. Los datos de la sesión se almacenan en archivos, lo que lo hace ideal para aplicaciones que no requieren una base de datos.

Si deseas usar una base de datos, consulta el plugin [ghostff/session](/awesome-plugins/ghost-session) que tiene muchas de estas mismas características pero con un backend de base de datos.

Visita el [repositorio de Github](https://github.com/flightphp/session) para el código fuente completo y detalles.

## Instalación

Instala el plugin a través de Composer:

```bash
composer require flightphp/session
```

## Uso Básico

Aquí tienes un ejemplo simple de cómo usar el plugin `flightphp/session` en tu aplicación Flight:

```php
require 'vendor/autoload.php';

use flight\Session;

$app = Flight::app();

// Registrar el servicio de sesión
$app->register('session', Session::class);

// Ruta de ejemplo con uso de sesión
Flight::route('/login', function() {
    $session = Flight::session();
    $session->set('user_id', 123);
    $session->set('username', 'johndoe');
    $session->set('is_admin', false);

    echo $session->get('username'); // Salida: johndoe
    echo $session->get('preferences', 'default_theme'); // Salida: default_theme

    if ($session->get('user_id')) {
        Flight::json(['message' => '¡El usuario ha iniciado sesión!', 'user_id' => $session->get('user_id')]);
    }
});

Flight::route('/logout', function() {
    $session = Flight::session();
    $session->clear(); // Limpiar todos los datos de la sesión
    Flight::json(['message' => '¡Salida exitosa!']);
});

Flight::start();
```

### Puntos Clave
- **No Bloqueante**: Utiliza `read_and_close` para iniciar la sesión de manera predeterminada, evitando problemas de bloqueo de sesión.
- **Auto-Confirmar**: Habilitado por defecto, por lo que los cambios se guardan automáticamente al apagar, a menos que se desactive.
- **Almacenamiento en Archivos**: Las sesiones se almacenan en el directorio temporal del sistema bajo `/flight_sessions` por defecto.

## Configuración

Puedes personalizar el controlador de sesiones pasando un array de opciones al registrarte:

```php
$app->register('session', Session::class, [
    'save_path' => '/custom/path/to/sessions',         // Directorio para archivos de sesión
    'encryption_key' => 'a-secure-32-byte-key-here',   // Habilitar cifrado (32 bytes recomendados para AES-256-CBC)
    'auto_commit' => false,                            // Desactivar auto-confirmación para control manual
    'start_session' => true,                           // Iniciar sesión automáticamente (predeterminado: true)
    'test_mode' => false                               // Habilitar modo de prueba para el desarrollo
]);
```

### Opciones de Configuración
| Opción            | Descripción                                      | Valor Predeterminado                     |
|-------------------|--------------------------------------------------|-----------------------------------|
| `save_path`       | Directorio donde se almacenan los archivos de sesión         | `sys_get_temp_dir() . '/flight_sessions'` |
| `encryption_key`  | Clave para cifrado AES-256-CBC (opcional)        | `null` (sin cifrado)            |
| `auto_commit`     | Guardar automáticamente los datos de la sesión al apagarse               | `true`                            |
| `start_session`   | Iniciar la sesión automáticamente                  | `true`                            |
| `test_mode`       | Ejecutar en modo de prueba sin afectar las sesiones de PHP  | `false`                           |
| `test_session_id` | ID de sesión personalizado para modo de prueba (opcional)       | Generado aleatoriamente si no está configurado     |

## Uso Avanzado

### Confirmación Manual
Si desactivas la auto-confirmación, debes confirmar manualmente los cambios:

```php
$app->register('session', Session::class, ['auto_commit' => false]);

Flight::route('/update', function() {
    $session = Flight::session();
    $session->set('key', 'value');
    $session->commit(); // Guardar cambios explícitamente
});
```

### Seguridad de la Sesión con Cifrado
Habilitar el cifrado para datos sensibles:

```php
$app->register('session', Session::class, [
    'encryption_key' => 'your-32-byte-secret-key-here'
]);

Flight::route('/secure', function() {
    $session = Flight::session();
    $session->set('credit_card', '4111-1111-1111-1111'); // Cifrado automáticamente
    echo $session->get('credit_card'); // Desencriptado al recuperar
});
```

### Regeneración de Sesión
Regenerar el ID de sesión por seguridad (por ejemplo, después de iniciar sesión):

```php
Flight::route('/post-login', function() {
    $session = Flight::session();
    $session->regenerate(); // Nuevo ID, conservar datos
    // O
    $session->regenerate(true); // Nuevo ID, eliminar datos antiguos
});
```

### Ejemplo de Middleware
Proteger rutas con autenticación basada en sesión:

```php
Flight::route('/admin', function() {
    Flight::json(['message' => 'Bienvenido al panel de administración']);
})->addMiddleware(function() {
    $session = Flight::session();
    if (!$session->get('is_admin')) {
        Flight::halt(403, 'Acceso denegado');
    }
});
```

Este es solo un ejemplo simple de cómo usar esto en middleware. Para un ejemplo más detallado, consulta la documentación de [middleware](/learn/middleware).

## Métodos

La clase `Session` proporciona estos métodos:

- `set(string $key, $value)`: Almacena un valor en la sesión.
- `get(string $key, $default = null)`: Recupera un valor, con un valor predeterminado opcional si la clave no existe.
- `delete(string $key)`: Elimina una clave específica de la sesión.
- `clear()`: Elimina todos los datos de la sesión.
- `commit()`: Guarda los datos actuales de la sesión en el sistema de archivos.
- `id()`: Devuelve el ID de sesión actual.
- `regenerate(bool $deleteOld = false)`: Regenera el ID de sesión, opcionalmente eliminando datos antiguos.

Todos los métodos excepto `get()` y `id()` devuelven la instancia de `Session` para la encadenación.

## ¿Por Qué Usar Este Plugin?

- **Liviano**: Sin dependencias externas, solo archivos.
- **No Bloqueante**: Evita el bloqueo de sesiones con `read_and_close` por defecto.
- **Seguro**: Soporta cifrado AES-256-CBC para datos sensibles.
- **Flexible**: Opciones de auto-confirmación, modo de prueba y control manual.
- **Nativo de Flight**: Construido específicamente para el framework Flight.

## Detalles Técnicos

- **Formato de Almacenamiento**: Los archivos de sesión están prefijados con `sess_` y almacenados en el `save_path` configurado. Los datos cifrados utilizan un prefijo `E`, los datos en texto plano utilizan `P`.
- **Cifrado**: Utiliza AES-256-CBC con un IV aleatorio por cada escritura de sesión cuando se proporciona una `encryption_key`.
- **Recolección de Basura**: Implementa el `SessionHandlerInterface::gc()` de PHP para limpiar sesiones caducadas.

## Contribuyendo

¡Las contribuciones son bienvenidas! Haz un fork del [repositorio](https://github.com/flightphp/session), realiza tus cambios y envía una solicitud de extracción. Informa sobre errores o sugiere funcionalidades a través del rastreador de problemas de Github.

## Licencia

Este plugin está bajo la Licencia MIT. Consulta el [repositorio de Github](https://github.com/flightphp/session) para detalles.