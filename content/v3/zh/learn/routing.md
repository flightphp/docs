# 路由

> **注意：** 想了解更多关于路由的内容吗？请查看["为什么选择框架？"](/learn/why-frameworks)页面以获取更深入的解释。

在 Flight 中，基本路由是通过将 URL 模式与回调函数或类和方法的数组匹配来完成的。

```php
Flight::route('/', function(){
    echo '你好，世界！';
});
```

> 路由按照定义的顺序进行匹配。第一个匹配请求的路由将被调用。

### 回调/函数
回调可以是任何可调用的对象。因此，您可以使用常规函数：

```php
function hello() {
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

或者先创建一个对象，然后调用该方法：

```php

// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = '约翰·多';
    }

    public function hello() {
        echo "你好，{$this->name}!";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', [ $greeting, 'hello' ]);
// 您也可以在未先创建对象的情况下执行此操作
// 注意：没有参数将被注入到构造函数
Flight::route('/', [ 'Greeting', 'hello' ]);
// 此外，您还可以使用更短的语法
Flight::route('/', 'Greeting->hello');
// 或者
Flight::route('/', Greeting::class.'->hello');
```

#### 通过 DIC（依赖注入容器）进行依赖注入
如果您想通过容器（PSR-11，PHP-DI，Dice 等）使用依赖注入，
唯一可用的路由类型是直接自己创建对象并使用容器创建对象，或者可以使用字符串来定义要调用的类和方法。您可以查看[依赖注入](/learn/extending)页面以获取更多信息。

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
		// 对 $this->pdoWrapper 做一些事情
		$name = $this->pdoWrapper->fetchField("SELECT name FROM users WHERE id = ?", [ $id ]);
		echo "你好，世界！我的名字是 {$name}!";
	}
}

// index.php

// 设置容器，带上您需要的参数
// 有关 PSR-11 的更多信息，请查看依赖注入页面
$dice = new \Dice\Dice();

// 别忘了要重新分配变量为 '$dice = '!!!!!
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

// 像往常一样路由
Flight::route('/hello/@id', [ 'Greeting', 'hello' ]);
// 或
Flight::route('/hello/@id', 'Greeting->hello');
// 或
Flight::route('/hello/@id', 'Greeting::hello');

Flight::start();
```

## 方法路由

默认情况下，路由模式会与所有请求方法进行匹配。您可以通过在 URL 之前添加标识符来响应特定方法。

```php
Flight::route('GET /', function () {
  echo '我收到了一个 GET 请求。';
});

Flight::route('POST /', function () {
  echo '我收到了一个 POST 请求。';
});

// 您不能使用 Flight::get() 来创建路由，因为那是一个用于获取变量的方法，而不是创建路由。
// Flight::post('/', function() { /* 代码 */ });
// Flight::patch('/', function() { /* 代码 */ });
// Flight::put('/', function() { /* 代码 */ });
// Flight::delete('/', function() { /* 代码 */ });
```

您也可以通过使用 `|` 分隔符将多个方法映射到单个回调：

```php
Flight::route('GET|POST /', function () {
  echo '我收到了一个 GET 或 POST 请求。';
});
```

此外，您可以获取具有一些助手方法的 Router 对象：

```php

$router = Flight::router();

// 映射所有方法
$router->map('/', function() {
	echo '你好，世界！';
});

// GET 请求
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
  // 这将匹配 /user/1234
});
```

虽然此方法可用，但建议使用命名参数，或与正则表达式结合的命名参数，因为它们更具可读性且更易于维护。

## 命名参数

您可以在路由中指定命名参数，这些参数将传递给回调函数。**这更是为了路由的可读性，而非其他原因。请参见下面关于重要注意事项的部分。**

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "你好，$name ($id)!";
});
```

您还可以通过使用 `:` 分隔符将正则表达式包含在命名参数中：

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // 这将匹配 /bob/123
  // 但不会匹配 /bob/12345
});
```

> **注意：** 匹配正则组 `()` 和位置参数不受支持。:'(

### 重要注意事项

虽然在上面的示例中，似乎 `@name` 直接与变量 `$name` 绑定，但实际上并非如此。回调函数中参数的顺序决定了传递给它的内容。因此，如果您更改回调函数中参数的顺序，变量也会随之交换。以下是一个示例：

```php
Flight::route('/@name/@id', function (string $id, string $name) {
  echo "你好，$name ($id)!";
});
```

如果您访问以下 URL：`/bob/123`，输出将是 `你好，123 (bob)!`。在设置路由和回调函数时，请小心。

## 可选参数

您可以通过将段括在括号中来指定可选的命名参数以进行匹配。

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

匹配仅针对单个 URL 段进行。如果您想匹配多个段，可以使用 `*` 通配符。

```php
Flight::route('/blog/*', function () {
  // 这将匹配 /blog/2000/02/01
});
```

要将所有请求路由到单个回调，您可以这样做：

```php
Flight::route('*', function () {
  // 做一些事情
});
```

## 传递

您可以通过从回调函数返回 `true` 将执行权传递给下一个匹配路由。

```php
Flight::route('/user/@name', function (string $name) {
  // 检查某个条件
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

您可以为路由分配别名，以便稍后在代码中动态生成 URL（例如作为模板）。

```php
Flight::route('/users/@id', function($id) { echo '用户:'.$id; }, false, 'user_view');

