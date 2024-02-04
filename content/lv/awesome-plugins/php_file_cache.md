# Wruczek/PHP-File-Cache

Viegla, vienkārša un neatkarīga PHP failu kešošanas klase

**Priekšrocības** 
- Viegla, neatkarīga un vienkārša
- Visas koda daļas vienā failā - nav lieku draiveru.
- Drosīga - katram veidotam kešo failam ir php galvene ar die, padarot tiešu piekļuvi neiespējamu pat ja kādam ir zināms ceļš un jūsu serveris nav konfigurēts pareizi
- Labi dokumentēta un pārbaudīta
- Pareizi apstrādā konkurenci, izmantojot flock
- Atbalsta PHP 5.4.0 - 7.1+
- Bezmaksas saskaņā ar MIT licenci

## Instalēšana

Instalēt izmantojot composer:

```bash
composer require wruczek/php-file-cache
```

## Lietošana

Lietošana ir diezgan vienkārša.

```php
use Wruczek\PhpFileCache\PhpFileCache;

$app = Flight::app();

// Jūs nododat katalogu, kurā kešatmiņa tiks saglabāta, konstruktora funkcijai
$app->register('cache', PhpFileCache::class, [ __DIR__ . '/../cache/' ], function(PhpFileCache $cache) {

	// Tas pārliecinās, ka kešatmiņa tiek izmantota tikai tad, ja ir produkcionālā režīmā
	// ENVIRONMENT ir konstante, kas ir iestatīta jūsu ielādes failā vai citur jūsu lietotnē
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

Tad jūs varat to izmantot savā kodā šādi:

```php

// Saņemt kešatmiņas instanci
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // atgriezt datus, kas tiks kešoti
}, 10); // 10 sekundes

// vai
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10 sekundes
}
```

## Dokumentācija

Apmeklējiet [https://github.com/Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache) pilnai dokumentācijai un pārliecinieties, ka apskatāt [piemērus](https://github.com/Wruczek/PHP-File-Cache/tree/master/examples) mapē.