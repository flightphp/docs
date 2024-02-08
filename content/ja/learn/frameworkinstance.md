# フレームワークのインスタンス

Flightをグローバルな静的クラスとして実行する代わりに、オブジェクトのインスタンスとして実行することもできます。

```php
require 'flight/autoload.php';

$app = Flight::app();

$app->route('/', function () {
  echo 'hello world!';
});

$app->start();
```

静的なメソッドを呼び出す代わりに、同じ名前のインスタンスメソッドをEngineオブジェクトで呼び出すことになります。