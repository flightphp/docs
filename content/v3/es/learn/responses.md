# Respuestas

Flight ayuda a generar parte de los encabezados de respuesta para ti, pero tú tienes la mayor parte del control sobre lo que envías de vuelta al usuario. A veces puedes acceder directamente al objeto `Response`, pero la mayoría de las veces usarás la instancia de `Flight` para enviar una respuesta.

## Enviar una Respuesta Básica

Flight usa ob_start() para almacenar en buffer la salida. Esto significa que puedes usar `echo` o `print` para enviar una respuesta al usuario y Flight la capturará y la enviará de vuelta con los encabezados apropiados.

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
	// verbose, pero a veces se necesita cuando lo requieres
	Flight::response()->write("Hello, World!");

	// si quieres recuperar el cuerpo que has establecido en este punto
	// puedes hacerlo así
	$body = Flight::response()->getBody();
});
```

## Códigos de Estado

Puedes establecer el código de estado de la respuesta usando el método `status`:

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

## Establecer un Cuerpo de Respuesta

Puedes establecer el cuerpo de la respuesta usando el método `write`, sin embargo, si echo o print algo, 
se capturará y se enviará como el cuerpo de la respuesta a través del almacenamiento en buffer de salida.

```php
Flight::route('/', function() {
	Flight::response()->write("Hello, World!");
});

// lo mismo que

Flight::route('/', function() {
	echo "Hello, World!";
});
```

### Limpiar un Cuerpo de Respuesta

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

### Ejecutar un Callback en el Cuerpo de Respuesta

Puedes ejecutar un callback en el cuerpo de la respuesta usando el método `addResponseBodyCallback`:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// Esto comprimirá con gzip todas las respuestas para cualquier ruta
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Puedes agregar múltiples callbacks y se ejecutarán en el orden en que se agregaron. Como esto puede aceptar cualquier [callable](https://www.php.net/manual/en/language.types.callable.php), puede aceptar un array de clase `[ $class, 'method' ]`, un cierre `$strReplace = function($body) { str_replace('hi', 'there', $body); };`, o un nombre de función `'minify'` si tienes una función para minimizar tu código HTML, por ejemplo.

**Nota:** Los callbacks de rutas no funcionarán si estás usando la opción de configuración `flight.v2.output_buffering`.

### Callback para una Ruta Específica

Si querías que esto se aplicara solo a una ruta específica, podrías agregar el callback en la ruta misma:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// Esto comprimirá con gzip solo la respuesta para esta ruta
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

### Opción de Middleware

También puedes usar middleware para aplicar el callback a todas las rutas a través de middleware:

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		// Aplicar el callback aquí en el objeto response().
		Flight::response()->addResponseBodyCallback(function($body) {
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// minimiza el cuerpo de alguna manera
		return $body;
	}
}

// index.php
Flight::group('/users', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MinifyMiddleware() ]);
```

## Establecer un Encabezado de Respuesta

Puedes establecer un encabezado como el tipo de contenido de la respuesta usando el método `header`:

