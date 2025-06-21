Tracy Flight Panel Extensions
=====

这是一个扩展集，用于使与 Flight 的工作更丰富。

- Flight - 分析所有 Flight 变量。
- Database - 分析页面上运行的所有查询（如果正确启动数据库连接）。
- Request - 分析所有 `$_SERVER` 变量，并检查所有全局有效负载（`$_GET`、`$_POST`、`$_FILES`）。
- Session - 如果会话处于活动状态，则分析所有 `$_SESSION` 变量。

这是面板

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

每个面板都显示了关于您的应用程序的非常有帮助的信息！

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

点击[这里](https://github.com/flightphp/tracy-extensions)查看代码。

Installation
-------
运行 `composer require flightphp/tracy-extensions --dev`，然后您就可以开始了！

Configuration
-------
要启动此功能，您需要的配置非常少。您需要先启动 Tracy 调试器：[https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide)：

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// 引导代码
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// 您可能需要使用 Debugger::enable(Debugger::DEVELOPMENT) 指定您的环境

// 如果在您的应用程序中使用数据库连接，则需要一个
// 仅在开发环境中使用的必需 PDO 包装器（请不要在生产环境中使用！）
// 它的参数与常规 PDO 连接相同
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// 或者如果您将其附加到 Flight 框架
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// 现在每当您进行查询时，它都会捕获时间、查询和参数

// 这连接了点
if(Debugger::$showBar === true) {
	// 这需要为 false，否则 Tracy 无法正常渲染：(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// 更多代码

Flight::start();
```

## Additional Configuration

### Session Data
如果您有一个自定义会话处理程序（如 ghostff/session），您可以将任何会话数据数组传递给 Tracy，它会自动为您输出。您通过 `TracyExtensionLoader` 构造函数的第二个参数中的 `session_data` 键传递它。

```php

use Ghostff\Session\Session;
// 或使用 flight\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// 这需要为 false，否则 Tracy 无法正常渲染：(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// 路由和其他内容...

Flight::start();
```

### Latte

如果您的项目中安装了 Latte，您可以使用 Latte 面板来分析您的模板。您可以将 Latte 实例通过 `TracyExtensionLoader` 构造函数的第二个参数中的 `latte` 键传递进去。

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// 这是您添加 Latte 面板到 Tracy 的地方
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// 这需要为 false，否则 Tracy 无法正常渲染：(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
```