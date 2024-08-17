# 鬼/会话

PHP 会话管理器（非阻塞、闪存、分段、会话加密）。 使用 PHP open_ssl 可选择加密/解密会话数据。支持文件、MySQL、Redis 和 Memcached。

单击[这里](https://github.com/Ghostff/Session)查看代码。

## 安装

使用composer安装。

```bash
composer require ghostff/session
```

## 基本配置

您不需要传入任何内容即可使用默认设置与您的会话。您可以在[GitHub Readme](https://github.com/Ghostff/Session)中阅读更多设置。

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// 有一件事要记住，那就是每次加载页面都必须提交您的会话
// 或者您需要在配置中运行 auto_commit。
```

## 简单示例

以下是您可能如何使用此程序的简单示例。

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// 在此执行登录逻辑
	// 验证密码等。

	// 如果登录成功
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// 每次写入会话时，必须有意识地提交它。
	$session->commit();
});

// 此检查可以在受限页面逻辑中，或用中间件包装后执行。
Flight::route('/一些受限页面', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// 在此执行受限页面逻辑
});

// 中间件版本
Flight::route('/一些受限页面', function() {
	// 常规页面逻辑
})->addMiddleware(function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}
});
```

## 更复杂的示例

以下是您可能如何使用此程序的更复杂示例。

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// 将自定义路径设置为会话配置文件，并为会话ID设置一个随机字符串
$app->register('session', Session::class, ['path/to/session_config.php', bin2hex(random_bytes(32))], function(Session $session) {
		// 或者您可以手动覆盖配置选项
		$session->updateConfiguration([
			// 如果您希望将会话数据存储在数据库中（如果您想要像“注销所有设备”功能之类的功能）
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // 请将其更改为其他内容
			Session::CONFIG_AUTO_COMMIT   => true, // 只在需要时才执行此操作，否则很难 commit() 您的会话。
												   // 另外，您可以执行 Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # 用于PDO dns的数据库驱动程序，例如（mysql:host=...;dbname=...）
				'host'      => '127.0.0.1',         # 数据库主机
				'db_name'   => 'my_app_database',   # 数据库名称
				'db_table'  => 'sessions',          # 数据库表
				'db_user'   => 'root',              # 数据库用户名
				'db_pass'   => '',                  # 数据库密码
				'persistent_conn'=> false,          # 避免每次脚本需要与数据库交谈时建立新连接的开销，从而使web应用程序更快。找到自己的缺点
			]
		]);
	}
);
```

## 帮助！我的会话数据没有持久化！

您是否设置了会话数据，但在请求之间没有持续保留？您可能忘记提交会话数据。您可以在设置会话数据后调用 `$session->commit()` 完成此操作。

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// 在此执行登录逻辑
	// 验证密码等。

	// 如果登录成功
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// 每次写入会话时，必须有意识地提交它。
	$session->commit();
});
```

解决此问题的另一种方法是，在设置会话服务时，在您的配置中将 `auto_commit` 设置为 `true`。 这将在每次请求之后自动提交您的会话数据。

```php

$app->register('session', Session::class, ['path/to/session_config.php', bin2hex(random_bytes(32))], function(Session $session) {
		$session->updateConfiguration([
			Session::CONFIG_AUTO_COMMIT   => true,
		]);
	}
);
```

另外，您可以执行 `Flight::after('start', function() { Flight::session()->commit(); });` 在每次请求之后提交会话数据。

## 文档

访问[GitHub Readme](https://github.com/Ghostff/Session)获取完整文档。 如果您想自己查看这个包，那么默认_config.php 文件中的配置选项都[有很好的文档] (https://github.com/Ghostff/Session/blob/master/src/default_config.php)。 代码简单易懂。