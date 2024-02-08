# Middleware de Ruta

Flight admite middleware de ruta y middleware de grupo de rutas. El middleware es una función que se ejecuta antes (o después) de la devolución de llamada de la ruta. Esta es una excelente manera de agregar comprobaciones de autenticación de API en su código, o para validar que el usuario tiene permiso para acceder a la ruta.

## Middleware Básico

Aquí tienes un ejemplo básico:

```php
// Si solo proporcionas una función anónima, se ejecutará antes de la devolución de llamada de la ruta.
// no hay funciones de middleware "después" excepto para clases (ver más abajo)
Flight::route('/ruta', function() { echo '¡Aquí estoy!'; })->addMiddleware(function() {
	echo '¡Primero el Middleware!';
});

Flight::start();

// ¡Esto mostrará "¡Primero el Middleware! ¡Aquí estoy!"
```

Hay algunas notas muy importantes sobre el middleware que debes tener en cuenta antes de usarlos:
- Las funciones de middleware se ejecutan en el orden en que se agregan a la ruta. La ejecución es similar a cómo [Slim Framework maneja esto](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work).
   - Los "Befores" se ejecutan en el orden agregado y los "Afters" se ejecutan en orden inverso.
- Si tu función de middleware devuelve falso, se detiene toda la ejecución y se lanza un error 403 Prohibido. Probablemente querrás manejar esto de forma más elegante con un `Flight::redirect()` o algo similar.
- Si necesitas parámetros de tu ruta, se pasarán en un solo array a tu función de middleware. (`function($params) { ... }` o `public function before($params) {}`). La razón de esto es que puedes estructurar tus parámetros en grupos y en algunos de esos grupos, tus parámetros pueden aparecer en un orden diferente, lo que rompería la función de middleware al hacer referencia al parámetro incorrecto. De esta forma, puedes acceder a ellos por nombre en lugar de por posición.

## Clases de Middleware

El middleware también se puede registrar como una clase. Si necesitas la funcionalidad "después", **debes** usar una clase.

```php
class MiMiddleware {
	public function before($params) {
		echo '¡Primero el Middleware!';
	}

	public function after($params) {
		echo '¡Último el Middleware!';
	}
}

$MiMiddleware = new MiMiddleware();
Flight::route('/ruta', function() { echo '¡Aquí estoy! '; })->addMiddleware($MiMiddleware); // también ->addMiddleware([ $MiMiddleware, $MiMiddleware2 ]);

Flight: :start();

// Esto mostrará "¡Primero el Middleware! ¡Aquí estoy! ¡Último el Middleware!"
```

## Agrupación de Middleware

Puedes agregar un grupo de rutas y luego cada ruta en ese grupo tendrá el mismo middleware también. Esto es útil si necesitas agrupar un montón de rutas, por ejemplo, para un middleware de autenticación de API para verificar la clave API en la cabecera.

```php

// agregado al final del método de grupo
Flight::group('/api', function() {

	// Esta ruta de aspecto "vacío" realmente coincidirá con /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
    Flight::route('/usuarios', function() { echo 'usuarios'; }, false, 'usuarios');
	Flight::route('/usuarios/@id', function($id) { echo 'usuario:'.$id; }, false, 'ver_usuario');
}, [ new ApiAuthMiddleware() ]);
```

Si deseas aplicar un middleware global a todas tus rutas, puedes agregar un grupo "vacío":

```php

// agregado al final del método de grupo
Flight::group('', function() {
	Flight::route('/usuarios', function() { echo 'usuarios'; }, false, 'usuarios');
	Flight::route('/usuarios/@id', function($id) { echo 'usuario:'.$id; }, false, 'ver_usuario');
}, [ new ApiAuthMiddleware() ]);
```