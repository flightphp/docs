```zh
# 路由

> **注意:** 想了解更多关于路由的内容吗？查看[为什么要使用框架](/learn/why-frameworks)页面，了解更详细的解释。

Flight中的基本路由是通过将URL模式与回调函数或一个类和方法的数组进行匹配来完成的。

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

或者一个类方法：

```php
class Greeting {
    public static function hello() {
        echo '你好，世界！';
    }
}

Flight::route('/', array('Greeting','hello'));
```

或者一个对象方法：

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

路由按照定义的顺序进行匹配。第一个匹配到请求的路由将被调用。

## 方法路由

默认情况下，路由模式将与所有请求方法匹配。您可以通过在URL前放置标识符来响应特定方法。

```php
Flight::route('GET /', function () {
  echo '我收到了一个GET请求。';
});

Flight::route('POST /', function () {
  echo '我收到了一个POST请求。';
});
```

您还可以使用`|`分隔符将多个方法映射到单个回调：

```php
Flight::route('GET|POST /', function () {
  echo '我收到了一个GET或POST请求。';
});
```

此外，您可以获取Router对象，该对象具有一些可供您使用的辅助方法：

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

您可以在您的路由中使用正则表达式：

```php
Flight::route('/user/[0-9]+', function () {
  // 这将匹配/user/1234
});
```

虽然此方法可用，但建议使用命名参数，或带有正则表达式的命名参数，因为它们更易读和更易于维护。

## 命名参数

您可以在路由中指定命名参数，这些参数将传递给您的回调函数。

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "你好，$name ($id)！";
});
```

您还可以在命名参数中使用正则表达式，方法是使用`:`分隔符：

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // 这将匹配/bob/123
  // 但不会匹配/bob/12345
});
```

> **注意:** 与命名参数匹配正则表达式组`()`不受支持。:'\(

## 可选参数

您可以指定命名参数为可选匹配，方法是将段分组在括号中。

```php
Flight::route(
  '/blog(/@year(/@month(/@day)))',
  function(?string $year, ?string $month, ?string $day) {
    // 这将匹配以下URLS:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
  }
);
```

任何未匹配的可选参数将作为`NULL`传入。

## 通配符

仅在单独的URL段上执行匹配。如果要匹配多个段，您可以使用`*`通配符。

```php
Flight::route('/blog/*', function () {
  // 这将匹配/blog/2000/02/01
});
```

要将所有请求路由到单个回调，您可以执行以下操作：

```php
Flight::route('*', function () {
  // 执行某些操作
});
```

## 传递

您可以通过从回调函数返回`true`来将执行传递给下一个匹配的路由。

```php
Flight::route('/user/@name', function (string $name) {
  // 检查某个条件
  if ($name !== "张三") {
    // 继续下一个路由
    return true;
  }
});

Flight::route('/user/*', function () {
  // 这将被调用
});
```

## 路由别名

您可以为路由指定别名，以便稍后在您的代码中动态生成URL（例如模板）。

```php
Flight::route('/users/@id', function($id) { echo '用户:'.$id; }, false, 'user_view');

// 稍后在代码中的某处
Flight::getUrl('user_view', [ 'id' => 5 ]); // 将返回'/users/5'
```

如果您的URL恰好发生更改，则这将特别有帮助。在上面的示例中，假设用户被移动到`/admin/users/@id`：
有了别名功能，您无需更改引用别名的任何地方，因为别名现在将返回`/admin/users/5`，就像上面的示例一样。

路由别名也适用于组：

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo '用户:'.$id; }, false, 'user_view');
});


// 稍后在代码中的某处
Flight::getUrl('user_view', [ 'id' => 5 ]); // 将返回'/users/5'
```

## 路由信息

如果您想要检查匹配的路由信息，您可以通过在路由方法的第三个参数中传入`true`来请求路由对象传递给您的回调函数。路由对象将始终作为传递给您的回调函数的最后一个参数。

```php
Flight::route('/', function(\flight\net\Route $route) {
  // 匹配的HTTP方法数组
  $route->methods;

  // 命名参数数组
  $route->params;

  // 匹配的正则表达式
  $route->regex;

  // 包含URL模式中使用的任何`*`的内容
  $route->splat;

  // 显示URL路径....如果您真的需要的话
  $route->pattern;

  // 显示分配给此中间件的内容
  $route->middleware;

  // 显示分配给此路由的别名
  $route->alias;
}, true);
```

## 路由分组

有时候您可能希望将相关的路由组合在一起（例如`/api/v1`）。您可以使用`group`方法来实现这一点：

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

您甚至可以嵌套组：

```php
Flight::group('/api', function () {
  Flight::group('/v1', function () {
	// Flight::get()获取变量，不设置路由！请查看下面的对象上下文
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

	// Flight::get()获取变量，不设置路由！请查看下面的对象上下文
	Flight::route('GET /users', function () {
	  // 匹配GET /api/v2/users
	});
  });
});
```

### 与对象上下文一起分组

您仍然可以使用`Engine`对象与`Router`一起使用路由分组：

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