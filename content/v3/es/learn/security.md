# Seguridad

## Resumen

La seguridad es un asunto importante cuando se trata de aplicaciones web. Quieres asegurarte de que tu aplicación sea segura y de que los datos de tus usuarios estén 
seguros. Flight proporciona una serie de funciones para ayudarte a asegurar tus aplicaciones web.

## Comprensión

Hay una serie de amenazas de seguridad comunes de las que debes estar al tanto al construir aplicaciones web. Algunas de las amenazas más comunes
incluyen:
- Cross Site Request Forgery (CSRF)
- Cross Site Scripting (XSS)
- SQL Injection
- Cross Origin Resource Sharing (CORS)

[Templates](/learn/templates) ayudan con XSS escapando la salida por defecto para que no tengas que recordarlo. [Sessions](/awesome-plugins/session) puede ayudar con CSRF almacenando un token CSRF en la sesión del usuario como se describe a continuación. Usar declaraciones preparadas con PDO puede ayudar a prevenir ataques de inyección SQL (o usar métodos útiles en la clase [PdoWrapper](/learn/pdo-wrapper)). CORS puede manejarse con un gancho simple antes de que se llame `Flight::start()`.

Todos estos métodos trabajan juntos para ayudar a mantener tus aplicaciones web seguras. Siempre debe estar a la vanguardia de tu mente aprender y entender las mejores prácticas de seguridad.

## Uso Básico

### Encabezados

Los encabezados HTTP son una de las formas más fáciles de asegurar tus aplicaciones web. Puedes usar encabezados para prevenir clickjacking, XSS y otros ataques. 
Hay varias formas en que puedes agregar estos encabezados a tu aplicación.

Dos excelentes sitios web para verificar la seguridad de tus encabezados son [securityheaders.com](https://securityheaders.com/) y 
[observatory.mozilla.org](https://observatory.mozilla.org/). Después de configurar el código a continuación, puedes verificar fácilmente que tus encabezados estén funcionando con esos dos sitios web.

#### Agregar Manualmente

Puedes agregar estos encabezados manualmente usando el método `header` en el objeto `Flight\Response`.
```php
// Establecer el encabezado X-Frame-Options para prevenir clickjacking
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Establecer el encabezado Content-Security-Policy para prevenir XSS
// Nota: este encabezado puede volverse muy complejo, así que querrás
//  consultar ejemplos en internet para tu aplicación
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Establecer el encabezado X-XSS-Protection para prevenir XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Establecer el encabezado X-Content-Type-Options para prevenir MIME sniffing
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Establecer el encabezado Referrer-Policy para controlar cuánta información de referrer se envía
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Establecer el encabezado Strict-Transport-Security para forzar HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Establecer el encabezado Permissions-Policy para controlar qué funciones y APIs pueden usarse
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Estos pueden agregarse al inicio de tus archivos `routes.php` o `index.php`.

#### Agregar como un Filtro

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

#### Agregar como un Middleware

También puedes agregarlos como una clase middleware que proporciona la mayor flexibilidad para qué rutas aplicar esto. En general, estos encabezados deben aplicarse a todas las respuestas HTML y API.

```php
// app/middlewares/SecurityHeadersMiddleware.php

namespace app\middlewares;

use flight\Engine;

class SecurityHeadersMiddleware
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function before(array $params): void
	{
		$response = $this->app->response();
		$response->header('X-Frame-Options', 'SAMEORIGIN');
		$response->header("Content-Security-Policy", "default-src 'self'");
		$response->header('X-XSS-Protection', '1; mode=block');
		$response->header('X-Content-Type-Options', 'nosniff');
		$response->header('Referrer-Policy', 'no-referrer-when-downgrade');
		$response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		$response->header('Permissions-Policy', 'geolocation=()');
	}
}

// index.php o donde tengas tus rutas
// FYI, este grupo de cadena vacía actúa como un middleware global para
// todas las rutas. Por supuesto, podrías hacer lo mismo y solo agregar
// esto a rutas específicas.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// más rutas
}, [ SecurityHeadersMiddleware::class ]);
```

### Cross Site Request Forgery (CSRF)

Cross Site Request Forgery (CSRF) es un tipo de ataque donde un sitio web malicioso puede hacer que el navegador de un usuario envíe una solicitud a tu sitio web. 
Esto puede usarse para realizar acciones en tu sitio web sin el conocimiento del usuario. Flight no proporciona un mecanismo de protección CSRF incorporado, 
pero puedes implementar fácilmente el tuyo propio usando middleware.

#### Configuración

Primero necesitas generar un token CSRF y almacenarlo en la sesión del usuario. Luego puedes usar este token en tus formularios y verificarlo cuando 
el formulario se envíe. Usaremos el plugin [flightphp/session](/awesome-plugins/session) para manejar sesiones.

```php
// Generar un token CSRF y almacenarlo en la sesión del usuario
// (asumiendo que has creado un objeto de sesión y lo has adjuntado a Flight)
// consulta la documentación de sesión para más información
Flight::register('session', flight\Session::class);

