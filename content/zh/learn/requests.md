# 请求

Flight将HTTP请求封装为单个对象，可以通过以下方式访问：

```php
$request = Flight::request();
```

请求对象提供以下属性：

- **body** - 原始HTTP请求正文
- **url** - 请求的URL
- **base** - URL的父子目录
- **method** - 请求方法（GET, POST, PUT, DELETE）
- **referrer** - 引荐 URL
- **ip** - 客户端的IP地址
- **ajax** - 请求是否是AJAX请求
- **scheme** - 服务器协议（http, https）
- **user_agent** - 浏览器信息
- **type** - 内容类型
- **length** - 内容长度
- **query** - 查询字符串参数
- **data** - Post数据或JSON数据
- **cookies** - Cookie数据
- **files** - 上传的文件
- **secure** - 连接是否安全
- **accept** - HTTP接受参数
- **proxy_ip** - 客户端的代理IP地址
- **host** - 请求主机名

你可以将`query`、`data`、`cookies`和`files`属性视为数组或对象来访问。

因此，要获取查询字符串参数，可以这样做：

```php
$id = Flight::request()->query['id'];
```

或者可以这样做：

```php
$id = Flight::request()->query->id;
```

## 原始请求正文

要获取原始的HTTP请求正文，例如处理PUT请求时，可以这样做：

```php
$body = Flight::request()->getBody();
```

## JSON输入

如果您发送包含类型为`application/json`以及数据`{"id": 123}`的请求，可以从`data`属性中获取：

```php
$id = Flight::request()->data->id;
```

## 访问`$_SERVER`

可以通过`getVar()`方法快速访问`$_SERVER`数组：

```php

$host = Flight::request()->getVar['HTTP_HOST'];
```

## 访问请求头

可以使用`getHeader()`或`getHeaders()`方法访问请求头：

```php

// 可能您需要Authorization头
$host = Flight::request()->getHeader('Authorization');

// 如果您需要获取所有头部信息
$headers = Flight::request()->getHeaders();
```