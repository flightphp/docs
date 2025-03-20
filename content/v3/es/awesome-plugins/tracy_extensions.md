Tracy Flight Panel Extensions
=====

Este es un conjunto de extensiones para hacer que trabajar con Flight sea un poco más enriquecedor.

- Flight - Analiza todas las variables de Flight.
- Database - Analiza todas las consultas que se han ejecutado en la página (si inicias correctamente la conexión a la base de datos)
- Request - Analiza todas las variables `$_SERVER` y examina todas las cargas útiles globales (`$_GET`, `$_POST`, `$_FILES`)
- Session - Analiza todas las variables `$_SESSION` si las sesiones están activas.

Este es el Panel

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

¡Y cada panel muestra información muy útil sobre su aplicación!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Haz clic [aquí](https://github.com/flightphp/tracy-extensions) para ver el código.

Instalación
-------
Ejecuta `composer require flightphp/tracy-extensions --dev` y ¡estarás en camino!

Configuración
-------
Hay muy poca configuración que necesitas hacer para empezar. Necesitarás iniciar el depurador Tracy antes de usar esto [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// código de arranque
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Puede que necesites especificar tu entorno con Debugger::enable(Debugger::DEVELOPMENT)

// si usas conexiones a la base de datos en tu aplicación, hay un 
// envoltorio PDO requerido para usar SOLO EN DESARROLLO (¡no en producción, por favor!)
// Tiene los mismos parámetros que una conexión PDO regular
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// o si conectas esto al framework Flight
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// ahora, cada vez que hagas una consulta, capturará el tiempo, la consulta y los parámetros

// Esto conecta los puntos
if(Debugger::$showBar === true) {
	// Esto necesita ser falso o Tracy no puede renderizar realmente :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// más código

Flight::start();
```

## Configuración Adicional

### Datos de Sesión
Si tienes un controlador de sesión personalizado (como ghostff/session), puedes pasar cualquier array de datos de sesión a Tracy y lo mostrará automáticamente. Lo pasas con la clave `session_data` en el segundo parámetro del constructor de `TracyExtensionLoader`.

```php

use Ghostff\Session\Session;
// o usa flight\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// Esto necesita ser falso o Tracy no puede renderizar realmente :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// rutas y otras cosas...

Flight::start();
```

### Latte

Si tienes Latte instalado en tu proyecto, puedes usar el panel de Latte para analizar tus plantillas. Puedes pasar la instancia de Latte al constructor de `TracyExtensionLoader` con la clave `latte` en el segundo parámetro.

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// aquí es donde agregas el Panel de Latte a Tracy
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// Esto necesita ser falso o Tracy no puede renderizar realmente :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
```