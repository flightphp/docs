## Tracy Flight Panel Extensions
=====

这是一组扩展，可以让与 Flight 的工作变得更加丰富。

- Flight - 分析所有 Flight 变量。
- Database - 分析在页面上运行的所有查询（如果您正确初始化了数据库连接）
- Request - 分析所有 `$_SERVER` 变量，并检查所有全局有效负载（`$_GET`，`$_POST`，`$_FILES`）
- Session - 分析所有 `$_SESSION` 变量（如果会话处于活动状态）。

这是面板

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

每个面板都显示关于您的应用程序非常有帮助的信息！

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

单击[这里](https://github.com/flightphp/tracy-extensions)查看代码。

安装
-------
运行 `composer require flightphp/tracy-extensions --dev`，您就开始了！

配置
-------
您需要做很少的配置才能启动此功能。在使用此功能之前，您需要初始化 Tracy 调试器[https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide)：

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// 引导代码
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// 您可能需要使用 Debugger::enable(Debugger::DEVELOPMENT) 指定您的环境

// 如果您的应用程序中使用数据库连接，则有一个必需的 PDO 包装器，仅在开发中使用（请勿在生产环境中使用！）
// 它具有与常规 PDO 连接相同的参数
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// 或者如果您将这个附加到 Flight 框架
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// 现在，每当进行查询时，它将捕获时间、查询和参数

// 连接这些点
if(Debugger::$showBar === true) {
	// 这需为false，否则 Tracy 无法实际渲染 :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// 更多代码

Flight::start();
```

## 额外配置

### 会话数据
如果您有自定义会话处理程序（例如 ghostff/session），您可以将任何会话数据数组传递给 Tracy，它将自动为您输出。您可以在 `TracyExtensionLoader` 构造函数的第二个参数中的 `session_data` 键中传递它。

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// 这需为false，否则 Tracy 无法实际渲染 :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// 路由和其他事物...

Flight::start();
```

### Latte

如果您在项目中安装了 Latte，您可以使用 Latte 面板来分析您的模板。您可以将 Latte 实例传递给 `TracyExtensionLoader` 构造函数的第二个参数中的 `latte` 键。

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// 这是您向 Tracy 添加 Latte 面板的位置
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// 这需为false，否则 Tracy 无法实际渲染 :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
