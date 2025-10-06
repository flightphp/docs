# Solicitudes

## Resumen

Flight encapsula la solicitud HTTP en un solo objeto, que se puede acceder haciendo:

```php
$request = Flight::request();
```

## Comprensión

Las solicitudes HTTP son uno de los aspectos centrales para entender sobre el ciclo de vida de HTTP. Un usuario realiza una acción en un navegador web o un cliente HTTP, y envían una serie de encabezados, cuerpo, URL, etc. a su proyecto. Puede capturar estos encabezados (el idioma del navegador, qué tipo de compresión pueden manejar, el agente de usuario, etc.) y capturar el cuerpo y la URL que se envía a su aplicación Flight. Estas solicitudes son esenciales para que su app entienda qué hacer a continuación.

## Uso básico

PHP tiene varios super globals incluyendo `$_GET`, `$_POST`, `$_REQUEST`, `$_SERVER`, `$_FILES` y `$_COOKIE`. Flight abstrae estos en colecciones prácticas [Collections](/learn/collections). Puede acceder a las propiedades `query`, `data`, `cookies` y `files` como arrays u objetos.

> **Nota:** Se **DESACONSEJA EN GRAN MEDIDA** usar estos super globals en su proyecto y deben referenciarse a través del objeto `request()`.

> **Nota:** No hay abstracción disponible para `$_ENV`.

### `$_GET`

Puede acceder al array `$_GET` a través de la propiedad `query`:

```php
// GET /search?keyword=something
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	// o
	$keyword = Flight::request()->query->keyword;
	echo "You are searching for: $keyword";
	// consultar una base de datos o algo más con el $keyword
});
```

### `$_POST`

Puede acceder al array `$_POST` a través de la propiedad `data`:

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	// o
	$name = Flight::request()->data->name;
	$email = Flight::request()->data->email;
	echo "You submitted: $name, $email";
	// guardar en una base de datos o algo más con el $name y $email
});
```

### `$_COOKIE`

Puede acceder al array `$_COOKIE` a través de la propiedad `cookies`:

```php
Flight::route('GET /login', function(){
	$savedLogin = Flight::request()->cookies['myLoginCookie'];
	// o
	$savedLogin = Flight::request()->cookies->myLoginCookie;
	// verificar si realmente está guardado o no y si lo está, iniciar sesión automáticamente
	if($savedLogin) {
		Flight::redirect('/dashboard');
		return;
	}
});
```

Para obtener ayuda sobre cómo establecer nuevos valores de cookies, vea [overclokk/cookie](/awesome-plugins/php-cookie)

### `$_SERVER`

Hay un método abreviado disponible para acceder al array `$_SERVER` a través del método `getVar()`:

```php

