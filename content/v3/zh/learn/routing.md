# 路由

> **Note:** 想要更深入地了解路由？请查看 ["why a framework?"](/learn/why-frameworks) 页面以获取更详细的解释。

Flight 中的基本路由是通过将 URL 模式与回调函数或类和方法的数组进行匹配来实现的。

```php
Flight::route('/', function(){
    echo 'hello world!';  // 这将输出 'hello world!'
});
```

> 路由按定义的顺序进行匹配。第一个匹配请求的路由将被调用。

### 回调/函数
回调可以是任何可调用的对象。因此，您可以使用常规函数：

```php
function hello() {
    echo 'hello world!';  // 这将输出 'hello world!'
}

Flight::route('/', 'hello');
```

### 类
您也可以使用类的静态方法：

```php
class Greeting {
    public static function hello() {
        echo 'hello world!';  // 这将输出 'hello world!'
    }
}

Flight::route('/', [ 'Greeting','hello' ]);
```

或者先创建对象，然后调用方法：

```php
// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';  // 初始化名称为 'John Doe'
    }

    public function hello() {
        echo "Hello, {$this->name}!";  // 输出问候消息
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// 您也可以不先创建对象
// 注意：不会向构造函数注入参数
Flight::route('/', [ 'Greeting', 'hello' ]);
// 此外，您可以使用这种更简短的语法
Flight::route('/', 'Greeting->hello');
// 或
Flight::route('/', Greeting::class.'->hello');
```

#### 通过 DIC（依赖注入容器）进行依赖注入
如果您想通过容器（PSR-11、PHP-DI、Dice 等）进行依赖注入，那么只有直接创建对象并使用容器创建您的对象，或者使用字符串定义类和方法时才可用。您可以转到 [Dependency Injection](/learn/extending) 页面获取更多信息。

这是一个快速示例：

```php
use flight\database\PdoWrapper;

// Greeting.php
class Greeting
{
	protected PdoWrapper $pdoWrapper;
	public function __construct(PdoWrapper $pdoWrapper) {
		$this->pdoWrapper = $pdoWrapper;  // 注入 PdoWrapper 实例
	}

	public function hello(int $id) {
		// 使用 $this->pdoWrapper 进行一些操作
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Hello, world! My name is {$name}!";  // 输出问候消息
	}
}

// index.php

// 使用所需的参数设置容器
// 请参阅依赖注入页面以获取有关 PSR-11 的更多信息
$dice = new \Dice\Dice();

// 不要忘记用 '$dice = ' 重新赋值！！！！
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
	return $dice->create($class, $params);  // 使用 Dice 创建实例
});

// 如往常一样定义路由
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// 或
Flight::route('/hello/@id', 'Greeting->hello');
// 或
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## 方法路由

默认情况下，路由模式会与所有请求方法匹配。您可以通过在 URL 前面放置标识符来响应特定方法。

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';  // 这将输出 'I received a GET request.'
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';  // 这将输出 'I received a POST request.'
});

// 您不能使用 Flight::get() 来创建路由，因为那是获取变量的方法
// Flight::post('/', function() { /* code */ });
// Flight::patch('/', function() { /* code */ });
// Flight::put('/', function() { /* code */ });
// Flight::delete('/', function() { /* code */ });
```

您还可以将多个方法映射到一个回调函数中，使用 `|` 分隔符：

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';  // 这将输出 'I received either a GET or a POST request.'
});
```

此外，您可以获取 Router 对象，该对象有一些辅助方法供您使用：

```php
$router = Flight::router();

// 映射所有方法
$router->map('/', function() {
	echo 'hello world!';  // 这将输出 'hello world!'
});

// GET 请求
$router->get('/users', function() {
	echo 'users';  // 这将输出 'users'
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
  // 这将匹配 /user/1234
});
```

虽然这种方法可用，但建议使用命名参数，或带有正则表达式的命名参数，因为它们更易读且更容易维护。

## 命名参数

您可以在路由中指定命名参数，这些参数将传递给您的回调函数。**这主要是为了提高路由的可读性。请参阅下面的重要注意事项。**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";  // 输出问候消息
});
```

