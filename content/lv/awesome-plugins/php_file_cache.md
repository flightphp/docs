# flightphp/cache

Gaismas, vienkārša un patstāvīga PHP iekšējā kešatmiņas klase

**Priekšrocības** 
- Gaismas, patstāvīga un vienkārša
- Visi kodi vienā failā - bez liekiem draiveriem.
- Droša - katram ģenerētajam kešatmiņas failam ir php galvene ar die, padarot tiešu piekļuvi neiespējamu, pat ja kāds zina ceļu un jūsu serveris nav pareizi konfigurēts
- Labi dokumentēta un pārbaudīta
- Pareizi apstrādā konkurenci, izmantojot flock
- Atbalsta PHP 7.4+
- Bezmaksas saskaņā ar MIT licenci

Šī dokumentācijas vietne izmanto šo bibliotēku, lai kešotu katru no lapām!

Noklikšķiniet [šeit](https://github.com/flightphp/cache), lai skatītu kodu.

## Instalācija

Instalējiet, izmantojot composer:

```bash
composer require flightphp/cache
```

## Izmantošana

Izmantošana ir salīdzinoši vienkārša. Tas saglabā kešatmiņas failu kešatmiņas direktorijā.

```php
use flight\Cache;

$app = Flight::app();

// Jūs nododat direktoriju, kur kešatmiņa tiks glabāta, konstruktorā
$app->register('cache', Cache::class, [ __DIR__ . '/../cache/' ], function(Cache $cache) {

	// Tas nodrošina, ka kešatmiņa tiek izmantota tikai ražošanas režīmā
	// ENVIRONMENT ir konstante, kas tiek iestatīta jūsu sākuma failā vai citur jūsu lietotnē
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

Tad jūs to varat izmantot savā kodā šādi:

```php

// Iegūstiet kešatmiņas instanci
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // atgrieziet datus, kas tiks kešoti
}, 10); // 10 sekundes

// vai
$data = $cache->retrieve('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->store('simple-cache-test', $data, 10); // 10 sekundes
}
```

## Dokumentācija

Apmeklējiet [https://github.com/flightphp/cache](https://github.com/flightphp/cache) pilnīgai dokumentācijai un pārliecinieties, ka apskatāt [piemērus](https://github.com/flightphp/cache/tree/master/examples) mapi.