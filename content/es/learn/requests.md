# Solicitudes

Flight encapsula la solicitud HTTP en un solo objeto, al cual se puede acceder así:

```php
$solicitud = Flight::solicitud();
```

El objeto de solicitud proporciona las siguientes propiedades:

- **cuerpo** - El cuerpo sin procesar de la solicitud HTTP
- **url** - La URL solicitada
- **base** - El subdirectorio padre de la URL
- **método** - El método de solicitud (GET, POST, PUT, DELETE)
- **refererente** - La URL del referente
- **ip** - Dirección IP del cliente
- **ajax** - Si la solicitud es una solicitud AJAX
- **esquema** - El protocolo del servidor (http, https)
- **user_agent** - Información del navegador
- **tipo** - El tipo de contenido
- **longitud** - La longitud del contenido
- **query** - Parámetros de la cadena de consulta
- **datos** - Datos de la publicación o datos JSON
- **cookies** - Datos de cookies
- **archivos** - Archivos subidos
- **seguro** - Si la conexión es segura
- **aceptar** - Parámetros de aceptación HTTP
- **proxy_ip** - Dirección IP del proxy del cliente
- **host** - El nombre del host de la solicitud

Se puede acceder a las propiedades `query`, `datos`, `cookies` y `archivos`
como arreglos u objetos.

Entonces, para obtener un parámetro de cadena de consulta, puedes hacer:

```php
$id = Flight::solicitud()->query['id'];
```

O puedes hacer:

```php
$id = Flight::solicitud()->query->id;
```

## Cuerpo de Solicitud sin Procesar

Para obtener el cuerpo sin procesar de la solicitud HTTP, por ejemplo, al tratar con solicitudes PUT,
puedes hacer:

```php
$cuerpo = Flight::solicitud()->getBody();
```

## Entrada JSON

Si envías una solicitud con el tipo `application/json` y los datos `{"id": 123}`
estará disponible desde la propiedad `datos`:

```php
$id = Flight::solicitud()->datos->id;
```

## Acceso a `$_SERVER`

Hay un atajo disponible para acceder a la matriz `$_SERVER` a través del método `getVar()`:

```php
$host = Flight::solicitud()->getVar['HTTP_HOST'];
```

## Accediendo a Cabeceras de Solicitud

Puedes acceder a las cabeceras de la solicitud utilizando el método `getHeader()` o `getHeaders()`:

```php

// Tal vez necesites la cabecera de Autorización
$host = Flight::solicitud()->getHeader('Authorization');

// Si necesitas obtener todas las cabeceras
$cabeceras = Flight::solicitud()->getHeaders();
```