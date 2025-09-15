# 路由

> **注意:** 想要更深入地了解路由？请查看 ["why a framework?"](/learn/why-frameworks) 页面以获取更详细的解释。

在 Flight 中，基本路由是通过匹配 URL 模式与回调函数或类和方法的数组来实现的。

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> 路由按定义的顺序匹配。第一个匹配请求的路由将被调用。

### 回调/函数
回调可以是任何可调用的对象。因此，您可以使用常规函数：

```php
function hello() {
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

### 类
您也可以使用类的静态方法：

```php
class Greeting {
    public static function hello() {
        echo 'hello world!';
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
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Hello, {$this->name}!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// 您也可以不先创建对象
// 注意：构造函数不会注入任何参数
Flight::route('/', [ 'Greeting', 'hello' ]);
// 此外，您可以使用这种更短的语法
Flight::route('/', 'Greeting->hello');
// 或
Flight::route('/', Greeting::class.'->hello');
```

#### 通过 DIC (依赖注入容器) 进行依赖注入
如果您想通过容器使用依赖注入 (PSR-11、PHP-DI、Dice 等)，那么只有直接创建对象并使用容器创建您的对象，或者使用字符串定义类和方法时才可用。您可以转到 [依赖注入](/learn/extending) 页面获取更多信息。

这是一个快速示例：

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
		// 使用 $this->pdoWrapper 做一些事情
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Hello, world! My name is {$name}!";
	}
}

// index.php

// 使用所需的参数设置容器
// 请参阅依赖注入页面获取有关 PSR-11 的更多信息
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
	return $dice->create($class, $params);
});

// 像正常一样定义路由
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// 或
Flight::route('/hello/@id', 'Greeting->hello');
// 或
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## 方法路由

默认情况下，路由模式会匹配所有请求方法。您可以通过在 URL 前面放置标识符来响应特定方法。

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';
});

// 您不能使用 Flight::get() 来创建路由，因为那是获取变量的方法
// Flight::post('/', function() { /* code */ });
// Flight::patch('/', function() { /* code */ });
// Flight::put('/', function() { /* code */ });
// Flight::delete('/', function() { /* code */ });
```

您还可以使用 `|` 分隔符将多个方法映射到单个回调：

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';
});
```

此外，您可以获取 Router 对象，该对象有一些辅助方法供您使用：

```php
$router = Flight::router();

// 映射所有方法
$router->map('/', function() {
	echo 'hello world!';
});

// GET 请求
$router->get('/users', function() {
	echo 'users';
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

虽然这种方法可用，但推荐使用命名参数，或带有正则表达式的命名参数，因为它们更易读且更容易维护。

## 命名参数

您可以在路由中指定命名参数，这些参数将传递给您的回调函数。**这主要是为了路由的可读性。请参阅下面的重要注意事项。**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

您还可以使用 `:` 分隔符在命名参数中包含正则表达式：

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // 这将匹配 /bob/123
  // 但不会匹配 /bob/12345
});
```

> **注意:** 匹配正则组 `()` 与位置参数不兼容。:'\(

### 重要注意事项

虽然在上面的示例中，似乎 `@name` 直接绑定到变量 `$name`，但事实并非如此。回调函数中参数的顺序决定了传递的内容。因此，如果您在回调函数中交换参数顺序，变量也会交换。例如：

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";
});
```

如果您访问以下 URL：`/bob/123`，输出将是 `hello, 123 (bob)!`。在设置路由和回调函数时请小心。

## 可选参数

您可以通过用括号括起段来指定路由中可选的命名参数。

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

匹配仅在单个 URL 段上进行。如果您想匹配多个段，可以使用 `*` 通配符。

```php
Flight::route('/blog/*', function () {
  // 这将匹配 /blog/2000/02/01
});
```

要将所有请求路由到一个单个回调，可以这样做：

```php
Flight::route('*', function () {
  // 做一些事情
});
```

## 传递

您可以通过从回调函数返回 `true` 来将执行传递给下一个匹配路由。

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

您可以为路由分配一个别名，以便稍后在代码中动态生成 URL（例如，在模板中）。

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// 稍后在代码中的某个地方
Flight::getUrl('user_view', [ 'id' => 5 ]); // 将返回 '/users/5'
```

如果您的 URL 发生变化，这一点特别有用。在上面的示例中，假设 users 移动到 `/admin/users/@id` 了。
使用别名，您不必更改任何引用别名的地方，因为别名现在将返回 `/admin/users/5`，如示例中所示。

路由别名在组中也能正常工作：

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// 稍后在代码中的某个地方
Flight::getUrl('user_view', [ 'id' => 5 ]); // 将返回 '/users/5'
```

## 路由信息

