```zh
# 路由中间件

Flight支持路由和路由组中间件。中间件是在路由回调之前（或之后）执行的函数。这是一个很好的方法，在您的代码中添加API身份验证检查，或验证用户是否有权限访问路由。

## 基本中间件

以下是一个基本示例:

```php
// 如果您只提供一个匿名函数，它将在路由回调之前执行。
// 除了类之外，没有“后置”中间件函数（请参见下文）
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo '中间件优先!';
});

Flight::start();

// 这将输出“中间件优先! Here I am!”
```

在您使用中间件之前，有一些非常重要的注意事项，您应该知道:
- 中间件函数按照它们被添加到路由的顺序执行。执行类似于[Slim框架如何处理此事](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work)。
   - 前置中间件按照添加顺序执行，后置中间件按照相反顺序执行。
- 如果您的中间件函数返回false，则所有执行将停止，并抛出403禁止错误。您可能希望使用`Flight::redirect()`或类似的东西更优雅地处理这个问题。
- 如果您需要您路由中的参数，它们将被传递到您的中间件函数的一个数组中。 (`function($params) { ... }` 或 `public function before($params) {}`)。之所以这样做是您可以将参数结构化为组，并且在其中一些组中，您的参数实际上可能以不同的顺序显示，这将通过按错误参数来打破中间件函数。这样一来，您可以通过名称而不是位置访问它们。

## 中间件类

中间件也可以注册为一个类。如果您需要“后置”功能，您**必须**使用类。

```php
class MyMiddleware {
	public function before($params) {
		echo '中间件优先!';
	}

	public function after($params) {
		echo '中间件最后!';
	}
}

$MyMiddleware = new MyMiddleware();
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware($MyMiddleware); // 也可以 ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// 这将显示“中间件优先! Here I am! 中间件最后!”
```

## 分组中间件

您可以添加一个路由组，然后该组中的每个路由也将具有相同的中间件。如果您需要通过身份验证中间件检查标头中的API密钥来分组一堆路由，这将非常有用。

```php

// 添加到组方法的结尾
Flight::group('/api', function() {

	// 这个“空白”路由实际上将匹配/api
	Flight::route('', function() { echo 'api'; }, false, 'api');
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

如果您想要将全局中间件应用于所有路由，您可以添加一个“空白”组:

```php

// 添加到组方法的结尾
Flight::group('', function() {
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```