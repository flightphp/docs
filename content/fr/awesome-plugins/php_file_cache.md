# Wruczek/PHP-File-Cache

Classe de mise en cache de fichiers PHP légère, simple et autonome

**Avantages**
- Légère, autonome et simple
- Tout le code dans un seul fichier - pas de pilotes inutiles.
- Sécurisé - chaque fichier de cache généré a un en-tête PHP avec die, rendant l'accès direct impossible même si quelqu'un connaît le chemin et que votre serveur n'est pas configuré correctement
- Bien documenté et testé
- Gère correctement la concurrence via flock
- Prend en charge PHP 5.4.0 - 7.1+
- Gratuit sous une licence MIT

Cliquez [ici](https://github.com/Wruczek/PHP-File-Cache) pour voir le code.

## Installation

Installez via composer:

```bash
composer require wruczek/php-file-cache
```

## Utilisation

L'utilisation est assez simple.

```php
use Wruczek\PhpFileCache\PhpFileCache;

$app = Flight::app();

// Vous passez le répertoire dans lequel le cache sera stocké dans le constructeur
$app->register('cache', PhpFileCache::class, [ __DIR__ . '/../cache/' ], function(PhpFileCache $cache) {

	// Cela garantit que le cache n'est utilisé que en mode production
	// ENVIRONMENT est une constante définie dans votre fichier d'amorçage ou ailleurs dans votre application
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

Ensuite, vous pouvez l'utiliser dans votre code comme ceci :

```php

// Obtenir l'instance du cache
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // retourne les données à mettre en cache
}, 10); // 10 secondes

// ou
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10 secondes
}
```

## Documentation

Visitez [https://github.com/Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache) pour la documentation complète et assurez-vous de consulter le dossier [examples](https://github.com/Wruczek/PHP-File-Cache/tree/master/examples).