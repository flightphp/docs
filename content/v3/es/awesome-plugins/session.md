# FlightPHP Sesión - Manejador de Sesiones Ligero Basado en Archivos

Esto es un plugin ligero, basado en archivos, para manejar sesiones en el [Flight PHP Framework](https://docs.flightphp.com/). Proporciona una solución simple pero potente para gestionar sesiones, con características como lecturas de sesiones no bloqueantes, cifrado opcional, funcionalidad de auto-commit y un modo de prueba para desarrollo. Los datos de sesión se almacenan en archivos, lo que lo hace ideal para aplicaciones que no requieren una base de datos.

Si deseas usar una base de datos, consulta el plugin [ghostff/session](/awesome-plugins/ghost-session) que incluye muchas de estas mismas características pero con un backend de base de datos.

Visita el [repositorio de Github](https://github.com/flightphp/session) para el código fuente completo y detalles.

## Instalación

Instala el plugin a través de Composer:

```bash
composer require flightphp/session
```

## Uso Básico

Aquí hay un ejemplo simple de cómo usar el plugin `flightphp/session` en tu aplicación Flight:

```php
require 'vendor/autoload.php';

use flight\Session;

$app = Flight::app();

// Registra el servicio de sesión
$app->register('session', Session::class);

// Ejemplo de ruta con uso de sesión
Flight::route('/login', function() {
    $session = Flight::session();
    $session->set('user_id', 123);
    $session->set('username', 'johndoe');
    $session->set('is_admin', false);

    echo $session->get('username'); // Esto imprime: johndoe
    echo $session->get('preferences', 'default_theme'); // Esto imprime: default_theme

    if ($session->get('user_id')) {
        Flight::json(['message' => '¡El usuario está logueado!', 'user_id' => $session->get('user_id')]);
    }
});

Flight::route('/logout', function() {
    $session = Flight::session();
    $session->clear(); // Limpia todos los datos de sesión
    Flight::json(['message' => 'Cerrado de sesión exitoso']);
});

Flight::start();
```

### Puntos Clave
- **No Bloqueante**: Usa `read_and_close` para iniciar la sesión por defecto, previniendo problemas de bloqueo de sesiones.
- **Auto-Commit**: Habilitado por defecto, por lo que los cambios se guardan automáticamente al cerrar a menos que se desactive.
- **Almacenamiento en Archivos**: Las sesiones se almacenan en el directorio temporal del sistema bajo `/flight_sessions` por defecto.

## Configuración

Puedes personalizar el manejador de sesiones pasando un arreglo de opciones al registrar:

```php
// Sí, es un arreglo doble :)
$app->register('session', Session::class, [ [
    'save_path' => '/custom/path/to/sessions',         // Directorio para los archivos de sesión
	'prefix' => 'myapp_',                              // Prefijo para los archivos de sesión
    'encryption_key' => 'a-secure-32-byte-key-here',   // Habilita el cifrado (se recomienda 32 bytes para AES-256-CBC)
    'auto_commit' => false,                            // Desactiva el auto-commit para control manual
    'start_session' => true,                           // Inicia la sesión automáticamente (por defecto: true)
    'test_mode' => false,                              // Habilita el modo de prueba para desarrollo
    'serialization' => 'json',                         // Método de serialización: 'json' (por defecto) o 'php' (heredado)
] ]);
```

### Opciones de Configuración
| Opción            | Descripción                                      | Valor por Defecto                     |
|-------------------|--------------------------------------------------|---------------------------------------|
| `save_path`       | Directorio donde se almacenan los archivos de sesión         | `sys_get_temp_dir() . '/flight_sessions'` |
| `prefix`          | Prefijo para el archivo de sesión guardado                | `sess_`                           |
| `encryption_key`  | Clave para el cifrado AES-256-CBC (opcional)        | `null` (sin cifrado)            |
| `auto_commit`     | Guarda automáticamente los datos de sesión al cerrar               | `true`                            |
| `start_session`   | Inicia la sesión automáticamente                  | `true`                            |
| `test_mode`       | Ejecuta en modo de prueba sin afectar las sesiones de PHP  | `false`                           |
| `test_session_id` | ID de sesión personalizado para el modo de prueba (opcional)       | Generado aleatoriamente si no se establece     |
| `serialization`   | Método de serialización: 'json' (por defecto, seguro) o 'php' (heredado, permite objetos) | `'json'` |

## Modos de Serialización

Por defecto, esta biblioteca usa **serialización JSON** para los datos de sesión, lo que es seguro y evita vulnerabilidades de inyección de objetos PHP. Si necesitas almacenar objetos PHP en la sesión (no recomendado para la mayoría de las aplicaciones), puedes optar por la serialización PHP heredada:

- `'serialization' => 'json'` (por defecto):
  - Solo se permiten arreglos y primitivos en los datos de sesión.
  - Más seguro: inmune a la inyección de objetos PHP.
  - Los archivos se prefijan con `J` (JSON plano) o `F` (JSON cifrado).
- `'serialization' => 'php'`:
  - Permite almacenar objetos PHP (usa con precaución).
  - Los archivos se prefijan con `P` (serialización PHP plana) o `E` (serialización PHP cifrada).

**Nota:** Si usas serialización JSON, intentar almacenar un objeto lanzará una excepción.

## Uso Avanzado

### Commit Manual
Si desactivas el auto-commit, debes confirmar los cambios manualmente:

```php
$app->register('session', Session::class, ['auto_commit' => false]);

Flight::route('/update', function() {
    $session = Flight::session();
    $session->set('key', 'value');
    $session->commit(); // Guarda explícitamente los cambios
});
```

### Seguridad de Sesión con Cifrado
Habilita el cifrado para datos sensibles:

```php
$app->register('session', Session::class, [
    'encryption_key' => 'your-32-byte-secret-key-here'
]);

Flight::route('/secure', function() {
    $session = Flight::session();
    $session->set('credit_card', '4111-1111-1111-1111'); // Se cifra automáticamente
    echo $session->get('credit_card'); // Se descifra al recuperar
});
```

### Regeneración de Sesión
Regenera el ID de sesión por seguridad (por ejemplo, después de iniciar sesión):

```php
Flight::route('/post-login', function() {
    $session = Flight::session();
    $session->regenerate(); // Nuevo ID, mantiene los datos
    // O
    $session->regenerate(true); // Nuevo ID, elimina los datos antiguos
});
```

### Ejemplo de Middleware
Protege rutas con autenticación basada en sesiones:

```php
Flight::route('/admin', function() {
    Flight::json(['message' => 'Bienvenido al panel de administración']);
})->addMiddleware(function() {
    $session = Flight::session();
    if (!$session->get('is_admin')) {
        Flight::halt(403, 'Acceso denegado');
    }
    // Esto es solo un ejemplo simple de cómo usarlo en middleware. Para un ejemplo más detallado, consulta la documentación de [middleware](/learn/middleware).
});
```

## Métodos

La clase `Session` proporciona estos métodos:

- `set(string $key, $value)`: Almacena un valor en la sesión.
- `get(string $key, $default = null)`: Recupera un valor, con un valor predeterminado opcional si la clave no existe.
- `delete(string $key)`: Elimina una clave específica de la sesión.
- `clear()`: Elimina todos los datos de sesión, pero mantiene el mismo nombre de archivo para la sesión.
- `commit()`: Guarda los datos de sesión actuales en el sistema de archivos.
- `id()`: Devuelve el ID de sesión actual.
- `regenerate(bool $deleteOldFile = false)`: Regenera el ID de sesión, incluyendo la creación de un nuevo archivo de sesión, manteniendo todos los datos antiguos y el archivo antiguo permanece en el sistema. Si `$deleteOldFile` es `true`, se elimina el archivo de sesión antiguo.
- `destroy(string $id)`: Destruye una sesión por ID y elimina el archivo de sesión del sistema. Esto forma parte de la `SessionHandlerInterface` y `$id` es requerido. El uso típico sería `$session->destroy($session->id())`.
- `getAll()`: Devuelve todos los datos de la sesión actual.

Todos los métodos excepto `get()` y `id()` devuelven la instancia de `Session` para encadenamiento.

## ¿Por Qué Usar Este Plugin?

- **Ligero**: Sin dependencias externas, solo archivos.
- **No Bloqueante**: Evita el bloqueo de sesiones con `read_and_close` por defecto.
- **Seguro**: Soporta cifrado AES-256-CBC para datos sensibles.
- **Flexible**: Opciones de auto-commit, modo de prueba y control manual.
- **Nativo de Flight**: Construido específicamente para el framework Flight.

## Detalles Técnicos

- **Formato de Almacenamiento**: Los archivos de sesión se prefijan con `sess_` y se almacenan en el `save_path` configurado. Prefijos de contenido de archivo:
  - `J`: JSON plano (por defecto, sin cifrado)
  - `F`: JSON cifrado (por defecto con cifrado)
  - `P`: Serialización PHP plana (heredada, sin cifrado)
  - `E`: Serialización PHP cifrada (heredada con cifrado)
- **Cifrado**: Usa AES-256-CBC con un IV aleatorio por escritura de sesión cuando se proporciona una `encryption_key`. El cifrado funciona para ambos modos de serialización JSON y PHP.
- **Serialización**: JSON es el método por defecto y más seguro. La serialización PHP está disponible para usos heredados/avanzados, pero es menos segura.
- **Recolección de Basura**: Implementa `SessionHandlerInterface::gc()` de PHP para limpiar sesiones expiradas.

## Contribuciones

¡Las contribuciones son bienvenidas! Bifurca el [repositorio](https://github.com/flightphp/session), realiza tus cambios y envía una solicitud de extracción. Reporta errores o sugiere características a través del rastreador de problemas de Github.

## Licencia

Este plugin está licenciado bajo la Licencia MIT. Consulta el [repositorio de Github](https://github.com/flightphp/session) para detalles.