# 路由

## 概述
Flight PHP 中的路由将 URL 模式映射到回调函数或类方法，从而实现快速且简单的请求处理。它设计用于最小开销、适合初学者的使用方式，并且无需外部依赖即可扩展。

## 理解
路由是 Flight 中连接 HTTP 请求与应用程序逻辑的核心机制。通过定义路由，您可以指定不同的 URL 如何触发特定代码，无论是通过函数、类方法还是控制器动作。Flight 的路由系统灵活，支持基本模式、命名参数、正则表达式，以及高级功能如依赖注入和资源路由。这种方法使您的代码保持有序且易于维护，同时对初学者快速简单，对高级用户可扩展。

> **注意：** 想要更多了解路由？请查看 ["为什么使用框架？"](/learn/why-frameworks) 页面以获取更深入的解释。

## 基本用法

### 定义简单路由
Flight 中的基本路由通过将 URL 模式与回调函数或类和方法的数组匹配来实现。

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> 路由按照定义顺序匹配。第一个匹配请求的路由将被调用。

### 使用函数作为回调
回调可以是任何可调用的对象。因此，您可以使用常规函数：

```php
function hello() {
    echo 'hello world!';
}

Flight::route('/', 'hello');
```

### 使用类和方法作为控制器
您也可以使用类的方法（静态或非静态）：

```php
class GreetingController {
    public function hello() {
        echo 'hello world!';
    }
}

Flight::route('/', [ 'GreetingController','hello' ]);
// 或
Flight::route('/', [ GreetingController::class, 'hello' ]); // 首选方法
// 或
Flight::route('/', [ 'GreetingController::hello' ]);
// 或 
Flight::route('/', [ 'GreetingController->hello' ]);
```

或者先创建对象，然后调用方法：

```php
use flight\Engine;

// GreetingController.php
class GreetingController
{
	protected Engine $app
    public function __construct(Engine $app) {
		$this->app = $app;
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "Hello, {$this->name}!";
    }
}

// index.php
$app = Flight::app();
$greeting = new GreetingController($app);

Flight::route('/', [ $greeting, 'hello' ]);
```

> **注意：** 默认情况下，当框架内调用控制器时，`flight\Engine` 类总是被注入，除非您通过 [依赖注入容器](/learn/dependency-injection-container) 指定。

### 方法特定路由

默认情况下，路由模式与所有请求方法匹配。您可以通过在 URL 前放置标识符来响应特定方法。

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';
});

// 您不能使用 Flight::get() 来定义路由，因为那是获取变量的方法，
// 而不是创建路由。
Flight::post('/', function() { /* code */ });
Flight::patch('/', function() { /* code */ });
Flight::put('/', function() { /* code */ });
Flight::delete('/', function() { /* code */ });
```

您也可以使用 `|` 分隔符将多个方法映射到单个回调：

```php
Flight::route('GET|POST /', function () {
  echo 'I received either a GET or a POST request.';
});
```

### 使用路由器对象

此外，您可以获取路由器对象，它提供了一些辅助方法供您使用：

```php

$router = Flight::router();

// 映射所有方法，就像 Flight::route() 一样
$router->map('/', function() {
	echo 'hello world!';
});

// GET 请求
$router->get('/users', function() {
	echo 'users';
});
$router->post('/users', 			function() { /* code */});
$router->put('/users/update/@id', 	function() { /* code */});
$router->delete('/users/@id', 		function() { /* code */});
$router->patch('/users/@id', 		function() { /* code */});
```

### 正则表达式 (Regex)
您可以在路由中使用正则表达式：

```php
Flight::route('/user/[0-9]+', function () {
  // 这将匹配 /user/1234
});
```

虽然此方法可用，但推荐使用命名参数，或带有正则表达式的命名参数，因为它们更易读且易于维护。

### 命名参数
您可以在路由中指定命名参数，这些参数将被传递到您的回调函数中。**这更多是为了路由的可读性。请参阅下面的重要注意事项部分。**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

您也可以使用 `:` 分隔符将正则表达式与命名参数结合：

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // 这将匹配 /bob/123
  // 但不会匹配 /bob/12345
});
```