您也可以在命名参数中使用正则表达式，通过 `:` 分隔符：

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // 这将匹配 /bob/123
  // 但不会匹配 /bob/12345
});
```

> **Note:** 匹配正则表达式组 `()` 与位置参数不兼容。 :'\(

### 重要注意事项

虽然在上面的示例中，`@name` 似乎直接绑定到变量 `$name`，但事实并非如此。回调函数中参数的顺序决定了传递的内容。因此，如果您在回调函数中交换参数的顺序，变量也会交换。这里是一个示例：

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";  // 输出问候消息
});
```

如果您访问以下 URL：`/bob/123`，输出将是 `hello, 123 (bob)!`。请在设置路由和回调函数时小心。

## 可选参数

您可以通过在片段中添加括号来指定可选的命名参数。

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // 这将匹配以下 URL：
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

任何未匹配的可选参数将作为 `NULL` 传递。

## 通配符

匹配仅在单个 URL 片段上进行。如果您想匹配多个片段，可以使用 `*` 通配符。

```php
Flight::route('/blog/*', function () {
  // 这将匹配 /blog/2000/02/01
});
```

要将所有请求路由到一个回调函数，您可以这样做：

```php
Flight::route('*', function () {
  // 执行某些操作
});
```

## 传递

您可以通过从回调函数返回 `true` 来将执行传递给下一个匹配的路由。

```php
Flight::route('/user/@name', function (string $name) {
  // 检查某些条件
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

您可以为路由分配一个别名，以便稍后在代码中动态生成 URL（例如，在模板中使用）。

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');  // 定义路由并分配别名

// 稍后在代码中的某个地方
Flight::getUrl('user_view', [ 'id' => 5 ]);  // 将返回 '/users/5'
```

如果您的 URL 发生变化，这一点特别有用。在上面的示例中，假设 users 被移动到 `/admin/users/@id`。使用别名，您不必更改任何引用别名的位置，因为别名现在将返回 `/admin/users/5`，如上面的示例。

路由别名在组中也有效：

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');  // 定义路由并分配别名
});


// 稍后在代码中的某个地方
Flight::getUrl('user_view', [ 'id' => 5 ]);  // 将返回 '/users/5'
```

## 路由信息

如果您想检查匹配的路由信息，可以通过在路由方法中将第三个参数设置为 `true` 来请求将路由对象传递给您的回调。路由对象将始终是传递给回调函数的最后一个参数。

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 与之匹配的 HTTP 方法数组
  $route->methods;

  // 命名参数数组
  $route->params;

  // 匹配的正则表达式
  $route->regex;

  // 包含 URL 模式中任何 '*' 的内容
  $route->splat;

  // 显示 URL 路径......如果您真的需要它
  $route->pattern;

  // 显示分配给此路由的中间件
  $route->middleware;

  // 显示分配给此路由的别名
  $route->alias;
}, true);
```

## 路由分组

有时您可能希望将相关路由组合在一起（例如 `/api/v1`）。您可以通过使用 `group` 方法来实现：

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// 匹配 /api/v1/users
  });

  Flight::route('/posts', function () {
	// 匹配 /api/v1/posts
  });
});
```

您甚至可以嵌套组：

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() 用于获取变量，它不会设置路由！请参阅对象上下文下面的内容
	Flight::route('GET /users', function () {
	  // 匹配 GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // 匹配 POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // 匹配 PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() 用于获取变量，它不会设置路由！请参阅对象上下文下面的内容
	Flight::route('GET /users', function () {
	  // 匹配 GET /api/v2/users
	});
  });
});
```

### 使用对象上下文的分组

您仍然可以使用以下方式在 `Engine` 对象中使用路由分组：

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // 使用 $router 变量
  $router->get('/users', function () {
	// 匹配 GET /api/v1/users
  });

  $router->post('/posts', function () {
	// 匹配 POST /api/v1/posts
  });
});
```

### 使用中间件的分组

您还可以为路由组分配中间件：

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// 匹配 /api/v1/users
  });
}, [ MyAuthMiddleware::class ]);  // 或 [ new MyAuthMiddleware() ] 如果您想使用实例
```

