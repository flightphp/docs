# Manejo de Errores

## Errores y Excepciones

Todos los errores y excepciones son capturados por Flight y pasados al método `error`.
El comportamiento predeterminado es enviar una respuesta genérica de `HTTP 500 Internal Server Error` con información sobre el error.

Puede anular este comportamiento según sus necesidades:

```php
Flight::map('error', function (Throwable $error) {
  // Manejar error
  echo $error->getTraceAsString();
});
```

Por defecto, los errores no se registran en el servidor web. Puede habilitar esto cambiando la configuración:

```php
Flight::set('flight.log_errors', true);
```

## No Encontrado

Cuando una URL no se puede encontrar, Flight llama al método `notFound`. El comportamiento predeterminado es enviar una respuesta de `HTTP 404 Not Found` con un mensaje simple.

Puede anular este comportamiento según sus necesidades:

```php
Flight::map('notFound', function () {
  // Manejar no encontrado
});
```