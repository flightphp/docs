# 迁移到 v3

大部分情况下都保留了向后兼容性，但在从 v2 迁移到 v3 时有一些变化需要注意。

## 输出缓冲行为（3.5.0）

[输出缓冲](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) 是 PHP 脚本生成的输出被存储在缓冲区（PHP 内部）中，然后再发送到客户端的过程。这允许您在发送到客户端之前修改输出。

在 MVC 应用程序中，控制器是“管理器”，负责管理视图的操作。在控制器外部生成输出（或在 Flight 的情况下有时是匿名函数）会破坏 MVC 模式。这一变化旨在更符合 MVC 模式，以使框架更可预测和更易于使用。

在 v2 中，输出缓冲的处理方式不一致，导致它没有始终关闭自己的输出缓冲，这使得 [单元测试](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) 和 [流式处理](https://github.com/flightphp/core/issues/413) 更加困难。对于大多数用户，这种变化实际上可能不会影响您。但是，如果您在回调函数和控制器之外输出内容（例如在 hook 中），您很可能会遇到问题。在过去，在框架实际执行之前在 hook 中输出内容以及 output 之外的内容可能是有效的，但在未来不会有效。

### 可能会出现问题的地方
```php
// index.php
require 'vendor/autoload.php';

// 一个例子
define('START_TIME', microtime(true));

function hello() {
	echo 'Hello World';
}

Flight::map('hello', 'hello');
Flight::after('hello', function(){
	// 这实际上没问题
	echo '<p>This Hello World phrase was brought to you by the letter "H"</p>';
});

Flight::before('start', function(){
	// 这样做将导致错误
	echo '<html><head><title>My Page</title></head><body>';
});

Flight::route('/', function(){
	// 这实际上没问题
	echo 'Hello World';

	// 这也应该没问题
	Flight::hello();
});

Flight::after('start', function(){
	// 这将导致错误
	echo '<div>Your page loaded in '.(microtime(true) - START_TIME).' seconds</div></body></html>';
});
```

### 开启 v2 渲染行为

您是否能保留旧代码不进行重写以使其在 v3 中运行？是的，您可以！您可以通过将 `flight.v2.output_buffering` 配置选项设置为 `true` 来开启 v2 渲染行为。这将允许您继续使用旧的渲染行为，但建议在未来修复它。在框架的 v4 中，这将被移除。

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// 现在这将是没问题的
	echo '<html><head><title>My Page</title></head><body>';
});

// 更多代码
```

## 调度器更改（3.7.0）

如果您直接调用了 `Dispatcher` 的静态方法，例如 `Dispatcher::invokeMethod()`、`Dispatcher::execute() `等，您需要更新您的代码，不要直接调用这些方法。`Dispatcher` 已转换为更具面向对象性质，以便更轻松地使用依赖注入容器。如果需要调用类似 Dispatcher 的方法，您可以手动使用类似 `$result = $class->$method(...$params);` 或 `call_user_func_array()`。