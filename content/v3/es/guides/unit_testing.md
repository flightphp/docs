# Pruebas Unitarias en Flight PHP con PHPUnit

Esta guía introduce las pruebas unitarias en Flight PHP utilizando [PHPUnit](https://phpunit.de/), dirigida a principiantes que desean entender *por qué* las pruebas unitarias son importantes y cómo aplicarlas de manera práctica. Nos enfocaremos en probar *comportamientos*—asegurando que tu aplicación haga lo que esperas, como enviar un correo electrónico o guardar un registro—en lugar de cálculos triviales. Comenzaremos con un simple [manejador de rutas](/learn/routing) y avanzaremos a un [controlador](/learn/routing) más complejo, incorporando [inyección de dependencias](/learn/dependency-injection-container) (DI) y simulando servicios de terceros.

## ¿Por qué realizar pruebas unitarias?

Las pruebas unitarias aseguran que tu código se comporte como se espera, detectando errores antes de que lleguen a producción. Es especialmente valioso en Flight, donde el enrutamiento ligero y la flexibilidad pueden llevar a interacciones complejas. Para desarrolladores individuales o equipos, las pruebas unitarias actúan como una red de seguridad, documentando el comportamiento esperado y previniendo regresiones cuando revisitas el código más tarde. También mejoran el diseño: el código difícil de probar a menudo indica clases excesivamente complejas o fuertemente acopladas.

A diferencia de ejemplos simplistas (por ejemplo, probar `x * y = z`), nos enfocaremos en comportamientos del mundo real, como validar entradas, guardar datos o activar acciones como correos electrónicos. Nuestro objetivo es hacer que las pruebas sean accesibles y significativas.

## Principios Guía Generales

1. **Probar Comportamiento, No Implementación**: Enfócate en resultados (por ejemplo, “correo enviado” o “registro guardado”) en lugar de detalles internos. Esto hace que las pruebas sean robustas frente a refactorizaciones.
2. **Deja de usar `Flight::`**: Los métodos estáticos de Flight son terriblemente convenientes, pero hacen que las pruebas sean difíciles. Debes acostumbrarte a usar la variable `$app` de `$app = Flight::app();`. `$app` tiene todos los mismos métodos que `Flight::`. Todavía podrás usar `$app->route()` o `$this->app->json()` en tu controlador, etc. También debes usar el enrutador real de Flight con `$router = $app->router()` y luego podrás usar `$router->get()`, `$router->post()`, `$router->group()`, etc. Ver [Enrutamiento](/learn/routing).
3. **Mantén las Pruebas Rápidas**: Las pruebas rápidas fomentan ejecuciones frecuentes. Evita operaciones lentas como llamadas a bases de datos en pruebas unitarias. Si tienes una prueba lenta, es una señal de que estás escribiendo una prueba de integración, no una prueba unitaria. Las pruebas de integración son cuando realmente involucras bases de datos reales, llamadas HTTP reales, envío de correos reales, etc. Tienen su lugar, pero son lentas y pueden ser inestables, lo que significa que a veces fallan por una razón desconocida. 
4. **Usa Nombres Descriptivos**: Los nombres de las pruebas deben describir claramente el comportamiento que se está probando. Esto mejora la legibilidad y el mantenimiento.
5. **Evita Globales Como la Peste**: Minimiza el uso de `$app->set()` y `$app->get()`, ya que actúan como estado global, requiriendo simulaciones en cada prueba. Prefiere DI o un contenedor DI (ver [Contenedor de Inyección de Dependencias](/learn/dependency-injection-container)). Incluso usar el método `$app->map()` es técnicamente un "global" y debe evitarse en favor de DI. Usa una biblioteca de sesiones como [flightphp/session](https://github.com/flightphp/session) para que puedas simular el objeto de sesión en tus pruebas. **No** llames a [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php) directamente en tu código, ya que eso inyecta una variable global en tu código, haciendo que sea difícil de probar.
6. **Usa Inyección de Dependencias**: Inyecta dependencias (por ejemplo, [`PDO`](https://www.php.net/manual/en/class.pdo.php), remitentes de correo) en los controladores para aislar la lógica y simplificar la simulación. Si tienes una clase con demasiadas dependencias, considera refactorizarla en clases más pequeñas que cada una tenga una sola responsabilidad siguiendo los [principios SOLID](https://en.wikipedia.org/wiki/SOLID).
7. **Simula Servicios de Terceros**: Simula bases de datos, clientes HTTP (cURL) o servicios de correo para evitar llamadas externas. Prueba una o dos capas de profundidad, pero deja que tu lógica principal se ejecute. Por ejemplo, si tu aplicación envía un mensaje de texto, **NO** quieres enviar realmente un mensaje de texto cada vez que ejecutes tus pruebas porque esos cargos se acumularán (y será más lento). En su lugar, simula el servicio de mensaje de texto y solo verifica que tu código llamó al servicio de mensaje de texto con los parámetros correctos.
8. **Apunta a una Alta Cobertura, No a la Perfección**: 100% de cobertura de líneas es bueno, pero no significa realmente que todo en tu código se pruebe de la manera que debería (adelante, investiga [cobertura de rama/camino en PHPUnit](https://localheinz.com/articles/2023/03/22/collecting-line-branch-and-path-coverage-with-phpunit/)). Prioriza comportamientos críticos (por ejemplo, registro de usuarios, respuestas de API y capturar respuestas fallidas).
9. **Usa Controladores para Rutas**: En tus definiciones de rutas, usa controladores no closures. La instancia `flight\Engine $app` se inyecta en cada controlador a través del constructor por defecto. En las pruebas, usa `$app = new Flight\Engine()` para instanciar Flight dentro de una prueba, inyectarla en tu controlador y llamar métodos directamente (por ejemplo, `$controller->register()`). Ver [Extensión de Flight](/learn/extending) y [Enrutamiento](/learn/routing).
10. **Elige un estilo de simulación y mantente con él**: PHPUnit soporta varios estilos de simulación (por ejemplo, prophecy, simulaciones integradas), o puedes usar clases anónimas que tienen sus propios beneficios como completado de código, romper si cambias la definición del método, etc. Solo sé consistente en todas tus pruebas. Ver [Objetos Mock de PHPUnit](https://docs.phpunit.de/en/12.3/test-doubles.html#test-doubles).
11. **Usa visibilidad `protected` para métodos/propiedades que quieras probar en subclases**: Esto te permite sobrescribirlos en subclases de prueba sin hacerlos públicos, esto es especialmente útil para simulaciones de clases anónimas.

## Configurando PHPUnit

Primero, configura [PHPUnit](https://phpunit.de/) en tu proyecto Flight PHP usando Composer para pruebas fáciles. Ver la [guía de inicio de PHPUnit](https://phpunit.readthedocs.io/en/12.3/installation.html) para más detalles.

1. En el directorio de tu proyecto, ejecuta:
   ```bash
   composer require --dev phpunit/phpunit
   ```
   Esto instala la última versión de PHPUnit como una dependencia de desarrollo.

2. Crea un directorio `tests` en la raíz de tu proyecto para archivos de prueba.

3. Agrega un script de prueba a `composer.json` para mayor comodidad:
   ```json
   // otro contenido de composer.json
   "scripts": {
       "test": "phpunit --configuration phpunit.xml"
   }
   ```

4. Crea un archivo `phpunit.xml` en la raíz:
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

Ahora, cuando tus pruebas estén construidas, puedes ejecutar `composer test` para ejecutar las pruebas.

## Probando un Manejador de Ruta Simple

Comencemos con una ruta básica [ruta](/learn/routing) que valida la entrada de correo electrónico de un usuario. Probaremos su comportamiento: devolviendo un mensaje de éxito para correos válidos y un error para los inválidos. Para la validación de correo, usamos [`filter_var`](https://www.php.net/manual/en/function.filter-var.php).

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
		$responseArray = [];
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$responseArray = ['status' => 'error', 'message' => 'Invalid email'];
		} else {
			$responseArray = ['status' => 'success', 'message' => 'Valid email'];
		}

		$this->app->json($responseArray);
	}
}
```

Para probar esto, crea un archivo de prueba. Ver [Pruebas Unitarias y Principios SOLID](/learn/unit-testing-and-solid-principles) para más sobre estructuración de pruebas:

```php
// tests/UserControllerTest.php
use PHPUnit\Framework\TestCase;
use Flight;
use flight\Engine;

class UserControllerTest extends TestCase {

    public function testValidEmailReturnsSuccess() {
		$app = new Engine();
		$request = $app->request();
		$request->data->email = 'test@example.com'; // Simulate POST data
		$UserController = new UserController($app);
		$UserController->register($request->data->email);
        $response = $app->response()->getBody();
		$output = json_decode($response, true);
        $this->assertEquals('success', $output['status']);
        $this->assertEquals('Valid email', $output['message']);
    }

    public function testInvalidEmailReturnsError() {
		$app = new Engine();
		$request = $app->request();
		$request->data->email = 'invalid-email'; // Simulate POST data
		$UserController = new UserController($app);
		$UserController->register($request->data->email);
		$response = $app->response()->getBody();
		$output = json_decode($response, true);
		$this->assertEquals('error', $output['status']);
		$this->assertEquals('Invalid email', $output['message']);
	}
}
```

**Puntos Clave**:
- Simulamos datos POST usando la clase request. No uses globales como `$_POST`, `$_GET`, etc., ya que hace que las pruebas sean más complicadas (tienes que resetear siempre esos valores o otras pruebas podrían fallar).
- Todos los controladores por defecto tendrán la instancia `flight\Engine` inyectada en ellos incluso sin configurar un contenedor DIC. Esto hace que sea mucho más fácil probar controladores directamente.
- No hay uso de `Flight::` en absoluto, haciendo que el código sea más fácil de probar.
- Las pruebas verifican comportamiento: estado y mensaje correctos para correos válidos/inválidos.

Ejecuta `composer test` para verificar que la ruta se comporte como se espera. Para más sobre [requests](/learn/requests) y [responses](/learn/responses) en Flight, ver la documentación relevante.

## Usando Inyección de Dependencias para Controladores Probables

Para escenarios más complejos, usa [inyección de dependencias](/learn/dependency-injection-container) (DI) para hacer controladores probables. Evita los globales de Flight (por ejemplo, `Flight::set()`, `Flight::map()`, `Flight::register()`) ya que actúan como estado global, requiriendo simulaciones para cada prueba. En su lugar, usa el contenedor DI de Flight, [DICE](https://github.com/Level-2/Dice), [PHP-DI](https://php-di.org/) o DI manual.

Usemos [`flight\database\PdoWrapper`](/learn/pdo-wrapper) en lugar de PDO crudo. ¡Este wrapper es mucho más fácil de simular y probar unitariamente!

Aquí hay un controlador que guarda un usuario en una base de datos y envía un correo de bienvenida:

```php
use flight\database\PdoWrapper;

class UserController {
    protected $app;
    protected $db;
    protected $mailer;

    public function __construct(Engine $app, PdoWrapper $db, MailerInterface $mailer) {
        $this->app = $app;
        $this->db = $db;
        $this->mailer = $mailer;
    }

    public function register() {
		$email = $this->app->request()->data->email;
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			// adding the return here helps unit testing to stop execution
			return $this->app->jsonHalt(['status' => 'error', 'message' => 'Invalid email']);
		}

		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);

		return $this->app->json(['status' => 'success', 'message' => 'User registered']);
    }
}
```

**Puntos Clave**:
- El controlador depende de una instancia [`PdoWrapper`](/learn/pdo-wrapper) y una `MailerInterface` (un servicio de correo de terceros ficticio).
- Las dependencias se inyectan a través del constructor, evitando globales.

### Probando el Controlador con Simulaciones

Ahora, probemos el comportamiento de `UserController`: validando correos, guardando en la base de datos y enviando correos. Simularemos la base de datos y el remitente para aislar el controlador.

```php
// tests/UserControllerDICTest.php
use PHPUnit\Framework\TestCase;

class UserControllerDICTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {

		// Sometimes mixing mocking styles is necessary
		// Here we use PHPUnit's built-in mock for PDOStatement
		$statementMock = $this->createMock(PDOStatement::class);
		$statementMock->method('execute')->willReturn(true);
		// Using an anonymous class to mock PdoWrapper
        $mockDb = new class($statementMock) extends PdoWrapper {
			protected $statementMock;
			public function __construct($statementMock) {
				$this->statementMock = $statementMock;
			}

			// When we mock it this way, we are not really making a database call.
			// We can further setup this to alter the PDOStatement mock to simulate failures, etc.
            public function runQuery(string $sql, array $params = []): PDOStatement {
                return $this->statementMock;
            }
        };
        $mockMailer = new class implements MailerInterface {
            public $sentEmail = null;
            public function sendWelcome($email): bool {
                $this->sentEmail = $email;
                return true;	
            }
        };
		$app = new Engine();
		$app->request()->data->email = 'test@example.com';
        $controller = new UserControllerDIC($app, $mockDb, $mockMailer);
        $controller->register();
		$response = $app->response()->getBody();
		$result = json_decode($response, true);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('User registered', $result['message']);
        $this->assertEquals('test@example.com', $mockMailer->sentEmail);
    }

    public function testInvalidEmailSkipsSaveAndEmail() {
		 $mockDb = new class() extends PdoWrapper {
			// An empty constructor bypasses the parent constructor
			public function __construct() {}
            public function runQuery(string $sql, array $params = []): PDOStatement {
                throw new Exception('Should not be called');
            }
        };
        $mockMailer = new class implements MailerInterface {
            public $sentEmail = null;
            public function sendWelcome($email): bool {
                throw new Exception('Should not be called');
            }
        };
		$app = new Engine();
		$app->request()->data->email = 'invalid-email';

		// Need to map jsonHalt to avoid exiting
		$app->map('jsonHalt', function($data) use ($app) {
			$app->json($data, 400);
		});
        $controller = new UserControllerDIC($app, $mockDb, $mockMailer);
        $controller->register();
        $response = $app->response()->getBody();
        $result = json_decode($response, true);
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Invalid email', $result['message']);
    }
}
```

**Puntos Clave**:
- Simulamos `PdoWrapper` y `MailerInterface` para evitar llamadas reales a base de datos o correos.
- Las pruebas verifican comportamiento: correos válidos activan inserciones en base de datos y envíos de correo; correos inválidos saltan ambos.
- Simula dependencias de terceros (por ejemplo, `PdoWrapper`, `MailerInterface`), dejando que la lógica del controlador se ejecute.

### Simulando Demasiado

Ten cuidado de no simular demasiado de tu código. Déjame darte un ejemplo abajo sobre por qué esto podría ser algo malo usando nuestro `UserController`. Cambiaremos esa verificación en un método llamado `isEmailValid` (usando `filter_var`) y las otras nuevas adiciones en un método separado llamado `registerUser`.

```php
use flight\database\PdoWrapper;
use flight\Engine;

// UserControllerDICV2.php
class UserControllerDICV2 {
	protected $app;
    protected $db;
    protected $mailer;

    public function __construct(Engine $app, PdoWrapper $db, MailerInterface $mailer) {
        $this->app = $app;
        $this->db = $db;
        $this->mailer = $mailer;
    }

    public function register() {
		$email = $this->app->request()->data->email;
		if (!$this->isEmailValid($email)) {
			// adding the return here helps unit testing to stop execution
			return $this->app->jsonHalt(['status' => 'error', 'message' => 'Invalid email']);
		}

		$this->registerUser($email);

		$this->app->json(['status' => 'success', 'message' => 'User registered']);
    }

	protected function isEmailValid($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
	}

	protected function registerUser($email) {
		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);
	}
}
```

Y ahora la prueba unitaria sobremapeada que no prueba realmente nada:

```php
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {
		$app = new Engine();
		$app->request()->data->email = 'test@example.com';
		// we are skipping the extra dependency injection here cause it's "easy"
        $controller = new class($app) extends UserControllerDICV2 {
			protected $app;
			// Bypass the deps in the construct
			public function __construct($app) {
				$this->app = $app;
			}

			// We'll just force this to be valid.
			protected function isEmailValid($email) {
				return true; // Always return true, bypassing real validation
			}

			// Bypass the actual DB and mailer calls
			protected function registerUser($email) {
				return false;
			}
		};
        $controller->register();
		$response = $app->response()->getBody();
		$result = json_decode($response, true);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('User registered', $result['message']);
    }
}
```

¡Hurra, tenemos pruebas unitarias y están pasando! Pero espera, ¿qué pasa si realmente cambio el funcionamiento interno de `isEmailValid` o `registerUser`? Mis pruebas seguirán pasando porque he simulado toda la funcionalidad. Déjame mostrarte lo que quiero decir.

```php
// UserControllerDICV2.php
class UserControllerDICV2 {

	// ... other methods ...

	protected function isEmailValid($email) {
		// Changed logic
		$validEmail = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
		// Now it should only have a specific domain
		$validDomain = strpos($email, '@example.com') !== false; 
		return $validEmail && $validDomain;
	}
}
```

Si ejecuto mis pruebas unitarias anteriores, ¡todavía pasan! Pero porque no estaba probando por comportamiento (dejando que algo del código se ejecute realmente), he codificado potencialmente un error esperando ocurrir en producción. La prueba debería modificarse para tener en cuenta el nuevo comportamiento, y también lo opuesto de cuando el comportamiento no es lo que esperamos.

## Ejemplo Completo

Puedes encontrar un ejemplo completo de un proyecto Flight PHP con pruebas unitarias en GitHub: [n0nag0n/flight-unit-tests-guide](https://github.com/n0nag0n/flight-unit-tests-guide).
Para una comprensión más profunda, ver [Pruebas Unitarias y Principios SOLID](/learn/unit-testing-and-solid-principles).

## Errores Comunes

- **Sobremapeo**: No simules cada dependencia; deja que algo de lógica (por ejemplo, validación de controlador) se ejecute para probar comportamiento real. Ver [Pruebas Unitarias y Principios SOLID](/learn/unit-testing-and-solid-principles).
- **Estado Global**: Usar variables PHP globales (por ejemplo, [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php), [`$_COOKIE`](https://www.php.net/manual/en/reserved.variables.cookie.php)) de manera intensiva hace que las pruebas sean frágiles. Lo mismo con `Flight::`. Refactoriza para pasar dependencias explícitamente.
- **Configuración Compleja**: Si la configuración de la prueba es engorrosa, tu clase puede tener demasiadas dependencias o responsabilidades violando los [principios SOLID](/learn/unit-testing-and-solid-principles).

## Escalando con Pruebas Unitarias

Las pruebas unitarias brillan en proyectos más grandes o cuando revisitas código después de meses. Documentan comportamiento y detectan regresiones, ahorrándote de re-aprender tu aplicación. Para desarrolladores individuales, prueba rutas críticas (por ejemplo, registro de usuarios, procesamiento de pagos). Para equipos, las pruebas aseguran comportamiento consistente a través de contribuciones. Ver [¿Por qué Frameworks?](/learn/why-frameworks) para más sobre los beneficios de usar frameworks y pruebas.

¡Contribuye con tus propios consejos de pruebas al repositorio de documentación de Flight PHP!

_Escrito por [n0nag0n](https://github.com/n0nag0n) 2025_