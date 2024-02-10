# Seguridad

La seguridad es muy importante cuando se trata de aplicaciones web. Quieres asegurarte de que tu aplicación sea segura y de que los datos de tus usuarios estén protegidos. Flight proporciona una serie de funciones para ayudarte a asegurar tus aplicaciones web.

## Falsificación de solicitud entre sitios (CSRF)

La falsificación de solicitud entre sitios (CSRF) es un tipo de ataque en el que un sitio web malicioso puede hacer que el navegador de un usuario envíe una solicitud a tu sitio web. Esto se puede utilizar para realizar acciones en tu sitio web sin el conocimiento del usuario. Flight no proporciona un mecanismo de protección CSRF incorporado, pero puedes implementar fácilmente el tuyo propio usando middleware.

Primero necesitas generar un token CSRF y almacenarlo en la sesión del usuario. Luego puedes usar este token en tus formularios y comprobarlo cuando se envíe el formulario.

```php
// Generar un token CSRF y almacenarlo en la sesión del usuario
// (suponiendo que has creado un objeto de sesión y lo has adjuntado a Flight)
Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
```

```html
<!-- Usa el token CSRF en tu formulario -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- otros campos del formulario -->
</form>
```

Y luego puedes comprobar el token CSRF usando filtros de eventos:

```php
// Este middleware comprueba si la solicitud es una solicitud POST y, si lo es, comprueba si el token CSRF es válido
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// captura el token CSRF de los valores del formulario
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Token CSRF no válido');
		}
	}
});
```

## Secuencias de comandos entre sitios (XSS)

Las secuencias de comandos entre sitios (XSS) es un tipo de ataque en el que un sitio web malicioso puede inyectar código en tu sitio web. La mayoría de estas oportunidades provienen de los valores del formulario que tus usuarios completarán. ¡Nunca debes confiar en la salida de tus usuarios! Siempre asume que todos son los mejores hackers del mundo. Pueden inyectar JavaScript malicioso o HTML en tu página. Este código se puede utilizar para robar información de tus usuarios o realizar acciones en tu sitio web. Utilizando la clase de vista de Flight, puedes escapar fácilmente la salida para prevenir ataques XSS.

```php

// Supongamos que el usuario es astuto e intenta usar esto como su nombre
$name = '<script>alert("XSS")</script>';

// Esto escapará la salida
Flight::view()->set('name', $name);
// Esto producirá: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Si usas algo como Latte registrado como tu clase de vista, también se escapará automáticamente esto.
Flight::view()->render('plantilla', ['name' => $name]);
```

## Inyección de SQL

La inyección de SQL es un tipo de ataque en el que un usuario malintencionado puede inyectar código SQL en tu base de datos. Esto se puede utilizar para robar información de tu base de datos o realizar acciones en tu base de datos. Nuevamente, ¡nunca debes confiar en la entrada de tus usuarios! Siempre asume que van a por todas. Puedes utilizar declaraciones preparadas en tus objetos `PDO` para prevenir la inyección de SQL.

```php

// Suponiendo que tienes Flight::db() registrado como tu objeto PDO
$declaración = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$declaración->execute([':username' => $username]);
$usuarios = $declaración->fetchAll();

// Si usas la clase PdoWrapper, esto se puede hacer fácilmente en una línea
$usuarios = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Puedes hacer lo mismo con un objeto PDO con marcadores ? placeholders
$declaración = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// Solo promete que nunca JAMÁS harás algo como esto...
$usuarios = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// porque ¿qué pasa si $username = "' OR 1=1; -- "; Después de construir la consulta se ve
// así
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// Parece extraño, pero es una consulta válida que funcionará. De hecho,
// es un ataque de inyección de SQL muy común que devolverá todos los usuarios.
```

## CORS

Cross-Origin Resource Sharing (CORS) es un mecanismo que permite que muchos recursos (por ejemplo, fuentes, JavaScript, etc.) en una página web se soliciten desde otro dominio fuera del dominio del que se originó el recurso. Flight no tiene funcionalidad incorporada, pero esto se puede manejar fácilmente con middleware o filtros de eventos similares a CSRF.

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
		$allowed = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		if (in_array($_SERVER['HTTP_ORIGIN'], $allowed)) {
			$response = Flight::response();
			$response->header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		}
	}
}

// index.php o donde tengas tus rutas
Flight::route('/usuarios', function() {
	$usuarios = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json($usuarios);
})->addMiddleware(new CorsMiddleware());
```

## Conclusión

La seguridad es muy importante y es fundamental asegurarse de que tus aplicaciones web sean seguras. Flight proporciona una serie de funciones para ayudarte a asegurar tus aplicaciones web, pero es importante mantenerse siempre vigilante y asegurarse de estar haciendo todo lo posible para mantener seguros los datos de tus usuarios. Siempre asume lo peor y nunca confíes en la entrada de tus usuarios. Siempre escapa la salida y utiliza declaraciones preparadas para prevenir la inyección de SQL. Utiliza siempre middleware para proteger tus rutas de ataques CSRF y CORS. Si haces todas estas cosas, estarás en buen camino para construir aplicaciones web seguras.