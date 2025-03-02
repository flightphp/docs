# 安全

安全在网页应用程序中至关重要。您希望确保您的应用程序是安全的，并且您的用户数据是安全的。`Flight` 提供了许多功能来帮助您保护您的网页应用程序。

## 标头

HTTP 标头是保护您的网页应用程序最简单的方法之一。您可以使用标头来防止点击劫持、XSS 和其他攻击。您可以通过多种方式将这些标头添加到您的应用程序中。

检查您的标头安全性的两个很棒的网站分别是 [securityheaders.com](https://securityheaders.com/) 和 [observatory.mozilla.org](https://observatory.mozilla.org/)。

### 手动添加

您可以通过在 `Flight\Response` 对象上使用 `header` 方法手动添加这些标头。
```php
// 设置 X-Frame-Options 标头以防止点击劫持
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// 设置内容安全策略标头以防止 XSS
// 注意：这个标头可能变得非常复杂，所以您可能需要在互联网上查找应用于您应用程序的示例
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// 设置 X-XSS-Protection 标头以防止 XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// 设置 X-Content-Type-Options 标头以防止 MIME 嗅探
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// 设置引用者策略标头以控制发送多少引用信息
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// 设置严格传输安全标头以强制使用 HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// 设置权限策略标头以控制可使用的功能和 API
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

这些可以添加到您的 `bootstrap.php` 或 `index.php` 文件的顶部。

### 作为过滤器添加

您也可以将它们添加到过滤器/钩子中，如下所示：

```php
// 在过滤器中添加标头
Flight::before('start', function() {
	Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');
	Flight::response()->header("Content-Security-Policy", "default-src 'self'");
	Flight::response()->header('X-XSS-Protection', '1; mode=block');
	Flight::response()->header('X-Content-Type-Options', 'nosniff');
	Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
	Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
	Flight::response()->header('Permissions-Policy', 'geolocation=()');
});
```

### 作为中间件添加

您也可以将它们作为中间件类添加。这是保持您的代码清洁和有组织的好方法。

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
		Flight::response()->header('Permissions-Policy', 'geolocation=()');
	}
}

// index.php 或者您设置路由的任何地方
// FYI，这个空字符串组充当全局中间件以
// 适用于所有路由。当然，您也可以做同样的事情，并且只需将
// 这些内容添加到特定路由。
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// 更多路由
}, [ new SecurityHeadersMiddleware() ]);
```


## 跨站请求伪造 (CSRF)

跨站请求伪造 (CSRF) 是一种攻击类型，恶意网站可以让用户的浏览器向您的网站发送请求。这可以用来在用户不知情的情况下在您的网站上执行操作。`Flight` 不提供内置的 CSRF 保护机制，但您可以通过使用中间件轻松实现自己的保护。

### 设置

首先，您需要生成一个 CSRF 令牌并将其存储在用户的会话中。然后您可以在表单中使用此令牌，并在提交表单时进行检查。

```php
// 生成一个 CSRF 令牌并将其存储在用户的会话中
// (假设您已经创建了一个会话对象并将其附加到 Flight)
// 有关更多信息，请参阅会话文档
Flight::register('session', \Ghostff\Session\Session::class);

// 每个会话只需要生成一个令牌（这样可以跨多个标签页和请求为同一个用户工作）
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

```html
<!-- 在您的表单中使用 CSRF 令牌 -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- 其他表单字段 -->
</form>
```

#### 使用 Latte

您还可以在 Latte 模板中设置一个自定义函数来输出 CSRF 令牌。

```php
// 设置一个自定义函数以输出 CSRF 令牌
// 注意：已经配置了 View 以 Latte 作为视图引擎
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

现在在您的 Latte 模板中，您可以使用 `csrf()` 函数来输出 CSRF 令牌。

```html
<form method="post">
	{csrf()}
	<!-- 其他表单字段 -->
</form>
```

简短而简单是吧？

### 检查 CSRF 令牌

您可以使用事件过滤器检查 CSRF 令牌：

```php
// 此中间件检查请求是否是 POST 请求，如果是，检查 CSRF 令牌是否有效
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// 从表单数据中获取csrf令牌
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Invalid CSRF token');
			// 或者用于 JSON 响应
			Flight::jsonHalt(['error' => 'Invalid CSRF token'], 403);
		}
	}
});
```

或者您可以使用一个中间件类：

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

// index.php 或者您设置路由的任何地方
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// 更多路由
}, [ new CsrfMiddleware() ]);
```


## 跨站脚本攻击 (XSS)

跨站脚本攻击 (XSS) 是一种攻击类型，恶意网站可以向您的网站注入代码。这些机会大多来自您的最终用户填写的表单值。您**绝不能**信任用户的输出！永远假设他们是世界上最好的黑客。他们可以向您的页面注入恶意 JavaScript 或 HTML。这段代码可以用来从您的用户那里窃取信息或在您的网站上执行操作。使用 `Flight` 的视图类，您可以轻松转义输出以防止 XSS 攻击。

```php
// 假设用户很聪明并尝试将此用作他们的名字
$name = '<script>alert("XSS")</script>';

