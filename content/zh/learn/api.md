```zh
# 框架 API 方法

Flight 旨在易于使用和理解。以下是框架的完整方法集。它包括核心方法，这些是常规的静态方法，以及可扩展方法，这些是可以被过滤或覆盖的映射方法。

## 核心方法

这些方法对框架至关重要，不能被覆盖。

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // 创建自定义框架方法。
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // 将一个类注册到框架方法。
Flight::unregister(string $name) // 将一个类从框架方法中注销。
Flight::before(string $name, callable $callback) // 在框架方法之前添加过滤器。
Flight::after(string $name, callable $callback) // 在框架方法之后添加过滤器。
Flight::path(string $path) // 添加用于自动加载类的路径。
Flight::get(string $key) // 获取一个变量。
Flight::set(string $key, mixed $value) // 设置一个变量。
Flight::has(string $key) // 检查变量是否已设置。
Flight::clear(array|string $key = []) // 清除一个变量。
Flight::init() // 将框架初始化为默认设置。
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
Flight::halt(int $code = 200, string $message = '') // 以可选的状态代码和消息停止框架。
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // 将 URL 模式映射到回调。
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // 将 POST 请求 URL 模式映射到回调。
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // 将 PUT 请求 URL 模式映射到回调。
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // 将 PATCH 请求 URL 模式映射到回调。
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // 将 DELETE 请求 URL 模式映射到回调。
Flight::group(string $pattern, callable $callback) // 为 URL 创建分组，模式必须是字符串。
Flight::getUrl(string $name, array $params = []) // 根据路由别名生成 URL。
Flight::redirect(string $url, int $code) // 重定向到另一个 URL。
Flight::render(string $file, array $data, ?string $key = null) // 渲染模板文件。
Flight::error(Throwable $error) // 发送 HTTP 500 响应。
Flight::notFound() // 发送 HTTP 404 响应。
Flight::etag(string $id, string $type = 'string') // 执行 ETag HTTP 缓存。
Flight::lastModified(int $time) // 执行最后修改的 HTTP 缓存。
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // 发送 JSON 响应。
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // 发送 JSONP 响应。
```

任何使用 `map` 和 `register` 添加的自定义方法也可以被过滤。
``` 