# 响应

Flight 帮助生成部分响应头，但您对发送给用户的内容拥有大部分控制。有时您可以直接访问 `Response` 对象，但大多数时候您会使用 `Flight` 实例来发送响应。

## 发送基本响应

Flight 使用 ob_start() 来缓冲输出。这意味着您可以使用 `echo` 或 `print` 发送响应给用户，Flight 会捕获它并与适当的头一起发送回用户。

```php
// 这将向用户的浏览器发送“Hello, World!”
Flight::route('/', function() {
	echo "Hello, World!";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// Hello, World!
```

作为替代，您也可以调用 `write()` 方法来添加到响应体。

```php
// 这将向用户的浏览器发送“Hello, World!”
Flight::route('/', function() {
	// 详细，但有时在需要时会很有用
	Flight::response()->write("Hello, World!");

	// 如果您想在这一点检索您设置的体
	// 您可以这样去做
	$body = Flight::response()->getBody();
});
```

## 状态码

您可以通过使用 `status` 方法来设置响应的状态码：

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

如果您想获取当前状态码，您可以使用不带任何参数的 `status` 方法：

```php
Flight::response()->status(); // 200
```

## 设置响应体

您可以通过使用 `write` 方法来设置响应体，但是如果您 echo 或 print 任何内容，它将通过输出缓冲被捕获并作为响应体发送。

```php
Flight::route('/', function() {
	Flight::response()->write("Hello, World!");
});

// 与以下相同

Flight::route('/', function() {
	echo "Hello, World!";
});
```

### 清除响应体

如果您想清除响应体，您可以使用 `clearBody` 方法：

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Hello, World!");
	} else {
		Flight::response()->clearBody();
	}
});
```

### 在响应体上运行回调

您可以通过使用 `addResponseBodyCallback` 方法在响应体上运行回调：

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// 这将为任何路由的响应进行 gzip 压缩
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

您可以添加多个回调，它们将按添加顺序运行。因为这可以接受任何 [callable](https://www.php.net/manual/en/language.types.callable.php)，它可以接受类数组 `[ $class, 'method' ]`、闭包 `$strReplace = function($body) { str_replace('hi', 'there', $body); };` 或函数名称 `'minify'`，例如如果您有一个函数来压缩您的 HTML 代码。

**注意：** 如果您使用 `flight.v2.output_buffering` 配置选项，路由回调将不起作用。

### 特定路由回调

如果您希望这只应用于特定路由，您可以在路由本身中添加回调：

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// 这将只为这个路由的响应进行 gzip 压缩
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

### 中间件选项

您也可以使用中间件通过中间件将回调应用于所有路由：

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
		// 以某种方式压缩体
		return $body;
	}
}

// index.php
Flight::group('/users', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MinifyMiddleware() ]);
```

## 设置响应头

您可以通过使用 `header` 方法来设置响应头，例如内容类型：

```php
// 这将以纯文本形式向用户的浏览器发送“Hello, World!”
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// 或
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

## JSON

Flight 提供发送 JSON 和 JSONP 响应的支持。要发送 JSON 响应，您传递一些数据来 JSON 编码：

```php
Flight::json(['id' => 123]);
```

> **注意：** 默认情况下，Flight 会发送一个 `Content-Type: application/json` 头与响应。它还会使用常量 `JSON_THROW_ON_ERROR` 和 `JSON_UNESCAPED_SLASHES` 来编码 JSON。

### JSON 与状态码

您也可以作为第二个参数传递状态码：

```php
Flight::json(['id' => 123], 201);
```

### JSON 与美化打印

您也可以传递一个参数到最后一个位置来启用美化打印：

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

如果您正在更改传递到 `Flight::json()` 的选项并希望更简单的语法，您可以重新映射 JSON 方法：

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// 现在它可以这样使用
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON 和停止执行 (v3.10.0)

如果您想发送 JSON 响应并停止执行，您可以使用 `jsonHalt()` 方法。这在您检查某种授权并且如果用户未授权时立即发送 JSON 响应、清除现有体内容并停止执行的情况下非常有用。

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// 检查用户是否授权
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
	}

	// 继续路由的其余部分
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

	// 继续路由的其余部分
});
```

### JSONP

对于 JSONP 请求，您可以可选地传递您用于定义回调函数的查询参数名称：

```php
Flight::jsonp(['id' => 123], 'q');
```

所以，当使用 `?q=my_func` 进行 GET 请求时，您应该收到输出：

```javascript
my_func({"id":123});
```

如果您不传递查询参数名称，它将默认使用 `jsonp`。

## 重定向到另一个 URL

您可以通过使用 `redirect()` 方法并传递一个新 URL 来重定向当前请求：

```php
Flight::redirect('/new/location');
```

默认情况下 Flight 发送一个 HTTP 303 (“See Other”) 状态码。您可以可选地设置自定义代码：

```php
Flight::redirect('/new/location', 401);
```

## 停止

您可以通过调用 `halt` 方法在任何点停止框架：

```php
Flight::halt();
```

您也可以指定可选的 `HTTP` 状态码和消息：

```php
Flight::halt(200, 'Be right back...');
```

调用 `halt` 会丢弃到那一刻的任何响应内容。如果您想停止框架并输出当前响应，请使用 `stop` 方法：

```php
Flight::stop($httpStatusCode = null);
```

> **注意：** `Flight::stop()` 有一些奇怪的行为，例如它会输出响应但继续执行您的脚本。您可以在调用 `Flight::stop()` 后使用 `exit` 或 `return` 来防止进一步执行，但一般推荐使用 `Flight::halt()`。

## 清除响应数据

您可以通过使用 `clear()` 方法来清除响应体和头。这将清除分配给响应的任何头、清除响应体，并将状态码设置为 `200`。

```php
Flight::response()->clear();
```

### 只清除响应体

如果您只想清除响应体，您可以使用 `clearBody()` 方法：

```php
// 这将保留响应() 对象上设置的任何头。
Flight::response()->clearBody();
```

## HTTP 缓存

Flight 提供内置支持 HTTP 级别缓存。如果缓存条件满足，Flight 将返回一个 HTTP `304 Not Modified` 响应。下次客户端请求相同资源时，他们将被提示使用本地缓存版本。

### 路由级别缓存

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

您可以使用 `lastModified` 方法并传递一个 UNIX 时间戳来设置页面最后修改的日期和时间。客户端将继续使用他们的缓存，直到最后修改值更改。

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo 'This content will be cached.';
});
```

### ETag

`ETag` 缓存类似于 `Last-Modified`，除了您可以为资源指定任何 ID：

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo 'This content will be cached.';
});
```

请记住，调用 `lastModified` 或 `etag` 都会设置并检查缓存值。如果请求之间的缓存值相同，Flight 将立即发送一个 `HTTP 304` 响应并停止处理。

## 下载文件 (v3.12.0)

有一个辅助方法来下载文件。您可以使用 `download` 方法并传递路径。

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```