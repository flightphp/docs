# Enrutamiento

> **Nota:** ¿Quieres entender más sobre el enrutamiento? Consulta la página de ["¿por qué un framework?"](/learn/why-frameworks) para una explicación más detallada.

El enrutamiento básico en Flight se realiza haciendo coincidir un patrón de URL con una función de devolución de llamada o un arreglo de una clase y un método.

```php
Flight::route('/', function(){
    echo '¡hola mundo!';
});
```

> Las rutas se emparejan en el orden en que se definen. La primera ruta que coincida con una solicitud será invocada.

### Devoluciones de llamada/Funciones
La devolución de llamada puede ser cualquier objeto que sea invocable. Así que puedes usar una función normal:

```php
function hello() {
    echo '¡hola mundo!';
}

Flight::route('/', 'hello');
```

### Clases
También puedes usar un método estático de una clase:

```php
class Greeting {
    public static function hello() {
        echo '¡hola mundo!';
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

O creando primero un objeto y luego llamando al método:

```php

// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "¡Hola, {$this->name}!";
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

#### Inyección de Dependencias a través de DIC (Contenedor de Inyección de Dependencias)
Si deseas utilizar la inyección de dependencias a través de un contenedor (PSR-11, PHP-DI, Dice, etc.), el único tipo de rutas donde eso está disponible es creando directamente el objeto tú mismo y utilizando el contenedor para crear tu objeto o puedes usar cadenas para definir la clase y el método a llamar. Puedes consultar la página de [Inyección de Dependencias](/learn/extending) para obtener más información.

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
		// hacer algo con $this->pdoWrapper
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "¡Hola, mundo! ¡Mi nombre es {$name}!";
	}
}

// index.php

// Configura el contenedor con los parámetros que necesites
// Consulta la página de Inyección de Dependencias para obtener más información sobre PSR-11
$dice = new \Dice\Dice();

// ¡No olvides reasignar la variable con '$dice = '!!!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'contraseña'
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

## Enrutamiento por Método

Por defecto, los patrones de ruta se comparan con todos los métodos de solicitud. Puedes responder a métodos específicos colocando un identificador antes de la URL.

```php
Flight::route('GET /', function () {
  echo 'He recibido una solicitud GET.';
});

Flight::route('POST /', function () {
  echo 'He recibido una solicitud POST.';
});

// No puedes usar Flight::get() para rutas ya que ese es un método
//    para obtener variables, no para crear una ruta.
// Flight::post('/', function() { /* código */ });
// Flight::patch('/', function() { /* código */ });
// Flight::put('/', function() { /* código */ });
// Flight::delete('/', function() { /* código */ });
```

También puedes mapear múltiples métodos a una sola devolución de llamada usando un delimitador `|`:

```php
Flight::route('GET|POST /', function () {
  echo 'He recibido ya sea una solicitud GET o POST.';
});
```

Además, puedes obtener el objeto Router que tiene algunos métodos auxiliares para que los uses:

```php

$router = Flight::router();

// mapea todos los métodos
$router->map('/', function() {
	echo '¡hola mundo!';
});

// solicitud GET
$router->get('/users', function() {
	echo 'usuarios';
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

Puedes especificar parámetros nombrados en tus rutas que se pasarán a tu función de devolución de llamada. **Esto es más por la legibilidad de la ruta que por cualquier otra cosa. Por favor, consulta la sección siguiente sobre una advertencia importante.**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "¡hola, $name ($id)!"; 
});
```

También puedes incluir expresiones regulares en tus parámetros nombrados usando el delimitador `:`:

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // Esto coincidirá con /bob/123
  // Pero no coincidirá con /bob/12345
});
```

> **Nota:** No se admiten grupos de coincidencias regex `()` con parámetros posicionales. :'\(

### Advertencia Importante

Aunque en el ejemplo anterior, parece que `@name` está directamente vinculado a la variable `$name`, no lo está. El orden de los parámetros en la función de devolución de llamada es lo que determina qué se pasa a ella. Así que, si cambiaras el orden de los parámetros en la función de devolución de llamada, las variables también se cambiarían. Aquí hay un ejemplo:

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "¡hola, $name ($id)!"; 
});
```

Y si fueras a la siguiente URL: `/bob/123`, la salida sería `¡hola, 123 (bob)!`. 
Por favor, ten cuidado al configurar tus rutas y tus funciones de devolución de llamada.

## Parámetros Opcionales

Puedes especificar parámetros nombrados que son opcionales para la coincidencia envolviendo segmentos entre paréntesis.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // Esto coincidirá con las siguientes URL:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Cualquier parámetro opcional que no coincida se pasará como `NULL`.

## Comodines

La coincidencia se realiza solo en segmentos individuales de la URL. Si deseas hacer coincidir múltiples segmentos, puedes usar el comodín `*`.

```php
Flight::route('/blog/*', function () {
  // Esto coincidirá con /blog/2000/02/01
});
```

Para enrutar todas las solicitudes a una sola devolución de llamada, puedes hacer:

```php
Flight::route('*', function () {
  // Hacer algo
});
```

## Pasar

Puedes pasar la ejecución a la siguiente ruta coincidente devolviendo `true` desde tu función de devolución de llamada.

```php
Flight::route('/user/@name', function (string $name) {
  // Verifica alguna condición
  if ($name !== "Bob") {
    // Continuar a la siguiente ruta
    return true;
  }
});

Flight::route('/user/*', function () {
  // Esto se llamará
});
```

## Alias de Ruta

Puedes asignar un alias a una ruta, para que la URL pueda generarse de forma dinámica más tarde en tu código (como una plantilla, por ejemplo).

