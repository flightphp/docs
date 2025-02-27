# 响应

Flight 帮助生成部分响应头，但你对返回给用户的内容拥有大部分控制权。有时你可以直接访问 `Response` 对象，但大多数时候你会使用 `Flight` 实例来发送响应。

## 发送基本响应

Flight 使用 ob_start() 来缓冲输出。这意味着你可以使用 `echo` 或 `print` 将响应发送给用户，Flight 会捕获它并将其连同适当的头部发送回用户。

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

作为替代，你可以调用 `write()` 方法来添加到响应体中。

```php

// 这将向用户的浏览器发送 "Hello, World!"
Flight::route('/', function() {
	// 虽然冗长，但在某些情况下确实能派上用场
	Flight::response()->write("Hello, World!");

	// 如果你想检索在此时设置的响应体
	// 你可以像这样做
	$body = Flight::response()->getBody();
});
```

## 状态码

你可以通过使用 `status` 方法来设置响应的状态码：

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

如果你想获取当前状态码，可以使用不带任何参数的 `status` 方法：

```php
Flight::response()->status(); // 200
```

## 设置响应体

你可以通过使用 `write` 方法来设置响应体，但是，如果你使用 echo 或 print 发送任何内容，
它将被捕获并通过输出缓冲作为响应体发送。

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

如果你想清除响应体，可以使用 `clearBody` 方法：

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

你可以使用 `addResponseBodyCallback` 方法在响应体上运行回调：

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// 这将对所有路由的响应进行 gzip 压缩
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

你可以添加多个回调，它们将按照添加的顺序运行。由于这可以接受任何 [可调用的](https://www.php.net/manual/en/language.types.callable.php)，它可以接受类数组 `[ $class, 'method' ]`、闭包 `$strReplace = function($body) { str_replace('hi', 'there', $body); };`，或一个函数名 `'minify'`，例如，如果你有一个函数可以压缩你的 HTML 代码。

**注意：** 如果你使用 `flight.v2.output_buffering` 配置选项，则路由回调将无法工作。

### 特定路由回调

如果你想让这个回调仅适用于特定路由，可以在路由本身中添加回调：

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

### 中间件选项

你还可以使用中间件通过中间件将回调应用于所有路由：

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

## 设置响应头

你可以通过使用 `header` 方法设置响应的内容类型等头：

```php

// 这将以纯文本格式向用户的浏览器发送 "Hello, World!"
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// 或者
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

## JSON

Flight 提供对发送 JSON 和 JSONP 响应的支持。要发送 JSON 响应，您需要将一些数据传递给 JSON 编码：

```php
Flight::json(['id' => 123]);
```

> **注意：** 默认情况下，Flight 将发送 `Content-Type: application/json` 头部作为响应。它还将在编码 JSON 时使用常量 `JSON_THROW_ON_ERROR` 和 `JSON_UNESCAPED_SLASHES`。

### 带状态码的 JSON

你还可以将状态码作为第二个参数传入：

```php
Flight::json(['id' => 123], 201);
```

### 带美化打印的 JSON

你还可以将参数传入最后一个位置以启用美化打印：

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

如果你想更简单的语法可以改变传递给 `Flight::json()` 的选项，可以直接重新映射 JSON 方法：

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// 现在可以这样使用
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON 和停止执行（v3.10.0）

如果你想发送 JSON 响应并停止执行，可以使用 `jsonHalt` 方法。
这在你检查某种类型的授权时很有用，如果用户没有获得授权，你可以立即发送 JSON 响应，清除现有的主体内容并停止执行。

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// 检查用户是否获得授权
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
	}

	// 继续处理其余的路由
});
```

在 v3.10.0 之前，你必须做类似这样的事情：

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// 检查用户是否获得授权
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Unauthorized']));
	}

	// 继续处理其余的路由
});
```

### JSONP

对于 JSONP 请求，你可以选择性地传递用于定义回调函数的查询参数名：

```php
Flight::jsonp(['id' => 123], 'q');
```

因此，在使用 `?q=my_func` 进行 GET 请求时，你应该收到以下输出：

```javascript
my_func({"id":123});
```

如果你没有传递查询参数名称，它将默认为 `jsonp`。

## 重定向到另一个 URL

你可以通过使用 `redirect()` 方法并传入一个新 URL 来重定向当前请求：

```php
Flight::redirect('/new/location');
```

默认情况下，Flight 发送 HTTP 303（“见其他”）状态码。你可以选择设置自定义代码：

```php
Flight::redirect('/new/location', 401);
```

## 停止

你可以随时通过调用 `halt` 方法停止框架：

```php
Flight::halt();
```

你还可以指定可选的 `HTTP` 状态码和消息：

```php
Flight::halt(200, '稍等...');
```

调用 `halt` 将丢弃到目前为止的任何响应内容。如果你想停止框架并输出当前响应，请使用 `stop` 方法：

```php
Flight::stop();
```

## 清除响应数据

你可以使用 `clear()` 方法清除响应体和头。这将清除分配给响应的任何头，清除响应体，并将状态码设置为 `200`。

```php
Flight::response()->clear();
```

### 仅清除响应体

如果你只想清除响应体，可以使用 `clearBody()` 方法：

```php
// 这将仍然保留设置在 response() 对象上的任何头。
Flight::response()->clearBody();
```

## HTTP 缓存

Flight 提供对 HTTP 层级缓存的内置支持。如果满足缓存条件，Flight 将返回 HTTP `304 Not Modified` 响应。下一次客户端请求同一资源时，他们将被提示使用本地缓存的版本。

### 路由级缓存

如果你想缓存整个响应，可以使用 `cache()` 方法并传入缓存时间。

```php

// 这将缓存响应 5 分钟
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo '此内容将被缓存。';
});

// 或者，你可以使用字符串传递给 strtotime() 方法
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo '此内容将被缓存。';
});
```

### 最后修改时间

你可以使用 `lastModified` 方法并传入 UNIX 时间戳来设置页面最后修改的日期和时间。客户端将继续使用他们的缓存，直到最后修改值发生变化。

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo '此内容将被缓存。';
});
```

### ETag

`ETag` 缓存类似于 `Last-Modified`，只不过你可以指定任何你想要的资源 ID：

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo '此内容将被缓存。';
});
```

请记住，调用 `lastModified` 或 `etag` 都将设置并检查缓存值。如果请求之间的缓存值相同，Flight 将立即发送 `HTTP 304` 响应并停止处理。

## 下载文件（v3.12.0）

有一个辅助方法用于下载文件。你可以使用 `download` 方法并传入路径。

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```