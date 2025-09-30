# 集合

## 概述

Flight 中的 `Collection` 类是一个方便的实用工具，用于管理数据集合。它允许您使用数组和对象表示法来访问和操作数据，使您的代码更简洁和灵活。

## 理解

`Collection` 基本上是一个数组的包装器，但具有一些额外的功能。您可以像数组一样使用它，遍历它，计算其项数，甚至将项访问为对象属性。这在您希望在应用程序中传递结构化数据，或使您的代码更易读时特别有用。

Collection 实现了几个 PHP 接口：
- `ArrayAccess`（因此您可以使用数组语法）
- `Iterator`（因此您可以使用 `foreach` 循环）
- `Countable`（因此您可以使用 `count()`）
- `JsonSerializable`（因此您可以轻松转换为 JSON）

## 基本用法

### 创建 Collection

您可以通过将数组传递给其构造函数来创建集合：

```php
use flight\util\Collection;

$data = [
  'name' => 'Flight',
  'version' => 3,
  'features' => ['routing', 'views', 'extending']
];

$collection = new Collection($data);
```

### 访问项

您可以使用数组或对象表示法访问项：

```php
// 数组表示法
echo $collection['name']; // 输出: Flight

// 对象表示法
echo $collection->version; // 输出: 3
```

如果您尝试访问不存在的键，您将得到 `null` 而不是错误。

### 设置项

您也可以使用任一表示法设置项：

```php
// 数组表示法
$collection['author'] = 'Mike Cao';

// 对象表示法
$collection->license = 'MIT';
```

### 检查和移除项

检查项是否存在：

```php
if (isset($collection['name'])) {
  // 执行某些操作
}

if (isset($collection->version)) {
  // 执行某些操作
}
```

移除项：

```php
unset($collection['author']);
unset($collection->license);
```

### 遍历 Collection

Collection 是可迭代的，因此您可以在 `foreach` 循环中使用它们：

```php
foreach ($collection as $key => $value) {
  echo "$key: $value\n";
}
```

### 计算项数

您可以计算集合中的项数：

```php
echo count($collection); // 输出: 4
```

### 获取所有键或数据

获取所有键：

```php
$keys = $collection->keys(); // ['name', 'version', 'features', 'license']
```

获取所有数据作为数组：

```php
$data = $collection->getData();
```

### 清空 Collection

移除所有项：

```php
$collection->clear();
```

### JSON 序列化

Collection 可以轻松转换为 JSON：

```php
echo json_encode($collection);
// 输出: {"name":"Flight","version":3,"features":["routing","views","extending"],"license":"MIT"}
```

## 高级用法

如果需要，您可以完全替换内部数据数组：

```php
$collection->setData(['foo' => 'bar']);
```

Collection 在您希望在组件之间传递结构化数据，或为数组数据提供更面向对象的接口时特别有用。

## 另请参阅

- [Requests](/learn/requests) - 学习如何处理 HTTP 请求以及如何使用集合来管理请求数据。
- [PDO Wrapper](/learn/pdo-wrapper) - 学习如何在 Flight 中使用 PDO 包装器以及如何使用集合来管理数据库结果。

## 故障排除

- 如果您尝试访问不存在的键，您将得到 `null` 而不是错误。
- 请记住，集合不是递归的：嵌套数组不会自动转换为集合。
- 如果您需要重置集合，请使用 `$collection->clear()` 或 `$collection->setData([])`。

## 更新日志

- v3.0 - 改进了类型提示并支持 PHP 8+。
- v1.0 - Collection 类的初始发布。