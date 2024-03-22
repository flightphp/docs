## 路由

> **提示：** 想了解更多关于路由的信息吗？查看["为什么选择框架？"](/learn/why-frameworks)页面获取更深入的解释。

在 Flight 中，基本的路由是通过将 URL 模式与回调函数或类和方法数组匹配来完成的。

```php
Flight::route('/', function(){
    echo '你好，世界！';
});
```

> 路由按照定义的顺序匹配。第一个匹配请求的路由将被调用。

### 回调/函数
回调可以是任何可调用的对象。所以你可以使用一个普通函数：

```php
function hello(){
    echo '你好，世界！';
}

Flight::route('/', 'hello');
```

### 类
你也可以使用类的静态方法：

```php
class Greeting {
    public static function hello() {
        echo '你好，世界！';
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

或者先创建一个对象，然后调用方法：

```php

// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = '张三';
    }

    public function hello() {
        echo "你好，{$this->name}！";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// 你也可以在不先创建对象的情况下完成
// 注意：构造函数不会注入参数
Flight::route('/', [ 'Greeting', 'hello' ]);
```

#### 通过 DIC（Dependency Injection Container）进行依赖注入
如果你想通过容器（PSR-11、PHP-DI、Dice等）进行依赖注入，
只有一种类型的路由可用，要么直接创建对象并使用容器创建你的对象，
要么可以使用字符串来定义要调用的类和方法。你可以查看[依赖注入](/learn/extending)页面获取更多信息。

这是一个快速的例子：

```php

use flight\database\PdoWrapper;

// Greeting.php
class Greeting
{
	protected PdoWrapper $pdoWrapper;
	public function __construct(PdoWrapper $pdoWrapper) {
		$this->pdoWrapper = $pdoWrapper;
	}

	public function hello(int $id) {
		// 使用 $this->pdoWrapper 处理一些操作
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "你好，世界！我的名字是 {$name}!";
	}
}

// index.php

// 使用任何你需要的参数设置容器
// 在 PSR-11 页面上查看有关更多信息
$dice = new \Dice\Dice();

// 不要忘记重新分配变量 '$dice = '!!!!!
$dice = $dice->addRule('flight\database\PdoWrapper', [
	'shared' => true,
	'constructParams' => [ 
		'mysql:host=localhost;dbname=test', 
		'root',
		'password'
	]
]);

// 注册容器处理程序
Flight::registerContainerHandler(function($class, $params) use ($dice) {
	return $dice->create($class, $params);
});

// 像平常一样路由
Flight::route('/你好/@id', [ 'Greeting', 'hello' ]);
// 或
Flight::route('/你好/@id', 'Greeting->hello');
// 或
Flight::route('/你好/@id', 'Greeting::hello');

Flight::start();
```