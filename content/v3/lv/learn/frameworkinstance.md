# Framework instances

Vietā zīmēt `Flight` kā globālu statisku klasi, Jūs varat izpildespienākumu
palaižot kā objekta instanci.

```php
require 'flight/autoload.php';

$app = Flight::app();

$app->route('/', function () {
  echo 'sveika, pasaule!';
});

$app->start();
```

Tātad vietā, lai izsauktu statisko metodi, Jūs izsauktu instances metodi ar
tādu pašu nosaukumu uz Dzinēja objektu.