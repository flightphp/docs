# FlightPHP 会话 - 轻量级文件基础会话处理器

这是一个轻量级的文件基础会话处理器插件，适用于 [Flight PHP Framework](https://docs.flightphp.com/)。它提供了一种简单而强大的会话管理解决方案，具有非阻塞会话读取、可选加密、自动提交功能和开发测试模式等特性。会话数据存储在文件中，适合不需要数据库的应用程序。

如果您确实想使用数据库，可以查看 [ghostff/session](/awesome-plugins/ghost-session) 插件，它具有许多相同的功能，但使用数据库后端。

访问 [Github 仓库](https://github.com/flightphp/session) 获取完整源代码和详细信息。

## 安装

通过 Composer 安装插件：

```bash
composer require flightphp/session
```

## 基本用法

以下是如何在您的 Flight 应用程序中使用 `flightphp/session` 插件的简单示例：

```php
require 'vendor/autoload.php';

use flight\Session;

$app = Flight::app();

// 注册会话服务
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
- **自动提交**：默认启用，因此在关闭时更改会自动保存，除非禁用。
- **文件存储**：会话默认存储在系统临时目录下的 `/flight_sessions` 中。

## 配置

您可以在注册时传递选项数组来自定义会话处理器：

```php
$app->register('session', Session::class, [
    'save_path' => '/custom/path/to/sessions',         // 会话文件目录
    'encryption_key' => 'a-secure-32-byte-key-here',   // 启用加密（建议 AES-256-CBC 的 32 字节密钥）
    'auto_commit' => false,                            // 禁用自动提交以进行手动控制
    'start_session' => true,                           // 自动启动会话（默认：true）
    'test_mode' => false                               // 启用开发测试模式
]);
```

### 配置选项
| 选项              | 描述                                           | 默认值                          |
|-------------------|-----------------------------------------------|---------------------------------|
| `save_path`       | 存储会话文件的目录                          | `sys_get_temp_dir() . '/flight_sessions'` |
| `encryption_key`  | AES-256-CBC 加密的密钥（可选）               | `null`（不加密）               |
| `auto_commit`     | 在关闭时自动保存会话数据                     | `true`                          |
| `start_session`   | 自动启动会话                                  | `true`                          |
| `test_mode`       | 在不影响 PHP 会话的情况下以测试模式运行      | `false`                         |
| `test_session_id` | 测试模式的自定义会话 ID（可选）              | 如果未设置则随机生成           |

## 高级用法

### 手动提交
如果您禁用自动提交，您必须手动提交更改：

```php
$app->register('session', Session::class, ['auto_commit' => false]);

Flight::route('/update', function() {
    $session = Flight::session();
    $session->set('key', 'value');
    $session->commit(); // 显式保存更改
});
```

### 使用加密的会话安全性
为敏感数据启用加密：

```php
$app->register('session', Session::class, [
    'encryption_key' => 'your-32-byte-secret-key-here'
]);

Flight::route('/secure', function() {
    $session = Flight::session();
    $session->set('credit_card', '4111-1111-1111-1111'); // 自动加密
    echo $session->get('credit_card'); // 在提取时解密
});
```

### 会话再生
为了安全性（例如，在登录后）再生会话 ID：

```php
Flight::route('/post-login', function() {
    $session = Flight::session();
    $session->regenerate(); // 新 ID，保留数据
    // 或者
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

这只是如何在中间件中使用此功能的一个简单示例。有关更深入的示例，请参阅 [中间件](/learn/middleware) 文档。

## 方法

`Session` 类提供以下方法：

- `set(string $key, $value)`：将值存储在会话中。
- `get(string $key, $default = null)`：检索值，如果键不存在，则可选默认值。
- `delete(string $key)`：从会话中移除特定键。
- `clear()`：删除所有会话数据。
- `commit()`：将当前会话数据保存到文件系统。
- `id()`：返回当前会话 ID。
- `regenerate(bool $deleteOld = false)`：再生会话 ID，可选择删除旧数据。

除了 `get()` 和 `id()` 之外，所有方法都返回 `Session` 实例以便于链接调用。

## 为什么使用这个插件？

- **轻量级**：没有外部依赖——仅仅是文件。
- **非阻塞**：默认使用 `read_and_close` 避免会话锁定。
- **安全**：支持 AES-256-CBC 加密敏感数据。
- **灵活**：提供自动提交、测试模式和手动控制选项。
- **Flight 原生**：专门为 Flight 框架构建。

## 技术细节

- **存储格式**：会话文件以 `sess_` 前缀开头，存储在配置的 `save_path` 中。加密数据使用 `E` 前缀，明文使用 `P`。
- **加密**：在提供 `encryption_key` 时，使用 AES-256-CBC，且每次会话写入时采用随机 IV。
- **垃圾收集**：实现 PHP 的 `SessionHandlerInterface::gc()` 以清理过期的会话。

## 贡献

欢迎贡献！叉出 [仓库](https://github.com/flightphp/session)，进行更改，并提交拉取请求。通过 Github 问题跟踪器报告错误或提出功能建议。

## 许可证

这个插件遵循 MIT 许可证。详情请参阅 [Github 仓库](https://github.com/flightphp/session) 。