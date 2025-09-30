# flightphp/cache

Clase ligera, simple y autónoma de caché en archivo PHP bifurcada de [Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache)

**Ventajas** 
- Ligera, autónoma y simple
- Todo el código en un solo archivo - sin controladores innecesarios.
- Segura - cada archivo de caché generado tiene un encabezado PHP con die, haciendo imposible el acceso directo incluso si alguien conoce la ruta y tu servidor no está configurado correctamente
- Bien documentada y probada
- Maneja la concurrencia correctamente mediante flock
- Soporta PHP 7.4+
- Gratuita bajo una licencia MIT

¡Este sitio de documentación está utilizando esta biblioteca para almacenar en caché cada una de las páginas!

Haz clic [aquí](https://github.com/flightphp/cache) para ver el código.

## Instalación

Instala mediante composer:

```bash
composer require flightphp/cache
```

## Uso

El uso es bastante directo. Esto guarda un archivo de caché en el directorio de caché.

```php
use flight\Cache;

$app = Flight::app();

// Pasas el directorio donde se almacenará el caché al constructor
$app->register('cache', Cache::class, [ __DIR__ . '/../cache/' ], function(Cache $cache) {

	// Esto asegura que el caché solo se use en modo producción
	// ENVIRONMENT es una constante que se establece en tu archivo de bootstrap o en otra parte de tu app
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

### Obtener un Valor de Caché

Usas el método `get()` para obtener un valor en caché. Si quieres un método conveniente que actualice el caché si ha expirado, puedes usar `refreshIfExpired()`.

```php

// Obtener instancia de caché
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // devolver datos para ser almacenados en caché
}, 10); // 10 segundos

// o
$data = $cache->get('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->set('simple-cache-test', $data, 10); // 10 segundos
}
```

### Almacenar un Valor de Caché

Usas el método `set()` para almacenar un valor en el caché.

```php
Flight::cache()->set('simple-cache-test', 'my cached data', 10); // 10 segundos
```

### Borrar un Valor de Caché

Usas el método `delete()` para borrar un valor en el caché.

```php
Flight::cache()->delete('simple-cache-test');
```

### Verificar si Existe un Valor de Caché

Usas el método `exists()` para verificar si un valor existe en el caché.

```php
if(Flight::cache()->exists('simple-cache-test')) {
	// hacer algo
}
```

### Limpiar el Caché
Usas el método `flush()` para limpiar todo el caché.

```php
Flight::cache()->flush();
```

### Extraer metadatos con caché

Si quieres extraer marcas de tiempo y otros metadatos sobre una entrada de caché, asegúrate de pasar `true` como el parámetro correcto.

```php
$data = $cache->refreshIfExpired("simple-cache-meta-test", function () {
    echo "Refreshing data!" . PHP_EOL;
    return date("H:i:s"); // devolver datos para ser almacenados en caché
}, 10, true); // true = devolver con metadatos
// o
$data = $cache->get("simple-cache-meta-test", true); // true = devolver con metadatos

/*
Ejemplo de elemento en caché recuperado con metadatos:
{
    "time":1511667506, <-- marca de tiempo unix de guardado
    "expire":10,       <-- tiempo de expiración en segundos
    "data":"04:38:26", <-- datos deserializados
    "permanent":false
}

Usando metadatos, podemos, por ejemplo, calcular cuándo se guardó el elemento o cuándo expira
También podemos acceder a los datos en sí con la clave "data"
*/

$expiresin = ($data["time"] + $data["expire"]) - time(); // obtener marca de tiempo unix cuando expiran los datos y restar la marca de tiempo actual
$cacheddate = $data["data"]; // accedemos a los datos en sí con la clave "data"

echo "Último guardado de caché: $cacheddate, expira en $expiresin segundos";
```

## Documentación

Visita [https://github.com/flightphp/cache](https://github.com/flightphp/cache) para ver el código. Asegúrate de ver la carpeta [examples](https://github.com/flightphp/cache/tree/master/examples) para formas adicionales de usar el caché.