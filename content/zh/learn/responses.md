# 响应

Flight 帮助为您生成部分响应头，但您大部分控制权都是在您手中，您可以控制向用户发送什么数据。有时候您可以直接访问`Response`对象，但大部分时候您将使用`Flight`实例来发送响应。

## 发送基本响应

Flight 使用ob_start()来对输出进行缓冲。这意味着您可以使用 `echo` 或 `print` 来向用户发送响应，Flight 将捕获它并使用适当的响应头将其发送回给用户。

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

作为替代方案，您也可以调用 `write()` 方法来添加正文。

```php

// 这将向用户的浏览器发送 "Hello, World!"
Flight::route('/', function() {
	// 冗长，但有时候当您需要时会派上用场
	Flight::response()->write("Hello, World!");

	// 如果您想在这一点上检索您设置的正文
	// 您可以这样做
	$body = Flight::response()->getBody();
});
```

## 状态码

您可以使用 `status` 方法来设置响应的状态码：

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

如果您想获取当前状态码，您可以不带任何参数地使用 `status` 方法：

```php
Flight::response()->status(); // 200
```

## 在响应正文上运行回调

您可以使用 `addResponseBodyCallback` 方法在响应正文上运行回调：

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// 这将为任何路由的所有响应启用gzip压缩
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

您可以添加多个回调，它们将按添加顺序运行。因为这个方法可以接受任何[可调用](https://www.php.net/manual/en/language.types.callable.php)，所以它可以接受类数组 `[ $class, 'method' ]`，闭包 `$strReplace = function($body) { str_replace('hi', 'there', $body); };`，或者函数名 `'minify'`，比如如果您有一个用于缩小HTML代码的函数。

**注意：** 如果您使用 `flight.v2.output_buffering` 配置选项，则路由回调将不起作用。

### 特定路由回调

如果您希望这仅适用于特定路由，您可以在路由中添加回调：

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// 这将仅压缩此路由的响应
	Flight::response()->addResponseBodyCallback(function($body) {
		return gzencode($body, 9);
	});
});
```

### 中间件选项

您还可以使用中间件通过中间件将回调应用于所有路由：

```php
// MinifyMiddleware.php
class MinifyMiddleware {
	public function before() {
		Flight::response()->addResponseBodyCallback(function($body) {
			// 这是一个示例
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// 缩小正文
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

您可以使用 `header` 方法设置响应的头部，例如内容类型：

```php

// 这将用纯文本发送 "Hello, World!" 给用户的浏览器
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

## JSON

Flight 提供了发送 JSON 和 JSONP 响应的支持。要发送 JSON 响应，您需要将要进行 JSON 编码的数据传递：

```php
Flight::json(['id' => 123]);
```

### 带状态码的 JSON

您还可以将状态码作为第二个参数传递：

```php
Flight::json(['id' => 123], 201);
```

### 美化打印的 JSON

您还可以在最后一个位置传入一个参数以启用美化打印：

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

如果您要更改传递给 `Flight::json()` 的选项并希望有一个更简单的语法，您可以重新映射 JSON 方法：

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
}

// 现在可以这样使用
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON 和停止执行

如果您想发送一个 JSON 响应并停止执行，您可以使用 `jsonHalt` 方法。这在您正在检查某种类型的授权并且用户未经授权时，可以立即发送 JSON 响应，清除现有正文内容并停止执行。

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// 检查用户是否经过授权
	if($authorized === false) {
		Flight::jsonHalt(['error' => '未经授权'], 401);
	}

	// 继续执行路由的其余部分
});
```

### JSONP

对于 JSONP 请求，您还可以选择传入定义回调函数的查询参数名称：

```php
Flight::jsonp(['id' => 123], 'q');
```

因此，当使用 `?q=my_func` 进行 GET 请求时，您应该会收到以下输出：

```javascript
my_func({"id":123});
```

如果您不传递查询参数名称，它将默认为 `jsonp`。

## 重定向到另一个 URL

您可以使用 `redirect()` 方法并传递一个新的 URL 来重定向当前请求：

```php
Flight::redirect('/new/location');
```

默认情况下，Flight 发送 HTTP 303 ("See Other") 状态码。您可以选择设置自定义代码：

```php
Flight::redirect('/new/location', 401);
```

## 停止

您可以随时通过调用 `halt` 方法来停止框架：

```php
Flight::halt();
```

您还可以指定可选的 `HTTP` 状态码和消息：

```php
Flight::halt(200, '马上回来...');
```

调用 `halt` 将丢弃到该点为止的所有响应内容。如果要停止框架并输出当前响应，请使用 `stop` 方法：

```php
Flight::stop();
```

## HTTP 缓存

Flight 提供了内置支持的 HTTP 级缓存。如果满足缓存条件，Flight 将返回 HTTP `304 Not Modified` 响应。下次客户端请求相同资源时，将提示他们使用本地缓存版本。

### 路由级缓存

如果您想缓存整个响应，可以使用 `cache()` 方法并传入缓存时间。

```php

// 这将缓存响应 5 分钟
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo '这个内容将被缓存。';
});

// 或者，您可以使用传递给 strtotime() 方法的字符串
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo '这个内容将被缓存。';
});
```

### 最后修改

您可以使用 `lastModified` 方法并传入 UNIX 时间戳来设置页面上次修改的日期和时间。客户端将继续使用他们的缓存，直到最后修改值发生变化。

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo '这个内容将被缓存。';
});
```

### ETag

`ETag` 缓存类似于 `Last-Modified`，不同之处在于您可以为资源指定任何您想要的 ID：

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo '这个内容将被缓存。';
});
```

请注意，调用`lastModified`或`etag`都将设置并检查缓存值。如果请求之间的缓存值相同时，Flight 将立即发送HTTP 304响应并停止处理。