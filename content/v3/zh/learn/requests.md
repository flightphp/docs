# 请求

## 概述

Flight 将 HTTP 请求封装到一个单一对象中，可以通过以下方式访问：

```php
$request = Flight::request();
```

## 理解

HTTP 请求是理解 HTTP 生命周期的核心方面之一。用户在 Web 浏览器或 HTTP 客户端上执行一个操作，他们会向您的项目发送一系列头部、主体、URL 等。您可以捕获这些头部（浏览器的语言、他们能处理的压缩类型、用户代理等）并捕获发送到您的 Flight 应用程序的主体和 URL。这些请求对于您的应用程序了解下一步该做什么至关重要。

## 基本用法

PHP 有几个超级全局变量，包括 `$_GET`、`$_POST`、`$_REQUEST`、`$_SERVER`、`$_FILES` 和 `$_COOKIE`。Flight 将这些抽象为方便的 [Collections](/learn/collections)。您可以像数组或对象一样访问 `query`、`data`、`cookies` 和 `files` 属性。

> **注意：** 强烈不鼓励在您的项目中使用这些超级全局变量，应通过 `request()` 对象引用。

> **注意：** `$_ENV` 没有可用的抽象。

### `$_GET`

您可以通过 `query` 属性访问 `$_GET` 数组：

```php
// GET /search?keyword=something
Flight::route('/search', function(){
	$keyword = Flight::request()->query['keyword'];
	// 或
	$keyword = Flight::request()->query->keyword;
	echo "您正在搜索：$keyword";
	// 使用 $keyword 查询数据库或其他内容
});
```

### `$_POST`

您可以通过 `data` 属性访问 `$_POST` 数组：

```php
Flight::route('POST /submit', function(){
	$name = Flight::request()->data['name'];
	$email = Flight::request()->data['email'];
	// 或
	$name = Flight::request()->data->name;
	$email = Flight::request()->data->email;
	echo "您提交了：$name, $email";
	// 使用 $name 和 $email 保存到数据库或其他内容
});
```

### `$_COOKIE`

您可以通过 `cookies` 属性访问 `$_COOKIE` 数组：

```php
Flight::route('GET /login', function(){
	$savedLogin = Flight::request()->cookies['myLoginCookie'];
	// 或
	$savedLogin = Flight::request()->cookies->myLoginCookie;
	// 检查是否真正保存，如果是则自动登录
	if($savedLogin) {
		Flight::redirect('/dashboard');
		return;
	}
});
```

有关设置新 cookie 值的帮助，请参阅 [overclokk/cookie](/awesome-plugins/php-cookie)

### `$_SERVER`

可以通过 `getVar()` 方法快捷访问 `$_SERVER` 数组：

```php

$host = Flight::request()->getVar('HTTP_HOST');
```

### `$_FILES`

您可以通过 `files` 属性访问上传的文件：

```php
// 直接访问 $_FILES 属性。有关推荐方法，请参阅下文
$uploadedFile = Flight::request()->files['myFile']; 
// 或
$uploadedFile = Flight::request()->files->myFile;
```

有关更多信息，请参阅 [Uploaded File Handler](/learn/uploaded-file)。

#### 处理文件上传

_v3.12.0_

您可以使用框架的一些辅助方法处理文件上传。基本上，它归结为从请求中拉取文件数据，并将其移动到新位置。

```php
Flight::route('POST /upload', function(){
	// 如果您有一个输入字段如 <input type="file" name="myFile">
	$uploadedFileData = Flight::request()->getUploadedFiles();
	$uploadedFile = $uploadedFileData['myFile'];
	$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
});
```

如果您上传了多个文件，可以循环遍历它们：

```php
Flight::route('POST /upload', function(){
	// 如果您有一个输入字段如 <input type="file" name="myFiles[]">
	$uploadedFiles = Flight::request()->getUploadedFiles()['myFiles'];
	foreach ($uploadedFiles as $uploadedFile) {
		$uploadedFile->moveTo('/path/to/uploads/' . $uploadedFile->getClientFilename());
	}
});
```

