# Enrutamiento

## Resumen
El enrutamiento en Flight PHP mapea patrones de URL a funciones de devolución de llamada o métodos de clase, permitiendo un manejo rápido y simple de solicitudes. Está diseñado para un overhead mínimo, un uso amigable para principiantes y extensibilidad sin dependencias externas.

## Comprensión
El enrutamiento es el mecanismo central que conecta las solicitudes HTTP con la lógica de tu aplicación en Flight. Al definir rutas, especificas cómo diferentes URLs activan código específico, ya sea a través de funciones, métodos de clase o acciones de controladores. El sistema de enrutamiento de Flight es flexible, soporta patrones básicos, parámetros con nombre, expresiones regulares y características avanzadas como inyección de dependencias y enrutamiento con recursos. Este enfoque mantiene tu código organizado y fácil de mantener, mientras permanece rápido y simple para principiantes y extensible para usuarios avanzados.

> **Nota:** ¿Quieres entender más sobre enrutamiento? Consulta la página ["¿por qué un framework?"](/learn/why-frameworks) para una explicación más detallada.

## Uso Básico

### Definiendo una Ruta Simple
El enrutamiento básico en Flight se realiza coincidiendo un patrón de URL con una función de devolución de llamada o un array de una clase y un método.

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> Las rutas se coinciden en el orden en que se definen. La primera ruta que coincida con una solicitud será invocada.

### Usando Funciones como Devoluciones de Llamada
La devolución de llamada puede ser cualquier objeto que sea invocable. Así que puedes usar una función regular:

```php
function hello() {
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

### Usando Clases y Métodos como un Controlador
También puedes usar un método (estático o no) de una clase:

```php
class GreetingController {
    public function hello() {
        echo 'hello world!';
    }
}

Flight::route('/', [ 'GreetingController','hello' ]);
// o
Flight::route('/', [ GreetingController::class, 'hello' ]); // método preferido
// o
Flight::route('/', [ 'GreetingController::hello' ]);
// o 
Flight::route('/', [ 'GreetingController->hello' ]);
```

O creando un objeto primero y luego llamando al método:

```php
use flight\Engine;

// GreetingController.php
class GreetingController
{
	protected Engine $app
    public function __construct(Engine $app) {
		$this->app = $app;
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Hello, {$this->name}!";
    }
}

// index.php
$app = Flight::app();
$greeting = new GreetingController($app);

Flight::route('/', [ $greeting, 'hello' ]);
```

> **Nota:** Por defecto, cuando un controlador es llamado dentro del framework, la clase `flight\Engine` siempre se inyecta a menos que especifiques a través de un [contenedor de inyección de dependencias](/learn/dependency-injection-container)

### Enrutamiento Específico de Método

Por defecto, los patrones de ruta se coinciden contra todos los métodos de solicitud. Puedes responder a métodos específicos colocando un identificador antes de la URL.

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';
});

// No puedes usar Flight::get() para rutas ya que ese es un método 
//    para obtener variables, no para crear una ruta.
Flight::post('/', function() { /* code */ });
Flight::patch('/', function() { /* code */ });
Flight::put('/', function() { /* code */ });
Flight::delete('/', function() { /* code */ });
```

También puedes mapear múltiples métodos a una sola devolución de llamada usando un delimitador `|`:

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';
});
```

### Manejo Especial para Solicitudes HEAD y OPTIONS

Flight proporciona un manejo integrado para solicitudes HTTP `HEAD` y `OPTIONS`:

#### Solicitudes HEAD

- Las **solicitudes HEAD** se tratan igual que las solicitudes `GET`, pero Flight elimina automáticamente el cuerpo de la respuesta antes de enviarla al cliente.
- Esto significa que puedes definir una ruta para `GET`, y las solicitudes HEAD a la misma URL devolverán solo encabezados (sin contenido), como se espera según los estándares HTTP.

```php
Flight::route('GET /info', function() {
    echo 'This is some info!';
});
// Una solicitud HEAD a /info devolverá los mismos encabezados, pero sin cuerpo.
```

#### Solicitudes OPTIONS

Las solicitudes `OPTIONS` son manejadas automáticamente por Flight para cualquier ruta definida.
- Cuando se recibe una solicitud OPTIONS, Flight responde con un estado `204 No Content` y un encabezado `Allow` que lista todos los métodos HTTP soportados para esa ruta.
- No necesitas definir una ruta separada para OPTIONS.

```php
// Para una ruta definida como:
Flight::route('GET|POST /users', function() { /* ... */ });

