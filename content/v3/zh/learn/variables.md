# 变量

Flight允许您保存变量，以便它们可以在应用程序的任何地方使用。

```php
// 保存变量
Flight::set('id', 123);

// 在应用程序的其他地方
$id = Flight::get('id');
```
要查看变量是否已设置，可以执行以下操作：

```php
if (Flight::has('id')) {
  // 做些什么
}
```

您可以通过以下方式清除变量：

```php
// 清除id变量
Flight::clear('id');

// 清除所有变量
Flight::clear();
```

Flight还使用变量进行配置目的。

```php
Flight::set('flight.log_errors', true);
```