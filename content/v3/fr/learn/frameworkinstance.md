# Instance de Framework

Au lieu d'exécuter Flight en tant que classe statique globale, vous pouvez éventuellement l'exécuter en tant qu'instance d'objet.

```php
require 'flight/autoload.php';

$app = Flight::app();

$app->route('/', function () {
  echo 'hello world!';
});

$app->start();
```

Donc, au lieu d'appeler la méthode statique, vous appelleriez la méthode d'instance avec le même nom sur l'objet Engine.