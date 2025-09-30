# 迁移到 v3

向后兼容性在大多数情况下得到了保持，但从 v2 迁移到 v3 时，您应该注意一些更改。有些更改与设计模式冲突过多，因此必须进行一些调整。

## 输出缓冲行为

_v3.5.0_

[输出缓冲](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) 是 PHP 脚本生成的输出在发送到客户端之前存储在缓冲区（PHP 内部）中的过程。这允许您在发送到客户端之前修改输出。

在 MVC 应用程序中，Controller 是“经理”，它管理视图的行为。在控制器外部（或在 Flight 的情况下有时是一个匿名函数）生成输出会破坏 MVC 模式。此更改是为了更符合 MVC 模式，并使框架更可预测、更易用。

在 v2 中，输出缓冲的处理方式是不一致地关闭其自身的输出缓冲区，这使得 [单元测试](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) 和 [流式传输](https://github.com/flightphp/core/issues/413) 更加困难。对于大多数用户，此更改实际上可能不会影响您。但是，如果您在可调用对象和控制器外部回显内容（例如在钩子中），您可能会遇到问题。在钩子中回显内容，以及在框架实际执行之前回显内容，在过去可能有效，但今后将无效。

### 您可能遇到问题的位置
```php
// index.php
require 'vendor/autoload.php';

// 只是一个例子
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
	// 像这样的内容会导致错误
	echo '<html><head><title>My Page</title></head><body>';
});

Flight::route('/', function(){
	// 这实际上没问题
	echo 'Hello World';

	// 这也应该没问题
	Flight::hello();
});

Flight::after('start', function(){
	// 这会导致错误
	echo '<div>Your page loaded in '.(microtime(true) - START_TIME).' seconds</div></body></html>';
});
```

### 开启 v2 渲染行为

您是否仍然可以保持旧代码不变，而无需重写以使其与 v3 兼容？是的，您可以！您可以通过将 `flight.v2.output_buffering` 配置选项设置为 `true` 来开启 v2 渲染行为。这将允许您继续使用旧的渲染行为，但建议今后修复它。在框架的 v4 版本中，这将被移除。

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// 现在这就没问题了
	echo '<html><head><title>My Page</title></head><body>';
});

// 更多代码 
```

## 调度器更改

_v3.7.0_

如果您直接调用了 `Dispatcher` 的静态方法，例如 `Dispatcher::invokeMethod()`、`Dispatcher::execute()` 等，您需要更新代码，不要直接调用这些方法。`Dispatcher` 已转换为更面向对象的形式，以便更容易使用依赖注入容器。如果您需要以类似于 Dispatcher 的方式调用方法，您可以手动使用类似 `$result = $class->$method(...$params);` 或 `call_user_func_array()` 的方式。

## `halt()` `stop()` `redirect()` 和 `error()` 更改

_v3.10.0_

3.10.0 之前的默认行为是清除标头和响应主体。这已更改为仅清除响应主体。如果您还需要清除标头，可以使用 `Flight::response()->clear()`。