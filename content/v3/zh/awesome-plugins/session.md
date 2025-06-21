# FlightPHP 会话 - 轻量级基于文件的会话处理程序

这是一个轻量级、基于文件的会话处理程序插件，用于 [Flight PHP Framework](https://docs.flightphp.com/)。它提供了一个简单而强大的解决方案，用于管理会话，包括非阻塞会话读取、可选加密、自动提交功能以及开发测试模式。会话数据存储在文件中，非常适合不需要数据库的应用。

如果您想使用数据库，请查看 [ghostff/session](/awesome-plugins/ghost-session) 插件，它具有许多相同的功能，但使用数据库后端。

访问 [Github 仓库](https://github.com/flightphp/session) 以获取完整源代码和详细信息。

## 安装

通过 Composer 安装插件：

```bash
composer require flightphp/session
```

## 基本用法

以下是一个在 Flight 应用中使用 `flightphp/session` 插件的简单示例：

```php
require 'vendor/autoload.php';

use flight\Session;

$app = Flight::app(); // 注册会话服务
$app->register('session', Session::class);

// 示例路由，使用会话
Flight::route('/login', function() {
    $session = Flight::session();
    $session->set('user_id', 123);
    $session->set('username', 'johndoe');
    $session->set('is_admin', false);

    echo $session->get('username'); // 输出: johndoe
    echo $session->get('preferences', 'default_theme'); // 输出: default_theme

    if ($session->get('user_id')) {
        Flight::json(['message' => '用户已登录！', 'user_id' => $session->get('user_id')]);
    }
});

Flight::route('/logout', function() {
    $session = Flight::session();
    $session->clear(); // 清除所有会话数据
    Flight::json(['message' => '成功登出']);
});

Flight::start();
```

### 关键点
- **非阻塞**：默认使用 `read_and_close` 启动会话，防止会话锁定问题。
- **自动提交**：默认启用，因此更改会在关闭时自动保存，除非禁用。
- **文件存储**：会话默认存储在系统临时目录下的 `/flight_sessions`。

## 配置

在注册时，通过传递一个选项数组来自定义会话处理程序：

```php
// 是的，这是一个双数组 :)
$app->register('session', Session::class, [ [
    'save_path' => '/custom/path/to/sessions',         // 会话文件的目录
	'prefix' => 'myapp_',                              // 会话文件的前缀
    'encryption_key' => 'a-secure-32-byte-key-here',   // 启用加密（推荐使用 32 字节用于 AES-256-CBC）
    'auto_commit' => false,                            // 禁用自动提交以手动控制
    'start_session' => true,                           // 自动启动会话（默认: true）
    'test_mode' => false,                              // 启用测试模式用于开发
    'serialization' => 'json',                         // 序列化方法: 'json'（默认）或 'php'（旧版）
] ]);
```

### 配置选项
| 选项            | 描述                                      | 默认值                     |
|-------------------|--------------------------------------------------|-----------------------------------|
| `save_path`       | 会话文件存储的目录         | `sys_get_temp_dir() . '/flight_sessions'` |
| `prefix`          | 保存会话文件的文件前缀                | `sess_`                           |
| `encryption_key`  | 用于 AES-256-CBC 加密的密钥（可选）        | `null` (无加密)            |
| `auto_commit`     | 在关闭时自动保存会话数据               | `true`                            |
| `start_session`   | 自动启动会话                  | `true`                            |
| `test_mode`       | 在测试模式下运行而不影响 PHP 会话  | `false`                           |
| `test_session_id` | 测试模式的自定义会话 ID（可选）       | 如果未设置，则随机生成     |
| `serialization`   | 序列化方法: 'json'（默认，安全）或 'php'（旧版，允许对象） | `'json'` |

## 序列化模式

默认情况下，此库使用 **JSON 序列化** 来处理会话数据，这很安全，可以防止 PHP 对象注入漏洞。如果您需要存储 PHP 对象（不推荐用于大多数应用），您可以选择使用旧版 PHP 序列化：

- `'serialization' => 'json'` (默认):
  - 只允许会话数据中包含数组和基本类型。
  - 更安全：免疫 PHP 对象注入。
  - 文件以 `J`（纯 JSON）或 `F`（加密 JSON）开头。
- `'serialization' => 'php'`:
  - 允许存储 PHP 对象（请谨慎使用）。
  - 文件以 `P`（纯 PHP 序列化）或 `E`（加密 PHP 序列化）开头。

**注意：** 如果使用 JSON 序列化，尝试存储对象会引发异常。

## 高级用法

### 手动提交
如果禁用自动提交，您必须手动提交更改：

```php
$app->register('session', Session::class, ['auto_commit' => false]);

Flight::route('/update', function() {
    $session = Flight::session();
    $session->set('key', 'value');
    $session->commit(); // 显式保存更改
});
```

### 使用加密的会话安全
为敏感数据启用加密：

```php
$app->register('session', Session::class, [
    'encryption_key' => 'your-32-byte-secret-key-here'
]);

Flight::route('/secure', function() {
    $session = Flight::session();
    $session->set('credit_card', '4111-1111-1111-1111'); // 自动加密
    echo $session->get('credit_card'); // 检索时解密
});
```

### 会话再生
为安全起见（例如，登录后），再生会话 ID：

```php
Flight::route('/post-login', function() {
    $session = Flight::session();
    $session->regenerate(); // 新 ID，保留数据
    // 或
    $session->regenerate(true); // 新 ID，删除旧数据
});
```

### 中间件示例
使用基于会话的身份验证保护路由：

```php
Flight::route('/admin', function() {
    Flight::json(['message' => '欢迎来到管理面板']);
})->addMiddleware(function() {
    $session = Flight::session();
    if (!$session->get('is_admin')) {
        Flight::halt(403, '访问被拒绝');
    }
});
```

这是一个在中间件中使用它的简单示例。有关更深入的示例，请参阅 [middleware](/learn/middleware) 文档。

## 方法

`Session` 类提供以下方法：

- `set(string $key, $value)`: 在会话中存储一个值。
- `get(string $key, $default = null)`: 检索一个值，如果键不存在，则使用可选默认值。
- `delete(string $key)`: 从会话中删除特定键。
- `clear()`: 删除所有会话数据，但保留相同的会话文件名。
- `commit()`: 将当前会话数据保存到文件系统。
- `id()`: 返回当前会话 ID。
- `regenerate(bool $deleteOldFile = false)`: 再生会话 ID，包括创建新会话文件，保留所有旧数据，旧文件保留在系统中。如果 `$deleteOldFile` 为 `true`，则删除旧会话文件。
- `destroy(string $id)`: 通过 ID 销毁会话并从系统中删除会话文件。这是 `SessionHandlerInterface` 的一部分，`$id` 是必需的。典型用法为 `$session->destroy($session->id())`。
- `getAll()` : 返回当前会话的所有数据。

除 `get()` 和 `id()` 外的所有方法都返回 `Session` 实例以支持链式调用。

## 为什么使用此插件？

- **轻量级**：无需外部依赖——只需文件。
- **非阻塞**：默认使用 `read_and_close` 避免会话锁定。
- **安全**：支持 AES-256-CBC 加密用于敏感数据。
- **灵活**：提供自动提交、测试模式和手动控制选项。
- **Flight 专用**：专为 Flight 框架构建。

## 技术细节

- **存储格式**：会话文件以 `sess_` 为前缀，存储在配置的 `save_path` 中。文件内容前缀：
  - `J`：纯 JSON（默认，无加密）
  - `F`：加密 JSON（默认使用加密）
  - `P`：纯 PHP 序列化（旧版，无加密）
  - `E`：加密 PHP 序列化（旧版使用加密）
- **加密**：当提供 `encryption_key` 时，使用 AES-256-CBC 加密，每次会话写入时使用随机 IV。加密适用于 JSON 和 PHP 序列化模式。
- **序列化**：JSON 是默认且最安全的方法。PHP 序列化适用于旧版/高级使用，但安全性较低。
- **垃圾回收**：实现 PHP 的 `SessionHandlerInterface::gc()` 以清理过期的会话。

## 贡献

欢迎贡献！分叉 [仓库](https://github.com/flightphp/session)，进行更改，然后提交拉取请求。通过 Github 问题跟踪器报告错误或建议功能。

## 许可证

此插件采用 MIT 许可证。有关详细信息，请参阅 [Github 仓库](https://github.com/flightphp/session)。