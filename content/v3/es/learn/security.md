# Seguridad

## Descripción General

La seguridad es un tema importante cuando se trata de aplicaciones web. Desea asegurarse de que su aplicación sea segura y que los datos de sus usuarios estén protegidos. Flight proporciona una serie de características que le ayudan a proteger sus aplicaciones web.

El [esqueleto](https://github.com/flightphp/skeleton) oficial también incluye un archivo dedicado **`SECURITY.md`** y un middleware de cabeceras de seguridad para que las [herramientas de codificación con IA](/learn/ai) (y los humanos) tengan un lugar deliberado para los secretos, las cabeceras y las reglas de XSS/SQL, independiente del estilo general de codificación en `AGENTS.md`.

## Entendimiento

Existen varias amenazas de seguridad comunes que debe conocer al construir aplicaciones web. Algunas de las amenazas más comunes incluyen:
- Cross Site Request Forgery (CSRF)
- Cross Site Scripting (XSS)
- Inyección SQL
- Cross Origin Resource Sharing (CORS)

Las [Plantillas](/learn/templates) ayudan con XSS al escapar la salida por defecto (Twig y Latte lo hacen; aproveche esa ventaja). Las [Sesiones](/awesome-plugins/session) pueden ayudar con CSRF almacenando un token CSRF en la sesión del usuario como se describe a continuación. El uso de sentencias preparadas con PDO—o los helpers en [SimplePdo](/learn/simple-pdo)—ayuda a prevenir la inyección SQL. CORS puede manejarse con un hook simple antes de que se llame a `Flight::start()`.

Todos estos métodos trabajan juntos para ayudar a mantener sus aplicaciones web seguras. Siempre debe tener presente aprender y entender las mejores prácticas de seguridad. No le pida a un asistente de IA que "deshabilite CSP" o debilite las cabeceras solo para que una página cargue sin entender el compromiso.

## Uso Básico

### Cabeceras

Las cabeceras HTTP son una de las formas más fáciles de proteger sus aplicaciones web. Puede usar cabeceras para prevenir clickjacking, XSS y otros ataques. Hay varias formas de agregar estas cabeceras a su aplicación.

Dos excelentes sitios web para verificar la seguridad de sus cabeceras son [securityheaders.com](https://securityheaders.com/) y [observatory.mozilla.org](https://observatory.mozilla.org/). Después de configurar el código a continuación, puede verificar fácilmente que sus cabeceras funcionan con esos dos sitios web.

El esqueleto incluye `App\Middleware\SecurityHeadersMiddleware` (CSP con un nonce por solicitud, opciones de frame, HSTS y más). Prefiera extenderlo deliberadamente en lugar de desactivar las cabeceras.

#### Agregar A Mano

Puede agregar manualmente estas cabeceras usando el método `header` en el objeto `Flight\Response`.
```php
// Establece la cabecera X-Frame-Options para prevenir clickjacking
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Establece la cabecera Content-Security-Policy para prevenir XSS
// Nota: esta cabecera puede volverse muy compleja, así que querrá
//  consultar ejemplos en internet para su aplicación
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Establece la cabecera X-XSS-Protection para prevenir XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Establece la cabecera X-Content-Type-Options para prevenir el sniffing de MIME
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Establece la cabecera Referrer-Policy para controlar cuánta información del referrer se envía
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Establece la cabecera Strict-Transport-Security para forzar HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Establece la cabecera Permissions-Policy para controlar qué funciones y APIs pueden usarse
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Estas pueden agregarse al inicio de sus archivos `routes.php` o `index.php`.

#### Agregar como un Filtro

También puede agregarlas en un filtro/hook como el siguiente:

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

#### Agregar como un Middleware

También puede agregarlas como una clase middleware, lo cual proporciona la mayor flexibilidad para elegir a qué rutas aplicarlas. En general, estas cabeceras deben aplicarse a todas las respuestas HTML y de API.

Ruta y espacio de nombres estilo esqueleto (**el uso de mayúsculas de la carpeta coincide con `App\Middleware`**):

```php
// app/Middleware/SecurityHeadersMiddleware.php

namespace App\Middleware;

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
		// Prefiere un nonce CSP desde el bootstrap cuando tenga scripts inline (el esqueleto establece csp_nonce)
		$nonce = $this->app->get('csp_nonce');
		$csp = $nonce
			? "default-src 'self'; script-src 'self' 'nonce-{$nonce}'; style-src 'self' 'nonce-{$nonce}'"
			: "default-src 'self'";

		$response->header('X-Frame-Options', 'SAMEORIGIN');
		$response->header('Content-Security-Policy', $csp);
		$response->header('X-XSS-Protection', '1; mode=block');
		$response->header('X-Content-Type-Options', 'nosniff');
		$response->header('Referrer-Policy', 'no-referrer-when-downgrade');
		$response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		$response->header('Permissions-Policy', 'geolocation=()');
	}
}

