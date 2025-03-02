# Instância do Framework

Em vez de executar o Flight como uma classe estática global, você pode executá-lo opcionalmente como uma instância de objeto.

```php
require 'flight/autoload.php';

$app = Flight::app();

$app->route('/', function () {
  echo 'olá mundo!';
});

$app->start();
```

Portanto, em vez de chamar o método estático, você chamaria o método de instância com o mesmo nome no objeto Engine.