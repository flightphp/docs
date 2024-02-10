# 安全

在涉及网络应用程序时，安全是一件大事。您需要确保您的应用程序是安全的，用户的数据是安全的。Flight提供了许多功能来帮助您保护您的网络应用程序。

## 跨站请求伪造（CSRF）

跨站请求伪造（CSRF）是一种攻击类型，恶意网站可以让用户的浏览器发送请求到您的网站。这可以用来在用户不知情的情况下在您的网站上执行操作。Flight不提供内置CSRF保护机制，但您可以通过使用中间件轻松实现自己的保护机制。

首先，您需要生成一个CSRF令牌并将其存储在用户会话中。然后可以在表单中使用此令牌，并在提交表单时进行检查。

```php
// 生成一个CSRF令牌并将其存储在用户会话中
// （假设您已经创建了一个会话对象并将其附加到Flight）
Flight::session()->set('csrf_token', bin2hex(random_bytes(32)) );
```

```html
<!-- 在您的表单中使用CSRF令牌 -->
<form method="post">
	<input type="hidden" name="csrf_token" value="<?= Flight::session()->get('csrf_token') ?>">
	<!-- 其他表单字段 -->
</form>
```

然后，您可以使用事件过滤器来检查CSRF令牌：

```php
// 此中间件检查请求是否为POST请求，如果是，则检查CSRF令牌是否有效
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// 从表单值中捕获CSRF令牌
		$token = Flight::request()->data->csrf_token;
		if($token !== Flight::session()->get('csrf_token')) {
			Flight::halt(403, 'Invalid CSRF token');
		}
	}
});
```

## 跨站脚本（XSS）

跨站脚本（XSS）是一种攻击类型，恶意网站可以向您的网站注入代码。大多数机会来自用户填写的表单值。永远不要信任用户的输出！始终假定他们都是世界上最好的黑客。他们可以将恶意JavaScript或HTML注入到您的页面中。此代码可用于从用户那里窃取信息或在您的网站上执行操作。使用Flight的视图类，您可以轻松地转义输出以防止XSS攻击。

```php

// 让我们假设用户很聪明，尝试将此作为他们的名字
$name = '<script>alert("XSS")</script>';

// 这将转义输出
Flight::view()->set('name', $name);
// 这将输出：&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// 如果您使用像Latte这样的视图类，它也会自动转义这些。
Flight::view()->render('template', ['name' => $name]);
```

## SQL注入

SQL注入是一种攻击类型，恶意用户可以向数据库中注入SQL代码。这可以用于从数据库中窃取信息或在数据库上执行操作。再次强调，永远不要信任用户输入！始终假定他们是为了利益而来。您可以在`PDO`对象中使用预处理语句来防止SQL注入。

```php

// 假设您已经将Flight::db()注册为您的PDO对象
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// 如果您使用了PdoWrapper类，则可以轻松地在一行中完成此操作
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// 您也可以使用带有?占位符的PDO对象执行相同的操作
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// 只是保证您永远不要像这样做...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}' LIMIT 5");
// 因为如果 $username = "' OR 1=1; -- "; 在构建查询后会变成
// SELECT * FROM users WHERE username = '' OR 1=1; -- LIMIT 5
// 看起来很奇怪，但这是一个有效的查询，可以正常工作。事实上，
// 这是一个非常常见的SQL注入攻击，可以返回所有用户。
```

## 跨源资源共享（CORS）

跨源资源共享（CORS）是一种允许从另一个域外请求许多资源（例如字体，JavaScript等）的机制。Flight没有内置功能，但可以通过类似于CSRF中间件或事件过滤器轻松处理此问题。

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

// index.php或您设置路由的任何地方
Flight::route('/users', function() {
	$users = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json($users);
})->addMiddleware(new CorsMiddleware());
```

## 结论

安全性非常重要，确保您的网络应用程序是安全的是至关重要的。Flight提供了许多功能来帮助您保护您的网络应用程序，但重要的是始终保持警惕，并确保尽一切可能保护用户数据的安全。始终假定最坏的情况，并且不要相信用户的输入。始终转义输出并使用预处理语句来防止SQL注入。始终使用中间件来保护您的路由免受CSRF和CORS攻击。如果您做到了这些，您将为构建安全的网络应用程序走上正道。