// Una solicitud OPTIONS a /users responderá con:
//
// Status: 204 No Content
// Allow: GET, POST, HEAD, OPTIONS
```

### Usando el Objeto Router

Además, puedes obtener el objeto Router que tiene algunos métodos auxiliares para usar:

```php

$router = Flight::router();

// mapea todos los métodos igual que Flight::route()
$router->map('/', function() {
	echo 'hello world!';
});

// solicitud GET
$router->get('/users', function() {
	echo 'users';
});
$router->post('/users', 			function() { /* code */});
$router->put('/users/update/@id', 	function() { /* code */});
$router->delete('/users/@id', 		function() { /* code */});
$router->patch('/users/@id', 		function() { /* code */});
```

### Expresiones Regulares (Regex)
Puedes usar expresiones regulares en tus rutas:

```php
Flight::route('/user/[0-9]+', function () {
  // Esto coincidirá con /user/1234
});
```

Aunque este método está disponible, se recomienda usar parámetros con nombre, o parámetros con nombre con expresiones regulares, ya que son más legibles y fáciles de mantener.

### Parámetros con Nombre
Puedes especificar parámetros con nombre en tus rutas que se pasarán a tu función de devolución de llamada. **Esto es más para la legibilidad de la ruta que para cualquier otra cosa. Por favor, ve la sección a continuación sobre una advertencia importante.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

También puedes incluir expresiones regulares con tus parámetros con nombre usando el delimitador `:`:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Esto coincidirá con /bob/123
  // Pero no coincidirá con /bob/12345
});
```

> **Nota:** La coincidencia de grupos regex `()` con parámetros posicionales no está soportada. Ej: `:'\(`

#### Advertencia Importante

Aunque en el ejemplo anterior, parece que `@name` está directamente ligado a la variable `$name`, no lo está. El orden de los parámetros en la función de devolución de llamada es lo que determina qué se pasa a ella. Si cambiaras el orden de los parámetros en la función de devolución de llamada, las variables también se cambiarían. Aquí hay un ejemplo:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";
});
```

Y si fueras a la siguiente URL: `/bob/123`, la salida sería `hello, 123 (bob)!`. 
_¡Por favor ten cuidado_ cuando configures tus rutas y tus funciones de devolución de llamada!

### Parámetros Opcionales
Puedes especificar parámetros con nombre que sean opcionales para la coincidencia envolviendo segmentos en paréntesis.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // Esto coincidirá con las siguientes URLs:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Cualquier parámetro opcional que no coincida se pasará como `NULL`.

### Enrutamiento con Comodín
La coincidencia solo se realiza en segmentos individuales de URL. Si quieres coincidir múltiples segmentos, puedes usar el comodín `*`.

```php
Flight::route('/blog/*', function () {
  // Esto coincidirá con /blog/2000/02/01
});
```

Para enrutar todas las solicitudes a una sola devolución de llamada, puedes hacer:

```php
Flight::route('*', function () {
  // Haz algo
});
```

### Manejador de 404 No Encontrado

Por defecto, si una URL no se encuentra, Flight enviará una respuesta `HTTP 404 Not Found` que es muy simple y plana.
Si quieres tener una respuesta 404 más personalizada, puedes [mapear](/learn/extending) tu propio método `notFound`:

```php
Flight::map('notFound', function() {
	$url = Flight::request()->url;

	// También podrías usar Flight::render() con una plantilla personalizada.
    $output = <<<HTML
		<h1>My Custom 404 Not Found</h1>
		<h3>The page you have requested {$url} could not be found.</h3>
		HTML;

	$this->response()
		->clearBody()
		->status(404)
		->write($output)
		->send();
});
```

### Manejador de Método No Encontrado

Por defecto, si una URL se encuentra pero el método no está permitido, Flight enviará una respuesta `HTTP 405 Method Not Allowed` que es muy simple y plana (Ej: Method Not Allowed. Allowed Methods are: GET, POST). También incluirá un encabezado `Allow` con los métodos permitidos para esa URL.

Si quieres tener una respuesta 405 más personalizada, puedes [mapear](/learn/extending) tu propio método `methodNotFound`:

```php
use flight\net\Route;

