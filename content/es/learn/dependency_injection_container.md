# Contenedor de Inyección de Dependencias

## Introducción

El Contenedor de Inyección de Dependencias (DIC) es una herramienta poderosa que te permite gestionar las dependencias de tu aplicación. Es un concepto clave en los frameworks modernos de PHP y se utiliza para gestionar la instanciación y configuración de objetos. Algunos ejemplos de bibliotecas DIC son: [Dice](https://r.je/dice), [Pimple](https://pimple.symfony.com/), [PHP-DI](http://php-di.org/), y [league/container](https://container.thephpleague.com/).

Un DIC es una forma elegante de decir que te permite crear y gestionar tus clases en un lugar centralizado. Esto es útil cuando necesitas pasar el mismo objeto a múltiples clases (como tus controladores). Un ejemplo sencillo podría ayudar a entender esto mejor.

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
Flight::route('/usuario/@id', [ $UserController, 'view' ]);

Flight::start();
```

Puedes ver en el código anterior que estamos creando un nuevo objeto `PDO` y pasándolo a nuestra clase `UserController`. Esto está bien para una aplicación pequeña, pero a medida que tu aplicación crece, te darás cuenta de que estás creando el mismo objeto `PDO` en varios lugares. Aquí es donde un DIC resulta útil.

Aquí tienes el mismo ejemplo utilizando un DIC (usando Dice):
```php

require 'vendor/autoload.php';

// misma clase que arriba. Nada ha cambiado
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
// ¡no olvides reasignarlo a sí mismo como se muestra a continuación!
$container = $container->addRule('PDO', [
	// shared significa que se devolverá el mismo objeto cada vez
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// Esto registra el manejador de contenedores para que Flight sepa usarlo.
Flight::registerContainerHandler(function($class, $params) use ($container) {
	return $container->create($class, $params);
});

// ahora podemos usar el contenedor para crear nuestro UserController
Flight::route('/usuario/@id', [ 'UserController', 'view' ]);
// o alternativamente puedes definir la ruta de esta forma
Flight::route('/usuario/@id', 'UserController->view');
// o
Flight::route('/usuario/@id', 'UserController::view');

Flight::start();
```

Apuesto a que podrías estar pensando que se agregó mucho código extra al ejemplo.
La magia sucede cuando tienes otro controlador que necesita el objeto `PDO`.

```php

// Si todos tus controladores tienen un constructor que necesita un objeto PDO
// ¡cada una de las rutas a continuación lo recibirá automáticamente inyectado!
Flight::route('/empresa/@id', 'CompanyController->view');
Flight::route('/organización/@id', 'OrganizationController->view');
Flight::route('/categoría/@id', 'CategoryController->view');
Flight::route('/configuraciones', 'SettingsController->view');
```

El bono adicional de utilizar un DIC es que las pruebas unitarias se vuelven mucho más fáciles. Puedes crear un objeto simulado y pasarlo a tu clase. ¡Esto es de gran ayuda cuando estás escribiendo pruebas para tu aplicación!

## PSR-11

Flight también puede utilizar cualquier contenedor compatible con PSR-11. Esto significa que puedes usar cualquier contenedor que implemente la interfaz PSR-11. Aquí tienes un ejemplo utilizando el contenedor PSR-11 de League:

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

Flight::route('/usuario', [ 'UserController', 'view' ]);

Flight::start();
```

Aunque este puede ser un poco más detallado que el ejemplo anterior con Dice, ¡sigue cumpliendo con la misma funcionalidad!

## Manejador DIC Personalizado

También puedes crear tu propio manejador DIC. Esto es útil si tienes un contenedor personalizado que deseas utilizar y que no es PSR-11 (Dice). Consulta el [ejemplo básico](#basic-example) para saber cómo hacerlo.

Además, hay algunas configuraciones predeterminadas útiles que facilitarán tu vida al usar Flight.

### Instancia del Motor

Si estás utilizando la instancia `Engine` en tus controladores/middleware, así es como la configurarías:

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

// Ahora puedes utilizar la instancia del Motor en tus controladores/middleware

class MyController {
	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function index() {
		$this->app->render('index');
	}
}
```

### Agregar Otras Clases

Si tienes otras clases que deseas agregar al contenedor, con Dice es fácil, ya que serán resueltas automáticamente por el contenedor. Aquí tienes un ejemplo:

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