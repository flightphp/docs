# Ghostff/Session

PHP 会话管理器（非阻塞、闪存、分段、会话加密）。 使用 PHP open_ssl 可选加密/解密会话数据。 支持文件、MySQL、Redis 和 Memcached。

## 安装

使用 composer 进行安装。

```bash
composer require ghostff/session
```

## 基本配置

您不需要传入任何内容即可使用默认设置与会话。 您可以在 [Github Readme](https://github.com/Ghostff/Session) 中阅读更多有关设置的信息。

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// 请记住的一件事是您必须在每个页面加载时提交您的会话
// 或者您需要在配置中运行 auto_commit。
```

## 简单示例

这是您可能如何使用此功能的简单示例。

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// 在这里执行登录逻辑
	// 验证密码等

	// 如果登录成功
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// 每次写入会话时，都必须有意识地提交。
	$session->commit();
});

// 此检查可以在受限页面逻辑中，或在中间层包装中。
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// 在这里执行受限页面逻辑
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

这是您可能如何使用此功能的更复杂示例。

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// 将一个自定义路径设置为会话配置文件，并为会话ID提供一个随机字符串
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// 或者，您可以手动覆盖配置选项
		$session->updateConfiguration([
			// 如果要将会话数据存储在数据库中（如果您希望实现诸如“注销所有设备”功能之类的功能，则很好）
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // 请将此更改为其他内容
			Session::CONFIG_AUTO_COMMIT   => true, // 只有在需要时才这样做，和/或难以提交()您的会话
												   // 另外，您可以这样做 Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # PDO dns 的数据库驱动程序，例如(mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # 数据库主机
				'db_name'   => 'my_app_database',   # 数据库名
				'db_table'  => 'sessions',          # 数据表
				'db_user'   => 'root',              # 数据库用户名
				'db_pass'   => '',                  # 数据库密码
				'persistent_conn'=> false,          # 避免每次脚本需要与数据库通信时都建立新连接的开销，从而加快Web应用程序的速度。自己找后台
			]
		]);
	}
);
```

## 帮助！我的会话数据未持久保存！

您设置了会话数据，但在请求之间未持久保留？ 您可能忘记提交会话数据。 您可以在设置完会话数据后调用 `$session->commit()` 来执行此操作。

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// 在这里执行登录逻辑
	// 验证密码等

	// 如果登录成功
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// 每次写入会话时，都必须有意识地提交。
	$session->commit();
});
```

解决此问题的另一种方法是，在设置会话服务时，在配置中将 `auto_commit` 设置为 `true`。 这将在每个请求后自动提交会话数据。

```php

$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

此外，您可以执行 `Flight::after('start', function() { Flight::session()->commit(); });` 来在每个请求后提交您的会话数据。

## 文档

查看 [Github Readme](https://github.com/Ghostff/Session) 以获取完整文档。 配置选项在 [default_config.php](https://github.com/Ghostff/Session/blob/master/src/default_config.php) 文件中有很好的文档。 如果您希望自己查看此软件包，代码很容易理解。