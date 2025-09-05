# Solicitudes

Flight encapsula la solicitud HTTP en un solo objeto, que se puede
acceder haciendo:

```php
$request = Flight::request();
```

## Casos de Uso Típicos

Cuando estás trabajando con una solicitud en una aplicación web, generalmente querrás
extraer un encabezado, o un parámetro `$_GET` o `$_POST`, o quizás
incluso el cuerpo de la solicitud sin procesar. Flight proporciona una interfaz simple para hacer todo esto.

Aquí hay un ejemplo para obtener un parámetro de la cadena de consulta:

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "You are searching for: $keyword";
	// consulta una base de datos o algo más con el $keyword
});
```

Aquí hay un ejemplo de quizás un formulario con un método POST:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "You submitted: $name, $email";
	// guarda en una base de datos o algo más con el $name y $email
});
```

## Propiedades del Objeto de Solicitud

El objeto de solicitud proporciona las siguientes propiedades:

- **body** - El cuerpo de la solicitud HTTP sin procesar
- **url** - La URL que se está solicitando
- **base** - El subdirectorio principal de la URL
- **method** - El método de la solicitud (GET, POST, PUT, DELETE)
- **referrer** - La URL de referencia
- **ip** - La dirección IP del cliente
- **ajax** - Si la solicitud es una solicitud AJAX
- **scheme** - El protocolo del servidor (http, https)
- **user_agent** - Información del navegador
- **type** - El tipo de contenido
- **length** - La longitud del contenido
- **query** - Parámetros de la cadena de consulta
- **data** - Datos de POST o datos JSON
- **cookies** - Datos de cookies
- **files** - Archivos subidos
- **secure** - Si la conexión es segura
- **accept** - Parámetros de aceptación HTTP
- **proxy_ip** - Dirección IP del proxy del cliente. Escanea el array `$_SERVER` para `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` en ese orden.
- **host** - El nombre de host de la solicitud
- **servername** - El SERVER_NAME de `$_SERVER`

Puedes acceder a las propiedades `query`, `data`, `cookies` y `files`
como arrays u objetos.

Entonces, para obtener un parámetro de la cadena de consulta, puedes hacer:

```php
$id = Flight::request()->query['id'];
```

O puedes hacer:

```php
$id = Flight::request()->query->id;
```

## Cuerpo de Solicitud Sin Procesar

Para obtener el cuerpo de la solicitud HTTP sin procesar, por ejemplo al lidiar con solicitudes PUT,
puedes hacer:

```php
$body = Flight::request()->getBody();
```

## Entrada JSON

Si envías una solicitud con el tipo `application/json` y los datos `{"id": 123}`
estará disponible desde la propiedad `data`:

```php
$id = Flight::request()->data->id;
```

## `$_GET`

Puedes acceder al array `$_GET` a través de la propiedad `query`:

```php
$id = Flight::request()->query['id'];
```

## `$_POST`

Puedes acceder al array `$_POST` a través de la propiedad `data`:

```php
$id = Flight::request()->data['id'];
```

## `$_COOKIE`

Puedes acceder al array `$_COOKIE` a través de la propiedad `cookies`:

```php
$myCookieValue = Flight::request()->cookies['myCookieName'];
```

## `$_SERVER`

Hay un atajo disponible para acceder al array `$_SERVER` a través del método `getVar()`:

```php
$host = Flight::request()->getVar('HTTP_HOST');
```

## Accediendo a Archivos Subidos a través de `$_FILES`

Puedes acceder a los archivos subidos a través de la propiedad `files`:

```php
$uploadedFile = Flight::request()->files['myFile'];
```

## Procesamiento de Subidas de Archivos (v3.12.0)

Puedes procesar subidas de archivos usando el framework con algunos métodos de ayuda. Básicamente
se reduce a extraer los datos del archivo de la solicitud y moverlo a una nueva ubicación.

```php
Flight::route('POST /upload', function(){
	// Si tenías un campo de entrada como <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

Si tienes múltiples archivos subidos, puedes iterar a través de ellos:

```php
Flight::route('POST /upload', function(){
	// Si tenías un campo de entrada como <input type="file" name="myFiles[]">
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Nota de Seguridad:** Siempre valida y sanitiza la entrada del usuario, especialmente al lidiar con subidas de archivos. Siempre valida el tipo de extensiones que permitirás subir, pero también deberías validar los "magic bytes" del archivo para asegurarte de que realmente es el tipo de archivo que el usuario reclama. Hay [articles](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [and](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [libraries](https://github.com/RikudouSage/MimeTypeDetector) disponibles para ayudar con esto.

## Encabezados de Solicitud

Puedes acceder a los encabezados de solicitud usando el método `getHeader()` o `getHeaders()`:

```php
// Quizás necesites el encabezado Authorization
$host = Flight::request()->getHeader('Authorization');
// o
$host = Flight::request()->header('Authorization');

// Si necesitas obtener todos los encabezados
$headers = Flight::request()->getHeaders();
// o
$headers = Flight::request()->headers();
```

## Cuerpo de Solicitud

Puedes acceder al cuerpo de la solicitud sin procesar usando el método `getBody()`:

```php
$body = Flight::request()->getBody();
```

## Método de Solicitud

Puedes acceder al método de la solicitud usando la propiedad `method` o el método `getMethod()`:

```php
$method = Flight::request()->method; // en realidad llama a getMethod()
$method = Flight::request()->getMethod();
```

**Nota:** El método `getMethod()` primero obtiene el método de `$_SERVER['REQUEST_METHOD']`, luego puede ser sobrescrito 
por `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` si existe o `$_REQUEST['_method']` si existe.

## URLs de Solicitud

Hay un par de métodos de ayuda para ensamblar partes de una URL para tu conveniencia.

### URL Completa

Puedes acceder a la URL de solicitud completa usando el método `getFullUrl()`:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```

### URL Base

Puedes acceder a la URL base usando el método `getBaseUrl()`:

```php
$url = Flight::request()->getBaseUrl();
// Notice, no trailing slash.
// https://example.com
```

## Análisis de Consultas

Puedes pasar una URL al método `parseQuery()` para analizar la cadena de consulta en un array asociativo:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```