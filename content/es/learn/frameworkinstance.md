# Instancia del Framework

En lugar de ejecutar Flight como una clase estática global, opcionalmente puedes ejecutarlo como una instancia de objeto.

```php
require 'flight/autoload.php';

$app = Flight::app();

$app->route('/', function () {
  echo '¡hola mundo!';
});

$app->start();
```

Por lo tanto, en lugar de llamar al método estático, llamarías al método de instancia con el mismo nombre en el objeto del Motor.