// 这将转义输出
Flight::view()->set('name', $name);
// 这将输出：&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// 如果您使用像 Latte 注册为您的视图类，它也会自动转义这个。
Flight::view()->render('模板', ['name' => $name]);
```

## SQL 注入

SQL 注入是一种攻击类型，恶意用户可以向您的数据库注入 SQL 代码。这可以用来从您的数据库中窃取信息或在您的数据库上执行操作。再次强调，您**绝不能**信任用户的输入！永远假设他们是为了流血而来。您可以在您的 `PDO` 对象中使用预处理语句来防止 SQL 注入。

```php
// 假设您已经将 Flight::db() 注册为您的 PDO 对象
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// 如果您使用 PdoWrapper 类，这可以很容易地在一行中完成
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// 您也可以在含有 ? 占位符的 PDO 对象中执行相同的操作
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// 只是保证您永远不要像这样做...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// 因为如果 $username = "' OR 1=1; -- ";
// 在查询生成后看起来像这样
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// 看起来很奇怪，但它是一个有效的查询，会起作用。事实上，
// 这是一个非常常见的 SQL 注入攻击，将返回所有用户。
```

## 跨源资源共享 (CORS)

跨源资源共享 (CORS) 是一种允许网页中的许多资源（例如字体、JavaScript 等）从另一个域请求的机制。`Flight` 没有内置的功能, 但可以通过在 `Flight::start()` 方法调用之前运行一个钩子来轻松处理这个功能。

```php
// app/utils/CorsUtil.php

namespace app\utils;

class CorsUtil
{
	public function set(array $params): void
	{
		$request = Flight::request();
		$response = Flight::response();
		if ($request->getVar('HTTP_ORIGIN') !== '') {
			$this->allowOrigins();
			$response->header('Access-Control-Allow-Credentials', 'true');
			$response->header('Access-Control-Max-Age', '86400');
		}

		if ($request->method === 'OPTIONS') {
			if ($request->getVar('HTTP_ACCESS_CONTROL_REQUEST_METHOD') !== '') {
				$response->header(
					'Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD'
				);
			}
			if ($request->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS') !== '') {
				$response->header(
					"Access-Control-Allow-Headers",
					$request->getVar('HTTP_ACCESS_CONTROL_REQUEST_HEADERS')
				);
			}

			$response->status(200);
			$response->send();
			exit;
		}
	}

	private function allowOrigins(): void
	{
		// 在这里自定义您允许的主机。
		$allowed = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		$request = Flight::request();

		if (in_array($request->getVar('HTTP_ORIGIN'), $allowed, true) === true) {
			$response = Flight::response();
			$response->header("Access-Control-Allow-Origin", $request->getVar('HTTP_ORIGIN'));
		}
	}
}

// index.php 或者您设置路由的任何地方
$CorsUtil = new CorsUtil();

// 这需要在 start 方法之前运行。
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

## 结论

安全性至关重要，确保您的网页应用程序是安全的非常重要。`Flight` 提供了许多功能来帮助您保护您的网页应用程序，但始终保持警惕并确保尽一切可能保护用户数据的安全性非常重要。始终假设最坏的情况，并且永远不要相信用户的输入。始终转义输出并使用准备语句来预防 SQL 注入。始终使用中间件保护您的路由免受 CSRF 和 CORS 攻击。如果您做了所有这些事情，那么构建安全的网页应用程序的道路将会变得更加平稳。