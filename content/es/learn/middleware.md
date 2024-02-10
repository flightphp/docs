# Middleware de Ruta

Flight admite middleware de ruta y middleware de grupo de ruta. El middleware es una función que se ejecuta antes (o después) de la devolución de llamada de la ruta. Esta es una excelente manera de agregar controles de autenticación de API en su código, o para validar que el usuario tenga permiso para acceder a la ruta.

## Middleware Básico

Aquí hay un ejemplo básico:

```php
// Si solo proporciona una función anónima, se ejecutará antes de la devolución de llamada de la ruta.
// no hay funciones de middleware "después" excepto para clases (ver abajo)
Flight::route('/ruta', function() { echo '¡Aquí estoy!'; })->addMiddleware(function() {
	echo '¡Primero el Middleware!';
});

Flight::start();

// ¡Esto producirá "¡Primero el Middleware! ¡Aquí estoy!"
```

Hay algunas notas muy importantes sobre el middleware que debe tener en cuenta antes de usarlos:
- Las funciones de middleware se ejecutan en el orden en que se agregan a la ruta. La ejecución es similar a cómo [Slim Framework maneja esto](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Los "Befores" se ejecutan en el orden agregado y los "Afters" se ejecutan en orden inverso.
- Si su función de middleware devuelve falso, se detiene toda la ejecución y se lanza un error 403 Prohibido. Probablemente querrá manejar esto con más elegancia con un `Flight::redirect()` o algo similar.
- Si necesita parámetros de su ruta, se pasarán en una sola matriz a su función de middleware. (`function($params) { ... }` o `public function before($params) {}`). La razón de esto es que puede estructurar sus parámetros en grupos y en algunos de esos grupos, sus parámetros pueden aparecer en un orden diferente que rompería la función de middleware al hacer referencia al parámetro incorrecto. De esta manera, puede acceder a ellos por nombre en lugar de posición.

## Clases de Middleware

El middleware también se puede registrar como una clase. Si necesita la funcionalidad "después", **debe** usar una clase.

```php
class MyMiddleware {
	public function before($params) {
		echo '¡Primero el Middleware!';
	}

	public function after($params) {
		echo '¡Último el Middleware!';
	}
}

$MyMiddleware = new MyMiddleware();
Flight::route('/ruta', function() { echo '¡Aquí estoy!'; })->addMiddleware($MyMiddleware); // también ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// Esto mostrará "¡Primero el Middleware! ¡Aquí estoy! ¡Último el Middleware!"
```

## Agrupando Middleware

Puede agregar un grupo de ruta y luego cada ruta en ese grupo tendrá el mismo middleware también. Esto es útil si necesita agrupar un montón de rutas, por ejemplo, un middleware de Autenticación para verificar la clave API en la cabecera.

```php

// añadido al final del método de grupo
Flight::group('/api', function() {

	// Esta ruta con aspecto "vacío" realmente coincidirá con /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
    Flight::route('/usuarios', function() { echo 'usuarios'; }, false, 'usuarios');
	Flight::route('/usuarios/@id', function($id) { echo 'usuario:'.$id; }, false, 'ver_usuario');
}, [ new ApiAuthMiddleware() ]);
```

Si desea aplicar un middleware global a todas sus rutas, puede agregar un grupo "vacío":

```php

// añadido al final del método de grupo
Flight::group('', function() {
	Flight::route('/usuarios', function() { echo 'usuarios'; }, false, 'usuarios');
	Flight::route('/usuarios/@id', function($id) { echo 'usuario:'.$id; }, false, 'ver_usuario');
}, [ new ApiAuthMiddleware() ]);
```