# 请求

Flight 将 HTTP 请求封装为一个单独的对象，可以通过以下方式访问：

```php
$request = Flight::request();
```

请求对象提供以下属性：

- **url** - 请求的 URL
- **base** - URL 的父目录
- **method** - 请求方法 (GET, POST, PUT, DELETE)
- **referrer** - 引荐 URL
- **ip** - 客户端的 IP 地址
- **ajax** - 请求是否为 AJAX 请求
- **scheme** - 服务器协议 (http, https)
- **user_agent** - 浏览器信息
- **type** - 内容类型
- **length** - 内容长度
- **query** - 查询字符串参数
- **data** - 表单数据或 JSON 数据
- **cookies** - Cookie 数据
- **files** - 上传的文件
- **secure** - 连接是否安全
- **accept** - HTTP 接受参数
- **proxy_ip** - 客户端的代理 IP 地址
- **host** - 请求主机名

您可以将 `query`、`data`、`cookies` 和 `files` 属性作为数组或对象来访问。

因此，要获取查询字符串参数，可以这样做：

```php
$id = Flight::request()->query['id'];
```

或者可以这样做：

```php
$id = Flight::request()->query->id;
```

## 原始请求体

要获取原始的 HTTP 请求体，例如在处理 PUT 请求时，可以这样做：

```php
$body = Flight::request()->getBody();
```

## JSON 输入

如果您发送一个带有类型 `application/json` 和数据 `{"id": 123}` 的请求，那么可以从 `data` 属性中获取：

```php
$id = Flight::request()->data->id;
```