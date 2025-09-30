# 中间件

## 概述

Flight 支持路由和组路由中间件。中间件是您应用程序的一部分，在路由回调执行之前（或之后）执行代码。这是一种在代码中添加 API 认证检查或验证用户是否有权限访问路由的绝佳方式。

## 理解

中间件可以大大简化您的应用程序。与复杂的抽象类继承或方法覆盖相比，中间件允许您通过为它们分配自定义应用程序逻辑来控制路由。您可以将中间件想象成三明治。外面是面包，然后是层层叠加的配料，如生菜、西红柿、肉类和奶酪。然后想象每个请求就像咬一口三明治，您先吃外层，然后逐步深入核心。

以下是中间件工作原理的视觉示意。然后我们将向您展示一个实际示例来说明其功能。

```text
用户请求 URL /api ----> 
	Middleware->before() 执行 ----->
		附加到 /api 的可调用方法/函数执行并生成响应 ------>
	Middleware->after() 执行 ----->
用户从服务器接收响应
```

这是一个实际示例：

```text
用户导航到 URL /dashboard
	LoggedInMiddleware->before() 执行
		before() 检查有效的登录会话
			如果是，则什么都不做并继续执行
			如果否，则将用户重定向到 /login
				附加到 /api 的可调用方法/函数执行并生成响应
	LoggedInMiddleware->after() 没有定义任何内容，因此让执行继续
用户从服务器接收仪表板 HTML
```

### 执行顺序

中间件函数按照添加到路由的顺序执行。执行方式类似于 [Slim Framework 处理此问题](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work)。

`before()` 方法按照添加顺序执行，而 `after()` 方法则按照逆序执行。

示例：Middleware1->before()、Middleware2->before()、Middleware2->after()、Middleware1->after()。

## 基本用法

您可以将中间件用作任何可调用方法，包括匿名函数或类（推荐）。

### 匿名函数

这是一个简单示例：

```php
Flight::route('/path', function() { echo ' Here I am!'; })->addMiddleware(function() {
	echo 'Middleware first!';
});

Flight::start();

// 这将输出 "Middleware first! Here I am!"
```

> **注意：** 使用匿名函数时，只有 `before()` 方法会被解释。您**不能**使用匿名类定义 `after()` 行为。

### 使用类

中间件可以（并且应该）注册为类。如果您需要“after”功能，则**必须**使用类。

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
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware($MyMiddleware); 
// 也可以 ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// 这将显示 "Middleware first! Here I am! Middleware last!"
```

您也可以只需定义中间件类名，它将实例化该类。

```php
Flight::route('/path', function() { echo ' Here I am! '; })->addMiddleware(MyMiddleware::class); 
```

> **注意：** 如果您仅传入中间件名称，它将自动由 [依赖注入容器](dependency-injection-container) 执行，并且中间件将使用它需要的参数执行。如果您没有注册依赖注入容器，它将默认将 `flight\Engine` 实例传入 `__construct(Engine $app)`。

### 使用带参数的路由

如果您需要路由中的参数，它们将以单个数组形式传递给您的中间件函数。（`function($params) { ... }` 或 `public function before($params) { ... }`）。这样做的原因是，您可以将参数结构化为组，在某些组中，您的参数可能以不同的顺序出现，这会导致通过引用错误参数而破坏中间件函数。通过这种方式，您可以通过名称而不是位置访问它们。

```php
use flight\Engine;

class RouteSecurityMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$clientId = $params['clientId'];

		// jobId 可能传入也可能不传入
		$jobId = $params['jobId'] ?? 0;

		// 也许如果没有作业 ID，您就不需要查找任何内容。
		if($jobId === 0) {
			return;
		}

		// 在您的数据库中执行某种查找
		$isValid = !!$this->app->db()->fetchField("SELECT 1 FROM client_jobs WHERE client_id = ? AND job_id = ?", [ $clientId, $jobId ]);

		if($isValid !== true) {
			$this->app->halt(400, 'You are blocked, muahahaha!');
		}
	}
}

// routes.php
$router->group('/client/@clientId/job/@jobId', function(Router $router) {

	// 下面的组仍然获取父中间件
	// 但参数以单个数组形式传递到中间件中。
	$router->group('/job/@jobId', function(Router $router) {
		$router->get('', [ JobController::class, 'view' ]);
		$router->put('', [ JobController::class, 'update' ]);
		$router->delete('', [ JobController::class, 'delete' ]);
		// 更多路由...
	});
}, [ RouteSecurityMiddleware::class ]);
```

### 使用中间件分组路由

您可以添加一个路由组，然后该组中的每个路由都将具有相同的中间件。如果您需要按 Auth 中间件分组一堆路由来检查标头中的 API 密钥，这将非常有用。

```php

