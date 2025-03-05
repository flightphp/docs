# Métodos de la API del Framework

Flight está diseñado para ser fácil de usar y entender. Lo siguiente es el conjunto completo de métodos para el framework. Consiste en métodos principales, que son métodos estáticos regulares, y métodos extensibles, que son métodos mapeados que pueden ser filtrados o sobrescritos.

## Métodos Principales

Estos métodos son fundamentales para el framework y no pueden ser sobrescritos.

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Crea un método personalizado del framework.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Registra una clase a un método del framework.
Flight::unregister(string $name) // Anula el registro de una clase a un método del framework.
Flight::before(string $name, callable $callback) // Agrega un filtro antes de un método del framework.
Flight::after(string $name, callable $callback) // Agrega un filtro después de un método del framework.
Flight::path(string $path) // Agrega una ruta para la carga automática de clases.
Flight::get(string $key) // Obtiene una variable establecida por Flight::set().
Flight::set(string $key, mixed $value) // Establece una variable dentro del motor de Flight.
Flight::has(string $key) // Verifica si una variable está establecida.
Flight::clear(array|string $key = []) // Limpia una variable.
Flight::init() // Inicializa el framework con su configuración predeterminada.
Flight::app() // Obtiene la instancia del objeto de la aplicación.
Flight::request() // Obtiene la instancia del objeto de solicitud.
Flight::response() // Obtiene la instancia del objeto de respuesta.
Flight::router() // Obtiene la instancia del objeto de enrutador.
Flight::view() // Obtiene la instancia del objeto de vista.
```

## Métodos Extensibles

```php
Flight::start() // Inicia el framework.
Flight::stop() // Detiene el framework y envía una respuesta.
Flight::halt(int $code = 200, string $message = '') // Detiene el framework con un código de estado y mensaje opcionales.
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapea un patrón de URL a un callback.
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapea un patrón de URL de solicitud POST a un callback.
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapea un patrón de URL de solicitud PUT a un callback.
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapea un patrón de URL de solicitud PATCH a un callback.
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Mapea un patrón de URL de solicitud DELETE a un callback.
Flight::group(string $pattern, callable $callback) // Crea agrupación para urls, el patrón debe ser una cadena.
Flight::getUrl(string $name, array $params = []) // Genera una URL basada en un alias de ruta.
Flight::redirect(string $url, int $code) // Redirige a otra URL.
Flight::download(string $filePath) // Descarga un archivo.
Flight::render(string $file, array $data, ?string $key = null) // Renderiza un archivo de plantilla.
Flight::error(Throwable $error) // Envía una respuesta HTTP 500.
Flight::notFound() // Envía una respuesta HTTP 404.
Flight::etag(string $id, string $type = 'string') // Realiza caché HTTP ETag.
Flight::lastModified(int $time) // Realiza caché HTTP de última modificación.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envía una respuesta JSON.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envía una respuesta JSONP.
Flight::jsonHalt(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envía una respuesta JSON y detiene el framework.
Flight::onEvent(string $event, callable $callback) // Registra un oyente de eventos.
Flight::triggerEvent(string $event, ...$args) // Dispara un evento.
```

Cualquier método personalizado agregado con `map` y `register` también puede ser filtrado. Para ejemplos sobre cómo mapear estos métodos, consulte la guía [Extending Flight](/learn/extending).