# 路由

> **注意:** 想了解更多关于路由的内容吗？查看更详细的解释，请访问["为什么要使用框架？"](/learn/why-frameworks)页面。

Flight中的基本路由是通过将URL模式与回调函数或类和方法的数组进行匹配来完成的。

```php
Flight::route('/', function(){
    echo '你好，世界！';
});
```

> 路由匹配的顺序是按照定义的顺序进行的。第一个匹配请求的路由将被调用。

### 回调/函数
回调可以是任何可调用的对象。所以你可以使用一个常规函数：

```php
function hello() {
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

或者首先创建一个对象，然后调用方法：

```php

// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = '张三';
    }

    public function hello() {
        echo "你好，{$this->name}!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// 你还可以在不先创建对象的情况下做到这一点
// 注意：不会将参数注入到构造函数中
Flight::route('/', [ 'Greeting', 'hello' ]);
// 此外，你还可以使用这种更简洁的语法
Flight::route('/', 'Greeting->hello');
// 或者
Flight::route('/', Greeting::class.'->hello');
```

#### 通过DIC（依赖注入容器）进行依赖注入
如果你想通过容器（PSR-11、PHP-DI、Dice等）进行依赖注入，
那么唯一可用的类型就是直接创建对象自己并使用容器来创建你的对象，
或者你可以使用字符串来定义要调用的类和方法。你可以查看[依赖注入](/learn/extending)页面获取更多信息。

这里是一个快速示例：

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
		// 使用$this->pdoWrapper做一些事情
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "你好，世界！我的名字是{$name}!";
	}
}

// index.php

// 使用你需要的任何参数设置容器
// 查看依赖注入页面以获取有关PSR-11的更多信息
$dice = new \Dice\Dice();

// 别忘记通过'$dice = '重新分配变量！！！！
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

// 像正常一样设置路由
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// 或者
Flight::route('/hello/@id', 'Greeting->hello');
// 或者
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## 方法路由

默认情况下，路由模式将与所有请求方法进行匹配。你可以通过在URL之前放置一个标识符来响应特定方法。

```php
Flight::route('GET /', function () {
  echo '我收到了一个GET请求。';
});

Flight::route('POST /', function () {
  echo '我收到了一个POST请求。';
});

// 你不能使用Flight::get()来设置路由，因为那是一个用于获取变量，而不是创建路由的方法。
// Flight::post('/', function() { /* 代码 */ });
// Flight::patch('/', function() { /* 代码 */ });
// Flight::put('/', function() { /* 代码 */ });
// Flight::delete('/', function() { /* 代码 */ });
```

你还可以通过使用`|`分隔符将多个方法映射到单个回调函数：

```php
Flight::route('GET|POST /', function () {
  echo '我接收到了GET或POST请求。';
});
```

此外，你可以获得Router对象，该对象具有一些可供你使用的辅助方法：

```php

$router = Flight::router();

// 映射所有方法
$router->map('/', function() {
	echo '你好，世界！';
});

// GET请求
$router->get('/users', function() {
	echo '用户';
});
// $router->post();
// $router->put();
// $router->delete();
// $router->patch();
```

## 正则表达式

你可以在你的路由中使用正则表达式：

```php
Flight::route('/user/[0-9]+', function () {
  // 这将匹配/user/1234
});
```

虽然这种方法是可用的，但建议使用命名参数或带有正则表达式的命名参数，因为它们更易读和更易于维护。

## 命名参数

你可以在你的路由中指定具有命名参数，这些参数将传递给你的回调函数。

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "你好，$name（$id）!";
});
```

你还可以通过使用`:`分隔符将带有命名参数的正则表达式与命名参数一起使用：

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // 这将匹配/bob/123
  // 但不匹配/bob/12345
});
```

> **注意:** 不支持使用具有命名参数的正则表达式组`()`。: '\(

## 可选参数

你可以指定命名参数为匹配的可选参数，方法是将段包裹在括号中。

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // 这将匹配以下URL：
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

任何未匹配的可选参数都将作为`NULL`传递。

## 通配符

匹配只会在单独的URL段上进行。如果你想匹配多个段，可以使用`*`通配符。

```php
Flight::route('/blog/*', function () {
  // 这将匹配/blog/2000/02/01
});
```

要将所有请求路由到单个回调函数，可以这样做：

```php
Flight::route('*', function () {
  // 做某事
});
```

## 传递

你可以通过从回调函数中返回`true`将执行传递给下一个匹配的路由。

```php
Flight::route('/user/@name', function (string $name) {
  // 检查一些条件
  if ($name !== "Bob") {
    // 继续到下一个路由
    return true;
  }
});

