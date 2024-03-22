# Configuración

Puede personalizar ciertos comportamientos de Flight estableciendo valores de configuración a través del método `set`.

```php
Flight::set('flight.log_errors', true);
```

## Configuraciones Disponibles

La siguiente es una lista de todas las configuraciones disponibles:

- **flight.base_url** `?string` - Sobrescribe la URL base de la solicitud. (predeterminado: null)
- **flight.case_sensitive** `bool` - Coincidencia sensible a mayúsculas y minúsculas para URL. (predeterminado: false)
- **flight.handle_errors** `bool` - Permite a Flight manejar todos los errores internamente. (predeterminado: true)
- **flight.log_errors** `bool` - Registra errores en el archivo de registro de errores del servidor web. (predeterminado: false)
- **flight.views.path** `string` - Directorio que contiene archivos de plantillas de vista. (predeterminado: ./views)
- **flight.views.extension** `string` - Extensión del archivo de plantilla de vista. (predeterminado: .php)
- **flight.content_length** `bool` - Establece el encabezado `Content-Length`. (predeterminado: true)
- **flight.v2.output_buffering** `bool` - Usa el almacenamiento en búfer de salida heredado. Consulta [migración a v3](migrating-to-v3). (predeterminado: false)

## Variables

Flight le permite guardar variables para que puedan ser utilizadas en cualquier lugar de su aplicación.

```php
// Guarda tu variable
Flight::set('id', 123);

// En otro lugar de tu aplicación
$id = Flight::get('id');
```

Para ver si una variable ha sido establecida, puedes hacer:

```php
if (Flight::has('id')) {
  // Haz algo
}
```

Puede borrar una variable haciendo:

```php
// Borra la variable id
Flight::clear('id');

// Borra todas las variables
Flight::clear();
```

Flight también utiliza variables con propósitos de configuración.

```php
Flight::set('flight.log_errors', true);
```

## Manejo de Errores

### Errores y Excepciones

Todos los errores y excepciones son capturados por Flight y pasados al método `error`.
El comportamiento predeterminado es enviar una respuesta genérica de `HTTP 500 Internal Server Error` con alguna información de error.

Puede anular este comportamiento por sus propias necesidades:

```php
Flight::map('error', function (Throwable $error) {
  // Maneja el error
  echo $error->getTraceAsString();
});
```

Por defecto, los errores no se registran en el servidor web. Puede habilitar esto cambiando la configuración:

```php
Flight::set('flight.log_errors', true);
```

### No Encontrado

Cuando no se puede encontrar una URL, Flight llama al método `notFound`. El comportamiento predeterminado es enviar una respuesta de `HTTP 404 Not Found` con un mensaje simple.

Puede anular este comportamiento por sus propias necesidades:

```php
Flight::map('notFound', function () {
  // Maneja no encontrado
});
```