```php
// Esto enviará "Hello, World!" al navegador del usuario en texto plano
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// o
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

## JSON

Flight proporciona soporte para enviar respuestas JSON y JSONP. Para enviar una respuesta JSON, 
pasa algunos datos para ser codificados en JSON:

```php
Flight::json(['id' => 123]);
```

> **Nota:** Por defecto, Flight enviará un encabezado `Content-Type: application/json` con la respuesta. También usará las constantes `JSON_THROW_ON_ERROR` y `JSON_UNESCAPED_SLASHES` al codificar el JSON.

### JSON con Código de Estado

También puedes pasar un código de estado como el segundo argumento:

```php
Flight::json(['id' => 123], 201);
```

### JSON con Impresión Bonita

Puedes pasar un argumento en la última posición para habilitar la impresión bonita:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

Si estás cambiando opciones pasadas a `Flight::json()` y quieres una sintaxis más simple, puedes 
remapear el método JSON:

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// Y ahora puede usarse así
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON y Detener la Ejecución (v3.10.0)

Si quieres enviar una respuesta JSON y detener la ejecución, puedes usar el método `jsonHalt()`.
Esto es útil para casos en los que estás verificando algún tipo de autorización y si 
el usuario no está autorizado, puedes enviar una respuesta JSON inmediatamente, limpiar el contenido 
del cuerpo existente y detener la ejecución.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Verifica si el usuario está autorizado
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
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

### JSONP

Para solicitudes JSONP, puedes pasar opcionalmente el nombre del parámetro de consulta que estás 
usando para definir tu función de callback:

```php
Flight::jsonp(['id' => 123], 'q');
```

Entonces, al hacer una solicitud GET usando `?q=my_func`, deberías recibir la salida:

```javascript
my_func({"id":123});
```

Si no pasas un nombre de parámetro de consulta, se establecerá por defecto en `jsonp`.

## Redirigir a otra URL

Puedes redirigir la solicitud actual usando el método `redirect()` y pasando 
una nueva URL:

```php
Flight::redirect('/new/location');
```

Por defecto, Flight envía un código de estado HTTP 303 ("See Other"). Puedes establecer opcionalmente un 
código personalizado:

```php
Flight::redirect('/new/location', 401);
```

## Detener

Puedes detener el framework en cualquier momento llamando al método `halt`:

```php
Flight::halt();
```

También puedes especificar un código de estado HTTP opcional y un mensaje:

```php
Flight::halt(200, 'Be right back...');
```

Llamar a `halt` descartará cualquier contenido de respuesta hasta ese punto. Si quieres detener 
el framework y salir con la respuesta actual, usa el método `stop`:

```php
Flight::stop($httpStatusCode = null);
```

> **Nota:** `Flight::stop()` tiene un comportamiento extraño, como que saldrá la respuesta pero continuará ejecutando tu script. Puedes usar `exit` o `return` después de llamar a `Flight::stop()` para prevenir una ejecución adicional, pero generalmente se recomienda usar `Flight::halt()`. 

## Limpiar Datos de Respuesta

Puedes limpiar el cuerpo y los encabezados de la respuesta usando el método `clear()`. Esto limpiará 
cualquier encabezado asignado a la respuesta, limpiará el cuerpo de la respuesta y establecerá el código de estado en `200`.

```php
Flight::response()->clear();
```

### Limpiar Solo el Cuerpo de Respuesta

Si solo quieres limpiar el cuerpo de la respuesta, puedes usar el método `clearBody()`:

```php
// Esto mantendrá cualquier encabezado establecido en el objeto response().
Flight::response()->clearBody();
```

## Almacenamiento en Caché HTTP

Flight proporciona soporte integrado para el almacenamiento en caché a nivel HTTP. Si se cumple la condición de caché, 
Flight devolverá una respuesta HTTP `304 Not Modified`. La próxima vez que el cliente solicite el mismo recurso, se le pedirá que use su versión en caché local.

### Almacenamiento en Caché a Nivel de Ruta

Si quieres almacenar en caché toda tu respuesta, puedes usar el método `cache()` y pasar el tiempo de caché.

```php
// Esto almacenará en caché la respuesta por 5 minutos
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

### Last-Modified

Puedes usar el método `lastModified` y pasar una marca de tiempo UNIX para establecer la fecha 
y hora en que una página fue modificada por última vez. El cliente continuará usando su caché hasta 
que el valor de última modificación cambie.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'This content will be cached.';
});
```

### ETag

El almacenamiento en caché `ETag` es similar a `Last-Modified`, excepto que puedes especificar cualquier ID que 
quieras para el recurso:

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'This content will be cached.';
});
```

Ten en cuenta que llamar a `lastModified` o `etag` establecerá y verificará 
el valor de caché. Si el valor de caché es el mismo entre solicitudes, Flight enviará inmediatamente 
una respuesta `HTTP 304` y detendrá el procesamiento.

## Descargar un Archivo (v3.12.0)

Hay un método helper para descargar un archivo. Puedes usar el método `download` y pasar la ruta.

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```