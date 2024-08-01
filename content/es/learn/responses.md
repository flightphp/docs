# Respuestas

Flight ayuda a generar parte de las cabeceras de respuesta, pero tienes la mayor parte del control sobre lo que devuelves al usuario. A veces puedes acceder al objeto `Response` directamente, pero la mayoría de las veces usarás la instancia `Flight` para enviar una respuesta.

## Enviando una Respuesta Básica

Flight utiliza `ob_start()` para almacenar en búfer la salida. Esto significa que puedes usar `echo` o `print` para enviar una respuesta al usuario y Flight la capturará y la enviará de vuelta al usuario con las cabeceras apropiadas.

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

Como alternativa, puedes llamar al método `write()` para añadir al cuerpo también.

```php

// Esto enviará "¡Hola, Mundo!" al navegador del usuario
Flight::route('/', function() {
	// detallado, pero hace el trabajo a veces cuando lo necesitas
	Flight::response()->write("¡Hola, Mundo!");

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
		echo "¡Hola, Mundo!";
	} else {
		Flight::response()->status(403);
		echo "Prohibido";
	}
});
```

Si quieres obtener el código de estado actual, puedes usar el método `status` sin argumentos:

```php
Flight::response()->status(); // 200
```

## Estableciendo un Cuerpo de Respuesta

Puedes establecer el cuerpo de la respuesta usando el método `write`, sin embargo, si haces un echo o print de algo,
será capturado y enviado como el cuerpo de respuesta a través del almacenamiento en búfer de salida.

```php
Flight::route('/', function() {
	Flight::response()->write("¡Hola, Mundo!");
});

// igual que

Flight::route('/', function() {
	echo "¡Hola, Mundo!";
});
```

### Borrando un Cuerpo de Respuesta

Si quieres borrar el cuerpo de la respuesta, puedes usar el método `clearBody`:

```php
Flight::route('/', function() {
	if($algunaCondicion) {
		Flight::response()->write("¡Hola, Mundo!");
	} else {
		Flight::response()->clearBody();
	}
});
```

### Ejecutando una Devolución de Llamada en el Cuerpo de Respuesta

Puedes ejecutar una devolución de llamada en el cuerpo de respuesta usando el método `addResponseBodyCallback`:

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

