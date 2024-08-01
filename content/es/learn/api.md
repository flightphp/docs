## Métodos de la API del Framework

Flight está diseñado para ser fácil de usar y entender. A continuación se muestra el conjunto completo de métodos para el framework. Consta de métodos principales, que son métodos estáticos regulares, y métodos extensibles, que son métodos mapeados que pueden ser filtrados o anulados.

## Métodos Principales

Estos métodos son fundamentales para el framework y no pueden ser anulados.

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Crea un método personalizado para el framework.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Registra una clase a un método del framework.
Flight::unregister(string $name) // Anula el registro de una clase de un método del framework.
Flight::before(string $name, callable $callback) // Añade un filtro antes de un método del framework.
Flight::after(string $name, callable $callback) // Añade un filtro después de un método del framework.
Flight::path(string $path) // Añade una ruta para cargar clases automáticamente.
Flight::get(string $key) // Obtiene una variable establecida por Flight::set().
Flight::set(string $key, mixed $value) // Establece una variable dentro del motor de Flight.
Flight::has(string $key) // Comprueba si una variable está establecida.
Flight::clear(array|string $key = []) // Borra una variable.
Flight::init() // Inicializa el framework con su configuración predeterminada.
Flight::app() // Obtiene una instancia del objeto de la aplicación
Flight::request() // Obtiene una instancia del objeto de solicitud
Flight::response() // Obtiene una instancia del objeto de respuesta
Flight::router() // Obtiene una instancia del objeto de enrutador
Flight::view() // Obtiene una instancia del objeto de vista
```

## Métodos Extensibles

```php
Flight::start() // Inicia el framework.
Flight::stop() // Detiene el framework y envía una respuesta.
Flight::halt(int $code = 200, string $mensaje = '') // Detiene el framework con un código de estado y mensaje opcional.
Flight::route(string $patrón, callable $callback, bool $pass_route = false, string $alias = '') // Mapea un patrón de URL a un callback.
Flight::post(string $patrón, callable $callback, bool $pass_route = false, string $alias = '') // Mapea un patrón de URL de solicitud POST a un callback.
Flight::put(string $patrón, callable $callback, bool $pass_route = false, string $alias = '') // Mapea un patrón de URL de solicitud PUT a un callback.
Flight::patch(string $patrón, callable $callback, bool $pass_route = false, string $alias = '') // Mapea un patrón de URL de solicitud PATCH a un callback.
Flight::delete(string $patrón, callable $callback, bool $pass_route = false, string $alias = '') // Mapea un patrón de URL de solicitud DELETE a un callback.
Flight::group(string $patrón, callable $callback) // Crea agrupaciones para URLs, el patrón debe ser una cadena.
Flight::getUrl(string $name, array $params = []) // Genera una URL basada en un alias de ruta.
Flight::redirect(string $url, int $code) // Redirige a otra URL.
Flight::download(string $filePath) // Descarga un archivo.
Flight::render(string $archivo, array $datos, ?string $key = null) // Renderiza un archivo de plantilla.
Flight::error(Throwable $error) // Envía una respuesta HTTP 500.
Flight::notFound() // Envía una respuesta HTTP 404.
Flight::etag(string $id, string $type = 'string') // Realiza el almacenamiento en caché HTTP ETag.
Flight::lastModified(int $time) // Realiza el almacenamiento en caché HTTP de última modificación.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envía una respuesta JSON.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envía una respuesta JSONP.
Flight::jsonHalt(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Envía una respuesta JSON y detiene el framework.
```

Cualquier método personalizado añadido con `map` y `register` también se puede filtrar. Para ejemplos sobre cómo mapear estos métodos, consulta la guía [Extender Flight](/learn/extending).