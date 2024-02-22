# Seguridad

La seguridad es fundamental cuando se trata de aplicaciones web. Quieres asegurarte de que tu aplicación sea segura y de que los datos de tus usuarios estén protegidos. Flight proporciona varias funciones para ayudarte a asegurar tus aplicaciones web.

## Encabezados

Los encabezados HTTP son una de las formas más sencillas de asegurar tus aplicaciones web. Puedes utilizar los encabezados para prevenir el secuestro de clics, XSS y otros ataques. Hay varias formas en las que puedes agregar estos encabezados a tu aplicación.

Dos excelentes sitios web para verificar la seguridad de tus encabezados son [securityheaders.com](https://securityheaders.com/) y [observatory.mozilla.org](https://observatory.mozilla.org/).

### Agregar manualmente

Puedes agregar manualmente estos encabezados usando el método `header` en el objeto `Flight\Response`.
```php
// Establecer el encabezado X-Frame-Options para prevenir el secuestro de clics
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Establecer el encabezado Content-Security-Policy para prevenir XSS
// Nota: este encabezado puede volverse muy complejo, por lo que debes
// consultar ejemplos en Internet para tu aplicación
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Establecer el encabezado X-XSS-Protection para prevenir XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Establecer el encabezado X-Content-Type-Options para prevenir la detección de tipo MIME
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Establecer el encabezado Referrer-Policy para controlar cuánta información del referente se envía
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Establecer el encabezado Strict-Transport-Security para forzar HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Establecer el encabezado Permissions-Policy para controlar qué funciones y APIs pueden usarse
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Estos pueden agregarse al principio de tus archivos `bootstrap.php` o `index.php`.

### Agregar como un Filtro

También puedes agregarlos en un filtro/gancho como sigue:

```php
// Agregar los encabezados en un filtro
Flight::before('start', function() {
	Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
	Flight::response()->header("Content-Security-Policy", "default-src 'self'");
	Flight::response()->header('X-XSS-Protection', '1; mode=block');
	Flight::response()->header('X-Content-Type-Options', 'nosniff');
	Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
	Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
	Flight::response()->header('Permissions-Policy', 'geolocation=()');
});
```

### Agregar como un Middleware

También puedes agregarlos como una clase de middleware. Esta es una buena manera de mantener tu código limpio y organizado.

```php
// app/middleware/SecurityHeadersMiddleware.php

namespace app\middleware;

class SecurityHeadersMiddleware
{
	public function before(array $params): void
	{
		Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
		Flight::response()->header("Content-Security-Policy", "default-src 'self'");
		Flight::response()->header('X-XSS-Protection', '1; mode=block');
		Flight::response()->header('X-Content-Type-Options', 'nosniff');
		Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
		Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		Flight::response()->header('Permissions-Policy', 'geolocation=()');
	}
}

// index.php o donde tengas tus rutas
// Para tu información, este grupo de cadena vacía actúa como un middleware global para
// todas las rutas. Por supuesto, podrías hacer lo mismo y agregar esto solo a rutas específicas.
Flight::group('', function(Router $router) {
	$router->get('/usuarios', [ 'ControladorUsuario', 'obtenerUsuarios' ]);
	// más rutas
}, [ new SecurityHeadersMiddleware() ]);
```


## Falsificación de Solicitud entre Sitios (CSRF)

La Falsificación de Solicitud entre Sitios (CSRF) es un tipo de ataque en el que un sitio web malicioso puede hacer que el navegador de un usuario envíe una solicitud a tu sitio web. Esto se puede utilizar para realizar acciones en tu sitio web sin el conocimiento del usuario. Flight no proporciona un mecanismo de protección CSRF integrado, pero fácilmente puedes implementar el tuyo propio utilizando un middleware.

### Configuración

Primero necesitas generar un token CSRF y almacenarlo en la sesión del usuario. Luego puedes usar este token en tus formularios y verificarlo cuando se envía el formulario.

```php
// Generar un token CSRF y almacenarlo en la sesión del usuario
// (asumiendo que has creado un objeto de sesión y lo has adjuntado a Flight)
// Solo necesitas generar un único token por sesión (así funciona
// en múltiples pestañas y solicitudes para el mismo usuario)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- Utilizar el token CSRF en tu formulario -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- otros campos del formulario -->
</form>
```

#### Utilizando Latte

También puedes definir una función personalizada para mostrar el token CSRF en tus plantillas de Latte.

```php
// Definir una función personalizada para mostrar el token CSRF
// Nota: se ha configurado View con Latte como motor de vista
Flight::view()->addFunction('csrf', function() {
	$tokenCSRF = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $tokenCSRF . '">');
});
```

Y ahora en tus plantillas de Latte puedes utilizar la función `csrf()` para mostrar el token CSRF.

```html
<form method="post">
	{csrf()}
	<!-- otros campos del formulario -->
</form>
```

¿Corto y simple, verdad?

### Verificar el Token CSRF

Puedes verificar el token CSRF usando filtros de eventos:

```php
// Este middleware verifica si la solicitud es una solicitud POST y si lo es, verifica si el token CSRF es válido
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// capturar el token CSRF de los valores del formulario
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Token CSRF inválido');
		}
	}
});
```

O puedes usar una clase de middleware:

```php
// app/middleware/AutenticacionCsrf.php

namespace app\middleware;

class AutenticacionCsrf
{
	public function before(array $params): void
	{
		if(Flight::request()->method == 'POST') {
			$token = Flight::request()->data->csrf_token;
			if($token !== Flight::session()->get('csrf_token')) {
				Flight::halt(403, 'Token CSRF inválido');
			}
		}
	}
}

