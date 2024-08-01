## Enrutamiento

> **Nota:** ¿Quieres entender más sobre el enrutamiento? Consulta la página ["¿Por qué un framework?"](/learn/why-frameworks) para obtener una explicación más detallada.

El enrutamiento básico en Flight se realiza al hacer coincidir un patrón de URL con una función de devolución de llamada o una matriz de una clase y un método.

```php
Flight::route('/', function(){
    echo '¡Hola Mundo!';
});
```

> Las rutas se emparejan en el orden en que se definen. La primera ruta que se empareje con una solicitud será invocada.

### Devoluciones de llamada/Funciones
La devolución de llamada puede ser cualquier objeto que sea invocable. Por lo tanto, puedes usar una función regular:

```php
function hello() {
    echo '¡Hola Mundo!';
}

Flight::route('/', 'hello');
```

### Clases
También puedes usar un método estático de una clase:

```php
class Saludo {
    public static function hello() {
        echo '¡Hola Mundo!';
    }
}

Flight::route('/', [ 'Saludo','hello' ]);
```

O creando un objeto primero y luego llamando al método:

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

Flight::route('/', [ $saludo, 'hello' ]);
// También puedes hacer esto sin crear primero el objeto
// Nota: No se inyectarán argumentos en el constructor
Flight::route('/', [ 'Saludo', 'hello' ]);
// Adicionalmente puedes usar esta sintaxis más corta
Flight::route('/', 'Saludo->hello');
// o
Flight::route('/', Saludo::class.'->hello');
```

#### Inyección de Dependencias a través de DIC (Contenedor de Inyección de Dependencias)
Si deseas utilizar la inyección de dependencias a través de un contenedor (PSR-11, PHP-DI, Dice, etc), el
único tipo de rutas donde esto está disponible es creando directamente el objeto tú mismo
y usando el contenedor para crear tu objeto o puedes usar cadenas para definir la clase y
método a llamar. Puedes ir a la página de [Inyección de Dependencias](/learn/extending) para obtener
más información.

Aquí tienes un ejemplo rápido:

```php

use flight\database\PdoWrapper;

// Greeting.php
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

// Registra el controlador de contenedores
Flight::registerContainerHandler(function($class, $params) use ($dice) {
    return $dice->create($class, $params);
});

// Rutas como de costumbre
Flight::route('/hola/@id', [ 'Saludo', 'hello' ]);
// o
Flight::route('/hola/@id', 'Saludo->hello');
// o
Flight::route('/hola/@id', 'Saludo::hello');

Flight::start();
```

## Enrutamiento por Método

Por defecto, los patrones de rutas coinciden con todos los métodos de solicitud. Puedes responder
a métodos específicos colocando un identificador antes de la URL.

```php
Flight::route('GET /', function () {
  echo 'He recibido una solicitud GET.';
});

Flight::route('POST /', function () {
  echo 'He recibido una solicitud POST.';
});

