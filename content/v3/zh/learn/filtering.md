# 过滤

Flight 允许您在调用方法之前和之后对其进行过滤。无需记忆预定义的钩子。您可以过滤任何默认框架方法以及您映射的任何自定义方法。

过滤函数如下所示：

```php
function (array &$params, string &$output): bool {
  // 过滤代码
}
```

使用传入的变量，您可以操作输入参数和/或输出。

您可以通过以下方式在调用方法之前运行过滤器：

```php
Flight::before('start', function (array &$params, string &$output): bool {
  // 做一些事情
});
```

您可以通过以下方式在调用方法之后运行过滤器：

```php
Flight::after('start', function (array &$params, string &$output): bool {
  // 做一些事情
});
```

您可以为任何方法添加任意数量的过滤器。它们将按照声明的顺序被调用。

以下是过滤过程的示例：

```php
// 映射自定义方法
Flight::map('hello', function (string $name) {
  return "Hello, $name!";
});

// 添加一个前置过滤器
Flight::before('hello', function (array &$params, string &$output): bool {
  // 操作参数
  $params[0] = 'Fred';
  return true;
});

// 添加一个后置过滤器
Flight::after('hello', function (array &$params, string &$output): bool {
  // 操作输出
  $output .= " Have a nice day!";
  return true;
});

// 调用自定义方法
echo Flight::hello('Bob');
```

这应该显示：

```
Hello Fred! Have a nice day!
```

如果您定义了多个过滤器，可以通过在任何过滤器函数中返回 `false` 来中断链：

```php
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'one';
  return true;
});

Flight::before('start', function (array &$params, string &$output): bool {
  echo 'two';

  // 这将结束链
  return false;
});

// 这将不会被调用
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'three';
  return true;
});
```

请注意，`map` 和 `register` 等核心方法无法进行过滤，因为它们是直接调用而不是动态调用的。