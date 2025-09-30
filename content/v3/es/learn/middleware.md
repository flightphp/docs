# Middleware

## Resumen

Flight soporta middleware de rutas y grupos de rutas. El middleware es una parte de tu aplicación donde se ejecuta código antes 
(o después) de la devolución de llamada de la ruta. Esta es una excelente manera de agregar verificaciones de autenticación de API en tu código, o para validar que 
el usuario tiene permiso para acceder a la ruta.

## Entendimiento

El middleware puede simplificar enormemente tu aplicación. En lugar de herencia compleja de clases abstractas o sobrescrituras de métodos, el middleware 
te permite controlar tus rutas asignando tu lógica de aplicación personalizada a ellas. Puedes pensar en el middleware como
un sándwich. Tienes pan por fuera, y luego capas de ingredientes como lechuga, tomates, carnes y queso. Luego imagina
que cada solicitud es como tomar un bocado del sándwich donde comes las capas externas primero y avanzas hacia el centro.

Aquí hay una visualización de cómo funciona el middleware. Luego te mostraremos un ejemplo práctico de cómo funciona esto.

```text
Solicitud de usuario en URL /api ----> 
	Middleware->before() ejecutado ----->
		Callable/método adjunto a /api ejecutado y respuesta generada ------>
	Middleware->after() ejecutado ----->
Usuario recibe respuesta del servidor
```

Y aquí hay un ejemplo práctico:

```text
Usuario navega a URL /dashboard
	LoggedInMiddleware->before() se ejecuta
		before() verifica una sesión de inicio de sesión válida
			si sí, no hace nada y continúa la ejecución
			si no, redirige al usuario a /login
				Callable/método adjunto a /api ejecutado y respuesta generada
	LoggedInMiddleware->after() no tiene nada definido, así que deja que la ejecución continúe
Usuario recibe HTML del dashboard del servidor
```

### Orden de Ejecución

Las funciones de middleware se ejecutan en el orden en que se agregan a la ruta. La ejecución es similar a cómo [Slim Framework maneja esto](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).

Los métodos `before()` se ejecutan en el orden agregado, y los métodos `after()` se ejecutan en orden inverso.

Ej: Middleware1->before(), Middleware2->before(), Middleware2->after(), Middleware1->after().

## Uso Básico

Puedes usar middleware como cualquier método callable, incluyendo una función anónima o una clase (recomendado)

### Función Anónima

Aquí hay un ejemplo simple:

```php
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// Esto imprimirá "Middleware first! Here I am!"
```

> **Nota:** Cuando uses una función anónima, el único método que se interpreta es un método `before()`. **No puedes** definir comportamiento `after()` con una clase anónima.

### Usando Clases

El middleware puede (y debe) registrarse como una clase. Si necesitas la funcionalidad "after", **debes** usar una clase.

```php
class MyMiddleware {
	public function before($params) {
		echo 'Middleware first!';
	}

	public function after($params) {
		echo 'Middleware last!';
	}
}

$MyMiddleware = new MyMiddleware();
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware($MyMiddleware); 
// también ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// Esto mostrará "Middleware first! Here I am! Middleware last!"
```

También puedes definir solo el nombre de la clase de middleware y se instanciará la clase.

```php
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware(MyMiddleware::class); 
```

> **Nota:** Si pasas solo el nombre del middleware, se ejecutará automáticamente por el [contenedor de inyección de dependencias](dependency-injection-container) y el middleware se ejecutará con los parámetros que necesita. Si no tienes un contenedor de inyección de dependencias registrado, pasará por defecto la instancia de `flight\Engine` en el `__construct(Engine $app)`.

### Usando Rutas con Parámetros

Si necesitas parámetros de tu ruta, se pasarán en un solo array a tu función de middleware. (`function($params) { ... }` o `public function before($params) { ... }`). La razón de esto es que puedes estructurar tus parámetros en grupos y en algunos de esos grupos, tus parámetros pueden aparecer en un orden diferente, lo que rompería la función de middleware al referirse al parámetro incorrecto. De esta manera, puedes acceder a ellos por nombre en lugar de por posición.

