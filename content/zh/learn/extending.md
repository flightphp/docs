# 扩展

Flight旨在成为一个可扩展的框架。该框架提供了一组默认方法和组件，但允许您映射自己的方法，注册自己的类，甚至覆盖现有的类和方法。

如果您正在寻找DIC（依赖注入容器），请转到[Dependency Injection Container](dependency-injection-container)页面。

## 映射方法

要映射自己简单的自定义方法，可以使用`map`函数：

```php
// 映射您的方法
Flight::map('hello', function (string $name) {
  echo "你好 $name!";
});

// 调用您的自定义方法
Flight::hello('Bob');
```

当您需要将变量传递到方法中以获得预期值时，会更多地使用这种方法。像下面使用`register()`方法更多地是用于传递配置，然后调用您预先配置的类。

## 注册类

要注册自己的类并对其进行配置，可以使用`register`函数：

```php
// 注册您的类
Flight::register('user', User::class);

// 获取您的类的实例
$user = Flight::user();
```

注册方法还允许您将参数传递给类的构造函数。因此，当您加载您的自定义类时，它将被预先初始化。您可以通过传递一个额外的数组来定义构造函数参数。以下是加载数据库连接的示例：

```php
// 注册具有构造函数参数的类
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// 获取您的类的实例
// 这将使用定义的参数创建一个对象
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// 如果您稍后在代码中需要它，只需再次调用相同的方法
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

如果您传递了额外的回调参数，它将在类构建后立即执行。这允许您为新对象执行任何设置程序。回调函数接受一个参数，即新对象的实例。

```php
// 回调将传递构建的对象
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

默认情况下，每次加载类时都会获得共享实例。要获得类的新实例，只需将`false`作为参数传入：

```php
// 类的共享实例
$shared = Flight::db();

// 类的新实例
$new = Flight::db(false);
```

请记住，映射方法优先于注册的类。如果您使用相同名称声明两者，只会调用映射方法。

## 覆盖框架方法

Flight允许您覆盖其默认功能，以满足您自己的需求，而无需修改任何代码。

例如，当Flight无法将URL匹配到路由时，它会调用`notFound`方法，该方法发送一个通用的`HTTP 404`响应。您可以通过使用`map`方法覆盖此行为：

```php
Flight::map('notFound', function() {
  // 显示自定义404页面
  include 'errors/404.html';
});
```

Flight还允许您替换框架的核心组件。例如，您可以用自定义的路由器类替换默认的路由器类：

```php
// 注册您的自定义类
Flight::register('router', MyRouter::class);

// 当Flight加载路由器实例时，它将加载您的类
$myrouter = Flight::router();
```

然而，无法覆盖框架方法像`map`和`register`。如果尝试这样做，您将收到错误消息。