// app/config/routes.php — grupo de cadena vacía = middleware global para todas las rutas
use App\Middleware\SecurityHeadersMiddleware;
use flight\net\Router;

$router->group('', function (Router $router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// más rutas
}, [SecurityHeadersMiddleware::class]);
```

Los proyectos más antiguos pueden seguir usando `app/middlewares` y `app\middlewares`; eso funciona si las carpetas coinciden. Las nuevas aplicaciones de esqueleto usan **`app/Middleware/`** y **`App\Middleware`**. Consulte [Autoloading](/learn/autoloading).

### Cross Site Request Forgery (CSRF)

Cross Site Request Forgery (CSRF) es un tipo de ataque en el que un sitio web malicioso puede hacer que el navegador de un usuario envíe una solicitud a su sitio web. Esto puede usarse para realizar acciones en su sitio web sin el conocimiento del usuario. Flight no proporciona un mecanismo de protección CSRF incorporado, pero puede implementar fácilmente el suyo propio usando middleware.

#### Configuración

Primero necesita generar un token CSRF y almacenarlo en la sesión del usuario. Luego puede usar este token en sus formularios y verificarlo cuando se envíe el formulario. Usaremos el plugin [flightphp/session](/awesome-plugins/session) para administrar las sesiones.

```php
// Genera un token CSRF y lo almacena en la sesión del usuario
// (asumiendo que ha creado un objeto de sesión y lo ha adjuntado a Flight)
// consulte la documentación de sesión para más información
Flight::register('session', flight\Session::class);

// Solo necesita generar un único token por sesión (así funciona
// en múltiples pestañas y solicitudes para el mismo usuario)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### Usando la Plantilla Predeterminada de PHP de Flight

```html
<!-- Usa el token CSRF en tu formulario -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- otros campos del formulario -->
</form>
```

##### Usando Twig (predeterminado del esqueleto)

Registre una función de Twig o pase el token a cada vista de formulario. Ejemplo mínimo con un global + campo de formulario:

```php
// Al configurar Twig (por ejemplo, services.php)
$twig->addGlobal('csrf_token', $app->session()->get('csrf_token'));
```

```html
{# app/views/form.twig #}
<form method="post">
	<input type="hidden" name="csrf_token" value="{{ csrf_token }}">
	{# otros campos #}
</form>
```

##### Usando Latte

También puede establecer una función personalizada para mostrar el token CSRF en sus plantillas Latte.

```php

Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// otras configuraciones...

	// Establece una función personalizada para mostrar el token CSRF
	$latte->addFunction('csrf', function() {
		$csrfToken = Flight::session()->get('csrf_token');
		return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
	});

	$latte->render($finalPath, $data, $block);
});
```

Y ahora en sus plantillas Latte puede usar la función `csrf()` para mostrar el token CSRF.

```html
<form method="post">
	{csrf()}
	<!-- otros campos del formulario -->
</form>
```

#### Verificar el Token CSRF

Puede verificar el token CSRF usando varios métodos.

##### Middleware

```php
// app/Middleware/CsrfMiddleware.php

namespace App\Middleware;

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

// routes.php
use App\Middleware\CsrfMiddleware;

$router->group('', function ($router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// más rutas
}, [CsrfMiddleware::class]);
```

##### Filtros de Eventos

```php
// Este middleware verifica si la solicitud es una solicitud POST y, si lo es, verifica si el token CSRF es válido
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// captura el token csrf de los valores del formulario
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

Cross Site Scripting (XSS) es un tipo de ataque en el que una entrada de formulario maliciosa puede inyectar código en su sitio web. La mayoría de estas oportunidades provienen de valores de formulario que sus usuarios finales completarán. ¡**Nunca** debe confiar en la salida de sus usuarios! Asuma siempre que todos ellos son los mejores hackers del mundo. Pueden inyectar JavaScript o HTML malicioso en su página. Este código puede usarse para robar información de sus usuarios o realizar acciones en su sitio web. Usando la clase view de Flight o un motor de plantillas como [Twig](/awesome-plugins/twig) o [Latte](/awesome-plugins/latte), puede escapar fácilmente la salida para prevenir ataques XSS.

```php
// Supongamos que el usuario es astuto e intenta usar esto como su nombre
$name = '<script>alert("XSS")</script>';

