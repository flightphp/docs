# Seguridad

La seguridad es fundamental cuando se trata de aplicaciones web. Quieres asegurarte de que tu aplicación sea segura y de que los datos de tus usuarios estén protegidos. Flight proporciona una serie de funciones para ayudarte a asegurar tus aplicaciones web.

## Falsificación de Petición en Sitio Cruzado (CSRF)

La Falsificación de Petición en Sitio Cruzado (CSRF) es un tipo de ataque donde un sitio web malintencionado puede hacer que el navegador de un usuario envíe una solicitud a tu sitio web. Esto se puede usar para realizar acciones en tu sitio web sin el conocimiento del usuario. Flight no proporciona un mecanismo de protección CSRF incorporado, pero puedes implementar fácilmente el tuyo utilizando middleware.

Aquí tienes un ejemplo de cómo podrías implementar la protección CSRF usando filtros de eventos:

```php

// Este middleware verifica si la solicitud es una solicitud POST y, si lo es, verifica si el token CSRF es válido
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// captura el token csrf de los valores del formulario
		$token = Flight::request()->data->csrf_token;
		if($token != $_SESSION['csrf_token']) {
			Flight::halt(403, 'Token CSRF inválido');
		}
	}
});
```

## Scripting en Sitio Cruzado (XSS)

El Scripting en Sitio Cruzado (XSS) es un tipo de ataque donde un sitio web malintencionado puede inyectar código en tu sitio web. La mayoría de estas oportunidades provienen de los valores del formulario que completarán tus usuarios finales. ¡Nunca debes confiar en la salida de tus usuarios! Siempre debes asumir que todos ellos son los mejores hackers del mundo. Pueden inyectar JavaScript malicioso o HTML en tu página. Este código se puede utilizar para robar información de tus usuarios o realizar acciones en tu sitio web. Utilizando la clase de vista de Flight, puedes escapar fácilmente la salida para prevenir los ataques XSS.

```php

// Vamos a suponer que el usuario es astuto e intenta usar esto como su nombre
$nombre = '<script>alert("XSS")</script>';

// Esto escapará la salida
Flight::view()->set('nombre', $nombre);
// Esto mostrará: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Si utilizas algo como Latte registrado como tu clase de vista, también se escapará automáticamente esto.
Flight::view()->render('plantilla', ['nombre' => $nombre]);
```

## Inyección SQL

La Inyección SQL es un tipo de ataque donde un usuario malintencionado puede inyectar código SQL en tu base de datos. Esto se puede usar para robar información de tu base de datos o realizar acciones en ella. De nuevo, ¡nunca debes confiar en la entrada de tus usuarios! Siempre debes asumir que van a por sangre. Puedes utilizar declaraciones preparadas en tus objetos `PDO` para prevenir la inyección SQL.

```php

// Suponiendo que tienes Flight::db() registrado como tu objeto PDO
$declaracion = Flight::db()->prepare('SELECT * FROM usuarios WHERE nombre_usuario = :nombre_usuario');
$declaracion->execute([':nombre_usuario' => $nombre_usuario]);
$usuarios = $declaracion->fetchAll();

// Si utilizas la clase PdoWrapper, esto se puede hacer fácilmente en una línea
$usuarios = Flight::db()->fetchAll('SELECT * FROM usuarios WHERE nombre_usuario = :nombre_usuario', [ 'nombre_usuario' => $nombre_usuario ]);

// Puedes hacer lo mismo con un objeto PDO con marcadores de posición ?
$declaracion = Flight::db()->fetchAll('SELECT * FROM usuarios WHERE nombre_usuario = ?', [ $nombre_usuario ]);

// Solo promete que nunca HARÁS algo como esto...
$usuarios = Flight::db()->fetchAll("SELECT * FROM usuarios WHERE nombre_usuario = '{$nombre_usuario}'");
// porque ¿qué pasa si $nombre_usuario = "' OR 1=1;"; Después de que se construya la consulta, se verá
// así
// SELECT * FROM usuarios WHERE nombre_usuario = '' OR 1=1;
// Parece extraño, pero es una consulta válida que funcionará. De hecho,
// es un ataque de inyección de SQL muy común que devolverá todos los usuarios.
```

## CORS

El Compartir Recursos de Origen Cruzado (CORS) es un mecanismo que permite que muchos recursos (por ejemplo, fuentes, JavaScript, etc.) en una página web sean solicitados desde otro dominio fuera del dominio del cual proviene el recurso. Flight no tiene funcionalidades integradas pero esto se puede manejar fácilmente con middleware de filtros de eventos similares a CSRF.

```php

Flight::route('/usuarios', function() {
	$usuarios = Flight::db()->fetchAll('SELECT * FROM usuarios');
	Flight::json($usuarios);
})->addMiddleware(function() {
	if (isset($_SERVER['HTTP_ORIGIN'])) {
		header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		header('Access-Control-Allow-Credentials: true');
		header('Access-Control-Max-Age: 86400');
	}

	if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
		if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
			header(
				'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS'
			);
		}
		if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
			header(
				"Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}"
			);
		}
		exit(0);
	}
});
```

## Conclusión

La seguridad es fundamental y es importante asegurarse de que tus aplicaciones web sean seguras. Flight proporciona una serie de funciones para ayudarte a asegurar tus aplicaciones web, pero es importante estar siempre vigilante y asegurarte de estar haciendo todo lo posible para mantener seguros los datos de tus usuarios. Siempre asume lo peor y nunca confíes en la entrada de tus usuarios. Siempre escapa la salida y utiliza declaraciones preparadas para prevenir la inyección SQL. Siempre utiliza middleware para proteger tus rutas de los ataques CSRF y CORS. Si haces todas estas cosas, estarás en camino de construir aplicaciones web seguras.