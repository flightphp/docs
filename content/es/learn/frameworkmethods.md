# Métodos del Framework

Flight está diseñado para ser fácil de usar y entender. Lo siguiente es el conjunto completo
de métodos para el framework. Consiste en métodos centrales, que son métodos estáticos regulares,
y métodos extensibles, que son métodos asignados que pueden ser filtrados
o anulados.

## Métodos Centrales

```php
Flight::map(string $nombre, callable $retorno, bool $pasar_ruta = false) // Crea un método personalizado para el framework.
Flight::register(string $nombre, string $clase, array $params = [], ?callable $retorno = null) // Registra una clase a un método del framework.
Flight::before(string $nombre, callable $retorno) // Agrega un filtro antes de un método del framework.
Flight::after(string $nombre, callable $retorno) // Agrega un filtro después de un método del framework.
Flight::path(string $ruta) // Agrega una ruta para la carga automática de clases.
Flight::get(string $clave) // Obtiene una variable.
Flight::set(string $clave, mixed $valor) // Establece una variable.
Flight::has(string $clave) // Verifica si una variable está establecida.
Flight::clear(array|string $clave = []) // Borra una variable.
Flight::init() // Inicializa el framework a sus ajustes predeterminados.
Flight::app() // Obtiene la instancia del objeto de la aplicación
```

## Métodos Extensibles

```php
Flight::start() // Inicia el framework.
Flight::stop() // Detiene el framework y envía una respuesta.
Flight::halt(int $codigo = 200, string $mensaje = '') // Detiene el framework con un código de estado opcional y mensaje.
Flight::route(string $patrón, callable $retorno, bool $pasar_ruta = false) // Asigna un patrón de URL a un retorno.
Flight::group(string $patrón, callable $retorno) // Crea agrupaciones para URLs, el patrón debe ser una cadena.
Flight::redirect(string $url, int $codigo) // Redirige a otra URL.
Flight::render(string $archivo, array $datos, ?string $clave = null) // Renderiza un archivo de plantilla.
Flight::error(Throwable $error) // Envía una respuesta HTTP 500.
Flight::notFound() // Envía una respuesta HTTP 404.
Flight::etag(string $id, string $tipo = 'string') // Realiza almacenamiento en caché HTTP ETag.
Flight::lastModified(int $tiempo) // Realiza almacenamiento en caché HTTP de Última Modificación.
Flight::json(mixed $datos, int $codigo = 200, bool $codificar = true, string $charset = 'utf8', int $opción) // Envía una respuesta JSON.
Flight::jsonp(mixed $datos, string $parametro = 'jsonp', int $codigo = 200, bool $codificar = true, string $charset = 'utf8', int $opción) // Envía una respuesta JSONP.
```

Cualquier método personalizado añadido con `map` y `register` también puede ser filtrado.