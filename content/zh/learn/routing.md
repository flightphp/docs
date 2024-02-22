# 路由

> **注意：** 想了解更多关于路由的信息吗？查看["为什么选择框架？"](/learn/why-frameworks)页面以获取更深入的解释。

在Flight中，基本路由是通过将URL模式与回调函数或类和方法的数组进行匹配来完成的。

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

或是类的方法：

```php
class Greeting {
    public static function hello() {
        echo '你好，世界！';
    }
}

Flight::route('/', array('Greeting','hello'));
```

或者是对象的方法：

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

Flight::route('/', array($greeting, 'hello'));
```

路由按定义顺序进行匹配。第一个匹配请求的路由将被调用。

## 方法路由

默认情况下，路由模式会与所有请求方法进行匹配。您可以通过在URL之前放置一个标识符来响应特定方法。

```php
Flight::route('GET /', function () {
  echo '我收到了一个GET请求。';
});

Flight::route('POST /', function () {
  echo '我收到了一个POST请求。';
});
```

您还可以通过使用 `|` 分隔符将多个方法映射到单个回调函数：

```php
Flight::route('GET|POST /', function () {
  echo '我收到了一个GET或POST请求。';
});
```

此外，您可以获取路由器对象，其中包含一些可供您使用的辅助方法：

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
  // 这将匹配 /user/1234
});
```

虽然可以使用这种方法，但建议使用具名参数或具名参数与正则表达式结合，因为它们更易读且更易于维护。

## 具名参数

您可以在路由中指定要传递到回调函数的具名参数。

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "你好，$name（$id）！";
});
```

您还可以使用具名参数并结合正则表达式，方法是使用 `:` 分隔符：

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // 这将匹配 /bob/123
  // 但不会匹配 /bob/12345
});
```

> **注意：** 不支持使用具名参数匹配正则表达式组 `()`。：'\(

## 可选参数

您可以指定在匹配时是可选的具名参数，方法是将片段用括号括起来。

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

未匹配的任何可选参数都将传递为 `NULL`。

## 通配符

仅对个别URL段进行匹配。如果要匹配多个段，可以使用 `*` 通配符。

```php
Flight::route('/blog/*', function () {
  // 这将匹配 /blog/2000/02/01
});
```

要将所有请求路由到单个回调函数，可以执行以下操作：

```php
Flight::route('*', function () {
  // 做些什么
});
```

## 传递

您可以通过在回调函数中返回 `true` 来将执行传递到下一个匹配的路由。

```php
Flight::route('/user/@name', function (string $name) {
  // 检查某些条件
  if ($name !== "张三") {
    // 继续到下一个路由
    return true;
  }
});

Flight::route('/user/*', function () {
  // 这将会被调用
});
```

## 路由别名

您可以为路由指定一个别名，以便稍后在代码中动态生成URL（例如像模板一样）。

```php
Flight::route('/users/@id', function($id) { echo '用户：'.$id; }, false, 'user_view');

// 在代码的其他地方
Flight::getUrl('user_view', [ 'id' => 5 ]); // 将返回 '/users/5'
```

如果您的URL恰好更改，这将非常有帮助。在上面的示例中，假设将用户移至 `/admin/users/@id`。
有了别名，您无需更改引用别名的任何位置，因为别名现在将返回 `/admin/users/5`，就像上面的示例一样。

路由别名在组中仍然有效：

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo '用户：'.$id; }, false, 'user_view');
});


// 在代码的其他地方
Flight::getUrl('user_view', [ 'id' => 5 ]); // 将返回 '/users/5'
```

## 路由信息

如果要检查匹配的路由信息，可以通过在路由方法中传入 `true` 作为第三个参数来请求将路由对象传递给回调函数。路由对象始终是传递给回调函数的最后一个参数。

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 匹配的HTTP方法数组
  $route->methods;

  // 具名参数数组
  $route->params;

  // 匹配的正则表达式
  $route->regex;

  // 包含URL模式中使用的任何 '*' 的内容
  $route->splat;

  // 显示URL路径....如果您真的需要
  $route->pattern;

  // 显示分配给此路由的中间件
  $route->middleware;

  // 显示分配给此路由的别名
  $route->alias;
}, true);
```

## 路由分组

有时您可能需要将相关路由（例如 `/api/v1`）分组在一起。您可以通过使用 `group` 方法来实现这一点：

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
	// Flight::get() 获取变量，不会设置路由！查看下面的对象上下文
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

	// Flight::get() 获取变量，不会设置路由！查看下面的对象上下文
	Flight::route('GET /users', function () {
	  // 匹配 GET /api/v2/users
	});
  });
});
```

### 使用对象上下文进行分组

您仍然可以使用 `Engine` 对象与路由分组，方法如下：

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

您现在可以使用 `streamWithHeaders()` 方法将响应流式传输到客户端。
这对于发送大文件、长时间运行的进程或生成大型响应非常有用。
流式传输路由的处理方式略有不同于常规路由。

> **注意：** 仅当您的 [`flight.v2.output_buffering`](/learn/migrating-to-v3#output_buffering) 设置为 false 时才可以使用流式传输响应。

```php
Flight::route('/stream-users', function() {

	// 无论您如何获取您的数据，仅作为示例...
	$users_stmt = Flight::db()->query("SELECT id, first_name, last_name FROM users");

	echo '{';
	$user_count = count($users);
	while($user = $users_stmt->fetch(PDO::FETCH_ASSOC)) {
		echo json_encode($user);
		if(--$user_count > 0) {
			echo ',';
		}

		// 此操作是将数据发送给客户端所必需的
		ob_flush();
	}
	echo '}';

// 这是开始流式传输之前设置标头的方法。
})->streamWithHeaders([
	'Content-Type' => 'application/json',
	// 可选的状态码，默认为 200
	'status' => 200
]);