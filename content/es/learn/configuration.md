# Configuración

Puede personalizar ciertos comportamientos de Flight configurando valores de configuración a través del método `set`.

```php
Flight::set('flight.log_errors', true);
```

## Configuración Disponible

La siguiente es una lista de todas las configuraciones disponibles:

- **flight.base_url** - Sobrescriba la URL base de la solicitud. (por defecto: null)
- **flight.case_sensitive** - Coincidencia sensible a mayúsculas y minúsculas para URL. (por defecto: false)
- **flight.handle_errors** - Permitir a Flight manejar todos los errores internamente. (por defecto: true)
- **flight.log_errors** - Registrar errores en el archivo de registro de errores del servidor web. (por defecto: false)
- **flight.views.path** - Directorio que contiene archivos de plantillas de vista. (por defecto: ./views)
- **flight.views.extension** - Extensión del archivo de plantilla de vista. (por defecto: .php)

## Variables

Flight le permite guardar variables para que puedan ser utilizadas en cualquier lugar de su aplicación.

```php
// Guarde su variable
Flight::set('id', 123);

// En otro lugar de su aplicación
$id = Flight::get('id');
```

Para ver si una variable ha sido configurada puede hacer:

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

Flight también utiliza variables con propósitos de configuración.

```php
Flight::set('flight.log_errors', true);
```

## Manejo de Errores

### Errores y Excepciones

Todos los errores y excepciones son capturados por Flight y pasados al método `error`. El comportamiento predeterminado es enviar una respuesta genérica de `HTTP 500 Internal Server Error` con alguna información de error.

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

Cuando no se puede encontrar una URL, Flight llama al método `notFound`. El comportamiento predeterminado es enviar una respuesta de `HTTP 404 Not Found` con un mensaje simple.

Puede anular este comportamiento según sus necesidades:

```php
Flight::map('notFound', function () {
  // Manejar no encontrado
});
```