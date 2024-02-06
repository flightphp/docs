# Ghostff/Session

PHP 会话管理器（非阻塞，闪存，分段，会话加密）。 使用 PHP open_ssl 可选对会话数据进行加密/解密。 支持文件、MySQL、Redis 和 Memcached。

## 安装

使用 composer 进行安装。

```bash
composer require ghostff/session
```

## 基本配置

您不需要传入任何内容即可使用默认设置与您的会话。 您可以在[Github自述文件](https://github.com/Ghostff/Session)中阅读有关更多设置的信息。

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

// 有一点需要记住的是，您必须在每个页面加载时提交您的会话
// 或者您需要在配置中运行自动提交。
```

## 简单示例

这是您可能会使用此功能的简单示例。

```php
Flight::route('POST /login', function() {
	$session = Flight::session();

	// 在此处执行登录逻辑
	// 验证密码等。

	// 如果登录成功
	$session->set('is_logged_in', true);
	$session->set('user', $user);

	// 每次写入会话时，您必须明确提交它。
	$session->commit();
});

// 此检查可以在受限制页面逻辑中，或在中间件包装中进行。
Flight::route('/some-restricted-page', function() {
	$session = Flight::session();

	if(!$session->get('is_logged_in')) {
		Flight::redirect('/login');
	}

	// 在此处执行受限制页面逻辑
});

// 带有中间件的版本
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

这是您可能会使用此功能的更复杂示例。

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

// 将自定义路径设置为您的会话配置文件，并为会话ID提供一个随机字符串
$app->register('session', Session::class, [ 'path/to/session_config.php', bin2hex(random_bytes(32)) ], function(Session $session) {
		// 或者您可以手动覆盖配置选项
		$session->updateConfiguration([
			// 如果您希望将会话数据存储在数据库中（如果您希望实现类似“注销所有设备”的功能，这很好）
			Session::CONFIG_DRIVER        => Ghostff\Session\Drivers\MySql::class,
			Session::CONFIG_ENCRYPT_DATA  => true,
			Session::CONFIG_SALT_KEY      => hash('sha256', 'my-super-S3CR3T-salt'), // 请将此更改为其他内容
			Session::CONFIG_AUTO_COMMIT   => true, // 仅当需要提交，或者很难提交()您的会话时才这样做。
												//此外，您可以执行 Flight::after('start', function() { Flight::session()->commit(); }); 
			Session::CONFIG_MYSQL_DS         => [
				'driver'    => 'mysql',             # PDO dns 的数据库驱动程序，例如（mysql:host=...;dbname=...）
				'host'      => '127.0.0.1',         # 数据库主机
				'db_name'   => 'my_app_database',   # 数据库名称
				'db_table'  => 'sessions',          # 数据库表
				'db_user'   => 'root',              # 数据库用户名
				'db_pass'   => '',                  # 数据库密码
				'persistent_conn'=> false,          # 避免在每次脚本需要与数据库通信时建立一个新连接的开销，从而加快网页应用的速度。自行寻找背面
			]
		]);
	}
);
```

## 文档

查看[Github自述文件](https://github.com/Ghostff/Session)以获取完整文档。 配置选项在[默认配置文件](https://github.com/Ghostff/Session/blob/master/src/default_config.php) 中有详细说明。 如果您想自己查看此包，代码很容易理解。