# 路由中间件

Flight支持路由和路由组中间件。中间件是在路由回调之前（或之后）执行的函数。这是一个很好的方式，在你的代码中添加API身份验证检查，或者验证用户是否有权限访问路由。

## 基本中间件

这里有一个基本示例：

```php
// 如果你只提供一个匿名函数，它将在路由回调之前执行。
// 除了类（见下文）之外没有“后置”中间件函数。
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// 这将输出“Middleware first! Here I am!”
```

在你使用中间件之前，有一些非常重要的注意事项需要注意：
- 中间件函数按照它们添加到路由的顺序执行。执行方式类似于[Slim框架处理此问题的方式](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work)。
   - 前置中间件按添加顺序执行，后置中间件按相反顺序执行。
- 如果你的中间件函数返回false，所有执行将停止，并抛出403 Forbidden错误。你可能希望以更优雅的方式处理这个问题，比如使用`Flight::redirect()`或类似的方法。
- 如果需要从你的路由获取参数，它们将作为单个数组传递给你的中间件函数。(`function($params) { ... }` 或 `public function before($params) {}`)。之所以这样做是因为你可以将参数结构化成组，并且在其中一些组中，你的参数实际上可能以不同的顺序出现，这样引用错参数将破坏中间件函数。通过这种方式，你可以通过名称而不是位置访问它们。
- 如果只传入中间件的名称，它将自动由[依赖注入容器](dependency-injection-container)执行，并且中间件将使用它所需的参数执行。如果你没有注册依赖注入容器，它将传入`flight\Engine`实例到`__construct()`。

## 中间件类

中间件也可以注册为类。如果你需要“后置”功能，你**必须**使用类。

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

## 处理中间件错误

假设你有一个auth中间件，如果用户未经身份验证，你希望将用户重定向到登录页面。你有几种选择：

1. 你可以在中间件函数中返回false，Flight将自动返回一个403 Forbidden错误，但没有自定义内容。
1. 你可以使用`Flight::redirect()`将用户重定向到登录页面。
1. 你可以在中间件中创建自定义错误，并停止路由的执行。

### 基本示例

这是一个简单的返回false的示例：
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			return false;
		}

		// 既然是true，一切都会继续
	}
}
```

### 重定向示例

这是将用户重定向到登录页面的示例：
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			Flight::redirect('/login');
			exit;
		}
	}
}
```

### 自定义错误示例

假设你需要抛出一个JSON错误，因为你正在构建一个API。你可以这样做：
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->headers['Authorization'];
		if(empty($authorization)) {
			Flight::json(['error' => 'You must be logged in to access this page.'], 403);
			exit;
			// 或者
			Flight::halt(403, json_encode(['error' => 'You must be logged in to access this page.']);
		}
	}
}
```

## 分组中间件

你可以添加一个路由组，然后该组中的每个路由都将具有相同的中间件。如果你需要根据标头中的API密钥对一组路由进行分组，这将是有用的。

```php

// 添加到group方法的末尾
Flight::group('/api', function() {

	// 这个“空”路由将实际匹配/api
	Flight::route('', function() { echo 'api'; }, false, 'api');
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

如果你想对所有路由应用全局中间件，你可以添加一个“空”的组：

```php

// 添加到group方法的末尾
Flight::group('', function() {
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```