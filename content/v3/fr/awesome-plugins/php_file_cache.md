# flightphp/cache

Classe de mise en cache PHP légère, simple et autonome en fichier, dérivée de [Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache)

**Avantages** 
- Légère, autonome et simple
- Tout le code dans un seul fichier - pas de pilotes inutiles.
- Sécurisée - chaque fichier de cache généré a un en-tête PHP avec die, rendant l'accès direct impossible même si quelqu'un connaît le chemin et que votre serveur n'est pas configuré correctement
- Bien documentée et testée
- Gère la concurrence correctement via flock
- Supporte PHP 7.4+
- Gratuite sous licence MIT

Ce site de documentation utilise cette bibliothèque pour mettre en cache chaque page !

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

// Vous passez le répertoire où le cache sera stocké dans le constructeur
$app->register('cache', Cache::class, [ __DIR__ . '/../cache/' ], function(Cache $cache) {

	// Cela garantit que le cache n'est utilisé que en mode production
	// ENVIRONMENT est une constante définie dans votre fichier bootstrap ou ailleurs dans votre application
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

### Obtenir une valeur de cache

Vous utilisez la méthode `get()` pour obtenir une valeur mise en cache. Si vous voulez une méthode pratique qui rafraîchira le cache s'il est expiré, vous pouvez utiliser `refreshIfExpired()`.

```php

// Obtenir l'instance de cache
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // retourner les données à mettre en cache
}, 10); // 10 secondes

// ou
$data = $cache->get('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->set('simple-cache-test', $data, 10); // 10 secondes
}
```

### Stocker une valeur de cache

Vous utilisez la méthode `set()` pour stocker une valeur dans le cache.

```php
Flight::cache()->set('simple-cache-test', 'my cached data', 10); // 10 secondes
```

### Effacer une valeur de cache

Vous utilisez la méthode `delete()` pour effacer une valeur dans le cache.

```php
Flight::cache()->delete('simple-cache-test');
```

### Vérifier si une valeur de cache existe

Vous utilisez la méthode `exists()` pour vérifier si une valeur existe dans le cache.

```php
if(Flight::cache()->exists('simple-cache-test')) {
	// faire quelque chose
}
```

### Vider le cache
Vous utilisez la méthode `flush()` pour vider l'ensemble du cache.

```php
Flight::cache()->flush();
```

### Extraire les métadonnées avec le cache

Si vous voulez extraire les horodatages et autres métadonnées sur une entrée de cache, assurez-vous de passer `true` comme paramètre correct.

```php
$data = $cache->refreshIfExpired("simple-cache-meta-test", function () {
    echo "Refreshing data!" . PHP_EOL;
    return date("H:i:s"); // retourner les données à mettre en cache
}, 10, true); // true = retourner avec métadonnées
// ou
$data = $cache->get("simple-cache-meta-test", true); // true = retourner avec métadonnées

/*
Exemple d'élément mis en cache récupéré avec métadonnées :
{
    "time":1511667506, <-- horodatage unix de sauvegarde
    "expire":10,       <-- temps d'expiration en secondes
    "data":"04:38:26", <-- données désérialisées
    "permanent":false
}

En utilisant les métadonnées, nous pouvons, par exemple, calculer quand l'élément a été sauvegardé ou quand il expire
Nous pouvons aussi accéder aux données elles-mêmes avec la clé "data"
*/

$expiresin = ($data["time"] + $data["expire"]) - time(); // obtenir l'horodatage unix quand les données expirent et soustraire l'horodatage actuel
$cacheddate = $data["data"]; // nous accédons aux données elles-mêmes avec la clé "data"

echo "Dernière sauvegarde de cache : $cacheddate, expire dans $expiresin secondes";
```

## Documentation

Visitez [https://github.com/flightphp/cache](https://github.com/flightphp/cache) pour voir le code. Assurez-vous de consulter le dossier [examples](https://github.com/flightphp/cache/tree/master/examples) pour des façons supplémentaires d'utiliser le cache.