# Seguridad

La seguridad es un gran problema cuando se trata de aplicaciones web. Quieres asegurarte de que tu aplicación sea segura y que los datos de tus usuarios estén a salvo. Flight proporciona una serie de características para ayudarte a asegurar tus aplicaciones web.

## Encabezados

Los encabezados HTTP son una de las formas más fáciles de asegurar tus aplicaciones web. Puedes usar encabezados para prevenir clickjacking, XSS y otros ataques. Hay varias maneras de agregar estos encabezados a tu aplicación.

Dos excelentes sitios web para verificar la seguridad de tus encabezados son [securityheaders.com](https://securityheaders.com/) y 
[observatory.mozilla.org](https://observatory.mozilla.org/).

### Agregar a Mano

Puedes agregar manualmente estos encabezados utilizando el método `header` en el objeto `Flight\Response`.
```php
// Establece el encabezado X-Frame-Options para prevenir clickjacking
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Establece el encabezado Content-Security-Policy para prevenir XSS
// Nota: este encabezado puede volverse muy complejo, así que querrás
// consultar ejemplos en internet para tu aplicación
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Establece el encabezado X-XSS-Protection para prevenir XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Establece el encabezado X-Content-Type-Options para prevenir MIME sniffing
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Establece el encabezado Referrer-Policy para controlar cuánta información de referencia se envía
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Establece el encabezado Strict-Transport-Security para forzar HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Establece el encabezado Permissions-Policy para controlar qué características y APIs pueden usarse
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Estos se pueden agregar al principio de tus archivos `bootstrap.php` o `index.php`.

### Agregar como un Filtro

También puedes agregarlos en un filtro/gatillo como el siguiente: 

```php
// Agrega los encabezados en un filtro
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

También puedes agregarlos como una clase middleware. Esta es una buena manera de mantener tu código limpio y organizado.

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
// todas las rutas. Por supuesto, podrías hacer lo mismo y solo agregar
// esto solo a rutas específicas.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// más rutas
}, [ new SecurityHeadersMiddleware() ]);
```


## Cross Site Request Forgery (CSRF)

Cross Site Request Forgery (CSRF) es un tipo de ataque donde un sitio web malicioso puede hacer que el navegador de un usuario envíe una solicitud a tu sitio web. Esto puede usarse para realizar acciones en tu sitio web sin el conocimiento del usuario. Flight no proporciona un mecanismo de protección CSRF incorporado, pero puedes implementar fácilmente el tuyo utilizando middleware.

### Configuración

Primero, necesitas generar un token CSRF y almacenarlo en la sesión del usuario. Luego puedes usar este token en tus formularios y verificarlo cuando se envíe el formulario.

```php
// Genera un token CSRF y almacénalo en la sesión del usuario
// (suponiendo que has creado un objeto de sesión y lo has adjuntado a Flight)
// consulta la documentación de sesión para más información
Flight::register('session', \Ghostff\Session\Session::class);

// Solo necesitas generar un único token por sesión (para que funcione 
// en múltiples pestañas y solicitudes para el mismo usuario)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- Usa el token CSRF en tu formulario -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- otros campos del formulario -->
</form>
```

#### Usando Latte

También puedes establecer una función personalizada para mostrar el token CSRF en tus plantillas Latte.

```php
// Establece una función personalizada para mostrar el token CSRF
// Nota: La vista ha sido configurada con Latte como motor de vista
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

Y ahora en tus plantillas Latte puedes usar la función `csrf()` para mostrar el token CSRF.

```html
<form method="post">
	{csrf()}
	<!-- otros campos del formulario -->
</form>
```

¿Corto y sencillo, verdad?

### Verificar el Token CSRF

Puedes verificar el token CSRF usando filtros de eventos:

```php
// Este middleware verifica si la solicitud es una solicitud POST y si lo es, verifica si el token CSRF es válido
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// captura el token csrf de los valores del formulario
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Token CSRF inválido');
			// o para una respuesta JSON
			Flight::jsonHalt(['error' => 'Token CSRF inválido'], 403);
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
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// más rutas
}, [ new CsrfMiddleware() ]);
```

## Cross Site Scripting (XSS)

Cross Site Scripting (XSS) es un tipo de ataque donde un sitio web malicioso puede inyectar código en tu sitio web. La mayoría de estas oportunidades provienen de valores de formularios que tus usuarios finales llenarán. ¡Nunca debes confiar en la salida de tus usuarios! Siempre asume que todos ellos son los mejores hackers del mundo. Pueden inyectar JavaScript o HTML malicioso en tu página. Este código puede usarse para robar información de tus usuarios o realizar acciones en tu sitio web. Usando la clase de vista de Flight, puedes escapar fácilmente la salida para prevenir ataques XSS.

