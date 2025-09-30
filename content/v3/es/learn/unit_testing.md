# Pruebas Unitarias

## Resumen

Las pruebas unitarias en Flight te ayudan a asegurar que tu aplicación se comporte como se espera, detecta errores tempranamente y hace que tu código sea más fácil de mantener. Flight está diseñado para trabajar sin problemas con [PHPUnit](https://phpunit.de/), el framework de pruebas para PHP más popular.

## Comprensión

Las pruebas unitarias verifican el comportamiento de pequeñas piezas de tu aplicación (como controladores o servicios) de manera aislada. En Flight, esto significa probar cómo responden tus rutas, controladores y lógica a diferentes entradas—sin depender de estados globales o servicios externos reales.

Principios clave:
- **Probar comportamiento, no implementación:** Enfócate en lo que hace tu código, no en cómo lo hace.
- **Evitar estados globales:** Usa inyección de dependencias en lugar de `Flight::set()` o `Flight::get()`.
- **Simular servicios externos:** Reemplaza cosas como bases de datos o remitentes de correo con dobles de prueba.
- **Mantén las pruebas rápidas y enfocadas:** Las pruebas unitarias no deben acceder a bases de datos reales o APIs.

## Uso Básico

### Configuración de PHPUnit

1. Instala PHPUnit con Composer:
   ```bash
   composer require --dev phpunit/phpunit
   ```
2. Crea un directorio `tests` en la raíz de tu proyecto.
3. Agrega un script de prueba a tu `composer.json`:
   ```json
   "scripts": {
       "test": "phpunit --configuration phpunit.xml"
   }
   ```
4. Crea un archivo `phpunit.xml`:
   ```xml
   <?xml version="1.0" encoding="UTF-8"?>
   <phpunit bootstrap="vendor/autoload.php">
       <testsuites>
           <testsuite name="Flight Tests">
               <directory>tests</directory>
           </testsuite>
       </testsuites>
   </phpunit>
   ```

Ahora puedes ejecutar tus pruebas con `composer test`.

### Probando un Manipulador de Ruta Simple

Supongamos que tienes una ruta que valida un correo electrónico:

```php
// index.php
$app->route('POST /register', [ UserController::class, 'register' ]);

// UserController.php
class UserController {
    protected $app;
    public function __construct(flight\Engine $app) {
        $this->app = $app;
    }
    public function register() {
        $email = $this->app->request()->data->email;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->app->json(['status' => 'error', 'message' => 'Invalid email']);
        }
        return $this->app->json(['status' => 'success', 'message' => 'Valid email']);
    }
}
```

Una prueba simple para este controlador:

```php
use PHPUnit\Framework\TestCase;
use flight\Engine;

class UserControllerTest extends TestCase {
    public function testValidEmailReturnsSuccess() {
        $app = new Engine();
        $app->request()->data->email = 'test@example.com';
        $controller = new UserController($app);
        $controller->register();
        $response = $app->response()->getBody();
        $output = json_decode($response, true);
        $this->assertEquals('success', $output['status']);
        $this->assertEquals('Valid email', $output['message']);
    }

    public function testInvalidEmailReturnsError() {
        $app = new Engine();
        $app->request()->data->email = 'invalid-email';
        $controller = new UserController($app);
        $controller->register();
        $response = $app->response()->getBody();
        $output = json_decode($response, true);
        $this->assertEquals('error', $output['status']);
        $this->assertEquals('Invalid email', $output['message']);
    }
}
```

**Consejos:**
- Simula datos POST usando `$app->request()->data`.
- Evita usar estáticos `Flight::` en tus pruebas—usa la instancia `$app`.

### Usando Inyección de Dependencias para Controladores Probables

Inyecta dependencias (como la base de datos o el remitente de correo) en tus controladores para hacerlos fáciles de simular en pruebas:

```php
use flight\database\PdoWrapper;

class UserController {
    protected $app;
    protected $db;
    protected $mailer;
    public function __construct($app, $db, $mailer) {
        $this->app = $app;
        $this->db = $db;
        $this->mailer = $mailer;
    }
    public function register() {
        $email = $this->app->request()->data->email;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->app->json(['status' => 'error', 'message' => 'Invalid email']);
        }
        $this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
        $this->mailer->sendWelcome($email);
        return $this->app->json(['status' => 'success', 'message' => 'User registered']);
    }
}
```

Y una prueba con simulaciones:

```php
use PHPUnit\Framework\TestCase;

class UserControllerDICTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {
        $mockDb = $this->createMock(flight\database\PdoWrapper::class);
        $mockDb->method('runQuery')->willReturn(true);
        $mockMailer = new class {
            public $sentEmail = null;
            public function sendWelcome($email) { $this->sentEmail = $email; return true; }
        };
        $app = new flight\Engine();
        $app->request()->data->email = 'test@example.com';
        $controller = new UserController($app, $mockDb, $mockMailer);
        $controller->register();
        $response = $app->response()->getBody();
        $result = json_decode($response, true);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('User registered', $result['message']);
        $this->assertEquals('test@example.com', $mockMailer->sentEmail);
    }
}
```

## Uso Avanzado

- **Simulación:** Usa las simulaciones integradas de PHPUnit o clases anónimas para reemplazar dependencias.
- **Probando controladores directamente:** Instancia controladores con un nuevo `Engine` y simula dependencias.
- **Evita sobre-simular:** Deja que la lógica real se ejecute donde sea posible; solo simula servicios externos.

## Ver También

- [Guía de Pruebas Unitarias](/guides/unit-testing) - Una guía completa sobre las mejores prácticas para pruebas unitarias.
- [Contenedor de Inyección de Dependencias](/learn/dependency-injection-container) - Cómo usar DIC para gestionar dependencias y mejorar la probabilidad de pruebas.
- [Extensión](/learn/extending) - Cómo agregar tus propios ayudantes o sobrescribir clases principales.
- [Envoltorio PDO](/learn/pdo-wrapper) - Simplifica las interacciones con la base de datos y es más fácil de simular en pruebas.
- [Solicitudes](/learn/requests) - Manejo de solicitudes HTTP en Flight.
- [Respuestas](/learn/responses) - Envío de respuestas a los usuarios.
- [Pruebas Unitarias y Principios SOLID](/learn/unit-testing-and-solid-principles) - Aprende cómo los principios SOLID pueden mejorar tus pruebas unitarias.

## Solución de Problemas

- Evita usar estados globales (`Flight::set()`, `$_SESSION`, etc.) en tu código y pruebas.
- Si tus pruebas son lentas, es posible que estés escribiendo pruebas de integración—simula servicios externos para mantener las pruebas unitarias rápidas.
- Si la configuración de pruebas es compleja, considera refactorizar tu código para usar inyección de dependencias.

## Registro de Cambios

- v3.15.0 - Agregados ejemplos para inyección de dependencias y simulación.