# 故障排除

这个页面将帮助您解决在使用Flight时可能遇到的常见问题。

## 常见问题

### 404未找到或意外路由行为

如果您看到404未找到错误（但您发誓它确实存在，而且没有拼写错误），实际上可能是因为您在路由终点返回一个值而不是仅仅输出它。这样做的原因是故意的，但可能会让一些开发人员感到意外。

```php

Flight::route('/hello', function(){
	// 这可能导致404未找到错误
	return 'Hello World';
});

// 您可能想要的是
Flight::route('/hello', function(){
	echo 'Hello World';
});

```

造成这种情况的原因是路由器中内置的特殊机制，它将返回输出视为“继续下一个路由”。您可以在[路由](/learn/routing#passing)部分中查看这种行为的文档。

### 类未找到（自动加载不起作用）

可能有几个原因导致这种情况发生。以下是一些示例，但请确保您还查看了[自动加载](/learn/autoloading)部分。

#### 文件名不正确
最常见的情况是类名与文件名不匹配。

如果您有一个名为 `MyClass` 的类，那么文件应该命名为 `MyClass.php`。如果您有一个名为 `MyClass` 的类，而文件命名为 `myclass.php`，那么自动加载程序将找不到它。

#### 命名空间不正确
如果您正在使用命名空间，那么命名空间应该与目录结构匹配。

```php
// 代码

// 如果您的MyController位于app/controllers目录中并且具有命名空间
// 这样将无法工作。
Flight::route('/hello', 'MyController->hello');

// 您需要选择以下其中一种选项
Flight::route('/hello', 'app\controllers\MyController->hello');
// 或者如果您在顶部有一个use语句

use app\controllers\MyController;

Flight::route('/hello', [ MyController::class, 'hello' ]);
// 也可以写成
Flight::route('/hello', MyController::class.'->hello');
// 还有...
Flight::route('/hello', [ 'app\controllers\MyController', 'hello' ]);
```

#### `path()` 未定义

在骨架应用程序中，这在 `config.php` 文件中定义，但为了使您的类能够被找到，您需要确保在尝试使用它之前定义了 `path()` 方法（可能是指向您的目录根）。

```php

// 向自动加载器添加路径
Flight::path(__DIR__.'/../');

```  