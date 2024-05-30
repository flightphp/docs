```zh
# 路由

> **提示：** 想了解更多关于路由的内容吗？请查看["为什么选择框架?"](/learn/why-frameworks)页面，有更详尽的解释。

Flight中的基本路由是通过将URL模式与回调函数或类和方法的数组进行匹配来完成的。

```php
Flight::route('/', function(){
    echo '你好，世界！';
});
```

> 路由按照定义的顺序进行匹配。第一个匹配请求的路由将被调用。

### 回调函数/函数
回调函数可以是任何可调用的对象。因此，您可以使用常规函数：

```php
function hello(){
    echo '你好，世界！';
}

Flight::route('/', 'hello');
```

### 类
您也可以使用类的静态方法：

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
// 您也可以在不先创建对象的情况下执行此操作
// 提示：不会向构造函数注入参数
Flight::route('/', [ 'Greeting', 'hello' ]);
```

#### 通过DIC（Dependency Injection Container）进行依赖项注入
如果您想要通过容器（PSR-11、PHP-DI、Dice等）进行依赖项注入，那么
只有直接创建对象并使用容器创建对象的路由类型或者您可以使用字符串定义类和
要调用的方法。您可以前往[Dependency Injection](/learn/extending)页面了解更多信息。 

这里是一个简单示例：

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
		// 使用$this->pdoWrapper做某事情
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "你好，世界！我的名字是{$name}！";
	}
}

// index.php

// 使用任何您需要的参数设置容器
// 请查看有关PSR-11的更多信息
$dice = new \Dice\Dice();

// 不要忘记重新分配变量'$dice = '!!!!!
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

// 像正常一样处理路由
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// 或
Flight::route('/hello/@id', 'Greeting->hello');
// 或
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## 方法路由

默认情况下，路由模式将匹配所有请求方法。您可以通过在URL之前放置一个标识符来响应特定方法。

```php
Flight::route('GET /', function () {
  echo '我收到了一个GET请求。';
});

Flight::route('POST /', function () {
  echo '我收到了一个POST请求。';
});

// 您无法对路由使用Flight::get()，因为那是一个用于获取变量的方法，而不是创建路由。
// Flight::post('/', function() { /* 代码 */ });
// Flight::patch('/', function() { /* 代码 */ });
// Flight::put('/', function() { /* 代码 */ });
// Flight::delete('/', function() { /* 代码 */ });
```

您还可以通过使用“|”分隔符将多个方法映射到单个回调函数：

```php
Flight::route('GET|POST /', function () {
  echo '我收到了一个GET或POST请求。';
});
```

此外，您可以获取路由器对象，该对象具有一些可供您使用的辅助方法：

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

您可以在路由中使用正则表达式：

```php
Flight::route('/user/[0-9]+', function () {
  // 这将匹配/user/1234
});
```

尽管这种方法可用，但建议使用命名参数或
带正则表达式的命名参数，因为它们更易读且更易维护。

## 命名参数

您可以在路由中指定命名参数，这些参数将传递给
您的回调函数。

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "你好，$name ($id)！";
});
```

您还可以使用命名参数与正则表达式结合使用
使用“:”分隔符：

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // 这将匹配/bob/123
  // 但不会匹配/bob/12345
});
```