// Esto escapará la salida
Flight::view()->set('name', $name);
// Esto mostrará: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Twig (predeterminado del esqueleto) y Latte escapan automáticamente por defecto — prefíeralos sobre el echo crudo de PHP
Flight::render('template', ['name' => $name]);
// Twig: {{ name }}  → escapado
// Evite |raw / salida sin escapar a menos que el contenido sea completamente confiable
```

### Inyección SQL

La inyección SQL es un tipo de ataque en el que un usuario malicioso puede inyectar código SQL en su base de datos. Esto puede usarse para robar información de su base de datos o realizar acciones en su base de datos. Nuevamente, ¡**nunca** debe confiar en la entrada de sus usuarios! Asuma siempre que tienen malas intenciones. Use sentencias preparadas—los helpers de [SimplePdo](/learn/simple-pdo) convierten esto en el camino predeterminado.

```php
// Asumiendo que tiene Flight::db() registrado como SimplePdo (o inyecta SimplePdo en el controlador)
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// SimplePdo (preferido) — una sola línea con parámetros vinculados
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Misma idea con marcadores de posición ?
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

En controladores estilo esqueleto, prefiera la inyección por constructor de `SimplePdo` sobre `Flight::db()` para que las pruebas y el código generado por IA se mantengan consistentes ([DIC](/learn/dependency-injection-container)).

#### Ejemplo Inseguro

A continuación se explica por qué usamos sentencias preparadas de SQL para protegernos de ejemplos inocentes como el siguiente:

```php
// el usuario final completa un formulario web.
// como valor del formulario, el hacker ingresa algo como esto:
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// Una vez construida la consulta, se ve así
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// Parece extraño, pero es una consulta válida que funcionará. De hecho,
// es un ataque de inyección SQL muy común que devolverá todos los usuarios.

var_dump($users); // esto volcará todos los usuarios de la base de datos, no solo el único nombre de usuario
```

### Secretos y configuración

- Coloque los secretos en **`.env`** (o en el entorno real), no en los archivos de muestra `config.php` confirmados.
- Regla del esqueleto: valores literales por defecto en `config.php`; fusionar el entorno en el bootstrap; **no** lea `$_ENV` dentro de los controladores—inyecte la configuración en su lugar. Consulte [Configuration](/learn/configuration).
- Nunca confirme claves de API, contraseñas de BD o claves de cifrado de sesión. Indique a las herramientas de IA que consulten **`SECURITY.md`** para que no inventen atajos inseguros.

### Validación de Callback JSONP

Si usa el método `Flight::jsonp()` de Flight, tenga en cuenta que Flight valida el nombre del parámetro de callback JSONP contra una regex de lista de permitidos estricta (`/^[A-Za-z_$][\w$.]{0,127}$/`). Cualquier nombre de callback que no coincida con este patrón hará que Flight lance una excepción, previniendo la inyección de JavaScript arbitrario a través de un valor de callback malicioso.

Esta validación está incorporada y no requiere configuración adicional, pero vale la pena conocerla al depurar errores inesperados de los endpoints JSONP.

### CORS

Cross-Origin Resource Sharing (CORS) es un mecanismo que permite que muchos recursos (por ejemplo, fuentes, JavaScript, etc.) en una página web sean solicitados desde otro dominio fuera del dominio desde el cual se originó el recurso. Flight no tiene funcionalidad incorporada, pero esto puede manejarse fácilmente con un hook para ejecutarse antes de que se llame al método `Flight::start()`.

```php
// app/Utils/CorsUtil.php  (esqueleto: carpeta Utils PascalCase → App\Utils)

namespace App\Utils;

use flight\Engine;

class CorsUtil
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function set(array $params = []): void
	{
		$request = $this->app->request();
		$response = $this->app->response();
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

		$request = $this->app->request();

		if (in_array($request->getVar('HTTP_ORIGIN'), $allowed, true) === true) {
			$response = $this->app->response();
			$response->header("Access-Control-Allow-Origin", $request->getVar('HTTP_ORIGIN'));
		}
	}
}

// bootstrap / rutas — ejecutar antes de start
$app = Flight::app();
$cors = new \App\Utils\CorsUtil($app);
$app->before('start', [ $cors, 'set' ]);
```

### Endurecimiento de la Configuración de Flight

