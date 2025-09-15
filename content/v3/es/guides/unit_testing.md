# Pruebas unitarias en Flight PHP con PHPUnit

Esta guía introduce las pruebas unitarias en Flight PHP utilizando [PHPUnit](https://phpunit.de/), dirigida a principiantes que quieran entender *por qué* las pruebas unitarias son importantes y cómo aplicarlas de manera práctica. Nos centraremos en probar *comportamientos*—asegurando que tu aplicación haga lo que se espera, como enviar un correo electrónico o guardar un registro—en lugar de cálculos triviales. Comenzaremos con un simple [route handler](/learn/routing) y avanzaremos a un [controller](/learn/routing) más complejo, incorporando [dependency injection](/learn/dependency-injection-container) (DI) y simulando servicios de terceros.

## ¿Por qué realizar pruebas unitarias?

Las pruebas unitarias aseguran que tu código se comporte como se espera, detectando errores antes de que lleguen a producción. Es especialmente valioso en Flight, donde la enrutamiento ligero y la flexibilidad pueden generar interacciones complejas. Para desarrolladores individuales o equipos, las pruebas unitarias actúan como una red de seguridad, documentando el comportamiento esperado y previniendo regresiones al revisar el código más tarde. También mejoran el diseño: el código difícil de probar a menudo indica clases demasiado complejas o fuertemente acopladas.

A diferencia de ejemplos simplistas (por ejemplo, probar `x * y = z`), nos enfocaremos en comportamientos del mundo real, como validar entradas, guardar datos o activar acciones como correos electrónicos. Nuestro objetivo es hacer que las pruebas sean accesibles y significativas.

## Principios generales de orientación

1. **Prueba el comportamiento, no la implementación**: Enfócate en resultados (por ejemplo, “correo enviado” o “registro guardado”) en lugar de detalles internos. Esto hace que las pruebas sean más robustas ante refactorizaciones.
2. **Deja de usar `Flight::`**: Los métodos estáticos de Flight son muy convenientes, pero dificultan las pruebas. Debes acostumbrarte a usar la variable `$app` de `$app = Flight::app();`. `$app` tiene todos los mismos métodos que `Flight::`. Todavía podrás usar `$app->route()` o `$this->app->json()` en tu controller, etc. También deberías usar el enrutador real de Flight con `$router = $app->router()` y luego puedes usar `$router->get()`, `$router->post()`, `$router->group()`, etc. Ver [Routing](/learn/routing).
3. **Mantén las pruebas rápidas**: Las pruebas rápidas fomentan su ejecución frecuente. Evita operaciones lentas como llamadas a bases de datos en pruebas unitarias. Si tienes una prueba lenta, es una señal de que estás escribiendo una prueba de integración, no unitaria. Las pruebas de integración involucran bases de datos reales, llamadas HTTP reales, envío de correos reales, etc. Tienen su lugar, pero son lentas y pueden ser inestables, lo que significa que a veces fallan por razones desconocidas.
4. **Usa nombres descriptivos**: Los nombres de las pruebas deben describir claramente el comportamiento que se está probando. Esto mejora la legibilidad y el mantenimiento.
5. **Evita los globales como la peste**: Minimiza el uso de `$app->set()` y `$app->get()`, ya que actúan como estado global, requiriendo simulaciones en cada prueba. Prefiere DI o un contenedor de DI (ver [Dependency Injection Container](/learn/dependency-injection-container)). Incluso usar el método `$app->map()` es técnicamente un "global" y debe evitarse a favor de DI. Usa una biblioteca de sesiones como [flightphp/session](https://github.com/flightphp/session) para que puedas simular el objeto de sesión en tus pruebas. **No** llames a [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php) directamente en tu código, ya que eso inyecta una variable global, lo que hace que sea difícil de probar.
6. **Usa inyección de dependencias**: Inyecta dependencias (por ejemplo, [`PDO`](https://www.php.net/manual/en/class.pdo.php), mailers) en los controllers para aislar la lógica y simplificar las simulaciones. Si tienes una clase con demasiadas dependencias, considera refactorizarla en clases más pequeñas que sigan un solo principio de responsabilidad siguiendo los [principios SOLID](https://en.wikipedia.org/wiki/SOLID).
7. **Simula servicios de terceros**: Simula bases de datos, clientes HTTP (cURL) o servicios de correo para evitar llamadas externas. Prueba una o dos capas de profundidad, pero deja que tu lógica principal se ejecute. Por ejemplo, si tu aplicación envía un mensaje de texto, **NO** quieres enviar realmente un mensaje de texto cada vez que ejecutes tus pruebas, ya que eso generará cargos (y será más lento). En su lugar, simula el servicio de mensajes de texto y solo verifica que tu código llame al servicio con los parámetros correctos.
8. **Apunta a una cobertura alta, no a la perfección**: Una cobertura del 100% de líneas es buena, pero no significa que todo en tu código se esté probando como debería (investiga [cobertura de ramas/rutas en PHPUnit](https://localheinz.com/articles/2023/03/22/collecting-line-branch-and-path-coverage-with-phpunit/)). Prioriza comportamientos críticos (por ejemplo, registro de usuarios, respuestas de API y captura de respuestas fallidas).
9. **Usa controllers para rutas**: En tus definiciones de rutas, usa controllers en lugar de closures. La instancia `flight\Engine $app` se inyecta en cada controller a través del constructor por defecto. En pruebas, usa `$app = new Flight\Engine()` para instanciar Flight dentro de una prueba, inyectarla en tu controller y llamar a métodos directamente (por ejemplo, `$controller->register()`). Ver [Extending Flight](/learn/extending) y [Routing](/learn/routing).
10. **Elige un estilo de simulación y mantente en él**: PHPUnit soporta varios estilos de simulación (por ejemplo, prophecy, simulaciones integradas) o puedes usar clases anónimas, que tienen sus propios beneficios como autocompletado de código, rompiendo si cambias la definición del método, etc. Solo sé consistente en tus pruebas. Ver [PHPUnit Mock Objects](https://docs.phpunit.de/en/12.3/test-doubles.html#test-doubles).
11. **Usa visibilidad `protected` para métodos/propiedades que quieras probar en subclases**: Esto te permite sobrescribirlos en subclases de prueba sin hacerlos públicos, lo cual es especialmente útil para simulaciones con clases anónimas.

## Configurando PHPUnit

Primero, configura [PHPUnit](https://phpunit.de/) en tu proyecto Flight PHP usando Composer para facilitar las pruebas. Ver la [guía de inicio de PHPUnit](https://phpunit.readthedocs.io/en/12.3/installation.html) para más detalles.

1. En el directorio de tu proyecto, ejecuta:
   ```bash
   composer require --dev phpunit/phpunit
   ```
   Esto instala la última versión de PHPUnit como una dependencia de desarrollo.

2. Crea un directorio `tests` en la raíz de tu proyecto para los archivos de prueba.

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

Ahora, cuando tus pruebas estén listas, puedes ejecutar `composer test` para ejecutar las pruebas.

## Probando un simple route handler

Comencemos con una ruta básica [route](/learn/routing) que valida la entrada de correo electrónico de un usuario. Probaremos su comportamiento: devolver un mensaje de éxito para correos válidos y un error para los inválidos. Para la validación de correo, usamos [`filter_var`](https://www.php.net/manual/en/function.filter-var.php).

```php
// index.php  // archivo index.php
$app->route('POST /register', [ UserController::class, 'register' ]);

// UserController.php  // archivo UserController.php
class UserController {
	protected $app;  // instancia de app

	public function __construct(flight\Engine $app) {
		$this->app = $app;
	}

	public function register() {
		$email = $this->app->request()->data->email;
		$responseArray = [];
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$responseArray = ['status' => 'error', 'message' => 'Correo inválido'];  // mensaje de error
		} else {
			$responseArray = ['status' => 'success', 'message' => 'Correo válido'];  // mensaje de éxito
		}

		$this->app->json($responseArray);
	}
}
```

Para probar esto, crea un archivo de prueba. Ver [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) para más sobre la estructura de pruebas:

```php
// tests/UserControllerTest.php  // archivo de pruebas para UserController
use PHPUnit\Framework\TestCase;
use Flight;
use flight\Engine;

class UserControllerTest extends TestCase {

    public function testValidEmailReturnsSuccess() {  // prueba para correo válido que devuelve éxito
		$app = new Engine();
		$request = $app->request();
		$request->data->email = 'test@example.com';  // simular datos POST
		$UserController = new UserController($app);
		$UserController->register($request->data->email);
        $response = $app->response()->getBody();
		$output = json_decode($response, true);
        $this->assertEquals('success', $output['status']);
        $this->assertEquals('Valid email', $output['message']);  // verificar mensaje
    }

    public function testInvalidEmailReturnsError() {  // prueba para correo inválido que devuelve error
		$app = new Engine();
		$request = $app->request();
		$request->data->email = 'invalid-email';  // simular datos POST
		$UserController = new UserController($app);
		$UserController->register($request->data->email);
		$response = $app->response()->getBody();
		$output = json_decode($response, true);
		$this->assertEquals('error', $output['status']);
		$this->assertEquals('Invalid email', $output['message']);  // verificar mensaje
	}
}
```

**Puntos clave**:
- Simulamos datos POST usando la clase de solicitud. No uses globales como `$_POST`, `$_GET`, etc., ya que complica las pruebas (tienes que restablecer siempre esos valores o otras pruebas podrían fallar).
- Todos los controllers por defecto tendrán la instancia `flight\Engine` inyectada en ellos incluso sin configurar un contenedor DIC. Esto facilita probar controllers directamente.
- No hay uso de `Flight::` en absoluto, lo que hace que el código sea más fácil de probar.
- Las pruebas verifican el comportamiento: estado y mensaje correctos para correos válidos/inválidos.

Ejecuta `composer test` para verificar que la ruta se comporte como se espera. Para más sobre [requests](/learn/requests) y [responses](/learn/responses) en Flight, ver los documentos relevantes.

## Usando inyección de dependencias para controllers probables

Para escenarios más complejos, usa [dependency injection](/learn/dependency-injection-container) (DI) para hacer que los controllers sean probables. Evita los globales de Flight (por ejemplo, `Flight::set()`, `Flight::map()`, `Flight::register()`) ya que actúan como estado global, requiriendo simulaciones para cada prueba. En su lugar, usa el contenedor de DI de Flight, [DICE](https://github.com/Level-2/Dice), [PHP-DI](https://php-di.org/) o DI manual.

Usemos [`flight\database\PdoWrapper`](/awesome-plugins/pdo-wrapper) en lugar de PDO crudo. Este wrapper es mucho más fácil de simular y probar unitariamente.

Aquí hay un controller que guarda un usuario en una base de datos y envía un correo de bienvenida:

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
			// agregando el return aquí ayuda en las pruebas unitarias para detener la ejecución
			return $this->app->jsonHalt(['status' => 'error', 'message' => 'Correo inválido']);
		}

		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);

		return $this->app->json(['status' => 'success', 'message' => 'Usuario registrado']);
    }
}
```

**Puntos clave**:
- El controller depende de una instancia de [`PdoWrapper`](/awesome-plugins/pdo-wrapper) y una `MailerInterface` (un servicio de correo de terceros simulado).
- Las dependencias se inyectan a través del constructor, evitando globales.

### Probando el controller con simulaciones

Ahora, probemos el comportamiento de `UserController`: validar correos, guardar en la base de datos y enviar correos. Simularemos la base de datos y el mailer para aislar el controller.

```php
// tests/UserControllerDICTest.php  // archivo de pruebas para UserController con DI
use PHPUnit\Framework\TestCase;

class UserControllerDICTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {  // prueba para correo válido que guarda y envía correo

		// A veces mezclar estilos de simulación es necesario
		// Aquí usamos la simulación integrada de PHPUnit para PDOStatement
		$statementMock = $this->createMock(PDOStatement::class);
		$statementMock->method('execute')->willReturn(true);
		// Usando una clase anónima para simular PdoWrapper
        $mockDb = new class($statementMock) extends PdoWrapper {
			protected $statementMock;  // simulación de declaración
			public function __construct($statementMock) {
				$this->statementMock = $statementMock;
			}

			// Cuando lo simulamos de esta manera, no se realiza una llamada real a la base de datos.
			// Podemos configurar esto para simular fallos, etc.
            public function runQuery(string $sql, array $params = []): PDOStatement {
                return $this->statementMock;
            }
        };
        $mockMailer = new class implements MailerInterface {
            public $sentEmail = null;  // correo enviado
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

    public function testInvalidEmailSkipsSaveAndEmail() {  // prueba para correo inválido que omite guardar y enviar
		 $mockDb = new class() extends PdoWrapper {
			// Un constructor vacío omite el constructor padre
			public function __construct() {}
            public function runQuery(string $sql, array $params = []): PDOStatement {
                throw new Exception('No debería ser llamado');  // excepción si se llama
            }
        };
        $mockMailer = new class implements MailerInterface {
            public $sentEmail = null;
            public function sendWelcome($email): bool {
                throw new Exception('No debería ser llamado');  // excepción si se llama
            }
        };
		$app = new Engine();
		$app->request()->data->email = 'invalid-email';

		// Necesario mapear jsonHalt para evitar salir
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

**Puntos clave**:
- Simulamos `PdoWrapper` y `MailerInterface` para evitar llamadas reales a la base de datos o correos.
- Las pruebas verifican el comportamiento: correos válidos activan inserciones en la base de datos y envíos de correos; correos inválidos omiten ambos.
- Simula dependencias de terceros (por ejemplo, `PdoWrapper`, `MailerInterface`), permitiendo que la lógica del controller se ejecute.

### Simulando demasiado

Ten cuidado de no simular demasiado de tu código. Te doy un ejemplo a continuación sobre por qué esto podría ser malo usando nuestro `UserController`. Cambiaremos esa verificación a un método llamado `isEmailValid` (usando `filter_var`) y las otras adiciones nuevas a un método separado llamado `registerUser`.

```php
use flight\database\PdoWrapper;
use flight\Engine;

// UserControllerDICV2.php  // versión actualizada de UserController
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
			// agregando el return aquí ayuda en las pruebas unitarias para detener la ejecución
			return $this->app->jsonHalt(['status' => 'error', 'message' => 'Correo inválido']);
		}

		$this->registerUser($email);

		$this->app->json(['status' => 'success', 'message' => 'Usuario registrado']);
    }

	protected function isEmailValid($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;  // verifica si el correo es válido
	}

	protected function registerUser($email) {
		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);
	}
}
```

Y ahora la prueba unitaria sobre-similada que no prueba nada realmente:

```php
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {  // prueba para correo válido que guarda y envía correo
		$app = new Engine();
		$app->request()->data->email = 'test@example.com';
		// omitimos la inyección de dependencias extra porque es "fácil"
        $controller = new class($app) extends UserControllerDICV2 {
			protected $app;
			// Omite las dependencias en el constructor
			public function __construct($app) {
				$this->app = $app;
			}

			// Forzamos esto a ser válido.
			protected function isEmailValid($email) {
				return true;  // Siempre devuelve true, omitiendo la validación real
			}

			// Omite las llamadas reales a DB y mailer
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

¡Hurra, tenemos pruebas unitarias y pasan! Pero espera, ¿qué pasa si cambio el funcionamiento interno de `isEmailValid` o `registerUser`? Mis pruebas aún pasarán porque he simulado toda la funcionalidad. Te muestro lo que quiero decir.

```php
// UserControllerDICV2.php  // versión actualizada
class UserControllerDICV2 {

	// ... otros métodos ...

	protected function isEmailValid($email) {
		// Lógica cambiada
		$validEmail = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
		// Ahora solo debe tener un dominio específico
		$validDomain = strpos($email, '@example.com') !== false; 
		return $validEmail && $validDomain;  // verifica dominio
	}
}
```

Si ejecuto mis pruebas anteriores, aún pasan! Pero porque no estaba probando el comportamiento (dejando que parte del código se ejecute), podría haber codificado un error esperando en producción. La prueba debería modificarse para tener en cuenta el nuevo comportamiento, y también lo opuesto cuando el comportamiento no es el esperado.

## Ejemplo completo

Puedes encontrar un ejemplo completo de un proyecto Flight PHP con pruebas unitarias en GitHub: [n0nag0n/flight-unit-tests-guide](https://github.com/n0nag0n/flight-unit-tests-guide).
Para más guías, ver [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) y [Troubleshooting](/learn/troubleshooting).

## Fosos comunes

- **Sobre-simulación**: No simules todas las dependencias; deja que alguna lógica (por ejemplo, validación en el controller) se ejecute para probar el comportamiento real. Ver [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles).
- **Estado global**: Usar variables globales de PHP (por ejemplo, [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php), [`$_COOKIE`](https://www.php.net/manual/en/reserved.variables.cookie.php)) intensivamente hace que las pruebas sean frágiles. Lo mismo con `Flight::`. Refactoriza para pasar dependencias explícitamente.
- **Configuración compleja**: Si la configuración de la prueba es engorrosa, tu clase podría tener demasiadas dependencias o responsabilidades que violan los [principios SOLID](https://en.wikipedia.org/wiki/SOLID).

## Escalando con pruebas unitarias

Las pruebas unitarias brillan en proyectos más grandes o al revisar código después de meses. Documentan el comportamiento y detectan regresiones, ahorrándote re-aprender tu aplicación. Para desarrolladores individuales, prueba rutas críticas (por ejemplo, registro de usuarios, procesamiento de pagos). Para equipos, las pruebas aseguran un comportamiento consistente en contribuciones. Ver [Why Frameworks?](/learn/why-frameworks) para más sobre los beneficios de usar frameworks y pruebas.

¡Contribuye con tus propios consejos de pruebas al repositorio de documentación de Flight PHP!

_Escrito por [n0nag0n](https://github.com/n0nag0n) 2025_