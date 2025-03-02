# Framework-Instanz

Anstatt Flight als globale statische Klasse auszuführen, können Sie es optional als
ein Objektinstanz ausführen.

```php
require 'flight/autoload.php';

$app = Flight::app();

$app->route('/', function () {
  echo 'Hallo Welt!';
});

$app->start();
```

Also würden Sie anstelle des Aufrufs der statischen Methode die Instanzmethode mit
demselben Namen am Engine-Objekt aufrufen.