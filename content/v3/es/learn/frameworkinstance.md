# Instancia del Framework

En lugar de ejecutar Flight como una clase estática global, puedes opcionalmente ejecutarlo
como una instancia de objeto.

```php
require 'flight/autoload.php';

$app = Flight::app();

$app->route('/', function () {
  echo '¡hola mundo!';
});

$app->start();
```

Así que en lugar de llamar al método estático, llamarías al método de instancia con
el mismo nombre en el objeto Engine.