$host = Flight::request()->getVar('HTTP_HOST');
```

### `$_FILES`

Puede acceder a los archivos subidos a través de la propiedad `files`:

```php
// acceso crudo a la propiedad $_FILES. Vea abajo para el enfoque recomendado
$uploadedFile = Flight::request()->files['myFile']; 
// o
$uploadedFile = Flight::request()->files->myFile;
```

Vea [Uploaded File Handler](/learn/uploaded-file) para más información.

#### Procesamiento de subidas de archivos

_v3.12.0_

Puede procesar subidas de archivos usando el framework con algunos métodos de ayuda. Básicamente se reduce a extraer los datos del archivo de la solicitud y moverlo a una nueva ubicación.

```php
Flight::route('POST /upload', function(){
	// Si tenía un campo de entrada como <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

Si tiene múltiples archivos subidos, puede iterar a través de ellos:

```php
Flight::route('POST /upload', function(){
	// Si tenía un campo de entrada como <input type="file" name="myFiles[]">
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **Nota de seguridad:** Siempre valide y sanitice la entrada del usuario, especialmente al tratar con subidas de archivos. Siempre valide el tipo de extensiones que permitirá subir, pero también debe validar los "magic bytes" del archivo para asegurar que realmente sea el tipo de archivo que el usuario afirma que es. Hay [artículos](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [y](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [bibliotecas](https://github.com/RikudouSage/MimeTypeDetector) disponibles para ayudar con esto.

### Cuerpo de la solicitud

Para obtener el cuerpo crudo de la solicitud HTTP, por ejemplo al tratar con solicitudes POST/PUT, puede hacer:

```php
Flight::route('POST /users/xml', function(){
	$xmlBody = Flight::request()->getBody();
	// hacer algo con el XML que fue enviado.
});
```

### Cuerpo JSON

Si recibe una solicitud con el tipo de contenido `application/json` y los datos de ejemplo `{"id": 123}`, estará disponible desde la propiedad `data`:

```php
$id = Flight::request()->data->id;
```

### Encabezados de la solicitud

Puede acceder a los encabezados de la solicitud usando el método `getHeader()` o `getHeaders()`:

```php

// Tal vez necesite el encabezado Authorization
$host = Flight::request()->getHeader('Authorization');
// o
$host = Flight::request()->header('Authorization');

// Si necesita obtener todos los encabezados
$headers = Flight::request()->getHeaders();
// o
$headers = Flight::request()->headers();
```

### Método de la solicitud

Puede acceder al método de la solicitud usando la propiedad `method` o el método `getMethod()`:

```php
$method = Flight::request()->method; // realmente poblado por getMethod()
$method = Flight::request()->getMethod();
```

**Nota:** El método `getMethod()` primero extrae el método de `$_SERVER['REQUEST_METHOD']`, luego puede ser sobrescrito por `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` si existe o `$_REQUEST['_method']` si existe.

## Propiedades del objeto de solicitud

El objeto de solicitud proporciona las siguientes propiedades:

- **body** - El cuerpo crudo de la solicitud HTTP
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
- **proxy_ip** - Dirección IP del proxy del cliente. Escanea el array `$_SERVER` en busca de `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` en ese orden.
- **host** - El nombre de host de la solicitud
- **servername** - El SERVER_NAME de `$_SERVER`

## Métodos de ayuda

Hay algunos métodos de ayuda para ensamblar partes de una URL o tratar con ciertos encabezados.

### URL completa

Puede acceder a la URL completa de la solicitud usando el método `getFullUrl()`:

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```

### URL base

Puede acceder a la URL base usando el método `getBaseUrl()`:

```php
// http://example.com/path/to/something/cool?query=yes+thanks
$url = Flight::request()->getBaseUrl();
// https://example.com
// Note, no trailing slash.
```

## Análisis de consultas

Puede pasar una URL al método `parseQuery()` para analizar la cadena de consulta en un array asociativo:

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```

## Negociación de tipos de aceptación de contenido

_v3.17.2_

Puede usar el método `negotiateContentType()` para determinar el mejor tipo de contenido para responder basado en el encabezado `Accept` enviado por el cliente.

```php

// Ejemplo de encabezado Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8
// Lo de abajo define lo que soporta.
$availableTypes = ['application/json', 'application/xml'];
$typeToServe = Flight::request()->negotiateContentType($availableTypes);
if ($typeToServe === 'application/json') {
	// Servir respuesta JSON
} elseif ($typeToServe === 'application/xml') {
	// Servir respuesta XML
} else {
	// Por defecto algo más o lanzar un error
}
```

> **Nota:** Si ninguno de los tipos disponibles se encuentra en el encabezado `Accept`, el método retornará `null`. Si no hay encabezado `Accept` definido, el método retornará el primer tipo en el array `$availableTypes`.

## Ver también
- [Routing](/learn/routing) - Vea cómo mapear rutas a controladores y renderizar vistas.
- [Responses](/learn/responses) - Cómo personalizar respuestas HTTP.
- [Why a Framework?](/learn/why-frameworks) - Cómo las solicitudes encajan en el panorama general.
- [Collections](/learn/collections) - Trabajando con colecciones de datos.
- [Uploaded File Handler](/learn/uploaded-file) - Manejo de subidas de archivos.

## Solución de problemas
- `request()->ip` y `request()->proxy_ip` pueden ser diferentes si su servidor web está detrás de un proxy, balanceador de carga, etc.

## Registro de cambios
- v3.17.2 - Agregado negotiateContentType()
- v3.12.0 - Agregada capacidad para manejar subidas de archivos a través del objeto de solicitud.
- v1.0 - Lanzamiento inicial.