如果您想检查匹配的路由信息，有两种方式。您可以使用 `executedRoute` 属性，或者通过在路由方法中将第三个参数设置为 `true`，来请求将路由对象传递给您的回调。路由对象将始终是传递给回调函数的最后一个参数。

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

  // 显示 URL 路径....如果您真的需要它
  $route->pattern;

  // 显示分配给此路由的中间件
  $route->middleware;

  // 显示分配给此路由的别名
  $route->alias;
}, true);
```

或者，如果您想检查最后执行的路由，可以这样做：

```php
Flight::route('/', function() {
  $route = Flight::router()->executedRoute;
  // 使用 $route 做一些事情
  // 与之匹配的 HTTP 方法数组
  $route->methods;

  // 命名参数数组
  $route->params;

  // 匹配的正则表达式
  $route->regex;

  // 包含 URL 模式中任何 '*' 的内容
  $route->splat;

  // 显示 URL 路径....如果您真的需要它
  $route->pattern;

  // 显示分配给此路由的中间件
  $route->middleware;

  // 显示分配给此路由的别名
  $route->alias;
});
```

> **注意:** `executedRoute` 属性仅在路由执行后设置。如果您在路由执行前尝试访问它，将是 `NULL`。您也可以在中间件中使用 executedRoute！

## 路由分组

有时您可能希望将相关路由组合在一起（如 `/api/v1`）。您可以通过使用 `group` 方法来实现：

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
	// Flight::get() 是获取变量的，它不会设置路由！请参阅对象上下文下面的内容
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

	// Flight::get() 是获取变量的，它不会设置路由！请参阅对象上下文下面的内容
	Flight::route('GET /users', function () {
	  // 匹配 GET /api/v2/users
	});
  });
});
```

### 使用对象上下文的分组

您仍然可以使用 `Engine` 对象进行路由分组，如下所示：

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
}, [ MyAuthMiddleware::class ]); // 或 [ new MyAuthMiddleware() ] 如果您想使用实例
```

请参阅 [组中间件](/learn/middleware#grouping-middleware) 页面获取更多细节。

## 资源路由

您可以使用 `resource` 方法为资源创建一组路由。这将创建一个遵循 RESTful 约定的路由集。

要创建资源，请执行以下操作：

```php
Flight::resource('/users', UsersController::class);
```

后台将创建以下路由：

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

> **注意**: 您可以通过运行 `php runway routes` 在 `runway` 中查看新添加的路由。

### 自定义资源路由

有一些选项可以配置资源路由。

#### 别名基础

您可以配置 `aliasBase`。默认情况下，别名是指定 URL 的最后部分。例如 `/users/` 将导致 `aliasBase` 为 `users`。创建这些路由时，别名将是 `users.index`、`users.create` 等。如果您想更改别名，请将 `aliasBase` 设置为您想要的值。

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

#### Only 和 Except

您可以使用 `only` 和 `except` 选项指定要创建哪些路由。

```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

这些是白名单和黑名单选项，因此您可以指定要创建哪些路由。

#### 中间件

您还可以指定要在 `resource` 方法创建的每个路由上运行的中间件。

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

## 流式传输

您现在可以使用 `streamWithHeaders()` 方法向客户端流式传输响应。这对于发送大文件、长时间运行的过程或生成大响应非常有用。流式传输路由的处理方式与常规路由略有不同。

> **注意:** 流式响应仅在 [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) 设置为 false 时可用。

### 带有手动头部的流式传输

您可以通过在路由上使用 `stream()` 方法向客户端流式传输响应。如果这样做，您必须在输出任何内容之前手动设置所有头。这可以使用 `header()` PHP 函数或 `Flight::response()->setRealHeader()` 方法完成。

```php
Flight::route('/@filename', function($filename) {

	// 显然，您需要清理路径等。
	$fileNameSafe = basename($filename);

	// 如果您在路由执行后有额外的头要设置
	// 您必须在回显任何内容之前定义它们。
	// 它们必须全部是直接调用 header() 函数或 Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// 或
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$filePath = '/some/path/to/files/'.$fileNameSafe;

	if (!is_readable($filePath)) {
		Flight::halt(404, 'File not found');
	}

	// 如果您愿意，手动设置内容长度
	header('Content-Length: '.filesize($filePath));

	// 将文件作为读取时流式传输到客户端
	readfile($filePath);

// 这是这里的魔术行
})->stream();
```

### 带有头部的流式传输

您还可以使用 `streamWithHeaders()` 方法在开始流式传输之前设置头。

```php
Flight::route('/stream-users', function() {

	// 您可以在这里添加任何额外的头
	// 您必须使用 header() 或 Flight::response()->setRealHeader()

	// 无论您如何获取数据，只是一个示例...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// 这是必需的，以将数据发送到客户端
		ob_flush();
	}
	echo '}';

// 这是您在开始流式传输之前设置头的方式。
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// 可选状态码，默认值为 200
	'status' => 200
]);
```