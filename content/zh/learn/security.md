# 安全

在涉及 Web 应用程序时，安全性至关重要。您希望确保您的应用程序是安全的，用户的数据是安全的。Flight 提供了许多功能来帮助您保护您的 Web 应用程序。

## 头部

HTTP 头是保护您的 Web 应用程序的最简单方法之一。您可以使用头部来防止点击劫持、XSS 和其他攻击。您可以通过几种方式将这些头部添加到应用程序中。

用于检查头部安全性的两个很棒的网站是 [securityheaders.com](https://securityheaders.com/) 和 [observatory.mozilla.org](https://observatory.mozilla.org/)。

### 手动添加

您可以通过在 `Flight\Response` 对象上使用 `header` 方法手动添加这些头部。
```php
// 设置 X-Frame-Options 头部以防止点击劫持
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// 设置内容安全策略头部以防止 XSS
// 注意：这个头部可能会变得非常复杂，所以您需要在互联网上查找示例适用于您的应用程序
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// 设置 X-XSS-Protection 头部以防止 XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// 设置 X-Content-Type-Options 头部以防止 MIME 嗅探
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// 设置 Referrer-Policy 头部以控制发送多少引荐者信息
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// 设置 Strict-Transport-Security 头部以强制使用 HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// 设置 Permissions-Policy 头部以控制可使用的功能和 API
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

这些可以添加到您的 `bootstrap.php` 或 `index.php` 文件的顶部。

### 作为过滤器添加

您也可以在过滤器/钩子中添加它们，如下所示: 

```php
// 在过滤器中添加头部
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

您还可以将它们添加为中间件类。这是保持代码清晰和有组织的好方法。

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

// index.php 或您设置路由的任何地方
// 请注意，此空字符串组作为全局中间件为所有路由服务。当然，您也可以只针对特定路由添加。
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// 更多路由
}, [ new SecurityHeadersMiddleware() ]);
```


## 跨站请求伪造 (CSRF)

跨站请求伪造 (CSRF) 是一种攻击类型，恶意网站可以使用户的浏览器向您的网站发送请求。这可用于在用户不知情的情况下在您的网站上执行操作。Flight 不提供内置的 CSRF 保护机制，但您可以很容易地通过使用中间件来实现自己的保护。

### 设置

首先，您需要生成一个 CSRF 令牌并将其存储在用户的会话中。然后，您可以在表单中使用此令牌，并在提交表单时检查它。

```php
// 生成一个 CSRF 令牌并将其存储在用户的会话中
// (假设您已经创建了一个会话对象并将其连接到 Flight)
// 您只需要为每个会话生成一个令牌 (以便在同一用户的多个选项卡和请求中起作用)
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

您还可以设置自定义函数来在您的 Latte 模板中输出 CSRF 令牌。

```php
// 设置一个自定义函数来输出 CSRF 令牌
// 注意：视图已配置为使用 Latte 作为视图引擎
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

然后在您的 Latte 模板中，您可以使用 `csrf()` 函数输出 CSRF 令牌。

```html
<form method="post">
	{csrf()}
	<!-- 其他表单字段 -->
</form>
```

简单明了吧？

### 检查 CSRF 令牌

您可以使用事件过滤器检查 CSRF 令牌:

```php
// 此中间件检查请求是否为 POST 请求，如果是，则检查 CSRF 令牌是否有效
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// 从表单值中获取 CSRF 令牌
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Invalid CSRF token');
		}
	}
});
```

或者您可以使用中间件类:

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

// index.php 或您设置路由的任何地方
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// 更多路由
}, [ new CsrfMiddleware() ]);
```


## 跨站脚本攻击 (XSS)

跨站脚本攻击 (XSS) 是一种攻击类型，恶意网站可以向您的网站注入代码。大多数机会来自您的最终用户填写的表单值。您绝对 **不** 应信任来自您的用户的输出! 始终假定他们都是世界上最好的黑客。他们可以注入恶意 JavaScript 或 HTML 到您的页面。此代码可用于窃取用户信息或在您的网站上执行操作。使用 Flight 的视图类，您可以轻松转义输出以防止 XSS 攻击。

```php
// 假设用户很聪明，尝试将此用作他们的姓名
$name = '<script>alert("XSS")</script>';

// 这将转义输出
Flight::view()->set('name', $name);
// 这将输出: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// 如果您使用像 Latte 作为您的视图类注册的东西，它也将自动转义它。
Flight::view()->render('template', ['name' => $name]);
```

## SQL 注入

SQL 注入是一种攻击类型，恶意用户可以向您的数据库中注入 SQL 代码。这可用于从您的数据库中窃取信息或在您的数据库上执行操作。再次，请 **绝对不要** 信任您的用户输入! 始终假定他们在寻衅滋事。您可以在您的 `PDO` 对象中使用准备好的语句来防止 SQL 注入。

```php
// 假设您已将 Flight::db() 注册为您的 PDO 对象
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// 如果使用 PdoWrapper 类，可以轻松在一行内完成
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// 您可以在具有 ? 占位符的 PDO 对象中执行相同的操作
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// 只是承诺您永远不要、绝对不要做这样的事情……
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// 因为如果 $username = "' OR 1=1; -- "; 
// 构建查询后看起来像这样
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// 看起来很奇怪，但这是一个有效的查询，将生效。实际上，
// 这是一个非常常见的 SQL 注入攻击，将返回所有用户。
```

## CORS

跨源资源共享 (CORS) 是一种机制，允许网页上的许多资源 (例如，字体、JavaScript 等) 从资源原始域之外的另一个域请求。Flight 没有内置功能，但可以通过在调用 `Flight::start()` 方法之前运行钩子来轻松处理。

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
		// 在此自定义您允许的主机。
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

// index.php 或您设置路由的任何地方
$CorsUtil = new CorsUtil();
Flight::before('start', [ $CorsUtil, 'setupCors' ]);

```

## 结论

安全性非常重要，确保您的 Web 应用程序是安全的至关重要。Flight 提供了许多功能来帮助您保护您的 Web 应用程序，但始终保持警惕并确保您尽力保护用户数据。始终假定最坏的情况，并且永远不要相信用户的输入。始终转义输出并使用准备语句以防止 SQL 注入。始终使用中间件保护您的路由免受 CSRF 和 CORS 攻击。如果您做到所有这些，您将为构建安全的 Web 应用程序迈出重要的一步。