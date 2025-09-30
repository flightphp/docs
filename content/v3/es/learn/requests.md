# Solicitudes

## Resumen

Flight encapsula la solicitud HTTP en un solo objeto, que se puede acceder haciendo:

```php
$request = Flight::request();
```

## Comprensión

Las solicitudes HTTP son uno de los aspectos centrales para entender sobre el ciclo de vida de HTTP. Un usuario realiza una acción en un navegador web o un cliente HTTP, y envían una serie de encabezados, cuerpo, URL, etc. a tu proyecto. Puedes capturar estos encabezados (el idioma del navegador, qué tipo de compresión pueden manejar, el agente de usuario, etc.) y capturar el cuerpo y la URL que se envía a tu aplicación Flight. Estas solicitudes son esenciales para que tu app entienda qué hacer a continuación.

## Uso Básico

PHP tiene varios super globals incluyendo `$_GET`, `$_POST`, `$_REQUEST`, `$_SERVER`, `$_FILES` y `$_COOKIE`. Flight abstrae estos en [Collections](/learn/collections) prácticas. Puedes acceder a las propiedades `query`, `data`, `cookies` y `files` como arrays u objetos.

> **Nota:** Se **DESACONSEJA EN ALTO GRADO** usar estos super globals en tu proyecto y deben referenciarse a través del objeto `request()`.

> **Nota:** No hay abstracción disponible para `$_ENV`.

### `$_GET`

Puedes acceder al array `$_GET` a través de la propiedad `query`:

```php
// GET /search?keyword=something
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	// o
	$keyword = Flight::request()->query->keyword;
	echo "Estás buscando: $keyword";
	// consulta una base de datos o algo más con el $keyword
});
```

### `$_POST`

Puedes acceder al array `$_POST` a través de la propiedad `data`:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	// o
	$name = Flight::request()->data->name;
	$email = Flight::request()->data->email;
	echo "Enviaste: $name, $email";
	// guarda en una base de datos o algo más con el $name y $email
});
```

### `$_COOKIE`

Puedes acceder al array `$_COOKIE` a través de la propiedad `cookies`:

```php
Flight::route('GET /login', function(){
	$savedLogin = Flight::request()->cookies['myLoginCookie'];
	// o
	$savedLogin = Flight::request()->cookies->myLoginCookie;
	// verifica si realmente está guardado o no y si lo está, inicia sesión automáticamente
	if($savedLogin) {
		Flight::redirect('/dashboard');
		return;
	}
});
```

Para obtener ayuda sobre cómo establecer nuevos valores de cookies, consulta [overclokk/cookie](/awesome-plugins/php-cookie)

### `$_SERVER`

Hay un acceso directo disponible para acceder al array `$_SERVER` a través del método `getVar()`:

```php

$host = Flight::request()->getVar('HTTP_HOST');
```

### `$_FILES`

Puedes acceder a los archivos subidos a través de la propiedad `files`:

```php
// acceso raw a la propiedad $_FILES. Ver abajo para el enfoque recomendado
$uploadedFile = Flight::request()->files['myFile']; 
// o
$uploadedFile = Flight::request()->files->myFile;
```

Consulta [Uploaded File Handler](/learn/uploaded-file) para más información.

#### Procesamiento de Subidas de Archivos

_v3.12.0_

Puedes procesar subidas de archivos usando el framework con algunos métodos de ayuda. Básicamente se reduce a extraer los datos del archivo de la solicitud y moverlo a una nueva ubicación.

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

> **Nota de Seguridad:** Siempre valida y sanitiza la entrada del usuario, especialmente al tratar con subidas de archivos. Siempre valida el tipo de extensiones que permitirás subir, pero también debes validar los "magic bytes" del archivo para asegurarte de que realmente es el tipo de archivo que el usuario afirma que es. Hay [artículos](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [y](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [bibliotecas](https://github.com/RikudouSage/MimeTypeDetector) disponibles para ayudar con esto.

### Cuerpo de la Solicitud

Para obtener el cuerpo raw de la solicitud HTTP, por ejemplo al tratar con solicitudes POST/PUT, puedes hacer:

```php
Flight::route('POST /users/xml', function(){
	$xmlBody = Flight::request()->getBody();
	// haz algo con el XML que fue enviado.
});
```

### Cuerpo JSON

Si recibes una solicitud con el tipo de contenido `application/json` y los datos de ejemplo de `{"id": 123}` estará disponible desde la propiedad `data`:

```php
$id = Flight::request()->data->id;
```

### Encabezados de la Solicitud

Puedes acceder a los encabezados de la solicitud usando el método `getHeader()` o `getHeaders()`:

```php

