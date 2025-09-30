# 自动加载

## 概述

自动加载是 PHP 中的一个概念，您可以指定一个或多个目录来加载类。这比使用 `require` 或 `include` 来加载类更有益。它也是使用 Composer 包的要求。

## 理解

默认情况下，任何 `Flight` 类都会通过 Composer 自动为您自动加载。但是，如果您想自动加载自己的类，可以使用 `Flight::path()` 方法指定一个目录来加载类。

使用自动加载器可以显著简化您的代码。文件开头不再需要一堆 `include` 或 `require` 语句来捕获该文件中使用的所有类，而是可以动态调用您的类，它们会自动包含。

## 基本用法

假设我们有一个像下面的目录树：

```text
# 示例路径
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - 包含此项目的控制器
│   ├── translations
│   ├── UTILS - 仅包含此应用程序的类（特意全大写，用于后面的示例）
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

您可能已经注意到，这是与本文档站点相同的文件结构。

您可以像这样指定每个要加载的目录：

```php

/**
 * public/index.php
 */

// 将路径添加到自动加载器
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');


/**
 * app/controllers/MyController.php
 */

// 无需命名空间

// 所有自动加载的类推荐使用 Pascal Case（每个单词首字母大写，无空格）
class MyController {

	public function index() {
		// 做些什么
	}
}
```

## 命名空间

如果您确实有命名空间，实现起来其实非常简单。您应该使用 `Flight::path()` 方法指定应用程序的根目录（不是文档根目录或 `public/` 文件夹）。

```php

/**
 * public/index.php
 */

// 将路径添加到自动加载器
Flight::path(__DIR__.'/../');
```

现在您的控制器可能看起来像这样。请查看下面的示例，但请注意注释中的重要信息。

```php
/**
 * app/controllers/MyController.php
 */

// 命名空间是必需的
// 命名空间与目录结构相同
// 命名空间必须遵循与目录结构相同的命名规则
// 命名空间和目录不能有下划线（除非设置 Loader::setV2ClassLoading(false)）
namespace app\controllers;

// 所有自动加载的类推荐使用 Pascal Case（每个单词首字母大写，无空格）
// 从 3.7.2 开始，您可以通过运行 Loader::setV2ClassLoading(false); 使用 Pascal_Snake_Case 作为类名；
class MyController {

	public function index() {
		// 做些什么
	}
}
```

如果您想自动加载 utils 目录中的一个类，您基本上可以做同样的事情：

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// 命名空间必须匹配目录结构和命名规则（注意 UTILS 目录是全大写的
//     如上面的文件树所示）
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// 做些什么
	}
}
```

## 类名中的下划线

从 3.7.2 开始，您可以通过运行 `Loader::setV2ClassLoading(false);` 使用 Pascal_Snake_Case 作为类名。
这将允许您在类名中使用下划线。
这不是推荐的做法，但对于需要的人它是可用的。

```php
use flight\core\Loader;

/**
 * public/index.php
 */

// 将路径添加到自动加载器
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');
Loader::setV2ClassLoading(false);

/**
 * app/controllers/My_Controller.php
 */

// 无需命名空间

class My_Controller {

	public function index() {
		// 做些什么
	}
}
```

## 另请参阅
- [路由](/learn/routing) - 如何将路由映射到控制器并渲染视图。
- [为什么使用框架？](/learn/why-frameworks) - 理解使用像 Flight 这样的框架的好处。

## 故障排除
- 如果您似乎无法弄清楚为什么您的命名空间类找不到，请记住使用 `Flight::path()` 到项目中的根目录，而不是您的 `app/` 或 `src/` 目录或等效目录。

### 类未找到（自动加载不工作）

这可能有几个原因。下面是一些示例，但请确保您也查看[自动加载](/learn/autoloading)部分。

#### 错误的 文件名
最常见的是类名与文件名不匹配。

如果您有一个名为 `MyClass` 的类，那么文件应该命名为 `MyClass.php`。如果您有一个名为 `MyClass` 的类，但文件命名为 `myclass.php`，
那么自动加载器将无法找到它。

#### 错误的命名空间
如果您使用命名空间，那么命名空间应该匹配目录结构。

```php
// ...代码...

// 如果您的 MyController 在 app/controllers 目录中并且有命名空间
// 这将不起作用。
Flight::route('/hello', 'MyController->hello');

// 您需要选择以下选项之一
Flight::route('/hello', 'app\controllers\MyController->hello');
// 或者如果您在上部有 use 语句

use app\controllers\MyController;

Flight::route('/hello', [ MyController::class, 'hello' ]);
// 也可以这样写
Flight::route('/hello', MyController::class.'->hello');
// 也可以...
Flight::route('/hello', [ 'app\controllers\MyController', 'hello' ]);
```

#### `path()` 未定义

在骨架应用程序中，这是在 `config.php` 文件中定义的，但为了让您的类被找到，您需要确保 `path()`
方法在使用之前被定义（可能到您的目录根目录）。

```php
// 将路径添加到自动加载器
Flight::path(__DIR__.'/../');
```

## 更新日志
- v3.7.2 - 您可以通过运行 `Loader::setV2ClassLoading(false);` 使用 Pascal_Snake_Case 作为类名
- v2.0 - 添加了自动加载功能。