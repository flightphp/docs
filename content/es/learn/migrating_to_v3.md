# Migrar a v3

La compatibilidad con versiones anteriores se ha mantenido en su mayor parte, pero hay algunos cambios de los que debe ser consciente al migrar de v2 a v3.

## Comportamiento de almacenamiento en búfer de salida (3.5.0)

[El almacenamiento en búfer de salida](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) es el proceso mediante el cual la salida generada por un script de PHP se almacena en un búfer (interno de PHP) antes de ser enviado al cliente. Esto le permite modificar la salida antes de que se envíe al cliente.

En una aplicación MVC, el Controlador es el "gerente" y gestiona lo que hace la vista. Generar la salida fuera del controlador (o en el caso de Flight, a veces en una función anónima) rompe el patrón MVC. Este cambio busca estar más en línea con el patrón MVC y hacer que el marco sea más predecible y fácil de usar.

En v2, el almacenamiento en búfer de salida se manejaba de tal manera que no cerraba consistentemente su propio búfer de salida, lo que dificultaba las [pruebas unitarias](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) y el [streaming](https://github.com/flightphp/core/issues/413). Para la mayoría de los usuarios, este cambio puede que realmente no les afecte. Sin embargo, si está haciendo un eco de contenido fuera de los callables y controladores (por ejemplo, en un hook), es probable que tenga problemas. Hacer un eco de contenido en ganchos y antes de que el marco realmente se ejecute puede haber funcionado en el pasado, pero no funcionará en adelante.

### Dónde podrías tener problemas
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
	echo '<p>Esta frase de Hola Mundo fue traída a usted por la letra "H"</p>';
});

Flight::before('inicio', function(){
	// cosas como esta causarán un error
	echo '<html><head><title>Mi Página</title></head><body>';
});

Flight::route('/', function(){
	// esto está bien
	echo 'Hola Mundo';

	// Esto también debería estar bien
	Flight::hola();
});

Flight::after('inicio', function(){
	// esto causará un error
	echo '<div>Su página se cargó en '.(microtime(true) - TIEMPO_INICIO).' segundos</div></body></html>';
});
```

### Activar el Comportamiento de Renderización de v2

¿Puedes seguir manteniendo tu código antiguo tal como está sin tener que reescribirlo para que funcione con v3? ¡Sí, puedes! Puedes activar el comportamiento de renderización de v2 configurando la opción de configuración `flight.v2.output_buffering` en `true`. Esto te permitirá seguir utilizando el antiguo comportamiento de renderización, pero se recomienda corregirlo en el futuro. En la versión 4 del marco, esto se eliminará.

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

Si ha estado llamando directamente a métodos estáticos para `Dispatcher` como `Dispatcher::invokeMethod()`, `Dispatcher::execute()`, etc., deberá actualizar su código para no llamar directamente a estos métodos. `Dispatcher` se ha convertido en algo más orientado a objetos para permitir que los Contenedores de Inyección de Dependencias se utilicen de una manera más fácil. Si necesita invocar un método de manera similar a como lo hacía Dispatcher, puede usar manualmente algo como `$resultado = $clase->$método(...$params);` o `call_user_func_array()` en su lugar.