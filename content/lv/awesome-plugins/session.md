# Ghostff/Session

PHP sesiju pārvaldnieks (nevibloķējošs, zibatmiņa, segmenta, sesijas šifrēšana). Izmanto PHP open_ssl opciju datu šifrēšanai/atšifrēšanai. Atbalsta Failu, MySQL, Redis un Memcached.

Noklikšķiniet [šeit](https://github.com/Ghostff/Session), lai aplūkotu kodu.

## Instalācija

Instalēt ar komponistu.

```bash
composer require ghostff/session
```

## Pamata konfigurācija

Jums nav nepieciešams nodot neko, lai izmantotu noklusējuma iestatienus savai sesijai. Jūs varat lasīt par vairākiem iestatījumiem [Github Readme](https://github.com/Ghostff/Session).

```php

lietojiet Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// viena lieta, ko atcerēties, ir tā, ka ir jāsaglabā savu sesiju katru lapas ielādi
// pretējā gadījumā jums būs jāpalaiž auto_commit savā konfigurācijā.
```
