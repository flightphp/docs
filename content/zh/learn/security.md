# 安全

安全在Web应用程序中非常重要。您希望确保您的应用程序是安全的，并且您的用户数据是安全的。Flight提供了许多功能来帮助您保护您的Web应用程序。

## 跨站请求伪造（CSRF）

跨站请求伪造（CSRF）是一种攻击类型，恶意网站可以让用户的浏览器向您的网站发送请求。这可以用来在用户不知情的情况下在您的网站上执行操作。Flight不提供内置的CSRF防护机制，但您可以通过使用中间件轻松实现自己的防护机制。

这里是如何使用事件过滤器实现CSRF防护的示例:

```php

// 此中间件检查请求是否为POST请求，如果是，则检查CSRF令牌是否有效
Flight::before('start', function() {
	if(Flight::request()->method == 'POST') {

		// 从表单值中捕获csrf令牌
		$token = Flight::request()->data->csrf_token;
		if($token != $_SESSION['csrf_token']) {
			Flight::halt(403, 'Invalid CSRF token');
		}
	}
});
```

## 跨站脚本（XSS）

跨站脚本（XSS）是一种攻击类型，恶意网站可以向您的网站注入代码。大多数机会来自您的最终用户将填写的表单值。您绝对不能相信用户的输出！始终假定他们都是世界上最好的黑客。他们可以向您的页面注入恶意JavaScript或HTML。此代码可用于从您的用户那里窃取信息或在您的网站上执行操作。使用Flight的视图类，您可以轻松地转义输出以防止XSS攻击。

```php

// 让我们假设用户很聪明，尝试将此用作他们的名字
$name = '<script>alert("XSS")</script>';

// 这将转义输出
Flight::view()->set('name', $name);
// 这将输出: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// 如果您使用像被注册为视图类的Latte这样的东西，它也会自动转义此内容。
Flight::view()->render('template', ['name' => $name]);
```

## SQL注入

SQL注入是一种恶意用户可以向您的数据库中注入SQL代码的攻击类型。这可以用于从您的数据库中窃取信息或执行数据库上的操作。同样，您绝对不能相信用户的输入！始终假定他们嗅觉灵敏。您可以在`PDO`对象中使用准备好的语句来防止SQL注入。

```php

// 假设您已经将Flight::db()注册为您的PDO对象
$statement = Flight::db()->prepare('SELECT * FROM users WHERE username = :username');
$statement->execute([':username' => $username]);
$users = $statement->fetchAll();

// 如果您使用了PdoWrapper类，这可以很容易地在一行中完成
$users = Flight::db()->fetchAll('SELECT * FROM users WHERE username = :username', [ 'username' => $username ]);

// 您可以使用带有?占位符的PDO对象执行相同的操作
$statement = Flight::db()->fetchAll('SELECT * FROM users WHERE username = ?', [ $username ]);

// 只承诺您永远不要做这样的事情...
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE username = '{$username}'");
// 因为如果 $username = "' OR 1=1;"; 在构建查询后看起来像这样
// SELECT * FROM users WHERE username = '' OR 1=1;
// 这看起来很奇怪，但是它是一个有效的查询，将起作用。实际上，
// 这是一个非常常见的SQL注入攻击，会返回所有用户。
```

## 跨源资源共享（CORS）

跨源资源共享（CORS）是一种机制，允许从与资源来源域不同的另一个域请求网页上许多资源（例如，字体，JavaScript等）。Flight没有内置功能，但可以通过类似于CSRF的中间件或事件过滤器轻松处理此问题。

```php

Flight::route('/users', function() {
	$users = Flight::db()->fetchAll('SELECT * FROM users');
	Flight::json($users);
})->addMiddleware(function() {
	if (isset($_SERVER['HTTP_ORIGIN'])) {
		header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		header('Access-Control-Allow-Credentials: true');
		header('Access-Control-Max-Age: 86400');
	}

	if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
		if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
			header(
				'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS'
			);
		}
		if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
			header(
				"Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}"
			);
		}
		exit(0);
	}
});
```

## 结论

安全很重要，确保您的Web应用程序是安全的至关重要。Flight提供了许多功能来帮助您保护您的Web应用程序，但重要的是要始终保持警惕，确保尽一切努力保护用户数据的安全。始终假定情况最糟糕，并且绝不相信用户的输入。始终转义输出并使用准备好的语句来防止SQL注入。始终使用中间件来保护您的路由免受CSRF和CORS攻击。如果您做到所有这些，您就能够构建安全的Web应用程序。