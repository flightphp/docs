# 扩展 / 容器

Flight 旨在成为一个可扩展的框架。该框架提供了一组默认方法和组件，但允许您映射自己的方法、注册自己的类，甚至覆盖现有的类和方法。

## 映射方法

要映射您自己的简单自定义方法，您可以使用 `map` 函数：

```php
// 映射您的方法
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// 调用您的自定义方法
Flight::hello('Bob');
```

当您需要将变量传递到方法中以获得预期值时，使用 `register()` 方法如下方所示更为常见，通常用于传递配置然后调用您预先配置的类。

## 注册类 / 容器化

要注册您自己的类并对其进行配置，您可以使用 `register` 函数：

```php
// 注册您的类
Flight::register('user', User::class);

// 获取您的类的实例
$user = Flight::user();
```

注册方法还允许您传递参数给您的类构造函数。因此，当您加载您的自定义类时，它将预先初始化。您可以通过传递一个额外的数组来定义构造函数参数。以下是加载数据库连接的示例：

```php
// 注册带有构造函数参数的类
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// 获取您的类的实例
// 这将使用定义的参数创建一个对象
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// 如果您后续在您的代码中需要它，只需再次调用相同的方法
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

如果您传递了额外的回调参数，它将立即在类构造后执行。这使您能够为新对象执行任何设置程序。回调函数接受一个参数，即新对象的实例。

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

默认情况下，每次加载您的类时，您将获得一个共享实例。要获取类的新实例，只需将 `false` 作为参数传递：

```php
// 共享实例
$shared = Flight::db();

// 类的新实例
$new = Flight::db(false);
```

请注意，映射方法优先于注册类。如果您使用相同名称声明两者，只会调用映射方法。

## 覆盖

Flight 允许您覆盖其默认功能以满足您自己的需求，无需修改任何代码。

例如，当 Flight 无法将 URL 与路由匹配时，它会调用 `notFound` 方法，发送一个通用的 `HTTP 404` 响应。您可以使用 `map` 方法覆盖此行为：

```php
Flight::map('notFound', function() {
  // 显示自定义 404 页面
  include 'errors/404.html';
});
```

Flight 还允许您替换框架的核心组件。例如，您可以用自己的自定义类替换默认的 Router 类：

```php
// 注册您的自定义类
Flight::register('router', MyRouter::class);

// 当 Flight 加载 Router 实例时，将加载您的类
$myrouter = Flight::router();
```

然而，框架方法如 `map` 和 `register` 不能被覆盖。如果尝试这样做，将会收到错误。