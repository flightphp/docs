# 安全性

在网络应用程序方面，安全性是一个大问题。您希望确保您的应用程序是安全的，并且用户的数据是安全的。Flight 提供了一系列功能以帮助您保护您的网络应用程序。

## 头部

HTTP 头部是保护您的网络应用程序的最简单方法之一。您可以使用头部来防止点击劫持、XSS 和其他攻击。有几种方法可以将这些头部添加到您的应用程序中。

两个检查您头部安全性的网站是 [securityheaders.com](https://securityheaders.com/) 和 
[observatory.mozilla.org](https://observatory.mozilla.org/)。

### 手动添加

您可以通过在 `Flight\Response` 对象上使用 `header` 方法手动添加这些头部。
```php
// 设置 X-Frame-Options 头部以防止点击劫持
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// 设置 Content-Security-Policy 头部以防止 XSS
// 注意：这个头部可能会变得非常复杂，您会希望
// 参考互联网上的示例以供您的应用程序使用
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// 设置 X-XSS-Protection 头部以防止 XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// 设置 X-Content-Type-Options 头部以防止 MIME 嗅探
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// 设置 Referrer-Policy 头部以控制发送多少引用信息
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// 设置 Strict-Transport-Security 头部以强制使用 HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// 设置 Permissions-Policy 头部以控制可以使用哪些功能和 API
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

可以将这些添加到您的 `bootstrap.php` 或 `index.php` 文件的开头。

### 作为过滤器添加

您还可以像下面这样在过滤器/钩子中添加它们：

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

您还可以将它们添加为中间件类。这是一种保持代码简洁和有序的好方法。

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

// index.php 或您拥有路由的地方
// 仅供参考，这个空字符串组作为所有路由的全局中间件。
// 当然您可以做同样的事情，只将其添加到特定路由。
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// 更多路由
}, [ new SecurityHeadersMiddleware() ]);
```


## 跨站请求伪造 (CSRF)

跨站请求伪造 (CSRF) 是一种攻击方式，其中恶意网站可以使用户的浏览器向您的网站发送请求。这可以在用户不知情的情况下在您的网站上执行操作。Flight 不提供内置的 CSRF 保护机制，但您可以通过使用中间件轻松实现自己的保护。

### 设置

首先，您需要生成一个 CSRF 令牌并将其存储在用户的会话中。然后，您可以在您的表单中使用此令牌，并在表单提交时检查它。

```php
// 生成 CSRF 令牌并将其存储在用户的会话中
// （假设您已在 Flight 中创建了一个会话对象并将其附加）
// 有关更多信息，请参见会话文档
Flight::register('session', \Ghostff\Session\Session::class);

// 您只需为每个会话生成一个令牌（以便它在多个选项卡和请求中均能工作）
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

您还可以设置一个自定义函数，在您的 Latte 模板中输出 CSRF 令牌。

```php
// 设置一个自定义函数以输出 CSRF 令牌
// 注意：视图已配置使用 Latte 作为视图引擎
Flight::view()->addFunction('csrf', function() {
	$csrfToken = Flight::session()->get('csrf_token');
	return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
});
```

现在在您的 Latte 模板中，可以使用 `csrf()` 函数输出 CSRF 令牌。

```html
<form method="post">
	{csrf()}
	<!-- 其他表单字段 -->
</form>
```

简单明了吧？

### 检查 CSRF 令牌

您可以使用事件过滤器检查 CSRF 令牌：

```php
// 这个中间件检查请求是否为 POST 请求，如果是，则检查 CSRF 令牌是否有效
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// 从表单值中获取 csrf 令牌
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, '无效的 CSRF 令牌');
			// 或者用于 JSON 响应
			Flight::jsonHalt(['error' => '无效的 CSRF 令牌'], 403);
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
				Flight::halt(403, '无效的 CSRF 令牌');
			}
		}
	}
}

