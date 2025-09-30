Tracy Flight 面板扩展
=====

这是一个扩展集，用于使与 Flight 的协作更加丰富。

- Flight - 分析所有 Flight 变量。
- Database - 分析页面上运行的所有查询（如果您正确初始化数据库连接）。
- Request - 分析所有 `$_SERVER` 变量并检查所有全局负载（`$_GET`、`$_POST`、`$_FILES`）。
- Session - 如果会话处于活动状态，则分析所有 `$_SESSION` 变量。

这是面板

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

每个面板都会显示有关您的应用程序的非常有用的信息！

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

点击[这里](https://github.com/flightphp/tracy-extensions)查看代码。

安装
-------
运行 `composer require flightphp/tracy-extensions --dev`，您就上路了！

配置
-------
要开始使用此扩展，几乎不需要进行任何配置。您需要在使用此扩展之前初始化 Tracy 调试器 [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide)：

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// bootstrap code
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// 您可能需要使用 Debugger::enable(Debugger::DEVELOPMENT) 指定您的环境

// 如果您的应用程序中使用数据库连接，则有一个
// 必需的 PDO 包装器，仅用于开发（请勿用于生产！）
// 它与常规 PDO 连接具有相同的参数
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// 或者如果您将其附加到 Flight 框架
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// 现在每当您执行查询时，它都会捕获时间、查询和参数

// 这将连接各个部分
if(Debugger::$showBar === true) {
	// 这需要设置为 false，否则 Tracy 无法实际渲染 :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// more code

Flight::start();
```

## 附加配置

### 会话数据
如果您有自定义会话处理程序（例如 ghostff/session），您可以将会话数据的任何数组传递给 Tracy，它将自动为您输出。您可以通过 `TracyExtensionLoader` 构造函数的第二个参数中的 `session_data` 键来传递它。

```php

use Ghostff\Session\Session;
// 或使用 flight\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// 这需要设置为 false，否则 Tracy 无法实际渲染 :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// routes and other things...

Flight::start();
```

### Latte

_本节要求 PHP 8.1+。_

如果您的项目中安装了 Latte，则 Tracy 与 Latte 有原生集成，用于分析您的模板。您只需将扩展注册到您的 Latte 实例中。

```php

require 'vendor/autoload.php';

$app = Flight::app();

$app->map('render', function($template, $data, $block = null) {
	$latte = new Latte\Engine;

	// other configurations...

	// 仅在启用 Tracy 调试栏时添加扩展
	if(Debugger::$showBar === true) {
		// 这就是您将 Latte 面板添加到 Tracy 的地方
		$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
	}

	$latte->render($template, $data, $block);
});
```