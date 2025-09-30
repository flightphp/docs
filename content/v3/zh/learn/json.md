# JSON 包装器

## 概述

Flight 中的 `Json` 类提供了一种简单、一致的方式来编码和解码应用程序中的 JSON 数据。它封装了 PHP 的原生 JSON 函数，具有更好的错误处理和一些有用的默认设置，使处理 JSON 变得更容易和更安全。

## 理解

在现代 PHP 应用程序中处理 JSON 非常常见，尤其是在构建 API 或处理 AJAX 请求时。`Json` 类集中处理所有 JSON 编码和解码，因此您无需担心 PHP 内置函数的奇怪边缘情况或神秘错误。

关键特性：
- 一致的错误处理（失败时抛出异常）
- 编码/解码的默认选项（例如未转义的斜杠）
- 用于美化打印和验证的实用方法

## 基本用法

### 将数据编码为 JSON

要将 PHP 数据转换为 JSON 字符串，请使用 `Json::encode()`：

```php
use flight\util\Json;

$data = [
  'framework' => 'Flight',
  'version' => 3,
  'features' => ['routing', 'views', 'extending']
];

$json = Json::encode($data);
echo $json;
// 输出: {"framework":"Flight","version":3,"features":["routing","views","extending"]}
```

如果编码失败，您将收到一个带有有帮助错误消息的异常。

### 美化打印

想要让您的 JSON 易于阅读？使用 `prettyPrint()`：

```php
echo Json::prettyPrint($data);
/*
{
  "framework": "Flight",
  "version": 3,
  "features": [
    "routing",
    "views",
    "extending"
  ]
}
*/
```

### 解码 JSON 字符串

要将 JSON 字符串转换回 PHP 数据，请使用 `Json::decode()`：

```php
$json = '{"framework":"Flight","version":3}';
$data = Json::decode($json);
echo $data->framework; // 输出: Flight
```

如果您想要关联数组而不是对象，请将 `true` 作为第二个参数传递：

```php
$data = Json::decode($json, true);
echo $data['framework']; // 输出: Flight
```

如果解码失败，您将收到一个带有清晰错误消息的异常。

### 验证 JSON

检查字符串是否为有效的 JSON：

```php
if (Json::isValid($json)) {
  // 它是有效的！
} else {
  // 不是有效的 JSON
}
```

### 获取最后错误

如果您想要检查最后 JSON 错误消息（来自原生 PHP 函数）：

```php
$error = Json::getLastError();
if ($error !== '') {
  echo "最后 JSON 错误: $error";
}
```

## 高级用法

如果您需要更多控制，可以自定义编码和解码选项（参见 [PHP 的 json_encode 选项](https://www.php.net/manual/en/json.constants.php)）：

```php
// 使用 HEX_TAG 选项编码
$json = Json::encode($data, JSON_HEX_TAG);

// 使用自定义深度解码
$data = Json::decode($json, false, 1024);
```

## 另请参阅

- [Collections](/learn/collections) - 用于处理可以轻松转换为 JSON 的结构化数据。
- [Configuration](/learn/configuration) - 如何配置您的 Flight 应用程序。
- [Extending](/learn/extending) - 如何添加您自己的实用工具或覆盖核心类。

## 故障排除

- 如果编码或解码失败，将抛出异常——如果您想优雅地处理错误，请将您的调用包装在 try/catch 中。
- 如果您得到意外结果，请检查您的数据是否包含循环引用或非 UTF-8 字符。
- 在解码之前，使用 `Json::isValid()` 检查字符串是否为有效的 JSON。

## 更新日志

- v3.16.0 - 添加了 JSON 包装器实用类。