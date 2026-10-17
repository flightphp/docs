# 安全

## 概述

对于 Web 应用来说，安全是一件大事。您需要确保您的应用是安全的，并且用户的数据是安全的。Flight 提供了许多功能来帮助您保护您的 Web 应用。

官方的 [skeleton](https://github.com/flightphp/skeleton) 还附带了一份专门的 **`SECURITY.md`** 和 security-headers 中间件，这样 [AI 编码工具](/learn/ai)（以及人类）就有一个专门的地方来处理机密信息、安全头以及 XSS/SQL 规则——与 `AGENTS.md` 中的通用编码风格分开。

## 理解

在构建 Web 应用时，您应该了解一些常见的安全威胁。一些最常见的威胁包括：
- 跨站请求伪造（CSRF）
- 跨站脚本（XSS）
- SQL 注入
- 跨来源资源共享（CORS）

[Templates](/learn/templates) 默认转义输出来帮助应对 XSS（Twig 和 Latte 会这样做；请利用这一优势）。[Sessions](/awesome-plugins/session) 可以通过在用户会话中存储 CSRF 令牌来帮助应对 CSRF，如下所述。使用 PDO 的预处理语句——或在 [SimplePdo](/learn/simple-pdo) 上的辅助方法——有助于防止 SQL 注入。CORS 可以在调用 `Flight::start()` 之前通过一个简单的钩子来处理。

所有这些方法协同工作，帮助您的 Web 应用保持安全。您应该时刻牢记学习并理解安全最佳实践。不要为了让某个页面加载就让 AI 助手“禁用 CSP”或削弱安全头，而不理解其中的利弊。

## 基本用法

### 安全头（Headers）

HTTP 安全头是保护 Web 应用的最简单方法之一。您可以使用安全头来防止点击劫持、XSS 和其他攻击。有几种方法可以将这些安全头添加到应用中。

两个用于检查安全头的好网站是 [securityheaders.com](https://securityheaders.com/) 和 [observatory.mozilla.org](https://observatory.mozilla.org/)。设置好下面的代码后，您就可以轻松地使用这两个网站来验证您的安全头是否正常工作。

Skeleton 包含 `App\Middleware\SecurityHeadersMiddleware`（CSP 配合每个请求的 nonce、frame 选项、HSTS 等）。最好有意识地扩展它，而不是直接关闭安全头。

#### 手动添加

您可以通过 `Flight\Response` 对象上的 `header` 方法手动添加这些安全头。
```php
// 设置 X-Frame-Options 安全头以阻止点击劫持
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// 设置 Content-Security-Policy 安全头以防止 XSS
// 注意：这个安全头可能会变得非常复杂，因此您需要
//  参考网上的示例来适配您的应用
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// 设置 X-XSS-Protection 安全头以防止 XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// 设置 X-Content-Type-Options 安全头以防止 MIME 嗅探
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// 设置 Referrer-Policy 安全头以控制发送多少 referrer 信息
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// 设置 Strict-Transport-Security 安全头以强制使用 HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// 设置 Permissions-Policy 安全头以控制可使用的功能和 API
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

这些可以添加到您的 `routes.php` 或 `index.php` 文件顶部。

#### 作为过滤器添加

您还可以将它们添加在过滤器/钩子中，如下所示：

```php
// 在过滤器中添加安全头
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

您还可以将它们添加为一个中间件类，这可以为要应用的路由提供最大的灵活性。通常，这些安全头应应用于所有 HTML 和 API 响应。

Skeleton 风格的路径和命名空间（**文件夹大小写与 `App\Middleware` 匹配**）：

```php
// 文件：app/Middleware/SecurityHeadersMiddleware.php

namespace App\Middleware;

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
		// 当存在内联脚本时，优先使用 bootstrap 中的 CSP nonce（skeleton 会设置 csp_nonce）
		$nonce = $this->app->get('csp_nonce');
		$csp = $nonce
			? "default-src 'self'; script-src 'self' 'nonce-{$nonce}'; style-src 'self' 'nonce-{$nonce}'"
			: "default-src 'self'";

		$response->header('X-Frame-Options', 'SAMEORIGIN');
		$response->header('Content-Security-Policy', $csp);
		$response->header('X-XSS-Protection', '1; mode=block');
		$response->header('X-Content-Type-Options', 'nosniff');
		$response->header('Referrer-Policy', 'no-referrer-when-downgrade');
		$response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		$response->header('Permissions-Policy', 'geolocation=()');
	}
}

// 文件：app/config/routes.php — 空字符串组 = 应用于所有路由的全局中间件
use App\Middleware\SecurityHeadersMiddleware;
use flight\net\Router;

$router->group('', function (Router $router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// 更多路由
}, [SecurityHeadersMiddleware::class]);
```

较旧的项目可能仍然使用 `app/middlewares` 和 `app\middlewares`；只要文件夹匹配，那也可以工作。新的 skeleton 应用使用 **`app/Middleware/`** 和 **`App\Middleware`**。请参阅 [Autoloading](/learn/autoloading)。

### 跨站请求伪造（CSRF）

跨站请求伪造（CSRF）是一种攻击，恶意网站可以让用户的浏览器向您的网站发送请求。这可以在用户不知情的情况下在您的网站上执行操作。Flight 没有内置的 CSRF 防护机制，但您可以通过使用中间件轻松实现自己的防护。

#### 设置

首先，您需要生成一个 CSRF 令牌并将其存储在用户的会话中。然后，您可以在表单中使用此令牌，并在表单提交时检查它。我们将使用 [flightphp/session](/awesome-plugins/session) 插件来管理会话。

```php
// 生成 CSRF 令牌并将其存储在用户的会话中
// （假设您已经创建了一个会话对象并将其附加到 Flight）
// 有关更多信息，请参阅会话文档
Flight::register('session', flight\Session::class);

// 每个会话只需要生成一个令牌（这样它可以
// 在同一个用户的多个选项卡和请求中工作）
if(Flight::session()->get('csrf_token') === null) {
	Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
}
```

##### 使用默认的 PHP Flight 模板

```html
<!-- 在表单中使用 CSRF 令牌 -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- 其他表单字段 -->
</form>
```

##### 使用 Twig（skeleton 默认）

注册一个 Twig 函数，或将令牌传递给每个表单视图。一个使用全局变量和表单字段的简要示例：

```php
// 在配置 Twig 时（例如在 services.php 中）
$twig->addGlobal('csrf_token', $app->session()->get('csrf_token'));
```

```html
{# 模板：app/views/form.twig #}
<form method="post">
	<input type="hidden" name="csrf_token" value="{{ csrf_token }}">
	{# 其他字段 #}
</form>
```

##### 使用 Latte

您还可以在 Latte 模板中设置一个自定义函数来输出 CSRF 令牌。

```php

Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// 其他配置...

	// 设置一个自定义函数来输出 CSRF 令牌
	$latte->addFunction('csrf', function() {
		$csrfToken = Flight::session()->get('csrf_token');
		return new \Latte\Runtime\Html('<input type="hidden" name="csrf_token" value="' . $csrfToken . '">');
	});

	$latte->render($finalPath, $data, $block);
});
```

现在在您的 Latte 模板中，您可以使用 `csrf()` 函数来输出 CSRF 令牌。

```html
<form method="post">
	{csrf()}
	<!-- 其他表单字段 -->
</form>
```

#### 检查 CSRF 令牌

您可以使用几种方法来检查 CSRF 令牌。

##### 中间件

```php
// 文件：app/Middleware/CsrfMiddleware.php

namespace App\Middleware;

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
				$this->app->halt(403, '无效的 CSRF 令牌');
			}
		}
	}
}

// 文件：routes.php
use App\Middleware\CsrfMiddleware;

$router->group('', function ($router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// 更多路由
}, [CsrfMiddleware::class]);
```

##### 事件过滤器

```php
// 此中间件检查请求是否为 POST 请求，如果是，则检查 CSRF 令牌是否有效
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// 从表单值中获取 CSRF 令牌
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, '无效的 CSRF 令牌');
			// 或用于 JSON 响应
			Flight::jsonHalt(['error' => '无效的 CSRF 令牌'], 403);
		}
	}
});
```

### 跨站脚本（XSS）

跨站脚本（XSS）是一种攻击，恶意表单输入可以向您的网站注入代码。这些机会大多来自最终用户填写的表单值。您**绝不**应该信任用户的输出！始终假设他们都是世界上最好的黑客。他们可以向您的页面注入恶意的 JavaScript 或 HTML。这些代码可以用来窃取用户的信息或在您的网站上执行操作。通过使用 Flight 的视图类或像 [Twig](/awesome-plugins/twig) 或 [Latte](/awesome-plugins/latte) 这样的模板引擎，您可以轻松地转义输出以防止 XSS 攻击。

```php
// 假设用户很狡猾，试图用这个作为他们的名字
$name = '<script>alert("XSS")</script>';

// 这将转义输出
Flight::view()->set('name', $name);
// 这将输出：&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Twig（skeleton 默认）和 Latte 默认自动转义 — 优先使用它们而不是原始 PHP echo
Flight::render('template', ['name' => $name]);
// Twig: {{ name }}  → 已转义
// 避免使用 |raw / 未转义的输出，除非内容完全可信
```

### SQL 注入

SQL 注入是一种攻击，恶意用户可以向您的数据库注入 SQL 代码。这可以用来窃取数据库中的信息或在您的数据库上执行操作。同样，您**绝不**应该信任用户的输入！始终假设他们是不怀好意的。请使用预处理语句——[SimplePdo](/learn/simple-pdo) 辅助方法使其成为默认路径。

```php
// 假设您已将 Flight::db() 注册为 SimplePdo（或在控制器中注入 SimplePdo）
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// SimplePdo（推荐）— 使用绑定参数的单行写法
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// 同样的思路，使用 ? 占位符
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

在 skeleton 风格的控制器中，优先在构造函数中注入 `SimplePdo`，而不是使用 `Flight::db()`，这样测试和 AI 生成的代码可以保持一致（[DIC](/learn/dependency-injection-container)）。

#### 不安全的示例

下面就是为什么我们使用 SQL 预处理语句来防御像下面这样的简单示例：

```php
// 最终用户填写一个 Web 表单。
// 对于表单的值，黑客输入了类似这样的内容：
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// 查询构建后看起来像这样
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// 它看起来很奇怪，但它是一个有效的查询并且会起作用。事实上，
// 这是一个非常常见的 SQL 注入攻击，会返回所有用户。

var_dump($users); // 这将转储数据库中的所有用户，而不仅仅是那一个用户名
```

### 机密信息与配置

- 将机密信息放在 **`.env`**（或真实环境）中，而不是放在已提交的 `config.php` 示例中。
- Skeleton 规则：在 `config.php` 中放置字面默认值；在 bootstrap 中合并环境变量；**不要**在控制器内部读取 `$_ENV`——而是注入配置。请参阅 [Configuration](/learn/configuration)。
- 不要提交 API 密钥、数据库密码或会话加密密钥。让 AI 工具指向 **`SECURITY.md`**，这样它们就不会发明不安全的小聪明。

### JSONP 回调验证

如果您使用 Flight 的 `Flight::jsonp()` 方法，请注意 Flight 会根据严格的白名单正则表达式（`/^[A-Za-z_$][\w$.]{0,127}$/`）验证 JSONP 回调参数名称。任何不匹配此模式的回调名称都会导致 Flight 抛出异常，从而防止通过恶意的回调值注入任意 JavaScript。

此验证是内置的，不需要额外配置，但在调试来自 JSONP 端点的意外错误时，值得了解这一点。

### CORS

跨来源资源共享（CORS）是一种机制，允许网页上的许多资源（例如字体、JavaScript 等）从资源所在域以外的另一个域请求。Flight 没有内置的功能，但这可以很容易地通过一个在调用 `Flight::start()` 方法之前运行的钩子来处理。

```php
// 文件：app/Utils/CorsUtil.php（skeleton：PascalCase Utils 文件夹 → App\Utils）

namespace App\Utils;

use flight\Engine;

class CorsUtil
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function set(array $params = []): void
	{
		$request = $this->app->request();
		$response = $this->app->response();
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
		// 在这里自定义您的允许主机列表。
		$allowed = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		$request = $this->app->request();

		if (in_array($request->getVar('HTTP_ORIGIN'), $allowed