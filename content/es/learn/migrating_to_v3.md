# Migración a v3

La compatibilidad con versiones anteriores se ha mantenido en su mayor parte, pero hay algunos cambios de los que debes ser consciente al migrar de v2 a v3.

## Comportamiento del almacenamiento en búfer de salida (3.5.0)

[El almacenamiento en búfer de salida](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) es el proceso en el cual la salida generada por un script de PHP se almacena en un búfer (interno de PHP) antes de ser enviada al cliente. Esto te permite modificar la salida antes de ser enviada al cliente.

En una aplicación MVC, el Controlador es el "gestor" y se encarga de lo que hace la vista. Tener la salida generada fuera del controlador (o en el caso de Flight a veces en una función anónima) rompe el patrón MVC. Este cambio es para estar más en línea con el patrón MVC y hacer que el framework sea más predecible y fácil de usar.

En v2, el almacenamiento en búfer de salida se manejaba de una manera en la que no cerraba de manera consistente su propio búfer de salida y eso hacía que las [pruebas unitarias](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) y el [streaming](https://github.com/flightphp/core/issues/413) fuesen más difíciles. Para la mayoría de los usuarios, este cambio puede que en realidad no te afecte. Sin embargo, si estás imprimiendo contenido fuera de las funciones llamables y controladores (por ejemplo en un gancho), es probable que tengas problemas. Imprimir contenido en ganchos, y antes de que el framework se ejecute realmente pudo haber funcionado en el pasado, pero no funcionará en adelante.

### Donde podrías tener problemas
```php
// index.php
require 'vendor/autoload.php';

// solo un ejemplo
define('TIEMPO_INICIO', microtime(true));

function hola() {
	echo 'Hola Mundo';
}

Flight::map('hola', 'hola');
Flight::after('hola', function(){
	// esto en realidad estará bien
	echo '<p>Esta frase de Hola Mundo te la trae la letra "H"</p>';
});

Flight::before('inicio', function(){
	// cosas como estas causarán un error
	echo '<html><head><title>Mi Página</title></head><body>';
});

Flight::route('/', function(){
	// esto en realidad está bien
	echo 'Hola Mundo';

	// Esto también debería estar bien
	Flight::hola();
});

Flight::after('inicio', function(){
	// esto causará un error
	echo '<div>Tu página cargó en '.(microtime(true) - TIEMPO_INICIO).' segundos</div></body></html>';
});
```

### Activando el comportamiento de renderización de v2

¿Todavía puedes mantener tu código antiguo tal como está sin necesidad de reescribirlo para que funcione con v3? ¡Sí, puedes! Puedes activar el comportamiento de renderización de v2 configurando la opción de configuración `flight.v2.output_buffering` en `true`. Esto te permitirá seguir utilizando el antiguo comportamiento de renderización, pero se recomienda corregirlo de cara al futuro. En la v4 del framework, esto será eliminado.

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('inicio', function(){
	// Ahora esto estará bien
	echo '<html><head><title>Mi Página</title></head><body>';
});

// más código 
```

## Cambios en el Despachador (3.7.0)

Si has estado llamando directamente a métodos estáticos para `Dispatcher` como `Dispatcher::invokeMethod()`, `Dispatcher::execute()`, etc., necesitarás actualizar tu código para no llamar directamente a estos métodos. `Dispatcher` ha sido convertido para ser más orientado a objetos de manera que los Contenedores de Inyección de Dependencias puedan ser utilizados de una manera más sencilla. Si necesitas invocar un método de manera similar a como lo hacía Dispatcher, puedes usar manualmente algo como `$resultado = $clase->$metodo(...$params);` o `call_user_func_array()` en su lugar.