# 覆盖

Flight 允许您覆盖其默认功能以满足您自己的需求，而无需修改任何代码。

例如，当 Flight 无法将 URL 与路由匹配时，它会调用 `notFound` 方法，该方法发送一个通用的 `HTTP 404` 响应。您可以使用 `map` 方法覆盖此行为：

```php
Flight::map('notFound', function() {
  // 显示自定义 404 页面
  include 'errors/404.html';
});
```

Flight 还允许您替换框架的核心组件。例如，您可以使用自定义类替换默认的 Router 类：

```php
// 注册您的自定义类
Flight::register('router', MyRouter::class);

// 当 Flight 加载 Router 实例时，它将加载您的类
$myrouter = Flight::router();
```

但是，像 `map` 和 `register` 这样的框架方法不能被覆盖。如果您尝试这样做，将会收到错误提示。