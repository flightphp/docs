# Respuestas

Flight ayuda a generar parte de los encabezados de respuesta por ti, pero tú mantienes la mayor parte del control sobre lo que envías de vuelta al usuario. A veces puedes acceder al objeto `Response` directamente, pero la mayor parte del tiempo usarás la instancia de `Flight` para enviar una respuesta.

## Enviando una Respuesta Básica

Flight utiliza ob_start() para almacenar en búfer la salida. Esto significa que puedes usar `echo` o `print` para enviar una respuesta al usuario y Flight la capturará y la enviará de vuelta al usuario con los encabezados apropiados.

```php

// Esto enviará "¡Hola, Mundo!" al navegador del usuario
Flight::route('/', function() {
	echo "¡Hola, Mundo!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// ¡Hola, Mundo!
```

Como alternativa, puedes llamar al método `write()` para agregar al cuerpo también.

```php

// Esto enviará "¡Hola, Mundo!" al navegador del usuario
Flight::route('/', function() {
	// Verboso, pero a veces hace el trabajo cuando lo necesitas
	Flight::response()->write("¡Hola, Mundo!");

	// Si deseas recuperar el cuerpo que has establecido en este punto
	// puedes hacerlo de esta manera
	$body = Flight::response()->getBody();
});
```

## Códigos de Estado

Puedes establecer el código de estado de la respuesta utilizando el método `status`:

```php
Flight::route('/@id', function($id) {
	if($id == 123) {
		Flight::response()->status(200);
		echo "¡Hola, Mundo!";
	} else {
		Flight::response()->status(403);
		echo "Prohibido";
	}
});
```

Si deseas obtener el código de estado actual, puedes utilizar el método `status` sin ningún argumento:

```php
Flight::response()->status(); // 200
```

## Estableciendo un Cuerpo de Respuesta

Puedes establecer el cuerpo de la respuesta utilizando el método `write`, sin embargo, si haces echo o print de algo, 
será capturado y enviado como el cuerpo de respuesta a través del almacenamiento en búfer.

```php
Flight::route('/', function() {
	Flight::response()->write("¡Hola, Mundo!");
});

// igual que

Flight::route('/', function() {
	echo "¡Hola, Mundo!";
});
```

### Limpiando un Cuerpo de Respuesta

Si deseas limpiar el cuerpo de la respuesta, puedes usar el método `clearBody`:

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("¡Hola, Mundo!");
	} else {
		Flight::response()->clearBody();
	}
});
```

### Ejecutando un Callback en el Cuerpo de Respuesta

Puedes ejecutar un callback en el cuerpo de la respuesta utilizando el método `addResponseBodyCallback`:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// Esto comprimirá todas las respuestas para cualquier ruta
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Puedes agregar múltiples callbacks y se ejecutarán en el orden en que fueron agregados. Debido a que esto puede aceptar cualquier [callable](https://www.php.net/manual/es/language.types.callable.php), puede aceptar un array de clase `[ $class, 'method' ]`, un closure `$strReplace = function($body) { str_replace('hi', 'there', $body); };`, o un nombre de función `'minify'` si tuvieras una función para minificar tu código html, por ejemplo.

**Nota:** Los callbacks de ruta no funcionarán si estás utilizando la opción de configuración `flight.v2.output_buffering`.

### Callback de Ruta Específica

Si quieres que esto aplique solo a una ruta específica, podrías agregar el callback en la ruta misma:

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// Esto comprimirá solo la respuesta para esta ruta
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
		// Aplica el callback aquí en el objeto response().
		Flight::response()->addResponseBodyCallback(function($body) {
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// minificar el cuerpo de alguna manera
		return $body;
	}
}

// index.php
Flight::group('/users', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MinifyMiddleware() ]);
```

## Estableciendo un Encabezado de Respuesta

Puedes establecer un encabezado como el tipo de contenido de la respuesta utilizando el método `header`:

```php

// Esto enviará "¡Hola, Mundo!" al navegador del usuario en texto plano
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// o
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "¡Hola, Mundo!";
});
```

## JSON

Flight proporciona soporte para enviar respuestas JSON y JSONP. Para enviar una respuesta JSON pasas algunos datos para que sean codificados en JSON:

```php
Flight::json(['id' => 123]);
```

> **Nota:** Por defecto, Flight enviará un encabezado `Content-Type: application/json` con la respuesta. También utilizará las constantes `JSON_THROW_ON_ERROR` y `JSON_UNESCAPED_SLASHES` al codificar el JSON.

### JSON con Código de Estado