Puedes añadir múltiples devoluciones de llamada y se ejecutarán en el orden en que se añadieron. Dado que puede aceptar cualquier [llamable](https://www.php.net/manual/es/language.types.callable.php), puede aceptar una matriz de clase `[ $clase, 'método' ]`, un cierre `$strReplace = function($body) { str_replace('hi', 'there', $body); };`, o un nombre de función `'minify'` si tuvieras una función para minificar tu código html, por ejemplo.

**Nota:** Las devoluciones de llamada de rutas no funcionarán si estás usando la opción de configuración `flight.v2.output_buffering`.

### Devolución de Llamada de Ruta Específica

Si quieres que esto se aplique solo a una ruta específica, podrías añadir la devolución de llamada en la propia ruta:

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

También puedes usar middleware para aplicar la devolución de llamada a todas las rutas a través de middleware:

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		// Aplica la devolución de llamada aquí en el objeto response().
		Flight::response()->addResponseBodyCallback(function($body) {
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// minificar el cuerpo de alguna forma
		return $body;
	}
}

// index.php
Flight::group('/usuarios', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MinifyMiddleware() ]);
```

## Estableciendo una Cabecera de Respuesta

Puedes establecer una cabecera como el tipo de contenido de la respuesta usando el método `header`:

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

Flight proporciona soporte para enviar respuestas JSON y JSONP. Para enviar una respuesta JSON 
pasas algunos datos para ser codificados en JSON:

```php
Flight::json(['id' => 123]);
```

### JSON con Código de Estado

También puedes pasar un código de estado como segundo argumento:

```php
Flight::json(['id' => 123], 201);
```

### JSON con Impresión Bonita

También puedes pasar un argumento a la última posición para habilitar una impresión bonita:

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

Si estás cambiando opciones pasadas a `Flight::json()` y quieres una sintaxis más simple, puedes 
simplemente mapear el método JSON:

```php
Flight::map('json', function($datos, $código = 200, $opciones = 0) {
	Flight::_json($datos, $código, true, 'utf-8', $opciones);
}

// Y ahora se puede usar así
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON y Detener la Ejecución (v3.10.0)

Si quieres enviar una respuesta JSON y detener la ejecución, puedes usar el método `jsonHalt`.
Esto es útil para casos donde estás comprobando tal vez algún tipo de autorización y si
el usuario no está autorizado, puedes enviar una respuesta JSON inmediatamente, borrar el cuerpo existente
del contenido y detener la ejecución.

```php
Flight::route('/usuarios', function() {
	$autorizado = algunaComprobaciónDeAutorización();
	// Comprueba si el usuario está autorizado
	if($autorizado === false) {
		Flight::jsonHalt(['error' => 'No autorizado'], 401);
	}

	// Continúa con el resto de la ruta
});
```

Antes de v3.10.0, tendrías que hacer algo como esto:

```php
Flight::route('/usuarios', function() {
	$autorizado = algunaComprobaciónDeAutorización();
	// Comprueba si el usuario está autorizado
	if($autorizado === false) {
		Flight::halt(401, json_encode(['error' => 'No autorizado']));
	}

	// Continúa con el resto de la ruta
});
```

### JSONP

Para solicitudes JSONP, puedes pasar opcionalmente el nombre del parámetro de consulta que estás 
usando para definir tu función de devolución de llamada:

```php
Flight::jsonp(['id' => 123], 'q');
```

Entonces, al hacer una solicitud GET usando `?q=mi_func`, deberías recibir la salida:

```javascript
mi_func({"id":123});
```

Si no pasas un nombre de parámetro de consulta, será por defecto `jsonp`.

## Redirigir a otra URL

Puedes redirigir la solicitud actual usando el método `redirect()` y pasar
una nueva URL:

```php
Flight::redirect('/nueva/ubicacion');
```

Por defecto, Flight envía un código de estado HTTP 303 ("See Other"). Opcionalmente puedes establecer un 
código personalizado:

```php
Flight::redirect('/nueva/ubicacion', 401);
```

## Deteniendo

Puedes detener el framework en cualquier momento llamando al método `halt`:

```php
Flight::halt();
```

También puedes especificar opcionalmente un código de estado `HTTP` y un mensaje:

```php
Flight::halt(200, 'Regreso enseguida...');
```

Llamar a `halt` descartará cualquier contenido de respuesta hasta ese punto. Si quieres detener
el framework y enviar la respuesta actual, usa el método `stop`:

```php
Flight::stop();
```

## Limpiando los Datos de Respuesta

Puedes borrar el cuerpo de la respuesta y las cabeceras usando el método `clear()`. Esto limpiará
cualquier cabecera asignada a la respuesta, limpiará el cuerpo de la respuesta y establecerá el código de estado en `200`.

```php
Flight::response()->clear();
```

### Borrando solo el Cuerpo de la Respuesta

Si solo quieres borrar el cuerpo de la respuesta, puedes usar el método `clearBody()`:

```php
// Esto seguirá manteniendo cualquier cabecera establecida en el objeto response().
Flight::response()->clearBody();
```

## Caché HTTP

Flight proporciona soporte integrado para la caché a nivel HTTP. Si se cumple la condición de 
caché, Flight devolverá una respuesta de `HTTP 304 No modificado`. La próxima vez que el
cliente solicite el mismo recurso, se le pedirá que utilice su versión en caché local.

### Caché a Nivel de Ruta

Si quieres cachear toda tu respuesta, puedes usar el método `cache()` y pasar el tiempo a cachear.

```php

// Esto cacheará la respuesta durante 5 minutos
Flight::route('/noticias', function () {
  Flight::response()->cache(time() + 300);
  echo 'Este contenido será cachéado.';
});

// Alternativamente, puedes usar una cadena que pasarías
// al método strtotime()
Flight::route('/noticias', function () {
  Flight::response()->cache('+5 minutos');
  echo 'Este contenido será cachéado.';
});
```

### Última Modificación

Puedes usar el método `lastModified` y pasar una marca de tiempo UNIX para establecer la fecha
y hora en la que una página fue modificada por última vez. El cliente seguirá utilizando su caché hasta
que el valor de la última modificación se cambie.

```php
Flight::route('/noticias', function () {
  Flight::lastModified(1234567890);
  echo 'Este contenido será cachéado.';
});
```

### ETag

El caching `ETag` es similar a `Última Modificación`, excepto que puedes especificar cualquier id
que desees para el recurso:

```php
Flight::route('/noticias', function () {
  Flight::etag('mi-id-único');
  echo 'Este contenido será cachéado.';
});
```

Ten en cuenta que llamar tanto a `lastModified` como a `etag` establecerá y comprobará el
valor del caché. Si el valor de caché es el mismo entre las solicitudes, Flight enviará inmediatamente
una respuesta `HTTP 304` y dejará de procesar.

### Descargar un Archivo

Existe un método auxiliar para descargar un archivo. Puedes usar el método `download` y pasar la ruta.

```php
Flight::route('/descargar', function () {
  Flight::download('/ruta/al/archivo.txt');
});
```