Tracy Flight 面板扩展
=====

这是一个扩展集，使得与 Flight 的工作更加丰富。

- Flight - 分析所有 Flight 变量。
- Database - 分析页面上运行的所有查询（如果您正确初始化了数据库连接）
- Request - 分析所有 `$_SERVER` 变量并检查所有全局有效负载 (`$_GET`, `$_POST`, `$_FILES`)
- Session - 如果会话处于活动状态，则分析所有 `$_SESSION` 变量。

这是面板

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

每个面板都显示关于您的应用程序的非常有用的信息！

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

点击 [这里](https://github.com/flightphp/tracy-extensions) 查看代码。

安装
-------
运行 `composer require flightphp/tracy-extensions --dev`，您就可以开始了！

配置
-------
要使其启动，您需要进行的配置非常少。您需要在使用此 [https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide) 之前先启动 Tracy 调试器：

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// 启动代码
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// 您可能需要通过 Debugger::enable(Debugger::DEVELOPMENT) 指定您的环境

// 如果您在应用程序中使用数据库连接，这里有一个 
// 需要在开发中使用的 PDO 包装器（请勿在生产中使用！）
// 它具有与常规 PDO 连接相同的参数
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// 或者如果将其附加到 Flight 框架
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// 现在每当您进行查询时，它将捕获时间、查询和参数

// 这连接了点
if(Debugger::$showBar === true) {
	// 这需要为 false，否则 Tracy 不能实际渲染 :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}

// 更多代码

Flight::start();
```

## 额外配置

### 会话数据
如果您有一个自定义会话处理程序（例如 ghostff/session），您可以将任何会话数据数组传递给 Tracy，它将自动为您输出。您通过 `TracyExtensionLoader` 构造函数的第二个参数中的 `session_data` 键传递它。

```php

use Ghostff\Session\Session;
// 或使用 flight\Session;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('session', Session::class);

if(Debugger::$showBar === true) {
	// 这需要为 false，否则 Tracy 不能实际渲染 :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app(), [ 'session_data' => Flight::session()->getAll() ]);
}

// 路由和其他内容...

Flight::start();
```

### Latte

如果您的项目中安装了 Latte，您可以使用 Latte 面板来分析您的模板。您可以将 Latte 实例传递给 `TracyExtensionLoader` 构造函数，在第二个参数中使用 `latte` 键。

```php

use Latte\Engine;

require 'vendor/autoload.php';

$app = Flight::app();

$app->register('latte', Engine::class, [], function($latte) {
	$latte->setTempDirectory(__DIR__ . '/temp');

	// 这是您将 Latte 面板添加到 Tracy 的地方
	$latte->addExtension(new Latte\Bridges\Tracy\TracyExtension);
});

if(Debugger::$showBar === true) {
	// 这需要为 false，否则 Tracy 不能实际渲染 :(
	Flight::set('flight.content_length', false);
	new TracyExtensionLoader(Flight::app());
}
```