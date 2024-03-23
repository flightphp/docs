# 迁移到 v3

向后兼容性在大多数情况下得到了保留，但在从 v2 迁移到 v3 时有一些更改需要您注意。

## 输出缓冲行为 (3.5.0)

[输出缓冲](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) 是 PHP 脚本生成的输出被存储在一个缓冲区 (PHP 内部) 中，然后再发送到客户端的过程。这允许您在发送到客户端之前修改输出。

在 MVC 应用程序中，控制器是「管理者」，负责管理视图的操作。在控制器之外生成输出 (或在 Flight 中有时是匿名函数) 会破坏 MVC 模式。此更改是为了更符合 MVC 模式，使框架更可预测且更易于使用。

在 v2 中，输出缓冲的处理方式并未一致关闭自身的输出缓冲区，这使得 [单元测试](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) 和 [流式传输](https://github.com/flightphp/core/issues/413) 变得更加困难。对于大多数用户来说，此更改实际上可能不会影响您。但是，如果在不可调用和控制器之外输出内容 (例如在挂钩中)，您很可能会遇到问题。在过去，可以在挂钩中输出内容并在框架实际执行之前运行，但在未来将无法正常工作。

### 您可能会遇到问题的地方
```php
// index.php
require 'vendor/autoload.php';

// 仅为示例
define('START_TIME', microtime(true));

function hello() {
	echo 'Hello World';
}

Flight::map('hello', 'hello');
Flight::after('hello', function(){
	// 这实际上是可以的
	echo '<p>This Hello World phrase was brought to you by the letter "H"</p>';
});

Flight::before('start', function(){
	// 这样的事情将会导致错误
	echo '<html><head><title>My Page</title></head><body>';
});

Flight::route('/', function(){
	// 这实际上是可以的
	echo 'Hello World';

	// 这也应该没有问题
	Flight::hello();
});

Flight::after('start', function(){
	// 这会导致错误
	echo '<div>Your page loaded in '.(microtime(true) - START_TIME).' seconds</div></body></html>';
});
```

### 打开 v2 渲染行为

您是否仍然可以保持您的旧代码不进行重写就能使其在 v3 中正常工作？是的，您可以！通过将 `flight.v2.output_buffering` 配置选项设置为 `true` 来打开 v2 渲染行为。这将允许您继续使用旧的渲染行为，但建议将其修复以便向前兼容。在框架的 v4 中，此功能将被移除。

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// 现在这将是正常的
	echo '<html><head><title>My Page</title></head><body>';
});

// 更多的代码
```

## 调度器更改 (3.7.0)

如果您直接调用 `Dispatcher` 的静态方法，例如 `Dispatcher::invokeMethod()`，`Dispatcher::execute()` 等，您需要更新您的代码，不再直接调用这些方法。`Dispatcher` 已转换为更面向对象，以便更轻松使用依赖注入容器。如果您需要调用类似于 `Dispatcher` 的方法，您可以手动使用类似 `$result = $class->$method(...$params);` 或 `call_user_func_array()`。