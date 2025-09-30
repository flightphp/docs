# 过滤

## 概述

Flight 允许您在调用 [mapped methods](/learn/extending) 前后过滤它们。

## 理解
没有预定义的钩子需要您记忆。您可以过滤任何默认框架方法以及您已映射的任何自定义方法。

过滤函数看起来像这样：

```php
/**
 * @param array $params 被过滤方法的传递参数。
 * @param string $output （仅 v2 输出缓冲）被过滤方法的输出。
 * @return bool 返回 true/void 或不返回以继续链条，返回 false 以中断链条。
 */
function (array &$params, string &$output): bool {
  // 过滤代码
}
```

使用传递的变量，您可以操纵输入参数和/或输出。

您可以通过以下方式让过滤器在方法之前运行：

```php
Flight::before('start', function (array &$params, string &$output): bool {
  // 执行某些操作
});
```

您可以通过以下方式让过滤器在方法之后运行：

```php
Flight::after('start', function (array &$params, string &$output): bool {
  // 执行某些操作
});
```

您可以为任何方法添加任意数量的过滤器。它们将按照声明顺序被调用。

以下是过滤过程的示例：

```php
// 映射一个自定义方法
Flight::map('hello', function (string $name) {
  return "Hello, $name!";
});

// 添加一个 before 过滤器
Flight::before('hello', function (array &$params, string &$output): bool {
  // 操纵参数
  $params[0] = 'Fred';
  return true;
});

// 添加一个 after 过滤器
Flight::after('hello', function (array &$params, string &$output): bool {
  // 操纵输出
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

如果您定义了多个过滤器，您可以通过在任何过滤函数中返回 `false` 来中断链条：

```php
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'one';
  return true;
});

Flight::before('start', function (array &$params, string &$output): bool {
  echo 'two';

  // 这将结束链条
  return false;
});

// 这个不会被调用
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'three';
  return true;
});
```

> **注意：** 核心方法如 `map` 和 `register` 无法被过滤，因为它们被直接调用而非动态调用。请参阅 [Extending Flight](/learn/extending) 以获取更多信息。

## 另请参阅
- [Extending Flight](/learn/extending)

## 故障排除
- 如果您希望链条停止，请确保从过滤函数返回 `false`。如果您不返回任何内容，链条将继续。

## 更新日志
- v2.0 - 初始发布。