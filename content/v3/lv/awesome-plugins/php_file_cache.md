# flightphp/cache

Gaismas, vienkārša un neatkarīga PHP failā kešošanas klase, kas izveidota no [Wruczek/PHP-File-Cache](https://github.com/Wruczek/PHP-File-Cache) forka

**Priekšrocības** 
- Gaisma, neatkarīga un vienkārša
- Viss kods vienā failā - bez bezjēdzīgiem draiveriem.
- Droša - katrs ģenerētais kešošanas fails satur PHP galvenes ar die, padarot tiešu piekļuvi neiespējamu pat tad, ja kāds zina ceļu un jūsu serveris nav pareizi konfigurēts
- Labi dokumentēta un testēta
- Pareizi apstrādā vienlaicību, izmantojot flock
- Atbalsta PHP 7.4+
- Bezmaksas saskaņā ar MIT licenci

Šī dokumentācijas vietne izmanto šo bibliotēku, lai kešotu katru no lapām!

Noklikšķiniet [šeit](https://github.com/flightphp/cache), lai skatītu kodu.

## Instalācija

Instalējiet, izmantojot composer:

```bash
composer require flightphp/cache
```

## Lietošana

Lietošana ir diezgan vienkārša. Tas saglabā kešošanas failu kešošanas direktorijā.

```php
use flight\Cache;

$app = Flight::app();

// Jūs nododiet direktoriju, kurā kešs tiks saglabāts, konstruktorā
$app->register('cache', Cache::class, [ __DIR__ . '/../cache/' ], function(Cache $cache) {

	// Tas nodrošina, ka kešs tiek izmantots tikai ražošanas režīmā
	// ENVIRONMENT ir konstante, kas ir iestatīta jūsu bootstrap failā vai citur jūsu lietotnē
	$cache->setDevMode(ENVIRONMENT === 'development');
});
```

### Iegūt kešošanas vērtību

Jūs izmantojat `get()` metodi, lai iegūtu kešotu vērtību. Ja vēlaties ērtu metodi, kas atsvaidzinās kešu, ja tas ir beidzies, varat izmantot `refreshIfExpired()`.

```php

// Iegūt kešošanas instanci
$cache = Flight::cache();
$data = $cache->refreshIfExpired('simple-cache-test', function () {
    return date("H:i:s"); // return data to be cached
}, 10); // 10 sekundes

// or
$data = $cache->get('simple-cache-test');
if(empty($data)) {
	$data = date("H:i:s");
	$cache->set('simple-cache-test', $data, 10); // 10 sekundes
}
```

### Saglabāt kešošanas vērtību

Jūs izmantojat `set()` metodi, lai saglabātu vērtību kešā.

```php
Flight::cache()->set('simple-cache-test', 'my cached data', 10); // 10 sekundes
```

### Dzēst kešošanas vērtību

Jūs izmantojat `delete()` metodi, lai dzēstu vērtību kešā.

```php
Flight::cache()->delete('simple-cache-test');
```

### Pārbaudīt, vai kešošanas vērtība pastāv

Jūs izmantojat `exists()` metodi, lai pārbaudītu, vai vērtība pastāv kešā.

```php
if(Flight::cache()->exists('simple-cache-test')) {
	// do something
}
```

### Notīrīt kešu
Jūs izmantojat `flush()` metodi, lai notīrītu visu kešu.

```php
Flight::cache()->flush();
```

### Izvilkt meta datus ar kešu

Ja vēlaties izvilkt laika zīmes un citus meta datus par kešošanas ierakstu, pārliecinieties, ka nododiet `true` kā pareizo parametru.

```php
$data = $cache->refreshIfExpired("simple-cache-meta-test", function () {
    echo "Refreshing data!" . PHP_EOL;
    return date("H:i:s"); // return data to be cached
}, 10, true); // true = return with metadata
// or
$data = $cache->get("simple-cache-meta-test", true); // true = return with metadata

/*
Example cached item retrieved with metadata:
{
    "time":1511667506, <-- save unix timestamp
    "expire":10,       <-- expire time in seconds
    "data":"04:38:26", <-- unserialized data
    "permanent":false
}

Using metadata, we can, for example, calculate when item was saved or when it expires
We can also access the data itself with the "data" key
*/

$expiresin = ($data["time"] + $data["expire"]) - time(); // get unix timestamp when data expires and subtract current timestamp from it
$cacheddate = $data["data"]; // we access the data itself with the "data" key

echo "Latest cache save: $cacheddate, expires in $expiresin seconds";
```

## Dokumentācija

Apmeklējiet [https://github.com/flightphp/cache](https://github.com/flightphp/cache), lai skatītu kodu. Pārliecinieties, ka skatāt [examples](https://github.com/flightphp/cache/tree/master/examples) mapi papildu veidiem, kā izmantot kešu.