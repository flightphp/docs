## Enrutamiento

> **Nota:** ¿Quieres entender más sobre el enrutamiento? Consulta la página ["¿Por qué un marco?"](/learn/why-frameworks) para obtener una explicación más detallada.

El enrutamiento básico en Flight se realiza mediante la coincidencia de un patrón de URL con una función de devolución de llamada o un array de una clase y un método.

```php
Flight::route('/', function(){
    echo '¡Hola Mundo!';
});
```

La devolución de llamada puede ser cualquier objeto que sea invocable. Por lo tanto, puedes usar una función regular:

```php
function hello(){
    echo '¡Hola Mundo!';
}

Flight::route('/', 'hello');
```

O un método de clase:

```php
class Saludo {
    public static function hello() {
        echo '¡Hola Mundo!';
    }
}

Flight::route('/', array('Saludo','hello'));
```

O un método de un objeto:

```php

// Greeting.php
class Saludo
{
    public function __construct() {
        $this->name = 'Juan Pérez';
    }

    public function hello() {
        echo "¡Hola, {$this->name}!";
    }
}

// index.php
$saludo = new Saludo();

Flight::route('/', array($saludo, 'hello'));
```

Las rutas se emparejan en el orden en que se definen. La primera ruta que coincida con una solicitud será invocada.

## Enrutamiento de Método

Por defecto, los patrones de ruta se comparan con todos los métodos de solicitud. Puedes responder
a métodos específicos colocando un identificador antes de la URL.

```php
Flight::route('GET /', function () {
  echo 'Recibí una solicitud GET.';
});

Flight::route('POST /', function () {
  echo 'Recibí una solicitud POST.';
});
```

También puedes asignar varios métodos a una sola devolución de llamada utilizando un delimitador `|`:

```php
Flight::route('GET|POST /', function () {
  echo 'Recibí una solicitud GET o POST.';
});
```

Además, puedes obtener el objeto Router que tiene algunos métodos auxiliares para que los uses:

