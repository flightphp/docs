# Seguridad

## Resumen

La seguridad es un tema importante en las aplicaciones web. Debes asegurarte de que tu aplicación sea segura y de que los datos de tus usuarios estén 
a salvo. Flight proporciona una serie de características para ayudarte a proteger tus aplicaciones web.

El [skeleton](https://github.com/flightphp/skeleton) oficial también incluye un **`SECURITY.md`** dedicado y middleware de cabeceras de seguridad para que las [herramientas de codificación con IA](/learn/ai) (y los humanos) tengan un lugar deliberado para secretos, cabeceras y reglas XSS/SQL, separado del estilo de codificación general en `AGENTS.md`.

## Comprensión

Existen una serie de amenazas de seguridad comunes que debes tener en cuenta al crear aplicaciones web. Algunas de las amenazas más comunes 
incluyen:
- Cross Site Request Forgery (CSRF)
- Cross Site Scripting (XSS)
- Inyección SQL
- Intercambio de Recursos de Origen Cruzado (CORS)

Las [plantillas](/learn/templates) ayudan con XSS al escapar la salida de forma predeterminada (Twig y Latte lo hacen; aprovecha esa ventaja). Las [sesiones](/awesome-plugins/session) pueden ayudar con CSRF al almacenar un token CSRF en la sesión del usuario como se describe a continuación. El uso de sentencias preparadas con PDO (o los ayudantes de [SimplePdo](/learn/simple-pdo)) ayuda a prevenir la inyección SQL. CORS se puede manejar con un simple hook antes de que se llame a `Flight::start()`.

Todos estos métodos funcionan juntos para ayudar a mantener seguras tus aplicaciones web. Siempre debes tener presente aprender y entender las mejores prácticas de seguridad. No le pidas a un asistente de IA que "desactive CSP" o que debilite las cabeceras solo para que una página cargue sin entender la compensación.

## Uso Básico

### Cabeceras

Las cabeceras HTTP son una de las formas más fáciles de asegurar tus aplicaciones web. Puedes usar cabeceras para prevenir clickjacking, XSS y otros ataques. 
Hay varias formas de añadir estas cabeceras a tu aplicación.

Dos excelentes sitios web para verificar la seguridad de tus cabeceras son [securityheaders.com](https://securityheaders.com/) y 
[observatory.mozilla.org](https://observatory.mozilla.org/). Después de configurar el código siguiente, puedes verificar fácilmente que tus cabeceras funcionan con esos dos sitios web.

El skeleton incluye `App\Middleware\SecurityHeadersMiddleware` (CSP con un nonce por solicitud, opciones de frame, HSTS y más). Prefiere extender eso deliberadamente antes que desactivar cabeceras.

#### Añadir Manualmente

Puedes añadir estas cabeceras manualmente usando el método `header` en el objeto `Flight\Response`.
```php
// Establece la cabecera X-Frame-Options para evitar clickjacking
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Establece la cabecera Content-Security-Policy para prevenir XSS
// Nota: esta cabecera puede volverse muy compleja, así que querrás
//  consultar ejemplos en internet para tu aplicación
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Establece la cabecera X-XSS-Protection para prevenir XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Establece la cabecera X-Content-Type-Options para prevenir la detección MIME
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Establece la cabecera Referrer-Policy para controlar cuánta información de referente se envía
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Establece la cabecera Strict-Transport-Security para forzar HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Establece la cabecera Permissions-Policy para controlar qué características y APIs se pueden usar
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

Estas se pueden añadir al inicio de tus archivos `routes.php` o `index.php`.

#### Añadir como Filtro

También puedes añadirlas en un filtro/hook de la siguiente manera:

```php
// Añade las cabeceras en un filtro
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

#### Añadir como Middleware

También puedes añadirlas como una clase de middleware que proporciona la mayor flexibilidad sobre a qué rutas aplicar esto. En general, estas cabeceras deben aplicarse a todas las respuestas HTML y API.

Ruta y espacio de nombres estilo skeleton (**las mayúsculas de las carpetas coinciden con `App\Middleware`**):

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
		// Prefiere un nonce CSP del bootstrap cuando tengas scripts en línea (el skeleton establece csp_nonce)
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

// app/config/routes.php — el grupo con cadena vacía = middleware global para todas las rutas
use App\Middleware\SecurityHeadersMiddleware;
use flight\net\Router;

$router->group('', function (Router $router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// más rutas
}, [SecurityHeadersMiddleware::class]);
```

Los proyectos antiguos pueden seguir usando `app/middlewares` y `app\middlewares`; eso funciona si las carpetas coinciden. Las nuevas aplicaciones skeleton usan **`app/Middleware/`** y **`App\Middleware`**. Consulta [Autoloading](/learn/autoloading).

### Cross Site Request Forgery (CSRF)

Cross Site Request Forgery (CSRF) es un tipo de ataque en el que un sitio web malicioso puede hacer que el navegador de un usuario envíe una solicitud a tu sitio web. 
Esto se puede usar para realizar acciones en tu sitio web sin el conocimiento del usuario. Flight no proporciona un mecanismo de protección CSRF 
incorporado, pero puedes implementarlo fácilmente por tu cuenta usando middleware.

#### Configuración

Primero necesitas generar un token CSRF y almacenarlo en la sesión del usuario. Luego puedes usar este token en tus formularios y verificarlo cuando 
se envíe el formulario. Usaremos el plugin [flightphp/session](/awesome-plugins/session) para gestionar las sesiones.

```php
// Genera un token CSRF y guárdalo en la sesión del usuario
// (asumiendo que has creado un objeto de sesión y lo has adjuntado a Flight)
// consulta la documentación de sesiones para más información
Flight::register('session', flight\Session::class);

// Solo necesitas generar un único token por sesión (para que funcione
// en múltiples pestañas y solicitudes del mismo usuario)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### Usando la Plantilla PHP Predeterminada de Flight

```html
<!-- Usa el token CSRF en tu formulario -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- otros campos del formulario -->
</form>
```

##### Usando Twig (predeterminado del skeleton)

Registra una función de Twig o pasa el token a cada vista de formulario. Ejemplo mínimo con un global y un campo de formulario:

```php
// Al configurar Twig (ej. services.php)
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

También puedes configurar una función personalizada para generar el token CSRF en tus plantillas Latte.

```php

Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// otras configuraciones...

	// Configura una función personalizada para generar el token CSRF
	$latte->addFunction('csrf', function() {
		$csrfToken = Flight::session()->get('csrf_token');
		return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
	});

	$latte->render($finalPath, $data, $block);
});
```

Y ahora en tus plantillas Latte puedes usar la función `csrf()` para generar el token CSRF.

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
				$this->app->halt(403, 'Token CSRF no válido');
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
// Este middleware verifica si la solicitud es POST y, si es así, comprueba si el token CSRF es válido
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// captura el token csrf de los valores del formulario
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Token CSRF no válido');
			// o para una respuesta JSON
			Flight::jsonHalt(['error' => 'Token CSRF no válido'], 403);
		}
	}
});
```

### Cross Site Scripting (XSS)

Cross Site Scripting (XSS) es un tipo de ataque en el que una entrada de formulario maliciosa puede inyectar código en tu sitio web. La mayoría de estas oportunidades provienen 
de valores de formularios que tus usuarios finales completarán. ¡Nunca debes **confiar** en la salida de tus usuarios! Asume siempre que todos ellos son los 
mejores hackers del mundo. Pueden inyectar JavaScript o HTML malicioso en tu página. Este código se puede usar para robar información de tus 
usuarios o realizar acciones en tu sitio web. Usando la clase de vista de Flight o un motor de plantillas como [Twig](/awesome-plugins/twig) o [Latte](/awesome-plugins/latte), puedes escapar fácilmente la salida para prevenir ataques XSS.

```php
// Supongamos que el usuario es listo y trata de usar esto como su nombre
$name = '<script>alert("XSS")</script>';

// Esto escapará la salida
Flight::view()->set('name', $name);
// Esto mostrará: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Twig (predeterminado del skeleton) y Latte escapan automáticamente de forma predeterminada — prefierelos sobre el echo PHP crudo
Flight::render('template', ['name' => $name]);
// Twig: {{ name }}  → escapado
// Evita |raw / salida sin escapar a menos que el contenido sea totalmente confiable
```

### Inyección SQL

La Inyección SQL es un tipo de ataque en el que un usuario malicioso puede inyectar código SQL en tu base de datos. Esto se puede usar para robar información 
de tu base de datos o realizar acciones en tu base de datos. Nuevamente, ¡nunca debes **confiar** en la entrada de tus usuarios! Asume siempre que buscan 
tu sangre. Usa sentencias preparadas — los ayudantes de [SimplePdo](/learn/simple-pdo) hacen que este sea el camino predeterminado.

```php
// Asumiendo que tienes Flight::db() registrado como SimplePdo (o inyecta SimplePdo en el controlador)
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// SimplePdo (preferido) — una sola línea con parámetros vinculados
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// Misma idea con placeholders ?
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

En los controladores estilo skeleton, prefiere la inyección por constructor de `SimplePdo` sobre `Flight::db()` para que las pruebas y el código generado por IA sean consistentes ([DIC](/learn/dependency-injection-container)).

#### Ejemplo Inseguro

Esto es por lo que usamos sentencias preparadas SQL para protegernos de ejemplos inocentes como el siguiente:

```php
// el usuario final completa un formulario web.
// para el valor del formulario, el hacker pone algo como esto:
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// Después de construir la consulta se ve así
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// Parece extraño, pero es una consulta válida que funcionará. De hecho,
// es un ataque de inyección SQL muy común que devolverá todos los usuarios.

var_dump($users); // esto mostrará todos los usuarios en la base de datos, no solo el único nombre de usuario
```

### Secretos y configuración

- Pon los secretos en **`.env`** (o en el entorno real), no en ejemplos de `config.php` que se hayan subido al repositorio.
- Regla del skeleton: valores predeterminados literales en `config.php`; combina el entorno en el bootstrap; **no** leas `$_ENV` dentro de los controladores — inyecta la configuración en su lugar. Consulta [Configuration](/learn/configuration).
- Nunca subas claves de API, contraseñas de base de datos o claves de cifrado de sesión. Apunta las herramientas de IA a **`SECURITY.md`** para que no inventen atajos inseguros.

### Validación de Callback JSONP

Si usas el método `Flight::jsonp()`, ten en cuenta que Flight valida el nombre del parámetro callback JSONP contra una lista blanca estricta de expresiones regulares (`/^[A-Za-z_$][\w$.]{0,127}$/`). Cualquier nombre de callback que no coincida con este patrón hará que Flight lance una excepción, evitando la inyección de JavaScript arbitrario mediante un valor de callback malicioso.

Esta validación está integrada y no requiere configuración adicional, pero es útil saberlo al depurar errores inesperados de endpoints JSONP.

### CORS

Cross-Origin Resource Sharing (CORS) es un mecanismo que permite que muchos recursos (por ejemplo, fuentes, JavaScript, etc.) en una página web sean 
solicitados desde otro dominio fuera del dominio del cual se originó el recurso. Flight no tiene funcionalidad incorporada, 
pero esto puede manejarse fácilmente con un hook que se ejecute antes de que se llame al método `Flight::start()`.

```php
// app/Utils/CorsUtil.php  (skeleton: carpeta Utils en PascalCase → App\Utils)

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
		// personaliza aquí tus hosts permitidos.
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

// bootstrap / routes — ejecutar antes de start
$app = Flight::app();
$cors = new \App\Utils\CorsUtil($app);
$app->before('start', [ $cors, 'set' ]);
```

### Endurecimiento de la Configuración de Flight

Flight expone varias configuraciones del motor que tienen implicaciones directas en la seguridad. Configurarlas correctamente es una de las formas más fáciles de proteger tu aplicación.

#### `flight.allow_method_override`

De forma predeterminada, Flight permite que los clientes sobrescriban el método HTTP de una solicitud usando la cabecera `X-HTTP-Method-Override` o un campo `_method` en el cuerpo de una solicitud POST. Aunque esto es útil para formularios HTML que solo pueden enviar `GET`/`POST`, puede ser peligroso si no lo esperas — un atacante podría falsificar solicitudes `DELETE` o `PUT` a través de un formulario normal.

Si tu aplicación no depende de este comportamiento (por ejemplo, si estás construyendo una API consumida por clientes modernos o frontends JavaScript que pueden enviar cualquier verbo HTTP), debes desactivarlo:

```php
// En tu index.php o archivo bootstrap, antes de Flight::start()
Flight::set('flight.allow_method_override', false);
```

El valor predeterminado es `true` por compatibilidad hacia atrás, pero **se recomienda encarecidamente configurarlo en `false`** para cualquier aplicación que no necesite explícitamente la función de sobrescritura.

#### `flight.debug`

Flight tiene una configuración `flight.debug` que controla si la información de error detallada (mensaje de excepción, código y traza completa de la pila) se muestra en el navegador cuando ocurre una excepción no controlada. El valor predeterminado es `false`, lo que significa que solo se muestra un mensaje genérico `500 Internal Server Error` — no se filtran detalles internos al cliente.

Nunca lo habilites en un servidor de producción. Úsalo solo localmente o en un entorno de preparación:

```php
// Seguro solo para desarrollo local — NUNCA en producción
Flight::set('flight.debug', true);
```

Cuando `flight.debug` es `false` (el valor predeterminado), aún puedes capturar errores habilitando `flight.log_errors`:

```php
// Registra errores en el servidor sin exponerlos al cliente
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
```

#### `flight.views.restrict_to_path`

La clase `View` incorporada de Flight incluirá felizmente una plantilla desde una ruta absoluta, o desde un nombre relativo que salga de `flight.views.path` (por ejemplo, con `../`). Esto es intencional para aplicaciones que comparten plantillas deliberadamente entre carpetas, pero también es un riesgo de path traversal si un nombre de plantilla alguna vez proviene de entrada no confiable.

`flight.views.restrict_to_path` está **desactivado de forma predeterminada** para que las aplicaciones existentes sigan funcionando. Actívalo a menos que tengas una razón documentada para no hacerlo:

```php
// En tu index.php o archivo bootstrap, antes de Flight::start()
Flight::set('flight.views.restrict_to_path', true);
```

El motor aplica esa configuración a `View::$restrictToPath` cuando se crea la vista (mismo patrón que `flight.views.path` y `flight.views.extension`). Con esto activado:

- `render()` y `fetch()` solo incluyen archivos cuya ruta real esté dentro del directorio de vistas configurado (los enlaces simbólicos que apunten fuera también se rechazan).
- `exists()` devuelve `false` para esas mismas rutas en lugar de lanzar una excepción.
- `getTemplate()` en sí no cambia — sigue devolviendo las rutas como siempre lo ha hecho.
- Un archivo bloqueado lanza `Template file is outside the views path.` Un archivo faltante aún lanza el mensaje existente `Template file not found: ...`.

Si usas Twig o Latte con sus propios cargadores de sistema de archivos apuntando a tu directorio de vistas, esos motores ya contienen plantillas para esa raíz. Aún así, activa esto para la `View` nativa de Flight para que cualquier código que llame a `Flight::view()->render()` / `fetch()` reciba la misma protección. El [skeleton](https://github.com/flightphp/skeleton) oficial lo habilita en el bootstrap.

#### Configuración de producción recomendada

```php
// index.php o aplicado desde la configuración de la aplicación / bootstrap
Flight::set('flight.allow_method_override', false);
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
Flight::set('flight.views.restrict_to_path', true);
```

### Manejo de Errores
Oculta los detalles de error sensibles en producción para evitar filtrar información a los atacantes. En producción, registra los errores en lugar de mostrarlos con `display_errors` configurado en `0`.

```php
// En tu bootstrap.php o index.php

// añade esto a tu app/config/config.php
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // Desactiva la visualización de errores
    ini_set('log_errors', 1);     // Registra errores en su lugar
    ini_set('error_log', '/path/to/error.log');
}

