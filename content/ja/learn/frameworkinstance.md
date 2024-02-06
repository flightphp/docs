# フレームワークのインスタンス

グローバルな静的クラスとしてFlightを実行する代わりに、オブジェクトインスタンスとして実行することもできます。

```php
require 'flight/autoload.php';

$app = Flight::app();

$app->route('/', function () {
  echo 'ハローワールド！';
});

$app->start();
```

だから、静的メソッドを呼び出す代わりに、Engineオブジェクトの同じ名前のインスタンスメソッドを呼び出すことになります。