```php

$router = Flight::router();

// asignar a todos los métodos
$router->map('/', function() {
	echo '¡Hola Mundo!';
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

Aunque este método está disponible, se recomienda usar parámetros con nombre, o
parámetros con nombre y expresiones regulares, ya que son más legibles y fáciles de mantener.

## Parámetros con Nombre

Puedes especificar parámetros con nombre en tus rutas que se pasarán
a tu función de devolución de llamada.

```php
Flight::route('/@nombre/@id', function (string $nombre, string $id) {
  echo "¡Hola, $nombre ($id)!";
});
```

También puedes incluir expresiones regulares con tus parámetros con nombre mediante
el delimitador `:`:

```php
Flight::route('/@nombre/@id:[0-9]{3}', function (string $nombre, string $id) {
  // Esto coincidirá con /juan/123
  // Pero no coincidirá con /juan/12345
});
```

> **Nota:** No se admite la coincidencia de grupos de regex `()` con parámetros con nombre. :'\(

## Parámetros Opcionales

Puedes especificar parámetros con nombre que son opcionales para que coincidan al envolver
segmentos entre paréntesis.

```php
Flight::route(
  '/blog(/@anio(/@mes(/@dia)))',
  function(?string $anio, ?string $mes, ?string $dia) {
    // Esto coincidirá con las siguientes URL:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

Cualquier parámetro opcional que no se empareje se pasará como `NULL`.

## Comodines

La coincidencia se realiza solo en segmentos individuales de URL. Si deseas coincidir con múltiples
segmentos, puedes usar el comodín `*`.

```php
Flight::route('/blog/*', function () {
  // Esto coincidirá con /blog/2000/02/01
});
```

Para asignar todas las solicitudes a una sola devolución de llamada, puedes hacerlo así:

```php
Flight::route('*', function () {
  // Haz algo
});
```

## Pasando

Puedes pasar la ejecución a la siguiente ruta coincidente devolviendo `true` desde
tu función de devolución de llamada.

```php
Flight::route('/usuario/@nombre', function (string $nombre) {
  // Comprobar alguna condición
  if ($nombre !== "Juan") {
    // Continuar con la siguiente ruta
    return true;
  }
});

Flight::route('/usuario/*', function () {
  // Esto se llamará
});
```

## Alias de Ruta

Puedes asignar un alias a una ruta, de modo que la URL se pueda generar dinámicamente más tarde en tu código (como una plantilla, por ejemplo).

```php
Flight::route('/usuarios/@id', function($id) { echo 'usuario:'.$id; }, false, 'vista_usuario');

// más adelante en algún lugar del código
Flight::getUrl('vista_usuario', [ 'id' => 5 ]); // devolverá '/usuarios/5'
```

Esto es especialmente útil si tu URL cambia. En el ejemplo anterior, digamos que los usuarios se movieron a `/admin/usuarios/@id` en su lugar.
Con el alias, no tienes que cambiar en ninguna parte donde haces referencia al alias porque el alias ahora devolverá `/admin/usuarios/5` como en el
ejemplo anterior.

Los alias de ruta también funcionan en grupos:

```php
Flight::group('/usuarios', function() {
    Flight::route('/@id', function($id) { echo 'usuario:'.$id; }, false, 'vista_usuario');
});


// más adelante en algún lugar del código
Flight::getUrl('vista_usuario', [ 'id' => 5 ]); // devolverá '/usuarios/5'
```

## Información de Ruta

Si deseas inspeccionar la información de la ruta coincidente, puedes solicitar que se pase el objeto de ruta a tu devolución de llamada pasando `true` como tercer parámetro en
el método de ruta. El objeto de ruta siempre será el último parámetro pasado a tu
función de devolución de llamada.

```php
Flight::route('/', function(\flight\net\Route $ruta) {
  // Array de métodos HTTP emparejados
  $ruta->methods;

  // Array de parámetros con nombre
  $ruta->params;

  // Expresión regular de coincidencia
  $ruta->regex;

  // Contiene el contenido de cualquier '*' utilizado en el patrón de URL
  $ruta->splat;

  // Muestra la ruta de la URL....si realmente lo necesitas
  $ruta->pattern;

  // Muestra cuál middleware está asignado a esto
  $ruta->middleware;

  // Muestra el alias asignado a esta ruta
  $ruta->alias;
}, true);
```

## Agrupación de Rutas

Puede haber momentos en los que desees agrupar rutas relacionadas (como `/api/v1`).
Puedes hacer esto utilizando el método `group`:

```php
Flight::group('/api/v1', function () {
  Flight::route('/usuarios', function () {
	// Coincide con /api/v1/usuarios
  });

  Flight::route('/publicaciones', function () {
	// Coincide con /api/v1/publicaciones
  });
});
```

Incluso puedes anidar grupos de grupos:

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() obtiene variables, ¡no establece una ruta! Ver contexto de objeto abajo
	Flight::route('GET /usuarios', function () {
	  // Coincide con GET /api/v1/usuarios
	});

	Flight::post('/publicaciones', function () {
	  // Coincide con POST /api/v1/publicaciones
	});

	Flight::put('/publicaciones/1', function () {
	  // Coincide con PUT /api/v1/publicaciones
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() obtiene variables, ¡no establece una ruta! Ver contexto de objeto abajo
	Flight::route('GET /usuarios', function () {
	  // Coincide con GET /api/v2/usuarios
	});
  });
});
```

### Agrupación con Contexto de Objeto

Todavía puedes usar la agrupación de rutas con el objeto `Engine` de la siguiente manera:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // usar la variable $router
  $router->get('/usuarios', function () {
	// Coincide con GET /api/v1/usuarios
  });

  $router->post('/publicaciones', function () {
	// Coincide con POST /api/v1/publicaciones
  });
});
```

## Transmisión

Ahora puedes transmitir respuestas al cliente utilizando el método `streamWithHeaders()`.
Esto es útil para enviar archivos grandes, procesos en ejecución prolongada o generar respuestas grandes.
La transmisión de una ruta se maneja un poco diferente que una ruta regular.

> **Nota:** Las respuestas de transmisión solo están disponibles si tienes [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) establecido en false.

```php
Flight::route('/usuarios-transmision', function() {

	// como sea que obtengas tus datos, solo como ejemplo...
	$usuarios_stmt = Flight::db()->query("SELECT id, nombre, apellido FROM usuarios");

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

// Así es como establecerás los encabezados antes de comenzar la transmisión.
})->streamWithHeaders([
	'Tipo de Contenido' => 'aplicación/json',
	// código de estado opcional, por defecto es 200
	'estado' => 200
]);
```