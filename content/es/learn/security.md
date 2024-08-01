# Seguridad

La seguridad es fundamental cuando se trata de aplicaciones web. Quieres asegurarte de que tu aplicación sea segura y de que los datos de tus usuarios estén protegidos. Flight proporciona una serie de funciones para ayudarte a asegurar tus aplicaciones web.

## Cabeceras

Las cabeceras HTTP son una de las formas más fáciles de asegurar tus aplicaciones web. Puedes utilizar cabeceras para prevenir el secuestro de clics, XSS y otros ataques. Hay varias formas de agregar estas cabeceras a tu aplicación.

Dos excelentes sitios web para verificar la seguridad de tus cabeceras son [securityheaders.com](https://securityheaders.com/) y [observatory.mozilla.org](https://observatory.mozilla.org/).

### Agregar Manualmente

Puedes agregar manualmente estas cabeceras utilizando el método `header` en el objeto `Flight\Response`.
```php
// Establecer la cabecera X-Frame-Options para evitar el secuestro de clics
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Establecer la cabecera Content-Security-Policy para evitar XSS
// Nota: esta cabecera puede ser muy compleja, así que es recomendable
//  consultar ejemplos en internet para tu aplicación
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Establecer la cabecera X-XSS-Protection para prevenir XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Establecer la cabecera X-Content-Type-Options para prevenir el sniffing de MIME
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Establecer la cabecera Referrer-Policy para controlar cuánta información del referente se envía
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Establecer la cabecera Strict-Transport-Security para forzar HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Establecer la cabecera Permissions-Policy para controlar qué características y APIs se pueden utilizar
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Estas se pueden agregar al principio de tus archivos `bootstrap.php` o `index.php`.

### Agregar como Filtro

También puedes agregarlas en un filtro/gancho como se muestra a continuación: 

```php
// Agregar las cabeceras en un filtro
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

También puedes agregarlas como una clase de middleware. Esta es una buena manera de mantener tu código limpio y organizado.

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
// todas las rutas. Por supuesto, podrías hacer lo mismo y agregar esto únicamente a rutas específicas.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// más rutas
}, [ new SecurityHeadersMiddleware() ]);
```


## Falsificación de solicitudes entre sitios (CSRF)

La falsificación de solicitudes entre sitios (CSRF) es un tipo de ataque en el que un sitio web malicioso puede hacer que el navegador del usuario envíe una solicitud a tu sitio web. Esto se puede utilizar para realizar acciones en tu sitio web sin el conocimiento del usuario. Flight no proporciona un mecanismo integrado de protección CSRF, pero puedes implementar fácilmente el tuyo propio mediante el uso de middleware.

### Configuración

Primero debes generar un token CSRF y almacenarlo en la sesión del usuario. Luego puedes usar este token en tus formularios y verificarlo cuando se envíe el formulario.

```php
// Generar un token CSRF y guardarlo en la sesión del usuario
// (asumiendo que has creado un objeto de sesión y lo has adjuntado a Flight)
// consulta la documentación de la sesión para obtener más información
Flight::register('session', \Ghostff\Session\Session::class);

// Solo necesitas generar un token por sesión (para que funcione
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

#### Usando Latte

También puedes configurar una función personalizada para mostrar el token CSRF en tus plantillas de Latte.

```php
// Configurar una función personalizada para mostrar el token CSRF
// Nota: View ha sido configurado con Latte como motor de vista
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

¡Breve y sencillo, ¿verdad?

### Verificar el Token CSRF

Puedes verificar el token CSRF usando filtros de eventos:

```php
// Este middleware verifica si la solicitud es una solicitud POST y, si lo es, verifica si el token CSRF es válido
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// capturar el token csrf de los valores del formulario
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Token CSRF no válido');
			// o para una respuesta JSON
			Flight::jsonHalt(['error' => 'Token CSRF no válido'], 403);
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
				Flight::halt(403, 'Token CSRF no válido');
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


## Secuencias de comandos entre sitios (XSS)

Las secuencias de comandos entre sitios (XSS) son un tipo de ataque en el que un sitio web malicioso puede inyectar código en tu sitio web. La mayoría de estas oportunidades provienen de los valores de los formularios que completarán tus usuarios finales. ¡Nunca debes confiar en la salida de tus usuarios! Siempre asume que todos son los mejores hackers del mundo. Pueden inyectar JavaScript o HTML maliciosos en tu página. Este código se puede utilizar para robar información de tus usuarios o realizar acciones en tu sitio web. Utilizando la clase de vista de Flight, puedes escapar fácilmente la salida para prevenir ataques XSS.

