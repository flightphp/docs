# Configuración

Puede personalizar ciertos comportamientos de Flight configurando valores de configuración a través del método `set`.

```php
Flight::set('flight.log_errors', true);
```

La siguiente es una lista de todas las configuraciones disponibles:

- **flight.base_url** - Anular la URL base de la solicitud. (predeterminado: null)
- **flight.case_sensitive** - Coincidencia sensible a mayúsculas y minúsculas para URLs. (predeterminado: false)
- **flight.handle_errors** - Permitir que Flight maneje todos los errores internamente. (predeterminado: true)
- **flight.log_errors** - Registrar errores en el archivo de registro de errores del servidor web. (predeterminado: false)
- **flight.views.path** - Directorio que contiene archivos de plantillas de vista. (predeterminado: ./views)
- **flight.views.extension** - Extensión del archivo de plantilla de vista. (predeterminado: .php)