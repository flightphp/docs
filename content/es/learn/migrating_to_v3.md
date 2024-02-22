# Migrating to v3

La compatibilidad con versiones anteriores se ha mantenido en su mayor parte, pero hay algunos cambios de los que deberías ser consciente al migrar de v2 a v3.

## Almacenamiento en búfer de salida

[El almacenamiento en búfer de salida](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) es el proceso donde la salida generada por un script de PHP se almacena en un búfer (interno de PHP) antes de ser enviada al cliente. Esto te permite modificar la salida antes de enviarla al cliente.

En una aplicación MVC, el Controlador es el "gestor" y gestiona lo que hace la vista. Generar la salida fuera del controlador (o en el caso de Flights, a veces en una función anónima) rompe el patrón MVC. Este cambio busca estar más en línea con el patrón MVC y hacer que el marco sea más predecible y fácil de usar.

En v2, el almacenamiento en búfer de salida se manejaba de una manera en la que no cerraba consistentemente su propio búfer de salida y lo que hacía que las [pruebas unitarias](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) y [transmisiones continuas](https://github.com/flightphp/core/issues/413) fueran más difíciles. Para la mayoría de los usuarios, este cambio podría no afectarte realmente. Sin embargo, si estás imprimiendo contenido fuera de los llamables y controladores (por ejemplo, en un gancho), probablemente tendrás problemas. Imprimir contenido en ganchos y antes de que el marco realmente se ejecute pudo haber funcionado en el pasado, pero no funcionará en el futuro.

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
	echo '<p>Esta frase de Hola Mundo te es presentada por la letra "H"</p>';
});

Flight::before('start', function(){
	// cosas como esta causarán un error
	echo '<html><head><title>Mi Página</title></head><body>';
});

Flight::route('/', function(){
	// esto en realidad está bien
	echo 'Hola Mundo';

	// Esto también debería estar bien
	Flight::hola();
});

Flight::after('start', function(){
	// esto causará un error
	echo '<div>Tu página se cargó en '.(microtime(true) - TIEMPO_INICIO).' segundos</div></body></html>';
});
```

### Habilitar el Comportamiento de Renderizado v2

¿Puedes mantener tu código antiguo tal como está sin hacer una reescritura para que funcione con v3? ¡Sí, puedes! Puedes habilitar el comportamiento de renderizado v2 configurando la opción de configuración `flight.v2.output_buffering` en `true`. Esto te permitirá seguir utilizando el antiguo comportamiento de renderizado, pero se recomienda corregirlo en el futuro. En la versión 4 del marco, esto se eliminará.

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