```php
use flight\Engine;

class RouteSecurityMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$clientId = $params['clientId'];

		// jobId puede o no ser pasado
		$jobId = $params['jobId'] ?? 0;

		// tal vez si no hay ID de trabajo, no necesitas buscar nada.
		if($jobId === 0) {
			return;
		}

		// realiza una búsqueda de algún tipo en tu base de datos
		$isValid = !!$this->app->db()->fetchField("SELECT 1 FROM client_jobs WHERE client_id = ? AND job_id = ?", [ $clientId, $jobId ]);

		if($isValid !== true) {
			$this->app->halt(400, 'You are blocked, muahahaha!');
		}
	}
}

// routes.php
$router->group('/client/@clientId/job/@jobId', function(Router $router) {

	// Este grupo de abajo aún obtiene el middleware padre
	// Pero los parámetros se pasan en un solo array 
	// en el middleware.
	$router->group('/job/@jobId', function(Router $router) {
		$router->get('', [ JobController::class, 'view' ]);
		$router->put('', [ JobController::class, 'update' ]);
		$router->delete('', [ JobController::class, 'delete' ]);
		// más rutas...
	});
}, [ RouteSecurityMiddleware::class ]);
```

### Agrupando Rutas con Middleware

Puedes agregar un grupo de rutas, y luego cada ruta en ese grupo tendrá el mismo middleware también. Esto es 
útil si necesitas agrupar un montón de rutas por, digamos, un middleware de Auth para verificar la clave API en el encabezado.

```php

// agregado al final del método de grupo
Flight::group('/api', function() {

	// Esta ruta "vacía" coincidirá realmente con /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// Esto coincidirá con /api/users
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// Esto coincidirá con /api/users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

Si quieres aplicar un middleware global a todas tus rutas, puedes agregar un grupo "vacío":

```php

// agregado al final del método de grupo
Flight::group('', function() {

	// Esto sigue siendo /users
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// Y esto sigue siendo /users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ ApiAuthMiddleware::class ]); // o [ new ApiAuthMiddleware() ], lo mismo
```

### Casos de Uso Comunes

#### Validación de Clave API
Si quisieras proteger tus rutas `/api` verificando que la clave API sea correcta, puedes manejarlo fácilmente con middleware.

```php
use flight\Engine;

class ApiMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}
	
	public function before(array $params) {
		$authorizationHeader = $this->app->request()->getHeader('Authorization');
		$apiKey = str_replace('Bearer ', '', $authorizationHeader);

		// realiza una búsqueda en tu base de datos para la clave api
		$apiKeyHash = hash('sha256', $apiKey);
		$hasValidApiKey = !!$this->db()->fetchField("SELECT 1 FROM api_keys WHERE hash = ? AND valid_date >= NOW()", [ $apiKeyHash ]);

		if($hasValidApiKey !== true) {
			$this->app->jsonHalt(['error' => 'Invalid API Key']);
		}
	}
}

// routes.php
$router->group('/api', function(Router $router) {
	$router->get('/users', [ ApiController::class, 'getUsers' ]);
	$router->get('/companies', [ ApiController::class, 'getCompanies' ]);
	// más rutas...
}, [ ApiMiddleware::class ]);
```

¡Ahora todas tus rutas API están protegidas por este middleware de validación de clave API que has configurado! Si pones más rutas en el grupo del router, tendrán instantáneamente la misma protección!

#### Validación de Inicio de Sesión

¿Quieres proteger algunas rutas para que solo estén disponibles para usuarios que han iniciado sesión? ¡Eso se puede lograr fácilmente con middleware!

```php
use flight\Engine;

class LoggedInMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$session = $this->app->session();
		if($session->get('logged_in') !== true) {
			$this->app->redirect('/login');
			exit;
		}
	}
}

