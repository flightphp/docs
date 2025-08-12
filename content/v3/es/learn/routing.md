# Enrutamiento

> **Nota:** ¿Quieres entender más sobre enrutamiento? Consulta la página ["why a framework?"](/learn/why-frameworks) para una explicación más detallada.

El enrutamiento básico en Flight se realiza mediante la coincidencia de un patrón de URL con una función de devolución de llamada o un arreglo de una clase y un método.

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> Las rutas se coinciden en el orden en que se definen. La primera ruta que coincida con una solicitud se invocará.

### Devoluciones de llamada/Funciones
La devolución de llamada puede ser cualquier objeto que sea invocable. Por lo tanto, puedes usar una función regular:

```php
function hello() {
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

### Clases
También puedes usar un método estático de una clase:

```php
class Greeting {
    public static function hello() {
        echo 'hello world!';
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

O creando un objeto primero y luego llamando al método:

```php
// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Hello, {$this->name}!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// También puedes hacer esto sin crear el objeto primero
// Nota: No se inyectarán argumentos en el constructor
Flight::route('/', [ 'Greeting', 'hello' ]);
// Además, puedes usar esta sintaxis más corta
Flight::route('/', 'Greeting->hello');
// o
Flight::route('/', Greeting::class.'->hello');
```

#### Inyección de dependencias a través de DIC (Contenedor de Inyección de Dependencias)
Si deseas usar inyección de dependencias a través de un contenedor (PSR-11, PHP-DI, Dice, etc.), el único tipo de rutas donde esto está disponible es creando el objeto tú mismo directamente o usando cadenas para definir la clase y el método a llamar. Puedes ir a la página de [Inyección de Dependencias](/learn/extending) para más información.

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
// Consulta la página de Inyección de Dependencias para más información sobre PSR-11
$dice = new \Dice\Dice();

// ¡No olvides reasignar la variable con '$dice =' !!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// Registra el controlador del contenedor
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

## Enrutamiento por Método

Por defecto, los patrones de ruta se coinciden contra todos los métodos de solicitud. Puedes responder a métodos específicos colocando un identificador antes de la URL.

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';
});

// No puedes usar Flight::get() para rutas ya que ese es un método para obtener variables, no para crear una ruta.
// Flight::post('/', function() { /* code */ });
// Flight::patch('/', function() { /* code */ });
// Flight::put('/', function() { /* code */ });
// Flight::delete('/', function() { /* code */ });
```

También puedes mapear múltiples métodos a una sola devolución de llamada usando un delimitador `|`:

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';
});
```

Además, puedes obtener el objeto Router que tiene algunos métodos de ayuda para usar:

```php
$router = Flight::router();

// mapea todos los métodos
$router->map('/', function() {
	echo 'hello world!';
});

// solicitud GET
$router->get('/users', function() {
	echo 'users';
});
// $router->post();
// $router->put();
// $router->delete();
// $router->patch();
```

## Expresiones Regulares

Puedes usar expresiones regulares en tus rutas:

```php
Flight::route('/user/[0-9]+', function () {
  // Esto coincidirá con /user/1234
});
```

Aunque este método está disponible, se recomienda usar parámetros nombrados, o parámetros nombrados con expresiones regulares, ya que son más legibles y fáciles de mantener.

## Parámetros Nombrados

Puedes especificar parámetros nombrados en tus rutas que se pasarán a tu función de devolución de llamada. **Esto es más para la legibilidad de la ruta que para cualquier otra cosa. Por favor, consulta la sección a continuación sobre la advertencia importante.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

También puedes incluir expresiones regulares con tus parámetros nombrados usando el delimitador `:`:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Esto coincidirá con /bob/123
  // Pero no coincidirá con /bob/12345
});
```

> **Nota:** Coincidir grupos de regex `()` con parámetros posicionales no está soportado. :'\(

### Advertencia Importante

Aunque en el ejemplo anterior parece que `@name` está directamente ligado a la variable `$name`, no lo está. El orden de los parámetros en la función de devolución de llamada es lo que determina qué se pasa a ella. Así que si cambias el orden de los parámetros en la función de devolución de llamada, las variables también se cambiarán. Aquí hay un ejemplo:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";
});
```

Y si vas a la siguiente URL: `/bob/123`, la salida sería `hello, 123 (bob)!`. Por favor, ten cuidado al configurar tus rutas y tus funciones de devolución de llamada.

## Parámetros Opcionales

Puedes especificar parámetros nombrados que sean opcionales para la coincidencia envolviendo segmentos en paréntesis.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // Esto coincidirá con las siguientes URLS:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Cualquier parámetro opcional que no coincida se pasará como `NULL`.

## Comodines

La coincidencia solo se realiza en segmentos individuales de URL. Si quieres coincidir con múltiples segmentos, puedes usar el comodín `*`.

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

## Pasar Ejecución

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

## Alias de Rutas

Puedes asignar un alias a una ruta, para que la URL pueda generarse dinámicamente más adelante en tu código (como en una plantilla, por ejemplo).

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// más adelante en el código en algún lugar
Flight::getUrl('user_view', [ 'id' => 5 ]); // devolverá '/users/5'
```

Esto es especialmente útil si tu URL cambia. En el ejemplo anterior, digamos que los usuarios se mueven a `/admin/users/@id` en su lugar. Con el alias en su lugar, no tienes que cambiar en ningún lugar donde se haga referencia al alias porque el alias ahora devolverá `/admin/users/5` como en el ejemplo anterior.

El alias de rutas también funciona en grupos:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// más adelante en el código en algún lugar
Flight::getUrl('user_view', [ 'id' => 5 ]); // devolverá '/users/5'
```

