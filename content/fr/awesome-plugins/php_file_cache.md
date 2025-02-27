# flightphp/cache

Classe de mise en cache en fichier PHP légère, simple et autonome

**Avantages** 
- Légère, autonome et simple
- Tout le code dans un seul fichier - pas de pilotes inutiles.
- Sécurisée - chaque fichier de cache généré a un en-tête php avec die, rendant l'accès direct impossible même si quelqu'un connaît le chemin et que votre serveur n'est pas configuré correctement
- Bien documenté et testé
- Gère correctement la concurrence via flock
- Prend en charge PHP 7.4+
- Gratuit sous une licence MIT

Ce site de documentation utilise cette bibliothèque pour mettre en cache chacune des pages !

Cliquez [ici](https://github.com/flightphp/cache) pour voir le code.

## Installation

Installez via composer :

```bash
composer require flightphp/cache
```

## Utilisation

L'utilisation est assez simple. Cela enregistre un fichier de cache dans le répertoire de cache.

```php
use flight\Cache;

$app = Flight::app();

// Vous passez le répertoire dans lequel le cache sera stocké dans le constructeur
$app->register('cache', Cache::class, [ __DIR__ . '/../cache/' ], function(Cache $cache) {

	// Cela garantit que le cache n'est utilisé que lorsque vous êtes en mode production
	// ENVIRONMENT est une constante qui est définie dans votre fichier de démarrage ou ailleurs dans votre application
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

Ensuite, vous pouvez l'utiliser dans votre code comme ceci :

```php

// Obtenir l'instance de cache
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // retourner les données à mettre en cache
}, 10); // 10 secondes

// ou
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10 secondes
}
```

## Documentation

Visitez [https://github.com/flightphp/cache](https://github.com/flightphp/cache) pour une documentation complète et assurez-vous de voir le dossier [exemples](https://github.com/flightphp/cache/tree/master/examples).