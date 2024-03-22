# Extensiones del Panel Tracy para Flight

Este es un conjunto de extensiones para hacer que trabajar con Flight sea un poco más completo.

- Flight - Analiza todas las variables de Flight.
- Base de Datos - Analiza todas las consultas que se han ejecutado en la página (si se inicia correctamente la conexión a la base de datos).
- Petición - Analiza todas las variables `$_SERVER` y examina todos los datos globales (`$_GET`, `$_POST`, `$_FILES`).
- Sesión - Analiza todas las variables `$_SESSION` si las sesiones están activas.

Este es el Panel

![Barra de Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

¡Y cada panel muestra información muy útil sobre tu aplicación!

![Datos de Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Base de Datos de Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Petición de Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Instalación
-------
Ejecuta `composer require flightphp/tracy-extensions --dev` ¡y estás listo para comenzar!

Configuración
-------
Hay muy poca configuración que necesitas hacer para comenzar. Deberás iniciar el depurador Tracy antes de usar esto [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// código de inicio
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Puede que necesites especificar tu entorno con Debugger::enable(Debugger::DEVELOPMENT)

// si usas conexiones a bases de datos en tu aplicación, hay
// un envoltorio PDO requerido para USO ÚNICAMENTE EN DESARROLLO (¡no en producción por favor!)
// Tiene los mismos parámetros que una conexión PDO regular
$pdo = new PdoQueryCapture('sqlite:test.db', 'usuario', 'contraseña');
// o si adjuntas esto al framework Flight
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'usuario', 'contraseña']);
// ahora cada vez que hagas una consulta capturará el tiempo, la consulta y los parámetros

// Esto conecta los puntos
if(Debugger::$showBar === true) {
	// ¡Esto necesita ser false o Tracy no puede renderizar correctamente!
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// más código

Flight::start();
```

## Configuración Adicional

### Datos de Sesión
Si tienes un controlador de sesión personalizado (como ghostff/session), puedes pasar cualquier arreglo de datos de sesión a Tracy y automáticamente lo mostrará. Puedes pasarlos con la clave `session_data` en el segundo parámetro del constructor `TracyExtensionLoader`.

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// ¡Esto necesita ser false o Tracy no puede renderizar correctamente!
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// rutas y otras cosas...

Flight::start();
```

### Latte

Si tienes Latte instalado en tu proyecto, puedes usar el panel de Latte para analizar tus plantillas. Puedes pasar la instancia de Latte al constructor `TracyExtensionLoader` con la clave `latte` en el segundo parámetro.

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
	// ¡Esto necesita ser false o Tracy no puede renderizar correctamente!
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