// index.php 或您拥有路由的地方
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// 更多路由
}, [ new CsrfMiddleware() ]);
```

## 跨站脚本攻击 (XSS)

跨站脚本攻击 (XSS) 是一种攻击，其中恶意网站可以将代码注入到您的网站中。这些机会大多来自于您的终端用户填写的表单值。您**绝对**不应信任用户的输出！始终假设他们都是世界上最优秀的黑客。他们可以将恶意 JavaScript 或 HTML 注入到您的页面中。此代码可用于窃取用户的信息或在您的网站上执行操作。通过使用 Flight 的视图类，您可以轻松转义输出以防止 XSS 攻击。

```php
// 假设用户聪明到尝试用这个作为他们的名字
$name = '<script>alert("XSS")</script>';

// 这将转义输出
Flight::view()->set('name', $name);
// 这将输出：&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// 如果您使用像 Latte 这样的视图类，它也会自动转义。
Flight::view()->render('template', ['name' => $name]);
```

## SQL 注入

SQL 注入是一种攻击，其中恶意用户可以向您的数据库注入 SQL 代码。这可以用于窃取数据库中的信息或对数据库执行操作。同样，您**绝对**不应信任用户的输入！始终假设他们是怀着恶意的。您可以在 `PDO` 对象中使用预处理语句来防止 SQL 注入。

```php
// 假设您已经将 Flight::db() 注册为您的 PDO 对象
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// 如果您使用 PdoWrapper 类，那么可以轻松通过一行代码完成
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// 使用带有 ? 占位符的 PDO 对象也可以做到同样的事情
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// 只是请答应我永远不要做这样的事情...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// 因为如果 $username = "' OR 1=1; -- "; 
// 查询构建后看起来像这样
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// 它看起来很奇怪，但这是一个有效的查询，可以工作。实际上，
// 这是一个非常常见的 SQL 注入攻击，将返回所有用户。
```

## 跨源资源共享 (CORS)

跨源资源共享 (CORS) 是一种机制，允许在网页上请求来自另一个域的多个资源（如字体、JavaScript 等），这些资源来自原始域之外。Flight 没有内置的功能，但可以通过在调用 `Flight::start()` 方法之前运行的钩子轻松处理。

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
		// 在这里自定义您的允许主机。
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

// index.php 或您拥有路由的地方
$CorsUtil = new CorsUtil();

// 这需要在 start 运行之前执行。
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

## 错误处理
在生产环境中隐藏敏感的错误详情，以避免向攻击者泄露信息。

```php
// 在您的 bootstrap.php 或 index.php 中

// 在 flightphp/skeleton 中，这位于 app/config/config.php
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // 禁用错误显示
    ini_set('log_errors', 1);     // 记录错误
    ini_set('error_log', '/path/to/error.log');
}

// 在您的路由或控制器中
// 使用 Flight::halt() 进行控制的错误响应
Flight::halt(403, '拒绝访问');
```

## 输入清理
绝对不要信任用户输入。在处理之前进行清理，以防止恶意数据潜入。

```php

// 假设一个包含 $_POST['input'] 和 $_POST['email'] 的 $_POST 请求

// 清理字符串输入
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// 清理电子邮件
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

## 密码哈希
安全存储密码并使用 PHP 内置函数安全验证它们。

```php
$password = Flight::request()->data->password;
// 存储时哈希密码（例如，在注册期间）
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 验证密码（例如，在登录期间）
if (password_verify($password, $stored_hash)) {
    // 密码匹配
}
```

## 速率限制
通过使用缓存来限制请求速率，以防止暴力攻击。

```php
// 假设您已安装并注册 flightphp/cache
// 在中间件中使用 flightphp/cache
Flight::before('start', function() {
    $cache = Flight::cache();
    $ip = Flight::request()->ip;
    $key = "rate_limit_{$ip}";
    $attempts = (int) $cache->retrieve($key);
    
    if ($attempts >= 10) {
        Flight::halt(429, '请求过多');
    }
    
    $cache->set($key, $attempts + 1, 60); // 60 秒后重置
});
```

## 结论

安全性是一个大问题，确保您的网络应用程序安全非常重要。Flight 提供了一系列功能来帮助您保护网络应用程序，但始终保持警惕，确保您尽力保护用户的数据是非常重要的。始终假设最坏的情况，绝不要信任用户的输入。始终转义输出，使用预处理语句防止 SQL 注入。始终使用中间件保护您的路由免受 CSRF 和 CORS 攻击。如果您做到这些，您将朝着构建安全的网络应用程序的方向迈出坚实的步伐。