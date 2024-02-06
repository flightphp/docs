## 路由

在 Flight 中，路由是通过将 URL 模式与回调函数进行匹配来完成的。

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
class Greeting
{
    public function __construct() {
        $this->name = '张三';
    }

    public function hello() {
        echo "你好，{$this->name}!";
    }
}

$greeting = new Greeting();

Flight::route('/', array($greeting, 'hello'));
```

路由按照定义顺序匹配。首个匹配请求的路由将被调用。

## 方法路由

默认情况下，路由模式与所有请求方法进行匹配。您可以通过在 URL 前放置标识符来响应特定方法。

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
  echo '我收到的是 GET 请求或 POST 请求。';
});
```

## 正则表达式

您可以在路由中使用正则表达式：

```php
Flight::route('/user/[0-9]+', function () {
  // 这将匹配 /user/1234
});
```

## 命名参数

您可以在路由中指定命名参数，这些参数将传递给回调函数。

```php
Flight::route('/@name/@id', function (string $name, string $id) {
  echo "你好，$name ($id)!";
});
```

您还可以使用 `:` 分隔符在命名参数中包含正则表达式：

```php
Flight::route('/@name/@id:[0-9]{3}', function (string $name, string $id) {
  // 这将匹配 /bob/123
  // 但不会匹配 /bob/12345
});
```

不支持在带有命名参数的正则表达式组 `()` 中进行匹配。

## 可选参数

您可以指定可选参数以便进行匹配，通过将段落包含在括号中。

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

未匹配的任何可选参数将作为 NULL 传递。

## 通配符

匹配仅在单独的 URL 段上完成。如果您想匹配多个段，可以使用 `*` 通配符。

```php
Flight::route('/blog/*', function () {
  // 这将匹配 /blog/2000/02/01
});
```

要将所有请求路由到单个回调函数，可以执行：

```php
Flight::route('*', function () {
  // 做一些事情
});
```

## 传递

您可以通过从回调函数中返回 `true` 来将执行传递给下一个匹配的路由。

```php
Flight::route('/user/@name', function (string $name) {
  // 检查某些条件
  if ($name !== "张三") {
    // 继续下一个路由
    return true;
  }
});

Flight::route('/user/*', function () {
  // 这将被调用
});
```

## 路由信息

如果您想检查匹配路由信息，可以通过在路由方法的第三个参数中传入 `true` 请求路由对象传递给您的回调函数。路由对象将始终作为传递给回调函数的最后一个参数。

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
}, true);
```

## 路由分组

有时，您可能希望将相关路由分组在一起（例如 `/api/v1`）。您可以通过使用 `group` 方法来实现此目的：

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

甚至可以嵌套组：

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

### 带有对象上下文的分组

您仍然可以使用引擎对象和路由分组，方式如下：

```php
$app = new \flight\Engine();
$app->group('/api/v1', function (Router $router) {
  $router->get('/users', function () {
	// 匹配 GET /api/v1/users
  });

  $router->post('/posts', function () {
	// 匹配 POST /api/v1/posts
  });
});
```

## 路由别名

您可以为路由分配别名，以便稍后在代码中动态生成 URL（例如，像模板一样）。

```php
Flight::route('/users/@id', function($id) { echo '用户:'.$id; }, false, 'user_view');

// 随后在代码中的某个地方
Flight::getUrl('user_view', [ 'id' => 5 ]); // 将返回 '/users/5'
```

如果您的 URL 恰好发生更改，则这将非常有帮助。在上面的示例中，假设用户已经移动到 `/admin/users/@id`。通过使用别名，您无需更改引用别名的任何位置，因为别名现在将返回 `/admin/users/5`，就像上面的示例一样。

路由别名在组中仍然有效：

```php
Flight::group('/users', function() {
    Flight::route('/@id', function($id) { echo '用户:'.$id; }, false, 'user_view');
});


// 后续代码中的某个地方
Flight::getUrl('user_view', [ 'id' => 5 ]); // 将返回 '/users/5'
```

## 路由中间件

Flight 支持路由和组路由中间件。中间件是在路由回调之前（或之后）执行的函数。这是在代码中添加 API 身份验证检查的绝佳途径，或者验证用户是否有权限访问路由。

这是一个基本示例：

```php
// 如果您只提供匿名函数，它将在路由回调之前执行。
// 除类外，没有“after”中间件函数（请参见下文）
Flight::route('/path', function() { echo ' 在这里！'; })->addMiddleware(function() {
	echo '首先是中间件！';
});

Flight::start();

// 这将输出“首先是中间件！ 在这里！”
```

在使用中间件之前，您应该了解一些关于中间件的非常重要的注意事项：
- 中间件函数按添加到路由的顺序执行。执行方式类似于 [Slim Framework 处理的方式](https://www.slimframework.com/docs/v4/concepts/middleware.html#how-does-middleware-work)。
   - 首先按添加顺序执行，然后后置根据相反的顺序执行。
- 如果您的中间件函数返回 false，则会停止所有执行，并抛出 403 禁止错误。您可能希望使用 `Flight::redirect()` 或类似方法更优雅地处理此情况。
- 如果需要从路由中获取参数，它们将作为单个数组传递给中间件函数（`function($params) { ... }` 或 `public function before($params) {}）。这样做是因为您可以根据名称组织参数，并且这些参数在其中一些组中可能以不同顺序出现，这将通过通过引用错误的参数破坏中间件函数。通过这种方式，您可以按名称而不是位置访问它们。

### 中间件类

中间件也可以注册为类。如果您需要“后置”功能，则必须使用类。

```php
class MyMiddleware {
	public function before($params) {
		echo '首先是中间件！';
	}

	public function after($params) {
		echo '最后是中间件！';
	}
}

$MyMiddleware = new MyMiddleware();
Flight::route('/path', function() { echo ' 在这里！ '; })->addMiddleware($MyMiddleware); // 还可以 ->addMiddleware([ $MyMiddleware, $MyMiddleware2 ]);

Flight::start();

// 这将显示“首先是中间件！ 在这里！ 最后是中间件！”
```

### 中间件分组

您可以添加一个路由组，然后该组中的每个路由也将具有相同的中间件。 如果需要通过头部中的 API 密钥检查 Auth 中间件，这将非常有用。

```php

// 添加到 group 方法的末尾
Flight::group('/api', function() {
    Flight::route('/users', function() { echo '用户'; }, false, 'users');
	Flight::route('/users/@id', function($id) { echo '用户:'.$id; }, false, 'user_view');
}, [ new ApiAuthMiddleware() ]);
```  