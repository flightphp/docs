# Migrating to v3

La compatibilidad con versiones anteriores se ha mantenido en su mayor parte, pero hay algunos cambios de los que debes ser consciente al migrar de v2 a v3.

## Comportamiento del almacenamiento en búfer de salida (3.5.0)

[El almacenamiento en búfer de salida](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) es el proceso por el cual la salida generada por un script de PHP se almacena en un búfer (interno de PHP) antes de ser enviada al cliente. Esto te permite modificar la salida antes de enviarla al cliente.

En una aplicación MVC, el Controlador es el "gestor" y se encarga de lo que hace la vista. Tener una salida generada fuera del controlador (o en algunos casos de forma anónima en Flight) rompe el patrón MVC. Este cambio es para que se ajuste más al patrón MVC y hace que el marco sea más predecible y fácil de usar.

En v2, el almacenamiento en búfer de salida se manejaba de una manera en la que no cerraba consistentemente su propio búfer de salida, lo que hacía que las pruebas unitarias y la transmisión fueran más difíciles. Para la mayoría de los usuarios, este cambio puede que en realidad no les afecte. Sin embargo, si estás haciendo eco de contenido fuera de las llamadas de retorno y controladores (por ejemplo, en un gancho), es probable que tengas problemas. Hacer eco de contenido en ganchos y antes de que el marco realmente se ejecute puede haber funcionado en el pasado, pero no funcionará en el futuro.

### Donde podrías tener problemas
```php
// index.php
require 'vendor/autoload.php';

// solo un ejemplo
define('START_TIME', microtime(true));

function hello() {
	echo 'Hola Mundo';
}

Flight::map('hello', 'hello');
Flight::after('hello', function(){
	// esto en realidad estará bien
	echo '<p>Esta frase Hola Mundo fue traída a usted por la letra "H"</p>';
});

Flight::before('start', function(){
	// cosas como esta causarán un error
	echo '<html><head><title>Mi Página</title></head><body>';
});

Flight::route('/', function(){
	// esto está bien
	echo 'Hola Mundo';

	// Esto también debería estar bien
	Flight::hello();
});

Flight::after('start', function(){
	// esto causará un error
	echo '<div>Su página se cargó en '.(microtime(true) - START_TIME).' segundos</div></body></html>';
});
```

### Activando el Comportamiento de Renderizado v2

¿Todavía puedes mantener tu código antiguo tal como está sin necesidad de reescribirlo para que funcione con v3? ¡Sí, puedes! Puedes activar el comportamiento de renderizado v2 estableciendo la opción de configuración `flight.v2.output_buffering` en `true`. Esto te permitirá seguir usando el antiguo comportamiento de renderizado, pero se recomienda corregirlo de aquí en adelante. En la versión 4 del marco, esto será eliminado.

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// Ahora esto estará bien
	echo '<html><head><title>Mi Página</title></head><body>';
});

// más código
```

## Cambios en el Despachador (3.7.0)

Si has estado llamando directamente a métodos estáticos para `Dispatcher` como `Dispatcher::invokeMethod()`, `Dispatcher::execute()`, etc. necesitarás actualizar tu código para no llamar directamente a estos métodos. `Dispatcher` se ha convertido en una manera más orientada a objetos para que los Contenedores de Inyección de Dependencias puedan ser utilizados de una forma más sencilla. Si necesitas invocar un método de manera similar a como lo hacía Dispatcher, puedes usar manualmente algo como `$result = $class->$method(...$params);` o `call_user_func_array()` en su lugar.

## Cambios en `halt()` `stop()` `redirect()` y `error()` (3.10.0)

El comportamiento predeterminado antes de la versión 3.10.0 era limpiar tanto los encabezados como el cuerpo de la respuesta. Esto fue cambiado para limpiar solo el cuerpo de la respuesta. Si necesitas limpiar también los encabezados, puedes usar `Flight::response()->clear()`.