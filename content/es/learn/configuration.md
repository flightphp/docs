# Configuración

Puedes personalizar ciertos comportamientos de Flight configurando valores de configuración a través del método `set`.

```php
Flight::set('flight.log_errors', true);
```

A continuación se presenta una lista de todas las configuraciones disponibles:

- **flight.base_url** - Anular la URL base de la solicitud. (por defecto: null)
- **flight.case_sensitive** - Coincidencia sensible a mayúsculas y minúsculas para URL. (por defecto: falso)
- **flight.handle_errors** - Permitir que Flight maneje todos los errores internamente. (por defecto: true)
- **flight.log_errors** - Registrar errores en el archivo de registro de errores del servidor web. (por defecto: falso)
- **flight.views.path** - Directorio que contiene archivos de plantillas de vista. (por defecto: ./views)
- **flight.views.extension** - Extensión de archivo de plantilla de vista. (por defecto: .php)