# Middleware de Ruta

Flight admite middleware de ruta y de grupo de ruta. El middleware es una función que se ejecuta antes (o después) de la devolución de llamada de la ruta. Esta es una excelente manera de agregar verificaciones de autenticación de API en su código, o para validar que el usuario tiene permiso para acceder a la ruta.

## Middleware Básico

Aquí tienes un ejemplo básico:

```php
// Si solo proporciona una función anónima, se ejecutará antes de la devolución de llamada de la ruta.
// no hay funciones de middleware "después" excepto para las clases (ver abajo)
Flight::route('/path', function() { echo '¡Aquí estoy!'; })->addMiddleware(function() {
	echo '¡Middleware primero!';
});

Flight::start();

// ¡Esto mostrará "¡Middleware primero! ¡Aquí estoy!"
```

Hay algunas notas muy importantes sobre el middleware que debes tener en cuenta antes de usarlo:
- Las funciones de middleware se ejecutan en el orden en que se agregan a la ruta. La ejecución es similar a cómo [Slim Framework maneja esto](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Los Antes se ejecutan en el orden agregado, y los Después se ejecutan en orden inverso.
- Si su función de middleware devuelve false, se detiene toda la ejecución y se lanza un error 403 Prohibido. Probablemente querrás manejar esto de manera más elegante con un `Flight::redirect()` o algo similar.
- Si necesita parámetros de su ruta, se pasarán en una sola matriz a su función de middleware. (`function($params) { ... }` o `public function before($params) {}`). La razón de esto es que puedes estructurar tus parámetros en grupos y en algunos de esos grupos, tus parámetros pueden aparecer en un orden diferente que rompería la función de middleware al hacer referencia al parámetro incorrecto. De esta manera, puedes acceder a ellos por nombre en lugar de por posición.
- Si solo pasa el nombre del middleware, se ejecutará automáticamente por el [contenedor de inyección de dependencias](dependency-injection-container) y el middleware se ejecutará con los parámetros que necesita. Si no tiene un contenedor de inyección de dependencias registrado, pasará la instancia de `flight\Engine` en `__construct()`.

## Clases de Middleware

El middleware también se puede registrar como una clase. Si necesitas la funcionalidad "después", **deb** usar una clase.

```php
class MyMiddleware {
	public function before($params) {
		echo '¡Middleware primero!';
	}

	public function after($params) {
		echo '¡Middleware último!';
	}
}

$MyMiddleware = new MyMiddleware();
Flight::route('/path', function() { echo '¡Aquí estoy! '; })->addMiddleware($MyMiddleware); // también ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// Esto mostrará "¡Middleware primero! ¡Aquí estoy! ¡Middleware último!"
```

## Manejo de Errores de Middleware

Digamos que tienes un middleware de autenticación y quieres redirigir al usuario a una página de inicio de sesión si no está autenticado. Tienes algunas opciones a tu disposición:

1. Puedes devolver false desde la función de middleware y Flight devolverá automáticamente un error 403 Prohibido, pero sin personalización.
1. Puedes redirigir al usuario a una página de inicio de sesión usando `Flight::redirect()`.
1. Puedes crear un error personalizado dentro del middleware y detener la ejecución de la ruta.

### Ejemplo Básico

Aquí tienes un ejemplo simple de return false;:
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			return false;
		}

		// dado que es verdadero, todo continúa
	}
}
```

### Ejemplo de Redirección

Aquí tienes un ejemplo de redirigir al usuario a una página de inicio de sesión:
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

Digamos que necesitas lanzar un error JSON porque estás construyendo una API. Puedes hacerlo de la siguiente manera:
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->headers['Authorization'];
		if(empty($authorization)) {
			Flight::jsonHalt(['error' => 'Debes iniciar sesión para acceder a esta página.'], 403);
			// o
			Flight::json(['error' => 'Debes iniciar sesión para acceder a esta página.'], 403);
			exit;
			// o
			Flight::halt(403, json_encode(['error' => 'Debes iniciar sesión para acceder a esta página.']);
		}
	}
}
```

## Agrupación de Middleware

Puedes agregar un grupo de rutas, y luego cada ruta en ese grupo tendrá el mismo middleware también. Esto es útil si necesitas agrupar varias rutas por un middleware de autenticación para verificar la clave de API en el encabezado.

```php

// añadido al final del método de grupo
Flight::group('/api', function() {

	// Esta ruta con aspecto "vacío" en realidad coincidirá con /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// Esto coincidirá con /api/usuarios
    Flight::route('/usuarios', function() { echo 'usuarios'; }, false, 'usuarios');
	// Esto coincidirá con /api/usuarios/1234
	Flight::route('/usuarios/@id', function($id) { echo 'usuario:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

Si deseas aplicar un middleware global a todas tus rutas, puedes agregar un grupo "vacío":

```php

// añadido al final del método de grupo
Flight::group('', function() {

	// Esto sigue siendo /usuarios
	Flight::route('/usuarios', function() { echo 'usuarios'; }, false, 'usuarios');
	// Y esto sigue siendo /usuarios/1234
	Flight::route('/usuarios/@id', function($id) { echo 'usuario:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```