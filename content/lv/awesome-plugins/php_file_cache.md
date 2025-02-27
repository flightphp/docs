# flightphp/cache

Gaisma, vienkārša un patstāvīga PHP iekšējā kešatmiņas klase

**Priekšrocības** 
- Gaisma, patstāvīga un vienkārša
- Visi kods vienā failā - nav nevajadzīgu vadītāju.
- Droša - katram ģenerētajam kešatmiņas failam ir php galvene ar die, padarot tiešu piekļuvi neiespējamu, pat ja kāds zina ceļu un jūsu serveris nav pareizi konfigurēts
- Labi dokumentēta un testēta
- Pareizi apstrādā konkurenci, izmantojot flock
- Atbalsta PHP 7.4+
- Bezmaksas, izmantojot MIT licenci

Šī dokumentācija izmanto šo bibliotēku, lai kešotu katru no lappusēm!

Noklikšķiniet [šeit](https://github.com/flightphp/cache), lai skatītu kodu.

## Uzstādīšana

Uzstādīšana, izmantojot composer:

```bash
composer require flightphp/cache
```

## Izmantošana

Izmantošana ir samērā vienkārša. Tas saglabā kešatmiņas failu kešatmiņas direktorijā.

```php
use flight\Cache;

$app = Flight::app();

// Jūs nododat direktoriju, kur kešatmiņa tiks saglabāta, konstruktora iekšā
$app->register('cache', Cache::class, [ __DIR__ . '/../cache/' ], function(Cache $cache) {

	// Tas nodrošina, ka kešatmiņa tiek izmantota tikai ražošanas režīmā
	// ENVIRONMENT ir konstante, kas tiek iestatīta jūsu bootstrapa failā vai citur jūsu lietojumprogrammā
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

Tad jūs varat to izmantot savā kodā šādi:

```php

// Iegūt kešatmiņas instanci
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // atgriezt datus, kas jākodē
}, 10); // 10 sekundes

// vai
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10 sekundes
}
```

## Dokumentācija

Apmeklējiet [https://github.com/flightphp/cache](https://github.com/flightphp/cache) pilnai dokumentācijai un noteikti apskatiet [piemērus](https://github.com/flightphp/cache/tree/master/examples) mapē.