# flightphp/cache

Clase de caché en archivo PHP ligera, simple y autónoma

**Ventajas** 
- Ligera, autónoma y simple
- Todo el código en un solo archivo - sin controladores innecesarios.
- Seguro - cada archivo de caché generado tiene un encabezado php con die, haciendo imposible el acceso directo incluso si alguien conoce la ruta y tu servidor no está configurado correctamente
- Bien documentada y probada
- Maneja la concurrencia correctamente a través de flock
- Soporta PHP 7.4+
- Gratis bajo una licencia MIT

¡Este sitio de documentación está utilizando esta biblioteca para almacenar en caché cada una de las páginas!

Haz clic [aquí](https://github.com/flightphp/cache) para ver el código.

## Instalación

Instala a través de composer:

```bash
composer require flightphp/cache
```

## Uso

El uso es bastante sencillo. Esto guarda un archivo de caché en el directorio de caché.

```php
use flight\Cache;

$app = Flight::app();

// Pasas el directorio donde se almacenará la caché al constructor
$app->register('cache', Cache::class, [ __DIR__ . '/../cache/' ], function(Cache $cache) {

	// Esto asegura que la caché solo se use cuando esté en modo de producción
	// ENVIRONMENT es una constante que se establece en tu archivo de arranque o en otro lugar de tu aplicación
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

Luego puedes usarlo en tu código así:

```php

// Obtener instancia de caché
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // devolver datos a ser almacenados en caché
}, 10); // 10 segundos

// o
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10 segundos
}
```

## Documentación

Visita [https://github.com/flightphp/cache](https://github.com/flightphp/cache) para documentación completa y asegúrate de ver la carpeta de [ejemplos](https://github.com/flightphp/cache/tree/master/examples).