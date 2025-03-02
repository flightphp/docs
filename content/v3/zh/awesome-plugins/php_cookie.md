# Cookies

[overclokk/cookie](https://github.com/overclokk/cookie) 是一个简单的库，用于在应用程序中管理 cookie。

## 安装

使用 composer 安装很简单。

```bash
composer require overclokk/cookie
```

## 用法

使用方法就是在 Flight 类中注册一个新方法。

```php
use Overclokk\Cookie\Cookie;

/*
 * 在您的引导文件或 public/index.php 文件中设置
 */

Flight::register('cookie', Cookie::class);

/**
 * ExampleController.php
 */

class ExampleController {
	public function login() {
		// 设置一个 cookie

		// 想要将其设置为 false，以便获得一个新的实例
		// 如果您想要自动完成，请使用下面的注释
		/** @var \Overclokk\Cookie\Cookie $cookie */
		$cookie = Flight::cookie(false);
		$cookie->set(
			'stay_logged_in', // cookie 的名称
			'1', // 您想设置的值
			86400, // cookie 应持续的秒数
			'/', // 可以访问到 cookie 的路径
			'example.com', // 可以访问到 cookie 的域
			true, // cookie 只会通过安全的 HTTPS 连接传输
			true // cookie 只能通过 HTTP 协议访问
		);

		// 可选地，如果您希望保留默认值，并且希望以更长时间设置 cookie
		$cookie->forever('stay_logged_in', '1');
	}

	public function home() {
		// 检查您是否拥有该 cookie
		if (Flight::cookie()->has('stay_logged_in')) {
			// 将用户放在例如仪表板区域。
			Flight::redirect('/dashboard');
		}
	}
}