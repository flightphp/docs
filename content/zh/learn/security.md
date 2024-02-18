# 安全

在Web应用程序中，安全性是一件大事。您希望确保您的应用程序是安全的，用户的数据是安全的。Flight提供了许多功能来帮助您保护您的Web应用程序。

## 头信息

HTTP标头是保护Web应用程序的最简单方式之一。您可以使用标头来防止点击劫持、跨站脚本（XSS）和其他攻击。您可以通过几种方式将这些标头添加到您的应用程序中。

### 手动添加

您可以通过在`Flight\Response`对象上使用 `header` 方法手动添加这些标头。
```php
// 设置X-Frame-Options标头以防止点击劫持
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// 设置Content-Security-Policy标头以防止跨站脚本攻击
// 注：此标头可能非常复杂，请查看互联网上的示例以供参考
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// 设置X-XSS-Protection标头以防止跨站脚本攻击
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// 设置X-Content-Type-Options标头以防止MIME嗅探
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// 设置Referrer-Policy标头以控制发送的引荐信息量
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// 设置Strict-Transport-Security标头以强制使用HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
```

这些可以添加在您的`bootstrap.php`或`index.php`文件的顶部。

### 作为过滤器添加

您也可以将它们添加在一个过滤器/钩子中，如下所示：

```php
// 在过滤器中添加标头
Flight::before('start', function() {
	Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
	Flight::response()->header("Content-Security-Policy", "default-src 'self'");
	Flight::response()->header('X-XSS-Protection', '1; mode=block');
	Flight::response()->header('X-Content-Type-Options', 'nosniff');
	Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
	Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
});
```

### 作为中间件添加

您也可以将它们作为中间件类添加。这是保持代码清晰和有条理的好方法。

```php
// app/middleware/SecurityHeadersMiddleware.php

namespace app\middleware;

class SecurityHeadersMiddleware
{
	public function before(array $params): void
	{
		Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
		Flight::response()->header("Content-Security-Policy", "default-src 'self'");
		Flight::response()->header('X-XSS-Protection', '1; mode=block');
		Flight::response()->header('X-Content-Type-Options', 'nosniff');
		Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
		Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
	}
}

// index.php或您放置路由的任何地方
// 顺便说一下，这个空的字符串组充当全局中间件以应用于
// 所有路由。当然，您也可以做同样的事情，只需将其添加
// 到特定路由。
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// 更多路由
}, [ new SecurityHeadersMiddleware() ]);
```


## 跨站请求伪造（CSRF）

跨站请求伪造（CSRF）是一种攻击类型，恶意网站可以使用户的浏览器向您的网站发送请求。这可以被用于在用户不知情的情况下在您的网站上执行操作。Flight不提供内置的CSRF保护机制，但您可以通过使用中间件轻松实现自己的保护机制。

### 设置

首先，您需要生成一个CSRF令牌并将其存储在用户会话中。然后，您可以在表单中使用此令牌，并在提交表单时进行检查。

```php
// 生成一个CSRF令牌并将其存储在用户会话中
// （假设您已经创建了一个会话对象并将其附加到Flight）
// 您只需要为每个会话生成一个令牌（以便它可以跨多个标签页和请求为同一用户工作）
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- 在您的表单中使用CSRF令牌 -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- 其他表单字段 -->
</form>
```

#### 使用Latte

您还可以设置一个自定义函数以在您的Latte模板中输出CSRF令牌。

```php
// 设置一个自定义函数以输出CSRF令牌
// 注意：View已配置为将Latte作为视图引擎
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

现在，在您的Latte模板中，您可以使用 `csrf()` 函数来输出CSRF令牌。

```html
<form method="post">
	{csrf()}
	<!-- 其他表单字段 -->
</form>
```

简短而简单，对吧？

### 检查CSRF令牌

您可以使用事件过滤器检查CSRF令牌：

```php
// 此中间件检查请求是否为POST请求，如果是，则检查CSRF令牌是否有效
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// 从表单值中获取csrf令牌
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Invalid CSRF token');
		}
	}
});
```

或者，您可以使用中间件类：

```php
// app/middleware/CsrfMiddleware.php