// routes.php
$router->group('/admin', function(Router $router) {
	$router->get('/dashboard', [ DashboardController::class, 'index' ]);
	$router->get('/clients', [ ClientController::class, 'index' ]);
	// más rutas...
}, [ LoggedInMiddleware::class ]);
```

#### Validación de Parámetro de Ruta

¿Quieres proteger a tus usuarios de cambiar valores en la URL para acceder a datos que no deberían? ¡Eso se puede resolver con middleware!

```php
use flight\Engine;

class RouteSecurityMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$clientId = $params['clientId'];
		$jobId = $params['jobId'];

		// realiza una búsqueda de algún tipo en tu base de datos
		$isValid = !!$this->app->db()->fetchField("SELECT 1 FROM client_jobs WHERE client_id = ? AND job_id = ?", [ $clientId, $jobId ]);

		if($isValid !== true) {
			$this->app->halt(400, 'You are blocked, muahahaha!');
		}
	}
}

// routes.php
$router->group('/client/@clientId/job/@jobId', function(Router $router) {
	$router->get('', [ JobController::class, 'view' ]);
	$router->put('', [ JobController::class, 'update' ]);
	$router->delete('', [ JobController::class, 'delete' ]);
	// más rutas...
}, [ RouteSecurityMiddleware::class ]);
```

## Manejo de la Ejecución de Middleware

Supongamos que tienes un middleware de autenticación y quieres redirigir al usuario a una página de inicio de sesión si no está 
autenticado. Tienes un par de opciones a tu disposición:

1. Puedes devolver false desde la función de middleware y Flight devolverá automáticamente un error 403 Forbidden, pero sin personalización.
1. Puedes redirigir al usuario a una página de inicio de sesión usando `Flight::redirect()`.
1. Puedes crear un error personalizado dentro del middleware y detener la ejecución de la ruta.

### Simple y Directo

Aquí hay un ejemplo simple de `return false;` :

```php
class MyMiddleware {
	public function before($params) {
		$hasUserKey = Flight::session()->exists('user');
		if ($hasUserKey === false) {
			return false;
		}

		// ya que es verdadero, todo sigue adelante
	}
}
```

### Ejemplo de Redirección

Aquí hay un ejemplo de redirigir al usuario a una página de inicio de sesión:
```php
class MyMiddleware {
	public function before($params) {
		$hasUserKey = Flight::session()->exists('user');
		if ($hasUserKey === false) {
			Flight::redirect('/login');
			exit;
		}
	}
}
```

### Ejemplo de Error Personalizado

Supongamos que necesitas lanzar un error JSON porque estás construyendo una API. Puedes hacerlo así:
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->getHeader('Authorization');
		if(empty($authorization)) {
			Flight::jsonHalt(['error' => 'You must be logged in to access this page.'], 403);
			// o
			Flight::json(['error' => 'You must be logged in to access this page.'], 403);
			exit;
			// o
			Flight::halt(403, json_encode(['error' => 'You must be logged in to access this page.']);
		}
	}
}
```

## Ver También
- [Routing](/learn/routing) - Cómo mapear rutas a controladores y renderizar vistas.
- [Requests](/learn/requests) - Entendiendo cómo manejar solicitudes entrantes.
- [Responses](/learn/responses) - Cómo personalizar respuestas HTTP.
- [Dependency Injection](/learn/dependency-injection-container) - Simplificando la creación y gestión de objetos en rutas.
- [Why a Framework?](/learn/why-frameworks) - Entendiendo los beneficios de usar un framework como Flight.
- [Middleware Execution Strategy Example](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work)

## Solución de Problemas
- Si tienes una redirección en tu middleware, pero tu aplicación no parece estar redirigiendo, asegúrate de agregar una declaración `exit;` en tu middleware.

## Registro de Cambios
- v3.1: Agregado soporte para middleware.