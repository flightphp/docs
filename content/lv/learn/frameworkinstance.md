# Iestatītāja instances

Tā vietā, lai izpildītu Flight kā globālu statisku klasi, jūs to varat palaist
kā objekta instanci.

```php
require 'flight/autoload.php';

$app = Flight::app();

$app->route('/', function () {
  echo 'sveika, pasaule!';
});

$app->start();
```

Tātad, lietojot statisko metodi, jūs izsauktu izpildes metodi ar
tādu pašu nosaukumu uz Engine objektu.