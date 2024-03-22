# 安全性

安全性是涉及 Web 应用程序时的重要问题。您希望确保您的应用程序是安全的，并且您的用户数据是安全的。Flight 提供了许多功能来帮助您保护您的 Web 应用程序。

## 标头

HTTP 标头是保护您的 Web 应用程序的一种最简单的方式之一。您可以使用标头来防止点击劫持、XSS 和其他攻击。有几种方法可以将这些标头添加到应用程序中。

可检查您的标头安全性的两个很好的网站是 [securityheaders.com](https://securityheaders.com/) 和 [observatory.mozilla.org](https://observatory.mozilla.org/)。

### 手动添加

您可以通过在 `Flight\Response` 对象上使用 `header` 方法手动添加这些标头。
```php
// 设置 X-Frame-Options 标头以防止点击劫持
Flight::response()->header('X-Frame-Options', 'SAMEORIGIN');

// 设置 Content-Security-Policy 标头以防止 XSS
// 注意：此标头可能非常复杂，因此您需要在互联网上查找应用程序的示例
Flight::response()->header("Content-Security-Policy", "default-src 'self'");

// 设置 X-XSS-Protection 标头以防止 XSS
Flight::response()->header('X-XSS-Protection', '1; mode=block');

// 设置 X-Content-Type-Options 标头以防止 MIME 嗅探
Flight::response()->header('X-Content-Type-Options', 'nosniff');

// 设置 Referrer-Policy 标头来控制发送多少引用者信息
Flight::response()->header('Referrer-Policy', 'no-referrer-when-downgrade');

// 设置 Strict-Transport-Security 标头以强制使用 HTTPS
Flight::response()->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

// 设置 Permissions-Policy 标头以控制可以使用哪些功能和 API
Flight::response()->header('Permissions-Policy', 'geolocation=()');
```

这些可以添加到您的 `bootstrap.php` 或 `index.php` 文件的顶部。

### 作为过滤器添加

您还可以在过滤器/钩子中添加它们，如下所示：

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

您还可以将它们作为中间件类添加。这是保持代码整洁和有组织的好方法。

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
// FYI，这个空字符串组充当全局中间件，以保护所有路由。当然，您也可以为特定路由执行同样的操作。
Flight::group('', function(Router $router) {
	$router->get('/users', [ 'UserController', 'getUsers' ]);
	// 更多路由
}, [ new SecurityHeadersMiddleware() ]);