// Solo necesitas generar un solo token por sesión (para que funcione 
// a través de múltiples pestañas y solicitudes para el mismo usuario)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### Usando la Plantilla PHP Flight Predeterminada

```html
<!-- Usar el token CSRF en tu formulario -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- otros campos del formulario -->
</form>
```

##### Usando Latte

También puedes establecer una función personalizada para mostrar el token CSRF en tus plantillas Latte.

```php

Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// otras configuraciones...

	// Establecer una función personalizada para mostrar el token CSRF
	$latte->addFunction('csrf', function() {
		$csrfToken = Flight::session()->get('csrf_token');
		return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
	});

	$latte->render($finalPath, $data, $block);
});
```

Y ahora en tus plantillas Latte puedes usar la función `csrf()` para mostrar el token CSRF.

```html
<form method="post">
	{csrf()}
	<!-- otros campos del formulario -->
</form>
```

#### Verificar el Token CSRF

Puedes verificar el token CSRF usando varios métodos.

##### Middleware

```php
// app/middlewares/CsrfMiddleware.php

namespace app\middleware;

use flight\Engine;

class CsrfMiddleware
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function before(array $params): void
	{
		if($this->app->request()->method == 'POST') {
			$token = $this->app->request()->data->csrf_token;
			if($token !== $this->app->session()->get('csrf_token')) {
				$this->app->halt(403, 'Invalid CSRF token');
			}
		}
	}
}

// index.php o donde tengas tus rutas
use app\middlewares\CsrfMiddleware;

Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// más rutas
}, [ CsrfMiddleware::class ]);
```

##### Filtros de Eventos

```php
// Este middleware verifica si la solicitud es una solicitud POST y si lo es, verifica si el token CSRF es válido
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// capturar el token csrf de los valores del formulario
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Invalid CSRF token');
			// o para una respuesta JSON
			Flight::jsonHalt(['error' => 'Invalid CSRF token'], 403);
		}
	}
});
```

### Cross Site Scripting (XSS)

Cross Site Scripting (XSS) es un tipo de ataque donde una entrada de formulario maliciosa puede inyectar código en tu sitio web. La mayoría de estas oportunidades provienen 
de valores de formulario que tus usuarios finales completarán. ¡**Nunca** confíes en la salida de tus usuarios! Siempre asume que todos ellos son los 
mejores hackers del mundo. Pueden inyectar JavaScript o HTML malicioso en tu página. Este código puede usarse para robar información de tus 
usuarios o realizar acciones en tu sitio web. Usando la clase de vista de Flight o otro motor de plantillas como [Latte](/awesome-plugins/latte), puedes escapar fácilmente la salida para prevenir ataques XSS.

```php
// Supongamos que el usuario es astuto e intenta usar esto como su nombre
$name = '<script>alert("XSS")</script>';

// Esto escapará la salida
Flight::view()->set('name', $name);
// Esto mostrará: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Si usas algo como Latte registrado como tu clase de vista, también escapará esto automáticamente.
Flight::view()->render('template', ['name' => $name]);
```

### SQL Injection

SQL Injection es un tipo de ataque donde un usuario malicioso puede inyectar código SQL en tu base de datos. Esto puede usarse para robar información 
de tu base de datos o realizar acciones en tu base de datos. De nuevo, ¡**nunca** confíes en la entrada de tus usuarios! Siempre asume que están 
buscando sangre. Puedes usar declaraciones preparadas en tus objetos `PDO` para prevenir inyección SQL.

```php
// Asumiendo que tienes Flight::db() registrado como tu objeto PDO
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// Si usas la clase PdoWrapper, esto puede hacerse fácilmente en una línea
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Puedes hacer lo mismo con un objeto PDO con marcadores de posición ?
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

#### Ejemplo Inseguro

A continuación se explica por qué usamos declaraciones SQL preparadas para protegernos de ejemplos inocentes como el de abajo:

```php
// el usuario final completa un formulario web.
// para el valor del formulario, el hacker pone algo como esto:
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// Después de que la consulta se construye, se ve así
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// Se ve extraño, pero es una consulta válida que funcionará. De hecho,
// es un ataque de inyección SQL muy común que devolverá todos los usuarios.