namespace app\middleware;

class CsrfMiddleware
{
	public function before(array $params): void
	{
		if(Flight::request()->method == 'POST') {
			$token = Flight::request()->data->csrf_token;
			if($token !== Flight::session()->get('csrf_token')) {
				Flight::halt(403, 'Invalid CSRF token');
			}
		}
	}
}

// index.php或您放置路由的任何地方
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// 更多路由
}, [ new CsrfMiddleware() ]);
```


## 跨站脚本（XSS）

跨站脚本（XSS）是一种攻击类型，恶意网站可以向您的网站注入代码。大多数机会来自您的最终用户填写的表单值。您**永远不应**信任用户的输出！始终假定他们都是世界上最厉害的黑客。他们可以将恶意JavaScript或HTML注入到您的页面中。此代码可用于从用户那里窃取信息或在您的网站上执行操作。使用Flight的视图类，您可以轻松转义输出以防止XSS攻击。

```php
// 假设用户很聪明，尝试将此作为其名称
$name = '<script>alert("XSS")</script>';

// 这将转义输出
Flight::view()->set('name', $name);
// 这将输出：&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// 如果您使用像Latte这样被注册为视图类，它也将自动转义此内容。
Flight::view()->render('template', ['name' => $name]);
```

## SQL注入

SQL注入是一种攻击类型，恶意用户可以将SQL代码注入到您的数据库中。这可以用于从数据库中窃取信息或在数据库上执行操作。同样，您**永远不应**相信用户的输入！始终假定他们是来找麻烦的。您可以在`PDO`对象中使用预处理语句来防止SQL注入。

```php
// 假设您已注册Flight::db()为您的PDO对象
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// 如果您使用了PdoWrapper类，则可以轻松地在一行中执行此操作
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// 您还可以使用带有？占位符的PDO对象执行相同操作
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// 只是承诺您永远不要像这样做...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// 因为如果 $username = "' OR 1=1; -- "; 
// 查询构建后看起来像这样
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// 看起来很奇怪，但这是一个有效的查询，将起作用。实际上，
// 这是一个非常常见的SQL注入攻击，将返回所有用户。
```

## 跨域资源共享（CORS）

跨域资源共享（CORS）是一种允许在网页上请求来自源站之外的许多资源（例如字体、JavaScript等）的机制。Flight没有内置功能，但是可以通过类似于CSRF的中间件或事件过滤器轻松处理这个功能。

```php
// app/middleware/CorsMiddleware.php

namespace app\middleware;

class CorsMiddleware
{
	public function before(array $params): void
	{
		$response = Flight::response();
		if (isset($_SERVER['HTTP_ORIGIN'])) {
			$this->allowOrigins();
			$response->header('Access-Control-Allow-Credentials: true');
			$response->header('Access-Control-Max-Age: 86400');
		}

		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
				$response->header(
					'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS'
				);
			}
			if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
				$response->header(
					"Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}"
				);
			}
			$response->send();
			exit(0);
		}
	}

	private function allowOrigins(): void
	{
		// 在此定义您允许的主机。
		$allowed = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		if (in_array($_SERVER['HTTP_ORIGIN'], $allowed)) {
			$response = Flight::response();
			$response->header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		}
	}
}

// index.php或您放置路由的任何地方
Flight::route('/users', function() {
	$users = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json($users);
})->addMiddleware(new CorsMiddleware());
```


## 结论

安全性是很重要的，确保您的Web应用程序是安全的是至关重要的。Flight提供了许多功能来帮助您保护您的Web应用程序，但重要的是要始终保持警惕，确保您尽力保护用户的数据安全。始终设想最坏的情况，并且永远不要信任用户的输入。始终转义输出并使用预处理语句来防止SQL注入。始终使用中间件来保护您的路由免受CSRF和CORS攻击。如果您执行所有这些操作，您将在打造安全的Web应用程序的道路上走得很顺利。