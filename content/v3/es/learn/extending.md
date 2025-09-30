# Extendiendo

## Resumen

Flight está diseñado para ser un framework extensible. El framework viene con un
conjunto de métodos y componentes predeterminados, pero te permite mapear tus propios métodos,
registrar tus propias clases o incluso sobrescribir clases y métodos existentes.

## Entendiendo

Hay 2 formas en que puedes extender la funcionalidad de Flight:

1. Mapeo de Métodos - Esto se usa para crear métodos personalizados simples que puedes llamar
   desde cualquier lugar de tu aplicación. Estos se usan típicamente para funciones de utilidad
   que quieres poder llamar desde cualquier parte de tu código. 
2. Registro de Clases - Esto se usa para registrar tus propias clases con Flight. Esto se
   usa típicamente para clases que tienen dependencias o requieren configuración.

También puedes sobrescribir métodos existentes del framework para alterar su comportamiento predeterminado y adaptarlo mejor
a las necesidades de tu proyecto. 

> Si estás buscando un DIC (Contenedor de Inyección de Dependencias), ve a la
página de [Contenedor de Inyección de Dependencias](/learn/dependency-injection-container).

## Uso Básico

### Sobrescribiendo Métodos del Framework

Flight te permite sobrescribir su funcionalidad predeterminada para adaptarla a tus propias necesidades,
sin tener que modificar ningún código. Puedes ver todos los métodos que puedes sobrescribir [a continuación](#mappable-framework-methods).

Por ejemplo, cuando Flight no puede coincidir una URL con una ruta, invoca el método `notFound`
que envía una respuesta genérica `HTTP 404`. Puedes sobrescribir este comportamiento
usando el método `map`:

```php
Flight::map('notFound', function() {
  // Mostrar página personalizada 404
  include 'errors/404.html';
});
```

Flight también te permite reemplazar componentes principales del framework.
Por ejemplo, puedes reemplazar la clase Router predeterminada con tu propia clase personalizada:

```php
// crear tu clase Router personalizada
class MyRouter extends \flight\net\Router {
	// sobrescribir métodos aquí
	// por ejemplo, un atajo para solicitudes GET para eliminar
	// la característica de pasar ruta
	public function get($pattern, $callback, $alias = '') {
		return parent::get($pattern, $callback, false, $alias);
	}
}

// Registrar tu clase personalizada
Flight::register('router', MyRouter::class);

// Cuando Flight carga la instancia de Router, cargará tu clase
$myRouter = Flight::router();
$myRouter->get('/hello', function() {
  echo "Hello World!";
}, 'hello_alias');
```

Sin embargo, los métodos del framework como `map` y `register` no pueden ser sobrescritos. Obtendrás
un error si intentas hacerlo (nuevamente, ve [a continuación](#mappable-framework-methods) para una lista de métodos).

### Métodos del Framework Mapeables

A continuación se muestra el conjunto completo de métodos para el framework. Consiste en métodos principales, 
que son métodos estáticos regulares, y métodos extensibles, que son métodos mapeados que pueden 
ser filtrados o sobrescritos.

#### Métodos Principales

Estos métodos son principales para el framework y no pueden ser sobrescritos.

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Crea un método personalizado del framework.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Registra una clase a un método del framework.
Flight::unregister(string $name) // Desregistra una clase de un método del framework.
Flight::before(string $name, callable $callback) // Agrega un filtro antes de un método del framework.
Flight::after(string $name, callable $callback) // Agrega un filtro después de un método del framework.
Flight::path(string $path) // Agrega una ruta para la carga automática de clases.
Flight::get(string $key) // Obtiene una variable establecida por Flight::set().
Flight::set(string $key, mixed $value) // Establece una variable dentro del motor de Flight.
Flight::has(string $key) // Verifica si una variable está establecida.
Flight::clear(array|string $key = []) // Limpia una variable.
Flight::init() // Inicializa el framework a sus configuraciones predeterminadas.
Flight::app() // Obtiene la instancia del objeto de aplicación
Flight::request() // Obtiene la instancia del objeto de solicitud
Flight::response() // Obtiene la instancia del objeto de respuesta
Flight::router() // Obtiene la instancia del objeto de enrutador
Flight::view() // Obtiene la instancia del objeto de vista
```

#### Métodos Extensibles

```php
Flight::start() // Inicia el framework.
Flight::stop() // Detiene el framework y envía una respuesta.
Flight::halt(int $code = 200, string $message = '') // Detiene el framework con un código de estado y mensaje opcionales.
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapea un patrón de URL a un callback.
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapea un patrón de URL de solicitud POST a un callback.
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapea un patrón de URL de solicitud PUT a un callback.
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapea un patrón de URL de solicitud PATCH a un callback.
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapea un patrón de URL de solicitud DELETE a un callback.
Flight::group(string $pattern, callable $callback) // Crea agrupación para URLs, el patrón debe ser una cadena.
Flight::getUrl(string $name, array $params = []) // Genera una URL basada en un alias de ruta.
Flight::redirect(string $url, int $code) // Redirige a otra URL.
Flight::download(string $filePath) // Descarga un archivo.
Flight::render(string $file, array $data, ?string $key = null) // Renderiza un archivo de plantilla.
Flight::error(Throwable $error) // Envía una respuesta HTTP 500.
Flight::notFound() // Envía una respuesta HTTP 404.
Flight::etag(string $id, string $type = 'string') // Realiza caché HTTP ETag.
Flight::lastModified(int $time) // Realiza caché HTTP de última modificación.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envía una respuesta JSON.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envía una respuesta JSONP.
Flight::jsonHalt(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envía una respuesta JSON y detiene el framework.
Flight::onEvent(string $event, callable $callback) // Registra un oyente de eventos.
Flight::triggerEvent(string $event, ...$args) // Activa un evento.
```

Cualquier método personalizado agregado con `map` y `register` también puede ser filtrado. Para ejemplos sobre cómo filtrar estos métodos, ve la guía de [Filtrado de Métodos](/learn/filtering).

#### Clases del Framework Extensibles

Hay varias clases en las que puedes sobrescribir funcionalidad extendiéndolas y
registrando tu propia clase. Estas clases son:

```php
Flight::app() // Clase de aplicación - extiende la clase flight\Engine
Flight::request() // Clase de solicitud - extiende la clase flight\net\Request
Flight::response() // Clase de respuesta - extiende la clase flight\net\Response
Flight::router() // Clase de enrutador - extiende la clase flight\net\Router
Flight::view() // Clase de vista - extiende la clase flight\template\View
Flight::eventDispatcher() // Clase de despachador de eventos - extiende la clase flight\core\Dispatcher
```

### Mapeo de Métodos Personalizados

Para mapear tu propio método personalizado simple, usa la función `map`:

```php
// Mapear tu método
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// Llamar a tu método personalizado
Flight::hello('Bob');
```

Aunque es posible crear métodos personalizados simples, se recomienda solo crear
funciones estándar en PHP. Esto tiene autocompletado en IDE y es más fácil de leer.
El equivalente del código anterior sería:

```php
function hello(string $name) {
  echo "hello $name!";
}

hello('Bob');
```

Esto se usa más cuando necesitas pasar variables a tu método para obtener un valor
esperado. Usar el método `register()` como a continuación es más para pasar configuración
y luego llamar a tu clase preconfigurada.

### Registro de Clases Personalizadas

Para registrar tu propia clase y configurarla, usa la función `register`. La ventaja que esto tiene sobre map() es que puedes reutilizar la misma clase cuando llamas a esta función (sería útil con `Flight::db()` para compartir la misma instancia).

```php
// Registrar tu clase
Flight::register('user', User::class);

// Obtener una instancia de tu clase
$user = Flight::user();
```

El método register también te permite pasar parámetros al constructor de tu clase.
Entonces, cuando cargues tu clase personalizada, vendrá preinicializada.
Puedes definir los parámetros del constructor pasando un array adicional.
Aquí hay un ejemplo de carga de una conexión a la base de datos:

```php
// Registrar clase con parámetros del constructor
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// Obtener una instancia de tu clase
// Esto creará un objeto con los parámetros definidos
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// y si lo necesitas más adelante en tu código, solo llamas al mismo método nuevamente
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

Si pasas un parámetro de callback adicional, se ejecutará inmediatamente
después de la construcción de la clase. Esto te permite realizar cualquier procedimiento de configuración para tu
nuevo objeto. La función de callback toma un parámetro, una instancia del nuevo objeto.

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

> **Nota:** Ten en cuenta que los métodos mapeados tienen precedencia sobre las clases registradas. Si declaras ambos usando el mismo nombre, solo se invocará el método mapeado.

### Ejemplos

Aquí hay algunos ejemplos de cómo puedes extender Flight con funcionalidad que no está incorporada en el núcleo.

#### Registro de Logs

Flight no tiene un sistema de registro de logs incorporado, sin embargo, es realmente fácil
usar una biblioteca de registro con Flight. Aquí hay un ejemplo usando la
biblioteca Monolog:

```php
// services.php

// Registrar el logger con Flight
Flight::register('log', Monolog\Logger::class, [ 'name' ], function(Monolog\Logger $log) {
    $log->pushHandler(new Monolog\Handler\StreamHandler('path/to/your.log', Monolog\Logger::WARNING));
});
```

Ahora que está registrado, puedes usarlo en tu aplicación:

```php
// En tu controlador o ruta
Flight::log()->warning('This is a warning message');
```

Esto registrará un mensaje en el archivo de log que especificaste. ¿Qué pasa si quieres registrar algo cuando ocurre
un error? Puedes usar el método `error`:

```php
// En tu controlador o ruta
Flight::map('error', function(Throwable $ex) {
	Flight::log()->error($ex->getMessage());
	// Mostrar tu página de error personalizada
	include 'errors/500.html';
});
```

También podrías crear un sistema básico de APM (Monitoreo de Rendimiento de Aplicación)
usando los métodos `before` y `after`:

```php
// En tu archivo services.php

Flight::before('start', function() {
	Flight::set('start_time', microtime(true));
});

Flight::after('start', function() {
	$end = microtime(true);
	$start = Flight::get('start_time');
	Flight::log()->info('Request '.Flight::request()->url.' took ' . round($end - $start, 4) . ' seconds');

	// También podrías agregar tus encabezados de solicitud o respuesta
	// para registrarlos también (ten cuidado ya que esto sería mucho
	// datos si tienes muchas solicitudes)
	Flight::log()->info('Request Headers: ' . json_encode(Flight::request()->headers));
	Flight::log()->info('Response Headers: ' . json_encode(Flight::response()->headers));
});
```

#### Caché

Flight no tiene un sistema de caché incorporado, sin embargo, es realmente fácil
usar una biblioteca de caché con Flight. Aquí hay un ejemplo usando la
biblioteca [PHP File Cache](/awesome-plugins/php_file_cache):

```php
// services.php

// Registrar el caché con Flight
Flight::register('cache', \flight\Cache::class, [ __DIR__ . '/../cache/' ], function(\flight\Cache $cache) {
    $cache->setDevMode(ENVIRONMENT === 'development');
});
```

Ahora que está registrado, puedes usarlo en tu aplicación:

```php
// En tu controlador o ruta
$data = Flight::cache()->get('my_cache_key');
if (empty($data)) {
	// Realizar algún procesamiento para obtener los datos
	$data = [ 'some' => 'data' ];
	Flight::cache()->set('my_cache_key', $data, 3600); // caché por 1 hora
}
```

#### Instanciación Fácil de Objetos DIC

Si estás usando un DIC (Contenedor de Inyección de Dependencias) en tu aplicación,
puedes usar Flight para ayudarte a instanciar tus objetos. Aquí hay un ejemplo usando
la biblioteca [Dice](https://github.com/level-2/Dice):

```php
// services.php

// crear un nuevo contenedor
$container = new \Dice\Dice;
// no olvides reasignarlo a sí mismo como a continuación!
$container = $container->addRule('PDO', [
	// shared significa que el mismo objeto se retornará cada vez
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// ahora podemos crear un método mapeable para crear cualquier objeto. 
Flight::map('make', function($class, $params = []) use ($container) {
	return $container->create($class, $params);
});

// Esto registra el manejador del contenedor para que Flight sepa usarlo para controladores/middleware
Flight::registerContainerHandler(function($class, $params) {
	Flight::make($class, $params);
});


// supongamos que tenemos la siguiente clase de ejemplo que toma un objeto PDO en el constructor
class EmailCron {
	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function send() {
		// código que envía un email
	}
}

// Y finalmente puedes crear objetos usando inyección de dependencias
$emailCron = Flight::make(EmailCron::class);
$emailCron->send();
```

¿Genial, verdad?

## Ver También
- [Contenedor de Inyección de Dependencias](/learn/dependency-injection-container) - Cómo usar un DIC con Flight.
- [Caché de Archivos](/awesome-plugins/php_file_cache) - Ejemplo de uso de una biblioteca de caché con Flight.

## Solución de Problemas
- Recuerda que los métodos mapeados tienen precedencia sobre las clases registradas. Si declaras ambos usando el mismo nombre, solo se invocará el método mapeado.

## Registro de Cambios
- v2.0 - Lanzamiento Inicial.