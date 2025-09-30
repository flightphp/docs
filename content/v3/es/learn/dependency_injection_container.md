# Contenedor de Inyección de Dependencias

## Resumen

El Contenedor de Inyección de Dependencias (DIC) es una potente mejora que te permite gestionar
las dependencias de tu aplicación.

## Comprensión

La Inyección de Dependencias (DI) es un concepto clave en los frameworks PHP modernos y se
utiliza para gestionar la instanciación y configuración de objetos. Algunos ejemplos de bibliotecas DIC
son: [flightphp/container](https://github.com/flightphp/container), [Dice](https://r.je/dice), [Pimple](https://pimple.symfony.com/), 
[PHP-DI](http://php-di.org/), y [league/container](https://container.thephpleague.com/).

Un DIC es una forma elegante de permitirte crear y gestionar tus clases en un
lugar centralizado. Esto es útil cuando necesitas pasar el mismo objeto a
múltiples clases (como tus controladores o middleware, por ejemplo).

## Uso Básico

La forma antigua de hacer las cosas podría verse así:
```php

require 'vendor/autoload.php';

// clase para gestionar usuarios desde la base de datos
class UserController {

	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function view(int $id) {
		$stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
		$stmt->execute(['id' => $id]);

		print_r($stmt->fetch());
	}
}

// en tu archivo routes.php

$db = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');

$UserController = new UserController($db);
Flight::route('/user/@id', [ $UserController, 'view' ]);
// otras rutas de UserController...

Flight::start();
```

Puedes ver en el código anterior que estamos creando un nuevo objeto `PDO` y pasándolo
a nuestra clase `UserController`. Esto está bien para una aplicación pequeña, pero a medida que
tu aplicación crece, encontrarás que estás creando o pasando el mismo objeto `PDO` 
en múltiples lugares. Aquí es donde un DIC resulta útil.

Aquí está el mismo ejemplo usando un DIC (usando Dice):
```php

require 'vendor/autoload.php';

// misma clase que arriba. Nada cambió
class UserController {

	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function view(int $id) {
		$stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
		$stmt->execute(['id' => $id]);

		print_r($stmt->fetch());
	}
}

// crear un nuevo contenedor
$container = new \Dice\Dice;

// agregar una regla para decirle al contenedor cómo crear un objeto PDO
// ¡no olvides reasignarlo a sí mismo como a continuación!
$container = $container->addRule('PDO', [
	// shared significa que el mismo objeto se retornará cada vez
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// Esto registra el manejador del contenedor para que Flight sepa usarlo.
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// ahora podemos usar el contenedor para crear nuestro UserController
Flight::route('/user/@id', [ UserController::class, 'view' ]);

Flight::start();
```

Apuesto a que podrías estar pensando que se agregó mucho código extra al ejemplo.
La magia ocurre cuando tienes otro controlador que necesita el objeto `PDO`.

```php

// Si todos tus controladores tienen un constructor que necesita un objeto PDO
// ¡cada una de las rutas a continuación lo tendrá inyectado automáticamente!!!
Flight::route('/company/@id', [ CompanyController::class, 'view' ]);
Flight::route('/organization/@id', [ OrganizationController::class, 'view' ]);
Flight::route('/category/@id', [ CategoryController::class, 'view' ]);
Flight::route('/settings', [ SettingsController::class, 'view' ]);
```

El beneficio adicional de utilizar un DIC es que las pruebas unitarias se vuelven mucho más fáciles. Puedes
crear un objeto simulado y pasarlo a tu clase. ¡Esto es un gran beneficio cuando estás
escribiendo pruebas para tu aplicación!

### Creando un manejador DIC centralizado

Puedes crear un manejador DIC centralizado en tu archivo de servicios extendiendo tu app. Aquí hay un ejemplo:

```php
// services.php

// crear un nuevo contenedor
$container = new \Dice\Dice;
// ¡no olvides reasignarlo a sí mismo como a continuación!
$container = $container->addRule('PDO', [
	// shared significa que el mismo objeto se retornará cada vez
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// ahora podemos crear un método mapeable para crear cualquier objeto. 
Flight::map('make', function($class, $params = []) use ($container) {
	return $container->create($class, $params);
});

// Esto registra el manejador del contenedor para que Flight sepa usarlo para controladores/middleware
Flight::registerContainerHandler(function($class, $params) {
	Flight::make($class, $params);
});


// supongamos que tenemos la siguiente clase de muestra que toma un objeto PDO en el constructor
class EmailCron {
	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function send() {
		// código que envía un email
	}
}

// Y finalmente puedes crear objetos usando inyección de dependencias
$emailCron = Flight::make(EmailCron::class);
$emailCron->send();
```

### `flightphp/container`

Flight tiene un plugin que proporciona un contenedor simple compatible con PSR-11 que puedes usar para manejar
tu inyección de dependencias. Aquí hay un ejemplo rápido de cómo usarlo:

```php

// index.php por ejemplo
require 'vendor/autoload.php';

use flight\Container;

$container = new Container;

$container->set(PDO::class, fn(): PDO => new PDO('sqlite::memory:'));

Flight::registerContainerHandler([$container, 'get']);

class TestController {
  private PDO $pdo;

  function __construct(PDO $pdo) {
    $this->pdo = $pdo;
  }

  function index() {
    var_dump($this->pdo);
	// ¡imprimirá esto correctamente!
  }
}

Flight::route('GET /', [TestController::class, 'index']);

Flight::start();
```

#### Uso Avanzado de flightphp/container

También puedes resolver dependencias de manera recursiva. Aquí hay un ejemplo:

```php
<?php

require 'vendor/autoload.php';

use flight\Container;

class User {}

interface UserRepository {
  function find(int $id): ?User;
}

class PdoUserRepository implements UserRepository {
  private PDO $pdo;

  function __construct(PDO $pdo) {
    $this->pdo = $pdo;
  }

  function find(int $id): ?User {
    // Implementación ...
    return null;
  }
}

$container = new Container;

$container->set(PDO::class, static fn(): PDO => new PDO('sqlite::memory:'));
$container->set(UserRepository::class, PdoUserRepository::class);

$userRepository = $container->get(UserRepository::class);
var_dump($userRepository);

/*
object(PdoUserRepository)#4 (1) {
  ["pdo":"PdoUserRepository":private]=>
  object(PDO)#3 (0) {
  }
}
 */
```

### DICE

También puedes crear tu propio manejador DIC. Esto es útil si tienes un contenedor personalizado
que quieres usar que no es PSR-11 (Dice). Consulta la
sección de [uso básico](#basic-usage) para saber cómo hacerlo.

Además, hay
algunos valores predeterminados útiles que facilitarán tu vida al usar Flight.

#### Instancia de Engine

Si estás usando la instancia `Engine` en tus controladores/middleware, aquí está
cómo la configurarías:

```php

// En algún lugar de tu archivo de bootstrap
$engine = Flight::app();

$container = new \Dice\Dice;
$container = $container->addRule('*', [
	'substitutions' => [
		// Aquí es donde pasas la instancia
		Engine::class => $engine
	]
]);

$engine->registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// Ahora puedes usar la instancia de Engine en tus controladores/middleware

class MyController {
	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function index() {
		$this->app->render('index');
	}
}
```

#### Agregando Otras Clases

Si tienes otras clases que quieres agregar al contenedor, con Dice es fácil ya que serán resueltas automáticamente por el contenedor. Aquí hay un ejemplo:

```php

$container = new \Dice\Dice;
// Si no necesitas inyectar dependencias en tus clases
// ¡no necesitas definir nada!
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

class MyCustomClass {
	public function parseThing() {
		return 'thing';
	}
}

class UserController {

	protected MyCustomClass $MyCustomClass;

	public function __construct(MyCustomClass $MyCustomClass) {
		$this->MyCustomClass = $MyCustomClass;
	}

	public function index() {
		echo $this->MyCustomClass->parseThing();
	}
}

Flight::route('/user', 'UserController->index');
```

### PSR-11

Flight también puede usar cualquier contenedor compatible con PSR-11. Esto significa que puedes usar cualquier
contenedor que implemente la interfaz PSR-11. Aquí hay un ejemplo usando el contenedor PSR-11 de League:

```php

require 'vendor/autoload.php';

// misma clase UserController que arriba

$container = new \League\Container\Container();
$container->add(UserController::class)->addArgument(PdoWrapper::class);
$container->add(PdoWrapper::class)
	->addArgument('mysql:host=localhost;dbname=test')
	->addArgument('user')
	->addArgument('pass');
Flight::registerContainerHandler($container);

Flight::route('/user', [ 'UserController', 'view' ]);

Flight::start();
```

Esto puede ser un poco más verboso que el ejemplo anterior de Dice, ¡aún así
cumple el trabajo con los mismos beneficios!

## Ver También
- [Extending Flight](/learn/extending) - Aprende cómo puedes agregar inyección de dependencias a tus propias clases extendiendo el framework.
- [Configuration](/learn/configuration) - Aprende cómo configurar Flight para tu aplicación.
- [Routing](/learn/routing) - Aprende cómo definir rutas para tu aplicación y cómo funciona la inyección de dependencias con controladores.
- [Middleware](/learn/middleware) - Aprende cómo crear middleware para tu aplicación y cómo funciona la inyección de dependencias con middleware.

## Solución de Problemas
- Si tienes problemas con tu contenedor, asegúrate de que estás pasando los nombres de clase correctos al contenedor.

## Registro de Cambios
- v3.7.0 - Agregada la capacidad de registrar un manejador DIC en Flight.