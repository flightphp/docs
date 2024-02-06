# Enrutamiento

El enrutamiento en Flight se realiza al hacer coincidir un patrón de URL con una función de devolución de llamada.

```php
Flight::route('/', function(){
    echo '¡Hola mundo!';
});
```

La devolución de llamada puede ser cualquier objeto que sea invocable. Por lo que puedes usar una función regular:

```php
function hola(){
    echo '¡Hola mundo!';
}

Flight::route('/', 'hola');
```

O un método de clase:

```php
class Saludo {
    public static function hola() {
        echo '¡Hola mundo!';
    }
}

Flight::route('/', array('Saludo','hola'));
```

O un método de objeto:

```php
class Saludo
{
    public function __construct() {
        $this->nombre = 'Juan Pérez';
    }

    public function hola() {
        echo "¡Hola, {$this->nombre}!";
    }
}

$saludo = new Saludo();

Flight::route('/', array($saludo, 'hola'));
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

También puedes asignar múltiples métodos a una única devolución de llamada usando un delimitador `|`:

```php
Flight::route('GET|POST /', function () {
  echo 'Recibí una solicitud GET o POST.';
});
```

## Expresiones Regulares

Puedes usar expresiones regulares en tus rutas:

```php
Flight::route('/usuario/[0-9]+', function () {
  // Esto coincidirá con /usuario/1234
});
```

## Parámetros Nombrados

Puedes especificar parámetros nombrados en tus rutas que se pasarán a
tu función de devolución de llamada.

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

No se admite el emparejamiento de grupos de regex `()` con parámetros nombrados.

## Parámetros Opcionales

Puedes especificar parámetros nombrados que son opcionales para emparejar al envolver
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

Cualquier parámetro opcional que no se empareje se pasará como NULL.

## Comodines

El emparejamiento solo se realiza en segmentos individuales de URL. Si deseas emparejar múltiples
segmentos puedes usar el comodín `*`.

```php
Flight::route('/blog/*', function () {
  // Esto coincidirá con /blog/2000/02/01
});
```

Para enrutarse todas las solicitudes a una devolución de llamada única, puedes hacer:

```php
Flight::route('*', function () {
  // Hacer algo
});
```

## Pasando

Puedes pasar la ejecución a la siguiente ruta coincidente al devolver `true` desde
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

## Información de Ruta

Si deseas inspeccionar la información de la ruta coincidente, puedes solicitar el objeto de ruta
se pase a tu devolución de llamada pasando `true` como tercer parámetro en
el método de ruta. El objeto de ruta siempre será el último parámetro pasado a tu
función de devolución de llamada.

```php
Flight::route('/', function(\flight\net\Route $ruta) {
  // Matriz de métodos HTTP emparejados
  $ruta->metodos;

  // Matriz de parámetros nombrados
  $ruta->params;

  // Expresión regular coincidente
  $ruta->regex;

  // Contiene el contenido de cualquier '*' utilizado en el patrón de URL
  $ruta->splat;
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

  Flight::route('/mensajes', function () {
	// Coincide con /api/v1/mensajes
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

	Flight::post('/mensajes', function () {
	  // Coincide con POST /api/v1/mensajes
	});

	Flight::put('/mensajes/1', function () {
	  // Coincide con PUT /api/v1/mensajes
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
  $router->get('/usuarios', function () {
	// Coincide con GET /api/v1/usuarios
  });

  $router->post('/mensajes', function () {
	// Coincide con POST /api/v1/mensajes
  });
});
```

## Alias de Ruta

Puedes asignar un alias a una ruta, para que la URL pueda generarse dinámicamente más tarde en tu código (como una plantilla, por ejemplo).

```php
Flight::route('/usuarios/@id', function($id) { echo 'usuario:'.$id; }, false, 'vista_usuario');

// más adelante en algún lugar del código
Flight::getUrl('vista_usuario', [ 'id' => 5 ]); // devolverá '/usuarios/5'
```

Esto es especialmente útil si tu URL cambia. En el ejemplo anterior, digamos que los usuarios fueron movidos a `/admin/usuarios/@id` en su lugar.
Con la creación de alias, no tienes que cambiar en ningún lugar donde hagas referencia al alias porque el alias ahora devolverá `/admin/usuarios/5` como en el
ejemplo anterior.

Los alias de ruta también funcionan en grupos:

```php
Flight::group('/usuarios', function() {
    Flight::route('/@id', function($id) { echo 'usuario:'.$id; }, false, 'vista_usuario');
});


// más adelante en algún lugar del código
Flight::getUrl('vista_usuario', [ 'id' => 5 ]); // devolverá '/usuarios/5'
```

## Middleware de Ruta
Flight soporta middleware de ruta y middleware de ruta de grupo. El middleware es una función que se ejecuta antes (o después) de la devolución de llamada de la ruta. Esta es una excelente manera de agregar verificaciones de autenticación de API en tu código, o para validar que el usuario tenga permiso para acceder a la ruta.

Aquí tienes un ejemplo básico:

```php
// Si solo proporcionas una función anónima, se ejecutará antes de la devolución de llamada de la ruta.
// no hay funciones de middleware "después" excepto para las clases (ver abajo)
Flight::route('/ruta', function() { echo '¡Aquí estoy!'; })->addMiddleware(function() {
	echo 'Primer middleware!';
});

Flight::start();

// Esto resultará en "Primer middleware! ¡Aquí estoy!"
```

Hay algunas notas muy importantes sobre el middleware que debes tener en cuenta antes de usarlo:
- Las funciones de middleware se ejecutan en el orden en que se agregan a la ruta. La ejecución es similar a cómo [Slim Framework maneja esto](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Los "Antes" se ejecutan en el orden agregado, y los "Después" se ejecutan en orden inverso.
- Si tu función de middleware devuelve false, se detiene toda la ejecución y se lanza un error 403 Forbidden. Probablemente querrás manejar esto de forma más amable con un `Flight::redirect()` o algo similar.
- Si necesitas parámetros de tu ruta, se pasarán en una única matriz a tu función de middleware. (`function($params) { ... }` o `public function before($params) {}`). La razón de esto es que puedes estructurar tus parámetros en grupos y en algunos de esos grupos, tus parámetros pueden aparecer en un orden diferente que rompería la función de middleware al hacer referencia al parámetro equivocado. De esta manera, puedes acceder a ellos por nombre en lugar de por posición.

### Clases de Middleware

El middleware también se puede registrar como una clase. Si necesitas la funcionalidad "después", debes usar una clase.

```php
class MiMiddleware {
	public function before($params) {
		echo 'Primer middleware!';
	}

	public function after($params) {
		echo 'Último middleware!';
	}
}

$MyMiddleware = new MyMiddleware();
Flight::route('/ruta', function() { echo ' ¡Aquí estoy! '; })->addMiddleware($MyMiddleware); // también ->addMiddleware([ $MiMiddleware, $MiMiddleware2 ]);

Flight::start();

// Esto mostrará "Primer middleware! ¡Aquí estoy! Último middleware!"
```

### Grupos de Middleware

Puedes agregar un grupo de ruta, y luego cada ruta en ese grupo también tendrá el mismo middleware. Esto es útil si necesitas agrupar un montón de rutas, por ejemplo, un middleware Auth para verificar la clave API en la cabecera.

```php

// agregado al final del método de grupo
Flight::group('/api', function() {
    Flight::route('/usuarios', function() { echo 'usuarios'; }, false, 'usuarios');
	Flight::route('/usuarios/@id', function($id) { echo 'usuario:'.$id; }, false, 'vista_usuario');
}, [ new ApiAuthMiddleware() ]);
```