> **安全注意：** 始终验证和清理用户输入，尤其是在处理文件上传时。始终验证您允许上传的扩展类型，但您还应该验证文件的“魔术字节”以确保它是用户声称的文件类型。有 [文章](https://dev.to/yasuie/php-file-upload-check-uploaded-files-with-magic-bytes-54oe) [和](https://amazingalgorithms.com/snippets/php/detecting-the-mime-type-of-an-uploaded-file-using-magic-bytes/) [库](https://github.com/RikudouSage/MimeTypeDetector) 可帮助处理此问题。

### 请求主体

要获取原始 HTTP 请求主体，例如在处理 POST/PUT 请求时，您可以这样做：

```php
Flight::route('POST /users/xml', function(){
	$xmlBody = Flight::request()->getBody();
	// 处理发送的 XML。
});
```

### JSON 主体

如果您收到内容类型为 `application/json` 的请求，并且示例数据为 `{"id": 123}`，它将从 `data` 属性可用：

```php
$id = Flight::request()->data->id;
```

### 请求头部

您可以使用 `getHeader()` 或 `getHeaders()` 方法访问请求头部：

```php

// 也许您需要 Authorization 头部
$host = Flight::request()->getHeader('Authorization');
// 或
$host = Flight::request()->header('Authorization');

// 如果您需要获取所有头部
$headers = Flight::request()->getHeaders();
// 或
$headers = Flight::request()->headers();
```

### 请求方法

您可以使用 `method` 属性或 `getMethod()` 方法访问请求方法：

```php
$method = Flight::request()->method; // 实际由 getMethod() 填充
$method = Flight::request()->getMethod();
```

**注意：** `getMethod()` 方法首先从 `$_SERVER['REQUEST_METHOD']` 拉取方法，然后如果存在 `$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']` 或 `$_REQUEST['_method']`，它可以被覆盖。

## 请求对象属性

请求对象提供以下属性：

- **body** - 原始 HTTP 请求主体
- **url** - 被请求的 URL
- **base** - URL 的父子目录
- **method** - 请求方法 (GET, POST, PUT, DELETE)
- **referrer** - 引用 URL
- **ip** - 客户端 IP 地址
- **ajax** - 是否为 AJAX 请求
- **scheme** - 服务器协议 (http, https)
- **user_agent** - 浏览器信息
- **type** - 内容类型
- **length** - 内容长度
- **query** - 查询字符串参数
- **data** - 帖子数据或 JSON 数据
- **cookies** - Cookie 数据
- **files** - 上传的文件
- **secure** - 是否为安全连接
- **accept** - HTTP 接受参数
- **proxy_ip** - 客户端代理 IP 地址。按顺序扫描 `$_SERVER` 数组中的 `HTTP_CLIENT_IP`、`HTTP_X_FORWARDED_FOR`、`HTTP_X_FORWARDED`、`HTTP_X_CLUSTER_CLIENT_IP`、`HTTP_FORWARDED_FOR`、`HTTP_FORWARDED`。
- **host** - 请求主机名
- **servername** - 来自 `$_SERVER` 的 SERVER_NAME

## URL 辅助方法

有一些辅助方法可以方便地组合 URL 的部分。

### 完整 URL

您可以使用 `getFullUrl()` 方法访问完整的请求 URL：

```php
$url = Flight::request()->getFullUrl();
// https://example.com/some/path?foo=bar
```

### 基础 URL

您可以使用 `getBaseUrl()` 方法访问基础 URL：

```php
// http://example.com/path/to/something/cool?query=yes+thanks
$url = Flight::request()->getBaseUrl();
// https://example.com
// 注意，没有尾随斜杠。
```

## 查询解析

您可以将 URL 传递给 `parseQuery()` 方法来解析查询字符串为关联数组：

```php
$query = Flight::request()->parseQuery('https://example.com/some/path?foo=bar');
// ['foo' => 'bar']
```

## 另请参阅
- [Routing](/learn/routing) - 了解如何将路由映射到控制器并渲染视图。
- [Responses](/learn/responses) - 如何自定义 HTTP 响应。
- [Why a Framework?](/learn/why-frameworks) - 请求如何融入大局。
- [Collections](/learn/collections) - 处理数据集合。
- [Uploaded File Handler](/learn/uploaded-file) - 处理文件上传。

## 故障排除
- 如果您的 Web 服务器位于代理、负载均衡器等后面，`request()->ip` 和 `request()->proxy_ip` 可能不同。

## 更新日志
- v3.12.0 - 添加了通过请求对象处理文件上传的能力。
- v1.0 - 初始发布。