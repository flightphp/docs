# Instância do Framework

Em vez de executar a Flight como uma classe estática global, você pode opcionalmente executá-la como uma instância de objeto.

```php
require 'flight/autoload.php';

$app = Flight::app();

$app->route('/', function () {
  // exibe 'olá mundo!'
});

$app->start();
```

Portanto, em vez de chamar o método estático, você chamaria o método de instância com o mesmo nome no objeto Engine.