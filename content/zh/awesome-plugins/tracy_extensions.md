Tracy Flight 面板扩展
=====

这是一组扩展，使与 Flight 一起工作更加丰富。

- Flight - 分析所有 Flight 变量。
- Database - 分析页面上运行的所有查询（如果您正确初始化了数据库连接）
- Request - 分析所有 `$_SERVER` 变量并检查所有全局数据（`$_GET`，`$_POST`，`$_FILES`）
- Session - 分析所有活动会话的 `$_SESSION` 变量。

这是面板

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

每个面板显示有关您的应用程序的非常有用的信息！

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

安装
-------
运行 `composer require flightphp/tracy-extensions --dev` 就可以开始了！

配置
-------
您需要做很少的配置才能开始使用这个工具。您需要在使用此工具之前初始化 Tracy 调试器 [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide)：

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// 启动代码
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// 您可能需要使用 Debugger::enable(Debugger::DEVELOPMENT) 指定您的环境

// 如果在应用程序中使用数据库连接，则有一个
// 需要使用的必需 PDO 包装器，仅限在开发环境中使用（请不要用于生产！）
// 它具有与常规 PDO 连接相同的参数
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// 或者如果将其附加到 Flight 框架
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// 现在每次进行查询时都会捕获时间、查询和参数

// 连接这些点
if(Debugger::$showBar === true) {
	// 这需要为 false，否则 Tracy 实际上无法渲染 :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// 更多代码

Flight::start();
```

## 附加配置

### 会话数据
如果您有自定义会话处理程序（例如 ghostff/session），您可以将任何会话数据数组传递给 Tracy，并且它将自动为您输出。您可以在`TracyExtensionLoader` 构造函数的第二个参数中使用 `session_data` 键传递它。

```php

use Ghostff\Session\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// 这需要为 false，否则 Tracy 实际上无法渲染 :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// 路由和其他内容...

Flight::start();
```

### Latte

如果在项目中安装了 Latte，则可以使用 Latte 面板分析您的模板。您可以使用第二个参数中的 `latte` 键将 Latte 实例传递给 `TracyExtensionLoader` 构造函数。

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
	// 这需要为 false，否则 Tracy 实际上无法渲染 :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
