# Solicitudes

Flight encapsula la solicitud HTTP en un solo objeto, que puede ser
accedido haciendo:

```php
$request = Flight::request();
```

## Casos de Uso Típicos

Cuando estás trabajando con una solicitud en una aplicación web, típicamente querrás
extraer un encabezado, o un parámetro `$_GET` o `$_POST`, o quizás
incluso el cuerpo de la solicitud en bruto. Flight proporciona una interfaz simple para hacer todas estas cosas.

Aquí hay un ejemplo de obtención de un parámetro de la cadena de consulta:

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "Estás buscando: $keyword";
	// consulta una base de datos o algo más con el $keyword
});
```

Aquí hay un ejemplo de tal vez un formulario con un método POST:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "Has enviado: $name, $email";
	// guarda en una base de datos o algo más con el $name y $email
});
```

## Propiedades del Objeto Solicitud

El objeto solicitud proporciona las siguientes propiedades:

- **body** - El cuerpo de la solicitud HTTP en bruto
- **url** - La URL solicitada
- **base** - El subdirectorio padre de la URL
- **method** - El método de solicitud (GET, POST, PUT, DELETE)
- **referrer** - La URL de referencia
- **ip** - Dirección IP del cliente
- **ajax** - Si la solicitud es una solicitud AJAX
- **scheme** - El protocolo del servidor (http, https)
- **user_agent** - Información del navegador
- **type** - El tipo de contenido
- **length** - La longitud del contenido
- **query** - Parámetros de la cadena de consulta
- **data** - Datos de la publicación o datos JSON
- **cookies** - Datos de cookies
- **files** - Archivos subidos
- **secure** - Si la conexión es segura
- **accept** - Parámetros de aceptación HTTP
- **proxy_ip** - Dirección IP del proxy del cliente. Escanea el arreglo `$_SERVER` en busca de `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` en ese orden.
- **host** - El nombre de host de la solicitud

Puedes acceder a las propiedades `query`, `data`, `cookies` y `files`
como matrices u objetos.

Así que, para obtener un parámetro de la cadena de consulta, puedes hacer:

```php
$id = Flight::request()->query['id'];
```

O puedes hacer:

```php
$id = Flight::request()->query->id;
```

## Cuerpo de Solicitud RAW

Para obtener el cuerpo de la solicitud HTTP en bruto, por ejemplo al manejar solicitudes PUT,
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

Puedes acceder al arreglo `$_GET` a través de la propiedad `query`:

```php
$id = Flight::request()->query['id'];
```

## `$_POST`

Puedes acceder al arreglo `$_POST` a través de la propiedad `data`:

```php
$id = Flight::request()->data['id'];
```

## `$_COOKIE`

Puedes acceder al arreglo `$_COOKIE` a través de la propiedad `cookies`:

```php
$myCookieValue = Flight::request()->cookies['myCookieName'];
```

## `$_SERVER`

Hay un acceso directo disponible para acceder al arreglo `$_SERVER` a través del método `getVar()`:

```php

$host = Flight::request()->getVar['HTTP_HOST'];
```

## Accediendo a Archivos Subidos a través de `$_FILES`

Puedes acceder a archivos subidos a través de la propiedad `files`:

```php
$uploadedFile = Flight::request()->files['myFile'];
```

## Procesamiento de Cargas de Archivos

Puedes procesar cargas de archivos utilizando el framework con algunos métodos de ayuda. Básicamente
se reduce a extraer los datos del archivo de la solicitud y moverlos a una nueva ubicación.

```php
Flight::route('POST /upload', function(){
	// Si tuvieras un campo de entrada como <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

Si tienes múltiples archivos subidos, puedes recorrerlos:

```php
Flight::route('POST /upload', function(){
	// Si tuvieras un campo de entrada como <input type="file" name="myFiles[]">
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Nota de Seguridad:** Siempre valida y desinfecta la entrada del usuario, especialmente al tratar con cargas de archivos. Siempre valida el tipo de extensiones que permitirás que se suban, pero también debes validar los "bytes mágicos" del archivo para asegurarte de que es realmente el tipo de archivo que el usuario dice que es. Hay [artículos](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [y](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [bibliotecas](https://github.com/RikudouSage/MimeTypeDetector) disponibles para ayudar con esto.

## Encabezados de Solicitud

Puedes acceder a los encabezados de solicitud usando el método `getHeader()` o `getHeaders()`:

```php

// Tal vez necesites el encabezado de Autorización
$host = Flight::request()->getHeader('Authorization');
// o
$host = Flight::request()->header('Authorization');

// Si necesitas obtener todos los encabezados
$headers = Flight::request()->getHeaders();
// o
$headers = Flight::request()->headers();
```

## Cuerpo de Solicitud

Puedes acceder al cuerpo de la solicitud en bruto utilizando el método `getBody()`:

```php
$body = Flight::request()->getBody();
```

## Método de Solicitud

Puedes acceder al método de solicitud utilizando la propiedad `method` o el método `getMethod()`:

```php
$method = Flight::request()->method; // en realidad llama a getMethod()
$method = Flight::request()->getMethod();
```

**Nota:** El método `getMethod()` primero obtiene el método de `$_SERVER['REQUEST_METHOD']`, luego puede ser sobrescrito 
por `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` si existe o `$_REQUEST['_method']` si existe.

## URLs de Solicitud

Hay un par de métodos auxiliares para ensamblar partes de una URL para tu conveniencia.

### URL Completa

Puedes acceder a la URL completa de la solicitud usando el método `getFullUrl()`:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```
### URL Base

Puedes acceder a la URL base usando el método `getBaseUrl()`:

```php
$url = Flight::request()->getBaseUrl();
// Nota, sin barra final.
// https://example.com
```

## Análisis de Consultas

Puedes pasar una URL al método `parseQuery()` para analizar la cadena de consulta en un arreglo asociativo:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```