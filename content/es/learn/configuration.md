# Configuración

Puede personalizar ciertos comportamientos de Flight configurando los valores de configuración a través del método `set`.

```php
Flight::set('flight.log_errors', true);
```

## Configuraciones Disponibles

La siguiente es una lista de todas las configuraciones disponibles:

- **flight.base_url** - Anular la URL base de la solicitud. (predeterminado: nulo)
- **flight.case_sensitive** - Coincidencia sensible a mayúsculas y minúsculas para las URL. (predeterminado: falso)
- **flight.handle_errors** - Permitir que Flight maneje todos los errores internamente. (predeterminado: verdadero)
- **flight.log_errors** - Registrar errores en el archivo de registro de errores del servidor web. (predeterminado: falso)
- **flight.views.path** - Directorio que contiene archivos de plantillas de vista. (predeterminado: ./views)
- **flight.views.extension** - Extensión de archivo de plantilla de vista. (predeterminado: .php)

## Variables

Flight le permite guardar variables para que puedan ser utilizadas en cualquier parte de su aplicación.

```php
// Guarda tu variable
Flight::set('id', 123);

// En otro lugar de tu aplicación
$id = Flight::get('id');
```
Para ver si una variable ha sido establecida, puede hacer lo siguiente:

```php
if (Flight::has('id')) {
  // Haz algo
}
```

Puede borrar una variable haciendo lo siguiente:

```php
// Borra la variable id
Flight::clear('id');

// Borra todas las variables
Flight::clear();
```

Flight también utiliza variables con fines de configuración.

```php
Flight::set('flight.log_errors', true);
```

## Manejo de Errores

### Errores y Excepciones

Todos los errores y excepciones son capturados por Flight y pasados al método `error`.
El comportamiento predeterminado es enviar una respuesta genérica de `HTTP 500 Internal Server Error` con cierta información de error.

Puede anular este comportamiento según sus necesidades:

```php
Flight::map('error', function (Throwable $error) {
  // Manejar error
  echo $error->getTraceAsString();
});
```

De forma predeterminada, los errores no se registran en el servidor web. Puede habilitar esto cambiando la configuración:

```php
Flight::set('flight.log_errors', true);
```

### No Encontrado

Cuando una URL no se puede encontrar, Flight llama al método `notFound`. El comportamiento predeterminado es enviar una respuesta de `HTTP 404 Not Found` con un mensaje simple.

Puede anular este comportamiento según sus necesidades:

```php
Flight::map('notFound', function () {
  // Manejar no encontrado
});
```