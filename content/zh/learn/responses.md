# 响应

Flight 有助于为您生成部分响应标头，但您可以控制大部分要发送给用户的内容。有时您可以直接访问 `Response` 对象，但大多数情况下您将使用 `Flight` 实例发送响应。

## 发送基本响应

Flight 使用 `ob_start()` 来缓冲输出。这意味着您可以使用 `echo` 或 `print` 将响应发送给用户，Flight 将捕获并将其带回并发送回适当的头。

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

作为替代，您可以调用 `write()` 方法来添加到正文中。

```php

// 这将向用户的浏览器发送 "Hello, World!"
Flight::route('/', function() {
	// 冗长，但有时在需要时能完成工作
	Flight::response()->write("Hello, World!");

	// 如果您想要检索到目前设置的正文
	// 可以这样做
	$body = Flight::response()->getBody();
});
```

## 状态码

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

如果要获取当前状态码，可以使用不带任何参数的 `status` 方法：

```php
Flight::response()->status(); // 200
```

## 设置响应标头

您可以使用 `header` 方法设置响应的标头，如内容类型：

```php

// 这将向用户的浏览器发送纯文本 "Hello, World!"
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	echo "Hello, World!";
});
```



## JSON

Flight 支持发送 JSON 和 JSONP 响应。要发送 JSON 响应，您需要传递要进行 JSON 编码的某些数据：

```php
Flight::json(['id' => 123]);
```

### JSONP

对于 JSONP 请求，您可以选择传递用于定义回调函数的查询参数名：

```php
Flight::jsonp(['id' => 123], 'q');
```

因此，当使用 `?q=my_func` 发出 GET 请求时，您应该收到输出：

```javascript
my_func({"id":123});
```

如果不传入查询参数名，它将默认为 `jsonp`。

## 重定向到另一个 URL

您可以使用 `redirect()` 方法和传入新的 URL 来重定向当前请求：

```php
Flight::redirect('/new/location');
```

默认情况下，Flight 发送 HTTP 303 ("查看其他") 状态码。您可以可选地设置自定义代码：

```php
Flight::redirect('/new/location', 401);
```

## 停止

您可以通过调用 `halt` 方法在任何时候停止框架：

```php
Flight::halt();
```

您还可以指定可选的 `HTTP` 状态码和消息：

```php
Flight::halt(200, '马上回来...');
```

调用 `halt` 将丢弃到目前为止的任何响应内容。如果要停止框架并输出当前响应，请使用 `stop` 方法：

```php
Flight::stop();
```

## HTTP 缓存

Flight 提供内置支持的 HTTP 级缓存。如果满足缓存条件，Flight 将返回 HTTP `304 未修改` 响应。下次客户端请求相同资源时，将提示他们使用本地缓存。

### 路由级别缓存

如果要缓存整个响应，可以使用 `cache()` 方法并传递缓存时间。

```php

// 这将为 5 分钟缓存响应
Flight::route('/news', function () {
  Flight::cache(time() + 300);
  echo '这个内容将被缓存。';
});

// 或者，您可以使用会传递给 strtotime() 方法的字符串
Flight::route('/news', function () {
  Flight::cache('+5 minutes');
  echo '这个内容将被缓存。';
});
```

### 最后修改时间

您可以使用 `lastModified` 方法并传递 UNIX 时间戳来设置页面最后修改的日期和时间。客户端将继续使用其缓存，直到最后修改的值被更改。

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo '这个内容将被缓存。';
});
```

### ETag

`ETag` 缓存类似于 `Last-Modified`，只是您可以为资源指定任何想要的 id：

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo '这个内容将被缓存。';
});
```

请注意，调用 `lastModified` 或 `etag` 将同时设置和检查缓存值。如果在请求之间缓存值相同，Flight 将立即发送 `HTTP 304` 响应并停止处理。