// 稍后在代码的某个地方
Flight::getUrl('user_view', [ 'id' => 5 ]); // 将返回 '/users/5'
```

这在您的 URL 发生变化时特别有用。在上面的示例中，假设用户被移到 `/admin/users/@id` 。
有了别名后，您不必更改在任何地方引用别名的地方，因为别名将现在返回 `/admin/users/5`，就像上面的示例一样。

路由别名仍然可以在组中使用：

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo '用户:'.$id; }, false, 'user_view');
});

// 稍后在代码的某个地方
Flight::getUrl('user_view', [ 'id' => 5 ]); // 将返回 '/users/5'
```

## 路由信息

如果您想检查匹配路由的信息，可以通过在路由方法中传入 `true` 作为第三个参数来请求将路由对象传递给回调。路由对象将始终是传递给回调函数的最后一个参数。

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 匹配的 HTTP 方法数组
  $route->methods;

  // 命名参数数组
  $route->params;

  // 匹配的正则表达式
  $route->regex;

  // 包含 URL 模式中使用的任何 '*' 的内容
  $route->splat;

  // 显示 URL 路径....如果您真的需要它
  $route->pattern;

  // 显示分配给此路由的中间件
  $route->middleware;

  // 显示分配给此路由的别名
  $route->alias;
}, true);
```

## 路由分组

有时候您可能希望将相关路由分组在一起（例如 `/api/v1`）。
您可以使用 `group` 方法来实现：

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
	// Flight::get() 获取变量，不设置路由！请参见下面的对象上下文
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

	// Flight::get() 获取变量，不设置路由！请参见下面的对象上下文
	Flight::route('GET /users', function () {
	  // 匹配 GET /api/v2/users
	});
  });
});
```

### 使用对象上下文的分组

您仍然可以与 `Engine` 对象一起使用路由分组，如下所示：

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

## 资源路由

您可以使用 `resource` 方法为资源创建一组路由。这将创建遵循 RESTful 规范的一组路由。

要创建资源，请执行以下操作：

```php
Flight::resource('/users', UsersController::class);
```

在后台将发生以下内容：

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

而您的控制器将如下所示：

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

> **注意**：您可以通过运行 `php runway routes` 来查看新增的路由。

### 自定义资源路由

有一些选项可以配置资源路由。

#### 别名基础

您可以配置 `aliasBase`。 默认情况下，别名是指定 URL 的最后部分。
例如 `/users/` 将导致别名为 `users`。 创建这些路由时，别名为 `users.index`、`users.create` 等。如果您想更改别名，请将 `aliasBase` 设置为您想要的值。

```php
Flight::resource('/users', UsersController::class, [ 'aliasBase' => 'user' ]);
```

#### 仅和排除

您还可以通过使用 `only` 和 `except` 选项来指定要创建的路由。

```php
Flight::resource('/users', UsersController::class, [ 'only' => [ 'index', 'show' ] ]);
```

```php
Flight::resource('/users', UsersController::class, [ 'except' => [ 'create', 'store', 'edit', 'update', 'destroy' ] ]);
```

这些实际上是白名单和黑名单选项，以便您可以指定要创建的路由。

#### 中间件

您还可以指定在 `resource` 方法创建的每个路由上运行的中间件。

```php
Flight::resource('/users', UsersController::class, [ 'middleware' => [ MyAuthMiddleware::class ] ]);
```

## 流式传输

您现在可以使用 `streamWithHeaders()` 方法将响应流式传输到客户端。
这对于发送大文件、长时间运行的过程或生成大量响应很有用。
流式传输路由的处理方式与常规路由略有不同。

> **注意：** 如果您将 [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) 设置为 false，则仅在此情况下可以使用流式响应。

### 使用手动标头流式传输

您可以通过在路由上使用 `stream()` 方法将响应流式传输到客户端。如果您这样做，必须在将任何内容输出到客户端之前手动设置所有方法。
这可以通过 `header()` php 函数或 `Flight::response()->setRealHeader()` 方法完成。

```php
Flight::route('/@filename', function($filename) {

	// 显然，您需要清理路径等内容。
	$fileNameSafe = basename($filename);

	// 如果您在路由执行后有其他要设置的标头，
	// 您必须在任何内容被输出之前定义它们。
	// 它们必须全部是对 header() 函数的原始调用或
	// 调用 Flight::response()->setRealHeader()
	header('Content-Disposition: attachment; filename="'.$fileNameSafe.'"');
	// 或者
	Flight::response()->setRealHeader('Content-Disposition', 'attachment; filename="'.$fileNameSafe.'"');

	$fileData = file_get_contents('/some/path/to/files/'.$fileNameSafe);

	// 错误捕获等
	if(empty($fileData)) {
		Flight::halt(404, '文件未找到');
	}

	// 如果您喜欢，可以手动设置内容长度
	header('Content-Length: '.filesize($filename));

	// 将数据流式传输到客户端
	echo $fileData;

// 这是这里的魔法行
})->stream();
```

### 使用标头流式传输

您还可以使用 `streamWithHeaders()` 方法在开始流式传输之前设置标头。

```php
Flight::route('/stream-users', function() {

	// 您可以在此处添加任何额外的标头
	// 您必须使用 header() 或 Flight::response()->setRealHeader()

	// 无论您如何提取数据，举个例子...
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

// 在您开始流式传输之前设置标头。
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	'Content-Disposition' => 'attachment; filename="users.json"',
	// 可选状态码，默认为 200
	'status' => 200
]);
```