```php
// Supongamos que el usuario es astuto y trata de usar esto como su nombre
$name = '<script>alert("XSS")</script>';

// Esto escapará la salida
Flight::view()->set('name', $name);
// Esto mostrará: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Si usas algo como Latte registrado como tu clase de vista, también escapará esto automáticamente.
Flight::view()->render('template', ['name' => $name]);
```

## Inyección de SQL

La inyección de SQL es un tipo de ataque donde un usuario malicioso puede inyectar código SQL en tu base de datos. Esto puede usarse para robar información de tu base de datos o realizar acciones en tu base de datos. Nuevamente, jamás debes confiar en la entrada de tus usuarios. Siempre asume que están en busca de venganza. Puedes usar declaraciones preparadas en tus objetos `PDO` para prevenir la inyección de SQL.

```php
// Suponiendo que tienes Flight::db() registrado como tu objeto PDO
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Si usas la clase PdoWrapper, esto se puede hacer fácilmente en una línea
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Puedes hacer lo mismo con un objeto PDO con marcadores de posición ?
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Solo prométeme que nunca, NUNCA harás algo como esto...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// porque ¿qué pasa si $username = "' OR 1=1; -- "; 
// Después de que se construye la consulta, se ve así
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Se ve extraño, pero es una consulta válida que funcionará. De hecho,
// es un ataque de inyección de SQL muy común que devolverá todos los usuarios.
```

## CORS

El intercambio de recursos de origen cruzado (CORS) es un mecanismo que permite que muchos recursos (por ejemplo, fuentes, JavaScript, etc.) en una página web sean solicitados desde otro dominio fuera del dominio del que se originó el recurso. Flight no tiene funcionalidad incorporada, pero esto se puede manejar fácilmente con un gancho que se ejecute antes de que se llame al método `Flight::start()`.

```php
// app/utils/CorsUtil.php

namespace app\utils;

class CorsUtil
{
	public function set(array $params): void
	{
		$request = Flight::request();
		$response = Flight::response();
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
		$allowed = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		$request = Flight::request();

		if (in_array($request->getVar('HTTP_ORIGIN'), $allowed, true) === true) {
			$response = Flight::response();
			$response->header("Access-Control-Allow-Origin", $request->getVar('HTTP_ORIGIN'));
		}
	}
}

// index.php o donde tengas tus rutas
$CorsUtil = new CorsUtil();

// Esto debe ejecutarse antes de que se inicie el arranque.
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

## Manejo de Errores
Oculta detalles sensibles de los errores en producción para evitar filtrar información a los atacantes.

```php
// En tu bootstrap.php o index.php

// en flightphp/skeleton, esto está en app/config/config.php
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // Desactivar la visualización de errores
    ini_set('log_errors', 1);     // Registrar errores en su lugar
    ini_set('error_log', '/path/to/error.log');
}

// En tus rutas o controladores
// Usa Flight::halt() para respuestas de error controladas
Flight::halt(403, 'Acceso denegado');
```

## Sanitización de Entrada
Nunca confíes en la entrada del usuario. Sanitiza antes de procesar para evitar que datos maliciosos se cuelen.

```php

// Supongamos que hay una solicitud $_POST con $_POST['input'] y $_POST['email']

// Sanitiza una entrada de cadena
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// Sanitiza un email
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

## Hashing de Contraseña
Almacena las contraseñas de forma segura y verifícalas de manera segura utilizando las funciones integradas de PHP.

```php
$password = Flight::request()->data->password;
// Hashea una contraseña al almacenarla (por ejemplo, durante el registro)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Verifica una contraseña (por ejemplo, durante el inicio de sesión)
if (password_verify($password, $stored_hash)) {
    // La contraseña coincide
}
```

## Limitación de Tasa
Protege contra ataques de fuerza bruta limitando las tasas de solicitud con un caché.

```php
// Suponiendo que tienes flightphp/cache instalado y registrado
// Usando flightphp/cache en un middleware
Flight::before('start', function() {
    $cache = Flight::cache();
    $ip = Flight::request()->ip;
    $key = "rate_limit_{$ip}";
    $attempts = (int) $cache->retrieve($key);
    
    if ($attempts >= 10) {
        Flight::halt(429, 'Demasiadas solicitudes');
    }
    
    $cache->set($key, $attempts + 1, 60); // Reiniciar después de 60 segundos
});
```

## Conclusión

La seguridad es un gran problema y es importante asegurarte de que tus aplicaciones web sean seguras. Flight proporciona una serie de características para ayudarte a asegurar tus aplicaciones web, pero es importante siempre estar alerta y asegurarte de que estás haciendo todo lo posible para mantener seguros los datos de tus usuarios. Siempre asume lo peor y nunca confíes en la entrada de tus usuarios. Siempre escapa la salida y utiliza sentencias preparadas para prevenir la inyección de SQL. Siempre usa middleware para proteger tus rutas de ataques CSRF y CORS. Si haces todas estas cosas, estarás bien encaminado para construir aplicaciones web seguras.