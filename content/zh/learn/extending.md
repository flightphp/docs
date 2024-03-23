# 扩展

Flight旨在成为可扩展的框架。该框架带有一组默认方法和组件，但允许您映射自己的方法、注册自己的类，甚至覆盖现有的类和方法。

如果您正在寻找DIC（依赖注入容器），请转到[Dependency Injection Container](dependency-injection-container)页面。

## 映射方法

要映射自己的简单自定义方法，您可以使用`map`函数：

```php
// 映射您的方法
Flight::map('hello'， function (string $name) {
  echo "你好，$name!";
});

// 调用自定义方法
Flight::hello('Bob');
```

当需要将变量传递给您的方法以获得预期值时，更多使用此功能。像下面这样使用`register()`方法更多是用于传递配置，然后调用您预先配置的类。

## 注册类

要注册自己的类并进行配置，您可以使用`register`函数：

```php
// 注册您的类
Flight::register('user'， User::class);

// 获取类的实例
$user = Flight::user();
```

注册方法还允许您传递参数给您的类构造函数。因此，当加载您的自定义类时，它将被预先初始化。您可以通过传递一个额外的数组来定义构造函数参数。以下是加载数据库连接的示例：

```php
// 注册带有构造函数参数的类
Flight::register('db'， PDO::class， ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// 获取您类的实例
// 这将使用定义的参数创建一个对象
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// 如果您在以后的代码中需要它，只需再次调用相同的方法
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

如果您传递了一个额外的回调参数，它将立即在类构造之后执行。这允许您为新对象执行任何设置程序。回调函数接受一个新对象的实例作为参数。

```php
// 回调会传入构建的对象
Flight::register(
  'db'，
  PDO::class，
  ['mysql:host=localhost;dbname=test', 'user', 'pass']，
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

请记住，映射方法优先于注册类。如果您使用相同的名称声明两者，只会调用映射的方法。

## 覆盖框架方法

Flight允许您覆盖其默认功能，以满足您自己的需求，而无需修改任何代码。

例如，当Flight无法将URL与路由匹配时，它会调用`notFound`方法，该方法发送一个通用的`HTTP 404`响应。您可以使用`map`方法覆盖此行为：

```php
Flight::map('notFound'， function() {
  // 显示自定义的404页面
  include 'errors/404.html';
});
```

Flight还允许您替换框架的核心组件。例如，您可以用自己的自定义类替换默认的Router类：

```php
// 注册您的自定义类
Flight::register('router'， MyRouter::class);

// 当Flight加载Router实例时，它将加载您的类
$myrouter = Flight::router();
```

然而，无法覆盖框架方法，如`map`和`register`。如果尝试这样做，将会收到错误消息。