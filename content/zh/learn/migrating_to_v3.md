# 迁移至 v3

在大多数情况下，向后兼容性已经得到保留，但在从 v2 迁移至 v3 时，有一些变化是您应该注意的。

## 输出缓冲

[输出缓冲](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) 是指 PHP 脚本生成的输出被存储在缓冲区（PHP 内部）中，然后再发送到客户端的过程。这允许您在发送到客户端之前修改输出。

在 MVC 应用程序中，控制器是“管理者”，它管理视图的行为。在控制器之外生成输出（或在 Flight 框架的某些情况下是匿名函数中）会破坏 MVC 模式。此更改是为了更符合 MVC 模式，并使框架更可预测和易于使用。

在 v2 中，输出缓冲是以一种不一致的方式处理的，它没有始终关闭自己的输出缓冲区，这使得 [单元测试](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42) 和 [流式处理](https://github.com/flightphp/core/issues/413) 更加困难。对于大多数用户来说，这个变化实际上可能不会影响您。但是，如果在不可调用函数和控制器之外输出内容（例如在挂钩中），您很可能会遇到问题。在过去，在挂钩中和在框架实际执行之前输出内容可能有效，但在将来不会有效。

### 您可能遇到问题的地方
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
	// 这样的内容将导致错误
	echo '<html><head><title>My Page</title></head><body>';
});

Flight::route('/', function(){
	// 这实际上没问题
	echo 'Hello World';

	// 这个也应该没问题
	Flight::hello();
});

Flight::after('start', function(){
	// 这将导致错误
	echo '<div>Your page loaded in '.(microtime(true) - START_TIME).' seconds</div></body></html>';
});
```

### 打开 v2 渲染行为

您是否仍然可以保持旧代码不进行重写以使其与 v3 兼容？是的，可以！通过将 `flight.v2.output_buffering` 配置选项设置为 `true`，可以打开 v2 渲染行为。这将允许您继续使用旧的渲染行为，但建议您在未来修复它。在框架的 v4 中，这将被移除。

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// 现在这没有问题
	echo '<html><head><title>My Page</title></head><body>';
});

// 更多代码
```