// No puedes usar Flight::get() para rutas ya que ese es un método
//    para obtener variables, no crear una ruta.
// Flight::post('/', function() { /* código */ });
// Flight::patch('/', function() { /* código */ });
// Flight::put('/', function() { /* código */ });
// Flight::delete('/', function() { /* código */ });
```

También puedes mapear varios métodos a una sola devolución de llamada usando el delimitador `|`:

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

Aunque este método está disponible, se recomienda usar parámetros nombrados, o
parámetros nombrados con expresiones regulares, ya que son más legibles y fáciles de mantener.

## Parámetros Nombrados

Puedes especificar parámetros nombrados en tus rutas que se pasarán
a tu función de devolución de llamada.

```php
Flight::route('/@nombre/@id', function (string $nombre, string $id) {
  echo "¡hola, $nombre ($id)!";
});
```

También puedes incluir expresiones regulares con tus parámetros nombrados mediante
el delimitador `:`:

```php
Flight::route('/@nombre/@id:[0-9]{3}', function (string $nombre, string $id) {
  // Esto coincidirá con /bob/123
  // Pero no coincidirá con /bob/12345
});
```

> **Nota:** No se admite la coincidencia de grupos de expresiones regulares `()` con parámetros nombrados. :'\(

## Parámetros Opcionales

Puedes especificar parámetros nombrados que sean opcionales para la coincidencia envolviendo
segmentos entre paréntesis.

```php
Flight::route(
  '/blog(/@año(/@mes(/@día)))',
  function(?string $año, ?string $mes, ?string $día) {
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

La coincidencia se realiza solo en segmentos individuales de URL. Si deseas coincidir múltiples
segmentos puedes usar el comodín `*`.

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

## Paso

Puedes pasar la ejecución a la siguiente ruta coincidente devolviendo `true` desde
tu función de devolución de llamada.

```php
Flight::route('/usuario/@nombre', function (string $nombre) {
  // Comprobar alguna condición
  if ($nombre !== "Bob") {
    // Continuar con la siguiente ruta
    return true;
  }
});

Flight::route('/usuario/*', function () {
  // Esto se llamará
});
```

## Alias de Ruta

Puedes asignar un alias a una ruta, para que la URL se pueda generar dinámicamente más tarde en tu código (como una plantilla, por ejemplo).

```php
Flight::route('/usuarios/@id', function($id) { echo 'usuario:'.$id; }, false, 'vista_usuario');

// más tarde en algún lugar del código
Flight::getUrl('vista_usuario', [ 'id' => 5 ]); // devolverá '/usuarios/5'
```

Esto es especialmente útil si tu URL cambia. En el ejemplo anterior, supongamos que los usuarios se movieron a `/admin/usuarios/@id` en cambio.
Con el alias en su lugar, no tienes que cambiar en ningún lugar donde hagas referencia al alias, porque el alias ahora devolverá `/admin/usuarios/5` como en el
ejemplo anterior.

El alias de ruta también funciona en grupos:

```php
Flight::group('/usuarios', function() {
    Flight::route('/@id', function($id) { echo 'usuario:'.$id; }, false, 'vista_usuario');
});


// más tarde en algún lugar del código
Flight::getUrl('vista_usuario', [ 'id' => 5 ]); // devolverá '/usuarios/5'
```

## Información de Ruta

Si deseas inspeccionar la información de la ruta coincidente, puedes solicitar que se pase el objeto de ruta a tu devolución de llamada pasando `true` como tercer parámetro en
el método de ruta. El objeto de ruta siempre será el último parámetro pasado a tu
función de devolución de llamada.

```php
Flight::route('/', function(\flight\net\Route $ruta) {
  // Matriz de métodos HTTP que coinciden
  $ruta->methods;

  // Matriz de parámetros nombrados
  $ruta->params;

  // Coincidencia de expresión regular
  $ruta->regex;

  // Contiene el contenido de cualquier '*' utilizado en el patrón de URL
  $ruta->splat;

  // Muestra la ruta de la URL.... si realmente la necesitas
  $ruta->pattern;

  // Muestra qué middleware está asignado a esta
  $ruta->middleware;

  // Muestra el alias asignado a esta ruta
  $ruta->alias;
}, true);
```

## Agrupación de Rutas

Puede haber momentos en los que desees agrupar rutas relacionadas juntas (como `/api/v1`).
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
$app->group('/api/v1', function (Router $enrutador) {

  // usa la variable $enrutador
  $enrutador->get('/usuarios', function () {
    // Coincide con GET /api/v1/usuarios
  });

  $enrutador->post('/publicaciones', function () {
    // Coincide con POST /api/v1/publicaciones
  });
});
```

## Streaming

Ahora puedes transmitir respuestas al cliente utilizando el método `streamWithHeaders()`. 
Esto es útil para enviar archivos grandes, procesos de larga duración o generar respuestas grandes. 
La transmisión de una ruta se maneja de forma un poco diferente a una ruta normal.

> **Nota:** Las respuestas en streaming solo están disponibles si tienes [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) configurado en falso.

### Transmitir con Encabezados Manuales

Puedes transmitir una respuesta al cliente usando el método `stream()` en una ruta. Si lo haces
esto, debes configurar todos los encabezados manualmente antes de enviar cualquier cosa al cliente.
Esto se hace con la función `header()` de PHP o el método `Flight::response()->setRealHeader()`.

```php
Flight::route('/@nombreArchivo', function($nombreArchivo) {

    // obviamente sanitizarías la ruta y lo que sea necesario.
    $nombreArchivoSeguro = basename($nombreArchivo);

    // Si tienes encabezados adicionales para configurar aquí después de que la ruta se haya ejecutado
    // debes definirlos antes de imprimir algo.
    // Todos deben ser una llamada directa a la función header() o
    // una llamada a Flight::response()->setRealHeader()
    header('Content-Disposition: attachment; filename="'.$nombreArchivoSeguro.'"');
    // o
    Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$nombreArchivoSeguro.'"');

    $datosArchivo = file_get_contents('/alguna/ruta/a/archivos/'.$nombreArchivoSeguro);

    // Captura de errores y lo que sea necesario
    if(empty($datosArchivo)) {
        Flight::halt(404, 'Archivo no encontrado');
    }

    // configurar manualmente la longitud del contenido si así lo prefieres
    header('Content-Length: '.filesize($nombreArchivo));

    // Transmite los datos al cliente
    echo $datosArchivo;

// Esta es la línea mágica aquí
})->stream();
```

### Transmitir con Encabezados

También puedes usar el método `streamWithHeaders()` para establecer los encabezados antes de comenzar a transmitir.

```php
Flight::route('/stream-usuarios', function() {

    // puedes agregar cualquier encabezado adicional que desees aquí
    // solo debes usar header() o Flight::response()->setRealHeader()

    // sin embargo extraigas tus datos, solo como ejemplo...
    $usuarios_stmt = Flight::db()->query("SELECT id, nombre, apellido FROM usuarios");

    echo '{';
    $conteoUsuarios = count($usuarios);
    while($usuario = $usuarios_stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode($usuario);
        if(--$conteoUsuarios > 0) {
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
    // código de estado opcional, predeterminado a 200
    'estado' => 200
]);
```