Flight::map('methodNotFound', function(Route $route) {
	$url = Flight::request()->url;
	$methods = implode(', ', $route->methods);

	// También podrías usar Flight::render() con una plantilla personalizada.
	$output = <<<HTML
		<h1>My Custom 405 Method Not Allowed</h1>
		<h3>The method you have requested for {$url} is not allowed.</h3>
		<p>Allowed Methods are: {$methods}</p>
		HTML;

	$this->response()
		->clearBody()
		->status(405)
		->setHeader('Allow', $methods)
		->write($output)
		->send();
});
```

## Uso Avanzado

### Inyección de Dependencias en Rutas
Si quieres usar inyección de dependencias a través de un contenedor (PSR-11, PHP-DI, Dice, etc.), el único tipo de rutas donde eso está disponible es creando directamente el objeto tú mismo y usando el contenedor para crear tu objeto o puedes usar cadenas para definir la clase y el método a llamar. Puedes ir a la página de [Inyección de Dependencias](/learn/dependency-injection-container) para más información. 

Aquí hay un ejemplo rápido:

```php

use flight\database\PdoWrapper;

// Greeting.php
class Greeting
{
	protected PdoWrapper $pdoWrapper;
	public function __construct(PdoWrapper $pdoWrapper) {
		$this->pdoWrapper = $pdoWrapper;
	}

	public function hello(int $id) {
		// haz algo con $this->pdoWrapper
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Hello, world! My name is {$name}!";
	}
}

// index.php

// Configura el contenedor con los parámetros que necesites
// Ve la página de Inyección de Dependencias para más información sobre PSR-11
$dice = new \Dice\Dice();

// ¡No olvides reasignar la variable con '$dice = '!!!!! 
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// Registra el manejador del contenedor
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
});

// Rutas como de costumbre
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// o
Flight::route('/hello/@id', 'Greeting->hello');
// o
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

### Pasando la Ejecución a la Siguiente Ruta
<span class="badge bg-warning">Obsoleto</span>
Puedes pasar la ejecución a la siguiente ruta coincidente devolviendo `true` desde tu función de devolución de llamada.

```php
Flight::route('/user/@name', function (string $name) {
  // Verifica alguna condición
  if ($name !== "Bob") {
    // Continúa a la siguiente ruta
    return true;
  }
});

Flight::route('/user/*', function () {
  // Esto se llamará
});
```

Ahora se recomienda usar [middleware](/learn/middleware) para manejar casos de uso complejos como este.

### Alias de Ruta
Al asignar un alias a una ruta, puedes llamar más tarde ese alias en tu aplicación dinámicamente para que se genere después en tu código (ej: un enlace en una plantilla HTML, o generando una URL de redirección).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
// o 
Flight::route('/users/@id', function($id) { echo 'user:'.$id; })->setAlias('user_view');

// más tarde en el código en algún lugar
class UserController {
	public function update() {

		// código para guardar usuario...
		$id = $user['id']; // 5 por ejemplo

		$redirectUrl = Flight::getUrl('user_view', [ 'id' => $id ]); // devolverá '/users/5'
		Flight::redirect($redirectUrl);
	}
}

```

Esto es especialmente útil si tu URL cambia. En el ejemplo anterior, supongamos que users se movió a `/admin/users/@id` en su lugar.
Con el alias en su lugar para la ruta, ya no necesitas encontrar todas las URLs antiguas en tu código y cambiarlas porque el alias ahora devolverá `/admin/users/5` como en el ejemplo anterior.

El alias de ruta todavía funciona en grupos también:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
	// o
	Flight::route('/@id', function($id) { echo 'user:'.$id; })->setAlias('user_view');
});
```

### Inspeccionando Información de Ruta
Si quieres inspeccionar la información de la ruta coincidente, hay 2 formas de hacerlo:

1. Puedes usar una propiedad `executedRoute` en el objeto `Flight::router()`.
2. Puedes solicitar que el objeto ruta se pase a tu devolución de llamada pasando `true` como el tercer parámetro en el método ruta. El objeto ruta siempre será el último parámetro pasado a tu función de devolución de llamada.

#### `executedRoute`
```php
Flight::route('/', function() {
  $route = Flight::router()->executedRoute;
  // Haz algo con $route
  // Array de métodos HTTP coincidentes
  $route->methods;

  // Array de parámetros con nombre
  $route->params;

  // Expresión regular coincidente
  $route->regex;

  // Contiene el contenido de cualquier '*' usado en el patrón de URL
  $route->splat;

  // Muestra la ruta de la url....si realmente la necesitas
  $route->pattern;

  // Muestra qué middleware está asignado a esto
  $route->middleware;

  // Muestra el alias asignado a esta ruta
  $route->alias;
});
```

> **Nota:** La propiedad `executedRoute` solo se establecerá después de que una ruta haya sido ejecutada. Si intentas acceder a ella antes de que una ruta haya sido ejecutada, será `NULL`. También puedes usar executedRoute en [middleware](/learn/middleware) ¡también!

