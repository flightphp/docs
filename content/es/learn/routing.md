## Enrutamiento

> **Nota:** ¿Deseas entender más sobre enrutamiento? Echa un vistazo a la página [por qué frameworks](/learn/why-frameworks) para una explicación más detallada.

El enrutamiento básico en Flight se realiza al emparejar un patrón de URL con una función de devolución de llamada o una matriz de una clase y método.

```php
Flight::route('/', function(){
    echo '¡Hola Mundo!';
});
```

La devolución de llamada puede ser cualquier objeto que se pueda llamar. Por lo que puedes usar una función regular:

```php
function hello(){
    echo '¡Hola Mundo!';
}

Flight::route('/', 'hello');
```

O un método de clase:

```php
class Greeting {
    public static function hello() {
        echo '¡Hola Mundo!';
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

Las rutas se emparejan en el orden en el que se definen. La primera ruta que coincida con una solicitud será invocada.

## Enrutamiento de Método

De forma predeterminada, los patrones de ruta se emparejan con todos los métodos de solicitud. Puedes responder
a métodos específicos colocando un identificador antes de la URL.

```php
Flight::route('GET /', function () {
  echo 'Recibí una solicitud GET.';
});

Flight::route('POST /', function () {
  echo 'Recibí una solicitud POST.';
});
```

También puedes asignar múltiples métodos a una única devolución de llamada mediante el uso de un delimitador `|`:

```php
Flight::route('GET|POST /', function () {
  echo 'Recibí una solicitud GET o POST.';
});
```

Además, puedes obtener el objeto Router que tiene algunos métodos auxiliares para que lo uses:

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
  echo "¡Hola, $nombre ($id)!";
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

> **Nota:** No se admite la coincidencia de grupos de regex `()` con parámetros nombrados. :'\(

## Parámetros Opcionales

Puedes especificar parámetros nombrados que son opcionales para emparejar envolviendo
segmentos entre paréntesis.

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $anio, ?string $mes, ?string $dia) {
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

El emparejamiento se realiza solo en segmentos individuales de URL. Si deseas emparejar múltiples
segmentos, puedes usar el comodín `*`.

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

## Pasando

Puedes pasar la ejecución a la siguiente ruta coincidente devolviendo `true` desde
tu función de devolución de llamada.

```php
Flight::route('/usuario/@nombre', function (string $nombre) {
  // Verifica alguna condición
  if ($nombre !== "Bob") {
    // Continuar con la siguiente ruta
    return true;
  }
});

Flight::route('/usuario/*', function () {
  // Esto será llamado
});
```

## Alias de Ruta

Puedes asignar un alias a una ruta, para que la URL pueda generarse dinámicamente más tarde en tu código (como una plantilla, por ejemplo).

```php
Flight::route('/usuarios/@id', function($id) { echo 'usuario:'.$id; }, false, 'vista_usuario');

// más tarde en algún lugar del código
Flight::getUrl('vista_usuario', [ 'id' => 5 ]); // devolverá '/usuarios/5'
```

Esto es especialmente útil si tu URL llegara a cambiar. En el ejemplo anterior, digamos que los usuarios se movieron a `/admin/usuarios/@id` en su lugar.
Con el alias en su lugar, no tendrías que cambiar en ningún lugar donde haces referencia al alias porque el alias ahora devolverá `/admin/usuarios/5` como en el
ejemplo anterior.

La asignación de alias de ruta sigue funcionando en grupos también:

```php
Flight::group('/usuarios', function() {
    Flight::route('/@id', function($id) { echo 'usuario:'.$id; }, false, 'vista_usuario');
});


// más tarde en algún lugar del código
Flight::getUrl('vista_usuario', [ 'id' => 5 ]); // devolverá '/usuarios/5'
```

## Información de Ruta

Si deseas inspeccionar la información de la ruta coincidente, puedes solicitar que se pase el objeto de la ruta a tu devolución de llamada proporcionando `true` como tercer parámetro en
el método de ruta. El objeto de la ruta siempre será el último parámetro pasado a tu
función de devolución de llamada.

```php
Flight::route('/', function(\flight\net\Ruta $ruta) {
  // Matriz de métodos HTTP emparejados
  $ruta->methods;

  // Matriz de parámetros nombrados
  $ruta->params;

  // Expresión regular de coincidencia
  $ruta->regex;

  // Contiene el contenido de cualquier '*' usado en el patrón de URL
  $ruta->splat;

  // Muestra la ruta de la URL....si realmente la necesitas
  $ruta->pattern;

  // Muestra qué middleware está asignado a esto
  $ruta->middleware;

  // Muestra el alias asignado a esta ruta
  $ruta->alias;
}, true);
```

## Agrupación de Rutas

Puede haber momentos en los que desees agrupar rutas relacionadas juntas (como `/api/v1`).
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
	// ¡Flight::get() obtiene variables, no establece una ruta! Ver contexto de objeto a continuación
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

	// ¡Flight::get() obtiene variables, no establece una ruta! Ver contexto de objeto a continuación
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

  // usa la variable $router
  $router->get('/usuarios', function () {
	// Coincide con GET /api/v1/usuarios
  });

  $router->post('/publicaciones', function () {
	// Coincide con POST /api/v1/publicaciones
  });
});
```