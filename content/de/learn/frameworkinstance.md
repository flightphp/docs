# Framework-Instanz

Anstatt Flight als globale statische Klasse auszuführen, können Sie es optional als Objektinstanz ausführen.

```php
require 'flight/autoload.php';

$app = Flight::app(); // Erstellt eine Instanz

$app->route('/', function () {
  echo 'hello world!';
});

$app->start();
```

Anstatt die statische Methode aufzurufen, würden Sie also die Instanzmethode mit demselben Namen auf dem Engine-Objekt aufrufen.