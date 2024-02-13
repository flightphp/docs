# 自动加载

自动加载是 PHP 中的一个概念，您可以指定一个或多个目录以从中加载类。这比使用 `require` 或 `include` 加载类要更有益。这也是使用 Composer 包的要求之一。

默认情况下，任何 `Flight` 类都会由 Composer 自动加载。但是，如果您想要自动加载自己的类，可以使用 `Flight::path` 方法来指定从哪个目录加载类。

## 基本示例

假设我们有以下目录树：

```text
# 示例路径
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - 包含此项目的控制器
│   ├── translations
│   ├── UTILS - 仅用于此应用程序的类（此处全大写是为了后面的示例）
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

您可能已经注意到，这与此文档站点的文件结构相同。

您可以像这样指定要从中加载的每个目录：

```php
/**
 * public/index.php
 */

// 添加一个路径到自动加载程序
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');


/**
 * app/controllers/MyController.php
 */

// 不需要命名空间

// 所有自动加载的类都建议使用帕斯卡命名法（每个单词首字母大写，无空格）
// 不能在类名中使用下划线是一个要求
class MyController {

	public function index() {
		// 执行某些操作
	}
}
```

## 命名空间

如果您有命名空间，实际上很容易实现。您应该使用 `Flight::path()` 方法来指定应用程序的根目录（而不是文档根目录或 `public/` 文件夹）。

```php
/**
 * public/index.php
 */

// 添加一个路径到自动加载程序
Flight::path(__DIR__.'/../');
```

现在，您的控制器可能如下所示。查看下面的示例，但请注意注释中的重要信息。

```php
/**
 * app/controllers/MyController.php
 */

// 命名空间是必需的
// 命名空间与目录结构相同
// 命名空间必须与目录结构大小写一致
// 命名空间和目录都不能包含下划线
namespace app\controllers;

// 所有自动加载的类都建议使用帕斯卡命名法（每个单词首字母大写，无空格）
// 不能在类名中使用下划线是一个要求
class MyController {

	public function index() {
		// 执行某些操作
	}
}
```

如果您想要自动加载 utils 目录中的类，您可以做基本相同的操作：

```php
/**
 * app/UTILS/ArrayHelperUtil.php
 */

// 命名空间必须与目录结构和大小写匹配（请注意文件树中 UTILS 目录是全大写的）
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// 执行某些操作
	}
}
```