# 扩展

Flight 旨在成为一个可扩展的框架。该框架提供了一组默认方法和组件，但允许您映射自己的方法，注册自己的类，甚至覆盖现有的类和方法。

如果您正在寻找 DIC（依赖注入容器），请转到[依赖注入容器](dependency-injection-container) 页面。

## 映射方法

您可以使用 `map` 函数来映射自己的简单自定义方法：

```php
// 映射您的方法
Flight::map('hello'， function (string $name) {
  echo "你好，$name!";
});

// 调用您的自定义方法
Flight::hello('Bob');
```

尽管可以创建简单的自定义方法，但建议仅在 PHP 中创建标准函数。这样可以在 IDE 中获得自动补全，并且更容易阅读。
上述代码的等效版本如下：

```php
function hello(string $name) {
  echo "你好，$name!";
}

hello('Bob');
```

当您需要将变量传递给您的方法以获取预期的值时，这更常用。像下面这样使用 `register()` 方法更适合传递配置，然后调用您预先配置的类。

## 注册类

要注册自己的类并对其进行配置，您可以使用 `register` 函数：

```php
// 注册您的类
Flight::register('user', User::class);

// 获得您类的实例
$user = Flight::user();
```

`register` 方法还允许您将参数传递给您的类构造函数。因此，当加载您的自定义类时，它将预先初始化。
通过传入一个额外的数组来定义构造函数参数。
这是加载数据库连接的示例：

```php
// 带构造函数参数注册类
Flight::register('db', PDO::class,['mysql:host=localhost;dbname=test', 'user', 'pass']);

// 获得您类的实例
// 这将使用定义的参数创建一个对象
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// 如果以后在您的代码中需要它，只需再次调用相同的方法
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

如果传递了额外的回调参数，它将在类构造后立即执行。
这允许您为新对象执行任何设置过程。回调函数接受一个参数，即新对象的实例。

```php
// 将被构造的对象传递给回调函数
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

默认情况下，每次加载您的类时，您将获得一个共享实例。
要获取类的新实例，只需将 `false` 作为参数传递即可：

```php
// 类的共享实例
$shared = Flight::db();

// 类的新实例
$new = Flight::db(false);
```

请记住，映射的方法优先于注册的类。如果您使用相同名称声明两者，那么只会调用映射的方法。

## 覆盖框架方法

Flight 允许您覆盖其默认功能以满足您自己的需求，而无需修改任何代码。您可以查看所有可覆盖的方法[此处](/learn/api)。

例如，当 Flight 无法将 URL 与路由匹配时，它将调用 `notFound` 方法，后者发送通用的 `HTTP 404` 响应。您可以通过使用 `map` 方法覆盖此行为：

```php
Flight::map('notFound', function() {
  // 显示自定义 404 页面
  include 'errors/404.html';
});
```

Flight 还允许您替换框架的核心组件。
例如，您可以使用自己的自定义类替换默认的 Router 类：

```php
// 注册您的自定义类
Flight::register('router', MyRouter::class);

// 当 Flight 加载 Router 实例时，它将加载您的类
$myrouter = Flight::router();
```

但是，`map` 和 `register` 等框架方法无法被覆盖。如果尝试这样做，将会收到错误。