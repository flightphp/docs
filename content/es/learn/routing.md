```es
# Enrutamiento

> **Nota:** ¿Quieres entender más sobre el enrutamiento? Consulta la página ["¿por qué un framework?"](/learn/why-frameworks) para obtener una explicación más detallada.

El enrutamiento básico en Flight se realiza mediante la coincidencia de un patrón de URL con una función de devolución de llamada o un conjunto de una clase y un método.

```php
Flight::route('/', function(){
    echo '¡Hola Mundo!';
});
```

> Las rutas se comparan en el orden en que se definen. La primera ruta que coincida con una solicitud será invocada.

### Devoluciones de Llamada/Funciones
La devolución de llamada puede ser cualquier objeto que sea invocable. Así que puedes usar una función regular:

```php
function hola(){
    echo '¡Hola Mundo!';
}

Flight::route('/', 'hola');
```

### Clases
También puedes usar un método estático de una clase:

```php
class Saludo {
    public static function hola() {
        echo '¡Hola Mundo!';
    }
}

Flight::route('/', [ 'Saludo','hola' ]);
```

O creando un objeto primero y luego llamando al método:

```php

// Saludo.php
class Saludo
{
    public function __construct() {
        $this->nombre = 'John Doe';
    }

    public function hola() {
        echo "¡Hola, {$this->nombre}!";
    }
}

// index.php
$saludo = new Saludo();

Flight::route('/', [ $saludo, 'hola' ]);
// También puedes hacerlo sin crear el objeto primero
// Nota: No se inyectarán argumentos en el constructor
Flight::route('/', [ 'Saludo', 'hola' ]);
```

#### Inyección de Dependencias a través de DIC (Contenedor de Inyección de Dependencias)
Si deseas utilizar la inyección de dependencias a través de un contenedor (PSR-11, PHP-DI, Dice, etc), el único tipo de rutas donde está disponible es creando el objeto directamente tú mismo
y usando el contenedor para crear tu objeto o puedes usar cadenas para definir la clase y el método a llamar. Puedes ir a la página [Inyección de Dependencias](/learn/extending) para obtener más información.

Aquí tienes un ejemplo rápido:

```php

use flight\database\PdoWrapper;

// Saludo.php
class Saludo
{
	protected PdoWrapper $pdoWrapper;
	public function __construct(PdoWrapper $pdoWrapper) {
		$this->pdoWrapper = $pdoWrapper;
	}

	public function hello(int $id) {
		// hacer algo con $this->pdoWrapper
		$nombre = $this->pdoWrapper->fetchField("SELECT nombre FROM usuarios WHERE id = ?", [ $id ]);
		echo "¡Hola, mundo! Mi nombre es {$nombre}!";
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
		'password'
	]
]);

// Registra el manejador de contenedores
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
});

// Rutas como de costumbre
Flight::route('/hola/@id', [ 'Saludo', 'hola' ]);
// o
Flight::route('/hola/@id', 'Saludo->hola');
// o
Flight::route('/hola/@id', 'Saludo::hola');

Flight::start();
```

## Enrutamiento de Métodos

Por defecto, los patrones de ruta se comparan con todos los métodos de solicitud. Puedes responder a métodos específicos colocando un identificador antes de la URL.

```php
Flight::route('GET /', function () {
  echo 'He recibido una solicitud GET.';
});

Flight::route('POST /', function () {
  echo 'He recibido una solicitud POST.';
});

// No puedes usar Flight::get() para rutas ya que es un método
//    para obtener variables, no crear una ruta.
// Flight::post('/', function() { /* código */ });
// Flight::patch('/', function() { /* código */ });
// Flight::put('/', function() { /* código */ });
// Flight::delete('/', function() { /* código */ });
```

También puedes asignar múltiples métodos a una sola devolución de llamada mediante el uso de un delimitador `|`:

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

Aunque este método está disponible, se recomienda usar parámetros nombrados o
parámetros nombrados con expresiones regulares, ya que son más legibles y fáciles de mantener.

## Parámetros Nombrados

Puedes especificar parámetros nombrados en tus rutas que se pasarán
a tu función de devolución de llamada.

```php
Flight::route('/@nombre/@id', function (string $nombre, string $id) {
  echo "¡hola, $nombre ($id)!";
});
```

También puedes incluir expresiones regulares con tus parámetros nombrados mediante el uso
del delimitador `:`:

```php
Flight::route('/@nombre/@id:[0-9]{3}', function (string $nombre, string $id) {
  // Esto coincidirá con /bob/123
  // Pero no coincidirá con /bob/12345
});
```

> **Nota:** No se admite la coincidencia de grupos de regex `()` con parámetros nombrados. :'\(

## Parámetros Opcionales

Puedes especificar parámetros nombrados que son opcionales para que coincidan al envolver
segmentos en paréntesis.

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

La coincidencia se hace solo en segmentos individuales de URL. Si deseas hacer coincidir múltiples
segmentos puedes usar el comodín `*`.

```php
Flight::route('/blog/*', function () {
  // Esto coincidirá con /blog/2000/02/01
});
```

Para dirigir todas las solicitudes a una única devolución de llamada, puedes hacer:

```php
Flight::route('*', function () {
  // Haz algo
});
```

## Pasar

Puedes pasar la ejecución a la siguiente ruta coincidente devolviendo `true` desde
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

## Alias de Ruta

Puedes asignar un alias a una ruta, de modo que la URL pueda generarse dinámicamente más tarde en tu código (como una plantilla, por ejemplo).

```php
Flight::route('/usuarios/@id', function($id) { echo 'usuario:'.$id; }, false, 'vista_usuario');

