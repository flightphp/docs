# 扩展

Flight 被设计为一个可扩展的框架。该框架提供了一组默认方法和组件，但它允许你映射自己的方法，注册自己的类，甚至覆盖现有的类和方法。

如果你在寻找 DIC（依赖注入容器），请查看 [Dependency Injection Container](dependency-injection-container) 页面。

## 映射方法

要映射你自己的简单自定义方法，可以使用 `map` 函数：

```php
// 映射你的方法
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// 调用你的自定义方法
Flight::hello('Bob');
```

虽然可以创建简单的自定义方法，但建议直接在 PHP 中创建标准函数。这在 IDE 中具备自动完成功能，更易于阅读。上述代码的等效形式为：

```php
function hello(string $name) {
  echo "hello $name!";
}

hello('Bob');
```

当你需要将变量传入你的方法以获得预期值时，这种方式更为常用。使用 `register()` 方法，如下所示，更适用于传入配置，然后调用你预先配置的类。

## 注册类

要注册你自己的类并配置它，可以使用 `register` 函数：

```php
// 注册你的类
Flight::register('user', User::class);

// 获取你的类的实例
$user = Flight::user();
```

register 方法还允许你将参数传递给类构造函数。因此，当你加载自定义类时，它将被预初始化。你可以通过传入附加数组来定义构造函数参数。以下是加载数据库连接的示例：

```php
// 使用构造函数参数注册类
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// 获取你的类的实例
// 这将创建一个具有定义参数的对象
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// 如果你稍后需要它，只需再次调用相同的方法
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

如果你传入附加的回调参数，它将在类构造完成后立即执行。这允许你为新对象执行任何设置程序。回调函数接受一个参数，即新对象的实例。

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

默认情况下，每次加载类时，你将获得一个共享实例。要获取类的新实例，只需将 `false` 作为参数传入：

```php
// 类的共享实例
$shared = Flight::db();

// 类的新实例
$new = Flight::db(false);
```

请注意，映射方法优先于注册类。如果你用相同的名称声明两者，将只调用映射的方法。

## 日志记录

Flight 没有内置的日志系统，但是，使用日志库与 Flight 非常容易。以下是使用 Monolog 库的示例：

```php
// index.php 或 bootstrap.php

// 使用 Flight 注册日志记录器
Flight::register('log', Monolog\Logger::class, [ 'name' ], function(Monolog\Logger $log) {
    $log->pushHandler(new Monolog\Handler\StreamHandler('path/to/your.log', Monolog\Logger::WARNING));
});
```

现在它已注册，你可以在应用程序中使用它：

```php
// 在你的控制器或路由中
Flight::log()->warning('这是一个警告信息');
```

这将把消息记录到你指定的日志文件中。如果你想在发生错误时记录某些内容，可以使用 `error` 方法：

```php
// 在你的控制器或路由中

Flight::map('error', function(Throwable $ex) {
	Flight::log()->error($ex->getMessage());
	// 显示你的自定义错误页面
	include 'errors/500.html';
});
```

你还可以使用 `before` 和 `after` 方法创建一个基本的 APM（应用程序性能监控）系统：

```php
// 在你的引导文件中

Flight::before('start', function() {
	Flight::set('start_time', microtime(true));
});

Flight::after('start', function() {
	$end = microtime(true);
	$start = Flight::get('start_time');
	Flight::log()->info('请求 '.Flight::request()->url.' 花费 ' . round($end - $start, 4) . ' 秒');

	// 你还可以将请求或响应头添加到日志中（小心，因为如果你有很多请求，这会产生大量数据）
	Flight::log()->info('请求头: ' . json_encode(Flight::request()->headers));
	Flight::log()->info('响应头: ' . json_encode(Flight::response()->headers));
});
```

## 覆盖框架方法

Flight 允许你覆盖其默认功能以满足自己的需求，而无需修改任何代码。你可以在 [这里](/learn/api) 查看所有可以覆盖的方法。

例如，当 Flight 无法将 URL 匹配到路由时，它会调用 `notFound` 方法并发送通用的 `HTTP 404` 响应。你可以使用 `map` 方法覆盖此行为：

```php
Flight::map('notFound', function() {
  // 显示自定义 404 页面
  include 'errors/404.html';
});
```

Flight 还允许你替换框架的核心组件。例如，你可以用自己的自定义类替换默认的 Router 类：

```php
// 注册你的自定义类
Flight::register('router', MyRouter::class);

// 当 Flight 加载 Router 实例时，它将加载你的类
$myrouter = Flight::router();
```

但是，像 `map` 和 `register` 这样的框架方法无法被覆盖。如果你尝试这样做，将会出现错误。