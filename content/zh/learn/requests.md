# 请求

Flight将HTTP请求封装为一个对象，可以通过以下方式访问：

```php
$request = Flight::request();
```

## 典型用例

在Web应用程序中处理请求时，通常需要提取头部，或者`$_GET`或`$_POST`参数，甚至可能是原始请求体。Flight提供了一个简单的接口来执行所有这些操作。

这里有一个获取查询字符串参数的示例：

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "您正在搜索：$keyword";
	// 使用$keyword查询数据库或其他内容
});
```

这里是一个使用POST方法的表单示例：

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
  	$email = Flight::request()->data['email'];
  	echo "您提交了：$name, $email";
	// 使用$name和$email保存到数据库或其他地方
});
```

## 请求对象属性

请求对象提供以下属性：

- **body** - 原始HTTP请求体
- **url** - 请求的URL
- **base** - URL的父目录
- **method** - 请求方法（GET、POST、PUT、DELETE）
- **referrer** - 引荐URL
- **ip** - 客户端的IP地址
- **ajax** - 请求是否为AJAX请求
- **scheme** - 服务器协议（http、https）
- **user_agent** - 浏览器信息
- **type** - 内容类型
- **length** - 内容长度
- **query** - 查询字符串参数
- **data** - POST数据或JSON数据
- **cookies** - Cookie数据
- **files** - 上传的文件
- **secure** - 连接是否安全
- **accept** - HTTP接受参数
- **proxy_ip** - 客户端的代理IP地址。按顺序扫描`$_SERVER`数组中的`HTTP_CLIENT_IP`、`HTTP_X_FORWARDED_FOR`、`HTTP_X_FORWARDED`、`HTTP_X_CLUSTER_CLIENT_IP`、`HTTP_FORWARDED_FOR`和`HTTP_FORWARDED`。
- **host** - 请求主机名

您可以将`query`、`data`、`cookies`和`files`属性视为数组或对象。

因此，要获取查询字符串参数，可以这样做：

```php
$id = Flight::request()->query['id'];
```

或者可以这样做：

```php
$id = Flight::request()->query->id;
```

## 原始请求体

要获取原始HTTP请求体，例如在处理PUT请求时，可以这样做：

```php
$body = Flight::request()->getBody();
```

## JSON输入

如果用类型`application/json`和数据`{"id": 123}`发送请求，可以从`data`属性中获取：

```php
$id = Flight::request()->data->id;
```

## `$_GET`

您可以通过`query`属性访问`$_GET`数组：

```php
$id = Flight::request()->query['id'];
```

## `$_POST`

您可以通过`data`属性访问`$_POST`数组：

```php
$id = Flight::request()->data['id'];
```

## `$_COOKIE`

您可以通过`cookies`属性访问`$_COOKIE`数组：

```php
$myCookieValue = Flight::request()->cookies['myCookieName'];
```

## `$_SERVER`

您可以使用`getVar()`方法访问`$_SERVER`数组的快捷方式：

```php

$host = Flight::request()->getVar['HTTP_HOST'];
```

## 通过`$_FILES`上传的文件

您可以通过`files`属性访问上传的文件：

```php
$uploadedFile = Flight::request()->files['myFile'];
```

## 请求头

您可以使用`getHeader()`或`getHeaders()`方法访问请求头：

```php

//也许您需要Authorization头部
$host = Flight::request()->getHeader('Authorization');
// 或
$host = Flight::request()->header('Authorization');

//如果需要获取所有头部
$headers = Flight::request()->getHeaders();
// 或
$headers = Flight::request()->headers();
```

## 请求体

您可以使用`getBody()`方法访问原始请求体：

```php
$body = Flight::request()->getBody();
```

## 请求方法

您可以使用`method`属性或`getMethod()`方法访问请求方法：

```php
$method = Flight::request()->method; // 实际上调用了getMethod()
$method = Flight::request()->getMethod();
```

**注意：** `getMethod()`方法首先从`$_SERVER['REQUEST_METHOD']`中获取方法，然后如果存在`$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']`，则会被覆盖，如果存在`$_REQUEST['_method']`，也会被覆盖。

## 请求URL

有一些辅助方法可用于方便地组合URL的各个部分。

### 完整URL

您可以使用`getFullUrl()`方法访问完整的请求URL：

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```
### 基本URL

您可以使用`getBaseUrl()`方法访问基本URL：

```php
$url = Flight::request()->getBaseUrl();
// 请注意，没有尾随斜杠。
// https://example.com
```

## 查询解析

您可以将URL传递给`parseQuery()`方法来将查询字符串解析为关联数组：

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```