# 路由中间件

Flight 支持路由和组路由中间件。中间件是一个函数，在路由回调之前（或之后）执行。这是一种在代码中添加 API 身份验证检查的好方法，或者验证用户是否有权限访问路由。

## 基本中间件

这是一个基本示例：

```php
// 如果您只提供一个匿名函数，它将在路由回调之前执行。
// 除了类（见下文）外，没有“after”中间件函数
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// 这将输出 “Middleware first! Here I am!”
```

在使用中间件之前，您应该注意一些非常重要的注意事项：
- 中间件函数按照添加到路由的顺序执行。执行方式类似于 [Slim Framework 处理方式](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work)。
   - Befores 按照添加顺序执行，而 Afters 按照逆序执行。
- 如果您的中间件函数返回 false，则所有执行将停止，并抛出 403 禁止错误。您可能希望更优雅地处理这方面，例如使用 `Flight::redirect()` 或类似方法。
- 如果您需要从路由获取参数，它们将作为单个数组传递给中间件函数。(`function($params) { ... }` 或 `public function before($params) {}`)。这样做的原因是，您可以将参数结构化为组，在某些组中，参数可能以不同的顺序出现，这会破坏中间件函数通过位置引用参数的方式。这样，您可以按名称而不是位置访问它们。
- 如果您只传递中间件名称，它将通过 [依赖注入容器](dependency-injection-container) 自动执行，并使用所需参数执行中间件。如果您没有注册依赖注入容器，它将向 `__construct()` 传递 `flight\Engine` 实例。

## 中间件类

中间件也可以作为类注册。如果您需要“after”功能，您**必须**使用类。

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
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware($MyMiddleware); // 也可以 ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// 这将显示 “Middleware first! Here I am! Middleware last!”
```

## 处理中间件错误

假设您有一个身份验证中间件，并且希望如果用户未认证，则将他们重定向到登录页面。您有几个可用的选项：

1. 您可以从中间件函数返回 false，Flight 将自动返回 403 禁止错误，但没有自定义选项。
1. 您可以使用 `Flight::redirect()` 将用户重定向到登录页面。
1. 您可以在中间件中创建自定义错误并停止路由执行。

### 基本示例

这是一个简单的 return false; 示例：
```php
class MyMiddleware {
	public function before($params) {
		if (isset($_SESSION['user']) === false) {
			return false;
		}

		// 由于它是 true，因此一切将继续
	}
}
```

### 重定向示例

这是一个将用户重定向到登录页面的示例：
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

假设您需要抛出一个 JSON 错误，因为您正在构建一个 API。您可以这样操作：
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->headers['Authorization'];
		if(empty($authorization)) {
			Flight::jsonHalt(['error' => 'You must be logged in to access this page.'], 403);
			// 或
			Flight::json(['error' => 'You must be logged in to access this page.'], 403);
			exit;
			// 或
			Flight::halt(403, json_encode(['error' => 'You must be logged in to access this page.']));
		}
	}
}
```

## 分组中间件

您可以添加一个路由组，然后该组中的每个路由都将具有相同的中间件。这在需要按例如 Auth 中间件对一组路由进行分组（以检查标头中的 API 密钥）时非常有用。

```php
// 添加在 group 方法的末尾
Flight::group('/api', function() {

	// 这个“空”路由实际上会匹配 /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// 这将匹配 /api/users
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// 这将匹配 /api/users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

如果您想为所有路由应用全局中间件，可以添加一个“空”组：

```php
// 添加在 group 方法的末尾
Flight::group('', function() {

	// 这仍然是 /users
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// 这仍然是 /users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ ApiAuthMiddleware::class ]); // 或 [ new ApiAuthMiddleware() ]，效果相同
```