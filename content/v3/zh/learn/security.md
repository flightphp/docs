# 安全

## 概述

对于 Web 应用而言，安全性至关重要。您需要确保应用程序是安全的，并且用户的数据是安全的。Flight 提供了许多功能来帮助您保护 Web 应用。

官方的 [骨架](https://github.com/flightphp/skeleton) 还附带了一个专门的 **`SECURITY.md`** 和安全头部中间件，以便 [AI 编码工具](/learn/ai)（以及人类）有一个专门的地方来存放机密、头部以及 XSS/SQL 规则——与 `AGENTS.md` 中的通用编码风格分开。

## 理解

在构建 Web 应用时，您应该了解一些常见的安全威胁。一些最常见的威胁包括：
- 跨站请求伪造（CSRF）
- 跨站脚本（XSS）
- SQL 注入
- 跨源资源共享（CORS）

[模板](/learn/templates) 通过默认转义输出来帮助防范 XSS（Twig 和 Latte 会这样做；请利用这一优势）。[会话](/awesome-plugins/session) 可以通过在用户会话中存储 CSRF 令牌来帮助防范 CSRF，如下所述。使用带有 PDO 的准备语句——或 [SimplePdo](/learn/simple-pdo) 上的辅助函数——有助于防止 SQL 注入。CORS 可以在调用 `Flight::start()` 之前通过一个简单的钩子来处理。

所有这些方法共同作用，帮助保护您的 Web 应用安全。学习并理解安全最佳实践应该始终是您首要考虑的事情。不要仅仅为了让页面加载而要求 AI 助手“禁用 CSP”或削弱头部，而不理解其中的权衡。

## 基本用法

### 头部

HTTP 头部是保护 Web 应用最简单的方法之一。您可以使用头部来防止点击劫持、XSS 和其他攻击。有几种方法可以将这些头部添加到您的应用程序中。

检查头部安全性的两个优秀网站是 [securityheaders.com](https://securityheaders.com/) 和 [observatory.mozilla.org](https://observatory.mozilla.org/)。设置好下面的代码后，您可以使用这两个网站轻松验证您的头部是否正常工作。

骨架包含 `App\Middleware\SecurityHeadersMiddleware`（CSP 带有每次请求的 nonce、frame options、HSTS 等）。相比直接关闭头部，更倾向于有意地扩展它。

#### 手动添加

您可以通过 `Flight\Response` 对象上的 `header` 方法手动添加这些头部。

```php
// 设置 X-Frame-Options 头部以阻止点击劫持
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// 设置 Content-Security-Policy 头部以阻止 XSS
// 注意：此头部可能变得非常复杂，因此您需要
//  查看互联网上针对您应用的示例
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// 设置 X-XSS-Protection 头部以阻止 XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// 设置 X-Content-Type-Options 头部以防止 MIME 嗅探
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// 设置 Referrer-Policy 头部以控制发送多少引用者信息
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// 设置 Strict-Transport-Security 头部以强制使用 HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// 设置 Permissions-Policy 头部以控制可以使用哪些功能和 API
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

这些可以添加到您的 `routes.php` 或 `index.php` 文件的顶部。

#### 作为过滤器添加

您还可以在过滤器/钩子中添加它们，如下所示：

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

#### 作为中间件添加

您还可以将它们作为中间件类添加，这样可以灵活控制将这些头部应用于哪些路由。通常，这些头部应应用于所有 HTML 和 API 响应。

骨架风格的路径和命名空间（**文件夹大小写与 `App\Middleware` 匹配**）：

```php
// app/Middleware/SecurityHeadersMiddleware.php

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
		// 当您有内联脚本时，优先使用来自 bootstrap 的 CSP nonce（骨架会设置 csp_nonce）
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

// app/config/routes.php — 空字符串分组 = 所有路由的全局中间件
use App\Middleware\SecurityHeadersMiddleware;
use flight\net\Router;

$router->group('', function (Router $router) {
	$router->get('/users', [ \App\Controller\UserController::class, 'getUsers' ]);
	// 更多路由
}, [SecurityHeadersMiddleware::class]);
```

较旧的项目可能仍使用 `app/middlewares` 和 `app\middlewares`；如果文件夹匹配，那是可行的。新的骨架应用使用 **`app/Middleware/`** 和 **`App\Middleware`**。请参阅 [自动加载](/learn/autoloading)。

### 跨站请求伪造（CSRF）

跨站请求伪造（CSRF）是一种攻击类型，恶意网站可以利用用户的浏览器向您的网站发送请求。这可以在用户不知情的情况下在您的网站上执行操作。Flight 没有提供内置的 CSRF 保护机制，但您可以通过使用中间件轻松实现自己的保护。

#### 设置

首先，您需要生成一个 CSRF 令牌并将其存储在用户会话中。然后可以在表单中使用此令牌，并在表单提交时对其进行检查。我们将使用 [flightphp/session](/awesome-plugins/session) 插件来管理会话。

```php
// 生成一个 CSRF 令牌并将其存储在用户会话中
// （假设您已经创建了一个会话对象并将其附加到 Flight）
// 有关更多信息，请参阅会话文档
Flight::register('session', flight\Session::class);

// 每个会话只需生成一个令牌（因此它
// 可跨多个标签页和同一用户的请求使用）
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

##### 使用 Twig（骨架默认）

注册一个 Twig 函数或将令牌传递给每个表单视图。以下是一个使用全局变量和表单字段的最小示例：

```php
// 配置 Twig 时（例如 services.php）
$twig->addGlobal('csrf_token', $app->session()->get('csrf_token'));
```

```html
{# app/views/form.twig #}
<form method="post">
	<input type="hidden" name="csrf_token" value="{{ csrf_token }}">
	{# 其他字段 #}
</form>
```

##### 使用 Latte

您还可以设置一个自定义函数来在 Latte 模板中输出 CSRF 令牌。

```php

Flight::map('render', function(string $template, array $data, ?string $block): void {
	$latte = new Latte\Engine;

	// 其他配置...

	// 设置一个自定义函数以输出 CSRF 令牌
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
	<!-- 其他表单字段 -->
</form>
```

#### 检查 CSRF 令牌

您可以使用多种方法检查 CSRF 令牌。

##### 中间件

```php
// app/Middleware/CsrfMiddleware.php

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
				$this->app->halt(403, 'Invalid CSRF token');
			}
		}
	}
}

// routes.php
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
			Flight::halt(403, 'Invalid CSRF token');
			// 或者用于 JSON 响应
			Flight::jsonHalt(['error' => 'Invalid CSRF token'], 403);
		}
	}
});
```

### 跨站脚本（XSS）

跨站脚本（XSS）是一种攻击类型，恶意的表单输入可以将代码注入您的网站。大多数此类机会来自最终用户填写的表单值。您**绝不应该**信任用户的输出！始终假设他们都是世界顶尖的黑客。他们可以向您的页面注入恶意的 JavaScript 或 HTML。这些代码可用于窃取用户的信息或在您的网站上执行操作。使用 Flight 的视图类或像 [Twig](/awesome-plugins/twig) 或 [Latte](/awesome-plugins/latte) 这样的模板引擎，您可以轻松地转义输出以防止 XSS 攻击。

```php
// 假设用户很聪明，试图将其用作他们的名字
$name = '<script>alert("XSS")</script>';

// 这将转义输出
Flight::view()->set('name', $name);
// 这将输出：&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// Twig（骨架默认）和 Latte 默认自动转义 — 相比原生的 PHP echo，更推荐使用它们
Flight::render('template', ['name' => $name]);
// Twig: {{ name }} → 已转义
// 除非内容完全可信，否则避免 |raw / 未转义的输出
```

### SQL 注入

SQL 注入是一种攻击类型，恶意用户可以向您的数据库注入 SQL 代码。这可以用来窃取数据库中的信息或在数据库上执行操作。同样，您**绝不应该**信任用户的输入！始终假设他们心怀不轨。使用准备语句——[SimplePdo](/learn/simple-pdo) 辅助函数使其成为默认路径。

```php
// 假设您已将 Flight::db() 注册为 SimplePdo（或在控制器中注入 SimplePdo）
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// SimplePdo（推荐）— 使用绑定参数的一行代码
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// 同样的思路，使用 ? 占位符
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);
```

在骨架风格的控制器中，更倾向于通过构造函数注入 `SimplePdo`，而不是使用 `Flight::db()`，以便测试和 AI 生成的代码保持一致（[DIC](/learn/dependency-injection-container)）。

#### 不安全示例

下面的内容说明了为什么我们要使用 SQL 准备语句来防范像下面这样的简单示例：

```php
// 最终用户填写一个 Web 表单。
// 对于表单的值，黑客输入了类似这样的内容：
$username = "' OR 1=1; -- ";

$sql = "SELECT * FROM users WHERE username = '$username' LIMIT 5";
$users = Flight::db()->fetchAll($sql);
// 查询构建完成后，它看起来像这样
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5

// 它看起来很奇怪，但这是一个有效的查询，可以工作。事实上，
// 这是一种非常常见的 SQL 注入攻击，会返回所有用户。

var_dump($users); // 这将转储数据库中的所有用户，而不仅仅是那一个用户名
```

### 机密与配置

- 将机密放在 **`.env`**（或真实环境中），而不是放在已提交的 `config.php` 示例中。
- 骨架规则：在 `config.php` 中使用字面量默认值；在 bootstrap 中合并环境变量；**不要**在控制器中读取 `$_ENV`——而是注入配置。请参阅 [配置](/learn/configuration)。
- 绝不要提交 API 密钥、数据库密码或会话加密密钥。将 AI 工具指向 **`SECURITY.md`**，以免它们编造不安全的捷径。

### JSONP 回调验证

如果您使用 Flight 的 `Flight::jsonp()` 方法，请注意 Flight 会针对严格的白名单正则表达式（`/^[A-Za-z_$][\w$.]{0,127}$/`）验证 JSONP 回调参数名称。任何不符合此模式的回调名称都会导致 Flight 抛出异常，从而防止通过恶意回调值注入任意 JavaScript。

此验证是内置的，无需额外配置，但在调试 JSONP 端点出现的意外错误时，值得了解这一点。

### CORS（跨源资源共享）

跨源资源共享（CORS）是一种机制，允许网页上的许多资源（例如字体、JavaScript 等）从资源来源域之外的其他域请求。Flight 没有内置功能，但可以通过在调用 `Flight::start()` 方法之前运行的钩子轻松处理。

```php
// app/Utils/CorsUtil.php（骨架：PascalCase 的 Utils 文件夹 → App\Utils）

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
		// 在此处自定义您允许的主机。
		$allowed = [
			'capacitor://localhost',
			'ionic://localhost',
			'http://localhost',
			'http://localhost:4200',
			'http://localhost:8080',
			'http://localhost:8100',
		];

		$request = $this->app->request();

		if (in_array($request->getVar('HTTP_ORIGIN'), $allowed, true) === true) {
			$response = $this->app->response();
			$response->header("Access-Control-Allow-Origin", $request->getVar('HTTP_ORIGIN'));
		}
	}
}

// bootstrap / 路由 — 在启动前运行
$app = Flight::app();
$cors = new \App\Utils\CorsUtil($app);
$app->before('start', [ $cors, 'set' ]);
```

### Flight 配置加固

Flight 公开了几个具有直接安全影响的引擎设置。正确设置这些是加固应用程序最简单的方法之一。

#### `flight.allow_method_override`

默认情况下，Flight 允许客户端使用 `X-HTTP-Method-Override` 头部或 POST 主体中的 `_method` 字段来覆盖请求的 HTTP 方法。虽然这对于只能发送 `GET`/`POST` 的 HTML 表单很方便，但如果您没有预料到这一点，它可能会很危险——攻击者可以通过一个普通表单伪造 `DELETE` 或 `PUT` 请求。

如果您的应用程序不依赖此行为（例如，您正在构建一个供现代客户端或 JavaScript 前端使用的 API，它们可以发送任何 HTTP 动词），则应禁用它：

```php
// 在您的 index.php 或 bootstrap 文件中，在 Flight::start() 之前
Flight::set('flight.allow_method_override', false);
```

默认值为 `true` 是为了向后兼容，但**强烈建议将其设置为 `false`**，适用于任何不明确需要覆盖功能的应用程序。

#### `flight.debug`

Flight 有一个 `flight.debug` 设置，用于控制当未处理的异常发生时，是否在浏览器中呈现详细的错误信息（异常消息、代码和完整的堆栈跟踪）。默认值为 `false`，这意味着只显示通用的 `500 Internal Server Error` 消息——不会向客户端泄露内部细节。

绝不要在生产服务器上启用此设置。仅在本地或暂存环境中使用：

```php
// 仅适合本地开发——绝不要在生产环境中使用
Flight::set('flight.debug', true);
```

当 `flight.debug` 为 `false`（默认值）时，您仍然可以通过启用 `flight.log_errors` 来捕获错误：

```php
// 在服务器端记录错误，而不将其暴露给客户端
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
```

#### `flight.views.restrict_to_path`

Flight 内置的 `View` 类会乐意地从绝对路径或从相对名称（例如 `../`）爬出 `flight.views.path` 来包含模板。这对于有意跨文件夹共享模板的应用程序是有意为之的，但如果模板名称来自不受信任的输入，这也是一种路径遍历风险。

`flight.views.restrict_to_path` 默认是**关闭的**，以便现有应用程序继续工作。除非您有文档化的理由不这样做，否则请将其打开：

```php
// 在您的 index.php 或 bootstrap 文件中，在 Flight::start() 之前
Flight::set('flight.views.restrict_to_path', true);
```

当视图被创建时，引擎会将该设置应用到 `View::$restrictToPath` 上（与 `flight.views.path` 和 `flight.views.extension` 的模式相同）。启用后：

- `render()` 和 `fetch()` 只包含真实路径位于配置的视图目录内的文件（指向外部的符号链接也会被拒绝）。
- `exists()` 对这些路径返回 `false`，而不是抛出异常。
- `getTemplate()` 本身不变——它仍然像以前一样返回路径。
- 被阻止的文件会抛出 `Template file is outside the views path.` 错误信息；缺失的文件仍然会抛出现有的 `Template file not found: ...` 信息。

如果您使用 Twig 或 Latte，并将它们自己的文件系统加载器指向您的视图目录，这些引擎已经将模板包含在该根目录内。仍然为 Flight 原生的 `View` 启用此设置，以便任何调用 `Flight::view()->render()` / `fetch()` 的代码都能获得相同的保护。官方的 [骨架](https://github.com/flightphp/skeleton) 在 bootstrap 中启用了它。

#### 推荐的生产环境配置

```php
// index.php 或从应用配置 / bootstrap 应用
Flight::set('flight.allow_method_override', false);
Flight::set('flight.debug', false);
Flight::set('flight.log_errors', true);
Flight::set('flight.views.restrict_to_path', true);
```

### 错误处理

在生产环境中隐藏敏感的错误详细信息，以避免向攻击者泄露信息。在生产环境中，使用 `display_errors` 设置为 `0` 来记录错误而不是显示它们。

```php
// 在您的 bootstrap.php 或 index.php 中

// 将此添加到您的 app/config/config.php
$environment = ENVIRONMENT;
if ($environment === 'production') {
    ini_set('display_errors', 0); // 禁用错误显示
    ini_set('log_errors', 1);     // 改为记录错误
    ini_set('error_log', '/path/to/error.log');
}

// 在您的路由或控制器中
// 使用 Flight::halt() 进行受控的错误响应
Flight::halt(403, 'Access denied');
```

### 输入清理

绝不要信任用户输入。在处理之前使用 [filter_var](https://www.php.net/manual/en/function.filter-var.php) 对其进行清理，以防止恶意数据潜入。在应用代码中，更倾向于通过 `$app->request()`（或 `Flight::request()`）读取输入，而不是使用原始的 `$_GET` / `$_POST`。

```php

// 假设有一个包含 $_POST['input'] 和 $_POST['email'] 的 $_POST 请求

// 清理字符串输入
$clean_input = filter_var(Flight::request()->data->input, FILTER_SANITIZE_STRING);
// 清理电子邮件
$clean_email = filter_var(Flight::request()->data->email, FILTER_SANITIZE_EMAIL);
```

### 密码哈希

使用 PHP 的内置函数（如 [password_hash](https://www.php.net/manual/en/function.password-hash.php) 和 [password_verify](https://www.php.net/manual/en/function.password-verify.php)）安全地存储密码并安全地验证密码。密码绝不应以纯文本形式存储，也不应使用可逆的方法进行加密。哈希确保即使您的数据库被入侵，实际密码仍然受到保护。

```php
$password = Flight::request()->data->password;
// 在存储时对密码进行哈希（例如注册时）
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 验证密码（例如登录时）
if (password_verify($password, $stored_hash)) {
    // 密码匹配
}
```

### 速率限制

通过使用缓存限制请求速率，防止暴力破解攻击或拒绝服务攻击。

```php
// 假设您已安装并注册了 flightphp/cache
// 在过滤器中使用 flightphp/cache
Flight::before('start', function() {
    $cache = Flight::cache();
    $ip = Flight::request()->ip;
    $key = "rate_limit_{$ip}";
    $attempts = (int) $cache->retrieve($key);
    
    if ($attempts >= 10) {
        Flight::halt(429, 'Too many requests');
    }
    
    $cache->set($key, $attempts + 1, 60); // 60 秒后重置
});
```

## 另请参阅

- [会话](/awesome-plugins/session) - 如何安全地管理用户会话。
- [模板](/learn/templates) - Twig/Latte 自动转义和 XSS。
- [SimplePdo](/learn/simple-pdo) - 带有准备语句的数据库辅助函数。
- [PdoWrapper](/learn/pdo-wrapper) - 已弃用；新代码请使用 SimplePdo。
- [中间件](/learn/middleware) - 如何使用中间件来简化添加安全头部的过程。
- [配置](/learn/configuration) - `.env` 与字面量配置、生产环境标志。
- [AI 与开发者体验](/learn/ai) - 将安全策略保存在 `SECURITY.md` 中以供代理使用。
- [响应](/learn/responses) - 如何使用安全头部自定义 HTTP 响应。
- [请求](/learn/requests) - 如何处理和清理用户输入。
- [filter_var](https://www.php.net/manual/en/function.filter-var.php) - 用于输入清理的 PHP 函数。
- [password_hash](https://www.php.net/manual/en/function.password-hash.php) - 用于安全密码哈希的 PHP 函数。
- [password_verify](https://www.php.net/manual/en/function.password-verify.php) - 用于验证哈希密码的 PHP 函数。

## 故障排除

- 有关 Flight 框架组件相关问题的故障排除信息，请参阅上面的“另请参阅”部分。
- 如果 CSP 阻止了您的脚本，请添加一个 nonce（骨架模式）或将特定来源加入白名单——不要在没有计划的情况下设置 `script-src *`。

## 更新日志

- 文档 – 骨架 `App\Middleware`、Twig CSRF/XSS 说明、SimplePdo、机密/`.env`，以及面向 AI 友好项目的 `SECURITY.md`。
- 文档 – 在 Flight 配置加固下记录了 `flight.views.restrict_to_path`（为原生视图提供可选路径包含限制）。
- v3.18.1 - 新增 Flight 配置加固部分，涵盖 `flight.allow_method_override`、`flight.debug` 和 JSONP 回调验证。
- v3.1.0 - 新增关于 CORS、错误处理、输入清理、密码哈希和速率限制的部分。
- v2.0 - 为默认视图添加了转义以防止 XSS。