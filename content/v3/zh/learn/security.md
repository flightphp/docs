# 安全

## 概述

安全对于 Web 应用程序来说是一个重要问题。您需要确保您的应用程序是安全的，并且您的用户数据是安全的。Flight 提供了一系列功能来帮助您保护 Web 应用程序的安全。

## 理解

在构建 Web 应用程序时，您应该了解一些常见的威胁。其中最常见的威胁包括：
- 跨站请求伪造 (CSRF)
- 跨站脚本攻击 (XSS)
- SQL 注入
- 跨源资源共享 (CORS)

[Templates](/learn/templates) 通过默认转义输出来帮助防范 XSS，这样您就不必记住要这样做。[Sessions](/awesome-plugins/session) 可以通过在用户会话中存储 CSRF 令牌来帮助防范 CSRF，如下面所述。使用 PDO 的预准备语句可以帮助防止 SQL 注入攻击（或者使用 [PdoWrapper](/learn/pdo-wrapper) 类中的便捷方法）。CORS 可以通过在调用 `Flight::start()` 之前使用简单的钩子来处理。

所有这些方法共同协作以帮助保持您的 Web 应用程序安全。您应该始终将学习和理解安全最佳实践放在首位。

## 基本用法

### 标头

HTTP 标头是保护 Web 应用程序的最简单方法之一。您可以使用标头来防止点击劫持、XSS 和其他攻击。您可以通过几种方式将这些标头添加到您的应用程序中。

检查标头安全的两个优秀网站是 [securityheaders.com](https://securityheaders.com/) 和 [observatory.mozilla.org](https://observatory.mozilla.org/)。在设置下面的代码后，您可以轻松使用这两个网站验证您的标头是否有效。

#### 手动添加

您可以使用 `Flight\Response` 对象上的 `header` 方法手动添加这些标头。
```php
// Set the X-Frame-Options header to prevent clickjacking
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// Set the Content-Security-Policy header to prevent XSS
// Note: this header can get very complex, so you'll want
//  to consult examples on the internet for your application
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// Set the X-XSS-Protection header to prevent XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// Set the X-Content-Type-Options header to prevent MIME sniffing
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// Set the Referrer-Policy header to control how much referrer information is sent
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// Set the Strict-Transport-Security header to force HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// Set the Permissions-Policy header to control what features and APIs can be used
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

这些可以在您的 `routes.php` 或 `index.php` 文件顶部添加。

#### 作为过滤器添加

您也可以像以下一样在过滤器/钩子中添加它们：

```php
// Add the headers in a filter
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

#### 作为中间件添加

您也可以将它们作为中间件类添加，这为应用到哪些路由提供了最大的灵活性。通常，这些标头应该应用到所有 HTML 和 API 响应。

```php
// app/middlewares/SecurityHeadersMiddleware.php

namespace app\middlewares;

use flight\Engine;

class SecurityHeadersMiddleware
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function before(array $params): void
	{
		$response = $this->app->response();
		$response->header('X-Frame-Options', 'SAMEORIGIN');
		$response->header("Content-Security-Policy", "default-src 'self'");
		$response->header('X-XSS-Protection', '1; mode=block');
		$response->header('X-Content-Type-Options', 'nosniff');
		$response->header('Referrer-Policy', 'no-referrer-when-downgrade');
		$response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		$response->header('Permissions-Policy', 'geolocation=()');
	}
}

// index.php or wherever you have your routes
// FYI, this empty string group acts as a global middleware for
// all routes. Of course you could do the same thing and just add
// this only to specific routes.
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// more routes
}, [ SecurityHeadersMiddleware::class ]);
```

### 跨站请求伪造 (CSRF)

跨站请求伪造 (CSRF) 是一种攻击类型，其中恶意网站可以让用户的浏览器向您的网站发送请求。这可以用于在用户不知情的情况下在您的网站上执行操作。Flight 不提供内置的 CSRF 保护机制，但您可以使用中间件轻松实现自己的机制。

#### 设置

首先，您需要生成一个 CSRF 令牌并将其存储在用户会话中。然后，您可以在表单中使用此令牌，并在表单提交时检查它。我们将使用 [flightphp/session](/awesome-plugins/session) 插件来管理会话。

```php
// Generate a CSRF token and store it in the user's session
// (assuming you've created a session object at attached it to Flight)
// see the session documentation for more information
Flight::register('session', flight\Session::class);

// You only need to generate a single token per session (so it works 
// across multiple tabs and requests for the same user)
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### 使用默认 PHP Flight 模板

```html
<!-- Use the CSRF token in your form -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- other form fields -->
</form>
```

##### 使用 Latte

您也可以在 Latte 模板中设置一个自定义函数来输出 CSRF 令牌。

```php

Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// other configurations...

	// Set a custom function to output the CSRF token
	$latte->addFunction('csrf', function() {
		$csrfToken = Flight::session()->get('csrf_token');
		return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
	});

	$latte->render($finalPath, $data, $block);
});
```

现在，在您的 Latte 模板中，您可以使用 `csrf()` 函数来输出 CSRF 令牌。

```html
<form method="post">
	{csrf()}
	<!-- other form fields -->