> **注意：** 不支持使用位置参数匹配正则表达式组 `()`。例如：`:'\(`

#### 重要注意事项

在上面的示例中，看起来 `@name` 直接绑定到变量 `$name`，但实际上并非如此。回调函数中参数的顺序决定了传递给它的内容。如果您在回调函数中切换参数顺序，变量也会相应切换。以下是一个示例：

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";
});
```

如果您访问以下 URL：`/bob/123`，输出将是 `hello, 123 (bob)!`。 
_请小心_ 设置路由和回调函数时！

### 可选参数
您可以通过用括号包围段来指定可选的命名参数，用于匹配。

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

未匹配的任何可选参数将被作为 `NULL` 传递。

### 通配符路由
匹配仅针对单个 URL 段进行。如果您想匹配多个段，可以使用 `*` 通配符。

```php
Flight::route('/blog/*', function () {
  // 这将匹配 /blog/2000/02/01
});
```

要将所有请求路由到单个回调，您可以这样做：

```php
Flight::route('*', function () {
  // 执行某些操作
});
```

### 404 未找到处理程序

默认情况下，如果找不到 URL，Flight 将发送一个非常简单且朴素的 `HTTP 404 Not Found` 响应。如果您想要更自定义的 404 响应，您可以 [映射](/learn/extending) 自己的 `notFound` 方法：

```php
Flight::map('notFound', function() {
	$url = Flight::request()->url;

	// 您也可以使用 Flight::render() 和自定义模板。
    $output = <<<HTML
		<h1>My Custom 404 Not Found</h1>
		<h3>The page you have requested {$url} could not be found.</h3>
		HTML;

	$this->response()
		->clearBody()
		->status(404)
		->write($output)
		->send();
});
```

## 高级用法

### 路由中的依赖注入
如果您想通过容器（PSR-11、PHP-DI、Dice 等）使用依赖注入，仅在直接创建对象并使用容器创建对象，或使用字符串定义类和要调用的方法时可用。您可以前往 [依赖注入](/learn/dependency-injection-container) 页面获取更多信息。

以下是一个快速示例：

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
		// 使用 $this->pdoWrapper 执行某些操作
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Hello, world! My name is {$name}!";
	}
}

// index.php

// 使用所需的参数设置容器
// 请参阅依赖注入页面以获取有关 PSR-11 的更多信息
$dice = new \Dice\Dice();

// 不要忘记使用 '$dice = ' 重新赋值变量！！！！
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

// 像往常一样定义路由
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// 或
Flight::route('/hello/@id', 'Greeting->hello');
// 或
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

### 将执行传递到下一个路由
<span class="badge bg-warning">已弃用</span>
您可以通过从回调函数返回 `true` 将执行传递到下一个匹配的路由。

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

现在推荐使用 [中间件](/learn/middleware) 来处理此类复杂用例。

### 路由别名
通过为路由分配别名，您可以在应用程序中动态调用该别名，以便稍后在代码中生成（例如：HTML 模板中的链接，或生成重定向 URL）。

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
// 或 
Flight::route('/users/@id', function($id) { echo 'user:'.$id; })->setAlias('user_view');

// 稍后在代码中的某处
class UserController {
	public function update() {

		// 保存用户的代码...
		$id = $user['id']; // 例如 5

		$redirectUrl = Flight::getUrl('user_view', [ 'id' => $id ]); // 将返回 '/users/5'
		Flight::redirect($redirectUrl);
	}
}

```

这在 URL 发生变化时特别有用。在上面的示例中，假设用户已移动到 `/admin/users/@id`。
使用路由别名，您无需在代码中查找所有旧 URL 并更改它们，因为别名现在将返回 `/admin/users/5`，如上面的示例。

