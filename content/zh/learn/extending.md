## 扩展 / 容器

Flight 旨在成为一种可扩展的框架。框架提供一组默认方法和组件，但允许您映射自己的方法，注册自己的类，甚至重写现有的类和方法。

## 映射方法

要映射自定义方法，您可以使用 `map` 函数：

```php
// 映射您的方法
Flight::map('hello', function (string $name) {
  echo "hello $name!";
});

// 调用您的自定义方法
Flight::hello('Bob');
```

## 注册类 / 容器化

要注册自己的类，您可以使用 `register` 函数：

```php
// 注册您的类
Flight::register('user', User::class);

// 获取您的类的实例
$user = Flight::user();
```

register 方法还允许您将参数传递给您的类构造函数。因此，当加载自定义类时，它将被预初始化。您可以通过传入一个额外的数组来定义构造函数参数。以下是加载数据库连接的示例：

```php
// 带构造函数参数注册类
Flight::register('db', PDO::class, ['mysql:host=localhost;dbname=test', 'user', 'pass']);

// 获取您的类的实例
// 这将使用定义的参数创建一个对象
//
// new PDO('mysql:host=localhost;dbname=test','user','pass');
//
$db = Flight::db();
```

如果传递额外的回调参数，它将立即执行类构造后。这允许您为新对象执行任何设置流程。回调函数接受一个参数，即新对象的实例。

```php
// 回调将会传递构造的对象
Flight::register(
  'db',
  PDO::class,
  ['mysql:host=localhost;dbname=test', 'user', 'pass'],
  function (PDO $db) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
);
```

默认情况下，每次加载类时都会获得共享实例。要获取类的新实例，只需传入 `false` 作为参数：

```php
// 类的共享实例
$shared = Flight::db();

// 类的新实例
$new = Flight::db(false);
```

请注意，映射方法优先于注册的类。如果您使用相同名称声明两者，则只会调用映射方法。