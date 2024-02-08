# Wruczek/PHP-File-Cache

Viegla, vienkārša un autonomas PHP faila kešatmiņas klase

**Izdevības**
- Viegla, autonomiska un vienkārša
- Visas koda daļas vienā failā - bezjēdzīgu vadītāju nav.
- Drošs - katram ģenerētajam kešatmiņas failam ir php galvenes ar die, padarot tiešo piekļuvi neiespējamu pat tad, ja kāds zina ceļu un jūsu serveris nav konfigurēts pareizi
- Labi dokumentēts un pārbaudīts
- Pareizi apstrādā vienlaicīgumu, izmantojot flock
- Atbalsta PHP 5.4.0 - 7.1+
- Bez maksas, pamatojoties uz MIT licenci

## Uzstādīšana

Uzstādīt, izmantojot komponistu:

```bash
composer require wruczek/php-file-cache
```

## Lietošana

Lietošana ir diezgan vienkārša.

```php
use Wruczek\PhpFileCache\PhpFileCache;

$app = Flight::app();

// Jūs padodat katalogu, kurā kešatmiņa tiks saglabāta, konstruktorā
$app->register('cache', PhpFileCache::class, [ __DIR__ . '/../cache/' ], function(PhpFileCache $cache) {

	// Tas nodrošina, ka kešatmiņa tiek izmantota tikai tad, ja tā ir produktīvā režīmā
	// ENVIRONMENT ir konstante, kas iestatīta jūsu ielādes failā vai citur jūsu lietotnē
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

Tad to varat izmantot savā kodā šādi:

```php

// Iegūt kešatmiņas instanci
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // atgriezt dati, kas jākešo
}, 10); // 10 sekundes

// vai
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10 sekundes
}
```

## Dokumentācija

Apmeklējiet [https://github.com/Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache) pilnai dokumentācijai un pārliecinieties, ka redzat [examples](https://github.com/Wruczek/PHP-File-Cache/tree/master/examples) mapi.