# 响应

## 概述

Flight 会为您生成部分响应头，但您对发送回用户的内容拥有大部分控制权。大多数情况下，您将直接访问 `response()` 对象，但 Flight 提供了一些辅助方法来为您设置部分响应头。

## 理解

在用户向您的应用程序发送 [request](/learn/requests) 请求后，您需要为他们生成适当的响应。他们发送给您的信息包括他们偏好的语言、是否支持某些类型的压缩、他们的用户代理等，在处理完一切后，是时候向他们发送适当的响应了。这可以是设置头、输出 HTML 或 JSON 主体，或者将他们重定向到某个页面。

## 基本用法

### 发送响应主体

Flight 使用 `ob_start()` 来缓冲输出。这意味着您可以使用 `echo` 或 `print` 向用户发送响应，Flight 会捕获它并连同适当的头发送回用户。

```php
// 这将向用户的浏览器发送 "Hello, World!"
Flight::route('/', function() {
	echo "Hello, World!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Hello, World!
```

作为替代，您也可以调用 `write()` 方法来添加主体内容。

```php
// 这将向用户的浏览器发送 "Hello, World!"
Flight::route('/', function() {
	// 冗长，但有时在需要时能完成任务
	Flight::response()->write("Hello, World!");

	// 如果您想在此时检索已设置的主体
	// 您可以这样做
	$body = Flight::response()->getBody();
});
```

### JSON

Flight 提供支持发送 JSON 和 JSONP 响应。要发送 JSON 响应，您需要传递一些要进行 JSON 编码的数据：

```php
Flight::route('/@companyId/users', function(int $companyId) {
	// 例如从数据库中拉取您的用户
	$users = Flight::db()->fetchAll("SELECT id, first_name, last_name FROM users WHERE company_id = ?", [ $companyId ]);

	Flight::json($users);
});
// [{"id":1,"first_name":"Bob","last_name":"Jones"}, /* 更多用户 */ ]
```

> **注意：** 默认情况下，Flight 会发送 `Content-Type: application/json` 头与响应一起。它还会使用标志 `JSON_THROW_ON_ERROR` 和 `JSON_UNESCAPED_SLASHES` 来编码 JSON。

#### 带状态码的 JSON

您也可以将状态码作为第二个参数传递：

```php
Flight::json(['id' => 123], 201);
```

#### 美化打印的 JSON

您也可以在最后一个位置传递一个参数来启用美化打印：

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

#### 更改 JSON 参数顺序

`Flight::json()` 是一个非常旧的方法，但 Flight 的目标是维护项目的向后兼容性。
如果您想重新排序参数以使用更简单的语法，您可以像[任何其他 Flight 方法](/learn/extending)一样重新映射 JSON 方法：

```php
Flight::map('json', function($data, $code = 200, $options = 0) {

	// 现在在使用 json() 方法时，您不必 `true, 'utf-8'` 了！
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// 现在它可以这样使用
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

#### JSON 和停止执行

_v3.10.0_

如果您想发送 JSON 响应并停止执行，您可以使用 `jsonHalt()` 方法。
这对于检查某种授权类型的情况很有用，如果用户未授权，您可以立即发送 JSON 响应、清空现有主体内容并停止执行。

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// 检查用户是否授权
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
		// 这里不需要 exit;
	}

	// 继续执行路由的其余部分
});
```

在 v3.10.0 之前，您必须这样做：

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// 检查用户是否授权
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Unauthorized']));
	}

	// 继续执行路由的其余部分
});
```

### 清空响应主体

如果您想清空响应主体，您可以使用 `clearBody` 方法：

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Hello, World!");
	} else {
		Flight::response()->clearBody();
	}
});
```

上面的用例可能不常见，但如果在 [middleware](/learn/middleware) 中使用，它可能会更常见。

### 在响应主体上运行回调

您可以使用 `addResponseBodyCallback` 方法在响应主体上运行回调：

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// 这将对任何路由的所有响应进行 gzip 压缩
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

您可以添加多个回调，它们将按添加顺序运行。因为这可以接受任何 [callable](https://www.php.net/manual/en/language.types.callable.php)，它可以接受类数组 `[ $class, 'method' ]`、闭包 `$strReplace = function($body) { str_replace('hi', 'there', $body); };`，或函数名 `'minify'`，例如如果您有一个函数来压缩您的 HTML 代码。

**注意：** 如果您使用 `flight.v2.output_buffering` 配置选项，路由回调将不起作用。

#### 特定路由回调

如果您希望这仅适用于特定路由，您可以在路由本身中添加回调：

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// 这将仅对该路由的响应进行 gzip 压缩
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

#### 中间件选项

您也可以使用 [middleware](/learn/middleware) 通过中间件将回调应用于所有路由：

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		// 在 response() 对象上应用回调。
		Flight::response()->addResponseBodyCallback(function($body) {
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// 以某种方式压缩主体
		return $body;
	}
}

// index.php
Flight::group('/users', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MinifyMiddleware() ]);
```

### 状态码

您可以使用 `status` 方法设置响应的状态码：

```php
Flight::route('/@id', function($id) {
	if($id == 123) {
		Flight::response()->status(200);
		echo "Hello, World!";
	} else {
		Flight::response()->status(403);
		echo "Forbidden";
	}
});
```

如果您想获取当前状态码，您可以不带任何参数使用 `status` 方法：

```php
Flight::response()->status(); // 200
```

### 设置响应头

您可以使用 `header` 方法设置响应内容的类型等头：

```php
// 这将以纯文本形式向用户的浏览器发送 "Hello, World!"
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// 或
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

