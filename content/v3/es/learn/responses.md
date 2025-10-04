# Respuestas

## Resumen

Flight ayuda a generar parte de los encabezados de respuesta por ti, pero tú tienes la mayoría del control sobre lo que envías de vuelta al usuario. La mayoría del tiempo accederás directamente al objeto `response()`, pero Flight tiene algunos métodos auxiliares para configurar algunos de los encabezados de respuesta por ti.

## Comprensión

Después de que el usuario envíe su [solicitud](/learn/requests) a tu aplicación, necesitas generar una respuesta adecuada para ellos. Te han enviado información como el idioma que prefieren, si pueden manejar ciertos tipos de compresión, su agente de usuario, etc., y después de procesar todo, es hora de enviarles una respuesta adecuada. Esto puede ser configurar encabezados, generar un cuerpo de HTML o JSON para ellos, o redirigirlos a una página.

## Uso básico

### Envío de un cuerpo de respuesta

Flight usa `ob_start()` para almacenar en búfer la salida. Esto significa que puedes usar `echo` o `print` para enviar una respuesta al usuario y Flight la capturará y la enviará de vuelta al usuario con los encabezados apropiados.

```php
// Esto enviará "Hello, World!" al navegador del usuario
Flight::route('/', function() {
	echo "Hello, World!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Hello, World!
```

Como alternativa, puedes llamar al método `write()` para agregar al cuerpo también.

```php
// Esto enviará "Hello, World!" al navegador del usuario
Flight::route('/', function() {
	// verboso, pero a veces hace el trabajo cuando lo necesitas
	Flight::response()->write("Hello, World!");

	// si quieres recuperar el cuerpo que has configurado en este punto
	// puedes hacerlo así
	$body = Flight::response()->getBody();
});
```

### JSON

Flight proporciona soporte para enviar respuestas JSON y JSONP. Para enviar una respuesta JSON, pasa algunos datos para que se codifiquen en JSON:

```php
Flight::route('/@companyId/users', function(int $companyId) {
	// de alguna manera extrae tus usuarios de una base de datos por ejemplo
	$users = Flight::db()->fetchAll("SELECT id, first_name, last_name FROM users WHERE company_id = ?", [ $companyId ]);

	Flight::json($users);
});
// [{"id":1,"first_name":"Bob","last_name":"Jones"}, /* more users */ ]
```

> **Nota:** Por defecto, Flight enviará un encabezado `Content-Type: application/json` con la respuesta. También usará las banderas `JSON_THROW_ON_ERROR` y `JSON_UNESCAPED_SLASHES` al codificar el JSON.

#### JSON con código de estado

También puedes pasar un código de estado como segundo argumento:

```php
Flight::json(['id' => 123], 201);
```

#### JSON con impresión bonita

También puedes pasar un argumento en la última posición para habilitar la impresión bonita:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

#### Cambiar el orden de los argumentos JSON

`Flight::json()` es un método muy antiguo, pero el objetivo de Flight es mantener la compatibilidad hacia atrás para los proyectos. En realidad es muy simple si quieres rehacer el orden de los argumentos para usar una sintaxis más simple, puedes simplemente remapear el método JSON [como cualquier otro método de Flight](/learn/extending):

```php
Flight::map('json', function($data, $code = 200, $options = 0) {

	// ahora no tienes que usar `true, 'utf-8'` al usar el método json()!
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// Y ahora se puede usar así
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

#### JSON y detención de ejecución

_v3.10.0_

Si quieres enviar una respuesta JSON y detener la ejecución, puedes usar el método `jsonHalt()`. Esto es útil para casos en los que estás verificando quizás algún tipo de autorización y si el usuario no está autorizado, puedes enviar una respuesta JSON inmediatamente, limpiar el contenido del cuerpo existente y detener la ejecución.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Verifica si el usuario está autorizado
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
		// no se necesita exit; aquí.
	}

	// Continúa con el resto de la ruta
});
```

Antes de v3.10.0, tendrías que hacer algo como esto:

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Verifica si el usuario está autorizado
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Unauthorized']));
	}

	// Continúa con el resto de la ruta
});
```

### Limpieza de un cuerpo de respuesta

Si quieres limpiar el cuerpo de la respuesta, puedes usar el método `clearBody`:

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Hello, World!");
	} else {
		Flight::response()->clearBody();
	}
});
```

