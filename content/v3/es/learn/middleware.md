# Middleware de Ruta

Flight admite middleware de ruta y middleware de grupo de rutas. El middleware es una función que se ejecuta antes (o después) del callback de la ruta. Esta es una excelente manera de agregar verificaciones de autenticación de API en tu código, o para validar que el usuario tiene permiso para acceder a la ruta.

## Middleware Básico

Aquí hay un ejemplo básico:

```php
// Si solo proporcionas una función anónima, se ejecutará antes del callback de la ruta. 
// no hay funciones de middleware "después" excepto para clases (véase más abajo)
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// Esto mostrará "Middleware first! Here I am!"
```

Hay algunas notas muy importantes sobre el middleware que debes conocer antes de usarlas:
- Las funciones de middleware se ejecutan en el orden en que se agregan a la ruta. La ejecución es similar a cómo [Slim Framework maneja esto](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Los "befores" se ejecutan en el orden en que se agregan, y los "afters" se ejecutan en orden inverso.
- Si tu función de middleware devuelve false, toda la ejecución se detiene y se lanza un error de 403 Forbidden. Probablemente quieras manejar esto de manera más elegante con un `Flight::redirect()` o algo similar.
- Si necesitas parámetros de tu ruta, se pasarán en un solo arreglo a tu función de middleware. (`function($params) { ... }` o `public function before($params) {}`). La razón de esto es que puedes estructurar tus parámetros en grupos y en algunos de esos grupos, tus parámetros podrían aparecer en un orden diferente, lo que rompería la función de middleware al referirse al parámetro equivocado. De esta manera, puedes acceder a ellos por nombre en lugar de por posición.
- Si solo pasas el nombre del middleware, se ejecutará automáticamente mediante el [contenedor de inyección de dependencias](dependency-injection-container) y el middleware se ejecutará con los parámetros que necesita. Si no tienes un contenedor de inyección de dependencias registrado, pasará la instancia de `flight\Engine` en el `__construct()`.

## Clases de Middleware

El middleware también puede registrarse como una clase. Si necesitas la funcionalidad "después", **debes** usar una clase.

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
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware($MyMiddleware); // también ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// Esto mostrará "Middleware first! Here I am! Middleware last!"
```

## Manejo de Errores en Middleware

Supongamos que tienes un middleware de autenticación y quieres redirigir al usuario a una página de inicio de sesión si no está autenticado. Tienes algunas opciones a tu disposición:

1. Puedes devolver false desde la función de middleware y Flight devolverá automáticamente un error de 403 Forbidden, pero sin personalización.
1. Puedes redirigir al usuario a una página de inicio de sesión usando `Flight::redirect()`.
1. Puedes crear un error personalizado dentro del middleware y detener la ejecución de la ruta.

### Ejemplo Básico

Aquí hay un ejemplo simple de devolver false:
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			return false;
		}

		// como es true, todo continúa normalmente
	}
}
```

### Ejemplo de Redirección

Aquí hay un ejemplo de redirigir al usuario a una página de inicio de sesión:
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			Flight::redirect('/login');
			exit;
		}
	}
}
```

### Ejemplo de Error Personalizado

Supongamos que necesitas lanzar un error en JSON porque estás construyendo una API. Puedes hacerlo así:
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->headers['Authorization'];
		if(empty($authorization)) {
			Flight::jsonHalt(['error' => 'You must be logged in to access this page.'], 403);
			// o
			Flight::json(['error' => 'You must be logged in to access this page.'], 403);
			exit;
			// o
			Flight::halt(403, json_encode(['error' => 'You must be logged in to access this page.']));
		}
	}
}
```

## Agrupación de Middleware

Puedes agregar un grupo de rutas, y luego cada ruta en ese grupo tendrá el mismo middleware. Esto es útil si necesitas agrupar un montón de rutas por, digamos, un middleware de autenticación para verificar la clave de API en el encabezado.

```php
// agregado al final del método group
Flight::group('/api', function() {

	// Esta ruta "vacía" coincidirá con /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// Esto coincidirá con /api/users
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// Esto coincidirá con /api/users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

Si quieres aplicar un middleware global a todas tus rutas, puedes agregar un grupo "vacío":

```php
// agregado al final del método group
Flight::group('', function() {

	// Esto sigue siendo /users
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// Y esto sigue siendo /users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ ApiAuthMiddleware::class ]); // o [ new ApiAuthMiddleware() ], lo mismo
```