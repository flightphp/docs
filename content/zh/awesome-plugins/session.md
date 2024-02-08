# Ghostff/Session

PHP会话管理器（非阻塞，闪存，分段，会话加密）。 使用PHP open_ssl可选加密/解密会话数据。 支持文件，MySQL，Redis和Memcached。

## 安装

使用composer安装。

```bash
composer require ghostff/session
```

## 基本配置

您无需传递任何内容即可使用默认会话设置。 您可以在[Github Readme](https://github.com/Ghostff/Session)中阅读更多设置。

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// 需要记住的一件事是，您必须在每个页面加载时提交您的会话
// 否则，您将需要在配置中运行auto_commit。
```

## 简单示例

这是您可能如何使用这个的一个简单示例。

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// 在这里执行您的登录逻辑
	// 验证密码等。

	// 如果登录成功
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// 每次写入会话时，您必须有意识地提交它。
	$session->commit();
});

// 此检查可以在受限页面逻辑中进行，或者使用中间件包装。
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

这是您可能如何使用这个的一个更复杂的示例。

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// 设置自定义路径到您的会话配置文件，并为会话id提供一个随机字符串
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// 或者您可以手动覆盖配置选项
		$session->updateConfiguration([
			// 如果您想要在数据库中存储会话数据（如果您想要"使我在所有设备上注销"功能之类的东西）
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // 请更改为其他内容
			Session::CONFIG_AUTO_COMMIT   => true, // 只有在需要提交会话时才这样做，和/或者很难commit()您的会话。
												// 此外，您可以做 Flight::after('start', function() { Flight::session()->commit(); });
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # PDO dns的数据库驱动程序，例如(mysql:host=...;dbname=...)
				'host'      => '127.0.0.1',         # 数据库主机
				'db_name'   => 'my_app_database',   # 数据库名称
				'db_table'  => 'sessions',          # 数据库表
				'db_user'   => 'root',              # 数据库用户名
				'db_pass'   => '',                  # 数据库密码
				'persistent_conn'=> false,          # 避免每次脚本需要与数据库通信时建立新连接的开销，从而使网页应用更快。自行寻找缺点
			]
		]);
	}
);
```

## 文档

访问[GitHub Readme](https://github.com/Ghostff/Session)获取完整文档。 默认_config.php文件中的配置选项[有很好的文档](https://github.com/Ghostff/Session/blob/master/src/default_config.php)。 如果您想自行查看此包，代码很容易理解。