// Tal vez necesites el encabezado Authorization
$host = Flight::request()->getHeader('Authorization');
// o
$host = Flight::request()->header('Authorization');

// Si necesitas obtener todos los encabezados
$headers = Flight::request()->getHeaders();
// o
$headers = Flight::request()->headers();
```

### Método de la Solicitud

Puedes acceder al método de la solicitud usando la propiedad `method` o el método `getMethod()`:

```php
$method = Flight::request()->method; // realmente poblado por getMethod()
$method = Flight::request()->getMethod();
```

**Nota:** El método `getMethod()` primero extrae el método de `$_SERVER['REQUEST_METHOD']`, luego puede ser sobrescrito por `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` si existe o `$_REQUEST['_method']` si existe.

## Propiedades del Objeto Solicitud

El objeto solicitud proporciona las siguientes propiedades:

- **body** - El cuerpo raw de la solicitud HTTP
- **url** - La URL solicitada
- **base** - El subdirectorio padre de la URL
- **method** - El método de la solicitud (GET, POST, PUT, DELETE)
- **referrer** - La URL de referencia
- **ip** - Dirección IP del cliente
- **ajax** - Si la solicitud es una solicitud AJAX
- **scheme** - El protocolo del servidor (http, https)
- **user_agent** - Información del navegador
- **type** - El tipo de contenido
- **length** - La longitud del contenido
- **query** - Parámetros de la cadena de consulta
- **data** - Datos POST o datos JSON
- **cookies** - Datos de cookies
- **files** - Archivos subidos
- **secure** - Si la conexión es segura
- **accept** - Parámetros de aceptación HTTP
- **proxy_ip** - Dirección IP proxy del cliente. Escanea el array `$_SERVER` para `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` en ese orden.
- **host** - El nombre de host de la solicitud
- **servername** - El SERVER_NAME de `$_SERVER`

## Métodos de Ayuda para URL

Hay un par de métodos de ayuda para ensamblar partes de una URL para tu conveniencia.

### URL Completa

Puedes acceder a la URL completa de la solicitud usando el método `getFullUrl()`:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```

### URL Base

Puedes acceder a la URL base usando el método `getBaseUrl()`:

```php
// http://example.com/path/to/something/cool?query=yes+thanks
$url = Flight::request()->getBaseUrl();
// https://example.com
// Nota, no hay barra final.
```

## Análisis de Consulta

Puedes pasar una URL al método `parseQuery()` para analizar la cadena de consulta en un array asociativo:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```

## Ver También
- [Routing](/learn/routing) - Ve cómo mapear rutas a controladores y renderizar vistas.
- [Responses](/learn/responses) - Cómo personalizar respuestas HTTP.
- [Why a Framework?](/learn/why-frameworks) - Cómo encajan las solicitudes en el panorama general.
- [Collections](/learn/collections) - Trabajando con colecciones de datos.
- [Uploaded File Handler](/learn/uploaded-file) - Manejo de subidas de archivos.

## Solución de Problemas
- `request()->ip` y `request()->proxy_ip` pueden ser diferentes si tu servidor web está detrás de un proxy, balanceador de carga, etc.

## Registro de Cambios
- v3.12.0 - Agregada la capacidad para manejar subidas de archivos a través del objeto solicitud.
- v1.0 - Lanzamiento inicial.