## 路由中间件

Flight支持路由和路由组中间件。中间件是在路由回调之前（或之后）执行的函数。这是在您的代码中添加API身份验证检查或验证用户是否有权限访问路由的绝佳方式。

## 基本中间件

这里有一个基本示例：

```php
// 如果只提供一个匿名函数，它将在路由回调之前执行。
// 除了类（详见下文）之外，没有“后置”中间件函数
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// 这将输出“Middleware first! Here I am!”
```

在您使用中间件之前，有一些非常重要的注意事项：
- 中间件函数按照它们添加到路由的顺序执行。执行方式类似于 [Slim Framework 处理方式](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work)。
   - 之前分别按添加顺序执行，之后以相反顺序执行。
- 如果您的中间件函数返回false，则所有执行都将停止，并抛出403 Forbidden错误。您可能希望通过 `Flight::redirect()` 或类似方式更优雅地处理这种情况。
- 如果您需要从路由获取参数，则这些参数将作为单个数组传递给您的中间件函数（`function($params) { ... }`或`public function before($params) {}`）。之所以这样做是因为您可以将参数结构化成组，并且在某些组中，您的参数实际上可能以不同的顺序出现，这可能会通过引用错误的参数破坏中间件函数。通过这种方式，您可以按名称而不是位置访问它们。

## 中间件类

中间件也可以注册为类。如果您需要“后置”功能，则**必须**使用类。

```php
class MyMiddleware {
	public function before($params) {
		echo 'Middleware first!';
	}

	public function after($params) {
		echo 'Middleware last!';
	}
}

$MyMiddleware = new MyMiddleware();
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware($MyMiddleware); // also ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// 这将显示“Middleware first! Here I am! Middleware last!”
```

## 分组中间件

您可以添加一个路由组，然后该组中的每个路由也将具有相同的中间件。如果您需要按照标头中的API密钥对一堆路由进行分组，这将很有用。

```php

// 添加到组方法的末尾
Flight::group('/api', function() {

	// 这个“空”路由实际上将匹配/api
	Flight::route('', function() { echo 'api'; }, false, 'api');
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

如果您想要将全局中间件应用于所有路由，您可以添加一个“空”组：

```php

// 添加到组方法的末尾
Flight::group('', function() {
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```