路由别名在组中仍然有效：

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
	// 或
	Flight::route('/@id', function($id) { echo 'user:'.$id; })->setAlias('user_view');
});
```

### 检查路由信息
如果您想检查匹配的路由信息，有两种方式可以做到：

1. 您可以使用 `Flight::router()` 对象上的 `executedRoute` 属性。
2. 您可以通过在路由方法中将第三个参数传递为 `true` 来请求将路由对象传递到您的回调。路由对象将始终作为传递给回调函数的最后一个参数。

#### `executedRoute`
```php
Flight::route('/', function() {
  $route = Flight::router()->executedRoute;
  // 使用 $route 执行某些操作
  // 与之匹配的 HTTP 方法数组
  $route->methods;

  // 命名参数数组
  $route->params;

  // 匹配的正则表达式
  $route->regex;

  // 包含 URL 模式中任何 '*' 的内容
  $route->splat;

  // 显示 URL 路径……如果您真的需要它
  $route->pattern;

  // 显示分配给此的中间件
  $route->middleware;

  // 显示分配给此路由的别名
  $route->alias;
});
```

> **注意：** `executedRoute` 属性仅在路由执行后设置。如果您在路由执行前尝试访问它，它将是 `NULL`。您也可以在 [中间件](/learn/middleware) 中使用 executedRoute！

#### 在路由定义中传递 `true`
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

  // 显示 URL 路径……如果您真的需要它
  $route->pattern;

  // 显示分配给此的中间件
  $route->middleware;

  // 显示分配给此路由的别名
  $route->alias;
}, true);// <-- 这个 true 参数就是使它发生的原因
```

### 路由分组和中间件
有时您可能想将相关路由分组在一起（例如 `/api/v1`）。
您可以通过使用 `group` 方法来实现：

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
	// Flight::get() 获取变量，它不设置路由！请参阅下面的对象上下文
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

	// Flight::get() 获取变量，它不设置路由！请参阅下面的对象上下文
	Flight::route('GET /users', function () {
	  // 匹配 GET /api/v2/users
	});
  });
});
```

#### 使用对象上下文的分组

您仍然可以使用以下方式与 `Engine` 对象一起使用路由分组：

```php
$app = Flight::app();

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

> **注意：** 这是使用 `$router` 对象定义路由和组的首选方法。

#### 使用中间件的分组

您也可以为路由组分配中间件：

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// 匹配 /api/v1/users
  });
}, [ MyAuthMiddleware::class ]); // 或 [ new MyAuthMiddleware() ] 如果您想使用实例
```

请参阅 [组中间件](/learn/middleware#grouping-middleware) 页面以获取更多细节。

### 资源路由
您可以使用 `resource` 方法为资源创建一组路由。这将为遵循 RESTful 约定的资源创建一组路由。

要创建资源，请执行以下操作：

```php
Flight::resource('/users', UsersController::class);
```

后台将创建以下路由：

```php
[
      'index' => 'GET /users',
      'create' => 'GET /users/create',
      'store' => 'POST /users',
      'show' => 'GET /users/@id',
      'edit' => 'GET /users/@id/edit',
      'update' => 'PUT /users/@id',
      'destroy' => 'DELETE /users/@id'
]
```

您的控制器将使用以下方法：

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

> **注意**：您可以通过运行 `php runway routes` 使用 `runway` 查看新添加的路由。

#### 自定义资源路由

有几个选项可以配置资源路由。

##### 别名基

您可以配置 `aliasBase`。默认情况下，别名是指定 URL 的最后部分。
例如 `/users/` 将导致 `aliasBase` 为 `users`。创建这些路由时，
别名将是 `users.index`、`users.create` 等。如果您想更改别名，请将 `aliasBase`
设置为您想要的值。

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

##### Only 和 Except

您也可以使用 `only` 和 `except` 选项指定要创建哪些路由。

```php
// 只白名单这些方法，黑名单其余
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
// 只黑名单这些方法，白名单其余
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

这些基本上是白名单和黑名单选项，因此您可以指定要创建哪些路由。

##### 中间件

您也可以指定要在 `resource` 方法创建的每个路由上运行的中间件。

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

