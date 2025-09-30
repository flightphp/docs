# Migrando a v3

La compatibilidad hacia atrás se ha mantenido en su mayor parte, pero hay algunos cambios de los que debes estar al tanto al migrar de v2 a v3. Hay algunos cambios que conflictuaban demasiado con los patrones de diseño, por lo que se tuvieron que hacer algunos ajustes.

## Comportamiento de Buffering de Salida

_v3.5.0_

[Output buffering](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) es el proceso en el que la salida generada por un script PHP se almacena en un búfer (interno de PHP) antes de ser enviada al cliente. Esto te permite modificar la salida antes de que se envíe al cliente.

En una aplicación MVC, el Controlador es el "gestor" y gestiona lo que hace la vista. Tener salida generada fuera del controlador (o en el caso de Flight, a veces una función anónima) rompe el patrón MVC. Este cambio se realiza para alinearse más con el patrón MVC y hacer que el framework sea más predecible y fácil de usar.

En v2, el buffering de salida se manejaba de una manera en la que no cerraba consistentemente su propio búfer de salida, lo que hacía más difícil el [unit testing](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) y el [streaming](https://github.com/flightphp/core/issues/413). Para la mayoría de los usuarios, este cambio puede no afectarte realmente. Sin embargo, si estás haciendo eco de contenido fuera de callables y controladores (por ejemplo, en un hook), es probable que te encuentres con problemas. Hacer eco de contenido en hooks, y antes de que el framework se ejecute realmente, puede haber funcionado en el pasado, pero no funcionará hacia adelante.

### Dónde podrías tener problemas
```php
// index.php
require 'vendor/autoload.php';

// solo un ejemplo
define('START_TIME', microtime(true));

function hello() {
	echo 'Hello World';
}

Flight::map('hello', 'hello');
Flight::after('hello', function(){
	// esto en realidad estará bien
	echo '<p>This Hello World phrase was brought to you by the letter "H"</p>';
});

Flight::before('start', function(){
	// cosas como esta causarán un error
	echo '<html><head><title>My Page</title></head><body>';
});

Flight::route('/', function(){
	// esto en realidad está bien
	echo 'Hello World';

	// Esto también debería estar bien
	Flight::hello();
});

Flight::after('start', function(){
	// esto causará un error
	echo '<div>Your page loaded in '.(microtime(true) - START_TIME).' seconds</div></body></html>';
});
```

### Activando el Comportamiento de Renderizado de v2

¿Puedes mantener tu código antiguo tal como está sin hacer una reescritura para que funcione con v3? ¡Sí, puedes! Puedes activar el comportamiento de renderizado de v2 estableciendo la opción de configuración `flight.v2.output_buffering` en `true`. Esto te permitirá continuar usando el comportamiento de renderizado antiguo, pero se recomienda corregirlo hacia adelante. En v4 del framework, esto se eliminará.

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// Ahora esto estará bien
	echo '<html><head><title>My Page</title></head><body>';
});

// más código 
```

## Cambios en el Dispatcher

_v3.7.0_

Si has estado llamando directamente métodos estáticos para `Dispatcher` como `Dispatcher::invokeMethod()`, `Dispatcher::execute()`, etc., necesitarás actualizar tu código para no llamar directamente a estos métodos. `Dispatcher` se ha convertido en más orientado a objetos para que los Contenedores de Inyección de Dependencias puedan usarse de una manera más fácil. Si necesitas invocar un método similar a como lo hacía Dispatcher, puedes usar manualmente algo como `$result = $class->$method(...$params);` o `call_user_func_array()` en su lugar.

## Cambios en `halt()` `stop()` `redirect()` y `error()`

_v3.10.0_

El comportamiento predeterminado antes de 3.10.0 era limpiar tanto los encabezados como el cuerpo de la respuesta. Esto se cambió para limpiar solo el cuerpo de la respuesta. Si necesitas limpiar también los encabezados, puedes usar `Flight::response()->clear()`.