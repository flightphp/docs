## 扩展 / 容器

Flight 被设计为一个可扩展的框架。该框架附带一组默认的方法和组件，但允许您映射自己的方法，注册自己的类，甚至覆盖现有的类和方法。

## 映射方法

要映射您自己的简单自定义方法，您可以使用 `map` 函数：

```php
// 映射您的方法
Flight::map('hello', function (string $name) {
  echo "你好 $name!";
});

// 调用您的自定义方法
Flight::hello('Bob');
```

在需要将变量传递到您的方法中以获得预期值时，使用下面的 `register()` 方法更多。它更适用于传入配置，然后调用您预先配置的类。

## 注册类 / 容器化

要注册您自己的类并配置它，您可以使用 `register` 函数：

```php
// 注册您的类
Flight::register('user', User::class);

// 获取您的类的实例
$user = Flight::user();
```

`register` 方法还允许您向类构造函数传递参数。因此，当您加载自定义类时，它将被预先初始化。您可以通过传入一个额外的数组来定义构造函数参数。以下是加载数据库连接的示例：

```php
// 注册带构造函数参数的类
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// 获取您的类的实例
// 这将使用定义的参数创建一个对象
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// 如果您以后在代码中需要它，只需再次调用相同的方法
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

如果传入一个额外的回调参数，它将在类构造完成后立即执行。这允许您为新对象执行任何设置程序。回调函数接受一个参数，即新对象的实例。

```php
// 回调将传递构造的对象
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

默认情况下，每次加载类时都会获得一个共享实例。要获取类的新实例，只需将 `false` 作为参数传入：

```php
// 类的共享实例
$shared = Flight::db();

// 类的新实例
$new = Flight::db(false);
```

请记住，映射的方法优先于注册的类。如果您同时使用相同名称声明两者，则只会调用映射的方法。

## 覆盖

Flight 允许您覆盖其默认功能以满足您自己的需求，而无需修改任何代码。

例如，当 Flight 无法将 URL 与路由匹配时，它会调用 `notFound` 方法，该方法发送一个常规的 `HTTP 404` 响应。您可以使用 `map` 方法覆盖此行为：

```php
Flight::map('notFound', function() {
  // 显示自定义 404 页面
  include 'errors/404.html';
});
```

Flight 还允许您替换框架的核心组件。例如，您可以使用自定义类替换默认的 Router 类：

```php
// 注册您的自定义类
Flight::register('router', MyRouter::class);

// 当 Flight 加载 Router 实例时，将加载您的类
$myrouter = Flight::router();
```

但是，`map` 和 `register` 等框架方法无法被覆盖。如果尝试这样做，将会收到错误消息。