#### Pasar `true` a la definición de ruta
```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array de métodos HTTP coincidentes
  $route->methods;

  // Array de parámetros con nombre
  $route->params;

  // Expresión regular coincidente
  $route->regex;

  // Contiene el contenido de cualquier '*' usado en el patrón de URL
  $route->splat;

  // Muestra la ruta de la url....si realmente la necesitas
  $route->pattern;

  // Muestra qué middleware está asignado a esto
  $route->middleware;

  // Muestra el alias asignado a esta ruta
  $route->alias;
}, true);// <-- Este parámetro true es lo que hace que eso suceda
```

### Agrupación de Rutas y Middleware
Puede haber veces cuando quieras agrupar rutas relacionadas juntas (como `/api/v1`).
Puedes hacerlo usando el método `group`:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Coincide con /api/v1/users
  });

  Flight::route('/posts', function () {
	// Coincide con /api/v1/posts
  });
});
```

Incluso puedes anidar grupos de grupos:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() obtiene variables, ¡no establece una ruta! Ver contexto de objeto a continuación
	Flight::route('GET /users', function () {
	  // Coincide con GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // Coincide con POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Coincide con PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() obtiene variables, ¡no establece una ruta! Ver contexto de objeto a continuación
	Flight::route('GET /users', function () {
	  // Coincide con GET /api/v2/users
	});
  });
});
```

#### Agrupación con Contexto de Objeto

Aún puedes usar agrupación de rutas con el objeto `Engine` de la siguiente manera:

```php
$app = Flight::app();

$app->group('/api/v1', function (Router $router) {

  // usa la variable $router
  $router->get('/users', function () {
	// Coincide con GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Coincide con POST /api/v1/posts
  });
});
```

> **Nota:** Este es el método preferido de definir rutas y grupos con el objeto `$router`.

#### Agrupación con Middleware

También puedes asignar middleware a un grupo de rutas:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Coincide con /api/v1/users
  });
}, [ MyAuthMiddleware::class ]); // o [ new MyAuthMiddleware() ] si quieres usar una instancia
```

Ver más detalles en la página de [middleware de grupo](/learn/middleware#grouping-middleware).

### Enrutamiento con Recursos
Puedes crear un conjunto de rutas para un recurso usando el método `resource`. Esto creará un conjunto de rutas para un recurso que sigue las convenciones RESTful.

Para crear un recurso, haz lo siguiente:

```php
Flight::resource('/users', UsersController::class);
```

Y lo que sucederá en segundo plano es que creará las siguientes rutas:

```php
[
      'index' => 'GET /users',
      'create' => 'GET /users/create',
      'store' => 'POST /users',
      'show' => 'GET /users/@id',
      'edit' => 'GET /users/@id/edit',
      'update' => 'PUT /users/@id',
      'destroy' => 'DELETE /users/@id'
]
```

Y tu controlador usará los siguientes métodos:

```php
class UsersController
{
    public function index(): void
    {
    }

    public function show(string $id): void
    {
    }

    public function create(): void
    {
    }

    public function store(): void
    {
    }

    public function edit(string $id): void
    {
    }

    public function update(string $id): void
    {
    }

    public function destroy(string $id): void
    {
    }
}
```

> **Nota**: Puedes ver las rutas recién agregadas con `runway` ejecutando `php runway routes`.

#### Personalizando Rutas de Recursos

Hay algunas opciones para configurar las rutas de recursos.

##### Alias Base

Puedes configurar el `aliasBase`. Por defecto, el alias es la última parte de la URL especificada.
Por ejemplo, `/users/` resultaría en un `aliasBase` de `users`. Cuando se crean estas rutas, los alias son `users.index`, `users.create`, etc. Si quieres cambiar el alias, establece el `aliasBase` al valor que quieras.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

##### Solo y Excepto

También puedes especificar qué rutas quieres crear usando las opciones `only` y `except`.

```php
// Lista blanca solo estos métodos y lista negra el resto
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
// Lista negra solo estos métodos y lista blanca el resto
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

Estas son básicamente opciones de lista blanca y lista negra para que puedas especificar qué rutas quieres crear.

##### Middleware

También puedes especificar middleware para ejecutarse en cada una de las rutas creadas por el método `resource`.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

### Respuestas en Streaming

