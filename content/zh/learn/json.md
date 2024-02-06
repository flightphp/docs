# JSON

Flight提供发送JSON和JSONP响应的支持。要发送JSON响应，您需要传递一些数据进行JSON编码：

```php
Flight::json(['id' => 123]);
```

对于JSONP请求，您可以选择传递用于定义回调函数的查询参数名称：

```php
Flight::jsonp(['id' => 123], 'q');
```

因此，当使用 `?q=my_func` 发出GET请求时，您应该收到以下输出：

```javascript
my_func({"id":123});
```

如果您没有传递查询参数名称，它将默认为 `jsonp`。