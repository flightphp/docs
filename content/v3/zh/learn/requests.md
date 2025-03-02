# 请求

Flight 将 HTTP 请求封装成一个单独的对象，可以通过以下方式访问：

```php
$request = Flight::request();
```

## 典型用例

在 Web 应用程序中处理请求时，您通常会想提取一个头部，或 `$_GET` 或 `$_POST` 参数，甚至可能是原始请求体。Flight 提供了一个简单的接口来完成所有这些操作。

以下是获取查询字符串参数的示例：

```php
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	echo "您正在搜索: $keyword";
	// 使用 $keyword 查询数据库或其他内容
});
```

以下是使用 POST 方法的表单示例：

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	echo "您提交了: $name, $email";
	// 使用 $name 和 $email 保存到数据库或其他内容
});
```

## 请求对象属性

请求对象提供以下属性：

- **body** - 原始 HTTP 请求体
- **url** - 被请求的 URL
- **base** - URL 的父子目录
- **method** - 请求方法 (GET, POST, PUT, DELETE)
- **referrer** - 引用 URL
- **ip** - 客户端的 IP 地址
- **ajax** - 请求是否为 AJAX 请求
- **scheme** - 服务器协议 (http, https)
- **user_agent** - 浏览器信息
- **type** - 内容类型
- **length** - 内容长度
- **query** - 查询字符串参数
- **data** - Post 数据或 JSON 数据
- **cookies** - Cookie 数据
- **files** - 上传的文件
- **secure** - 连接是否安全
- **accept** - HTTP 接受参数
- **proxy_ip** - 客户端的代理 IP 地址。按顺序扫描 `$_SERVER` 数组中的 `HTTP_CLIENT_IP`, `HTTP_X_FORWARDED_FOR`, `HTTP_X_FORWARDED`, `HTTP_X_CLUSTER_CLIENT_IP`, `HTTP_FORWARDED_FOR`, `HTTP_FORWARDED`。
- **host** - 请求主机名

您可以将 `query`, `data`, `cookies`, 和 `files` 属性作为数组或对象访问。

因此，要获取查询字符串参数，可以执行：

```php
$id = Flight::request()->query['id'];
```

或可以这样做：

```php
$id = Flight::request()->query->id;
```

## 原始请求体

要获取原始 HTTP 请求体，例如在处理 PUT 请求时，可以执行：

```php
$body = Flight::request()->getBody();
```

## JSON 输入

如果您发送带有类型 `application/json` 和数据 `{"id": 123}` 的请求，它将可从 `data` 属性获得：

```php
$id = Flight::request()->data->id;
```

## `$_GET`

您可以通过 `query` 属性访问 `$_GET` 数组：

```php
$id = Flight::request()->query['id'];
```

## `$_POST`

您可以通过 `data` 属性访问 `$_POST` 数组：

```php
$id = Flight::request()->data['id'];
```

## `$_COOKIE`

您可以通过 `cookies` 属性访问 `$_COOKIE` 数组：

```php
$myCookieValue = Flight::request()->cookies['myCookieName'];
```

## `$_SERVER`

可以通过 `getVar()` 方法访问 `$_SERVER` 数组的快捷方式：

```php

$host = Flight::request()->getVar['HTTP_HOST'];
```

## 通过 `$_FILES` 访问上传的文件

您可以通过 `files` 属性访问上传的文件：

```php
$uploadedFile = Flight::request()->files['myFile'];
```

## 处理文件上传

您可以使用框架中的一些助手方法处理文件上传。基本上这归结为从请求中提取文件数据，并将其移动到新位置。

```php
Flight::route('POST /upload', function(){
	// 如果您有一个输入字段，如 <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

如果您上传了多个文件，可以遍历它们：

```php
Flight::route('POST /upload', function(){
	// 如果您有一个输入字段，如 <input type="file" name="myFiles[]">
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **安全提示:** 始终验证和清理用户输入，尤其是在处理文件上传时。始终验证您允许上传的扩展名类型，但您还应该验证文件的“魔术字节”，以确保它实际上是用户声称的文件类型。有可用的 [文章](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [和](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [库](https://github.com/RikudouSage/MimeTypeDetector) 来帮助处理这个问题。

## 请求头

您可以使用 `getHeader()` 或 `getHeaders()` 方法访问请求头：

```php

// 也许您需要授权头
$host = Flight::request()->getHeader('Authorization');
// 或者
$host = Flight::request()->header('Authorization');

// 如果您需要获取所有头
$headers = Flight::request()->getHeaders();
// 或者
$headers = Flight::request()->headers();
```

## 请求体

您可以使用 `getBody()` 方法访问原始请求体：

```php
$body = Flight::request()->getBody();
```

## 请求方法

您可以使用 `method` 属性或 `getMethod()` 方法访问请求方法：

```php
$method = Flight::request()->method; // 实际上调用 getMethod()
$method = Flight::request()->getMethod();
```

**注意:** `getMethod()` 方法首先从 `$_SERVER['REQUEST_METHOD']` 提取方法，然后可以通过 `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` 覆盖它（如果存在），或者通过 `$_REQUEST['_method']`（如果存在）覆盖它。

## 请求 URL

有几个助手方法可以组合 URL 的不同部分，以方便您使用。

### 完整 URL

您可以使用 `getFullUrl()` 方法访问完整请求 URL：

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```
### 基础 URL

您可以使用 `getBaseUrl()` 方法访问基础 URL：

```php
$url = Flight::request()->getBaseUrl();
// 注意，末尾没有斜杠。
// https://example.com
```

## 查询解析

您可以将 URL 传递给 `parseQuery()` 方法，以将查询字符串解析为关联数组：

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```