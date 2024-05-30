# 自动加载

在PHP中，自动加载是一个概念，您可以指定要从中加载类的目录或目录。这比使用`require`或`include`加载类要更有益。这也是使用Composer软件包的要求。

默认情况下，任何`Flight`类都会由composer自动加载。但是，如果您想要自动加载自己的类，则可以使用`Flight::path`方法指定要从中加载类的目录。

## 基本示例

假设我们有一个如下所示的目录树：

```text
# 示例路径
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - 包含此项目的控制器
│   ├── translations
│   ├── UTILS - 仅包含此应用程序的类（这是为了稍后的示例而全部大写）
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

您可能已经注意到，这与此文档站点的文件结构相同。

您可以像这样指定要加载的每个目录：

```php

/**
 * public/index.php
 */

// 向自动加载器添加路径
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');


/**
 * app/controllers/MyController.php
 */

// 无需命名空间

// 建议所有自动加载的类均为帕斯卡命名法（每个单词的首字母大写，没有空格）
// 从3.7.2开始，您可以通过运行Loader::setV2ClassLoading(false);来使用Pascal_Snake_Case作为类名
class MyController {

	public function index() {
		// 做一些事情
	}
}
```

## 命名空间

如果您有命名空间，实际上实现这一点会变得非常容易。您应该使用`Flight::path()`方法指定应用程序的根目录（而不是文档根目录或`public/`文件夹）。

```php

/**
 * public/index.php
 */

// 向自动加载器添加路径
Flight::path(__DIR__.'/../');
```

现在，您的控制器可能如下所示。查看下面的示例，但请留意重要信息的注释。

```php
/**
 * app/controllers/MyController.php
 */

// 必须使用命名空间
// 命名空间与目录结构相同
// 命名空间必须遵循与目录结构相同的大小写
// 命名空间和目录不能有任何下划线（除非设置了Loader::setV2ClassLoading(false)）
namespace app\controllers;

// 建议所有自动加载的类均为帕斯卡命名法（每个单词的首字母大写，没有空格）
// 从3.7.2开始，您可以通过运行Loader::setV2ClassLoading(false);来使用Pascal_Snake_Case作为类名
class MyController {

	public function index() {
		// 做一些事情
	}
}
```

如果您想要自动加载utils目录中的类，您将执行基本相同的操作：

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// 命名空间必须与目录结构和大小写匹配（注意UTILS目录全部大写，与上面的文件树相同）
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// 做一些事情
	}
}
```

## 类名中的下划线

从3.7.2开始，您可以通过运行`Loader::setV2ClassLoading(false);`来使用Pascal_Snake_Case作为类名。这将允许您在类名中使用下划线。虽然不建议这样做，但对那些需要的人是可用的。

```php

/**
 * public/index.php
 */

// 向自动加载器添加路径
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');
Loader::setV2ClassLoading(false);

/**
 * app/controllers/My_Controller.php
 */

// 无需命名空间

class My_Controller {

	public function index() {
		// 做一些事情
	}
}
```