var_dump($users); // esto volcará todos los usuarios en la base de datos, no solo el nombre de usuario único
```

### CORS

Cross-Origin Resource Sharing (CORS) es un mecanismo que permite que muchos recursos (p. ej., fuentes, JavaScript, etc.) en una página web se 
soliciten desde otro dominio fuera del dominio desde el cual se originó el recurso. Flight no tiene funcionalidad incorporada, 
pero esto puede manejarse fácilmente con un gancho para ejecutar antes de que se llame el método `Flight::start()`.

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

// Esto necesita ejecutarse antes de que start se ejecute.
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

### Manejo de Errores
Oculta detalles de errores sensibles en producción para evitar filtrar información a atacantes. En producción, registra errores en lugar de mostrarlos con `display_errors` establecido en `0`.

```php
// En tu bootstrap.php o index.php

// agrega esto a tu app/config/config.php
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // Deshabilitar la visualización de errores
    ini_set('log_errors', 1);     // Registrar errores en su lugar
    ini_set('error_log', '/path/to/error.log');
}

// En tus rutas o controladores
// Usa Flight::halt() para respuestas de error controladas
Flight::halt(403, 'Access denied');
```

### Sanitización de Entrada
Nunca confíes en la entrada del usuario. Sánitala usando [filter_var](https://www.php.net/manual/en/function.filter-var.php) antes de procesarla para prevenir que datos maliciosos se cuelen.

```php

// Supongamos una solicitud $_POST con $_POST['input'] y $_POST['email']

// Sanitizar una entrada de cadena
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// Sanitizar un email
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

### Hash de Contraseñas
Almacena contraseñas de manera segura y verifícalas de forma segura usando las funciones integradas de PHP como [password_hash](https://www.php.net/manual/en/function.password-hash.php) y [password_verify](https://www.php.net/manual/en/function.password-verify.php). Las contraseñas nunca deben almacenarse en texto plano, ni deben encriptarse con métodos reversibles. El hashing asegura que incluso si tu base de datos es comprometida, las contraseñas reales permanezcan protegidas.

```php
$password = Flight::request()->data->password;
// Hashear una contraseña al almacenar (p. ej., durante el registro)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Verificar una contraseña (p. ej., durante el inicio de sesión)
if (password_verify($password, $stored_hash)) {
    // La contraseña coincide
}
```

### Limitación de Tasa
Protege contra ataques de fuerza bruta o ataques de denegación de servicio limitando las tasas de solicitud con una caché.

```php
// Asumiendo que tienes flightphp/cache instalado y registrado
// Usando flightphp/cache en un filtro
Flight::before('start', function() {
    $cache = Flight::cache();
    $ip = Flight::request()->ip;
    $key = "rate_limit_{$ip}";
    $attempts = (int) $cache->retrieve($key);
    
    if ($attempts >= 10) {
        Flight::halt(429, 'Too many requests');
    }
    
    $cache->set($key, $attempts + 1, 60); // Reiniciar después de 60 segundos
});
```

## Ver También
- [Sessions](/awesome-plugins/session) - Cómo manejar sesiones de usuario de manera segura.
- [Templates](/learn/templates) - Usar plantillas para escapar automáticamente la salida y prevenir XSS.
- [PDO Wrapper](/learn/pdo-wrapper) - Interacciones simplificadas con la base de datos usando declaraciones preparadas.
- [Middleware](/learn/middleware) - Cómo usar middleware para simplificar el proceso de agregar encabezados de seguridad.
- [Responses](/learn/responses) - Cómo personalizar respuestas HTTP con encabezados seguros.
- [Requests](/learn/requests) - Cómo manejar y sanitizar la entrada del usuario.
- [filter_var](https://www.php.net/manual/en/function.filter-var.php) - Función PHP para sanitización de entrada.
- [password_hash](https://www.php.net/manual/en/function.password-hash.php) - Función PHP para hashing seguro de contraseñas.
- [password_verify](https://www.php.net/manual/en/function.password-verify.php) - Función PHP para verificar contraseñas hasheadas.

## Solución de Problemas
- Consulta la sección "Ver También" anterior para información de solución de problemas relacionada con problemas con componentes del Framework Flight.

## Registro de Cambios
- v3.1.0 - Agregadas secciones sobre CORS, Manejo de Errores, Sanitización de Entrada, Hash de Contraseñas y Limitación de Tasa.
- v2.0 - Agregado escaping para vistas predeterminadas para prevenir XSS.