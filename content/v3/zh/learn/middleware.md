## 路由中间件

Flight支持路由和路由组中间件。中间件是在路由回调之前（或之后）执行的函数。这是在代码中添加API身份验证检查的绝佳方式，或者验证用户是否有权限访问路由。

## 基本中间件

这是一个基本示例：

```php
// 如果只提供匿名函数，则将在路由回调之前执行。除类之外，没有“after”中间件函数（见下文）
Flight::route('/path', function() { echo 'Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// 这将输出“Middleware first! Here I am!”
```

在您使用中间件之前，请务必了解一些非常重要的内容：
- 中间件函数按添加到路由的顺序执行。执行方式类似于[Slim Framework处理此问题的方式](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work)。
   - before按添加顺序执行，after按相反顺序执行。
- 如果您的中间件函数返回false，则所有执行都将停止，并引发403 Forbidden错误。您可能希望通过`Flight::redirect()`或类似方法更加优雅地处理这种情况。
- 如果您需要从路由获取参数，它们将作为单个数组传递给您的中间件函数（`function($params) { ... }`或`public function before($params) {}`）。原因在于您可以将参数结构化为组，并在其中的某些组中，您的参数实际上可能以不同顺序显示，这将破坏中间件函数，因为引用错误的参数。通过这种方式，您可以按名称而不是位置访问它们。
- 如果只传入中间件的名称，它将自动由[依赖注入容器](dependency-injection-container)执行，并且中间件将以其需要的参数执行。如果您尚未注册依赖注入容器，则将`flight\Engine`实例传递给`__construct()`。

## 中间件类

中间件也可以注册为类。如果需要“after”功能，**必须**使用类。

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
Flight::route('/path', function() { echo 'Here I am! '; })->addMiddleware($MyMiddleware); // 也可以->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// 这将显示“Middleware first! Here I am! Middleware last!”
```

## 处理中间件错误

假设您有一个授权中间件，并希望如果用户没有经过身份验证，则将用户重定向到登录页面。您有几个选择：

1. 您可以从中间件函数中返回false，Flight将自动返回403 Forbidden错误，但无法自定义。
1. 您可以使用`Flight::redirect()`将用户重定向到登录页面。
1. 您可以在中间件中创建自定义错误，并停止路由的执行。

### 基本示例

这是一个简单的返回false; 示例：
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			return false;
		}

		// 既然是true，一切都将继续进行
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

假设您需要抛出JSON错误，因为您正在构建一个API。您可以这样做：
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->headers['Authorization'];
		if(empty($authorization)) {
			Flight::jsonHalt(['error' => 'You must be logged in to access this page.'], 403);
			// 或者
			Flight::json(['error' => 'You must be logged in to access this page.'], 403);
			exit;
			// 或者
			Flight::halt(403, json_encode(['error' => 'You must be logged in to access this page.']);
		}
	}
}
```

## 分组中间件

您可以添加一个路由组，然后该组中的每个路由也将具有相同的中间件。如果您需要将一堆路由按照Auth中间件进行分组以检查头部中的API密钥，则这很有用。

```php

// 添加到组方法的末尾
Flight::group('/api', function() {

	// 这个“空”外观的路由实际上将匹配/api
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// 这将匹配/api/users
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// 这将匹配/api/users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

如果要对所有路由应用全局中间件，可以添加一个“空”组：

```php

// 添加到组方法的末尾
Flight::group('', function() {

	// 这仍然是/users
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// 这仍然是/users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```