# 错误处理

## 错误和异常

所有错误和异常都会被 Flight 捕获并传递给`error`方法。
默认行为是发送一个通用的`HTTP 500 内部服务器错误`响应，带有一些错误信息。

您可以根据自己的需求覆盖此行为:

```php
Flight::map('error', function (Throwable $error) {
  // 处理错误
  echo $error->getTraceAsString();
});
```

默认情况下，错误不会记录到 web 服务器。您可以通过更改配置来启用此功能:

```php
Flight::set('flight.log_errors', true);
```

## 未找到

当 URL 找不到时，Flight 调用`notFound`方法。默认行为是发送一个`HTTP 404 未找到`响应，带有一个简单消息。

您可以根据自己的需求覆盖此行为:

```php
Flight::map('notFound', function () {
  // 处理未找到
});
```