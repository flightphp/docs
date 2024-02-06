```es
Tracy Flight Panel Extensions
=====

Este es un conjunto de extensiones para hacer que trabajar con Flight sea un poco más completo.

- Flight - Analizar todas las variables de Flight.
- Database - Analizar todas las consultas que se han ejecutado en la página (si inicias correctamente la conexión a la base de datos)
- Request - Analizar todas las variables `$_SERVER` y examinar todas las cargas globales (`$_GET`, `$_POST`, `$_FILES`)
- Session - Analizar todas las variables `$_SESSION` si las sesiones están activas.

Este es el Panel

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

¡Y cada panel muestra información muy útil sobre tu aplicación!

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Instalación
-------
¡Ejecuta `composer require flightphp/tracy-extensions --dev` y estás listo para empezar!

Configuración
-------
Hay muy poca configuración que necesitas hacer para comenzar. Deberás iniciar el depurador Tracy antes de usar esto [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// código de inicialización
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Puede que necesites especificar tu entorno con Debugger::enable(Debugger::DEVELOPMENT)

// si usas conexiones a base de datos en tu aplicación, hay
// un envoltorio PDO necesario para usar SOLAMENTE EN DESARROLLO (¡no en producción por favor!)
// Tiene los mismos parámetros que una conexión PDO regular
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// o si adjuntas esto al framework Flight
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// ahora cada vez que hagas una consulta, capturará el tiempo, la consulta y los parámetros

// Esto conecta los puntos
if(Debugger::$showBar === true) {
	new TracyExtensionLoader(Flight::app());
}

// más código

Flight::start();
```