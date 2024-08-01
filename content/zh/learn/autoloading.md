# 自動加載

自動加載是 PHP 中的一個概念，在這裡您指定要從哪些目錄加載類。這比使用`require`或`include`來加載類要好得多。這也是使用 Composer 套件的要求。

默認情況下，任何`Flight`類都會由 Composer 自動加載。但是，如果您想要自動加載自己的類，可以使用`Flight::path()`方法來指定要從哪個目錄加載類。

## 基本示例

假設我們有如下目錄樹：

```text
# 示例路徑
/home/user/project/my-flight-project/
├── app
│   ├── cache
│   ├── config
│   ├── controllers - 包含此項目的控制器
│   ├── translations
│   ├── UTILS - 包含僅用於此應用程序的類（這是為了後面的示例故意全部大寫）
│   └── views
└── public
    └── css
	└── js
	└── index.php
```

您可能已經注意到，這與此文檔站點的文件結構相同。

您可以像這樣指定要從每個目錄加載：

```php

/**
 * public/index.php
 */

// 添加一個路徑給自動加載程序
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');


/**
 * app/controllers/MyController.php
 */

// 不需要命名空間

// 建議將所有自動加載的類命名為帕斯卡命名法（每個單詞首字母大寫，沒有空格）
// 截至 3.7.2 版本，您可以通過運行 Loader::setV2ClassLoading(false); 來使用帕斯卡_蛇_命名法命名您的類
class MyController {

	public function index() {
		// 做一些事情
	}
}
```

## 命名空間

如果您有命名空間，實際上實現這一點變得非常容易。您應該使用`Flight::path()`方法來指定應用程序的根目錄（而不是文檔根目錄或`public/`文件夾）。

```php

/**
 * public/index.php
 */

// 添加一個路徑給自動加載程序
Flight::path(__DIR__.'/../');
```

現在您的控制器可能看起來像這樣。查看下面的示例，但請注意評注中的重要信息。

```php
/**
 * app/controllers/MyController.php
 */

// 必須有命名空間
// 命名空間與目錄結構相同
// 命名空間必須遵循與目錄結構相同的大小寫
// 命名空間和目錄不能有任何下劃線（除非 Loader::setV2ClassLoading(false) 已設置）
namespace app\controllers;

// 建議將所有自動加載的類命名為帕斯卡命名法（每個單詞首字母大寫，沒有空格）
// 截至 3.7.2 版本，您可以通過運行 Loader::setV2ClassLoading(false); 來使用帕斯卡_蛇_命名法命名您的類
class MyController {

	public function index() {
		// 做一些事情
	}
}
```

如果您希望自動加載 utils 目錄中的類，則基本上可以執行相同的操作：

```php

/**
 * app/UTILS/ArrayHelperUtil.php
 */

// 命名空間必須與目錄結構及大小寫相匹配（請注意 UTILS 目錄是全部大寫
//     如上面的文件樹所示）
namespace app\UTILS;

class ArrayHelperUtil {

	public function changeArrayCase(array $array) {
		// 做一些事情
	}
}
```

## 類名中的下劃線

截至 3.7.2 版本，您可以運行`Loader::setV2ClassLoading(false);`，來使用帕斯卡_蛇_命名法命名您的類。這將允許您在類名中使用下劃線。這不建議使用，但對於需要的用戶來說是可用的。

```php

/**
 * public/index.php
 */

// 添加一個路徑給自動加載程序
Flight::path(__DIR__.'/../app/controllers/');
Flight::path(__DIR__.'/../app/utils/');
Loader::setV2ClassLoading(false);

/**
 * app/controllers/My_Controller.php
 */

// 不需要命名空間

class My_Controller {

	public function index() {
		// 做一些事情
	}
}
```