// más tarde en algún lugar del código
Flight::getUrl('vista_usuario', [ 'id' => 5 ]); // devolverá '/usuarios/5'
```

Esto es especialmente útil si tu URL cambia. En el ejemplo anterior, digamos que los usuarios se movieron a `/admin/usuarios/@id` en lugar de eso.
Con el alias en su lugar, no tienes que cambiar en ninguna parte que haga referencia al alias porque el alias ahora devolverá `/admin/usuarios/5` como en el
ejemplo anterior.

El alias de ruta todavía funciona en grupos también:

```php
Flight::group('/usuarios', function() {
    Flight::route('/@id', function($id) { echo 'usuario:'.$id; }, false, 'vista_usuario');
});


// más tarde en algún lugar del código
Flight::getUrl('vista_usuario', [ 'id' => 5 ]); // devolverá '/usuarios/5'
```

## Información de Ruta

Si deseas inspeccionar la información de ruta coincidente, puedes solicitar que se pase el objeto de ruta a tu devolución de llamada pasando `true` como el tercer parámetro en
el método de ruta. El objeto de ruta siempre será el último parámetro pasado a la función de devolución de llamada.

```php
Flight::route('/', function(\flight\net\Route $ruta) {
  // Array de métodos HTTP coincidentes
  $ruta->métodos;

  // Array de parámetros nombrados
  $ruta->params;

  // Expresión regular coincidente
  $ruta->regex;

  // Contiene el contenido de cualquier '*' utilizado en el patrón de URL
  $ruta->splat;

  // Muestra la ruta de la url....si realmente la necesitas
  $ruta->patrón;

  // Muestra qué middleware está asignado a esto
  $ruta->middleware;

  // Muestra el alias asignado a esta ruta
  $ruta->alias;
}, true);
```

## Agrupación de Rutas

Puede haber momentos en los que quieras agrupar rutas relacionadas (como `/api/v1`).
Puedes hacer esto usando el método `group`:

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

### Agrupar con Contexto de Objeto

Todavía puedes usar la agrupación de rutas con el objeto `Engine` de la siguiente manera:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // utiliza la variable $router
  $router->get('/usuarios', function () {
	// Coincide con GET /api/v1/usuarios
  });

  $router->post('/publicaciones', function () {
	// Coincide con POST /api/v1/publicaciones
  });
});
```

## Streaming

Ahora puedes transmitir respuestas al cliente usando el método `streamWithHeaders()`.
Esto es útil para enviar archivos grandes, procesos largos o generar respuestas grandes.
El enrutamiento de un flujo se maneja un poco diferente que una ruta regular.

> **Nota:** Las respuestas de transmisión solo están disponibles si tienes [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) configurado en false.

### Transmitir con Encabezados Manuales

Puedes transmitir una respuesta al cliente usando el método `stream()` en una ruta. Si
haces esto, debes configurar todos los métodos manualmente antes de enviar cualquier cosa al cliente.
Esto se hace con la función `header()` de php o el método `Flight::response()->setRealHeader()`.

```php
Flight::route('/@nombre_archivo', function($nombre_archivo) {

	// obviamente deberías sanitizar la ruta y otras cosas.
	$nombre_archivo_seguro = basename($nombre_archivo);

	// Si tienes encabezados adicionales que configurar aquí después de que se haya ejecutado la ruta
	// debes definirlos antes de que se imprima cualquier cosa.
	// Todos deben ser una llamada en bruto a la función header() o
	// una llamada a Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$nombre_archivo_seguro.'"');
	// o
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$nombre_archivo_seguro.'"');

	$datos_archivo = file_get_contents('/alguna/ruta/a/archivos/'.$nombre_archivo_seguro);

	// Captura de errores y demás
	if(empty($datos_archivo)) {
		Flight::halt(404, 'Archivo no encontrado');
	}

	// configura manualmente la longitud del contenido si lo deseas
	header('Content-Length: '.filesize($nombre_archivo));
	
	// Transmite los datos al cliente
	echo $datos_archivo;

// Esta es la línea mágica aquí
})->stream();
```

### Transmitir con Encabezados

También puedes usar el método `streamWithHeaders()` para configurar los encabezados antes de comenzar a transmitir.

```php
Flight::route('/transmitir-usuarios', function() {

	// puedes agregar cualquier encabezado adicional que desees aquí
	// solo debes usar header() o Flight::response()->setRealHeader()

	// de cualquier manera extraigas tus datos, solo como ejemplo...
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

// Así es como configurarás los encabezados antes de comenzar a transmitir.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="usuarios.json"',
	// código de estado opcional, por defecto 200
	'estado' => 200
]);
```