// index.php o donde tengas tus rutas
Flight::group('', function(Router $router) {
	$router->get('/usuarios', [ 'ControladorUsuario', 'obtenerUsuarios' ]);
	// más rutas
}, [ new AutenticacionCsrf() ]);
```

## Secuencias de Comandos entre Sitios (XSS)

Las Secuencias de Comandos entre Sitios (XSS) es un tipo de ataque en el que un sitio web malicioso puede inyectar código en tu sitio web. La mayoría de estas oportunidades provienen de los valores de los formularios que tus usuarios completarán. ¡Nunca! debes confiar en la entrada de tus usuarios. Siempre debes asumir que todos son los mejores hackers del mundo. Pueden inyectar JavaScript o HTML malicioso en tu página. Este código se puede utilizar para robar información de tus usuarios o realizar acciones en tu sitio web. Utilizando la clase de vista de Flight, puedes escapar fácilmente la salida para prevenir ataques XSS.

```php
// Vamos a suponer que el usuario es astuto e intenta usar esto como su nombre
$nombre = '<script>alert("XSS")</script>';

// Esto escapará la salida
Flight::view()->set('nombre', $nombre);
// Esto producirá: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Si usas algo como Latte registrado como tu clase de vista, esto también se escapará automáticamente.
Flight::view()->render('plantilla', ['nombre' => $nombre]);
```

## Inyección SQL

La Inyección SQL es un tipo de ataque en el que un usuario malintencionado puede inyectar código SQL en tu base de datos. Esto se puede utilizar para robar información de tu base de datos o realizar acciones en tu base de datos. ¡Nunca! debes confiar en la entrada de tus usuarios. Siempre asume que van a por sangre. Puedes utilizar declaraciones preparadas en tus objetos `PDO` para prevenir la inyección SQL.

```php
// Suponiendo que tienes Flight::db() registrado como tu objeto PDO
$declaracion = Flight::db()->prepare('SELECT * FROM usuarios WHERE nombre_usuario = :nombre_usuario');
$declaracion->execute([':nombre_usuario' => $nombre_usuario]);
$usuarios = $declaracion->fetchAll();

// Si usas la clase PdoWrapper, esto se puede hacer fácilmente en una sola línea
$usuarios = Flight::db()->fetchAll('SELECT * FROM usuarios WHERE nombre_usuario = :nombre_usuario', [ 'nombre_usuario' => $nombre_usuario ]);

// Puedes hacer lo mismo con un objeto PDO usando marcadores de posición ?
$declaracion = Flight::db()->fetchAll('SELECT * FROM usuarios WHERE nombre_usuario = ?', [ $nombre_usuario ]);

// Solo promete que nunca, jamás hagas algo como esto...
$usuarios = Flight::db()->fetchAll("SELECT * FROM usuarios WHERE nombre_usuario = '{$nombre_usuario}' LIMIT 5");
// porque ¿qué pasaría si $nombre_usuario = "' OR 1=1; -- "; 
// Después de armar la consulta se vería así
// SELECT * FROM usuarios WHERE nombre_usuario = '' OR 1=1; -- LIMIT 5
// Parece extraño, pero es una consulta válida que funcionará. De hecho,
// es un ataque de inyección SQL muy común que devolverá todos los usuarios.
```

## CORS

El Intercambio de Recursos de Origen Cruzado (CORS) es un mecanismo que permite que muchos recursos (por ejemplo, fuentes, JavaScript, etc.) en una página web se soliciten desde otro dominio fuera del dominio del cual se originó el recurso. Flight no tiene funcionalidad incorporada, pero esto se puede manejar fácilmente con middleware o filtros de eventos similares a CSRF.

```php
// app/middleware/MiddlewareCors.php

namespace app\middleware;

class MiddlewareCors
{
	public function before(array $params): void
	{
		$response = Flight::response();
		if (isset($_SERVER['HTTP_ORIGIN'])) {
			$this->permitirOrigen();
			$response->header('Access-Control-Allow-Credentials: true');
			$response->header('Access-Control-Max-Age: 86400');
		}

		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
				$response->header(
					'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS'
				);
			}
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
				$response->header(
					"Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}"
				);
			}
			$response->send();
			exit(0);
		}
	}

	private function permitirOrigen(): void
	{
		// personaliza tus hosts permitidos aquí.
		$permitidos = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		if (in_array($_SERVER['HTTP_ORIGIN'], $permitidos)) {
			$response = Flight::response();
			$response->header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		}
	}
}

// index.php o donde tengas tus rutas
Flight::route('/usuarios', function() {
	$usuarios = Flight::db()->fetchAll('SELECT * FROM usuarios');
	Flight::json($usuarios);
})->addMiddleware(new MiddlewareCors());
```

## Conclusión

La seguridad es fundamental y es importante asegurarte de que tus aplicaciones web sean seguras. Flight proporciona varias funciones para ayudarte a asegurar tus aplicaciones web, pero es importante estar siempre atento y asegurarte de hacer todo lo posible para mantener seguros los datos de tus usuarios. Siempre asume lo peor y nunca confíes en la entrada de tus usuarios. Siempre escapa la salida y utiliza declaraciones preparadas para prevenir la inyección SQL. Utiliza middleware para proteger tus rutas de ataques CSRF y CORS. Si haces todas estas cosas, estarás en buen camino para crear aplicaciones web seguras.