Flight expone varias configuraciones del motor que tienen implicaciones directas de seguridad. Establecerlas correctamente es una de las formas más fáciles de endurecer su aplicación.

#### `flight.allow_method_override`

Por defecto, Flight permite a los clientes sobrescribir el método HTTP de una solicitud usando la cabecera `X-HTTP-Method-Override` o un campo `_method` en el cuerpo de un POST. Aunque esto es útil para formularios HTML que solo pueden enviar `GET`/`POST`, puede ser peligroso si no lo espera—un atacante podría falsificar solicitudes `DELETE` o `PUT` a través de un formulario normal.

Si su aplicación no depende de este comportamiento (por ejemplo, está construyendo una API consumida por clientes modernos o frontends JavaScript que pueden enviar cualquier verbo HTTP), debe desactivarlo:

```php
// En tu index.php o archivo de bootstrap, antes de Flight::start()
Flight::set('flight.allow_method_override', false);
```

El valor predeterminado es `true` por compatibilidad con versiones anteriores, pero **establecerlo en `false` se recomienda encarecidamente** para cualquier aplicación que no necesite explícitamente la función de sobrescritura.

#### `flight.debug`

Flight tiene una configuración `flight.debug` que controla si se muestra en el navegador información detallada del error (mensaje de excepción, código y traza completa de la pila) cuando ocurre una excepción no manejada. El valor predeterminado es `false`, lo que significa que solo se muestra un mensaje genérico `500 Internal Server Error`—no se filtran detalles internos al cliente.

Nunca habilite esto en un servidor de producción. Úselo solo localmente o en un entorno de pruebas:

```php
// Seguro solo para desarrollo local — NUNCA en producción
Flight::set('flight.debug', true);
```

Cuando `flight.debug` es `false` (el valor predeterminado), aún puede capturar errores habilitando `flight.log_errors`:

```php
// Registra errores del lado del servidor sin exponerlos al cliente
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
```

#### `flight.views.restrict_to_path`

La clase `View` incorporada de Flight incluirá sin problema una plantilla desde una ruta absoluta, o desde un nombre relativo que sube fuera de `flight.views.path` (por ejemplo con `../`). Eso es intencional para aplicaciones que comparten deliberadamente plantillas entre carpetas, pero también es un riesgo de traversía de rutas si el nombre de una plantilla alguna vez proviene de entrada no confiable.

`flight.views.restrict_to_path` está **desactivado por defecto** para que las aplicaciones existentes sigan funcionando. Actívelo a menos que tenga una razón documentada para no hacerlo:

```php
// En tu index.php o archivo de bootstrap, antes de Flight::start()
Flight::set('flight.views.restrict_to_path', true);
```

El motor aplica esa configuración a `View::$restrictToPath` cuando se crea la vista (mismo patrón que `flight.views.path` y `flight.views.extension`). Con esto activado:

- `render()` y `fetch()` solo incluyen archivos cuya ruta real reside dentro del directorio de vistas configurado (también se rechazan los enlaces simbólicos que apuntan hacia afuera).
- `exists()` devuelve `false` para esas mismas rutas en lugar de lanzar una excepción.
- `getTemplate()` en sí mismo no cambia—sigue devolviendo rutas como siempre lo ha hecho.
- Un archivo bloqueado lanza `Template file is outside the views path.` Un archivo faltante sigue lanzando el mensaje existente `Template file not found: ...`.

