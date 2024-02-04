# Enrutamiento

El enrutamiento en Flight se realiza al coincidir un patrón de URL con una función de devolución de llamada.

```php
Flight::route('/', function(){
    echo '¡Hola mundo!';
});
```

La devolución de llamada puede ser cualquier objeto que sea callable. Entonces puedes usar una función regular:

```php
function hello(){
    echo '¡Hola mundo!';
}

Flight::route('/', 'hello');
```

O un método de clase:

```php
class Greeting {
    public static function hello() {
        echo '¡Hola mundo!';
    }
}

Flight::route('/', array('Greeting','hello'));
```

O un método de objeto:

```php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "¡Hola, {$this->name}!";
    }
}

$greeting = new Greeting();

Flight::route('/', array($greeting, 'hello'));
```

Las rutas se emparejan en el orden en que se definen. La primera ruta que coincida con una solicitud será invocada.

## Enrutamiento de Método

De forma predeterminada, los patrones de ruta se emparejan con todos los métodos de solicitud. Puedes responder a métodos específicos colocando un identificador antes de la URL.

```php
Flight::route('GET /', function () {
  echo 'Recibí una solicitud GET.';
});

Flight::route('POST /', function () {
  echo 'Recibí una solicitud POST.';
});
```

También puedes asignar varios métodos a una única devolución de llamada mediante un delimitador `|`:

```php
Flight::route('GET|POST /', function () {
  echo 'Recibí una solicitud ya sea GET o POST.';
});
```

## Expresiones Regulares

Puedes utilizar expresiones regulares en tus rutas:

```php
Flight::route('/usuario/[0-9]+', function () {
  // Esto coincidirá con /usuario/1234
});
```

## Parámetros con Nombre

Puedes especificar parámetros con nombre en tus rutas que se pasarán a la función de devolución de llamada.

```php
Flight::route('/@nombre/@id', function (string $nombre, string $id) {
  echo "¡Hola, $nombre ($id)!";
});
```

También puedes incluir expresiones regulares con tus parámetros con nombre mediante el delimitador `:`:

```php
Flight::route('/@nombre/@id:[0-9]{3}', function (string $nombre, string $id) {
  // Esto coincidirá con /bob/123
  // Pero no coincidirá con /bob/12345
});
```

No se admite el emparejamiento de grupos de regex `()` con parámetros con nombre.

## Parámetros Opcionales

Puedes especificar parámetros con nombre que son opcionales para el emparejamiento al envolver
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

Cualquier parámetro opcional que no coincida se pasará como NULL.

## Comodines

El emparejamiento solo se realiza en segmentos individuales de URL. Si deseas emparejar varios
segmentos, puedes usar el comodín `*`.

```php
Flight::route('/blog/*', function () {
  // Esto coincidirá con /blog/2000/02/01
});
```

Para enrutar todas las solicitudes a una única devolución de llamada, puedes hacer:

```php
Flight::route('*', function () {
  // Haz algo
});
```

## Pasando

Puedes pasar la ejecución a la siguiente ruta coincidente al devolver `true` desde
tu función de devolución de llamada.

```php
Flight::route('/usuario/@nombre', function (string $nombre) {
  // Verificar alguna condición
  if ($nombre !== "Bob") {
    // Continuar con la siguiente ruta
    return true;
  }
});

Flight::route('/usuario/*', function () {
  // Esto se llamará
});
```

## Información de Ruta

Si deseas inspeccionar la información de la ruta coincidente, puedes solicitar que la ruta
objeto se pase a tu devolución de llamada al pasar `true` como tercer parámetro en
el método de ruta. El objeto de ruta siempre será el último parámetro pasado a tu
función de devolución de llamada.

```php
Flight::route('/', function(\flight\net\Route $ruta) {
  // Matriz de métodos HTTP emparejados
  $ruta->methods;

  // Matriz de parámetros con nombre
  $ruta->params;

  // Expresión regular coincidente
  $ruta->regex;

  // Contiene el contenido de cualquier '*' utilizado en el patrón de URL
  $ruta->splat;
}, true);
```

## Agrupamiento de Rutas

Puede haber momentos en los que desees agrupar rutas relacionadas juntas (como `/api/v1`).
Puedes hacerlo usando el método `group`:

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
	// Flight::get() obtiene variables, ¡no define una ruta! Ver contexto del objeto a continuación
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

	// Flight::get() obtiene variables, ¡no define una ruta! Ver contexto del objeto a continuación
	Flight::route('GET /usuarios', function () {
	  // Coincide con GET /api/v2/usuarios
	});
  });
});
```

### Agrupamiento con Contexto de Objeto

Todavía puedes usar el agrupamiento de rutas con el objeto `Engine` de la siguiente manera:

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $enrutador) {
  $enrutador->get('/usuarios', function () {
	// Coincide con GET /api/v1/usuarios
  });

  $enrutador->post('/publicaciones', function () {
	// Coincide con POST /api/v1/publicaciones
  });
});
```