// 添加到 group 方法的末尾
Flight::group('/api', function() {

	// 这个“空”路由实际上匹配 /api
	Flight::route('', function() { echo 'api'; }, false, 'api');
	// 这将匹配 /api/users
    Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// 这将匹配 /api/users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```

如果您想将全局中间件应用到所有路由，可以添加一个“空”组：

```php

// 添加到 group 方法的末尾
Flight::group('', function() {

	// 这仍然是 /users
	Flight::route('/users', function() { echo 'users'; }, false, 'users');
	// 这仍然是 /users/1234
	Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
}, [ ApiAuthMiddleware::class ]); // 或 [ new ApiAuthMiddleware() ]，效果相同
```

### 常见用例

#### API 密钥验证
如果您想通过验证 API 密钥是否正确来保护您的 `/api` 路由，您可以轻松使用中间件处理。

```php
use flight\Engine;

class ApiMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}
	
	public function before(array $params) {
		$authorizationHeader = $this->app->request()->getHeader('Authorization');
		$apiKey = str_replace('Bearer ', '', $authorizationHeader);

		// 在您的数据库中查找 API 密钥
		$apiKeyHash = hash('sha256', $apiKey);
		$hasValidApiKey = !!$this->db()->fetchField("SELECT 1 FROM api_keys WHERE hash = ? AND valid_date >= NOW()", [ $apiKeyHash ]);

		if($hasValidApiKey !== true) {
			$this->app->jsonHalt(['error' => 'Invalid API Key']);
		}
	}
}

// routes.php
$router->group('/api', function(Router $router) {
	$router->get('/users', [ ApiController::class, 'getUsers' ]);
	$router->get('/companies', [ ApiController::class, 'getCompanies' ]);
	// 更多路由...
}, [ ApiMiddleware::class ]);
```

现在您的所有 API 路由都受到您设置的 API 密钥验证中间件的保护！如果您将更多路由放入路由器组中，它们将立即获得相同的保护！

#### 登录验证

您想保护某些路由仅供已登录用户使用吗？这可以通过中间件轻松实现！

```php
use flight\Engine;

class LoggedInMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$session = $this->app->session();
		if($session->get('logged_in') !== true) {
			$this->app->redirect('/login');
			exit;
		}
	}
}

// routes.php
$router->group('/admin', function(Router $router) {
	$router->get('/dashboard', [ DashboardController::class, 'index' ]);
	$router->get('/clients', [ ClientController::class, 'index' ]);
	// 更多路由...
}, [ LoggedInMiddleware::class ]);
```

#### 路由参数验证

您想保护用户免受在 URL 中更改值以访问他们不应访问的数据的影响吗？这可以通过中间件解决！

```php
use flight\Engine;

class RouteSecurityMiddleware {

	protected Engine $app;

	public function __construct(Engine $app) {
		$this->app = $app;
	}

	public function before(array $params) {
		$clientId = $params['clientId'];
		$jobId = $params['jobId'];

		// 在您的数据库中执行某种查找
		$isValid = !!$this->app->db()->fetchField("SELECT 1 FROM client_jobs WHERE client_id = ? AND job_id = ?", [ $clientId, $jobId ]);

		if($isValid !== true) {
			$this->app->halt(400, 'You are blocked, muahahaha!');
		}
	}
}

// routes.php
$router->group('/client/@clientId/job/@jobId', function(Router $router) {
	$router->get('', [ JobController::class, 'view' ]);
	$router->put('', [ JobController::class, 'update' ]);
	$router->delete('', [ JobController::class, 'delete' ]);
	// 更多路由...
}, [ RouteSecurityMiddleware::class ]);
```

## 处理中间件执行

假设您有一个认证中间件，并且想在用户未认证时将他们重定向到登录页面。您有几个选项可用：

1. 您可以从中间件函数返回 false，Flight 将自动返回 403 禁止错误，但没有自定义。
1. 您可以使用 `Flight::redirect()` 将用户重定向到登录页面。
1. 您可以在中间件中创建自定义错误并停止路由执行。

### 简单直接

这是一个简单的 `return false;` 示例：

```php
class MyMiddleware {
	public function before($params) {
		$hasUserKey = Flight::session()->exists('user');
		if ($hasUserKey === false) {
			return false;
		}

		// 因为它是 true，一切继续进行
	}
}
```

### 重定向示例

这是一个将用户重定向到登录页面的示例：
```php
class MyMiddleware {
	public function before($params) {
		$hasUserKey = Flight::session()->exists('user');
		if ($hasUserKey === false) {
			Flight::redirect('/login');
			exit;
		}
	}
}
```

### 自定义错误示例

假设您需要抛出 JSON 错误，因为您正在构建 API。您可以这样实现：
```php
class MyMiddleware {
	public function before($params) {
		$authorization = Flight::request()->getHeader('Authorization');
		if(empty($authorization)) {
			Flight::jsonHalt(['error' => 'You must be logged in to access this page.'], 403);
			// 或
			Flight::json(['error' => 'You must be logged in to access this page.'], 403);
			exit;
			// 或
			Flight::halt(403, json_encode(['error' => 'You must be logged in to access this page.']);
		}
	}
}
```

## 另请参阅
- [Routing](/learn/routing) - 如何将路由映射到控制器并渲染视图。
- [Requests](/learn/requests) - 理解如何处理传入请求。
- [Responses](/learn/responses) - 如何自定义 HTTP 响应。
- [Dependency Injection](/learn/dependency-injection-container) - 在路由中简化对象创建和管理。
- [Why a Framework?](/learn/why-frameworks) - 理解使用像 Flight 这样的框架的好处。
- [Middleware Execution Strategy Example](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work)

## 故障排除
- 如果您的中间件中有重定向，但您的应用程序似乎没有重定向，请确保在中间件中添加 `exit;` 语句。

## 更新日志
- v3.1: 添加了对中间件的支持。