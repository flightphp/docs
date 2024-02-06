# Wruczek/PHP-File-Cache

Viegla, vienkārša un neatkarīga PHP failu kešošanas klase

**Pamatpriekšrocības**
- Viegla, neatkarīga un vienkārša
- Visa kods vienā failā - bezjēdzīgi draiveri.
- Drosīga - katram ģenerētajam keša failam ir php galvene ar die, padarot tiešu piekļuvi neiespējamu pat tad, ja kāds zina ceļu un jūsu serveris nav pareizi konfigurēts
- Labi dokumentēta un pārbaudīta
- Pareizi apstrādā vienlaicību, izmantojot flock
- Atbalsta PHP 5.4.0 - 7.1+
- Bez maksas saskaņā ar MIT licenci

## Instalācija

Instalēt, izmantojot komponistu:

```bash
composer require wruczek/php-file-cache
```

## Lietošana

Lietošana ir diezgan vienkārša.

```php
use Wruczek\PhpFileCache\PhpFileCache;

$app = Flight::app();

// Jūs padodat direktoriju, kurā tiks saglabāts kešs, konstruktorā
$app->register('cache', PhpFileCache::class, [ __DIR__ . '/../cache/' ], function(PhpFileCache $cache) {

	// Tas nodrošina, ka kešs tiek izmantots tikai tad, ja ir produkciona režīms
	// ENVIRONMENT ir konstante, kas tiek iestatīta jūsu bootstarp failā vai citur jūsu lietotnē
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

Pēc tam jūs to varat izmantot savā kodā šādi:

```php

// Saņemt keša instanci
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // atgriezt dati, kas jākešo
}, 10); // 10 sekundes

// vai arī
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10 sekundes
}
```

## Dokumentācija

Apmeklējiet [https://github.com/Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache), lai iegūtu pilnu dokumentāciju un pārliecinieties, ka skatāties [piemērus](https://github.com/Wruczek/PHP-File-Cache/tree/master/examples) mapē.