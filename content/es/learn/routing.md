# Enrutamiento

> **Nota:** ¿Quieres entender más sobre el enrutamiento? Echa un vistazo a la página ["¿por qué un framework?"](/learn/why-frameworks) para obtener una explicación más detallada.

El enrutamiento básico en Flight se realiza al emparejar un patrón de URL con una función de devolución de llamada o una matriz de una clase y método.

```php
Flight::route('/', function(){
    echo '¡hola mundo!';
});
```

La devolución de llamada puede ser cualquier objeto que sea llamable. Por lo que puedes usar una función regular:

```php
function hello(){
    echo '¡hola mundo!';
}

Flight::route('/', 'hello');
```

O un método de clase:

```php
class Greeting {
    public static function hello() {
        echo '¡hola mundo!';
    }
}

Flight::route('/', array('Greeting','hello'));
```

O un método de objeto:

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

Flight::route('/', array($greeting, 'hello'));
```

Las rutas se emparejan en el orden en que se definen. La primera ruta que coincida con una solicitud será invocada.

## Enrutamiento de Método

Por defecto, los patrones de ruta se emparejan con todos los métodos de solicitud. Puedes responder
a métodos específicos colocando un identificador antes de la URL.

```php
Flight::route('GET /', function () {
  echo 'He recibido una solicitud GET.';
});

Flight::route('POST /', function () {
  echo 'He recibido una solicitud POST.';
});
```

También puedes asignar varios métodos a una única devolución de llamada usando un delimitador `|`:

```php
Flight::route('GET|POST /', function () {
  echo 'He recibido una solicitud GET o POST.';
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
$router->get('/usuarios', function() {
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
Flight::route('/usuario/[0-9]+', function () {
  // Esto coincidirá con /usuario/1234
});
```

Aunque este método está disponible, se recomienda usar parámetros nombrados o
parámetros nombrados con expresiones regulares, ya que son más legibles y fáciles de mantener.

## Parámetros Nombrados

Puedes especificar parámetros nombrados en tus rutas que se pasarán a lo largo de
tu función de devolución de llamada.

```php
Flight::route('/@nombre/@id', function (string $nombre, string $id) {
  echo "¡hola, $nombre ($id)!";
});
```

También puedes incluir expresiones regulares con tus parámetros nombrados usando
el delimitador `:`:

```php
Flight::route('/@nombre/@id:[0-9]{3}', function (string $nombre, string $id) {
  // Esto coincidirá con /bob/123
  // Pero no coincidirá con /bob/12345
});
```

> **Nota:** No se admite la coincidencia de grupos de regex `()` con parámetros nombrados. :'\(

## Parámetros Opcionales

Puedes especificar parámetros nombrados que son opcionales para la coincidencia al envolver
segmentos entre paréntesis.

```php
Flight::route(
  '/blog(/@año(/@mes(/@día)))',
  function(?string $año, ?string $mes, ?string $día) {
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

La coincidencia se realiza solo en segmentos individuales de URL. Si deseas coincidir con múltiples
segmentos puedes usar el comodín `*`.

```php
Flight::route('/blog/*', function () {
  // Esto coincidirá con /blog/2000/02/01
});
```

Para enrutamiento de todas las solicitudes a una única devolución de llamada, puedes hacerlo así:

```php
Flight::route('*', function () {
  // Haz algo
});
```

## Paso

Puedes pasar la ejecución a la siguiente ruta coincidente retornando `true` desde
tu función de devolución de llamada.

```php
Flight::route('/usuario/@nombre', function (string $nombre) {
  // Verifica alguna condición
  if ($nombre !== "Bob") {
    // Continuar a la siguiente ruta
    return true;
  }
});

Flight::route('/usuario/*', function () {
  // Esto se llamará
});
```

## Aliasing de Ruta

Puedes asignar un alias a una ruta, para que la URL pueda generarse dinámicamente más tarde en tu código (como una plantilla por ejemplo).

```php
Flight::route('/usuarios/@id', function($id) { echo 'usuario:'.$id; }, false, 'vista_usuario');

// más adelante en algún lugar del código
Flight::getUrl('vista_usuario', [ 'id' => 5 ]); // devolverá '/usuarios/5'
```

Esto es especialmente útil si tu URL llega a cambiar. En el ejemplo anterior, supongamos que los usuarios se movieron a `/admin/usuarios/@id` en su lugar.
Con el alias en su lugar, no tienes que cambiar en ningún lugar donde haces referencia al alias porque el alias ahora devolverá `/admin/usuarios/5` como en el
ejemplo anterior.

El alias de ruta aun funciona en grupos también:

```php
Flight::group('/usuarios', function() {
    Flight::route('/@id', function($id) { echo 'usuario:'.$id; }, false, 'vista_usuario');
});


// más adelante en algún lugar del código
Flight::getUrl('vista_usuario', [ 'id' => 5 ]); // devolverá '/usuarios/5'
```

## Información de Ruta

Si quieres inspeccionar la información de la ruta coincidente, puedes solicitar que el objeto de ruta se pase a tu devolución de llamada pasando `true` como el tercer parámetro en
el método de ruta. El objeto de ruta siempre será el último parámetro pasado a tu
función de devolución de llamada.

```php
Flight::route('/', function(\flight\net\Route $ruta) {
  // Array de métodos HTTP emparejados
  $ruta->methods;

  // Array de parámetros nombrados
  $ruta->params;

  // Expresión regular de coincidencia
  $ruta->regex;

  // Contiene los contenidos de cualquier '*' utilizado en el patrón de URL
  $ruta->splat;

  // Muestra la ruta de la URL.... si realmente lo necesitas
  $ruta->pattern;

  // Muestra qué middleware está asignado a esto
  $ruta->middleware;

  // Muestra el alias asignado a esta ruta
  $ruta->alias;
}, true);
```

## Agrupación de Rutas

Puede haber momentos en los que quieras agrupar rutas relacionadas juntas (como `/api/v1`).
Puedes hacer esto usando el método `group`:

```php
Flight::group('/api/v1', function () {
  Flight::route('/usuarios', function () {
	// Coincide con /api/v1/usuarios
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
	// Flight::get() obtiene variables, ¡no establece una ruta! Ver el contexto del objeto abajo
	Flight::route('GET /usuarios', function () {
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

	// Flight::get() obtiene variables, no establece una ruta. Ver el contexto del objeto abajo
	Flight::route('GET /usuarios', function () {
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
  $router->get('/usuarios', function () {
	// Coincide con GET /api/v1/usuarios
  });

  $router->post('/posts', function () {
	// Coincide con POST /api/v1/posts
  });
});
```

## Streaming

Ahora puedes transmitir respuestas al cliente usando el método `streamWithHeaders()`.
Esto es útil para enviar archivos grandes, procesos largos en ejecución o generar respuestas grandes.
La transmisión de una ruta se maneja un poco diferente que una ruta regular.

> **Nota:** La transmisión de respuestas solo está disponible si tienes [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) establecido en false.

```php
Flight::route('/stream-usuarios', function() {

	// cómo extraes tus datos, solo como ejemplo...
	$usuarios_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$cantidad_usuarios = count($usuarios);
	while($usuario = $usuarios_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($usuario);
		if(--$cantidad_usuarios > 0) {
			echo ',';
		}

		// Esto es necesario para enviar los datos al cliente
		ob_flush();
	}
	echo '}';

// Así es como se establecerán las cabeceras antes de empezar a transmitir.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	// código de estado opcional, por defecto es 200
	'status' => 200
]);
```