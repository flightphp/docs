# Seguridad

La seguridad es fundamental cuando se trata de aplicaciones web. Quieres asegurarte de que tu aplicación sea segura y de que los datos de tus usuarios estén protegidos. Flight proporciona una serie de funciones para ayudarte a asegurar tus aplicaciones web.

## Cabeceras

Las cabeceras HTTP son una de las formas más sencillas de proteger tus aplicaciones web. Puedes usar cabeceras para prevenir el secuestro de clics, XSS y otros ataques. Hay varias formas de agregar estas cabeceras a tu aplicación.

### Agregar manualmente

Puedes añadir manualmente estas cabeceras usando el método `header` en el objeto `Flight\Response`.
```php
// Establecer la cabecera X-Frame-Options para prevenir el secuestro de clics
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Establecer la cabecera Content-Security-Policy para prevenir XSS
// Nota: esta cabecera puede volverse muy compleja, por lo que es recomendable
// consultar ejemplos en internet para tu aplicación
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Establecer la cabecera X-XSS-Protection para prevenir XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Establecer la cabecera X-Content-Type-Options para prevenir el "sniffing" MIME
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Establecer la cabecera Referrer-Policy para controlar la información de referencia enviada
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Establecer la cabecera Strict-Transport-Security para forzar HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
```

Estas pueden ser añadidas al inicio de tus archivos `bootstrap.php` o `index.php`.

### Agregar como un Filtro

También puedes agregarlas en un filtro como el siguiente:

```php
// Agregar las cabeceras en un filtro
Flight::before('start', function() {
	Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
	Flight::response()->header("Content-Security-Policy", "default-src 'self'");
	Flight::response()->header('X-XSS-Protection', '1; mode=block');
	Flight::response()->header('X-Content-Type-Options', 'nosniff');
	Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
	Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
});
```

### Agregar como un Middleware

También puedes agregarlas como una clase de middleware. Esta es una buena manera de mantener tu código limpio y organizado.

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
	}
}

// index.php o donde tengas tus rutas
// FYI, este grupo de cadena vacía actúa como un middleware global para
// todas las rutas. Por supuesto, podrías hacer lo mismo y agregar esto
// solo a rutas específicas.
Flight::group('', function(Router $router) {
	$router->get('/usuarios', [ 'ControladorUsuarios', 'obtenerUsuarios' ]);
	// más rutas
}, [ new SecurityHeadersMiddleware() ]);
```


## Falsificación de solicitudes entre sitios (CSRF)

La falsificación de solicitudes entre sitios (CSRF) es un tipo de ataque en el que un sitio web malicioso puede hacer que el navegador de un usuario envíe una solicitud a tu sitio web. Esto se puede usar para realizar acciones en tu sitio web sin el conocimiento del usuario. Flight no proporciona un mecanismo integrado de protección CSRF, pero puedes implementar fácilmente el tuyo propio utilizando middleware.

### Configuración

Primero necesitas generar un token CSRF y almacenarlo en la sesión del usuario. Luego puedes usar este token en tus formularios y verificarlo cuando se envíe el formulario.

```php
// Generar un token CSRF y almacenarlo en la sesión del usuario
// (asumiendo que has creado un objeto de sesión y lo has adjuntado a Flight)
// Solo necesitas generar un token único por sesión (así funciona 
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

También puedes configurar una función personalizada para producir el token CSRF en tus plantillas de Latte.

```php
// Configura una función personalizada para producir el token CSRF
// Nota: La vista ha sido configurada con Latte como motor de vista
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

Y ahora en tus plantillas de Latte puedes usar la función `csrf()` para producir el token CSRF.

```html
<form method="post">
	{csrf()}
	<!-- otros campos del formulario -->
</form>
```

¿Corto y simple, verdad?

### Verificar el token CSRF

Puedes verificar el token CSRF utilizando filtros de eventos:

```php
// Este middleware verifica si la solicitud es una solicitud POST y, si lo es, verifica si el token CSRF es válido
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// captura el token csrf de los valores del formulario
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Token CSRF inválido');
		}
	}
});
```

O puedes usar una clase de middleware:

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
	$router->get('/usuarios', [ 'ControladorUsuarios', 'obtenerUsuarios' ]);
	// más rutas
}, [ new CsrfMiddleware() ]);
```