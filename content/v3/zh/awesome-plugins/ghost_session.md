# Ghostff/Session

PHP 会话管理器（非阻塞、闪存、分段、会话加密）。使用 PHP open_ssl 进行可选的会话数据加密/解密。支持 File、MySQL、Redis 和 Memcached。

点击[here](https://github.com/Ghostff/Session)查看代码。

## 安装

使用 composer 安装。

```bash
composer require ghostff/session
```

## 基本配置

您不需要传递任何内容来使用默认设置进行会话。您可以在 [Github Readme](https://github.com/Ghostff/Session) 中阅读更多设置。

```php
use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// 记住的一点是，您必须在每个页面加载时提交您的会话
// 否则，您需要在您的配置中运行 auto_commit。
```

## 简单示例

这是一个简单示例，展示您如何使用它。

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// 在这里执行您的登录逻辑
	// 验证密码等。

	// 如果登录成功
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// 每次写入会话时，您必须 deliberate 提交它。
	$session->commit();
});

// 这个检查可以放在受限页面逻辑中，或者用中间件包装。
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// 在这里执行您的受限页面逻辑
});

// 中间件版本
Flight::route('/some-restricted-page', function() {
	// 常规页面逻辑
})->addMiddleware(function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}
});
```

## 更复杂的示例

这是一个更复杂的示例，展示您如何使用它。

```php
use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// 将自定义路径设置为您的会话配置文件作为第一个参数
// 或者提供自定义数组
$app->register('session', Session::class, [ 
	[
		// 如果您想将会话数据存储在数据库中（如果您想要类似“从所有设备登出”功能）
		Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
		Session::CONFIG_ENCRYPT_DATA  => true,
		Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // 请将此更改为其他内容
		Session::CONFIG_AUTO_COMMIT   => true, // 仅在需要时或难以提交()您的会话时才这样做。
												// 另外，您可以执行 Flight::after('start', function() { Flight::session()->commit(); });
		Session::CONFIG_MYSQL_DS         => [
			'driver'    => 'mysql',             # 数据库驱动程序，用于 PDO dns，例如(mysql:host=...;dbname=...)
			'host'      => '127.0.0.1',         # 数据库主机
			'db_name'   => 'my_app_database',   # 数据库名称
			'db_table'  => 'sessions',          # 数据库表
			'db_user'   => 'root',              # 数据库用户名
			'db_pass'   => '',                  # 数据库密码
			'persistent_conn'=> false,          # 避免每次脚本需要与数据库通信时建立新连接，从而使 Web 应用程序更快。自己找到缺点
		]
	] 
]);
```

## 帮助！我的会话数据没有持久化！

您设置了会话数据，但它在请求之间没有持久化？您可能忘记了提交会话数据。您可以通过在设置会话数据后调用 `$session->commit()` 来实现。

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// 在这里执行您的登录逻辑
	// 验证密码等。

	// 如果登录成功
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// 每次写入会话时，您必须 deliberate 提交它。
	$session->commit();
});
```

另一种方法是，当您设置会话服务时，在您的配置中将 `auto_commit` 设置为 `true`。这将在每个请求后自动提交您的会话数据。

```php
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

另外，您可以执行 `Flight::after('start', function() { Flight::session()->commit(); });` 以在每个请求后提交会话数据。

## 文档

访问 [Github Readme](https://github.com/Ghostff/Session) 以获取完整文档。配置选项在 [default_config.php 文件中进行了很好的文档记录](https://github.com/Ghostff/Session/blob/master/src/default_config.php)。如果您想自己查看这个包，代码很容易理解。