</form>
```

#### 检查 CSRF 令牌

您可以使用几种方法检查 CSRF 令牌。

##### 中间件

```php
// app/middlewares/CsrfMiddleware.php

namespace app\middleware;

use flight\Engine;

class CsrfMiddleware
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function before(array $params): void
	{
		if($this->app->request()->method == 'POST') {
			$token = $this->app->request()->data->csrf_token;
			if($token !== $this->app->session()->get('csrf_token')) {
				$this->app->halt(403, 'Invalid CSRF token');
			}
		}
	}
}

// index.php or wherever you have your routes
use app\middlewares\CsrfMiddleware;

Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// more routes
}, [ CsrfMiddleware::class ]);
```

##### 事件过滤器

```php
// This middleware checks if the request is a POST request and if it is, it checks if the CSRF token is valid
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// capture the csrf token from the form values
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Invalid CSRF token');
			// or for a JSON response
			Flight::jsonHalt(['error' => 'Invalid CSRF token'], 403);
		}
	}
});
```

### 跨站脚本攻击 (XSS)

跨站脚本攻击 (XSS) 是一种攻击类型，其中恶意表单输入可以将代码注入到您的网站中。这些机会大多来自最终用户将填写的表单值。您**绝不**应该信任来自用户的输出！始终假设他们是世界上最好的黑客。他们可以将恶意的 JavaScript 或 HTML 注入到您的页面中。此代码可用于从您的用户窃取信息或在您的网站上执行操作。使用 Flight 的视图类或其他模板引擎如 [Latte](/awesome-plugins/latte)，您可以轻松转义输出以防止 XSS 攻击。

```php
// Let's assume the user is clever as tries to use this as their name
$name = '<script>alert("XSS")</script>';

// This will escape the output
Flight::view()->set('name', $name);
// This will output: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// If you use something like Latte registered as your view class, it will also auto escape this.
Flight::view()->render('template', ['name' => $name]);
```

### SQL 注入

SQL 注入是一种攻击类型，其中恶意用户可以将 SQL 代码注入到您的数据库中。这可以用于从您的数据库窃取信息或在您的数据库上执行操作。您再次**绝不**应该信任来自用户的输入！始终假设他们是来者不善的。您可以使用 `PDO` 对象中的预准备语句来防止 SQL 注入。

```php
// Assuming you have Flight::db() registered as your PDO object
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// If you use the PdoWrapper class, this can easily be done in one line
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// You can do the same thing with a PDO object with ? placeholders
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

#### 不安全示例

下面是为什么我们使用 SQL 预准备语句来保护免受像下面这样的无害示例的影响：

```php
// end user fills out a web form.
// for the value of the form, the hacker puts in something like this:
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// After the query is build it looks like this
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// It looks strange, but it's a valid query that will work. In fact,
// it's a very common SQL injection attack that will return all users.

var_dump($users); // this will dump all users in the database, not just the one single username
```

