# Extensiones de Panel de Tracy

Este es un conjunto de extensiones para hacer que trabajar con Flight sea un poco más completo.

- Flight - Analiza todas las variables de Flight.
- Base de Datos - Analiza todas las consultas que se han ejecutado en la página (si inicias correctamente la conexión a la base de datos)
- Petición - Analiza todas las variables `$_SERVER` y examina todos los datos globales (`$_GET`, `$_POST`, `$_FILES`)
- Sesión - Analiza todas las variables `$_SESSION` si las sesiones están activas.

Este es el Panel

![Barra de Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

¡Y cada panel muestra información muy útil sobre tu aplicación!

![Datos de Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Base de Datos de Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Petición de Flight](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

Instalación
-------
Ejecuta `composer require flightphp/tracy-extensions --dev` ¡y estás listo!

Configuración
-------
No necesitas hacer mucha configuración para empezar. Debes iniciar el depurador Tracy antes de usar esto [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide):

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// código de arranque
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// Es posible que necesites especificar tu entorno con Debugger::enable(Debugger::DEVELOPMENT)

// si utilizas conexiones de base de datos en tu aplicación, hay
// un envoltorio PDO requerido para usar SOLO EN DESARROLLO (¡por favor, no en producción!)
// Tiene los mismos parámetros que una conexión PDO regular
$pdo = new PdoQueryCapture('sqlite:test.db', 'usuario', 'contraseña');
// o si adjuntas esto al framework Flight
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'usuario', 'contraseña']);
// ahora cada vez que hagas una consulta, capturará el tiempo, la consulta y los parámetros

// Esto conecta los puntos
if(Debugger::$showBar === true) {
	new TracyExtensionLoader(Flight::app());
}

// más código

Flight::start();
```