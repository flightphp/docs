# 请求

Flight 将 HTTP 请求封装为一个单个对象，可通过以下方式访问：

```php
$request = Flight::request();
```

请求对象提供以下属性：

- **body** - HTTP 请求的原始主体
- **url** - 请求的 URL
- **base** - URL 的父子目录
- **method** - 请求方法 (GET, POST, PUT, DELETE)
- **referrer** - 引荐 URL
- **ip** - 客户端的 IP 地址
- **ajax** - 请求是否为 AJAX 请求
- **scheme** - 服务器协议 (http, https)
- **user_agent** - 浏览器信息
- **type** - 内容类型
- **length** - 内容长度
- **query** - 查询字符串参数
- **data** - POST 数据或 JSON 数据
- **cookies** - Cookie 数据
- **files** - 上传的文件
- **secure** - 连接是否安全
- **accept** - HTTP 接受参数
- **proxy_ip** - 客户端的代理 IP 地址
- **host** - 请求的主机名

您可以将 `query`、`data`、`cookies` 和 `files` 属性作为数组或对象访问。

因此，若要获取查询字符串参数，可以执行：

```php
$id = Flight::request()->query['id'];
```

或者执行：

```php
$id = Flight::request()->query->id;
```

## 原始请求主体

要获取原始的 HTTP 请求主体，例如在处理 PUT 请求时，可以执行：

```php
$body = Flight::request()->getBody();
```

## JSON 输入

如果您发送的请求类型为 `application/json`，数据为 `{"id": 123}`，则可以从 `data` 属性中获取：

```php
$id = Flight::request()->data->id;
```

## 访问 `$_SERVER`

可以通过 `getVar()` 方法快速访问 `$_SERVER` 数组：

```php
$host = Flight::request()->getVar['HTTP_HOST'];
```

## 访问请求头

可以使用 `getHeader()` 或 `getHeaders()` 方法访问请求头：

```php
// 可能你需要 Authorization 头
$host = Flight::request()->getHeader('Authorization');

// 如果需要获取所有头部
$headers = Flight::request()->getHeaders();
```  