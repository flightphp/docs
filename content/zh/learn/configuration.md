# 配置

您可以通过设置配置值来自定义Flight的某些行为，方法是通过`set`方法。

```php
Flight::set('flight.log_errors', true);
```

## 可用的配置设置

以下是所有可用配置设置的列表：

- **flight.base_url** `?string` - 重写请求的基本URL。(默认值: null)
- **flight.case_sensitive** `bool` - URL的大小写匹配。(默认值: false)
- **flight.handle_errors** `bool` - 允许Flight内部处理所有错误。(默认值: true)
- **flight.log_errors** `bool` - 将错误记录到Web服务器的错误日志文件中。(默认值: false)
- **flight.views.path** `string` - 包含视图模板文件的目录。(默认值: ./views)
- **flight.views.extension** `string` - 视图模板文件扩展名。(默认值: .php)
- **flight.content_length** `bool` - 设置`Content-Length`头部。(默认值: true)
- **flight.v2.output_buffering** `bool` - 使用旧版本的输出缓冲。参见[migrating to v3](migrating-to-v3)。(默认值: false)

## 变量

Flight允许您保存变量，以便可以在应用程序的任何地方使用。

```php
// 保存您的变量
Flight::set('id', 123);

// 在应用程序的其他地方
$id = Flight::get('id');
```

要查看变量是否已设置，您可以执行以下操作：

```php
if (Flight::has('id')) {
  // 做一些事情
}
```

您可以通过以下方式清除变量：

```php
// 清除id变量
Flight::clear('id');

// 清除所有变量
Flight::clear();
```

Flight还使用变量进行配置目的。

```php
Flight::set('flight.log_errors', true);
```

## 错误处理

### 错误和异常

Flight捕获所有错误和异常，并将其传递给`error`方法。
默认行为是发送一个带有一些错误信息的通用`HTTP 500内部服务器错误`响应。

您可以根据自己的需求覆盖此行为：

```php
Flight::map('error', function (Throwable $error) {
  // 处理错误
  echo $error->getTraceAsString();
});
```

默认情况下，错误不会记录到Web服务器。您可以通过更改配置来启用此功能：

```php
Flight::set('flight.log_errors', true);
```

### 未找到

当找不到URL时，Flight将调用`notFound`方法。默认行为是发送一个带有简单消息的`HTTP 404未找到`响应。

您可以根据自己的需求覆盖此行为：

```php
Flight::map('notFound', function () {
  // 处理未找到
});
```