// En tus rutas o controladores
// Usa Flight::halt() para respuestas de error controladas
Flight::halt(403, 'Acceso denegado');
```

### Saneamiento de Entradas
Nunca confíes en la entrada del usuario. Sanéala usando [filter_var](https://www.php.net/manual/en/function.filter-var.php) antes de procesarla para evitar que datos maliciosos se cuelen. Prefiere leer la entrada a través de `$app->request()` (o `Flight::request()`) en lugar de `$_GET` / `$_POST` crudos en el código de la aplicación.

```php

// Supongamos una solicitud $_POST con $_POST['input'] y $_POST['email']

// Sanear una entrada de cadena
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// Sanear un email
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

### Hash de Contraseñas
Almacena las contraseñas de forma segura y verifícalas de manera segura usando las funciones integradas de PHP como [password_hash](https://www.php.net/manual/en/function.password-hash.php) y [password_verify](https://www.php.net/manual/en/function.password-verify.php). Las contraseñas nunca deben almacenarse en texto plano, ni deben cifrarse con métodos reversibles. El hash asegura que incluso si tu base de datos se ve comprometida, las contraseñas reales permanezcan protegidas.

```php
$password = Flight::request()->data->password;
// Hashea una contraseña al almacenarla (ej., durante el registro)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Verifica una contraseña (ej., durante el inicio de sesión)
if (password_verify($password, $stored_hash)) {
    // La contraseña coincide
}
```

### Limitación de Velocidad (Rate Limiting)
Protégete contra ataques de fuerza bruta o ataques de denegación de servicio limitando las velocidades de solicitud con una caché.

```php
// Asumiendo que tienes flightphp/cache instalado y registrado
// Usando flightphp/cache en un filtro
Flight::before('start', function() {
    $cache = Flight::cache();
    $ip = Flight::request()->ip;
    $key = "rate_limit_{$ip}";
    $attempts = (int) $cache->retrieve($key);
    
    if ($attempts >= 10) {
        Flight::halt(429, 'Demasiadas solicitudes');
    }
    
    $cache->set($key, $attempts + 1, 60); // Restablecer después de 60 segundos
});
```

## Ver También
- [Sessions](/awesome-plugins/session) - Cómo gestionar las sesiones de usuario de forma segura.
- [Templates](/learn/templates) - Autoescape de Twig/Latte y XSS.
- [SimplePdo](/learn/simple-pdo) - Ayudantes de base de datos con sentencias preparadas.
- [PdoWrapper](/learn/pdo-wrapper) - Obsoleto; usa SimplePdo para código nuevo.
- [Middleware](/learn/middleware) - Cómo usar middleware para simplificar el proceso de agregar cabeceras de seguridad.
- [Configuration](/learn/configuration) - `.env` vs configuración literal, banderas de producción.
- [AI & Developer Experience](/learn/ai) - Mantén la política de seguridad en `SECURITY.md` para los agentes.
- [Responses](/learn/responses) - Cómo personalizar las respuestas HTTP con cabeceras seguras.
- [Requests](/learn/requests) - Cómo manejar y sanear la entrada del usuario.
- [filter_var](https://www.php.net/manual/en/function.filter-var.php) - Función de PHP para saneamiento de entradas.
- [password_hash](https://www.php.net/manual/en/function.password-hash.php) - Función de PHP para hash seguro de contraseñas.
- [password_verify](https://www.php.net/manual/en/function.password-verify.php) - Función de PHP para verificar contraseñas con hash.

## Solución de Problemas
- Consulta la sección "Ver También" anterior para obtener información sobre la solución de problemas relacionada con los componentes del Framework Flight.
- Si CSP bloquea tus scripts, agrega un nonce (patrón del skeleton) o permite orígenes específicos en la lista blanca—no establezcas `script-src *` sin un plan.

## Historial de Cambios
- Docs – Skeleton `App\Middleware`, notas de CSRF/XSS de Twig, SimplePdo, secretos/`.env` y `SECURITY.md` para proyectos amigables con IA.
- Docs – Documentado `flight.views.restrict_to_path` bajo Endurecimiento de la Configuración de Flight (contención de ruta opcional para vistas nativas).
- v3.18.1 - Se agregó la sección Endurecimiento de la Configuración de Flight que cubre `flight.allow_method_override`, `flight.debug` y validación de callback JSONP.
- v3.1.0 - Se agregaron secciones sobre CORS, Manejo de Errores, Saneamiento de Entradas, Hash de Contraseñas y Limitación de Velocidad.
- v2.0 - Se agregó escape para vistas predeterminadas para prevenir XSS.