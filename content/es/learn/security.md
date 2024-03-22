# Seguridad

La seguridad es muy importante cuando se trata de aplicaciones web. Quieres asegurarte de que tu aplicación sea segura y de que los datos de tus usuarios estén protegidos. Flight proporciona una serie de características para ayudarte a asegurar tus aplicaciones web.

## Cabeceras

Las cabeceras de HTTP son una de las formas más fáciles de proteger tus aplicaciones web. Puedes utilizar cabeceras para evitar el secuestro de clics, XSS y otros ataques. Hay varias formas de agregar estas cabeceras a tu aplicación.

Dos excelentes sitios web para verificar la seguridad de tus cabeceras son [securityheaders.com](https://securityheaders.com/) y [observatory.mozilla.org](https://observatory.mozilla.org/).

### Agregar Manualmente

Puedes agregar manualmente estas cabeceras utilizando el método `header` en el objeto `Flight\Response`.
```php
// Configura la cabecera X-Frame-Options para evitar el secuestro de clics
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Configura la cabecera Content-Security-Policy para evitar XSS
// Nota: esta cabecera puede volverse muy compleja, así que es mejor
//  consultar ejemplos en internet para tu aplicación
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Configura la cabecera X-XSS-Protection para evitar XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Configura la cabecera X-Content-Type-Options para evitar el sniffing de tipo MIME
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Configura la cabecera Referrer-Policy para controlar cuánta información de referencia se envía
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Configura la cabecera Strict-Transport-Security para forzar HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Configura la cabecera Permissions-Policy para controlar qué características y APIs se pueden utilizar
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Estas pueden agregarse al principio de tus archivos `bootstrap.php` o `index.php`.

### Agregar como Filtro

También puedes agregarlas en un filtro/gancho de la siguiente manera:

```php
// Agrega las cabeceras en un filtro
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

### Agregar como Middleware

También puedes agregarlas como una clase middleware. Esta es una buena manera de mantener tu código limpio y organizado.

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
// FYI, este grupo de cadena vacía actúa como un middleware global para
// todas las rutas. Por supuesto, podrías hacer lo mismo y agregar
// esto solo a rutas específicas.
Flight::group('', function(Router $router) {
	$router->get('/usuarios', [ 'ControladorUsuario', 'obtenerUsuarios' ]);
	// más rutas
}, [ new SecurityHeadersMiddleware() ]);
```

## Falsificación de Petición en Sitios Cruzados (CSRF)

La falsificación de petición en sitios cruzados (CSRF) es un tipo de ataque donde un sitio web malicioso puede hacer que el navegador de un usuario envíe una solicitud a tu sitio web. Esto se puede utilizar para realizar acciones en tu sitio web sin el conocimiento del usuario. Flight no proporciona un mecanismo de protección CSRF integrado, pero fácilmente puedes implementar el tuyo usando middleware.

### Configuración

Primero necesitas generar un token CSRF y almacenarlo en la sesión del usuario. Luego puedes utilizar este token en tus formularios y verificarlo cuando se envíe el formulario.

```php
// Genera un token CSRF y guárdalo en la sesión del usuario
// (asumiendo que has creado un objeto de sesión y lo has adjuntado a Flight)
// Solo necesitas generar un único token por sesión (así funciona
// en múltiples pestañas y solicitudes para el mismo usuario)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- Utiliza el token CSRF en tu formulario -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- otros campos del formulario -->
</form>
```

#### Usando Latte

También puedes configurar una función personalizada para mostrar el token CSRF en tus plantillas de Latte.

```php
// Configura una función personalizada para mostrar el token CSRF
// Nota: La vista se ha configurado con Latte como motor de vista
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

Y ahora en tus plantillas de Latte puedes usar la función `csrf()` para mostrar el token CSRF.

```html
<form method="post">
	{csrf()}
	<!-- otros campos del formulario -->
</form>
```

¡Corto y simple, ¿verdad?

### Verificar el Token CSRF

Puedes verificar el token CSRF usando filtros de eventos:

```php
// Este middleware verifica si la solicitud es una solicitud POST y, si lo es, verifica si el token CSRF es válido
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// captura el token csrf de los valores del formulario
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Token CSRF inválido');
		}
	}
});
```

O puedes usar una clase middleware:

```php
// app/middleware/CsrfMiddleware.php

namespace app\middleware;

class CsrfMiddleware
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
}, [ new CsrfMiddleware() ]);
```

## Secuencias de Comandos entre Sitios (XSS)

La secuencia de comandos entre sitios (XSS) es un tipo de ataque donde un sitio web malicioso puede inyectar código en tu sitio web. La mayoría de estas oportunidades provienen de los valores de los formularios que completarán tus usuarios. ¡Nunca debes confiar en la salida de tus usuarios! Siempre asume que todos ellos son los mejores hackers del mundo. Pueden inyectar JavaScript malicioso o HTML en tu página. Este código se puede utilizar para robar información de tus usuarios o realizar acciones en tu sitio web. Utilizando la clase de vista de Flight, puedes escapar fácilmente la salida para evitar ataques XSS.

```php
// Vamos a suponer que el usuario es astuto e intenta utilizar esto como su nombre
$nombre = '<script>alert("XSS")</script>';

// Esto escapará la salida
Flight::view()->set('nombre', $nombre);
// Esto mostrará: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Si utilizas algo como Latte registrado como tu clase de vista, esto también se escapará automáticamente.
Flight::view()->render('plantilla', ['nombre' => $nombre]);
```

## Inyección de SQL

La inyección de SQL es un tipo de ataque donde un usuario malicioso puede inyectar código SQL en tu base de datos. Esto se puede utilizar para robar información de tu base de datos o realizar acciones en tu base de datos. Nuevamente, ¡nunca debes confiar en la entrada de tus usuarios! Siempre asume que están detrás de ti. Puedes utilizar declaraciones preparadas en tus objetos `PDO` para prevenir la inyección de SQL.

```php
// Suponiendo que tienes Flight::db() registrado como tu objeto PDO
$declaracion = Flight::db()->prepare('SELECT * FROM usuarios WHERE nombre_usuario = :nombre_usuario');
$declaracion->execute([':nombre_usuario' => $nombre_usuario]);
$usuarios = $declaracion->fetchAll();

// Si utilizas la clase PdoWrapper, esto se puede hacer fácilmente en una línea
$usuarios = Flight::db()->fetchAll('SELECT * FROM usuarios WHERE nombre_usuario = :nombre_usuario', [ 'nombre_usuario' => $nombre_usuario ]);

// Puedes hacer lo mismo con un objeto PDO con marcadores de posición ?
$declaracion = Flight::db()->fetchAll('SELECT * FROM usuarios WHERE nombre_usuario = ?', [ $nombre_usuario ]);

//Solo promete que nunca JAMÁS harás algo como esto...
$usuarios = Flight::db()->fetchAll("SELECT * FROM usuarios WHERE nombre_usuario = '{$nombre_usuario}' LIMIT 5");
// porque ¿qué pasa si $nombre_usuario = "' OR 1=1; -- "; 
// Después de construir la consulta, se vería así
// SELECT * FROM usuarios WHERE nombre_usuario = '' OR 1=1; -- LIMIT 5
// Parece extraño, pero es una consulta válida que funcionará. De hecho,
// es un ataque muy común de inyección de SQL que devolverá todos los usuarios.
```

## CORS

El Compartir Recursos de Origen Cruzado (CORS) es un mecanismo que permite que muchos recursos (por ejemplo, fuentes, JavaScript, etc.) en una página web se soliciten desde otro dominio fuera del dominio del que se originó el recurso. Flight no tiene funcionalidad incorporada, pero esto se puede manejar fácilmente con middleware o filtros de eventos similar al CSRF.

```php
// app/middleware/CorsMiddleware.php

namespace app\middleware;

class CorsMiddleware
{
	public function before(array $params): void
	{
		$response = Flight::response();
		if (isset($_SERVER['HTTP_ORIGIN'])) {
			$this->allowOrigins();
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

	private function allowOrigins(): void
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
})->addMiddleware(new CorsMiddleware());
```

## Conclusión

La seguridad es muy importante y es fundamental asegurarte de que tus aplicaciones web sean seguras. Flight proporciona una serie de características para ayudarte a asegurar tus aplicaciones web, pero es importante mantenerse siempre vigilante y asegurarse de que estás haciendo todo lo posible para mantener seguros los datos de tus usuarios. Siempre asume lo peor y nunca confíes en la entrada de tus usuarios. Siempre escapa la salida y utiliza declaraciones preparadas para evitar la inyección de SQL. Siempre usa middleware para proteger tus rutas de ataques CSRF y CORS. Si haces todas estas cosas, estarás en camino de construir aplicaciones web seguras.