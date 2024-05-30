# Respuestas

Flight ayuda a generar parte de los encabezados de respuesta, pero tienes la mayor parte del control sobre lo que envías de vuelta al usuario. A veces puedes acceder directamente al objeto `Response`, pero la mayor parte del tiempo usarás la instancia `Flight` para enviar una respuesta.

## Envío de una Respuesta Básica

Flight utiliza ob_start() para almacenar en búfer la salida. Esto significa que puedes usar `echo` o `print` para enviar una respuesta al usuario y Flight la capturará y la enviará de vuelta al usuario con los encabezados adecuados.

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
	// A veces es detallado, pero hace el trabajo cuando lo necesitas
	Flight::response()->write("¡Hola, Mundo!");

	// Si quieres recuperar el cuerpo que has establecido en este punto
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
		echo "¡Hola, Mundo!";
	} else {
		Flight::response()->status(403);
		echo "Prohibido";
	}
});
```

Si deseas obtener el código de estado actual, puedes usar el método `status` sin argumentos:

```php
Flight::response()->status(); // 200
```

## Ejecutar una Devolución de Llamada en el Cuerpo de la Respuesta

Puedes ejecutar una devolución de llamada en el cuerpo de la respuesta usando el método `addResponseBodyCallback`:

```php
Flight::route('/usuarios', function() {
	$db = Flight::db();
	$usuarios = $db->fetchAll("SELECT * FROM usuarios");
	Flight::render('tabla_usuarios', ['usuarios' => $usuarios]);
});

// Esto comprimirá todas las respuestas para cualquier ruta
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

