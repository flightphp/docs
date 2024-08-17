# Wruczek/PHP-File-Cache

Clase PHP de almacenamiento en archivo ligera, simple y autónoma

**Ventajas**
- Ligera, autónoma y simple
- Todo el código en un archivo - no hay controladores innecesarios.
- Seguro - cada archivo de caché generado tiene un encabezado php con die, lo que hace que el acceso directo sea imposible incluso si alguien conoce la ruta y su servidor no está configurado correctamente
- Bien documentado y probado
- Maneja la concurrencia correctamente a través de flock
- Compatible con PHP 5.4.0 - 7.1+
- Gratis bajo una licencia MIT

Haga clic [aquí](https://github.com/Wruczek/PHP-File-Cache) para ver el código.

## Instalación

Instalar a través de composer:

```bash
composer require wruczek/php-file-cache
```

## Uso

El uso es bastante sencillo.

```php
use Wruczek\PhpFileCache\PhpFileCache;

$app = Flight::app();

// Pasa el directorio en el que se almacenará el caché al constructor
$app->register('cache', PhpFileCache::class, [ __DIR__ . '/../cache/' ], function(PhpFileCache $cache) {

	// Esto asegura que el caché solo se utilice en modo de producción
	// ENVIRONMENT es una constante que se establece en tu archivo de inicio (bootstrap) o en otro lugar de tu aplicación
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

Entonces puedes usarlo en tu código así:

```php

// Obtener instancia de caché
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // devuelve los datos a cachear
}, 10); // 10 segundos

// o
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10 segundos
}
```

## Documentación

Visita [https://github.com/Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache) para ver la documentación completa y asegúrate de ver la carpeta de [ejemplos](https://github.com/Wruczek/PHP-File-Cache/tree/master/examples).