El caso de uso anterior probablemente no es común, sin embargo podría ser más común si se usara en un [middleware](/learn/middleware).

### Ejecución de un callback en el cuerpo de la respuesta

Puedes ejecutar un callback en el cuerpo de la respuesta usando el método `addResponseBodyCallback`:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// Esto comprime con gzip todas las respuestas para cualquier ruta
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Puedes agregar múltiples callbacks y se ejecutarán en el orden en que se agregaron. Dado que esto puede aceptar cualquier [llamable](https://www.php.net/manual/en/language.types.callable.php), puede aceptar un array de clase `[ $class, 'method' ]`, una closure `$strReplace = function($body) { str_replace('hi', 'there', $body); };`, o un nombre de función `'minify'` si tuvieras una función para minificar tu código HTML por ejemplo.

**Nota:** Los callbacks de ruta no funcionarán si estás usando la opción de configuración `flight.v2.output_buffering`.

#### Callback de ruta específica

Si quisieras que esto solo se aplique a una ruta específica, podrías agregar el callback en la ruta misma:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// Esto comprime con gzip solo la respuesta para esta ruta
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

#### Opción de middleware

También puedes usar [middleware](/learn/middleware) para aplicar el callback a todas las rutas a través de middleware:

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		// Aplica el callback aquí en el objeto response().
		Flight::response()->addResponseBodyCallback(function($body) {
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// minifica el cuerpo de alguna manera
		return $body;
	}
}

// index.php
Flight::group('/users', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MinifyMiddleware() ]);
```

### Códigos de estado

Puedes configurar el código de estado de la respuesta usando el método `status`:

```php
Flight::route('/@id', function($id) {
	if($id == 123) {
		Flight::response()->status(200);
		echo "Hello, World!";
	} else {
		Flight::response()->status(403);
		echo "Forbidden";
	}
});
```

Si quieres obtener el código de estado actual, puedes usar el método `status` sin argumentos:

```php
Flight::response()->status(); // 200
```

### Configuración de un encabezado de respuesta

Puedes configurar un encabezado como el tipo de contenido de la respuesta usando el método `header`:

```php
// Esto enviará "Hello, World!" al navegador del usuario en texto plano
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// o
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

### Redirección

Puedes redirigir la solicitud actual usando el método `redirect()` y pasando una nueva URL:

```php
Flight::route('/login', function() {
	$username = Flight::request()->data->username;
	$password = Flight::request()->data->password;
	$passwordConfirm = Flight::request()->data->password_confirm;

	if($password !== $passwordConfirm) {
		Flight::redirect('/new/location');
		return; // esto es necesario para que la funcionalidad a continuación no se ejecute
	}

	// agrega el nuevo usuario...
	Flight::db()->runQuery("INSERT INTO users ....");
	Flight::redirect('/admin/dashboard');
});
```

> **Nota:** Por defecto, Flight envía un código de estado HTTP 303 ("See Other"). Puedes configurar opcionalmente un código personalizado:

```php
Flight::redirect('/new/location', 301); // permanente
```

### Detención de la ejecución de la ruta

Puedes detener el framework y salir inmediatamente en cualquier punto llamando al método `halt`:

```php
Flight::halt();
```

También puedes especificar un código de estado `HTTP` y mensaje opcionales:

```php
Flight::halt(200, 'Be right back...');
```

Llamar a `halt` descartará cualquier contenido de respuesta hasta ese punto y detendrá toda la ejecución. Si quieres detener el framework y generar la respuesta actual, usa el método `stop`:

```php
Flight::stop($httpStatusCode = null);
```

> **Nota:** `Flight::stop()` tiene un comportamiento extraño, como que generará la respuesta pero continuará ejecutando tu script, lo cual podría no ser lo que buscas. Puedes usar `exit` o `return` después de llamar a `Flight::stop()` para prevenir la ejecución adicional, pero en general se recomienda usar `Flight::halt()`.

Esto guardará la clave y el valor del encabezado en el objeto de respuesta. Al final del ciclo de vida de la solicitud, construirá los encabezados y enviará una respuesta.

## Uso avanzado

### Envío de un encabezado inmediatamente

Puede haber veces en las que necesites hacer algo personalizado con el encabezado y necesites enviar el encabezado en esa misma línea de código con la que estás trabajando. Si estás configurando una [ruta transmitida](/learn/routing), esto es lo que necesitarías. Eso se logra a través de `response()->setRealHeader()`.