```php
Flight::route('/users/@id', function($id) { echo 'usuario:'.$id; }, false, 'user_view');

// más tarde en el código en alguna parte
Flight::getUrl('user_view', [ 'id' => 5 ]); // devolverá '/users/5'
```

Esto es especialmente útil si tu URL cambia. En el ejemplo anterior, digamos que los usuarios se movieron a `/admin/users/@id` en su lugar.
Con el alias en su lugar, no tienes que cambiar en ningún lugar donde referencias el alias porque el alias ahora devolverá `/admin/users/5` como en el ejemplo anterior.

El alias de ruta también funciona en grupos:

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'usuario:'.$id; }, false, 'user_view');
});

// más tarde en el código en alguna parte
Flight::getUrl('user_view', [ 'id' => 5 ]); // devolverá '/users/5'
```

## Información de Ruta

Si deseas inspeccionar la información de la ruta coincidente, puedes solicitar que el objeto de ruta se pase a tu función de devolución de llamada pasando `true` como tercer parámetro en el método de ruta. El objeto de ruta siempre será el último parámetro pasado a tu función de devolución de llamada.

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array de métodos HTTP coincidentes
  $route->methods;

  // Array de parámetros nombrados
  $route->params;

  // Expresión regular coincidente
  $route->regex;

  // Contiene el contenido de cualquier '*' usado en el patrón de URL
  $route->splat;

  // Muestra la ruta de la URL....si realmente lo necesitas
  $route->pattern;

  // Muestra qué middleware está asignado a esto
  $route->middleware;

  // Muestra el alias asignado a esta ruta
  $route->alias;
}, true);
```

## Agrupación de Rutas

Puede haber momentos en los que desees agrupar rutas relacionadas (como `/api/v1`).
Puedes hacer esto usando el método `group`:

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
	// Flight::get() obtiene variables, ¡no establece una ruta! Consulta el contexto del objeto a continuación
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

	// Flight::get() obtiene variables, ¡no establece una ruta! Consulta el contexto del objeto a continuación
	Flight::route('GET /users', function () {
	  // Coincide con GET /api/v2/users
	});
  });
});
```

### Agrupación con Contexto de Objeto

Aún puedes utilizar la agrupación de rutas con el objeto `Engine` de la siguiente manera:

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

## Enrutamiento de Recursos

Puedes crear un conjunto de rutas para un recurso usando el método `resource`. Esto creará un conjunto de rutas para un recurso que sigue las convenciones RESTful.

Para crear un recurso, haz lo siguiente:

```php
Flight::resource('/users', UsersController::class);
```

Y lo que sucederá en segundo plano es que se crearán las siguientes rutas:

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

> **Nota**: Puedes ver las rutas recién añadidas con `runway` al ejecutar `php runway routes`.

### Personalizando Rutas de Recursos

Hay algunas opciones para configurar las rutas de recursos.

#### Alias Base

Puedes configurar el `aliasBase`. Por defecto, el alias es la última parte de la URL especificada.
Por ejemplo, `/users/` daría como resultado un `aliasBase` de `users`. Cuando se crean estas rutas,
los alias son `users.index`, `users.create`, etc. Si deseas cambiar el alias, establece el `aliasBase`
al valor que quieras.

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

#### Solo y Excepto

También puedes especificar qué rutas deseas crear utilizando las opciones `only` y `except`.

```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

Estas son opciones básicamente de listas blancas y negras para que puedas especificar qué rutas deseas crear.

#### Middleware

También puedes especificar middleware que se ejecute en cada una de las rutas creadas por el método `resource`.

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

## Transmisión

Ahora puedes transmitir respuestas al cliente utilizando el método `streamWithHeaders()`.
Esto es útil para enviar archivos grandes, procesos de larga duración o para generar grandes respuestas.
Transmitir una ruta se maneja de manera un poco diferente a una ruta regular.

> **Nota:** La transmisión de respuestas solo está disponible si tienes [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) configurado en falso.

### Transmitir con Encabezados Manuales

Puedes transmitir una respuesta al cliente utilizando el método `stream()` en una ruta. Si haces esto, debes establecer todos los métodos a mano antes de enviar cualquier cosa al cliente.
Esto se hace con la función `header()` de php o con el método `Flight::response()->setRealHeader()`.

```php
Flight::route('/@filename', function($filename) {

	// obviamente sanitizarías la ruta y demás.
	$fileNameSafe = basename($filename);

	// Si tienes encabezados adicionales que establecer aquí después de que se haya ejecutado la ruta
	// debes definirlos antes de que se imprima cualquier cosa.
	// Todos deben ser una llamada sin procesar a la función header() o 
	// una llamada a Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// o
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);

	// Captura de errores y demás
	if(empty($fileData)) {
		Flight::halt(404, 'Archivo no encontrado');
	}

	// establece manualmente la longitud del contenido si lo deseas
	header('Content-Length: '.filesize($filename));

	// Transmite los datos al cliente
	echo $fileData;

// Esta es la línea mágica aquí
})->stream();
```

### Transmitir con Encabezados

También puedes usar el método `streamWithHeaders()` para establecer los encabezados antes de comenzar a transmitir.

```php
Flight::route('/stream-users', function() {

	// puedes agregar cualquier encabezado adicional que desees aquí
	// solo debes usar header() o Flight::response()->setRealHeader()

	// sin importar cómo obtengas tus datos, solo como ejemplo...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// Esto es necesario para enviar los datos al cliente
		ob_flush();
	}
	echo '}';

// Así es como establecerás los encabezados antes de comenzar a transmitir.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// código de estado opcional, predeterminado es 200
	'status' => 200
]);
```