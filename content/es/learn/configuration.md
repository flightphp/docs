# Configuración

Puedes personalizar ciertos comportamientos de Flight configurando los valores de configuración a través del método `set`.

```php
Flight::set('flight.log_errors', true);
```

## Configuraciones Disponibles

La siguiente es una lista de todas las configuraciones disponibles:

- **flight.base_url** `?string` - Reemplaza la URL base de la solicitud. (predeterminado: null)
- **flight.case_sensitive** `bool` - Coincidencia sensible a mayúsculas y minúsculas para las URL. (predeterminado: false)
- **flight.handle_errors** `bool` - Permite a Flight manejar todos los errores internamente. (predeterminado: true)
- **flight.log_errors** `bool` - Registra los errores en el archivo de registro de errores del servidor web. (predeterminado: false)
- **flight.views.path** `string` - Directorio que contiene archivos de plantillas de vista. (predeterminado: ./views)
- **flight.views.extension** `string` - Extensión del archivo de plantilla de vista. (predeterminado: .php)
- **flight.content_length** `bool` - Establece el encabezado `Content-Length`. (predeterminado: true)
- **flight.v2.output_buffering** `bool` - Utiliza el almacenamiento en búfer de salida heredado. Consulta [migrating to v3](migrating-to-v3). (predeterminado: false)

## Variables

Flight te permite guardar variables para que puedan ser utilizadas en cualquier parte de tu aplicación.

```php
// Guarda tu variable
Flight::set('id', 123);

// En otra parte de tu aplicación
$id = Flight::get('id');
```
Para verificar si una variable ha sido establecida, puedes hacer:

```php
if (Flight::has('id')) {
  // Haz algo
}
```

Puedes limpiar una variable haciendo:

```php
// Limpia la variable id
Flight::clear('id');

// Limpia todas las variables
Flight::clear();
```

Flight también utiliza variables con fines de configuración.

```php
Flight::set('flight.log_errors', true);
```

## Manejo de Errores

### Errores y Excepciones

Todos los errores y excepciones son capturados por Flight y pasados al método `error`. El comportamiento predeterminado es enviar una respuesta genérica de `HTTP 500 Internal Server Error` con algo de información sobre el error.

Puedes anular este comportamiento para tus propias necesidades:

```php
Flight::map('error', function (Throwable $error) {
  // Manejar error
  echo $error->getTraceAsString();
});
```

De forma predeterminada, los errores no se registran en el servidor web. Puedes habilitar esto cambiando la configuración:

```php
Flight::set('flight.log_errors', true);
```

### No Encontrado

Cuando no se puede encontrar una URL, Flight llama al método `notFound`. El comportamiento predeterminado es enviar una respuesta de `HTTP 404 Not Found` con un mensaje simple.

Puedes anular este comportamiento para tus propias necesidades:

```php
Flight::map('notFound', function () {
  // Manejar no encontrado
});
```