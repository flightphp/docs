# Configuración

Puede personalizar ciertos comportamientos de Flight configurando valores de configuración a través del método 'set'.

```php
Flight::set('flight.log_errors', true);
```

## Configuraciones Disponibles

La siguiente es una lista de todas las configuraciones disponibles:

- **flight.base_url** `?string` - Anular la URL base de la solicitud. (por defecto: null)
- **flight.case_sensitive** `bool` - Coincidencia sensible a mayúsculas y minúsculas para las URL. (por defecto: false)
- **flight.handle_errors** `bool` - Permitir que Flight maneje todos los errores internamente. (por defecto: true)
- **flight.log_errors** `bool` - Registrar errores en el archivo de registro de errores del servidor web. (por defecto: false)
- **flight.views.path** `string` - Directorio que contiene archivos de plantillas de vista. (por defecto: ./views)
- **flight.views.extension** `string` - Extensión del archivo de plantilla de vista. (por defecto: .php)
- **flight.content_length** `bool` - Establecer la cabecera `Content-Length`. (por defecto: true)
- **flight.v2.output_buffering** `bool` - Usar el almacenamiento en búfer de salida heredado. Consulte [migrating to v3](migrating-to-v3). (por defecto: false)

## Configuración del Loader

Adicionalmente, hay otra configuración del cargador. Esto le permitirá cargar clases con `_` en el nombre de la clase.

```php
// Habilitar la carga de clase con guiones bajos
// Predeterminado a true
Loader::$v2ClassLoading = false;
```

## Variables

Flight le permite guardar variables para que puedan ser utilizadas en cualquier lugar de su aplicación.

```php
// Guarde su variable
Flight::set('id', 123);

// En otro lugar de su aplicación
$id = Flight::get('id');
```

Para ver si una variable ha sido establecida, puede hacerlo así:

```php
if (Flight::has('id')) {
  // Hacer algo
}
```

Puede borrar una variable haciendo:

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

Todos los errores y excepciones son capturados por Flight y pasados al método 'error'.
El comportamiento predeterminado es enviar una respuesta genérica de 'HTTP 500 Internal Server Error' con alguna información de error.

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

### No Encontrado

Cuando no se puede encontrar una URL, Flight llama al método 'notFound'. El comportamiento predeterminado es enviar una respuesta de 'HTTP 404 Not Found' con un mensaje simple.

Puede anular este comportamiento según sus necesidades:

```php
Flight::map('notFound', function () {
  // Manejar no encontrado
});
```