Si usa Twig o Latte con sus propios cargadores de sistema de archivos apuntando a su directorio de vistas, esos motores ya contienen plantillas a ese root. Aún así active esto para la `View` nativa de Flight, para que cualquier código que llame a `Flight::view()->render()` / `fetch()` obtenga la misma protección. El [esqueleto](https://github.com/flightphp/skeleton) oficial lo habilita en el bootstrap.

#### Configuración de producción recomendada

```php
// index.php o aplicado desde la configuración de la app / bootstrap
Flight::set('flight.allow_method_override', false);
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
Flight::set('flight.views.restrict_to_path', true);
```

### Manejo de Errores
Oculte los detalles sensibles de los errores en producción para evitar filtrar información a los atacantes. En producción, registre los errores en lugar de mostrarlos con `display_errors` establecido en `0`.

```php
// En tu bootstrap.php o index.php

// agrega esto a tu app/config/config.php
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // Deshabilita la visualización de errores
    ini_set('log_errors', 1);     // Registra los errores en su lugar
    ini_set('error_log', '/path/to/error.log');
}

// En tus rutas o controladores
// Usa Flight::halt() para respuestas de error controladas
Flight::halt(403, 'Access denied');
```

### Sanitización de Entrada
Nunca confíe en la entrada del usuario. Sanitícela usando [filter_var](https://www.php.net/manual/en/function.filter-var.php) antes de procesarla para evitar que datos maliciosos se infiltren. Prefiera leer la entrada a través de `$app->request()` (o `Flight::request()`) en lugar de `$_GET` / `$_POST` en crudo en el código de la aplicación.

```php

// Supongamos una solicitud $_POST con $_POST['input'] y $_POST['email']

// Sanitiza una entrada de cadena
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// Sanitiza un correo electrónico
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

### Hashing de Contraseñas
Almacene las contraseñas de forma segura y verifíquelas de forma segura usando las funciones incorporadas de PHP como [password_hash](https://www.php.net/manual/en/function.password-hash.php) y [password_verify](https://www.php.net/manual/en/function.password-verify.php). Las contraseñas nunca deben almacenarse en texto plano, ni deben cifrarse con métodos reversibles. El hashing asegura que incluso si su base de datos se ve comprometida, las contraseñas reales permanezcan protegidas.

```php
$password = Flight::request()->data->password;
// Hashea una contraseña al almacenarla (por ejemplo, durante el registro)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Verifica una contraseña (por ejemplo, durante el inicio de sesión)
if (password_verify($password, $stored_hash)) {
    // La contraseña coincide
}
```

### Limitación de Tasa
Protéjase contra ataques de fuerza bruta o ataques de denegación de servicio limitando las tasas de solicitudes con una caché.

```php
// Asumiendo que tiene flightphp/cache instalado y registrado
// Usando flightphp/cache en un filtro
Flight::before('start', function() {
    $cache = Flight::cache();
    $ip = Flight::request()->ip;
    $key = "rate_limit_{$ip}";
    $attempts = (int) $cache->retrieve($key);
    
    if ($attempts >= 10) {
        Flight::halt(429, 'Too many requests');
    }
    
    $cache->set($key, $attempts + 1, 60); // Reinicia después de 60 segundos
});
```

## Ver También
- [Sessions](/awesome-plugins/session) - Cómo administrar sesiones de usuario de forma segura.
- [Templates](/learn/templates) - Auto-escape de Twig/Latte y XSS.
- [SimplePdo](/learn/simple-pdo) - Helpers de base de datos con sentencias preparadas.
- [PdoWrapper](/learn/pdo-wrapper) - Obsoleto; use SimplePdo para código nuevo.
- [Middleware](/learn/middleware) - Cómo usar middleware para simplificar el proceso de agregar cabeceras de seguridad.
- [Configuration](/learn/configuration) - `.env` vs configuración literal, indicadores de producción.
- [AI & Developer Experience](/learn/ai) - Mantenga la política de seguridad en `SECURITY.md` para los agentes.
- [Responses](/learn/responses) - Cómo personalizar las respuestas HTTP con cabeceras seguras.
- [Requests](/learn/requests) - Cómo manejar y sanitizar la entrada del usuario.
- [filter_var](https://www.php.net/manual/en/function.filter-var.php) - Función PHP para la sanitización de entrada.
- [password_hash](https://www.php.net/manual/en/function.password-hash.php) - Función PHP para el hashing seguro de contraseñas.
- [password_verify](https://www.php.net/manual/en/function.password-verify.php) - Función PHP para verificar contraseñas hasheadas.

## Solución de Problemas
- Consulte la sección "Ver También" anterior para obtener información sobre la solución de problemas relacionados con componentes del Framework Flight.
- Si CSP bloquea tus scripts, agrega un nonce (patrón del esqueleto) o permite orígenes específicos en la lista de permitidos—no establezcas `script-src *` sin un plan.

## Registro de Cambios
- Docs – Esqueleto `App\Middleware`, notas de CSRF/XSS de Twig, SimplePdo, secretos/`.env`, y `SECURITY.md` para proyectos compatibles con IA.
- Docs – Documentado `flight.views.restrict_to_path` bajo Endurecimiento de la Configuración de Flight (contención de rutas opcional para vistas nativas).
- v3.18.1 - Se agregó la sección de Endurecimiento de la Configuración de Flight que cubre `flight.allow_method_override`, `flight.debug` y validación de callback JSONP.
- v3.1.0 - Se agregaron secciones sobre CORS, Manejo de Errores, Sanitización de Entrada, Hashing de Contraseñas y Limitación de Tasa.
- v2.0 - Se agregó escape para las vistas predeterminadas para prevenir XSS.