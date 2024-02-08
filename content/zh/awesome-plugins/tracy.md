# Tracy

Tracy 是一个令人惊叹的错误处理程序，可以与 Flight 一起使用。它有许多面板可以帮助您调试应用程序。扩展和添加您自己的面板也非常容易。Flight 团队为 Flight 项目创建了一些特定的面板，使用了 [flightphp/tracy-extensions](https://github.com/flightphp/tracy-extensions) 插件。

## 安装

使用 composer 进行安装。实际上，您希望在没有开发版本的情况下安装此项，因为 Tracy 自带一个生产错误处理组件。

```bash
composer require tracy/tracy
```

## 基本配置

有一些基本的配置选项可供开始使用。您可以在 [Tracy 文档](https://tracy.nette.org/en/configuring) 中了解更多信息。

```php

require 'vendor/autoload.php';

use Tracy\Debugger;

// Enable Tracy
Debugger::enable();
// Debugger::enable(Debugger::DEVELOPMENT) // 有时候您需要显式设置 (还有 Debugger::PRODUCTION)
// Debugger::enable('23.75.345.200'); // 您也可以提供一个 IP 地址数组

// 这里是错误和异常将被记录的地方。请确保此目录存在且可写。
Debugger::$logDirectory = __DIR__ . '/../log/';
Debugger::$strictMode = true; // 显示所有错误
// Debugger::$strictMode = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED; // 所有错误，除去已弃用的通知
if (Debugger::$showBar) {
    $app->set('flight.content_length', false); // 如果 Debugger 栏可见，则 Flight 无法设置 content-length

	// 这对于 Flight 的 Tracy 扩展是特定的，如果您已经包含了它
	// 否则请将其注释掉。
	new TracyExtensionLoader($app);
}
```

## 有用提示

当您调试代码时，有一些非常有用的函数可以为您输出数据。

- `bdump($var)` - 这将在单独的面板中将变量转储到 Tracy Bar 中。
- `dumpe($var)` - 这将转储变量，然后立即终止。