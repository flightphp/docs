# HTTP 缓存

Flight 提供了内置支持，用于 HTTP 级别的缓存。如果满足缓存条件，Flight 将返回一个 HTTP `304 Not Modified` 响应。下一次客户端请求相同资源时，它们将被提示使用本地缓存版本。

## 上次修改时间

您可以使用 `lastModified` 方法并传入一个 UNIX 时间戳来设置页面上次修改的日期和时间。客户端将继续使用它们的缓存，直到上次修改的值被更改。

```php
Flight::route('/news', function () {
  Flight::lastModified(1234567890);
  echo '此内容将被缓存。';
});
```

## ETag

`ETag` 缓存类似于 `Last-Modified`，不同之处在于您可以为资源指定任何想要的 id：

```php
Flight::route('/news', function () {
  Flight::etag('my-unique-id');
  echo '此内容将被缓存。';
});
```

请记住，调用 `lastModified` 或 `etag` 都将设置并检查缓存值。如果在请求之间的缓存值相同，则 Flight 将立即发送一个 `HTTP 304` 响应并停止处理。