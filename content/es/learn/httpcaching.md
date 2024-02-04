# Caché HTTP

Flight proporciona soporte incorporado para la caché a nivel HTTP. Si se cumple la condición de caché, Flight devolverá una respuesta HTTP `304 No modificado`. La próxima vez que el cliente solicite el mismo recurso, se le pedirá que utilice su versión en caché local.

## Última modificación

Puedes usar el método `lastModified` y pasarle una marca de tiempo UNIX para establecer la fecha y hora en que se modificó por última vez una página. El cliente seguirá utilizando su caché hasta que se modifique el valor de la última modificación.

```php
Flight::route('/noticias', function () {
  Flight::lastModified(1234567890);
  echo 'Este contenido será almacenado en caché.';
});
```

## ETag

La caché de `ETag` es similar a `Última modificación`, excepto que puedes especificar cualquier identificador que desees para el recurso:

```php
Flight::route('/noticias', function () {
  Flight::etag('mi-identificador-único');
  echo 'Este contenido será almacenado en caché.';
});
```

Ten en cuenta que llamar a `lastModified` o `etag` establecerá y comprobará el valor de la caché. Si el valor de la caché es el mismo entre las solicitudes, Flight enviará inmediatamente una respuesta `HTTP 304` y detendrá el procesamiento.