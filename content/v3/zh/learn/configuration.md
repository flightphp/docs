# 配置

## 概述

Flight 提供了一种简单的方式来配置框架的各个方面，以适应您的应用程序需求。有些配置是默认设置的，但您可以根据需要覆盖它们。您还可以设置自己的变量，以便在整个应用程序中使用。

## 理解

您可以通过 `set` 方法设置配置值来自定义 Flight 的某些行为。

```php
Flight::set('flight.log_errors', true);
```

在 `app/config/config.php` 文件中，您可以看到所有可用的默认配置变量。

## 基本用法

### Flight 配置选项

以下是所有可用配置设置的列表：

- **flight.base_url** `?string` - 如果 Flight 在子目录中运行，则覆盖请求的基 URL。（默认：null）
- **flight.case_sensitive** `bool` - URL 的区分大小写匹配。（默认：false）
- **flight.handle_errors** `bool` - 允许 Flight 内部处理所有错误。（默认：true）
  - 如果您希望 Flight 处理错误而不是默认的 PHP 行为，则此值需要为 true。
  - 如果您安装了 [Tracy](/awesome-plugins/tracy)，您希望将此值设置为 false，以便 Tracy 可以处理错误。
  - 如果您安装了 [APM](/awesome-plugins/apm) 插件，您希望将此值设置为 true，以便 APM 可以记录错误。
- **flight.log_errors** `bool` - 将错误记录到 Web 服务器的错误日志文件。（默认：false）
  - 如果您安装了 [Tracy](/awesome-plugins/tracy)，Tracy 将根据 Tracy 配置记录错误，而不是此配置。
- **flight.views.path** `string` - 包含视图模板文件的目录。（默认：./views）
- **flight.views.extension** `string` - 视图模板文件扩展名。（默认：.php）
- **flight.content_length** `bool` - 设置 `Content-Length` 标头。（默认：true）
  - 如果您使用 [Tracy](/awesome-plugins/tracy)，则需要将此值设置为 false，以便 Tracy 可以正确渲染。
- **flight.v2.output_buffering** `bool` - 使用旧版输出缓冲。请参阅 [migrating to v3](migrating-to-v3)。（默认：false）

### Loader 配置

加载器还有另一个配置设置。这将允许您自动加载类名中包含 `_` 的类。

```php
// Enable class loading with underscores
// Defaulted to true
Loader::$v2ClassLoading = false;
```

### 变量

Flight 允许您保存变量，以便在应用程序的任何地方使用它们。

```php
// Save your variable
Flight::set('id', 123);

// Elsewhere in your application
$id = Flight::get('id');
```
要检查变量是否已设置，您可以这样做：

```php
if (Flight::has('id')) {
  // Do something
}
```

您可以通过以下方式清除变量：

```php
// Clears the id variable
Flight::clear('id');

// Clears all variables
Flight::clear();
```

> **注意：** 仅仅因为您可以设置变量并不意味着您应该这样做。请谨慎使用此功能。原因是这里存储的任何内容都会成为全局变量。全局变量很糟糕，因为它们可以从应用程序的任何地方更改，这使得跟踪错误变得困难。此外，这还会使诸如 [unit testing](/guides/unit-testing) 之类的事情复杂化。

### 错误和异常

所有错误和异常都会被 Flight 捕获并传递给 `error` 方法，如果 `flight.handle_errors` 设置为 true。

默认行为是发送一个通用的 `HTTP 500 Internal Server Error` 响应，并附带一些错误信息。

您可以[覆盖](/learn/extending)此行为以满足自己的需求：

```php
Flight::map('error', function (Throwable $error) {
  // Handle error
  echo $error->getTraceAsString();
});
```

默认情况下，错误不会记录到 Web 服务器。您可以通过更改配置来启用此功能：

```php
Flight::set('flight.log_errors', true);
```

#### 404 未找到

当找不到 URL 时，Flight 会调用 `notFound` 方法。默认行为是发送一个 `HTTP 404 Not Found` 响应，并附带一个简单的消息。

您可以[覆盖](/learn/extending)此行为以满足自己的需求：

```php
Flight::map('notFound', function () {
  // Handle not found
});
```

## 另请参阅
- [Extending Flight](/learn/extending) - 如何扩展和自定义 Flight 的核心功能。
- [Unit Testing](/guides/unit-testing) - 如何为您的 Flight 应用程序编写单元测试。
- [Tracy](/awesome-plugins/tracy) - 用于高级错误处理和调试的插件。
- [Tracy Extensions](/awesome-plugins/tracy_extensions) - 用于将 Tracy 与 Flight 集成的扩展。
- [APM](/awesome-plugins/apm) - 用于应用程序性能监控和错误跟踪的插件。

## 故障排除
- 如果您在找出配置的所有值时遇到问题，您可以执行 `var_dump(Flight::get());`

## 更新日志
- v3.5.0 - 添加了 `flight.v2.output_buffering` 配置以支持旧版输出缓冲行为。
- v2.0 - 添加了核心配置。