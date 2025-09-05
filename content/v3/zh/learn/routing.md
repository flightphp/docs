# 路由

> **Note:** Want to understand more about routing? Check out the ["why a framework?"](/learn/why-frameworks) page for a more in-depth explanation.

Flight 中的基本路由是通过将 URL 模式与回调函数或类和方法的数组进行匹配来实现的。

```php
Flight::route('/', function(){
    echo 'hello world!';
});
```

> Routes are matched in the order they are defined. The first route to match a request will be invoked.

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
// You also can do this without creating the object first
// Note: No args will be injected into the constructor
Flight::route('/', [ 'Greeting', 'hello' ]);
// Additionally you can use this shorter syntax
Flight::route('/', 'Greeting->hello');
// or
Flight::route('/', Greeting::class.'->hello');
```

#### 通过 DIC (依赖注入容器) 进行依赖注入
如果您想通过容器（PSR-11、PHP-DI、Dice 等）使用依赖注入，那么只有在直接创建对象并使用容器创建对象，或者使用字符串定义类和方法时才可用。您可以转到 [Dependency Injection](/learn/extending) 页面获取更多信息。

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
		// do something with $this->pdoWrapper
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "Hello, world! My name is {$name}!";
	}
}

// index.php

// 设置容器所需参数
// 有关 PSR-11 的更多信息，请参阅 Dependency Injection 页面
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

// Routes like normal
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// or
Flight::route('/hello/@id', 'Greeting->hello');
// or
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## 方法路由

默认情况下，路由模式会与所有请求方法匹配。您可以通过在 URL 前面放置标识符来响应特定方法。

```php
Flight::route('GET /', function () {
  echo 'I received a GET request.';
});

Flight::route('POST /', function () {
  echo 'I received a POST request.';
});

// You cannot use Flight::get() for routes as that is a method 
//    to get variables, not create a route.
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

此外，您可以获取 Router 对象，该对象有一些帮助方法供您使用：

```php
$router = Flight::router();

// maps all methods
$router->map('/', function() {
	echo 'hello world!';
});

// GET request
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
  // This will match /user/1234
});
```

虽然此方法可用，但建议使用命名参数或带有正则表达式的命名参数，因为它们更易读且易于维护。

## 命名参数

您可以在路由中指定命名参数，这些参数将传递给您的回调函数。**这主要是为了路由的可读性。请参阅下面关于重要注意事项的部分。**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "hello, $name ($id)!";
});
```

您还可以通过使用 `:` 分隔符在命名参数中包含正则表达式：

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // This will match /bob/123
  // But will not match /bob/12345
});
```

> **Note:** Matching regex groups `()` with positional parameters isn't supported. :'\(

### 重要注意事项

在上面的示例中，似乎 `@name` 直接绑定到变量 `$name`，但事实并非如此。回调函数中参数的顺序决定了传递的内容。如果您在回调函数中切换参数顺序，变量也会随之切换。下面是一个示例：

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "hello, $name ($id)!";
});
```

如果您访问以下 URL：`/bob/123`，输出将是 `hello, 123 (bob)!`。在设置路由和回调函数时请小心。

## 可选参数

您可以通过用括号括住段来指定路由中可选的命名参数。

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // This will match the following URLS:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

任何未匹配的可选参数将作为 `NULL` 传递。

## 通配符

匹配仅在单个 URL 段上进行。如果要匹配多个段，可以使用 `*` 通配符。

```php
Flight::route('/blog/*', function () {
  // This will match /blog/2000/02/01
});
```

要将所有请求路由到一个回调，可以执行：

```php
Flight::route('*', function () {
  // Do something
});
```

## 传递

您可以通过从回调函数返回 `true` 来将执行传递到下一个匹配路由。

```php
Flight::route('/user/@name', function (string $name) {
  // Check some condition
  if ($name !== "Bob") {
    // Continue to next route
    return true;
  }
});

Flight::route('/user/*', function () {
  // This will get called
});
```

## 路由别名

您可以为路由分配一个别名，以便稍后在代码中动态生成 URL（例如，在模板中）。

```php
Flight::route('/users/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');

// later in code somewhere
Flight::getUrl('user_view', [ 'id' => 5 ]); // will return '/users/5'
```

如果 URL 发生更改，这一点特别有用。在上面的示例中，假设 users 移动到 `/admin/users/@id`。使用别名，您无需更改任何引用别名的地方，因为别名现在将返回 `/admin/users/5`，如上面的示例。

路由别名在组中也有效：

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo 'user:'.$id; }, false, 'user_view');
});


// later in code somewhere
Flight::getUrl('user_view', [ 'id' => 5 ]); // will return '/users/5'
```

## 路由信息

如果要检查匹配的路由信息，有两种方法。您可以使用 `executedRoute` 属性，或者通过在路由方法中将第三个参数设置为 `true`，请求将路由对象传递给您的回调。路由对象将始终是传递给回调函数的最后一个参数。