Puedes agregar múltiples devoluciones de llamada y se ejecutarán en el orden en que se agregaron. Dado que esto puede aceptar cualquier [callable](https://www.php.net/manual/en/language.types.callable.php), puede aceptar una matriz de clases `[ $clase, 'método' ]`, un cierre `$strReplace = function($body) { str_replace('hola', 'allí', $body); };`, o un nombre de función `'minificar'` si tuvieras una función para minificar tu código html, por ejemplo.

**Nota:** Las devoluciones de llamada de ruta no funcionarán si estás utilizando la opción de configuración `flight.v2.output_buffering`.

### Devolución de Llamada de Ruta Específica

Si deseas que esto se aplique solo a una ruta específica, podrías agregar la devolución de llamada en la propia ruta:

```php
Flight::route('/usuarios', function() {
	$db = Flight::db();
	$usuarios = $db->fetchAll("SELECT * FROM usuarios");
	Flight::render('tabla_usuarios', ['usuarios' => $usuarios]);

	// Esto comprimirá solo la respuesta para esta ruta
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

### Opción de Middleware

También puedes usar middleware para aplicar la devolución de llamada a todas las rutas a través del middleware:

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		Flight::response()->addResponseBodyCallback(function($body) {
			// Esto es un 
			return $this->minificar($body);
		});
	}

	protected function minificar(string $body): string {
		// minificar el cuerpo
		return $body;
	}
}

// index.php
Flight::group('/usuarios', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MinifyMiddleware() ]);
```

## Establecer un Encabezado de Respuesta

Puedes establecer un encabezado como el tipo de contenido de la respuesta usando el método `header`:

```php

// Esto enviará "¡Hola, Mundo!" al navegador del usuario en texto plano
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	echo "¡Hola, Mundo!";
});
```

## JSON

Flight proporciona soporte para enviar respuestas JSON y JSONP. Para enviar una respuesta JSON, pasas algunos datos para ser codificados en JSON:

```php
Flight::json(['id' => 123]);
```

### JSON con Código de Estado

También puedes pasar un código de estado como segundo argumento:

```php
Flight::json(['id' => 123], 201);
```

### JSON con Impresión Clara

También puedes pasar un argumento a la última posición para habilitar la impresión clara:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

Si estás cambiando opciones pasadas a `Flight::json()` y deseas una syntax más simple, solo puedes mapear el método JSON:

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// Y ahora puede ser utilizado de esta manera
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON y Detener la Ejecución

Si deseas enviar una respuesta JSON y detener la ejecución, puedes usar el método `jsonHalt`.
Esto es útil para casos en los que estás verificando tal vez algún tipo de autorización y si
el usuario no está autorizado, puedes enviar una respuesta JSON inmediatamente, borrar el contenido del cuerpo existente
y detener la ejecución.

```php
Flight::route('/usuarios', function() {
	$autorizado = algunaVerificaciónDeAutorización();
	// Comprueba si el usuario está autorizado
	if($autorizado === false) {
		Flight::jsonHalt(['error' => 'No autorizado'], 401);
	}

	// Continuar con el resto de la ruta
});
```

### JSONP

Para solicitudes JSONP, opcionalmente puedes pasar el nombre del parámetro de consulta que estás
usando para definir tu función de devolución de llamada:

```php
Flight::jsonp(['id' => 123], 'q');
```

Entonces, al hacer una solicitud GET usando `?q=mi_funcion`, deberías recibir la salida:

```javascript
mi_funcion({"id":123});
```

Si no pasas un nombre de parámetro de consulta, se establecerá por defecto en `jsonp`.

## Redireccionar a otra URL

Puedes redirigir la solicitud actual usando el método `redirect()` y pasando
una nueva URL:

```php
Flight::redirect('/nueva/ubicacion');
```

Por defecto, Flight envía un código de estado HTTP 303 ("Ver Otro"). Opcionalmente, puedes establecer un
código personalizado:

```php
Flight::redirect('/nueva/ubicacion', 401);
```

## Detener

Puedes detener el framework en cualquier momento llamando al método `halt`:

```php
Flight::halt();
```

También puedes especificar opcionalmente un código de estado `HTTP` y un mensaje:

```php
Flight::halt(200, 'Volveremos enseguida...');
```

Llamar a `halt` descartará cualquier contenido de respuesta hasta ese punto. Si deseas detener
el framework y enviar la respuesta actual, usa el método `stop`:

```php
Flight::stop();
```

## Caché HTTP

Flight proporciona soporte integrado para el almacenamiento en caché a nivel HTTP. Si la condición de
caché se cumple, Flight devolverá una respuesta `HTTP 304 No Modificado`. La próxima vez que el
cliente solicite el mismo recurso, se le pedirá usar su versión en caché local.

### Caché a Nivel de Ruta

Si deseas almacenar en caché toda tu respuesta, puedes usar el método `cache()` y pasar el tiempo en caché.

```php

// Esto almacenará en caché la respuesta durante 5 minutos
Flight::route('/noticias', function () {
  Flight::response()->cache(time() + 300);
  echo 'Este contenido será almacenado en caché.';
});

// Alternativamente, puedes usar una cadena que pasarías
// al método strtotime()
Flight::route('/noticias', function () {
  Flight::response()->cache('+5 minutes');
  echo 'Este contenido será almacenado en caché.';
});
```

### Última Modificación

Puedes usar el método `lastModified` y pasar una marca de tiempo UNIX para establecer la fecha
y hora en que una página fue modificada por última vez. El cliente seguirá usando su caché hasta
que el valor de última modificación sea cambiado.

```php
Flight::route('/noticias', function () {
  Flight::lastModified(1234567890);
  echo 'Este contenido será almacenado en caché.';
});
```

### ETag

El almacenamiento en caché del `ETag` es similar a `Última Modificación`, excepto que puedes especificar cualquier identificación
que desees para el recurso:

```php
Flight::route('/noticias', function () {
  Flight::etag('mi-identificador-único');
  echo 'Este contenido será almacenado en caché.';
});
```

Ten en cuenta que llamar tanto a `lastModified` como a `etag` establecerá y verificará el valor de la caché. Si el valor de la caché es el mismo entre las solicitudes, Flight enviará inmediatamente una respuesta `HTTP 304` y detendrá el procesamiento.