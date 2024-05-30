# Seguridad

La seguridad es fundamental cuando se trata de aplicaciones web. Deseas asegurarte de que tu aplicación sea segura y de que los datos de tus usuarios estén protegidos. Flight proporciona una serie de funciones para ayudarte a asegurar tus aplicaciones web.

## Encabezados

Los encabezados HTTP son una de las formas más sencillas de asegurar tus aplicaciones web. Puedes usar encabezados para prevenir el secuestro de clics, XSS y otros ataques. Hay varias formas de agregar estos encabezados a tu aplicación.

Dos excelentes sitios web para verificar la seguridad de tus encabezados son [securityheaders.com](https://securityheaders.com/) y [observatory.mozilla.org](https://observatory.mozilla.org/).

### Agregar manualmente

Puedes agregar manualmente estos encabezados utilizando el método `header` en el objeto `Flight\Response`.
```php
// Establecer el encabezado X-Frame-Options para prevenir el secuestro de clics
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Establecer el encabezado Content-Security-Policy para prevenir XSS
// Nota: este encabezado puede volverse muy complejo, así que es mejor
// consultar ejemplos en Internet para tu aplicación
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Establecer el encabezado X-XSS-Protection para prevenir XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Establecer el encabezado X-Content-Type-Options para prevenir el sniffing MIME
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Establecer el encabezado Referrer-Policy para controlar cuánta información del referente se envía
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Establecer el encabezado Strict-Transport-Security para forzar el HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Establecer el encabezado Permissions-Policy para controlar qué funciones y APIs se pueden utilizar
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Estos pueden ser agregados al principio de tus archivos `bootstrap.php` o `index.php`.

### Agregar como un filtro

También puedes agregarlos en un filtro/gancho como el siguiente:

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

### Agregar como middleware

También puedes añadirlos como una clase de middleware. Esta es una buena manera de mantener limpio y organizado tu código.

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
// todas las rutas. Por supuesto, podrías hacer lo mismo y añadir esto solo a rutas específicas.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// más rutas
}, [ new SecurityHeadersMiddleware() ]);
```

## Falsificación de solicitudes entre sitios (CSRF)

La falsificación de solicitudes entre sitios (CSRF) es un tipo de ataque en el que un sitio web malicioso puede hacer que el navegador de un usuario envíe una solicitud a tu sitio web. Esto se puede utilizar para realizar acciones en tu sitio web sin el conocimiento del usuario. Flight no proporciona un mecanismo de protección CSRF incorporado, pero puedes implementar fácilmente el tuyo propio utilizando middleware.

### Configuración

Primero necesitas generar un token CSRF y almacenarlo en la sesión del usuario. Luego puedes usar este token en tus formularios y verificarlo cuando se envía el formulario.

```php
// Generar un token CSRF y almacenarlo en la sesión del usuario
// (asumiendo que has creado un objeto de sesión y lo has adjuntado a Flight)
// Solo necesitas generar un token por sesión (para que funcione
// en varias pestañas y solicitudes para el mismo usuario)
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

También puedes establecer una función personalizada para mostrar el token CSRF en tus plantillas de Latte.

```php
// Establecer una función personalizada para mostrar el token CSRF
// Nota: La Vista ha sido configurada con Latte como el motor de vista
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

### Verificar el token CSRF

Puedes verificar el token CSRF usando filtros de eventos:

```php
// Este middleware verifica si la solicitud es una solicitud POST y si lo es, verifica si el token CSRF es válido
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

O puedes usar una clase de middleware:

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
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// más rutas
}, [ new CsrfMiddleware() ]);
```

## Cross Site Scripting (XSS)

Cross Site Scripting (XSS) es un tipo de ataque en el que un sitio web malicioso puede inyectar código en tu sitio web. La mayoría de estas oportunidades provienen de los valores de los formularios que completarán tus usuarios. ¡Nunca debes confiar en la salida de tus usuarios! Siempre asume que todos son los mejores hackers del mundo. Pueden inyectar JavaScript o HTML malicioso en tu página. Este código puede usarse para robar información de tus usuarios o realizar acciones en tu sitio web. Utilizando la clase de vista de Flight, puedes escapar fácilmente la salida para prevenir ataques XSS.

```php
// Supongamos que el usuario es astuto y trata de usar esto como su nombre
$nombre = '<script>alert("XSS")</script>';

// Esto escapará la salida
Flight::view()->set('nombre', $nombre);
// Esto producirá: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Si utilizas algo como Latte registrado como tu clase de vista, esto también se escapará automáticamente.
Flight::view()->render('plantilla', ['nombre' => $nombre]);
```

## Inyección SQL

La inyección SQL es un tipo de ataque en el que un usuario malintencionado puede insertar código SQL en tu base de datos. Esto se puede usar para robar información de tu base de datos o ejecutar acciones en tu base de datos. Nuevamente, ¡nunca debes confiar en la entrada de tus usuarios! Siempre asume que van a por todas. Puedes utilizar declaraciones preparadas en tus objetos `PDO` para prevenir la inyección SQL.

```php
// Suponiendo que tienes Flight::db() registrado como tu objeto PDO
$declaracion = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$declaracion->execute([':username' => $username]);
$usuarios = $declaracion->fetchAll();

// Si utilizas la clase PdoWrapper, esto se puede hacer fácilmente en una línea
$usuarios = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Puedes hacer lo mismo con un objeto PDO con marcadores de posición ?
$declaracion = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Solo promete que nunca, NUNCA harás algo como esto...
$usuarios = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// porque ¿qué pasa si $username = "' OR 1=1; -- "; 
// Después de que se construye la consulta, parece esto
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Parece extraño, pero es una consulta válida que funcionará. De hecho,
// es un ataque común de inyección SQL que devolverá todos los usuarios.
```

## CORS

Cross-Origin Resource Sharing (CORS) es un mecanismo que permite solicitar muchos recursos (por ejemplo, fuentes, JavaScript, etc.) en una página web desde otro dominio fuera del dominio del que se originó el recurso. Flight no tiene funcionalidad incorporada, pero esto se puede manejar fácilmente con un gancho que se ejecuta antes de que se llame al método `Flight::start()`.

```php
// app/utils/CorsUtil.php

namespace app\utils;

class CorsUtil
{
	public function set(array $params): void
	{
		$solicitud = Flight::request();
		$respuesta = Flight::response();
		if ($solicitud->getVar('HTTP_ORIGIN') !== '') {
			$this->permitirOrígenes();
			$respuesta->header('Access-Control-Allow-Credentials', 'true');
			$respuesta->header('Access-Control-Max-Age', '86400');
		}

		if ($solicitud->method === 'OPTIONS') {
			if ($solicitud->getVar('HTTP_ACCESS_CONTROL_REQUEST_METHOD') !== '') {
				$respuesta->header(
					'Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD'
				);
			}
			if ($solicitud->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS') !== '') {
				$respuesta->header(
					"Access-Control-Allow-Headers",
					$solicitud->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS')
				);
			}

			$respuesta->status(200);
			$respuesta->send();
			exit;
		}
	}

	private function permitirOrígenes(): void
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

		$solicitud = Flight::request();

		if (in_array($solicitud->getVar('HTTP_ORIGIN'), $permitidos, true) === true) {
			$respuesta = Flight::response();
			$respuesta->header("Access-Control-Allow-Origin", $solicitud->getVar('HTTP_ORIGIN'));
		}
	}
}

// index.php o donde tengas tus rutas
$CorsUtil = new CorsUtil();
Flight::before('start', [ $CorsUtil, 'setupCors' ]);

```

## Conclusión

La seguridad es fundamental y es importante asegurarte de que tus aplicaciones web sean seguras. Flight proporciona una serie de funciones para ayudarte a asegurar tus aplicaciones web, pero es importante estar siempre alerta y asegurarte de que estás haciendo todo lo posible para mantener seguros los datos de tus usuarios. Siempre asume lo peor y nunca confíes en la entrada de tus usuarios. Siempre escapa la salida y utiliza declaraciones preparadas para prevenir la inyección SQL. Siempre utiliza middleware para proteger tus rutas de ataques CSRF y CORS. Si haces todas estas cosas, estarás en buen camino para construir aplicaciones web seguras.