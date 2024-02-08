# Solicitudes

Flight encapsula la solicitud HTTP en un solo objeto, al cual se puede acceder haciendo:

```php
$request = Flight::request();
```

El objeto de solicitud proporciona las siguientes propiedades:

- **body** - El cuerpo de la solicitud HTTP sin procesar
- **url** - La URL solicitada
- **base** - El subdirectorio principal de la URL
- **method** - El método de solicitud (GET, POST, PUT, DELETE)
- **referrer** - La URL del referente
- **ip** - Dirección IP del cliente
- **ajax** - Si la solicitud es una solicitud AJAX
- **scheme** - El protocolo del servidor (http, https)
- **user_agent** - Información del navegador
- **type** - El tipo de contenido
- **length** - La longitud del contenido
- **query** - Parámetros de la cadena de consulta
- **data** - Datos de publicación o datos JSON
- **cookies** - Datos de cookies
- **files** - Archivos subidos
- **secure** - Si la conexión es segura
- **accept** - Parámetros de aceptación de HTTP
- **proxy_ip** - Dirección IP del proxy del cliente
- **host** - El nombre del host de la solicitud

Puedes acceder a las propiedades `query`, `data`, `cookies` y `files`
como arreglos u objetos.

Entonces, para obtener un parámetro de cadena de consulta, puedes hacer:

```php
$id = Flight::request()->query['id'];
```

O puedes hacer:

```php
$id = Flight::request()->query->id;
```

## Cuerpo de Solicitud sin Procesar

Para obtener el cuerpo de la solicitud HTTP sin procesar, por ejemplo cuando se trata de solicitudes PUT,
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

## Accediendo a `$_SERVER`

Hay un atajo disponible para acceder a la matriz `$_SERVER` a través del método `getVar()`:

```php

$host = Flight::request()->getVar['HTTP_HOST'];
```

## Accediendo a Encabezados de Solicitud

Puedes acceder a los encabezados de solicitud utilizando el método `getHeader()` o `getHeaders()`:

```php

// Tal vez necesitas el encabezado de Autorización
$host = Flight::request()->getHeader('Authorization');

// Si necesitas obtener todos los encabezados
$headers = Flight::request()->getHeaders();
```  