# Respuestas

Flight ayuda a generar parte de los encabezados de respuesta por ti, pero tienes la mayor parte del control sobre lo que envías de vuelta al usuario. A veces puedes acceder al objeto `Response` directamente, pero la mayoría de las veces usarás la instancia de `Flight` para enviar una respuesta.

## Enviando una respuesta básica

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

Como alternativa, puedes llamar al método `write()` para añadir al cuerpo también.

```php

// Esto enviará "¡Hola, Mundo!" al navegador del usuario
Flight::route('/', function() {
	// verboso, pero hace el trabajo algunas veces cuando lo necesitas
	Flight::response()->write("¡Hola, Mundo!");

	// si deseas recuperar el cuerpo que has establecido en este punto
	// puedes hacerlo de la siguiente manera
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

Si deseas obtener el código de estado actual, puedes utilizar el método `status` sin argumentos:

```php
Flight::response()->status(); // 200
```

## Estableciendo un Encabezado de Respuesta

Puedes establecer un encabezado como el tipo de contenido de la respuesta utilizando el método `header`:

```php

// Esto enviará "¡Hola, Mundo!" al navegador del usuario en texto plano
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	echo "¡Hola, Mundo!";
});
```



## JSON

Flight proporciona soporte para enviar respuestas JSON y JSONP. Para enviar una respuesta JSON
pasas algunos datos para ser codificados en JSON:

```php
Flight::json(['id' => 123]);
```

### JSONP

Para solicitudes JSONP, puedes opcionalmente pasar el nombre del parámetro de consulta que estás
usando para definir tu función de devolución de llamada:

```php
Flight::jsonp(['id' => 123], 'q');
```

Entonces, al hacer una solicitud GET usando `?q=my_func`, deberías recibir la salida:

```javascript
my_func({"id":123});
```

Si no pasas un nombre de parámetro de consulta, se establecerá por defecto en `jsonp`.

## Redireccionar a otra URL

Puedes redirigir la solicitud actual utilizando el método `redirect()` y pasando
una nueva URL:

```php
Flight::redirect('/nueva/ubicacion');
```

Por defecto, Flight envía un código de estado HTTP 303 ("Ver Otro"). Opcionalmente puedes configurar un
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
Flight::halt(200, 'Vuelvo enseguida...');
```

Llamar a `halt` descartará cualquier contenido de respuesta hasta ese punto. Si deseas detener
el framework y mostrar la respuesta actual, utiliza el método `stop`:

```php
Flight::stop();
```

## Caché HTTP

Flight proporciona soporte integrado para la caché a nivel HTTP. Si se cumple la condición de caché,
Flight devolverá una respuesta HTTP `304 No modificado`. La próxima vez que el
cliente solicite el mismo recurso, se le pedirá que utilice su versión en caché local.

### Caché a Nivel de Ruta

Si deseas cachear toda tu respuesta, puedes utilizar el método `cache()` y pasarle un tiempo para cachear.

```php

// Esto cacheará la respuesta durante 5 minutos
Flight::route('/noticias', function () {
  Flight::cache(time() + 300);
  echo 'Este contenido será cachéado.';
});

// Alternativamente, puedes usar una cadena que pasarías
// al método strtotime()
Flight::route('/noticias', function () {
  Flight::cache('+5 minutos');
  echo 'Este contenido será cachéado.';
});
```

### Última Modificación

Puedes usar el método `lastModified` y pasarle una marca de tiempo UNIX para establecer la fecha
y hora en que una página fue modificada por última vez. El cliente seguirá utilizando su caché hasta
que el valor de la última modificación cambie.

```php
Flight::route('/noticias', function () {
  Flight::lastModified(1234567890);
  echo 'Este contenido será cachéado.';
});
```

### ETag

La caché `ETag` es similar a `Última Modificación`, excepto que puedes especificar cualquier identificación
que desees para el recurso:

```php
Flight::route('/noticias', function () {
  Flight::etag('mi-identificador-único');
  echo 'Este contenido será cachéado.';
});
```

Ten en cuenta que llamar tanto a `lastModified` como a `etag` establecerá y comprobará el valor de caché. Si el valor de
caché es el mismo entre las solicitudes, Flight enviará inmediatamente una respuesta de `HTTP 304` y detendrá el procesamiento.