## Alias de Ruta

Puedes asignar un alias a una ruta, para que la URL pueda generarse dinámicamente más tarde en tu código (como una plantilla, por ejemplo).

```php
Flight::route('/usuarios/@id', function($id) { echo 'usuario:'.$id; }, false, 'vista_usuario');

// más tarde en algún lugar del código
Flight::getUrl('vista_usuario', [ 'id' => 5 ]); // devolverá '/usuarios/5'
```

Esto es especialmente útil si tu URL llegara a cambiar. En el ejemplo anterior, supongamos que los usuarios se movieron a `/admin/usuarios/@id` en su lugar.
Con el aliasado en su lugar, no necesitas cambiar en ningún lugar en el que haces referencia al alias porque el alias ahora devolverá `/admin/usuarios/5` como en el
ejemplo anterior.

El aliasado de rutas aún funciona en grupos también:

```php
Flight::group('/usuarios', function() {
    Flight::route('/@id', function($id) { echo 'usuario:'.$id; }, false, 'vista_usuario');
});


// más tarde en algún lugar del código
Flight::getUrl('vista_usuario', [ 'id' => 5 ]); // devolverá '/usuarios/5'
```

## Middleware de Ruta
Flight admite middleware de ruta y middleware de ruta en grupo. El middleware es una función que se ejecuta antes (o después) de la devolución de llamada de la ruta. Esta es una excelente manera de agregar verificaciones de autenticación API en tu código o validar que el usuario tenga permiso para acceder a la ruta.

Aquí tienes un ejemplo básico:

```php
// Si solo proporcionas una función anónima, se ejecutará antes de la devolución de llamada de la ruta.
// No hay funciones de middleware "after" excepto para clases (ver abajo)
Flight::route('/ruta', function() { echo ' ¡Aquí estoy!'; })->addMiddleware(function() {
	echo '¡Middleware primero!';
});

Flight::start();

// Esto imprimirá "¡Middleware primero! ¡Aquí estoy!"
```

Hay algunas notas muy importantes sobre el middleware que debes tener en cuenta antes de usarlo:
- Las funciones de middleware se ejecutan en el orden en que se agregan a la ruta. La ejecución es similar a cómo [Slim Framework maneja esto](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Los “befores” se ejecutan en el orden agregado, y los “afters” se ejecutan en orden inverso.
- Si tu función de middleware devuelve false, se detiene toda la ejecución y se lanza un error 403 Forbidden. Probablemente querrás manejar esto de forma más elegante con una `Flight::redirect()` o algo similar.
- Si necesitas parámetros de tu ruta, se pasarán en un solo array a tu función de middleware. (`function($params) { ... }` o `public function before($params) {}`). La razón de esto es que puedes estructurar tus parámetros en grupos y en alguno de esos grupos, tus parámetros pueden aparecer en un orden diferente que rompería la función del middleware al referirse al parámetro incorrecto. De esta manera, puedes acceder a ellos por nombre en lugar de posición.

### Clases de Middleware

El middleware también se puede registrar como una clase. Si necesitas la funcionalidad "after", debes usar una clase.

```php
class MiMiddleware {
	public function before($params) {
		echo '¡Middleware primero!';
	}

	public function after($params) {
		echo '¡Middleware último!';
	}
}

$MiMiddleware = new MiMiddleware();
Flight::route('/ruta', function() { echo ' ¡Aquí estoy! '; })->addMiddleware($MiMiddleware); // también ->addMiddleware([ $MiMiddleware, $MiMiddleware2 ]);

Flight::start();

// Esto mostrará "¡Middleware primero! ¡Aquí estoy! ¡Middleware último!"
```

### Grupos de Middleware

Puedes agregar un grupo de rutas y luego cada ruta en ese grupo tendrá el mismo middleware también. Esto es útil si necesitas agrupar un montón de rutas por ejemplo con un middleware de Auth para verificar la clave de la API en el encabezado.

```php

// añadido al final del método group
Flight::group('/api', function() {
    Flight::route('/usuarios', function() { echo 'usuarios'; }, false, 'usuarios');
	Flight::route('/usuarios/@id', function($id) { echo 'usuario:'.$id; }, false, 'vista_usuario');
}, [ new MiddlewareDeAutenticacionApi() ]);
```