Ahora puedes transmitir respuestas al cliente usando `stream()` o `streamWithHeaders()`. 
Esto es útil para enviar archivos grandes, procesos de larga duración o generar respuestas grandes. 
Transmitir una ruta se maneja un poco diferente a una ruta regular.

> **Nota:** Las respuestas en streaming solo están disponibles si tienes [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) establecido en `false`.

#### Streaming con Encabezados Manuales

Puedes transmitir una respuesta al cliente usando el método `stream()` en una ruta. Si haces esto, debes establecer todos los encabezados a mano antes de que salgas algo al cliente.
Esto se hace con la función php `header()` o el método `Flight::response()->setRealHeader()`.

```php
Flight::route('/@filename', function($filename) {

	$response = Flight::response();

	// obviamente sanitizarías la ruta y demás.
	$fileNameSafe = basename($filename);

	// Si tienes encabezados adicionales para establecer aquí después de que la ruta se haya ejecutado
	// debes definirlos antes de que nada se haga eco.
	// Deben ser todos una llamada cruda a la función header() o 
	// una llamada a Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// o
	$response->setRealHeader('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');

	$filePath = '/some/path/to/files/'.$fileNameSafe;

	if (!is_readable($filePath)) {
		Flight::halt(404, 'File not found');
	}

	// establece manualmente la longitud del contenido si lo deseas
	header('Content-Length: '.filesize($filePath));
	// o
	$response->setRealHeader('Content-Length: '.filesize($filePath));

	// Transmite el archivo al cliente mientras se lee
	readfile($filePath);

// Esta es la línea mágica aquí
})->stream();
```

#### Streaming con Encabezados

También puedes usar el método `streamWithHeaders()` para establecer los encabezados antes de comenzar a transmitir.

```php
Flight::route('/stream-users', function() {

	// puedes agregar cualquier encabezado adicional que quieras aquí
	// solo debes usar header() o Flight::response()->setRealHeader()

	// sin embargo, como obtengas tus datos, solo como ejemplo...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// Esto es requerido para enviar los datos al cliente
		ob_flush();
	}
	echo '}';

// Esta es la forma en que establecerás los encabezados antes de comenzar a transmitir.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// código de estado opcional, por defecto 200
	'status' => 200
]);
```

## Ver También
- [Middleware](/learn/middleware) - Usando middleware con rutas para autenticación, registro, etc.
- [Inyección de Dependencias](/learn/dependency-injection-container) - Simplificando la creación y gestión de objetos en rutas.
- [¿Por qué un Framework?](/learn/why-frameworks) - Entendiendo los beneficios de usar un framework como Flight.
- [Extensión](/learn/extending) - Cómo extender Flight con tu propia funcionalidad incluyendo el método `notFound`.
- [php.net: preg_match](https://www.php.net/manual/en/function.preg-match.php) - Función PHP para coincidencia de expresiones regulares.

## Solución de Problemas
- Los parámetros de ruta se coinciden por orden, no por nombre. Asegúrate de que el orden de los parámetros de la devolución de llamada coincida con la definición de la ruta.
- Usar `Flight::get()` no define una ruta; usa `Flight::route('GET /...')` para enrutamiento o el contexto del objeto Router en grupos (ej. `$router->get(...)`).
- La propiedad executedRoute solo se establece después de que una ruta se ejecuta; es NULL antes de la ejecución.
- El streaming requiere que la funcionalidad de búfer de salida legacy de Flight esté deshabilitada (`flight.v2.output_buffering = false`).
- Para inyección de dependencias, solo ciertas definiciones de ruta soportan instanciación basada en contenedor.

### 404 No Encontrado o Comportamiento de Ruta Inesperado

Si estás viendo un error 404 No Encontrado (pero juras por tu vida que realmente está ahí y no es un error tipográfico), esto en realidad podría ser un problema con que devuelves un valor en tu punto final de ruta en lugar de solo hacer eco de él. La razón para esto es intencional pero podría sorprender a algunos desarrolladores.

```php
Flight::route('/hello', function(){
	// Esto podría causar un error 404 No Encontrado
	return 'Hello World';
});

// Lo que probablemente quieres
Flight::route('/hello', function(){
	echo 'Hello World';
});
```

La razón para esto es por un mecanismo especial incorporado en el router que maneja la salida de retorno como una señal para "ir a la siguiente ruta". 
Puedes ver el comportamiento documentado en la sección de [Enrutamiento](/learn/routing#passing).

## Registro de Cambios
- v3: Agregado enrutamiento con recursos, alias de ruta y soporte para streaming, grupos de ruta y soporte para middleware.
- v1: La gran mayoría de características básicas disponibles.