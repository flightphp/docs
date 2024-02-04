# Manejo de Errores

## Errores y Excepciones

Todos los errores y excepciones son capturados por Flight y pasados al método `error`.
El comportamiento por defecto es enviar una respuesta genérica de `HTTP 500 Error Interno del Servidor` con información del error.

Puedes sobrescribir este comportamiento para tus propias necesidades:

```php
Flight::map('error', function (Throwable $error) {
  // Manejar error
  echo $error->getTraceAsString();
});
```

Por defecto, los errores no se registran en el servidor web. Puedes habilitar esto cambiando la configuración:

```php
Flight::set('flight.log_errors', true);
```

## No Encontrado

Cuando una URL no se puede encontrar, Flight llama al método `notFound`. El comportamiento
por defecto es enviar una respuesta de `HTTP 404 No Encontrado` con un mensaje simple.

Puedes sobrescribir este comportamiento para tus propias necesidades:

```php
Flight::map('notFound', function () {
  // Manejar no encontrado
});
```