# 路由

> **注意:** 想要了解更多关于路由的信息？请查看[为什么使用框架](/learn/why-frameworks)页面以获取更详细的解释。

在 Flight 中，基本的路由是通过将 URL 模式与回调函数或一个类和方法数组进行匹配来实现的。

```php
Flight::route('/', function(){
    echo '你好，世界！';
});
```

回调函数可以是可调用的任何对象。因此，您可以使用常规函数：

```php
function hello(){
    echo '你好，世界！';
}

Flight::route('/', 'hello');
```

或者类方法：

```php
class Greeting {
    public static function hello() {
        echo '你好，世界！';
    }
}

Flight::route('/', array('Greeting','hello'));
```

或者对象方法：

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

路由按照它们定义的顺序匹配。第一个匹配请求的路由将被调用。

## 方法路由

默认情况下，路由模式与所有请求方法匹配。您可以通过在 URL 前放置一个标识符来响应特定方法。

```php
Flight::route('GET /', function () {
  echo '我收到了一个 GET 请求。';
});

Flight::route('POST /', function () {
  echo '我收到了一个 POST 请求。';
});
```

您还可以通过使用 `|` 分隔符将多个方法映射到单个回调：

```php
Flight::route('GET|POST /', function () {
  echo '我收到了一个 GET 或 POST 请求。';
});
```

此外，您可以获取路由器对象，该对象具有一些可供您使用的辅助方法：

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

尽管此方法可用，但建议使用命名参数，或者带正则表达式的命名参数，因为它们更易读且更易维护。

## 命名参数

您可以在您的路由中指定命名参数，这些参数将传递给您的回调函数。

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "你好，$name ($id)！";
});
```

您还可以使用 `:` 分隔符在命名参数中包含正则表达式：

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // 这将匹配 /bob/123
  // 但不会匹配 /bob/12345
});
```

> **注意:** 不支持将带命名参数的正则表达式组 `()` 进行匹配。 :'\(

## 可选参数

您可以通过将段包装在括号中指定可选的匹配命名参数。

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // 这将匹配以下的 URL：
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

任何未匹配的可选参数都将作为 `NULL` 传递。

## 通配符

匹配仅在单独的 URL 段上进行。如果您希望匹配多个段，可以使用 `*` 通配符。

```php
Flight::route('/blog/*', function () {
  // 这将匹配 /blog/2000/02/01
});
```

要将所有请求路由到单个回调，可以执行：

```php
Flight::route('*', function () {
  // 做一些事情
});
```

## 传递

您可以通过从回调函数中返回 `true` 将执行传递到下一个匹配的路由。

```php
Flight::route('/user/@name', function (string $name) {
  // 检查一些条件
  if ($name !== "王五") {
    // 继续下一个路由
    return true;
  }
});

Flight::route('/user/*', function () {
  // 这将被调用
});
```

## 路由别名

您可以为路由指定别名，以便稍后在您的代码中动态生成 URL（例如模板）。

```php
Flight::route('/users/@id', function($id) { echo '用户:'.$id; }, false, 'user_view');

// 稍后在代码中的某处
Flight::getUrl('user_view', [ 'id' => 5 ]); // 将返回 '/users/5'
```

如果您的 URL 发生变化，则这将非常有帮助。在上面的示例中，假设用户已被移动到 `/admin/users/@id`。
有了别名，您无需更改引用别名的任何位置，因为别名现在将返回类似于上面示例中的 `/admin/users/5`。

路由别名在组中仍然有效：

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo '用户:'.$id; }, false, 'user_view');
});


// 稍后在代码中的某处
Flight::getUrl('user_view', [ 'id' => 5 ]); // 将返回 '/users/5'
```

## 路由信息

如果您想要检查匹配的路由信息，可以通过将第三个参数设置为 `true` 传递路由对象给您的回调函数。路由对象将始终作为传递给您的回调函数的最后一个参数。

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

  // 显示 URL 路径....如果您确实需要它
  $route->pattern;

  // 显示分配给此路由的中间件
  $route->middleware;

  // 显示分配给此路由的别名
  $route->alias;
}, true);
```

## 路由分组

有时您想要将相关的路由组合在一起（例如 `/api/v1`）。您可以使用 `group` 方法来实现此目的：

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

您甚至可以嵌套组的组：

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get() 获取变量，它不设置路由！请查看以下的对象上下文
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

	// Flight::get() 获取变量，它不设置路由！请查看以下的对象上下文
	Flight::route('GET /users', function () {
	  // 匹配 GET /api/v2/users
	});
  });
});
```

### 在对象上下文中进行分组

您仍可以以以下方式使用 `Engine` 对象进行路由分组：

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