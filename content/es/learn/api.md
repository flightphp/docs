# Métodos de la API del Framework

Flight está diseñado para ser fácil de usar y entender. A continuación se muestra el conjunto completo de métodos para el framework. Consta de métodos principales, que son métodos estáticos regulares, y métodos extensibles, que son métodos asignados que pueden ser filtrados o anulados.

## Métodos Principales

Estos métodos son fundamentales para el framework y no pueden ser anulados.

```php
Flight::map(string $nombre, callable $callback, bool $pasar_ruta = false) // Crea un método personalizado para el framework.
Flight::register(string $nombre, string $clase, array $params = [], ?callable $callback = null) // Registra una clase a un método del framework.
Flight::unregister(string $nombre) // Desregistra una clase de un método del framework.
Flight::before(string $nombre, callable $callback) // Agrega un filtro antes de un método del framework.
Flight::after(string $nombre, callable $callback) // Agrega un filtro después de un método del framework.
Flight::path(string $ruta) // Agrega una ruta para cargar clases automáticamente.
Flight::get(string $llave) // Obtiene una variable.
Flight::set(string $llave, mixed $valor) // Establece una variable.
Flight::has(string $llave) // Verifica si una variable está establecida.
Flight::clear(array|string $llave = []) // Borra una variable.
Flight::init() // Inicializa el framework a sus configuraciones predeterminadas.
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
Flight::halt(int $código = 200, string $mensaje = '') // Detiene el framework con un código de estado y mensaje opcionales.
Flight::route(string $patrón, callable $callback, bool $pasar_ruta = false, string $alias = '') // Asigna un patrón de URL a un callback.
Flight::post(string $patrón, callable $callback, bool $pasar_ruta = false, string $alias = '') // Asigna un patrón de URL de solicitud POST a un callback.
Flight::put(string $patrón, callable $callback, bool $pasar_ruta = false, string $alias = '') // Asigna un patrón de URL de solicitud PUT a un callback.
Flight::patch(string $patrón, callable $callback, bool $pasar_ruta = false, string $alias = '') // Asigna un patrón de URL de solicitud PATCH a un callback.
Flight::delete(string $patrón, callable $callback, bool $pasar_ruta = false, string $alias = '') // Asigna un patrón de URL de solicitud DELETE a un callback.
Flight::group(string $patrón, callable $callback) // Crea agrupaciones para URL, el patrón debe ser una cadena.
Flight::getUrl(string $nombre, array $params = []) // Genera una URL basada en un alias de ruta.
Flight::redirect(string $url, int $código) // Redirige a otra URL.
Flight::render(string $archivo, array $datos, ?string $clave = null) // Renderiza un archivo de plantilla.
Flight::error(Throwable $error) // Envía una respuesta HTTP 500.
Flight::notFound() // Envía una respuesta HTTP 404.
Flight::etag(string $id, string $tipo = 'string') // Realiza el almacenamiento en caché HTTP ETag.
Flight::lastModified(int $tiempo) // Realiza el almacenamiento en caché HTTP de última modificación.
Flight::json(mixed $datos, int $código = 200, bool $codificar = true, string $charset = 'utf8', int $opción) // Envía una respuesta JSON.
Flight::jsonp(mixed $datos, string $param = 'jsonp', int $código = 200, bool $codificar = true, string $charset = 'utf8', int $opción) // Envía una respuesta JSONP.
```

Cualquier método personalizado agregado con `map` y `register` también puede ser filtrado.