### 流式响应

您现在可以使用 `stream()` 或 `streamWithHeaders()` 向客户端流式传输响应。
这对于发送大文件、长时间运行的过程或生成大响应很有用。
流式传输路由的处理方式与常规路由略有不同。

> **注意：** 仅当您将 [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) 设置为 `false` 时，才支持流式响应。

#### 手动标头流式传输

您可以通过在路由上使用 `stream()` 方法向客户端流式传输响应。如果您
这样做，您必须在向客户端输出任何内容之前手动设置所有标头。
这使用 `header()` PHP 函数或 `Flight::response()->setRealHeader()` 方法完成。

```php
Flight::route('/@filename', function($filename) {

	$response = Flight::response();

	// 显然，您会清理路径等。
	$fileNameSafe = basename($filename);

	// 如果您在路由执行后有额外的标头要设置
	// 您必须在回显任何内容之前定义它们。
	// 它们必须全部是 header() 函数的原始调用或
	// 调用 Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// 或
	$response->setRealHeader('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');

	$filePath = '/some/path/to/files/'.$fileNameSafe;

	if (!is_readable($filePath)) {
		Flight::halt(404, 'File not found');
	}

	// 如果您愿意，手动设置内容长度
	header('Content-Length: '.filesize($filePath));
	// 或
	$response->setRealHeader('Content-Length: '.filesize($filePath));

	// 流式传输文件到客户端，因为它被读取
	readfile($filePath);

// 这是这里的魔法行
})->stream();
```

#### 带标头的流式传输

您也可以使用 `streamWithHeaders()` 方法在开始流式传输之前设置标头。

```php
Flight::route('/stream-users', function() {

	// 您可以在这里添加任何额外的标头
	// 您只需使用 header() 或 Flight::response()->setRealHeader()

	// 无论您如何拉取数据，仅作为示例...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// 这是在向客户端发送数据时必需的
		ob_flush();
	}
	echo '}';

// 这是您在开始流式传输之前设置标头的方式。
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// 可选状态码，默认 200
	'status' => 200
]);
```

## 另请参阅
- [中间件](/learn/middleware) - 使用中间件进行路由的认证、日志记录等。
- [依赖注入](/learn/dependency-injection-container) - 简化路由中的对象创建和管理。
- [为什么使用框架？](/learn/why-frameworks) - 理解使用像 Flight 这样的框架的好处。
- [扩展](/learn/extending) - 如何使用自己的功能扩展 Flight，包括 `notFound` 方法。
- [php.net: preg_match](https://www.php.net/manual/en/function.preg-match.php) - PHP 用于正则表达式匹配的函数。

## 故障排除
- 路由参数按顺序匹配，而不是按名称。确保回调参数顺序与路由定义匹配。
- 使用 `Flight::get()` 不会定义路由；对于路由，请使用 `Flight::route('GET /...')` 或组中的路由器对象上下文（例如 `$router->get(...)`）。
- executedRoute 属性仅在路由执行后设置；在执行前它是 NULL。
- 流式传输需要禁用遗留的 Flight 输出缓冲功能（`flight.v2.output_buffering = false`）。
- 对于依赖注入，仅某些路由定义支持基于容器的实例化。

### 404 未找到或意外路由行为

如果您看到 404 未找到错误（但您发誓它确实存在，并且不是拼写错误），这实际上可能是因为您在路由端点中返回了一个值而不是简单地回显它。这个原因是有意的，但可能会让一些开发者措手不及。

```php

Flight::route('/hello', function(){
	// 这可能会导致 404 未找到错误
	return 'Hello World';
});

// 您可能想要的
Flight::route('/hello', function(){
	echo 'Hello World';
});

```

这个原因是因为路由器中内置了一个特殊机制，将返回输出处理为“转到下一个路由”的信号。
您可以在 [路由](/learn/routing#passing) 部分查看该行为的文档。

## 更新日志
- v3：添加了资源路由、路由别名和流式传输支持、路由组和中间件支持。
- v1：绝大多数基本功能可用。