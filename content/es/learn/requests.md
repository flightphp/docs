# Solicitudes

Flight encapsula la solicitud HTTP en un solo objeto, al cual se puede acceder
haciendo:

```php
$solicitud = Flight::request();
```

El objeto de solicitud proporciona las siguientes propiedades:

- **url** - La URL que se solicita
- **base** - El subdirectorio padre de la URL
- **method** - El método de solicitud (GET, POST, PUT, DELETE)
- **referrer** - La URL del referente
- **ip** - Dirección IP del cliente
- **ajax** - Si la solicitud es una solicitud AJAX
- **scheme** - El protocolo del servidor (http, https)
- **user_agent** - Información del navegador
- **type** - El tipo de contenido
- **length** - La longitud del contenido
- **query** - Parámetros de cadena de consulta
- **data** - Datos de publicación o datos JSON
- **cookies** - Datos de la cookie
- **files** - Archivos cargados
- **secure** - Si la conexión es segura
- **accept** - Parámetros de aceptación HTTP
- **proxy_ip** - Dirección IP del proxy del cliente
- **host** - El nombre del host de la solicitud

Se puede acceder a las propiedades `query`, `data`, `cookies` y `files`
como arreglos u objetos.

Entonces, para obtener un parámetro de cadena de consulta, puedes hacer:

```php
$id = Flight::request()->query['id'];
```

O puedes hacer:

```php
$id = Flight::request()->query->id;
```

## Cuerpo de Solicitud CRUDA

Para obtener el cuerpo de la solicitud HTTP sin procesar, por ejemplo al tratar con solicitudes PUT,
puedes hacer:

```php
$cuerpo = Flight::request()->getBody();
```

## Entrada JSON

Si envías una solicitud con el tipo `application/json` y los datos `{"id": 123}`
estará disponible desde la propiedad `data`:

```php
$id = Flight::request()->data->id;
```