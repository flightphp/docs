Tracy Flight Panel Extensions
=====

Este es un conjunto de extensiones para hacer que trabajar con Flight sea un poco más completo.

- Flight - Analiza todas las variables de Flight.
- Database - Analiza todas las consultas que se han ejecutado en la página (si inicias correctamente la conexión a la base de datos)
- Request - Analiza todas las variables `$_SERVER` y examina todos los payloads globales (`$_GET`, `$_POST`, `$_FILES`)
- Session - Analiza todas las variables `$_SESSION` si las sesiones están activas.

Este es el Panel

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

¡Y cada panel muestra información muy útil sobre tu aplicación!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Haz clic [aquí](https://github.com/flightphp/tracy-extensions) para ver el código.

Installation
-------
Ejecuta `composer require flightphp/tracy-extensions --dev` y estarás listo para empezar!

Configuration
-------
Hay muy poca configuración que necesites hacer para comenzar. Deberás iniciar el depurador de Tracy antes de usar esto [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// bootstrap code
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Es posible que necesites especificar tu entorno con Debugger::enable(Debugger::DEVELOPMENT)

// si usas conexiones a la base de datos en tu app, hay un 
// wrapper PDO requerido para usar SOLO EN DESARROLLO (¡no en producción por favor!)
// Tiene los mismos parámetros que una conexión PDO regular
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// o si lo adjuntas al framework Flight
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// ahora cada vez que hagas una consulta capturará el tiempo, la consulta y los parámetros

// Esto conecta los puntos
if(Debugger::$showBar === true) {
	// Esto necesita ser false o Tracy no podrá renderizar :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// more code

Flight::start();
```

## Additional Configuration

### Session Data
Si tienes un manejador de sesiones personalizado (como ghostff/session), puedes pasar cualquier array de datos de sesión a Tracy y lo mostrará automáticamente por ti. Lo pasas con la clave `session_data` en el segundo parámetro del constructor de `TracyExtensionLoader`.

```php

use Ghostff\Session\Session;
// o use flight\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// Esto necesita ser false o Tracy no podrá renderizar :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// routes and other things...

Flight::start();
```

### Latte

_Se requiere PHP 8.1+ para esta sección._

Si tienes Latte instalado en tu proyecto, Tracy tiene una integración nativa con Latte para analizar tus plantillas. Simplemente registra la extensión con tu instancia de Latte.

```php

require 'vendor/autoload.php';

$app = Flight::app();

$app->map('render', function($template, $data, $block = null) {
	$latte = new Latte\Engine;

	// other configurations...

	// solo agrega la extensión si la Barra de Depuración de Tracy está habilitada
	if(Debugger::$showBar === true) {
		// aquí es donde agregas el Panel de Latte a Tracy
		$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
	}

	$latte->render($template, $data, $block);
});
```