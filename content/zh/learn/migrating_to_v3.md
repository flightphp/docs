# 迁移到 v3

大部分情况下向后兼容性已经得到保持，但在从 v2 迁移到 v3 时有一些变化需要您注意。

## 输出缓冲

[输出缓冲](https://stackoverflow.com/questions/2832010/what-is-output-buffering-in-php) 是指 PHP 脚本生成的输出被存储在一个缓冲区（PHP 内部）中，然后再发送到客户端的过程。这样可以允许您在发送到客户端之前修改输出内容。

在 MVC 应用程序中，控制器是“管理者”，它负责管理视图的操作。在控制器之外生成输出（或在 Flight 中有时是匿名函数）会破坏 MVC 模式。这一变化是为了更符合 MVC 模式，并使框架更可预测和易于使用。

在 v2 中，输出缓冲的处理方式并非一直关闭自己的输出缓冲，这导致[单元测试](https://github.com/flightphp/core/pull/545/files#diff-eb93da0a3473574fba94c3c4160ce68e20028e30b267875ab0792ade0b0539a0R42)和[流式处理](https://github.com/flightphp/core/issues/413)更加困难。对于大多数用户，这种变化实际上可能不会影响您。但是，如果您在可调用函数和控制器之外（例如在钩子中）输出内容，那么您可能会遇到问题。在钩子中输出内容，并在框架实际执行之前可能有效，但在未来可能会出现问题。

### 您可能遇到问题的地方
```php
// index.php
require 'vendor/autoload.php';

// 仅作为示例
define('START_TIME', microtime(true));

function hello() {
	echo '你好，世界';
}

Flight::map('hello', 'hello');
Flight::after('hello', function(){
	// 这实际上没问题
	echo '<p>这句“你好，世界”是由字母“H”呈现的</p>';
});

Flight::before('start', function(){
	// 类似这样的内容会导致错误
	echo '<html><head><title>我的页面</title></head><body>';
});

Flight::route('/', function(){
	// 这是完全没问题的
	echo '你好，世界';

	// 这也应该没问题
	Flight::hello();
});

Flight::after('start', function(){
	// 这将导致错误
	echo '<div>你的页面在 '.(microtime(true) - START_TIME).' 秒内加载完成</div></body></html>';
});
```

### 启用 v2 渲染行为

您是否仍然可以保持旧代码不进行重写以使其与 v3 兼容？是的，您可以！您可以通过将 `flight.v2.output_buffering` 配置选项设置为 `true` 来启用 v2 渲染行为。这将允许您继续使用旧的渲染行为，但建议您在未来修复它。 在框架的 v4 中，这将被移除。

```php
// index.php
require 'vendor/autoload.php';

Flight::set('flight.v2.output_buffering', true);

Flight::before('start', function(){
	// 现在这将是没问题的
	echo '<html><head><title>我的页面</title></head><body>';
});

// 更多代码
```