请参阅 [group middleware](/learn/middleware#grouping-middleware) 页面获取更多详细信息。

## 资源路由

您可以使用 `resource` 方法为资源创建一组路由。这将创建一个遵循 RESTful 约定的资源路由集。

要创建资源，请执行以下操作：

```php
Flight::resource('/users', UsersController::class);  // 创建资源路由
```

后台会创建以下路由：

```php
[
      'index' => 'GET ',
      'create' => 'GET /create',
      'store' => 'POST ',
      'show' => 'GET /@id',
      'edit' => 'GET /@id/edit',
      'update' => 'PUT /@id',
      'destroy' => 'DELETE /@id'
]
```

您的控制器将如下所示：

```php
class UsersController
{
    public function index(): void
    {
    }

    public function show(string $id): void
    {
    }

    public function create(): void
    {
    }

    public function store(): void
    {
    }

    public function edit(string $id): void
    {
    }

    public function update(string $id): void
    {
    }

    public function destroy(string $id): void
    {
    }
}
```

> **Note**: 您可以通过运行 `php runway routes` 使用 `runway` 查看新添加的路由。

### 自定义资源路由

有几个选项可用于配置资源路由。

#### 别名基础

您可以配置 `aliasBase`。默认情况下，别名是指定 URL 的最后部分。例如 `/users/` 会导致 `aliasBase` 为 `users`。创建这些路由时，别名是 `users.index`、`users.create` 等。如果您想更改别名，请将 `aliasBase` 设置为您想要的值。

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);  // 设置别名基础
```

#### Only 和 Except

您还可以使用 `only` 和 `except` 选项指定要创建哪些路由。

```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);  // 仅创建指定的路由
```

```php
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);  // 排除指定的路由
```

这些是白名单和黑名单选项，因此您可以指定要创建哪些路由。

#### 中间件

您还可以指定要在 `resource` 方法创建的每个路由上运行的中间件。

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);  // 添加中间件
```

## 流式传输

您现在可以使用 `streamWithHeaders()` 方法向客户端流式传输响应。这对于发送大文件、长时间运行的过程或生成大响应非常有用。流式传输路由的处理方式与常规路由略有不同。

> **Note:** 流式传输响应仅在 [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) 设置为 false 时可用。

### 手动设置标头的流式传输

您可以通过在路由上使用 `stream()` 方法向客户端流式传输响应。如果这样做，您必须在输出任何内容之前手动设置所有标头。这可以使用 `header()` PHP 函数或 `Flight::response()->setRealHeader()` 方法完成。

```php
Flight::route('/@filename', function($filename) {

	// 显然，您需要对路径进行清理等操作。
	$fileNameSafe = basename($filename);  // 获取安全文件名

	// 如果您在路由执行后有其他标头需要设置
	// 您必须在回显任何内容之前定义它们。
	// 它们必须是直接调用 header() 函数或 Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');  // 设置下载标头
	// 或
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);  // 获取文件内容

	// 错误捕获等
	if(empty($fileData)) {
		Flight::halt(404, 'File not found');  // 文件未找到
	}

	// 如果您愿意，手动设置内容长度
	header('Content-Length: '.filesize($filename));  // 设置内容长度

	// 向客户端流式传输数据
	echo $fileData;

// 这是魔术行
})->stream();
```

### 使用标头的流式传输

您还可以使用 `streamWithHeaders()` 方法在开始流式传输之前设置标头。

```php
Flight::route('/stream-users', function() {

	// 您可以在这里添加任何其他您想要的标头
	// 您必须使用 header() 或 Flight::response()->setRealHeader()

	// 无论您如何获取数据，这里只是一个示例...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");  // 查询用户数据

	echo '{';  // 开始 JSON 输出
	$user_count = count($users);  // 获取用户数量
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);  // 输出用户数据
		if(--$user_count > 0) {
			echo ',';  // 添加逗号
		}

		// 这用于将数据发送给客户端
		ob_flush();  // 刷新输出缓冲
	}
	echo '}';  // 结束 JSON 输出

// 这是设置标头的方式，在您开始流式传输之前。
})->streamWithHeaders([
	'Content-Type' => 'application/json',  // 设置内容类型
	'Content-Disposition' => 'attachment; filename="users.json"',  // 设置下载文件名
	// 可选状态码，默认值为 200
	'status' => 200
]);
```