```php
Flight::route('/', function() {
	Flight::response()->setRealHeader('Content-Type: text/plain');
	echo 'Streaming response...';
	sleep(5);
	echo 'Done!';
})->stream();
```

### JSONP

Para solicitudes JSONP, puedes pasar opcionalmente el nombre del parámetro de consulta que estás usando para definir tu función de callback:

```php
Flight::jsonp(['id' => 123], 'q');
```

Entonces, al hacer una solicitud GET usando `?q=my_func`, deberías recibir la salida:

```javascript
my_func({"id":123});
```

Si no pasas un nombre de parámetro de consulta, se usará `jsonp` por defecto.

> **Nota:** Si aún estás usando solicitudes JSONP en 2025 y más allá, únete al chat y cuéntanos por qué! ¡Nos encanta escuchar algunas buenas historias de batalla/horror!

### Limpieza de datos de respuesta

Puedes limpiar el cuerpo de la respuesta y los encabezados usando el método `clear()`. Esto limpiará cualquier encabezado asignado a la respuesta, limpiará el cuerpo de la respuesta y configurará el código de estado en `200`.

```php
Flight::response()->clear();
```

#### Limpieza solo del cuerpo de respuesta

Si solo quieres limpiar el cuerpo de la respuesta, puedes usar el método `clearBody()`:

```php
// Esto aún mantendrá cualquier encabezado configurado en el objeto response().
Flight::response()->clearBody();
```

### Caché HTTP

Flight proporciona soporte integrado para caché a nivel HTTP. Si se cumple la condición de caché, Flight devolverá una respuesta HTTP `304 Not Modified`. La próxima vez que el cliente solicite el mismo recurso, se le indicará que use su versión en caché localmente.

#### Caché a nivel de ruta

Si quieres cachear toda tu respuesta, puedes usar el método `cache()` y pasar el tiempo para cachear.

```php

// Esto cacheará la respuesta por 5 minutos
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'This content will be cached.';
});

// Alternativamente, puedes usar una cadena que pasarías
// al método strtotime()
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'This content will be cached.';
});
```

### Última modificación

Puedes usar el método `lastModified` y pasar una marca de tiempo UNIX para configurar la fecha y hora en que una página fue modificada por última vez. El cliente continuará usando su caché hasta que el valor de última modificación cambie.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'This content will be cached.';
});
```

### ETag

El caché `ETag` es similar a `Last-Modified`, excepto que puedes especificar cualquier id que quieras para el recurso:

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'This content will be cached.';
});
```

Ten en cuenta que llamar a cualquiera de `lastModified` o `etag` configurará y verificará ambos el valor de caché. Si el valor de caché es el mismo entre solicitudes, Flight enviará inmediatamente una respuesta `HTTP 304` y detendrá el procesamiento.

### Descarga de un archivo

_v3.12.0_

Hay un método auxiliar para transmitir un archivo al usuario final. Puedes usar el método `download` y pasar la ruta.

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
  // A partir de v3.17.1 puedes especificar un nombre de archivo personalizado para la descarga
  Flight::download('/path/to/file.txt', 'custom_name.txt');
});
```

## Ver también
- [Enrutamiento](/learn/routing) - Cómo mapear rutas a controladores y renderizar vistas.
- [Solicitudes](/learn/requests) - Comprender cómo manejar solicitudes entrantes.
- [Middleware](/learn/middleware) - Usar middleware con rutas para autenticación, registro, etc.
- [¿Por qué un framework?](/learn/why-frameworks) - Comprender los beneficios de usar un framework como Flight.
- [Extensión](/learn/extending) - Cómo extender Flight con tu propia funcionalidad.

## Solución de problemas
- Si tienes problemas con las redirecciones que no funcionan, asegúrate de agregar un `return;` al método.
- `stop()` y `halt()` no son lo mismo. `halt()` detendrá la ejecución inmediatamente, mientras que `stop()` permitirá que la ejecución continúe.

## Registro de cambios
- v3.17.1 - Agregado `$fileName` al método `downloadFile()`.
- v3.12.0 - Agregado método auxiliar `downloadFile`.
- v3.10.0 - Agregado `jsonHalt`.
- v1.0 - Lanzamiento inicial.