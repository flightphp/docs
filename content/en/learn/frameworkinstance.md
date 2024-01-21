# Framework Instance

Instead of running Flight as a global static class, you can optionally run it
as an object instance.

```php
require 'flight/autoload.php';

$app = Flight::app();

$app->route('/', function () {
  echo 'hello world!';
});

$app->start();
```

So instead of calling the static method, you would call the instance method with
the same name on the Engine object.