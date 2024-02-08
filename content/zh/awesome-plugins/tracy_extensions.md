Tracy Flight 面板扩展
=====

这是一组扩展，使与 Flight 一起工作更加丰富。

- Flight - 分析所有 Flight 变量。
- Database - 分析页面上已运行的所有查询（如果正确启动数据库连接）
- Request - 分析所有 `$_SERVER` 变量并检查所有全局负载（`$_GET`，`$_POST`，`$_FILES`）
- Session - 分析所有活动会话的 `$_SESSION` 变量。

这是面板

![Flight Bar](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

每个面板都显示有关您的应用程序的非常有帮助的信息！

![Flight Data](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight Database](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight Request](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

安装
-------
运行 `composer require flightphp/tracy-extensions --dev` 即可开始使用！

配置
-------
您需要做很少的配置才能启动此功能。您需要在使用此功能之前启动 Tracy 调试器[https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide)：

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// 启动代码
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
//  您可能需要使用 Debugger::enable(Debugger::DEVELOPMENT) 来指定您的环境

// 如果您在应用程序中使用数据库连接，那么有一个
// 必须在开发环境使用的 PDO 包装器（请勿在生产环境使用！）
// 它具有与常规 PDO 连接相同的参数
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// 或者如果您将其附加到 Flight 框架
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// 现在每当您进行查询时，它都会捕获时间、查询和参数

// 连接各部分
if(Debugger::$showBar === true) {
	new TracyExtensionLoader(Flight::app());
}

// 更多代码

Flight::start();
```