### CORS

跨源资源共享 (CORS) 是一种机制，允许 Web 页面上的许多资源（例如字体、JavaScript 等）从资源起源域之外的另一个域请求。Flight 没有内置功能，但这可以通过在调用 `Flight::start()` 方法之前运行一个钩子来轻松处理。

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
		// customize your allowed hosts here.
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

// index.php or wherever you have your routes
$CorsUtil = new CorsUtil();

// This needs to be run before start runs.
Flight::before('start', [ $CorsUtil, 'setupCors' ]);
```

### 错误处理
在生产环境中隐藏敏感的错误细节，以避免向攻击者泄露信息。在生产环境中，将 `display_errors` 设置为 `0`，并记录错误而不是显示它们。

```php
// In your bootstrap.php or index.php

// add this to your app/config/config.php
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // Disable error display
    ini_set('log_errors', 1);     // Log errors instead
    ini_set('error_log', '/path/to/error.log');
}

// In your routes or controllers
// Use Flight::halt() for controlled error responses
Flight::halt(403, 'Access denied');
```

### 输入净化
绝不信任用户输入。在处理之前使用 [filter_var](https://www.php.net/manual/en/function.filter-var.php) 净化它，以防止恶意数据潜入。

```php

// Lets assume a $_POST request with $_POST['input'] and $_POST['email']

// Sanitize a string input
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// Sanitize an email
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

### 密码哈希
使用 PHP 的内置函数如 [password_hash](https://www.php.net/manual/en/function.password-hash.php) 和 [password_verify](https://www.php.net/manual/en/function.password-verify.php) 安全地存储密码并验证它们。密码绝不应该以明文形式存储，也不应该使用可逆方法加密它们。哈希确保即使您的数据库被入侵，实际密码仍然受到保护。

```php
$password = Flight::request()->data->password;
// Hash a password when storing (e.g., during registration)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Verify a password (e.g., during login)
if (password_verify($password, $stored_hash)) {
    // Password matches
}
```

### 速率限制
通过使用缓存限制请求速率来保护免受暴力攻击或拒绝服务攻击。

```php
// Assuming you have flightphp/cache installed and registered
// Using flightphp/cache in a filter
Flight::before('start', function() {
    $cache = Flight::cache();
    $ip = Flight::request()->ip;
    $key = "rate_limit_{$ip}";
    $attempts = (int) $cache->retrieve($key);
    
    if ($attempts >= 10) {
        Flight::halt(429, 'Too many requests');
    }
    
    $cache->set($key, $attempts + 1, 60); // Reset after 60 seconds
});
```

## 另请参阅
- [Sessions](/awesome-plugins/session) - 如何安全管理用户会话。
- [Templates](/learn/templates) - 使用模板自动转义输出并防止 XSS。
- [PDO Wrapper](/learn/pdo-wrapper) - 使用预准备语句简化数据库交互。
- [Middleware](/learn/middleware) - 如何使用中间件简化添加安全标头的过程。
- [Responses](/learn/responses) - 如何使用安全标头自定义 HTTP 响应。
- [Requests](/learn/requests) - 如何处理和净化用户输入。
- [filter_var](https://www.php.net/manual/en/function.filter-var.php) - 用于输入净化的 PHP 函数。
- [password_hash](https://www.php.net/manual/en/function.password-hash.php) - 用于安全密码哈希的 PHP 函数。
- [password_verify](https://www.php.net/manual/en/function.password-verify.php) - 用于验证哈希密码的 PHP 函数。

## 故障排除
- 请参阅上面的“另请参阅”部分，获取与 Flight Framework 组件相关问题的故障排除信息。

## 更新日志
- v3.1.0 - 添加了关于 CORS、错误处理、输入净化、密码哈希和速率限制的部分。
- v2.0 - 添加了默认视图的转义以防止 XSS。