```php
// Vamos a asumir que el usuario es ingenioso e intenta usar esto como su nombre
$nombre = '<script>alert("XSS")</script>';

// Esto escapará la salida
Flight::view()->set('name', $name);
// Esto mostrará: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Si usas algo como Latte registrado como tu clase de vista, también se escapará automáticamente.
Flight::view()->render('plantilla', ['name' => $nombre]);
```

## Inyección SQL

La inyección SQL es un tipo de ataque en el que un usuario malintencionado puede inyectar código SQL en tu base de datos. Esto se puede utilizar para robar información de tu base de datos o realizar acciones en tu base de datos. De nuevo, ¡nunca debes confiar en la entrada de tus usuarios! Siempre asume que están en busca de sangre. Puedes utilizar declaraciones preparadas en tus objetos `PDO` para evitar la inyección SQL.

```php
// Suponiendo que tienes Flight::db() registrado como tu objeto PDO
$declaracion = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$declaracion->execute([':username' => $nombre_de_usuario]);
$usuarios = $declaracion->fetchAll();

// Si utilizas la clase PdoWrapper, esto se puede hacer fácilmente en una línea
$usuarios = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $nombre_de_usuario ]);

// Puedes hacer lo mismo con un objeto PDO con marcadores de posición ?
$declaracion = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $nombre_de_usuario ]);

// Solo promete que nunca, JAMÁS, harás algo como esto...
$usuarios = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$nombre_de_usuario}' LIMIT 5");
// porque ¿qué pasa si $nombre_de_usuario = "' O 1=1; -- "; 
// Después de construir la consulta se vería así
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Parece extraño, ¡pero es una consulta válida que funcionará! De hecho,
// es un ataque de inyección SQL muy común que devolverá a todos los usuarios.
```

## CORS

El intercambio de recursos de origen cruzado (CORS) es un mecanismo que permite a muchos recursos (por ejemplo, fuentes, JavaScript, etc.) en una página web ser solicitados desde otro dominio fuera del dominio del cual se originó el recurso. Flight no tiene funcionalidad incorporada, pero esto se puede manejar fácilmente con un gancho que se ejecuta antes de que se llame al método `Flight::start()`.

```php
// app/utils/CorsUtil.php

namespace app\utils;

class CorsUtil
{
	public function set(array $params): void
	{
		$solicitud = Flight::request();
		$respuesta = Flight::response();
		if ($request->getVar('HTTP_ORIGIN') !== '') {
			$this->allowOrigins();
			$response->header('Access-Control-Allow-Credentials', 'true');
			$response->header('Access-Control-Max-Age', '86400');
		}

		if ($request->method === 'OPTIONS') {
			if ($request->getVar('HTTP_ACCESS_CONTROL_REQUEST_METHOD') !== '') {
				$response->header(
					'Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD'
				);
			}
			if ($request->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS') !== '') {
				$response->header(
					"Access-Control-Allow-Headers",
					$request->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS')
				);
			}

			$response->status(200);
			$response->send();
			exit;
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

		$solicitud = Flight::request();

		if (in_array($request->getVar('HTTP_ORIGIN'), $permitidos, true) === true) {
			$respuesta = Flight::response();
			$respuesta->header("Access-Control-Allow-Origin", $request->getVar('HTTP_ORIGIN'));
		}
	}
}

// index.php o donde tengas tus rutas
$CorsUtil = new CorsUtil();

// Esto debe ejecutarse antes de que start se ejecute.
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

## Conclusión

La seguridad es fundamental y es importante asegurarse de que tus aplicaciones web sean seguras. Flight proporciona una serie de funciones para ayudarte a asegurar tus aplicaciones web, pero es importante estar siempre vigilante y asegurarse de hacer todo lo posible para mantener seguros los datos de tus usuarios. Siempre asume lo peor y nunca confíes en la entrada de tus usuarios. Siempre escapa la salida y utiliza declaraciones preparadas para prevenir la inyección SQL. Utiliza siempre middleware para proteger tus rutas de ataques CSRF y CORS. Si haces todas estas cosas, estarás en buen camino para construir aplicaciones web seguras.