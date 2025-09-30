# Configuración

## Resumen

Flight proporciona una forma sencilla de configurar varios aspectos del framework para adaptarlos a las necesidades de su aplicación. Algunos se establecen por defecto, pero puede anularlos según sea necesario. También puede establecer sus propias variables para usarlas en toda su aplicación.

## Comprensión

Puede personalizar ciertos comportamientos de Flight estableciendo valores de configuración a través del método `set`.

```php
Flight::set('flight.log_errors', true);
```

En el archivo `app/config/config.php`, puede ver todas las variables de configuración predeterminadas disponibles para usted.

## Uso Básico

### Opciones de Configuración de Flight

A continuación se presenta una lista de todas las configuraciones disponibles:

- **flight.base_url** `?string` - Anula la URL base de la solicitud si Flight se ejecuta en un subdirectorio. (predeterminado: null)
- **flight.case_sensitive** `bool` - Coincidencia sensible a mayúsculas y minúsculas para URLs. (predeterminado: false)
- **flight.handle_errors** `bool` - Permite que Flight maneje todos los errores internamente. (predeterminado: true)
  - Si desea que Flight maneje los errores en lugar del comportamiento predeterminado de PHP, esto debe ser true.
  - Si tiene [Tracy](/awesome-plugins/tracy) instalado, debe establecer esto en false para que Tracy pueda manejar los errores.
  - Si tiene el plugin [APM](/awesome-plugins/apm) instalado, debe establecer esto en true para que APM pueda registrar los errores.
- **flight.log_errors** `bool` - Registra errores en el archivo de registro de errores del servidor web. (predeterminado: false)
  - Si tiene [Tracy](/awesome-plugins/tracy) instalado, Tracy registrará errores basados en las configuraciones de Tracy, no en esta configuración.
- **flight.views.path** `string` - Directorio que contiene archivos de plantillas de vista. (predeterminado: ./views)
- **flight.views.extension** `string` - Extensión de archivo de plantilla de vista. (predeterminado: .php)
- **flight.content_length** `bool` - Establece el encabezado `Content-Length`. (predeterminado: true)
  - Si está utilizando [Tracy](/awesome-plugins/tracy), esto debe establecerse en false para que Tracy pueda renderizarse correctamente.
- **flight.v2.output_buffering** `bool` - Usa el búfer de salida heredado. Vea [migrando a v3](migrating-to-v3). (predeterminado: false)

### Configuración del Cargador

Adicionalmente, hay otra configuración para el cargador. Esto le permitirá cargar clases automáticamente con `_` en el nombre de la clase.

```php
// Habilitar la carga de clases con guiones bajos
// Predeterminado a true
Loader::$v2ClassLoading = false;
```

### Variables

Flight le permite guardar variables para que puedan usarse en cualquier lugar de su aplicación.

```php
// Guardar su variable
Flight::set('id', 123);

// En otro lugar de su aplicación
$id = Flight::get('id');
```

Para ver si se ha establecido una variable, puede hacer:

```php
if (Flight::has('id')) {
  // Hacer algo
}
```

Puede eliminar una variable haciendo:

```php
// Elimina la variable id
Flight::clear('id');

// Elimina todas las variables
Flight::clear();
```

> **Nota:** Solo porque puede establecer una variable no significa que deba hacerlo. Use esta función con moderación. La razón es que cualquier cosa almacenada aquí se convierte en una variable global. Las variables globales son malas porque pueden cambiarse desde cualquier lugar de su aplicación, lo que hace difícil rastrear errores. Además, esto puede complicar cosas como [pruebas unitarias](/guides/unit-testing).

### Errores y Excepciones

Todos los errores y excepciones son capturados por Flight y pasados al método `error` si `flight.handle_errors` está establecido en true.

El comportamiento predeterminado es enviar una respuesta genérica `HTTP 500 Internal Server Error` con algo de información de error.

Puede [anular](/learn/extending) este comportamiento para sus propias necesidades:

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

#### 404 No Encontrado

Cuando no se puede encontrar una URL, Flight llama al método `notFound`. El comportamiento predeterminado es enviar una respuesta `HTTP 404 Not Found` con un mensaje simple.

Puede [anular](/learn/extending) este comportamiento para sus propias necesidades:

```php
Flight::map('notFound', function () {
  // Manejar no encontrado
});
```

## Ver También
- [Extendiendo Flight](/learn/extending) - Cómo extender y personalizar la funcionalidad principal de Flight.
- [Pruebas Unitarias](/guides/unit-testing) - Cómo escribir pruebas unitarias para su aplicación Flight.
- [Tracy](/awesome-plugins/tracy) - Un plugin para manejo avanzado de errores y depuración.
- [Extensiones de Tracy](/awesome-plugins/tracy_extensions) - Extensiones para integrar Tracy con Flight.
- [APM](/awesome-plugins/apm) - Un plugin para monitoreo de rendimiento de aplicaciones y seguimiento de errores.

## Solución de Problemas
- Si tiene problemas para descubrir todos los valores de su configuración, puede hacer `var_dump(Flight::get());`

## Registro de Cambios
- v3.5.0 - Agregada configuración para `flight.v2.output_buffering` para soportar el comportamiento de búfer de salida heredado.
- v2.0 - Configuraciones principales agregadas.