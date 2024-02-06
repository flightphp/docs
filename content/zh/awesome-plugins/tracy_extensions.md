# Tracy Flight Panel Extensions
=====

这是一组扩展，使得与 Flight 一起工作更加丰富。

- Flight - 分析所有 Flight 变量。
- Database - 分析页面上运行的所有查询（如果您正确初始化了数据库连接）
- Request - 分析所有 `$_SERVER` 变量，并检查所有全局负载（`$_GET`、`$_POST`、`$_FILES`）
- Session - 分析所有 `$_SESSION` 变量（如果会话处于活动状态）。

这是面板

![Flight 工具栏](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-tracy-bar.png)

每个面板都显示关于您的应用程序非常有帮助的信息！

![Flight 数据](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-var-data.png)
![Flight 数据库](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-db.png)
![Flight 请求](https://raw.githubusercontent.com/flightphp/tracy-extensions/master/flight-request.png)

安装
-------
运行 `composer require flightphp/tracy-extensions --dev` ，然后就可以开始了！

配置
-------
您需要做很少的配置才能开始使用。您需要在使用之前初始化 Tracy 调试器[https://tracy.nette.org/en/guide](https://tracy.nette.org/en/guide)：

```php
<?php

use Tracy\Debugger;
use flight\debug\tracy\TracyExtensionLoader;

// 启动代码
require __DIR__ . '/vendor/autoload.php';

Debugger::enable();
// 您可能需要使用 Debugger::enable(Debugger::DEVELOPMENT) 指定环境

// 如果应用程序中使用了数据库连接，有一
// 必需的 PDO 包装器仅供在开发中使用（请勿在生产环境中使用！）
// 其具有与常规 PDO 连接相同的参数
$pdo = new PdoQueryCapture('sqlite:test.db', 'user', 'pass');
// 或者在 Flight 框架中附加此内容
Flight::register('db', PdoQueryCapture::class, ['sqlite:test.db', 'user', 'pass']);
// 现在，每当您执行查询时，它都会捕获时间、查询和参数

// 连接关键
if(Debugger::$showBar === true) {
	new TracyExtensionLoader(Flight::app());
}

// 更多代码

Flight::start();
```