# 配置

您可以通过 `set` 方法设置配置值来自定义 Flight 的某些行为。

```php
Flight::set('flight.log_errors', true);
```

以下是所有可用配置设置的列表：

- **flight.base_url** - 重写请求的基本 URL。(默认: null)
- **flight.case_sensitive** - URL 匹配时区分大小写。(默认: false)
- **flight.handle_errors** - 允许 Flight 内部处理所有错误。(默认: true)
- **flight.log_errors** - 将错误记录到 Web 服务器的错误日志文件。(默认: false)
- **flight.views.path** - 包含视图模板文件的目录。(默认: ./views)
- **flight.views.extension** - 视图模板文件扩展名。(默认: .php)