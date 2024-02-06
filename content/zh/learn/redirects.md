# 重定向

您可以使用`redirect`方法并传入新的URL来重定向当前请求：

```php
Flight::redirect('/new/location');
```

默认情况下，Flight发送HTTP 303状态码。您还可以选择设置自定义代码：

```php
Flight::redirect('/new/location', 401);
```