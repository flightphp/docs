# 路由

> **注意:** 想更深入了解路由吗？查看["为什么选择框架？"](/learn/why-frameworks)页面以获取更详细的解释。

在 Flight 中，基本的路由是通过将 URL 模式与回调函数或一个类和方法数组进行匹配来实现的。

```php
Flight::route('/', function(){
    echo '你好，世界！';
});
```

回调函数可以是任何可调用的对象。因此，您可以使用常规函数：

```php
function hello(){
    echo '你好，世界！';
}

Flight::route('/', 'hello');
```
或者一个类的方法：

```php
class Greeting {
    public static function hello() {
        echo '你好，世界！';
    }
}

Flight::route('/', array('Greeting','hello'));
```

或者一个对象的方法：

```php

// Greeting.php
class Greeting
{
    public function __construct() {
        $this->name = 'John Doe';
    }

    public function hello() {
        echo "你好，{$this->name}！";
    }
}

// index.php
$greeting = new Greeting();

Flight::route('/', array($greeting, 'hello'));
```

路由按照定义的顺序进行匹配。第一个匹配请求的路由将被调用。

## 方法路由

默认情况下，路由模式与所有请求方法匹配。您可以通过在 URL 之前放置一个标识符来响应特定方法。

```php
Flight::route('GET /', function () {
  echo '我收到了一个 GET 请求。';
});

Flight::route('POST /', function () {
  echo '我收到了一个 POST 请求。';
});
```

您还可以通过使用 `|` 分隔符将多个方法映射到单个回调函数：

```php
Flight::route('GET|POST /', function () {
  echo '我收到了一个 GET 或 POST 请求。';
});
```

此外，您可以获取路由器对象，该对象具有一些辅助方法供您使用：

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

您可以在您的路由中使用正则表达式：

```php
Flight::route('/user/[0-9]+', function () {
  // 这将匹配 /user/1234
});
```

尽管这种方法可用，但建议使用具有命名参数或包含正则表达式的命名参数，因为它们更可读且更易于维护。

## 命名参数

您可以在您的路由中指定命名参数，这些参数将传递给您的回调函数。

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "你好，$name ($id)!";
});
```

您还可以使用 `:` 分隔符将正则表达式与命名参数一起使用：

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // 这将匹配 /bob/123
  // 但不会匹配 /bob/12345
});
```

> **注意:** 不支持将正则表达式组 `()` 与命名参数匹配。 :'\(

## 可选参数

您可以通过将段落用括号括起来来指定可选匹配的命名参数。

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

如果未匹配任何可选参数，将作为 `NULL` 传递。

## 通配符

匹配仅在单个 URL 段上执行。如果要匹配多个段，可以使用 `*` 通配符。

```php
Flight::route('/blog/*', function () {
  // 这将匹配 /blog/2000/02/01
});
```

要将所有请求路由到一个回调函数，可以这样做：

```php
Flight::route('*', function () {
  // 做一些事情
});
```

## 传递

您可以通过从回调函数中返回 `true` 来将执行传递到下一个匹配的路由。

```php
Flight::route('/user/@name', function (string $name) {
  // 检查某些条件
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

您可以为路由分配一个别名，以便稍后在代码中动态生成 URL（例如模板）。

```php
Flight::route('/users/@id', function($id) { echo '用户：'.$id; }, false, 'user_view');

// 稍后在代码中某处
Flight::getUrl('user_view', [ 'id' => 5 ]); // 将返回 '/users/5'
```

如果您的 URL 发生更改，则此功能特别有用。在上面的示例中，假设用户被移动到 `/admin/users/@id`。
通过使用别名，您无需更改任何引用别名的地方，因为别名现在将返回 `/admin/users/5`，就像上面的示例一样。

路由别名也在组中起作用：

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo '用户：'.$id; }, false, 'user_view');
});


// 稍后在代码中某处
Flight::getUrl('user_view', [ 'id' => 5 ]); // 将返回 '/users/5'
```

## 路由信息

如果要检查匹配路由的信息，可以通过在路由方法的第三个参数中传入 `true` 来请求将路由对象传递给回调函数。路由对象始终作为最后一个参数传递给您的回调函数。

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

  // 显示 URL 路径... 如有必要
  $route->pattern;

  // 显示为此分配的中间件
  $route->middleware;

  // 显示为此路由分配的别名
  $route->alias;
}, true);
```

## 路由分组

有时您可能希望将相关路由（例如 `/api/v1`）分组在一起。您可以使用 `group` 方法来实现：

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

甚至可以嵌套组的组：

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

### 使用对象上下文进行分组

您仍然可以使用 `Engine` 对象中的路由分组，如以下方式：

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

## 流式传输

现在您可以使用 `streamWithHeaders()` 方法将响应流式传输到客户端。
这对于发送大文件、长时间运行的进程或生成大型响应非常有用。
流式传输路由的处理方式与常规路由略有不同。

> **注意:** 仅当您将 [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) 设置为 false 时才能使用流式传输响应。

```php
Flight::route('/stream-users', function() {

	// 无论您如何获取数据，只是一个示例...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// 发送数据至客户端的必需操作
		ob_flush();
	}
	echo '}';

// 这是在开始流式传输之前设置头信息的方式。
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	// 可选状态码，默认为 200
	'status' => 200
]);
```