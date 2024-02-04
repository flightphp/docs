# Ietvars Instance

Tas vietā, lai palaistu Flight kā globālu statisku klasi, jūs varat izvēlēties palaist to
kā objekta instance.

```php
require 'flight/autoload.php';

$app = Flight::app();

$app->route('/', function () {
  echo 'Sveika, pasaule!';
});

$app->start();
```

Tādēļ, tā vietā, lai izsauktu statisko metodi, jūs izsauktu instance metodi ar
tādu pašu nosaukumu uz Engine objektu.