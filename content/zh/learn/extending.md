# 扩展

Flight被设计为一个可扩展的框架。该框架附带了一组默认方法和组件，但它允许您映射您自己的方法，注册您自己的类，甚至重写现有的类和方法。

如果您在寻找DIC（依赖注入容器），请跳转到[依赖注入容器](dependency-injection-container)页面。

## 映射方法

要映射您自己的简单自定义方法，您可以使用`map`函数：

```php
// 映射您的方法
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// 调用您的自定义方法
Flight::hello('Bob');
```

虽然可以创建简单的自定义方法，但建议在PHP中创建标准函数。这在IDE中具备自动补全功能，并且更易于阅读。
上述代码的等效形式是：

```php
function hello(string $name) {
  echo "hello $name!";
}

hello('Bob');
```

当您需要将变量传递给您的方法以获取期望的值时，这种方式使用得更多。像下面这样使用`register()`方法更适合传入配置，然后调用您预配置的类。

## 注册类

要注册您自己的类并进行配置，您可以使用`register`函数：

```php
// 注册您的类
Flight::register('user', User::class);

// 获取您类的实例
$user = Flight::user();
```

register方法还允许您将参数传递给您的类构造函数。因此，当您加载自定义类时，它将预先初始化。
您可以通过传入一个额外的数组来定义构造函数参数。
以下是加载数据库连接的示例：

```php
// 注册带有构造函数参数的类
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// 获取您类的实例
// 这将使用定义的参数创建一个对象
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// 如果您稍后在代码中需要它，只需再次调用同一个方法
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

如果您传入一个额外的回调参数，它将在类构造后立即执行。这允许您为新对象执行任何设置程序。回调函数接受一个参数，即新对象的实例。

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

默认情况下，每次加载您的类时，您将获得一个共享实例。
要获取类的新实例，只需将`false`作为参数传递：

```php
// 类的共享实例
$shared = Flight::db();

// 类的新实例
$new = Flight::db(false);
```

请记住，映射的方法优先于注册的类。如果您使用相同的名称声明了两者，只有映射的方法会被调用。

## 日志记录

Flight没有内置的日志系统，然而，使用日志库与Flight配合是非常简单的。以下是使用Monolog库的示例：

```php
// index.php 或 bootstrap.php

// 在Flight中注册日志记录器
Flight::register('log', Monolog\Logger::class, [ 'name' ], function(Monolog\Logger $log) {
    $log->pushHandler(new Monolog\Handler\StreamHandler('path/to/your.log', Monolog\Logger::WARNING));
});
```

现在它已经注册，您可以在应用程序中使用它：

```php
// 在您的控制器或路由中
Flight::log()->warning('这是一条警告信息');
```

这将把消息记录到您指定的日志文件中。如果您希望在发生错误时记录某些内容，您可以使用`error`方法：

```php
// 在您的控制器或路由中

Flight::map('error', function(Throwable $ex) {
	Flight::log()->error($ex->getMessage());
	// 显示您的自定义错误页面
	include 'errors/500.html';
});
```

您还可以使用`before`和`after`方法创建基本的APM（应用性能监控）系统：

```php
// 在您的bootstrap文件中

Flight::before('start', function() {
	Flight::set('start_time', microtime(true));
});

Flight::after('start', function() {
	$end = microtime(true);
	$start = Flight::get('start_time');
	Flight::log()->info('请求 '.Flight::request()->url.' 耗时 ' . round($end - $start, 4) . ' 秒');

	// 您还可以将请求或响应头添加到日志中
	// 小心，因为如果您有很多请求，这将是
	// 大量数据
	Flight::log()->info('请求头: ' . json_encode(Flight::request()->headers));
	Flight::log()->info('响应头: ' . json_encode(Flight::response()->headers));
});
```

## 重写框架方法

Flight允许您重写其默认功能，以满足您自己的需求，而无需修改任何代码。您可以在[这里](/learn/api)查看所有可以重写的方法。

例如，当Flight无法将URL与路由匹配时，它会调用`notFound`方法，该方法发送一个通用的`HTTP 404`响应。您可以使用`map`方法重写此行为：

```php
Flight::map('notFound', function() {
  // 显示自定义404页面
  include 'errors/404.html';
});
```

Flight还允许您替换框架的核心组件。
例如，您可以用自己的自定义类替换默认的Router类：

```php
// 注册您的自定义类
Flight::register('router', MyRouter::class);

// 当Flight加载Router实例时，它将加载您的类
$myrouter = Flight::router();
```

然而，像`map`和`register`这样的框架方法无法被重写。如果您尝试这样做，将会出现错误。