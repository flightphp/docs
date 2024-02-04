# Solicitudes

El vuelo encapsula la solicitud HTTP en un solo objeto, al cual se puede acceder haciendo:

```php
$request = Flight::request();
```

El objeto de solicitud proporciona las siguientes propiedades:

- **url** - La URL solicitada
- **base** - El subdirectorio principal de la URL
- **method** - El método de solicitud (GET, POST, PUT, DELETE)
- **referrer** - La URL del remitente
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
- **accept** - Parámetros de aceptación HTTP
- **proxy_ip** - Dirección IP del proxy del cliente
- **host** - El nombre del host de la solicitud

Se puede acceder a las propiedades `query`, `data`, `cookies` y `files` como arreglos u objetos.

Entonces, para obtener un parámetro de cadena de consulta, puedes hacer:

```php
$id = Flight::request()->query['id'];
```

O también puedes hacer:

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

Si envías una solicitud con el tipo `application/json` y los datos `{"id": 123}`,
se podrá acceder desde la propiedad `data`:

```php
$id = Flight::request()->data->id;
```