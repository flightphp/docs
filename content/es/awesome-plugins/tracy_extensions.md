Tracy Flight Panel Extensions
=====

Este es un conjunto de extensiones para hacer que trabajar con Flight sea un poco más completo.

- Flight - Analiza todas las variables de Flight.
- Database - Analiza todas las consultas que se han ejecutado en la página (si inicializas correctamente la conexión a la base de datos)
- Request - Analiza todas las variables `$_SERVER` y examina todos los datos globales (`$_GET`, `$_POST`, `$_FILES`)
- Session - Analiza todas las variables `$_SESSION` si las sesiones están activas.

Este es el Panel

![Barra de Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

¡Y cada panel muestra información muy útil sobre tu aplicación!

![Datos de Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Base de Datos de Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Solicitud de Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Instalación
-------
Ejecuta `composer require flightphp/tracy-extensions --dev` ¡y estarás en marcha!

Configuración
-------
Hay muy poca configuración que necesitas hacer para comenzar con esto. Deberás inicializar el depurador Tracy antes de usar esto [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// código de arranque
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Es posible que necesites especificar tu entorno con Debugger::enable(Debugger::DEVELOPMENT)

// si usas conexiones de base de datos en tu aplicación, hay un
// envoltorio PDO requerido para usar SOLO EN DESARROLLO (¡no en producción por favor!)
// Tiene los mismos parámetros que una conexión PDO regular
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// o si adjuntas esto al framework Flight
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// ahora cada vez que hagas una consulta se capturará el tiempo, la consulta y los parámetros

// Esto conecta los puntos
if(Debugger::$showBar === true) {
	new TracyExtensionLoader(Flight::app());
}

// más código

Flight::start();
```