También puedes pasar un código de estado como el segundo argumento:

```php
Flight::json(['id' => 123], 201);
```

### JSON con Formato Bonito

También puedes pasar un argumento en la última posición para habilitar el formato bonito:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

Si estás cambiando las opciones pasadas a `Flight::json()` y deseas una sintaxis más simple, simplemente puedes volver a mapear el método JSON:

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// Y ahora se puede usar así
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON y Detener la Ejecución (v3.10.0)

Si deseas enviar una respuesta JSON y detener la ejecución, puedes usar el método `jsonHalt`. Esto es útil en casos donde estás verificando tal vez algún tipo de autorización y si el usuario no está autorizado, puedes enviar una respuesta JSON de inmediato, limpiar el contenido del cuerpo existente y detener la ejecución.

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// Verifica si el usuario está autorizado
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'No autorizado'], 401);
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
		Flight::halt(401, json_encode(['error' => 'No autorizado']));
	}

	// Continúa con el resto de la ruta
});
```

### JSONP

Para las solicitudes JSONP, puedes opcionalmente pasar el nombre del parámetro de consulta que estás utilizando para definir tu función de callback:

```php
Flight::jsonp(['id' => 123], 'q');
```

Entonces, al hacer una solicitud GET usando `?q=my_func`, deberías recibir la salida:

```javascript
my_func({"id":123});
```

Si no pasas un nombre de parámetro de consulta, predeterminará a `jsonp`.

## Redirigir a otra URL

Puedes redirigir la solicitud actual utilizando el método `redirect()` y pasando una nueva URL:

```php
Flight::redirect('/new/location');
```

Por defecto, Flight envía un código de estado HTTP 303 ("Ver Otro"). Puedes opcionalmente establecer un código personalizado:

```php
Flight::redirect('/new/location', 401);
```

## Deteniendo

Puedes detener el framework en cualquier momento llamando al método `halt`:

```php
Flight::halt();
```

También puedes especificar un código de estado HTTP y un mensaje opcional:

```php
Flight::halt(200, 'Regresaré pronto...');
```

Llamar a `halt` descartará cualquier contenido de respuesta hasta ese punto. Si deseas detener el framework y outputar la respuesta actual, usa el método `stop`:

```php
Flight::stop();
```

## Limpiando Datos de Respuesta

Puedes limpiar el cuerpo y los encabezados de la respuesta utilizando el método `clear()`. Esto limpiará
cualquier encabezado asignado a la respuesta, limpiará el cuerpo de la respuesta y establecerá el código de estado en `200`.

```php
Flight::response()->clear();
```

### Limpiando Solo el Cuerpo de Respuesta

Si solo deseas limpiar el cuerpo de la respuesta, puedes usar el método `clearBody()`:

```php
// Esto aún mantendrá cualquier encabezado establecido en el objeto response().
Flight::response()->clearBody();
```

## Caché HTTP

Flight proporciona soporte integrado para caché a nivel HTTP. Si se cumple la condición de caché,
Flight devolverá una respuesta HTTP `304 No Modificado`. La próxima vez que el cliente solicite el mismo recurso, se le recomendará utilizar su versión en caché local.

### Caché a Nivel de Ruta

Si deseas caché tu respuesta completa, puedes utilizar el método `cache()` y pasar el tiempo para caché.

```php

// Esto almacenará en caché la respuesta durante 5 minutos
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'Este contenido será almacenado en caché.';
});

// Alternativamente, puedes utilizar una cadena que pasarías
// al método strtotime()
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'Este contenido será almacenado en caché.';
});
```

### Última Modificación

Puedes usar el método `lastModified` y pasar un timestamp UNIX para establecer la fecha
y hora en que se modificó por última vez una página. El cliente seguirá utilizando su caché hasta que el valor de última modificación cambie.

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'Este contenido será almacenado en caché.';
});
```

### ETag

El almacenamiento en caché de `ETag` es similar a `Última Modificación`, excepto que puedes especificar cualquier id que quieras para el recurso:

```php
Flight::route('/news', function () {
  Flight::etag('mi-id-único');
  echo 'Este contenido será almacenado en caché.';
});
```

Ten en cuenta que llamar a `lastModified` o `etag` establecerá y comprobará el
valor de la caché. Si el valor de la caché es el mismo entre solicitudes, Flight enviará inmediatamente
una respuesta `HTTP 304` y detendrá el procesamiento.

## Descargar un Archivo (v3.12.0)

Hay un método auxiliar para descargar un archivo. Puedes usar el método `download` y pasar la ruta.

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```