```php
Flight::route('/', function(\flight\net\Route $route) {
  // Array of HTTP methods matched against
  $route->methods;

  // Array of named parameters
  $route->params;

  // Matching regular expression
  $route->regex;

  // Contains the contents of any '*' used in the URL pattern
  $route->splat;

  // Shows the url path....if you really need it
  $route->pattern;

  // Shows what middleware is assigned to this
  $route->middleware;

  // Shows the alias assigned to this route
  $route->alias;
}, true);
```

或者，如果要检查最后执行的路由，您可以执行：

```php
Flight::route('/', function() {
  $route = Flight::router()->executedRoute;
  // Do something with $route
  // Array of HTTP methods matched against
  $route->methods;

  // Array of named parameters
  $route->params;

  // Matching regular expression
  $route->regex;

  // Contains the contents of any '*' used in the URL pattern
  $route->splat;

  // Shows the url path....if you really need it
  $route->pattern;

  // Shows what middleware is assigned to this
  $route->middleware;

  // Shows the alias assigned to this route
  $route->alias;
});
```

> **Note:** The `executedRoute` property will only be set after a route has been executed. If you try to access it before a route has been executed, it will be `NULL`. You can also use executedRoute in middleware as well!

## 路由分组

有时您可能希望将相关路由组合在一起（例如 `/api/v1`）。您可以通过使用 `group` 方法来实现：

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Matches /api/v1/users
  });

  Flight::route('/posts', function () {
	// Matches /api/v1/posts
  });
});
```

您甚至可以嵌套组：

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() gets variables, it doesn't set a route! See object context below
	Flight::route('GET /users', function () {
	  // Matches GET /api/v1/users
	});

	Flight::post('/posts', function () {
	  // Matches POST /api/v1/posts
	});

	Flight::put('/posts/1', function () {
	  // Matches PUT /api/v1/posts
	});
  });
  Flight::group('/v2', function () {

	// Flight::get() gets variables, it doesn't set a route! See object context below
	Flight::route('GET /users', function () {
	  // Matches GET /api/v2/users
	});
  });
});
```

### 使用对象上下文的分组

您仍然可以使用 `Engine` 对象进行路由分组，如下所示：

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {

  // user the $router variable
  $router->get('/users', function () {
	// Matches GET /api/v1/users
  });

  $router->post('/posts', function () {
	// Matches POST /api/v1/posts
  });
});
```

### 使用中间件的分组

您还可以为路由组分配中间件：

```php
Flight::group('/api/v1', function () {
  Flight::route('/users', function () {
	// Matches /api/v1/users
  });
}, [ MyAuthMiddleware::class ]); // or [ new MyAuthMiddleware() ] if you want to use an instance
```

有关详细信息，请参阅 [group middleware](/learn/middleware#grouping-middleware) 页面。

## 资源路由

您可以使用 `resource` 方法为资源创建一组路由。这将创建一个遵循 RESTful 约定的资源路由集。

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

> **Note**: You can view the newly added routes with `runway` by running `php runway routes`.

### 自定义资源路由

有几个选项可用于配置资源路由。

#### 别名基础

您可以配置 `aliasBase`。默认情况下，别名是指定 URL 的最后部分。例如 `/users/` 将导致 `aliasBase` 为 `users`。创建这些路由时，别名是 `users.index`、`users.create` 等。如果要更改别名，请将 `aliasBase` 设置为所需的值。

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

#### Only and Except

您可以使用 `only` 和 `except` 选项指定要创建的路由。

```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

这些是白名单和黑名单选项，因此您可以指定要创建的路由。

#### 中间件

您还可以为 `resource` 方法创建的每个路由指定中间件。

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

## 流式传输

您现在可以使用 `streamWithHeaders()` 方法向客户端流式传输响应。这对于发送大文件、长时间运行的过程或生成大响应非常有用。流式传输路由的处理方式与常规路由略有不同。

> **Note:** Streaming responses is only available if you have [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) set to false.

### 手动设置头部的流式传输

您可以通过在路由上使用 `stream()` 方法向客户端流式传输响应。如果执行此操作，则必须在输出任何内容之前手动设置所有方法。这使用 `header()` PHP 函数或 `Flight::response()->setRealHeader()` 方法完成。

```php
Flight::route('/@filename', function($filename) {

	// 显然，您需要清理路径等。
	$fileNameSafe = basename($filename);

	// 如果在路由执行后有其他要设置的头，这里必须定义它们。
	// 它们必须全部是 header() 函数的原始调用或 Flight::response()->setRealHeader() 的调用。
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// or
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);

	// 错误捕获等
	if(empty($fileData)) {
		Flight::halt(404, 'File not found');
	}

	// 如果需要，手动设置内容长度
	header('Content-Length: '.filesize($filename));

	// Stream the data to the client
	echo $fileData;

// This is the magic line here
})->stream();
```

### 带有头部的流式传输

您还可以使用 `streamWithHeaders()` 方法在开始流式传输之前设置头。

```php
Flight::route('/stream-users', function() {

	// 您可以在这里添加任何其他头
	// 您必须使用 header() 或 Flight::response()->setRealHeader()

	// 无论如何获取数据，只是一个示例...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// This is required to send the data to the client
		ob_flush();
	}
	echo '}';

// This is how you'll set the headers before you start streaming.
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// optional status code, defaults to 200
	'status' => 200
]);
```