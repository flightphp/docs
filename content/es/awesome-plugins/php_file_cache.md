# Wruczek/PHP-File-Cache

Clase de almacenamiento en caché de PHP en archivo ligera, simple y independiente

**Ventajas**
- Ligero, independiente y simple
- Todo el código en un solo archivo, sin controladores innecesarios.
- Seguro, cada archivo de caché generado tiene una cabecera de php con die, lo que hace que el acceso directo sea imposible incluso si alguien conoce la ruta y su servidor no está configurado correctamente
- Bien documentado y probado
- Maneja la concurrencia correctamente a través de flock
- Compatible con PHP 5.4.0 - 7.1+
- Gratuito bajo una licencia MIT

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

// Pasa el directorio en el que se almacenará la caché al constructor
$app->register('cache', PhpFileCache::class, [ __DIR__ . '/../cache/' ], function(PhpFileCache $cache) {

	// Esto asegura que la caché solo se use en modo de producción
	// ENVIRONMENT es una constante que se establece en tu archivo de inicio o en otro lugar de tu aplicación
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

Luego puedes usarlo en tu código de la siguiente manera:

```php

// Obtener instancia de caché
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // devolver datos para ser almacenados en caché
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