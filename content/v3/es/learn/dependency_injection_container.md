# Contenedor de Inyección de Dependencias

## Introducción

El Contenedor de Inyección de Dependencias (DIC) es una herramienta potente que te permite gestionar
las dependencias de tu aplicación. Es un concepto clave en los marcos de PHP modernos y se
utiliza para gestionar la instanciación y configuración de objetos. Algunos ejemplos de bibliotecas DIC
son: [Dice](https://r.je/dice), [Pimple](https://pimple.symfony.com/), 
[PHP-DI](http://php-di.org/) y [league/container](https://container.thephpleague.com/).

Un DIC es una forma elegante de decir que te permite crear y gestionar tus clases en una
ubicación centralizada. Esto es útil cuando necesitas pasar el mismo objeto a
varias clases (como tus controladores). Un ejemplo sencillo podría ayudar a entender esto mejor.

## Ejemplo Básico

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

$User = new UserController(new PDO('mysql:host=localhost;dbname=test', 'user', 'pass'));
Flight::route('/user/@id', [ $UserController, 'view' ]);

Flight::start();
```

Se puede ver en el código anterior que estamos creando un nuevo objeto `PDO` y pasándolo
a nuestra clase `UserController`. Esto está bien para una aplicación pequeña, pero a medida
que tu aplicación crece, descubrirás que estás creando el mismo objeto `PDO` en múltiples
lugares. Aquí es donde resulta útil un DIC.

Aquí tienes el mismo ejemplo utilizando un DIC (usando Dice):
```php

require 'vendor/autoload.php';

// misma clase que arriba. Sin cambios
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
// ¡no olvides volver a asignarlo a sí mismo como se muestra abajo!
$container = $container->addRule('PDO', [
	// shared significa que el mismo objeto se devolverá cada vez
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// Esto registra el controlador de contenedor para que Flight sepa usarlo.
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// ahora podemos usar el contenedor para crear nuestro UserController
Flight::route('/user/@id', [ 'UserController', 'view' ]);
// o alternativamente puedes definir la ruta así
Flight::route('/user/@id', 'UserController->view');
// o
Flight::route('/user/@id', 'UserController::view');

Flight::start();
```

Apuesto a que puedes estar pensando que se añadió mucho código extra al ejemplo.
La magia reside en cuando tienes otro controlador que necesita el objeto `PDO`. 

```php

// Si todos tus controladores tienen un constructor que necesita un objeto PDO
// ¡¡¡Cada una de las rutas a continuación lo recibirán automáticamente inyectado!!!
Flight::route('/empresa/@id', 'CompanyController->view');
Flight::route('/organización/@id', 'OrganizationController->view');
Flight::route('/categoría/@id', 'CategoryController->view');
Flight::route('/ajustes', 'SettingsController->view');
```

El beneficio adicional de utilizar un DIC es que las pruebas unitarias se vuelven mucho más fáciles. Puedes
crear un objeto simulado y pasarlo a tu clase. ¡Este es un gran beneficio al escribir pruebas para tu aplicación!

## PSR-11

Flight también puede utilizar cualquier contenedor compatible con PSR-11. Esto significa que puedes usar cualquier
contenedor que implemente la interfaz PSR-11. Aquí tienes un ejemplo utilizando el contenedor PSR-11 de League:

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

Aunque pueda ser un poco más detallado que el ejemplo anterior con Dice, aún
se logra el mismo resultado con los mismos beneficios.

## Controlador DIC Personalizado

También puedes crear tu propio controlador DIC. Esto es útil si tienes un contenedor personalizado
que quieres utilizar y que no es compatible con PSR-11 (Dice). Consulta el
[ejemplo básico](#basic-example) para ver cómo hacerlo.

Además, existen algunas configuraciones útiles que facilitarán tu vida al usar Flight.

### Instancia del Motor

Si estás utilizando la instancia del `Engine` en tus controladores/middleware, así es
como lo configurarías:

```php

// En algún lugar de tu archivo de inicio
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

// Ahora puedes usar la instancia del Motor en tus controladores/middleware

class MyController {
	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function index() {
		$this->app->render('index');
	}
}
```

### Añadiendo Otras Clases

Si tienes otras clases que quieres agregar al contenedor, con Dice es fácil ya que serán resueltas automáticamente por el contenedor. Aquí tienes un ejemplo:

```php

$container = new \Dice\Dice;
// Si no necesitas inyectar nada en tu clase
// ¡no necesitas definir nada!
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

class MyCustomClass {
	public function parseThing() {
		return 'cosa';
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

Flight::route('/usuario', 'UserController->index');
```