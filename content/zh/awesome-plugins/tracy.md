# Tracy

Tracy 是一个令人吃惊的错误处理程序，可以与 Flight 一起使用。它有许多面板，可以帮助您调试应用程序。它也非常容易扩展和添加自己的面板。Flight 团队已经为 Flight 项目创建了一些面板，使用了 [flightphp/tracy-extensions](https://github.com/flightphp/tracy-extensions) 插件。

## 安装

使用 composer 安装。并且您实际上希望安装时不带有 dev 版本，因为 Tracy 自带一个生产错误处理组件。

```bash
composer require tracy/tracy
```

## 基本配置

有一些基本配置选项可供开始。您可以在 [Tracy 文档](https://tracy.nette.org/en/configuring) 中阅读更多信息。

```php

require 'vendor/autoload.php';

use Tracy\Debugger;

// 启用 Tracy
Debugger::enable();
// Debugger::enable(Debugger :: DEVELOPMENT) // 有时您必须明确说明 (也可以使用 Debugger :: PRODUCTION)
// Debugger::enable('23.75.345.200'); // 还可以提供 IP 地址数组

// 这里是错误和异常记录的位置。请确保此目录存在并可写。
Debugger::$logDirectory = __DIR__ . '/../log/';
Debugger::$strictMode = true; // 显示所有错误
// Debugger::$strictMode = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED; // 显示所有错误，除了已弃用提示
if (Debugger::$showBar) {
    $app->set('flight.content_length', false); // 如果 Debugger 栏可见，则 Flight 无法设置内容长度

	// 如果您包含了 Tracy Extension for Flight，则这是特定于该扩展的。
	// 否则请将其注释掉。
	new TracyExtensionLoader($app);
}
```

## 有用提示

当您调试代码时，有一些非常有用的函数可以输出数据。

- `bdump($var)` - 这将将变量转储到 Tracy Bar 中的一个单独面板中。
- `dumpe($var)` - 这将转储变量，然后立即停止。