Flight::route('/user/*', function () {
  // 这将被调用
});
```

## 路由别名

你可以为路由分配一个别名，以便稍后可以在代码中动态生成URL（例如模板）。

```php
Flight::route('/users/@id', function($id) { echo '用户:'.$id; }, false, 'user_view');

// 稍后在代码中的某处
Flight::getUrl('user_view', [ 'id' => 5 ]); // 将返回'/users/5'
```

如果你的URL可能会改变，这会特别有帮助。在上面的示例中，假设用户已经移动到`/admin/users/@id`。
有了别名，你就不必更改任何引用别名的地方，因为别名现在将返回`/admin/users/5`，就像上面的示例一样。

路由别名也适用于组：

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo '用户:'.$id; }, false, 'user_view');
});


// 稍后在代码中的某处
Flight::getUrl('user_view', [ 'id' => 5 ]); // 将返回'/users/5'
```

## 路由信息

如果你想检查匹配的路由信息，你可以请求路由对象作为第三个参数传递给你的回调函数，通过将`true`作为路由方法的第三个参数传递。路由对象总是作为最后一个参数传递给回调函数。

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 与匹配的HTTP方法数组
  $route->methods;

  // 命名参数数组
  $route->params;

  // 匹配的正则表达式
  $route->regex;

  // 包含URL模式中使用的所有'*'的内容
  $route->splat;

  // 显示url路径...如果你真的需要它
  $route->pattern;

  // 显示为此路由分配的中间件
  $route->middleware;

  // 显示分配给此路由的别名
  $route->alias;
}, true);
```

## 路由分组

有时你想将相关路由分组在一起（例如`/api/v1`）。你可以通过使用`group`方法来实现：

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// 匹配/api/v1/users
  });

  Flight::route('/posts', function () {
	// 匹配/api/v1/posts
  });
});
```

你甚至可以将组合的组合：

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get()获取变量，它不设置路由！查看下面的对象上下文
	Flight::route('GET /users', function () {
	  // 匹配GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // 匹配POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // 匹配PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get()获取变量，它不会设置路由！查看下面的对象上下文
	Flight::route('GET /users', function () {
	  // 匹配GET /api/v2/users
	});
  });
});
```

### 与对象上下文一起分组

你仍然可以在`Engine`对象中使用路由分组，方法如下：

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // 使用$router变量
  $router->get('/users', function () {
	// 匹配GET /api/v1/users
  });

  $router->post('/posts', function () {
	// 匹配POST /api/v1/posts
  });
});
```

## 流

现在你可以使用`streamWithHeaders()`方法向客户端流式传输响应。这对于发送大型文件、长时间运行的流程或生成大型响应非常有用。流式传输路由的处理方式与普通路由略有不同。

> **注意:** 仅当你将[`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering)设置为false时才可以使用流式传输响应。

### 带有手动标头的流

你可以通过在路由上使用`stream()`方法将响应流式传输给客户端。如果你这样做，你必须在向客户端输出任何内容之前手动设置所有方法。这是通过`header()` php函数或`Flight::response()->setRealHeader()`方法来完成的。

```php
Flight::route('/@filename', function($filename) {

	// 显然，你将清理路径等内容。
	$fileNameSafe = basename($filename);

	// 如果你在路由执行后还需要设置其他标头
	// 你必须在输出任何内容之前定义它们。
	// 它们必须全部是对header()函数的原始调用
	// 或对Flight::response()->setRealHeader()的调用
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// 或
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);

	// 错误捕获等
	if(empty($fileData)) {
		Flight::halt(404, '文件未找到');
	}

	// 如果愿意，也可手动设置内容长度
	header('Content-Length: '.filesize($filename));

	// 将数据流式传输给客户端
	echo $fileData;

// 这就是这里的神奇之处
})->stream();
```

### 带有标头的流

你还可以使用`streamWithHeaders()`方法在开始流式传输之前设置标头。

```php
Flight::route('/stream-users', function() {

	// 你可以在这里添加任何其他标头
	// 你只需使用header()或Flight::response()->setRealHeader()

	// 无论你如何获取数据，只是举例而已...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// 这是必需的，将数据发送到客户端
		ob_flush();
	}
	echo '}';

// 这是在开始流式传输之前设置标头的方法。
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// 可选状态码，默认为200
	'status' => 200
]);
```