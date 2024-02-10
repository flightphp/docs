# 配置

您可以通过设置配置值来自定义 Flight 的某些行为，方法是使用 `set` 方法。

```php
Flight::set('flight.log_errors', true);
```

## 可用的配置设置

以下是所有可用配置设置的列表：

- **flight.base_url** - 覆盖请求的基本 URL。 (默认: null)
- **flight.case_sensitive** - URL 匹配时区分大小写。 (默认: false)
- **flight.handle_errors** - 允许 Flight 在内部处理所有错误。 (默认: true)
- **flight.log_errors** - 将错误记录到 Web 服务器的错误日志文件中。 (默认: false)
- **flight.views.path** - 包含视图模板文件的目录。 (默认: ./views)
- **flight.views.extension** - 视图模板文件扩展名。 (默认: .php)

## 变量

Flight 允许您保存变量，以便在应用程序的任何位置使用。

```php
// 保存您的变量
Flight::set('id', 123);

// 在您的应用程序的其他位置
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
// 清除 id 变量
Flight::clear('id');

// 清除所有变量
Flight::clear();
```

Flight 还使用变量进行配置目的。

```php
Flight::set('flight.log_errors', true);
```

## 错误处理

### 错误和异常

Flight 捕获所有错误和异常，并将它们传递到 `error` 方法。默认行为是发送一个通用的 `HTTP 500 服务器内部错误` 响应以及一些错误信息。

您可以根据自己的需求覆盖此行为：

```php
Flight::map('error', function (Throwable $error) {
  // 处理错误
  echo $error->getTraceAsString();
});
```

默认情况下，错误不会记录到 Web 服务器中。您可以通过更改配置来启用此功能：

```php
Flight::set('flight.log_errors', true);
```

### 未找到

当找不到 URL 时，Flight 调用 `notFound` 方法。默认行为是发送一个 `HTTP 404 未找到` 响应以及一个简单消息。

您可以根据自己的需求覆盖此行为：

```php
Flight::map('notFound', function () {
  // 处理未找到
});
```