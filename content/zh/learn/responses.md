# 响应

Flight有助于为您生成部分响应头，但您对向用户发送的内容具有绝大部分控制。有时，您可以直接访问`Response`对象，但大多数情况下，您将使用`Flight`实例发送响应。

## 发送基本响应

Flight使用ob_start()来缓冲输出。这意味着您可以使用`echo`或`print`向用户发送响应，Flight将捕获它并带着相应的标头发送给用户。

```php

// 这将向用户的浏览器发送“你好，世界!”
Flight::route('/', function() {
	echo "你好，世界！";
});

// HTTP/1.1 200 OK
// Content-Type: text/html
//
// 你好，世界！
```

作为替代方案，您可以调用`write()`方法来添加到正文中。

```php

// 这将向用户的浏览器发送“你好，世界!”
Flight::route('/', function() {
	// 冗长，但有时需要时可以完成工作
	Flight::response()->write("你好，世界！");

	// 如果您想检索此时设置的正文
	// 您可以像这样操作
	$body = Flight::response()->getBody();
});
```

## 状态码

您可以使用`status`方法设置响应的状态码：

```php
Flight::route('/@id', function($id) {
	if($id == 123) {
		Flight::response()->status(200);
		echo "你好，世界！";
	} else {
		Flight::response()->status(403);
		echo "禁止访问";
	}
});
```

如果要获取当前状态码，可以使用不带任何参数的`status`方法：

```php
Flight::response()->status(); // 200
```

## 设置响应头

您可以使用`header`方法设置响应的标头，比如内容类型：

```php

// 这将以纯文本形式发送“你好，世界!”到用户的浏览器
Flight::route('/', function() {
	Flight::response()->header('Content-Type', 'text/plain');
	echo "你好，世界！";
});
```



## JSON

Flight支持发送JSON和JSONP响应。要发送JSON响应，您需要传递要进行JSON编码的数据：

```php
Flight::json(['id' => 123]);
```

### JSONP

对于JSONP请求，您可以选择传入用于定义回调函数的查询参数名称：

```php
Flight::jsonp(['id' => 123], 'q');
```

因此，当通过`?q=my_func`进行GET请求时，您应该收到输出：

```javascript
my_func({"id":123});
```

如果不传入查询参数名称，它将默认为`jsonp`。

## 重定向到另一个URL

您可以通过使用`redirect()`方法并传递新的URL来重定向当前请求：

```php
Flight::redirect('/new/location');
```

默认情况下，Flight发送HTTP 303（“查看其他”）状态码。您可以选择设置自定义代码：

```php
Flight::redirect('/new/location', 401);
```

## 停止

您可以在任何时候通过调用`halt`方法来停止此框架：

```php
Flight::halt();
```

您还可以指定可选的`HTTP`状态码和消息：

```php
Flight::halt(200, '马上回来...');
```

调用`halt`将丢弃到目前为止的任何响应内容。如果要停止框架并输出当前响应，请使用`stop`方法：

```php
Flight::stop();
```

## HTTP缓存

Flight提供用于HTTP级别缓存的内置支持。如果满足缓存条件，Flight将返回一个HTTP `304 Not Modified`响应。下次客户端请求相同资源时，它们将被提示使用其本地缓存版本。

### 路由级别缓存

如果要缓存整个响应，可以使用`cache()`方法并传递缓存时间。

```php

// 这将缓存5分钟
Flight::route('/news', function () {
  Flight::cache(time() + 300);
  echo '此内容将被缓存。';
});

// 或者，您可以使用传递给strtotime()方法的字符串
Flight::route('/news', function () {
  Flight::cache('+5 minutes');
  echo '此内容将被缓存。';
});
```

### 上次修改时间

您可以使用`lastModified`方法并传递UNIX时间戳来设置页面上次修改的日期和时间。直到最后修改的值更改为止，客户端将继续使用其缓存。

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo '此内容将被缓存。';
});
```

### ETag

`ETag`缓存类似于`Last-Modified`，只是您可以为资源指定任何ID：

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo '此内容将被缓存。';
});
```

请记住，调用`lastModified`或`etag`将设置并检查缓存值。如果请求之间的缓存值相同，Flight将立即发送`HTTP 304`响应并停止处理。