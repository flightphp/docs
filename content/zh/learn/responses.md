# 响应

Flight 帮助为您生成部分响应标头，但您可以控制向用户发送什么内容。有时，您可以直接访问`Response`对象，但大多数情况下，您将使用`Flight`实例发送响应。

## 发送基本响应

Flight 使用 `ob_start()` 缓冲输出。这意味着您可以使用 `echo` 或 `print` 向用户发送响应，Flight 将捕获它并使用适当的标头发送回用户。

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

作为替代，您还可以调用 `write()` 方法来添加到主体。

```php

// 这将向用户的浏览器发送 "Hello, World!"
Flight::route('/', function() {
	// 冗长，但在需要时有时会起作用
	Flight::response()->write("Hello, World!");

	// 如果您想要检索此时设置的主体
	// 您可以这样做
	$body = Flight::response()->getBody();
});
```

## 状态码

您可以使用`status`方法设置响应的状态码：

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

如果要获取当前状态码，可以使用不带参数的`status`方法：

```php
Flight::response()->status(); // 200
```

## 设置响应主体

您可以使用 `write` 方法设置响应主体，但是，如果您使用 `echo` 或 `print` 任何内容，它将被捕获并通过输出缓冲发送为响应主体。

```php
Flight::route('/', function() {
	Flight::response()->write("Hello, World!");
});

// 同样的

Flight::route('/', function() {
	echo "Hello, World!";
});
```

### 清除响应主体

如果要清除响应主体，可以使用 `clearBody` 方法：

```php
Flight::route('/', function() {
	if($someCondition) {
		Flight::response()->write("Hello, World!");
	} else {
		Flight::response()->clearBody();
	}
});
```

### 在响应主体上运行回调

您可以使用 `addResponseBodyCallback` 方法在响应主体上运行回调：

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);
});

// 这将为任何路由的所有响应执行gzip
Flight::response()->addResponseBodyCallback(function($body) {
	return gzencode($body, 9);
});
```

您可以添加多个回调，它们将按添加顺序运行。由于这可以接受任何 [callable](https://www.php.net/manual/en/language.types.callable.php)，它可以接受类数组 `[ $class, 'method' ]`，闭包 `$strReplace = function($body) { str_replace('hi', 'there', $body); };`，或者函数名称 `'minify'`，如果例如您有一个用于缩小您的html代码的函数。

**注意:** 如果使用`flight.v2.output_buffering`配置选项，则路由回调将无法正常工作。

### 特定路由回调

如果希望仅应用于特定路由，可以在路由本身中添加回调：

```php
Flight::route('/users', function() {
	$db = Flight::db();
	$users = $db->fetchAll("SELECT * FROM users");
	Flight::render('users_table', ['users' => $users]);

	// 这将仅对此路由的响应进行gzip
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
		// 在此处在 response() 对象上应用回调。
		Flight::response()->addResponseBodyCallback(function($body) {
			return $this->minify($body);
		});
	}

	protected function minify(string $body): string {
		// 以某种方式缩小主体
		return $body;
	}
}

// index.php
Flight::group('/users', function() {
	Flight::route('', function() { /* ... */ });
	Flight::route('/@id', function($id) { /* ... */ });
}, [ new MinifyMiddleware() ]);
```

## 设置响应标头

您可以使用`header`方法设置响应的内容类型等标头:

```php

// 这将以纯文本形式向用户的浏览器发送 "Hello, World!"
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// 或
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

## JSON

Flight 提供支持发送 JSON 和 JSONP 响应。要发送 JSON 响应，您需要传递要进行 JSON 编码的一些数据：

```php
Flight::json(['id' => 123]);
```

### 具有状态码的 JSON

您还可以将状态码作为第二个参数传递：

```php
Flight::json(['id' => 123], 201);
```

### 具有漂亮打印的 JSON

您还可以在最后一个位置传入参数以启用漂亮打印：

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

如果正在更改传递给`Flight::json()`的选项并希望使用更简单的语法，可以重新映射JSON方法：

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
});

// 现在可以这样使用
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON 和停止执行（v3.10.0）

如果要发送 JSON 响应并停止执行，可以使用 `jsonHalt` 方法。这在您检查某种授权类型，如果用户未经授权，您可以立即发送 JSON 响应，清除已有主体内容并停止执行时非常有用。

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// 检查用户是否已获授权
	if($authorized === false) {
		Flight::jsonHalt(['error' => '未经授权'], 401);
	}

	// 继续处理其余路由
});
```

在 v3.10.0 之前，您可能需要执行以下操作：

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// 检查用户是否已获授权
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => '未经授权']));
	}

	// 继续处理其余路由
});
```

### JSONP

对于 JSONP 请求，您可以选择传入用于定义回调函数的查询参数名称：

```php
Flight::jsonp(['id' => 123], 'q');
```

因此，使用 `?q=my_func` 发出 GET 请求时，您应该接收以下输出：

```javascript
my_func({"id":123});
```

如果不传入查询参数名称，它将默认为 `jsonp`。

## 重定向至另一个 URL

您可以使用 `redirect()` 方法重定向当前请求，并传递一个新的 URL：

```php
Flight::redirect('/new/location');
```

Flight 默认发送 HTTP 303（"查看其他"）状态码。您还可以选择设置自定义代码：

```php
Flight::redirect('/new/location', 401);
```

## 停止

您可以在任何时候通过调用 `halt` 方法停止框架：

```php
Flight::halt();
```

您还可以指定可选的 `HTTP` 状态码和消息：

```php
Flight::halt(200, '马上回来...');
```

调用 `halt` 将放弃到该点为止的任何响应内容。如果要停止框架并输出当前响应，使用 `stop` 方法：

```php
Flight::stop();
```

## 清除响应数据

您可以使用 `clear()` 方法清除响应主体和标头。这将清除分配给响应的任何标头，清除响应主体，并将状态码设置为 `200`。

```php
Flight::response()->clear();
```

### 仅清除响应主体

如果只想清除响应主体，可以使用 `clearBody()` 方法：

```php
// 这仍将保留在 response() 对象上设置的任何标头。
Flight::response()->clearBody();
```

## HTTP 缓存

Flight 提供内置支持的 HTTP 级缓存。如果满足缓存条件，Flight 将返回 HTTP `304 未修改` 响应。下次客户端请求同一资源时，它们将提示使用其本地缓存版本。

### 路由级别缓存

如果要缓存整个响应，可以使用 `cache()` 方法并传递缓存时间。

```php

// 这将为 5 分钟缓存响应
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo '此内容将被缓存。';
});

// 或者，您可以使用您将传递给 strtotime() 方法的字符串
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo '此内容将被缓存。';
});
```

### 上次修改

您可以使用 `lastModified` 方法并传递 UNIX 时间戳来设置页面上次修改的日期和时间。直到最后修改值更改为止，客户端将继续使用其缓存。

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo '此内容将被缓存。';
});
```

### ETag

`ETag` 缓存类似于 `Last-Modified`，只是您可以为资源指定任何 id：

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo '此内容将被缓存。';
});
```

请记住，调用 `lastModified` 或 `etag` 将同时设置和检查缓存值。如果在请求之间缓存值相同，则 Flight 将立即发送一个 `HTTP 304` 响应并停止处理。

### 下载文件

有一个用于下载文件的辅助方法。您可以使用`download`方法并传递路径。

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```