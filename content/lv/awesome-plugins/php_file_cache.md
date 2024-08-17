# Wruczek/PHP-File-Cache

Viegla, vienkārša un neatkarīga PHP iekšējās kešatmiņas klase

**Priekšrocības** 
- Viegla, neatkarīga un vienkārša
- Viss kods vienā failā - bezjēdzīgu draiveru nav.
- Drošs - katram ģenerētajam kešatmiņas failam ir php galvenes fails ar die, padarot tiešu piekļuvi neiespējamu pat tad, ja kādam ir zināms ceļš un jūsu serveris nav konfigurēts pareizi
- Labi dokumentēts un pārbaudīts
- Pareizi apstrādā vienlaicību ,izmantojot flock
- Atbalsta PHP 5.4.0 - 7.1+
- Bezmaksas saskaņā ar MIT licences noteikumiem

Noklikšķiniet [šeit](https://github.com/Wruczek/PHP-File-Cache), lai aplūkotu kodu.

## Instalācija

Uzstādiet, izmantojot komponistu:

```bash
composer require wruczek/php-file-cache
```

## Lietošana

Lietošana ir diezgan vienkārša.

```php
use Wruczek\PhpFileCache\PhpFileCache;

$app = Flight::app();

// Jūs nododat direktoriju, kurā kešatmiņa tiks saglabāta, konstruktorā
$app->register('cache', PhpFileCache::class, [ __DIR__ . '/../cache/' ], function(PhpFileCache $cache) {

	// Tas nodrošina, ka kešatmiņa tiek izmantota tikai tad, ja esat produktīvā režīmā
	// ENVIRONMENT ir konstante, kas ir iestatīta jūsu sākotnējā failā vai citur jūsu lietotnē
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

Tad jūs varat to izmantot savā kodā šādi:

```php

// Saņemt kešatmiņas instanci
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // atgriezt dati, kas tiks saglabāti
}, 10); // 10 sekundes

// vai
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10 sekundes
}
```

## Dokumentācija

Apmeklējiet [https://github.com/Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache) pilnai dokumentācijai un pārliecinieties, ka apskatāt [piemērus](https://github.com/Wruczek/PHP-File-Cache/tree/master/examples) mapes.