> **提示：** 不支持在命名参数中匹配正则表达式组 `()`。\:(

## 可选参数

您可以通过将段落包装在括号中指定可选匹配的命名参数。

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

任何未匹配的可选参数将作为`NULL`传递。

## 通配符

匹配仅在各个URL段中进行。如果要匹配多个段
您可以使用`*`通配符。

```php
Flight::route('/blog/*', function () {
  // 这将匹配 /blog/2000/02/01
});
```

要将所有请求路由到单个回调中，您可以这样做：

```php
Flight::route('*', function () {
  // 做点什么
});
```

## 传递

您可以通过从回调函数中返回`true`将执行传递给
下一个匹配路由。

```php
Flight::route('/user/@name', function (string $name) {
  // 检查一些条件
  if ($name !== "Bob") {
    // 继续下一个路由
    return true;
  }
});

Flight::route('/user/*', function () {
  // 这将被调用
});
```

## 路由别名

您可以为路由分配别名，以便以后在代码中动态生成URL（例如在模板中）。

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// 稍后在代码中的某个地方
Flight::getUrl('user_view', [ 'id' => 5 ]); // 将返回'/users/5'
```

如果您的URL发生变化，这将非常有帮助。在上面的示例中，假设用户已移至`/admin/users/@id`。
通过别名设置，您无需更改引用别名的任何位置，因为别名现在将像上面的示例一样返回`/admin/users/5`。

路由别名也适用于组：

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// 稍后在代码中的某个地方
Flight::getUrl('user_view', [ 'id' => 5 ]); // 将返回'/users/5'
```

## 路由信息

如果您希望检查匹配路由的信息，您可以请求将路由
对象传递给回调，方法是在路由方法的第三个参数中传递`true`。路由对象将始终是传递给您的回调函数的最后一个参数。

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 匹配的HTTP方法数组
  $route->methods;

  // 命名参数数组
  $route->params;

  // 匹配的正则表达式
  $route->regex;

  // 包含URL模式中使用的任何'*'的内容
  $route->splat;

  // 显示URL路径，如果您确实需要的话
  $route->pattern;

  // 显示分配给此中间件的内容
  $route->middleware;

  // 显示分配给此路由的别名
  $route->alias;
}, true);
```

## 路由分组

有时您希望将相关路由分组在一起（例如`/api/v1`）。
您可以通过使用`group`方法来实现这一点：

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

您甚至可以嵌套组的组：

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

	// Flight::get()获取变量，它不设置路由！查看下面的对象上下文
	Flight::route('GET /users', function () {
	  // 匹配GET /api/v2/users
	});
  });
});
```

### 使用对象上下文进行分组

您仍然可以使用`Engine`对象在对象上下文中组合路由：

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

## 流式传输

现在您可以使用`streamWithHeaders()`方法将响应流式传输到客户端。 
这对于发送大文件、长时间运行的进程或生成大型响应非常有用。 
流式传输路由的处理方式与常规路由略有不同。

> **提示：** 如果您的[`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering)设置为false，则只有在该条件下才能使用流式响应。

### 带手动标头的流式传输

您可以通过在路由上使用`stream()`方法将响应流式传输到客户端。 如果
这样做，您必须在向客户端输出任何内容之前自行设置所有方法。
这可以通过`header()` PHP函数或`Flight::response()->setRealHeader()`方法完成。

```php
Flight::route('/@filename', function($filename) {

	// 显然，您应该对路径进行过滤等操作。
	$fileNameSafe = basename($filename);

	// 如果在路由执行后还有其他标头要设置
	// 您必须在输出任何内容之前定义它们。
	// 它们必须全部是对header()函数的原始调用或
	// 调用Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// 或者
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);

	// 错误处理等
	if(empty($fileData)) {
		Flight::halt(404, '文件未找到');
	}

	// 如果需要，手动设置内容长度
	header('Content-Length: '.filesize($filename));

	// 向客户端流式传输数据
	echo $fileData;

// 这就是“魔术”的行
})->stream();
```

### 带头的流式传输

您还可以使用`streamWithHeaders()`方法在开始流式传输之前设置标题。

```php
Flight::route('/stream-users', function() {

	// 您可以在此添加任何其他标头
	// 您必须使用header()或Flight::response()->setRealHeader()

	// 无论您从何处获取数据，仅作为示例...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// 发送数据到客户端的操作是必需的
		ob_flush();
	}
	echo '}';

// 这就是在开始流式传输之前如何设置标头。
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// 可选状态码，默认为 200
	'status' => 200
]);
```