### 重定向

您可以使用 `redirect()` 方法并传递一个新 URL 来重定向当前请求：

```php
Flight::route('/login', function() {
	$username = Flight::request()->data->username;
	$password = Flight::request()->data->password;
	$passwordConfirm = Flight::request()->data->password_confirm;

	if($password !== $passwordConfirm) {
		Flight::redirect('/new/location');
		return; // 这是必要的，以防止下面的功能执行
	}

	// 添加新用户...
	Flight::db()->runQuery("INSERT INTO users ....");
	Flight::redirect('/admin/dashboard');
});
```

> **注意：** 默认情况下，Flight 发送 HTTP 303 (“See Other”) 状态码。您可以选择设置自定义代码：

```php
Flight::redirect('/new/location', 301); // 永久
```

### 停止路由执行

您可以通过调用 `halt` 方法在任何点停止框架并立即退出：

```php
Flight::halt();
```

您也可以指定可选的 `HTTP` 状态码和消息：

```php
Flight::halt(200, 'Be right back...');
```

调用 `halt` 将丢弃直到该点的任何响应内容并停止所有执行。
如果您想停止框架并输出当前响应，请使用 `stop` 方法：

```php
Flight::stop($httpStatusCode = null);
```

> **注意：** `Flight::stop()` 有一些奇怪的行为，例如它会输出响应但继续执行您的脚本，这可能不是您想要的。您可以在调用 `Flight::stop()` 后使用 `exit` 或 `return` 来防止进一步执行，但一般推荐使用 `Flight::halt()`。

这将保存头键和值到响应对象。在请求生命周期结束时，
它将构建头并发送响应。

## 高级用法

### 立即发送头

有时您需要对头进行自定义操作，并在您正在处理的代码行上发送头。
如果您正在设置 [streamed route](/learn/routing)，这就是您需要的。通过 `response()->setRealHeader()` 可以实现。

```php
Flight::route('/', function() {
	Flight::response()->setRealHeader('Content-Type: text/plain');
	echo 'Streaming response...';
	sleep(5);
	echo 'Done!';
})->stream();
```

### JSONP

对于 JSONP 请求，您可以选择传递用于定义回调函数的查询参数名称：

```php
Flight::jsonp(['id' => 123], 'q');
```

因此，当使用 `?q=my_func` 进行 GET 请求时，您应该收到输出：

```javascript
my_func({"id":123});
```

如果您不传递查询参数名称，它将默认使用 `jsonp`。

> **注意：** 如果您在 2025 年及以后仍在使用 JSONP 请求，请加入聊天告诉我们原因！我们喜欢听一些好的战斗/恐怖故事！

### 清空响应数据

您可以使用 `clear()` 方法清空响应主体和头。这将清除分配给响应的任何头、清空响应主体，并将状态码设置为 `200`。

```php
Flight::response()->clear();
```

#### 仅清空响应主体

如果您只想清空响应主体，您可以使用 `clearBody()` 方法：

```php
// 这将保留在 response() 对象上设置的任何头。
Flight::response()->clearBody();
```

### HTTP 缓存

Flight 提供内置支持 HTTP 级别的缓存。如果满足缓存条件，
Flight 将返回 HTTP `304 Not Modified` 响应。下次客户端请求相同资源时，他们将被提示使用本地缓存版本。

#### 路由级别缓存

如果您想缓存整个响应，您可以使用 `cache()` 方法并传递缓存时间。

```php

// 这将缓存响应 5 分钟
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo 'This content will be cached.';
});

// 或者，您可以使用传递给 strtotime() 方法的字符串
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo 'This content will be cached.';
});
```

### Last-Modified

您可以使用 `lastModified` 方法并传递 UNIX 时间戳来设置页面最后修改的日期
和时间。客户端将继续使用他们的缓存，直到
最后修改值更改。

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'This content will be cached.';
});
```

### ETag

`ETag` 缓存类似于 `Last-Modified`，除了您可以为资源指定任何您想要的 ID：

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'This content will be cached.';
});
```

请记住，调用 `lastModified` 或 `etag` 都会设置并检查
缓存值。如果请求之间的缓存值相同，Flight 将立即
发送 `HTTP 304` 响应并停止处理。

### 下载文件

_v3.12.0_

有一个辅助方法可以将文件流式传输到最终用户。您可以使用 `download` 方法并传递路径。

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```

## 另请参阅
- [Routing](/learn/routing) - 如何将路由映射到控制器并渲染视图。
- [Requests](/learn/requests) - 理解如何处理传入请求。
- [Middleware](/learn/middleware) - 使用中间件与路由进行身份验证、日志记录等。
- [Why a Framework?](/learn/why-frameworks) - 理解使用像 Flight 这样的框架的好处。
- [Extending](/learn/extending) - 如何使用您自己的功能扩展 Flight。

## 故障排除
- 如果重定向不起作用，请确保在方法中添加 `return;`。
- `stop()` 和 `halt()` 不是一回事。`halt()` 将立即停止执行，而 `stop()` 将允许执行继续。

## 更新日志
- v3.12.0 - 添加了 downloadFile 辅助方法。
- v3.10.0 - 添加了 `jsonHalt`。
- v1.0 - 初始发布。