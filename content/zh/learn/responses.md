# 响应

Flight 帮助您生成部分响应头，但您对发送给用户的内容拥有大部分控制权。有时您可以直接访问 `Response` 对象，但大多数时候您将使用 `Flight` 实例来发送响应。

## 发送基本响应

Flight 使用 ob_start() 来缓冲输出。这意味着您可以使用 `echo` 或 `print` 将响应发送给用户，Flight 会捕获它并将其与适当的头部一起返回给用户。

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

作为替代，您可以调用 `write()` 方法也可以向主体添加内容。

```php

// 这将向用户的浏览器发送 "Hello, World!"
Flight::route('/', function() {
	// 冗长，但在需要时能完成工作
	Flight::response()->write("Hello, World!");

	// 如果您想检索此时所设置的主体
	// 您可以这样做
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

如果您想获取当前状态码，可以使用不带任何参数的 `status` 方法：

```php
Flight::response()->status(); // 200
```

## 设置响应主体

您可以通过使用 `write` 方法来设置响应主体，但是，如果您使用 echo 或 print 输出任何内容，
它将被捕获并通过输出缓冲作为响应主体发送。

```php
Flight::route('/', function() {
	Flight::response()->write("Hello, World!");
});

// 与下面的相同

Flight::route('/', function() {
	echo "Hello, World!";
});
```

### 清除响应主体

如果您想清除响应主体，可以使用 `clearBody` 方法：

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

您可以通过使用 `addResponseBodyCallback` 方法在响应主体上运行回调：

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

您可以添加多个回调，它们将按添加的顺序运行。因为这可以接受任何 [可调用的](https://www.php.net/manual/en/language.types.callable.php)，它可以接受类数组 `[ $class, 'method' ]`、闭包 `$strReplace = function($body) { str_replace('hi', 'there', $body); };` 或函数名 `'minify'`，例如如果您有一个函数来压缩 HTML 代码。

**注意：** 如果您使用 `flight.v2.output_buffering` 配置选项，则路由回调将不起作用。

### 特定路由回调

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

### 中间件选项

您还可以使用中间件将回调应用于所有路由：

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

您可以通过使用 `header` 方法设置响应的头部，例如内容类型：

```php

// 这将以纯文本形式向用户的浏览器发送 "Hello, World!"
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	// 或者
	Flight::response()->setHeader('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```

## JSON

Flight 提供发送 JSON 和 JSONP 响应的支持。要发送 JSON 响应，您
传递一些数据以进行 JSON 编码：

```php
Flight::json(['id' => 123]);
```

> **注意：** 默认情况下，Flight 将在响应中发送 `Content-Type: application/json` 头。它还将在编码 JSON 时使用常量 `JSON_THROW_ON_ERROR` 和 `JSON_UNESCAPED_SLASHES`。

### 带有状态码的 JSON

您也可以作为第二个参数传入一个状态码：

```php
Flight::json(['id' => 123], 201);
```

### 带有漂亮打印的 JSON

您还可以在最后一个位置传入一个参数以启用漂亮打印：

```php
Flight::json(['id' => 123], 200, true, 'utf-8', JSON_PRETTY_PRINT);
```

如果您正在更改传入 `Flight::json()` 的选项，并且希望更简单的语法，您可以
只需重新映射 JSON 方法：

```php
Flight::map('json', function($data, $code = 200, $options = 0) {
	Flight::_json($data, $code, true, 'utf-8', $options);
});

// 现在可以像这样使用
Flight::json(['id' => 123], 200, JSON_PRETTY_PRINT);
```

### JSON 和停止执行 (v3.10.0)

如果您想发送 JSON 响应并停止执行，可以使用 `jsonHalt` 方法。
这在您检查某种类型的授权时非常有用，如果用户未被授权，您可以立即发送 JSON 响应，清除现有的主体内容并停止执行。

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// 检查用户是否被授权
	if($authorized === false) {
		Flight::jsonHalt(['error' => 'Unauthorized'], 401);
	}

	// 继续处理路由的其余部分
});
```

在 v3.10.0 之前，您必须做类似这样的事情：

```php
Flight::route('/users', function() {
	$authorized = someAuthorizationCheck();
	// 检查用户是否被授权
	if($authorized === false) {
		Flight::halt(401, json_encode(['error' => 'Unauthorized']));
	}

	// 继续处理路由的其余部分
});
```

### JSONP

对于 JSONP 请求，您可以选择性地传入用于定义回调函数的查询参数名称：

```php
Flight::jsonp(['id' => 123], 'q');
```

因此，当使用 `?q=my_func` 发起 GET 请求时，您应该收到输出：

```javascript
my_func({"id":123});
```

如果您不传入查询参数名称，它将默认为 `jsonp`。

## 重定向到另一个 URL

您可以通过使用 `redirect()` 方法并传入新的 URL 来重定向当前请求：

```php
Flight::redirect('/new/location');
```

默认情况下，Flight 发送 HTTP 303 ("See Other") 状态码。您可以选择设置自定义代码：

```php
Flight::redirect('/new/location', 401);
```

## 停止

您可以通过调用 `halt` 方法在任何时候停止框架：

```php
Flight::halt();
```

您还可以指定一个可选的 `HTTP` 状态码和消息：

```php
Flight::halt(200, 'Be right back...');
```

调用 `halt` 将丢弃到目前为止的任何响应内容。如果您想停止框架并输出当前响应，请使用 `stop` 方法：

```php
Flight::stop();
```

## 清除响应数据

您可以通过使用 `clear()` 方法清除响应主体和头部。这将清除
分配给响应的任何头部，清除响应主体，并将状态码设置为 `200`。

```php
Flight::response()->clear();
```

### 仅清除响应主体

如果您只想清除响应主体，可以使用 `clearBody()` 方法：

```php
// 这将仍然保留在 response() 对象上设置的任何头部。
Flight::response()->clearBody();
```

## HTTP 缓存

Flight 提供对 HTTP 级缓存的内置支持。如果满足缓存条件，
Flight 将返回 HTTP `304 Not Modified` 响应。下次客户端请求同一资源时，将提示他们使用本地
缓存的版本。

### 路由级缓存

如果您想缓存整个响应，可以使用 `cache()` 方法并传入缓存时间。

```php

// 这将缓存响应 5 分钟
Flight::route('/news', function () {
  Flight::response()->cache(time() + 300);
  echo '此内容将被缓存。';
});

// Alternatively, you can use a string that you would pass
// to the strtotime() method
Flight::route('/news', function () {
  Flight::response()->cache('+5 minutes');
  echo '此内容将被缓存。';
});
```

### 最后修改时间

您可以使用 `lastModified` 方法并传入一个 UNIX 时间戳，以设置页面最后修改的日期和时间。客户端将在最后修改值更改之前继续使用其缓存。

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo '此内容将被缓存。';
});
```

### ETag

`ETag` 缓存类似于 `Last-Modified`，但您可以为资源指定任何您想要的 ID：

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo '此内容将被缓存。';
});
```

请记住，调用 `lastModified` 或 `etag` 都会设置和检查缓存值。如果请求之间的缓存值相同，Flight 将立即发送 `HTTP 304` 响应并停止处理。

## 下载文件 (v3.12.0)

有一个辅助方法可以下载文件。您可以使用 `download` 方法并传入路径。

```php
Flight::route('/download', function () {
  Flight::download('/path/to/file.txt');
});
```