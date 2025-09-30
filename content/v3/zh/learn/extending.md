# 扩展

## 概述

Flight 被设计为一个可扩展的框架。该框架附带一组默认方法和组件，但它允许您映射自己的方法、注册自己的类，甚至覆盖现有的类和方法。

## 理解

您可以通过 2 种方式扩展 Flight 的功能：

1. 映射方法 - 这用于创建简单的自定义方法，您可以在应用程序的任何地方调用它们。这些通常用于实用函数，您希望能够在代码的任何地方调用。
2. 注册类 - 这用于将自己的类注册到 Flight 中。这通常用于具有依赖项或需要配置的类。

您也可以覆盖现有的框架方法，以更改其默认行为，以更好地满足您的项目需求。

> 如果您正在寻找 DIC（依赖注入容器），请跳转到 [依赖注入容器](/learn/dependency-injection-container) 页面。

## 基本用法

### 覆盖框架方法

Flight 允许您覆盖其默认功能以满足自己的需求，而无需修改任何代码。您可以查看所有可覆盖的方法 [下面](#mappable-framework-methods)。

例如，当 Flight 无法将 URL 匹配到路由时，它会调用 `notFound` 方法，该方法发送通用的 `HTTP 404` 响应。您可以使用 `map` 方法覆盖此行为：

```php
Flight::map('notFound', function() {
  // 显示自定义 404 页面
  include 'errors/404.html';
});
```

Flight 还允许您替换框架的核心组件。例如，您可以用自己的自定义类替换默认的 Router 类：

```php
// 创建自定义 Router 类
class MyRouter extends \flight\net\Router {
	// 在这里覆盖方法
	// 例如，用于 GET 请求的快捷方式，以移除
	// pass route 功能
	public function get($pattern, $callback, $alias = '') {
		return parent::get($pattern, $callback, false, $alias);
	}
}

// 注册自定义类
Flight::register('router', MyRouter::class);

// 当 Flight 加载 Router 实例时，它将加载您的类
$myRouter = Flight::router();
$myRouter->get('/hello', function() {
  echo "Hello World!";
}, 'hello_alias');
```

但是，像 `map` 和 `register` 这样的框架方法不能被覆盖。如果您尝试这样做，将得到错误（再次查看 [下面](#mappable-framework-methods) 以获取方法列表）。

### 可映射的框架方法

以下是框架的完整方法集。它包括核心方法，这些是常规的静态方法，以及可扩展方法，这些是可映射的方法，可以被过滤或覆盖。

#### 核心方法

这些方法是框架的核心，不能被覆盖。

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // 创建自定义框架方法。
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // 将类注册到框架方法。
Flight::unregister(string $name) // 注销框架方法的类。
Flight::before(string $name, callable $callback) // 在框架方法之前添加过滤器。
Flight::after(string $name, callable $callback) // 在框架方法之后添加过滤器。
Flight::path(string $path) // 为自动加载类添加路径。
Flight::get(string $key) // 获取由 Flight::set() 设置的变量。
Flight::set(string $key, mixed $value) // 在 Flight 引擎中设置变量。
Flight::has(string $key) // 检查变量是否已设置。
Flight::clear(array|string $key = []) // 清除变量。
Flight::init() // 将框架初始化为其默认设置。
Flight::app() // 获取应用程序对象实例
Flight::request() // 获取请求对象实例
Flight::response() // 获取响应对象实例
Flight::router() // 获取路由器对象实例
Flight::view() // 获取视图对象实例
```

#### 可扩展方法

```php
Flight::start() // 启动框架。
Flight::stop() // 停止框架并发送响应。
Flight::halt(int $code = 200, string $message = '') // 使用可选的状态码和消息停止框架。
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // 将 URL 模式映射到回调。
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // 将 POST 请求 URL 模式映射到回调。
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // 将 PUT 请求 URL 模式映射到回调。
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // 将 PATCH 请求 URL 模式映射到回调。
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // 将 DELETE 请求 URL 模式映射到回调。
Flight::group(string $pattern, callable $callback) // 为 URL 创建分组，模式必须是字符串。
Flight::getUrl(string $name, array $params = []) // 根据路由别名生成 URL。
Flight::redirect(string $url, int $code) // 重定向到另一个 URL。
Flight::download(string $filePath) // 下载文件。
Flight::render(string $file, array $data, ?string $key = null) // 渲染模板文件。
Flight::error(Throwable $error) // 发送 HTTP 500 响应。
Flight::notFound() // 发送 HTTP 404 响应。
Flight::etag(string $id, string $type = 'string') // 执行 ETag HTTP 缓存。
Flight::lastModified(int $time) // 执行最后修改 HTTP 缓存。
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // 发送 JSON 响应。
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // 发送 JSONP 响应。
Flight::jsonHalt(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // 发送 JSON 响应并停止框架。
Flight::onEvent(string $event, callable $callback) // 注册事件监听器。
Flight::triggerEvent(string $event, ...$args) // 触发事件。
```

使用 `map` 和 `register` 添加的任何自定义方法也可以被过滤。有关如何过滤这些方法的示例，请参阅 [过滤方法](/learn/filtering) 指南。

#### 可扩展的框架类

您可以通过扩展它们并注册自己的类来覆盖几个类的功能。这些类是：

```php
Flight::app() // 应用程序类 - 扩展 flight\Engine 类
Flight::request() // 请求类 - 扩展 flight\net\Request 类
Flight::response() // 响应类 - 扩展 flight\net\Response 类
Flight::router() // 路由器类 - 扩展 flight\net\Router 类
Flight::view() // 视图类 - 扩展 flight\template\View 类
Flight::eventDispatcher() // 事件分发器类 - 扩展 flight\core\Dispatcher 类
```

### 映射自定义方法

要映射自己的简单自定义方法，您使用 `map` 函数：

```php
// 映射您的方法
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// 调用您的自定义方法
Flight::hello('Bob');
```

虽然可以创建简单的自定义方法，但推荐直接在 PHP 中创建标准函数。这在 IDE 中具有自动完成功能，并且更容易阅读。上述代码的等效形式是：

```php
function hello(string $name) {
  echo "hello $name!";
}

hello('Bob');
```

这更多用于当您需要将变量传递到方法中以获取预期值时。使用下面的 `register()` 方法更多用于传入配置，然后调用您的预配置类。

### 注册自定义类

要注册自己的类并配置它，您使用 `register` 函数。与 map() 相比，此方法的优势是您可以在调用此函数时重用同一类（对于 `Flight::db()` 共享同一实例会很有帮助）。

```php
// 注册您的类
Flight::register('user', User::class);

// 获取类的实例
$user = Flight::user();
```

register 方法还允许您将参数传递给类的构造函数。因此，当您加载自定义类时，它将预先初始化。您可以通过传入额外的数组来定义构造函数参数。以下是加载数据库连接的示例：

```php
// 使用构造函数参数注册类
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// 获取类的实例
// 这将使用定义的参数创建对象
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();

// 如果您在代码中稍后需要它，只需再次调用同一方法
class SomeController {
  public function __construct() {
	$this->db = Flight::db();
  }
}
```

如果您传入额外的回调参数，它将在类构造后立即执行。这允许您为新对象执行任何设置过程。回调函数接受一个参数，即新对象的实例。

```php
// 回调将传递已构造的对象
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

默认情况下，每次加载类时，您将获得共享实例。要获取类的全新实例，只需将 `false` 作为参数传入：

```php
// 类的共享实例
$shared = Flight::db();

// 类的全新实例
$new = Flight::db(false);
```

> **注意：** 请记住，映射的方法优先于注册的类。如果您使用相同的名称声明两者，只有映射的方法将被调用。

### 示例

以下是一些如何使用核心中未内置的功能扩展 Flight 的示例。

#### 日志记录

Flight 没有内置的日志系统，但是使用日志库与 Flight 一起使用非常简单。以下是使用 Monolog 库的示例：

```php
// services.php

// 使用 Flight 注册日志记录器
Flight::register('log', Monolog\Logger::class, [ 'name' ], function(Monolog\Logger $log) {
    $log->pushHandler(new Monolog\Handler\StreamHandler('path/to/your.log', Monolog\Logger::WARNING));
});
```

现在注册后，您可以在应用程序中使用它：

```php
// 在您的控制器或路由中
Flight::log()->warning('This is a warning message');
```

这将向您指定的日志文件记录消息。如果您想在发生错误时记录某些内容怎么办？您可以使用 `error` 方法：

```php
// 在您的控制器或路由中
Flight::map('error', function(Throwable $ex) {
	Flight::log()->error($ex->getMessage());
	// 显示自定义错误页面
	include 'errors/500.html';
});
```

您还可以使用 `before` 和 `after` 方法创建一个基本的 APM（应用程序性能监控）系统：

```php
// 在您的 services.php 文件中

Flight::before('start', function() {
	Flight::set('start_time', microtime(true));
});

Flight::after('start', function() {
	$end = microtime(true);
	$start = Flight::get('start_time');
	Flight::log()->info('Request '.Flight::request()->url.' took ' . round($end - $start, 4) . ' seconds');

	// 您还可以添加请求或响应头
	// 以记录它们（小心，因为如果您有很多请求，这将是
	// 很多数据）
	Flight::log()->info('Request Headers: ' . json_encode(Flight::request()->headers));
	Flight::log()->info('Response Headers: ' . json_encode(Flight::response()->headers));
});
```

#### 缓存

Flight 没有内置的缓存系统，但是使用缓存库与 Flight 一起使用非常简单。以下是使用 [PHP File Cache](/awesome-plugins/php_file_cache) 库的示例：

```php
// services.php

// 使用 Flight 注册缓存
Flight::register('cache', \flight\Cache::class, [ __DIR__ . '/../cache/' ], function(\flight\Cache $cache) {
    $cache->setDevMode(ENVIRONMENT === 'development');
});
```

现在注册后，您可以在应用程序中使用它：

```php
// 在您的控制器或路由中
$data = Flight::cache()->get('my_cache_key');
if (empty($data)) {
	// 执行一些处理以获取数据
	$data = [ 'some' => 'data' ];
	Flight::cache()->set('my_cache_key', $data, 3600); // 缓存 1 小时
}
```

#### 简单的 DIC 对象实例化

如果您在应用程序中使用 DIC（依赖注入容器），您可以使用 Flight 来帮助实例化对象。以下是使用 [Dice](https://github.com/level-2/Dice) 库的示例：

```php
// services.php

// 创建新容器
$container = new \Dice\Dice;
// 不要忘记像下面一样将其重新分配给自己！
$container = $container->addRule('PDO', [
	// shared 表示每次返回相同的对象
	'shared' => true,
	'constructParams' => ['mysql:host=localhost;dbname=test', 'user', 'pass' ]
]);

// 现在我们可以创建一个可映射的方法来创建任何对象。
Flight::map('make', function($class, $params = []) use ($container) {
	return $container->create($class, $params);
});

// 这注册容器处理程序，以便 Flight 知道用于控制器/中间件的它
Flight::registerContainerHandler(function($class, $params) {
	Flight::make($class, $params);
});


// 假设我们有一个以下示例类，它在构造函数中接受 PDO 对象
class EmailCron {
	protected PDO $pdo;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function send() {
		// 发送电子邮件的代码
	}
}

// 最后，您可以使用依赖注入创建对象
$emailCron = Flight::make(EmailCron::class);
$emailCron->send();
```

很酷吧？

## 另请参阅
- [依赖注入容器](/learn/dependency-injection-container) - 如何与 Flight 使用 DIC。
- [文件缓存](/awesome-plugins/php_file_cache) - 使用缓存库与 Flight 的示例。

## 故障排除
- 记住，映射的方法优先于注册的类。如果您使用相同的名称声明两者，只有映射的方法将被调用。

## 更新日志
- v2.0 - 初始发布。