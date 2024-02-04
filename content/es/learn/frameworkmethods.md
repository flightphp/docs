# Métodos del Marco de Trabajo

Flight está diseñado para ser fácil de usar y entender. Lo siguiente es el conjunto completo de métodos para el marco de trabajo. Consta de métodos principales, que son métodos estáticos regulares, y métodos extensibles, que son métodos asignados que pueden ser filtrados o anulados.

## Métodos Principales

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Crea un método personalizado para el marco de trabajo.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Registra una clase en un método de marco de trabajo.
Flight::before(string $name, callable $callback) // Agrega un filtro antes de un método de marco de trabajo.
Flight::after(string $name, callable $callback) // Agrega un filtro después de un método de marco de trabajo.
Flight::path(string $path) // Agrega una ruta para la carga automática de clases.
Flight::get(string $key) // Obtiene una variable.
Flight::set(string $key, mixed $value) // Establece una variable.
Flight::has(string $key) // Verifica si una variable está establecida.
Flight::clear(array|string $key = []) // Borra una variable.
Flight::init() // Inicializa el marco de trabajo con su configuración predeterminada.
Flight::app() // Obtiene la instancia del objeto de aplicación
```

## Métodos Extensibles

```php
Flight::start() // Inicia el marco de trabajo.
Flight::stop() // Detiene el marco de trabajo y envía una respuesta.
Flight::halt(int $code = 200, string $message = '') // Detiene el marco de trabajo con un código de estado opcional y un mensaje.
Flight::route(string $pattern, callable $callback, bool $pass_route = false) // Asocia un patrón de URL a un callback.
Flight::group(string $pattern, callable $callback) // Crea agrupamientos para URLs, el patrón debe ser una cadena.
Flight::redirect(string $url, int $code) // Redirige a otra URL.
Flight::render(string $file, array $data, ?string $key = null) // Renderiza un archivo de plantilla.
Flight::error(Throwable $error) // Envía una respuesta de error HTTP 500.
Flight::notFound() // Envía una respuesta de error HTTP 404.
Flight::etag(string $id, string $type = 'string') // Realiza el almacenamiento en caché de ETag HTTP.
Flight::lastModified(int $time) // Realiza el almacenamiento en caché con la última modificación HTTP.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envía una respuesta JSON.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envía una respuesta JSONP.
```

Cualquier método personalizado agregado con `map` y `register` también puede ser filtrado.