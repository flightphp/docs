# Redirecciones

Puedes redirigir la solicitud actual utilizando el método `redirect` y pasando
una nueva URL:

```php
Flight::redirect('/nueva/ubicacion');
```

Por defecto, Flight envía un código de estado HTTP 303. Opcionalmente, puedes establecer un
código personalizado:

```php
Flight::redirect('/nueva/ubicacion', 401);
```