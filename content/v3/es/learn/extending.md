# Extendiendo

Flight está diseñado para ser un marco extensible. El marco viene con un conjunto 
de métodos y componentes predeterminados, pero te permite mapear tus propios métodos, 
registrar tus propias clases o incluso sobrescribir clases y métodos existentes.

Si estás buscando un DIC (Contenedor de Inyección de Dependencias), dirígete a la 
[página del Contenedor de Inyección de Dependencias](dependency-injection-container).

## Mapeo de Métodos

Para mapear tu propio método personalizado simple, usas la función `map`:

```php
// Mapea tu método
Flight::map('hello', function (string $name) {
  echo "¡hola $name!";
});

// Llama a tu método personalizado
Flight::hello('Bob');
```

Si bien es posible crear métodos personalizados simples, se recomienda crear 
funciones estándar en PHP. Esto tiene autocompletado en IDEs y es más fácil de leer. 
El equivalente del código anterior sería:

```php
function hello(string $name) {
  echo "¡hola $name!";
}

hello('Bob');
```

Esto se utiliza más cuando necesitas pasar variables a tu método para obtener un 
valor esperado. Usar el método `register()` como se muestra a continuación es más 
para pasar configuraciones y luego llamar a tu clase preconfigurada.

## Registro de Clases

Para registrar tu propia clase y configurarla, utilizas la función `register`:

```php
// Registra tu clase
Flight::register('user', User::class);

// Obtén una instancia de tu clase
$user = Flight::user();
```

El método de registro también te permite pasar parámetros al constructor de tu clase. 
Así que cuando cargas tu clase personalizada, vendrá preinicializada. Puedes definir 
los parámetros del constructor pasando un array adicional. Aquí tienes un ejemplo de 
carga de una conexión a base de datos:

```php
// Registra la clase con parámetros de constructor
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Obtén una instancia de tu clase
// Esto creará un objeto con los parámetros definidos
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// y si lo necesitas más tarde en tu código, solo llama al mismo método nuevamente
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Si pasas un parámetro de callback adicional, se ejecutará inmediatamente 
después de la construcción de la clase. Esto te permite realizar cualquier procedimiento 
de configuración para tu nuevo objeto. La función de callback toma un parámetro, 
una instancia del nuevo objeto.

```php
// El callback recibirá el objeto que fue construido
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

Por defecto, cada vez que cargues tu clase obtendrás una instancia compartida. 
Para obtener una nueva instancia de una clase, simplemente pasa `false` como parámetro:

```php
// Instancia compartida de la clase
$shared = Flight::db();

// Nueva instancia de la clase
$new = Flight::db(false);
```

Ten en cuenta que los métodos mapeados tienen prioridad sobre las clases registradas. Si 
declara ambos usando el mismo nombre, solo se invocará el método mapeado.

## Registro

Flight no tiene un sistema de registro integrado, sin embargo, es muy fácil 
utilizar una biblioteca de registro con Flight. Aquí hay un ejemplo usando la biblioteca 
Monolog:

```php
// index.php o bootstrap.php

// Registra el logger con Flight
Flight::register('log', Monolog\Logger::class, [ 'name' ], function(Monolog\Logger $log) {
    $log->pushHandler(new Monolog\Handler\StreamHandler('path/to/your.log', Monolog\Logger::WARNING));
});
```

Ahora que está registrado, puedes usarlo en tu aplicación:

```php
// En tu controlador o ruta
Flight::log()->warning('Este es un mensaje de advertencia');
```

Esto registrará un mensaje en el archivo de registro que especificaste. ¿Qué pasa si 
quieres registrar algo cuando ocurre un error? Puedes usar el método `error`:

```php
// En tu controlador o ruta

Flight::map('error', function(Throwable $ex) {
	Flight::log()->error($ex->getMessage());
	// Muestra tu página de error personalizada
	include 'errors/500.html';
});
```

También podrías crear un sistema básico de APM (Monitoreo del Rendimiento de la Aplicación) 
usando los métodos `before` y `after`:

```php
// En tu archivo de bootstrap

Flight::before('start', function() {
	Flight::set('start_time', microtime(true));
});

Flight::after('start', function() {
	$end = microtime(true);
	$start = Flight::get('start_time');
	Flight::log()->info('La solicitud '.Flight::request()->url.' tomó ' . round($end - $start, 4) . ' segundos');

	// También podrías agregar tus encabezados de solicitud o respuesta
	// para registrarlos también (ten cuidado ya que esto sería un 
	// gran volumen de datos si tienes muchas solicitudes)
	Flight::log()->info('Encabezados de Solicitud: ' . json_encode(Flight::request()->headers));
	Flight::log()->info('Encabezados de Respuesta: ' . json_encode(Flight::response()->headers));
});
```

## Sobrescribiendo Métodos del Marco

Flight te permite sobrescribir su funcionalidad predeterminada para adaptarla a 
tus propias necesidades, sin necesidad de modificar ningún código. Puedes ver todos 
los métodos que puedes sobrescribir [aquí](/learn/api).

Por ejemplo, cuando Flight no puede hacer coincidir una URL con una ruta, invoca el 
método `notFound` que envía una respuesta genérica `HTTP 404`. Puedes sobrescribir 
este comportamiento utilizando el método `map`:

```php
Flight::map('notFound', function() {
  // Muestra la página personalizada 404
  include 'errors/404.html';
});
```

Flight también te permite reemplazar componentes centrales del marco. 
Por ejemplo, puedes reemplazar la clase Router predeterminada con tu propia clase 
personalizada:

```php
// Registra tu clase personalizada
Flight::register('router', MyRouter::class);

// Cuando Flight carga la instancia del Router, cargará tu clase
$myrouter = Flight::router();
```

Sin embargo, los métodos del marco como `map` y `register` no se pueden sobrescribir. 
Recibirás un error si intentas hacerlo.