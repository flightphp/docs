# 框架 API 方法

Flight 被设计为易于使用和理解。以下是框架的完整方法集。它包括核心方法，即常规静态方法，以及可过滤或覆盖的可扩展方法。

## 核心方法

这些方法对框架至关重要，不可被覆盖。

```php
Flight::map(string $名称, callable $回调, bool $传递路由 = false) // 创建自定义框架方法。
Flight::register(string $名称, string $类, array $参数 = [], ?callable $回调 = null) // 将类注册到框架方法。
Flight::unregister(string $名称) // 将类取消注册到框架方法。
Flight::before(string $名称, callable $回调) // 在框架方法之前添加过滤器。
Flight::after(string $名称, callable $回调) // 在框架方法之后添加过滤器。
Flight::path(string $路径) // 添加自动加载类的路径。
Flight::get(string $键) // 获取变量。
Flight::set(string $键, mixed $值) // 设置变量。
Flight::has(string $键) // 检查变量是否设置。
Flight::clear(array|string $键 = []) // 清除变量。
Flight::init() // 将框架初始化为其默认设置。
Flight::app() // 获取应用程序对象实例
Flight::request() // 获取请求对象实例
Flight::response() // 获取响应对象实例
Flight::router() // 获取路由器对象实例
Flight::view() // 获取视图对象实例
```

## 可扩展方法

```php
Flight::start() // 启动框架。
Flight::stop() // 停止框架并发送响应。
Flight::halt(int $代码 = 200, string $消息 = '') // 停止框架，并可选择添加状态代码和消息。
Flight::route(string $模式, callable $回调, bool $传递路由 = false, string $别名 = '') // 将 URL 模式映射到回调。
Flight::post(string $模式, callable $回调, bool $传递路由 = false, string $别名 = '') // 将 POST 请求 URL 模式映射到回调。
Flight::put(string $模式, callable $回调, bool $传递路由 = false, string $别名 = '') // 将 PUT 请求 URL 模式映射到回调。
Flight::patch(string $模式, callable $回调, bool $传递路由 = false, string $别名 = '') // 将 PATCH 请求 URL 模式映射到回调。
Flight::delete(string $模式, callable $回调, bool $传递路由 = false, string $别名 = '') // 将 DELETE 请求 URL 模式映射到回调。
Flight::group(string $模式, callable $回调) // 为 URL 创建分组，模式必须是字符串。
Flight::getUrl(string $名称, array $参数 = []) // 基于路由别名生成 URL。
Flight::redirect(string $url, int $代码) // 重定向到另一个 URL。
Flight::render(string $文件, array $数据, ?string $键 = null) // 渲染模板文件。
Flight::error(Throwable $错误) // 发送 HTTP 500 响应。
Flight::notFound() // 发送 HTTP 404 响应。
Flight::etag(string $id, string $类型 = 'string') // 执行 ETag HTTP 缓存。
Flight::lastModified(int $时间) // 执行上次修改的 HTTP 缓存。
Flight::json(mixed $数据, int $代码 = 200, bool $编码 = true, string $字符集 = 'utf8', int $选项) // 发送 JSON 响应。
Flight::jsonp(mixed $数据, string $参数 = 'jsonp', int $代码 = 200, bool $编码 = true, string $字符集 = 'utf8', int $选项) // 发送 JSONP 响应。
```

通过 `map` 和 `register` 添加的任何自定义方法也可以被过滤。