```zh
# 框架方法

Flight 旨在易于使用和理解。以下是框架的完整方法集。
它包括核心方法，这些是常规静态方法，以及可被筛选或覆盖的可扩展方法，这些是映射方法。

## 核心方法

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // 创建自定义框架方法。
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // 将类注册到框架方法。
Flight::before(string $name, callable $callback) // 在调用框架方法前添加筛选器。
Flight::after(string $name, callable $callback) // 在调用框架方法后添加筛选器。
Flight::path(string $path) // 添加自动加载类的路径。
Flight::get(string $key) // 获取变量。
Flight::set(string $key, mixed $value) // 设置变量。
Flight::has(string $key) // 检查变量是否设置。
Flight::clear(array|string $key = []) // 清除变量。
Flight::init() // 将框架初始化为默认设置。
Flight::app() // 获取应用程序对象实例
```

## 可扩展方法

```php
Flight::start() // 启动框架。
Flight::stop() // 停止框架并发送响应。
Flight::halt(int $code = 200, string $message = '') // 停止框架，可选择性地附带状态代码和消息。
Flight::route(string $pattern, callable $callback, bool $pass_route = false) // 将 URL 模式映射到回调。
Flight::group(string $pattern, callable $callback) // 为 URL 创建分组，模式必须为字符串。
Flight::redirect(string $url, int $code) // 重定向到另一个 URL。
Flight::render(string $file, array $data, ?string $key = null) // 渲染模板文件。
Flight::error(Throwable $error) // 发送 HTTP 500 响应。
Flight::notFound() // 发送 HTTP 404 响应。
Flight::etag(string $id, string $type = 'string') // 执行 ETag HTTP 缓存。
Flight::lastModified(int $time) // 执行上次修改的 HTTP 缓存。
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // 发送 JSON 响应。
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // 发送 JSONP 响应。
```

`map` 和 `register` 添加的任何自定义方法也可进行筛选。
```