# Migrando a v3

La compatibilidad con versiones anteriores se ha mantenido en su mayor parte, pero hay algunos cambios de los que deberías ser consciente al migrar de v2 a v3.

## Búfer de Salida

[El búfer de salida](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) es el proceso en el que la salida generada por un script de PHP se almacena en un búfer (interno de PHP) antes de ser enviada al cliente. Esto te permite modificar la salida antes de enviarla al cliente.

En una aplicación MVC, el Controlador es el "gestor" y maneja lo que hace la vista. Generar la salida fuera del controlador (o en el caso de Flight, a veces en una función anónima) rompe el patrón MVC. Este cambio es para estar más en línea con el patrón MVC y hacer que el framework sea más predecible y fácil de usar.

En v2, el búfer de salida se manejaba de una manera donde no cerraba consistentemente su propio búfer de salida y esto hacía que las pruebas unitarias y la transmisión fueran más difíciles. Para la mayoría de los usuarios, este cambio puede que no les afecte realmente. Sin embargo, si estás mostrando contenido fuera de los métodos de llamada y controladores (por ejemplo en un gancho), es probable que tengas problemas. Mostrar contenido en ganchos, y antes de que el framework se ejecute realmente, pudo haber funcionado en el pasado, pero no funcionará en el futuro.

### Dónde podrías tener problemas
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
	echo '<p>Esta frase de Hola Mundo fue traída a usted por la letra "H"</p>';
});

Flight::before('start', function(){
	// cosas como esta causarán un error
	echo '<html><head><title>Mi Página</title></head><body>';
});

Flight::route('/', function(){
	// esto en realidad está bien
	echo 'Hola Mundo';

	// Esto también debería estar bien
	Flight::hello();
});

Flight::after('start', function(){
	// esto causará un error
	echo '<div>Tu página se cargó en '.(microtime(true) - START_TIME).' segundos</div></body></html>';
});
```

### Activando el Comportamiento de Renderizado v2

¿Aún puedes mantener tu código antiguo tal como está sin tener que reescribirlo para que funcione con v3? ¡Sí puedes! Puedes activar el comportamiento de renderizado v2 configurando la opción de configuración `flight.v2.output_buffering` en `true`. Esto te permitirá seguir utilizando el antiguo comportamiento de renderizado, pero se recomienda corregirlo para avanzar. En la versión 4 del framework, esto será eliminado.

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