## Información de Ruta

Si quieres inspeccionar la información de la ruta coincidente, puedes solicitar que se pase el objeto de ruta a tu devolución de llamada pasando `true` como el tercer parámetro en el método de ruta. El objeto de ruta siempre será el último parámetro pasado a tu función de devolución de llamada.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Arreglo de métodos HTTP coincidentes
  $route->methods;

  // Arreglo de parámetros nombrados
  $route->params;

  // Expresión regular coincidente
  $route->regex;

  // Contiene el contenido de cualquier '*' usado en el patrón de URL
  $route->splat;

  // Muestra la ruta de URL.... si realmente la necesitas
  $route->pattern;

  // Muestra qué middleware está asignado a esto
  $route->middleware;

  // Muestra el alias asignado a esta ruta
  $route->alias;
}, true);
```

## Agrupación de Rutas

Puede haber ocasiones en que quieras agrupar rutas relacionadas (como `/api/v1`). Puedes hacer esto usando el método `group`:

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
	// Flight::get() obtiene variables, ¡no establece una ruta! Consulta el contexto de objeto a continuación
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

	// Flight::get() obtiene variables, ¡no establece una ruta! Consulta el contexto de objeto a continuación
	Flight::route('GET /users', function () {
	  // Coincide con GET /api/v2/users
	});
  });
});
```

### Agrupación con Contexto de Objeto

Todavía puedes usar agrupación de rutas con el objeto `Engine` de la siguiente manera:

```php
$app = new \flight\Engine();
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

### Agrupación con Middleware

También puedes asignar middleware a un grupo de rutas:

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Coincide con /api/v1/users
  });
}, [ MyAuthMiddleware::class ]); // o [ new MyAuthMiddleware() ] si quieres usar una instancia
```

Consulta más detalles en la página de [middleware de grupo](/learn/middleware#grouping-middleware).

## Enrutamiento de Recursos

Puedes crear un conjunto de rutas para un recurso usando el método `resource`. Esto creará un conjunto de rutas para un recurso que sigue las convenciones RESTful.

Para crear un recurso, haz lo siguiente:

```php
Flight::resource('/users', UsersController::class);
```

Y lo que sucederá en el fondo es que creará las siguientes rutas:

```php
[
      'index' => 'GET ',
      'create' => 'GET /create',
      'store' => 'POST ',
      'show' => 'GET /@id',
      'edit' => 'GET /@id/edit',
      'update' => 'PUT /@id',
      'destroy' => 'DELETE /@id'
]
```

Y tu controlador se verá así:

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

### Personalizando Rutas de Recursos

Hay algunas opciones para configurar las rutas de recursos.

#### Base de Alias

Puedes configurar la `aliasBase`. Por defecto, el alias es la última parte de la URL especificada. Por ejemplo, `/users/` resultaría en un `aliasBase` de `users`. Cuando se crean estas rutas, los alias son `users.index`, `users.create`, etc. Si quieres cambiar el alias, establece `aliasBase` en el valor que deseas.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

#### Solo y Excepto

También puedes especificar qué rutas quieres crear usando las opciones `only` y `except`.

```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

Estas son básicamente opciones de lista blanca y lista negra para que puedas especificar qué rutas quieres crear.

#### Middleware

También puedes especificar middleware para que se ejecute en cada una de las rutas creadas por el método `resource`.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

## Transmisión en Streaming

Ahora puedes transmitir respuestas al cliente usando el método `streamWithHeaders()`. Esto es útil para enviar archivos grandes, procesos de larga duración o generar respuestas grandes. Transmitir una ruta se maneja un poco diferente a una ruta regular.

> **Nota:** Las respuestas en streaming solo están disponibles si tienes [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) establecido en false.

### Streaming con Encabezados Manuales

Puedes transmitir una respuesta al cliente usando el método `stream()`. Si haces esto, debes establecer todos los métodos a mano antes de que salgas algo al cliente. Esto se hace con la función `header()` de PHP o el método `Flight::response()->setRealHeader()`.

```php
Flight::route('/@filename', function($filename) {

	// obviamente, sanitizarías la ruta y demás.
	$fileNameSafe = basename($filename);

	// Si tienes encabezados adicionales para establecer aquí después de que la ruta se ejecute
	// debes definirlos antes de que se haga eco de nada.
	// Deben ser una llamada cruda a la función header() o 
	// una llamada a Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// o
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);

	// Captura de errores y demás
	if(empty($fileData)) {
		Flight::halt(404, 'File not found');
	}

	// establece manualmente la longitud de contenido si lo deseas
	header('Content-Length: '.filesize($filename));

	// Transmite los datos al cliente
	echo $fileData;

// Esta es la línea mágica aquí
})->stream();
```

### Streaming con Encabezados

También puedes usar el método `streamWithHeaders()` para establecer los encabezados antes de comenzar a transmitir.

```php
Flight::route('/stream-users', function() {

	// puedes agregar cualquier encabezado adicional que quieras aquí
	// solo debes usar header() o Flight::response()->setRealHeader()

	// sin embargo, como sea que obtengas tus datos, solo como un ejemplo...
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

// Así es como establecerás los encabezados antes de comenzar a transmitir.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// código de estado opcional, por defecto es 200
	'status' => 200
]);
```