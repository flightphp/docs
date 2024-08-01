# Solicitudes

Flight encapsula la solicitud HTTP en un solo objeto, al cual se puede acceder haciendo:

```php
$solicitud = Flight::request();
```

## Casos de Uso Típicos

Cuando estás trabajando con una solicitud en una aplicación web, típicamente querrás extraer un encabezado, o un parámetro `$_GET` o `$_POST`, o tal vez incluso el cuerpo de la solicitud sin procesar. Flight proporciona una interfaz sencilla para hacer todas estas cosas.

Aquí tienes un ejemplo de cómo obtener un parámetro de cadena de consulta:

```php
Flight::route('/buscar', function(){
	$palabraClave = Flight::request()->query['palabraClave'];
	echo "Estás buscando: $palabraClave";
	// buscar en una base de datos u otra cosa con la $palabraClave
});
```

Aquí tienes un ejemplo tal vez de un formulario con un método POST:

```php
Flight::route('POST /enviar', function(){
	$nombre = Flight::request()->data['nombre'];
	$email = Flight::request()->data['email'];
	echo "Has enviado: $nombre, $email";
	// guardar en una base de datos u otra cosa con el $nombre y $email
});
```

## Propiedades del Objeto de Solicitud

El objeto de solicitud proporciona las siguientes propiedades:

- **body** - El cuerpo de la solicitud HTTP sin procesar
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
- **query** - Parámetros de cadena de consulta
- **data** - Datos POST o datos JSON
- **cookies** - Datos de cookies
- **files** - Archivos subidos
- **secure** - Si la conexión es segura
- **accept** - Parámetros de aceptación HTTP
- **proxy_ip** - Dirección IP de proxy del cliente. Escanea la matriz `$_SERVER` en busca de `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED` en ese orden.
- **host** - El nombre del host solicitado

Puedes acceder a las propiedades `query`, `data`, `cookies` y `files`
como arrays u objetos.

Entonces, para obtener un parámetro de cadena de consulta, puedes hacer:

```php
$id = Flight::request()->query['id'];
```

O puedes hacer:

```php
$id = Flight::request()->query->id;
```

## Cuerpo de Solicitud SIN Procesar

Para obtener el cuerpo de la solicitud HTTP sin procesar, por ejemplo cuando se trata de solicitudes PUT, puedes hacer:

```php
$cuerpo = Flight::request()->getBody();
```

## Entrada JSON

Si envías una solicitud con el tipo `application/json` y los datos `{"id": 123}`
estarán disponibles en la propiedad `data`:

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
$miValorCookie = Flight::request()->cookies['miNombreCookie'];
```

## `$_SERVER`

Hay un atajo disponible para acceder al array `$_SERVER` a través del método `getVar()`:

```php
$host = Flight::request()->getVar['HTTP_HOST'];
```

## Archivos Subidos via `$_FILES`

Puedes acceder a archivos subidos a través de la propiedad `files`:

```php
$archivoSubido = Flight::request()->files['miArchivo'];
```

## Encabezados de Solicitud

Puedes acceder a los encabezados de solicitud utilizando los métodos `getHeader()` o `getHeaders()`:

```php

// Tal vez necesitas el encabezado de Autorización
$host = Flight::request()->getHeader('Authorization');
// o
$host = Flight::request()->header('Authorization');

// Si necesitas obtener todos los encabezados
$headers = Flight::request()->getHeaders();
// o
$headers = Flight::request()->headers();
```

## Cuerpo de la Solicitud

Puedes acceder al cuerpo de la solicitud utilizando el método `getBody()`:

```php
$body = Flight::request()->getBody();
```

## Método de Solicitud

Puedes acceder al método de solicitud utilizando la propiedad `method` o el método `getMethod()`:

```php
$metodo = Flight::request()->method; // en realidad llama a getMethod()
$metodo = Flight::request()->getMethod();
```

**Nota:** El método `getMethod()` primero extrae el método de `$_SERVER['REQUEST_METHOD']`, luego puede ser sobrescrito
por `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` si existe o `$_REQUEST['_method']` si existe.

## URLs de Solicitud

Hay un par de métodos auxiliares para unir partes de una URL para tu conveniencia.

### URL Completa

Puedes acceder a la URL completa de la solicitud utilizando el método `getFullUrl()`:

```php
$url = Flight::request()->getFullUrl();
// https://ejemplo.com/algun/destino?foo=bar
```
### URL Base

Puedes acceder a la URL base utilizando el método `getBaseUrl()`:

```php
$url = Flight::request()->getBaseUrl();
// Nota, sin barra inclinada al final.
// https://ejemplo.com
```

## Análisis de Consulta

Puedes pasar una URL al método `parseQuery()` para analizar la cadena de consulta en un arreglo asociativo:

```php
$consulta = Flight::request()->parseQuery('https://ejemplo.com/algun/destino?foo=bar');
// ['foo' => 'bar']
```