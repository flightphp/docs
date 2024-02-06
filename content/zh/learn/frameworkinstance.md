# 框架实例

而不是将Flight作为全局静态类运行，您可以选择将其作为对象实例运行。

```php
require 'flight/autoload.php';

$app = Flight::app();

$app->route('/', function () {
  echo 'hello world!';
});

$app->start();
```

因此，您可以调用Engine对象上具有相同名称的实例方法，而不是调用静态方法。