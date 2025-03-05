# Flight PHP 中的事件系统 (v3.15.0+)

Flight PHP 引入了一个轻量级且直观的事件系统，让你可以在应用中注册和触发自定义事件。通过添加 `Flight::onEvent()` 和 `Flight::triggerEvent()`，你可以在应用的生命周期的关键时刻挂钩，或定义自己的事件，以使代码更加模块化和可扩展。这些方法是 Flight 的**可映射方法**的一部分，意味着你可以重写它们的行为以满足你的需求。

本指南涵盖了开始使用事件所需的所有知识，包括它们的价值、如何使用它们以及实际示例，帮助初学者理解它们的威力。

## 为什么使用事件？

事件允许你将应用的不同部分分开，以防彼此依赖过重。这种分离——通常称为**解耦**——使得你的代码更容易更新、扩展或调试。你可以将逻辑分割成更小的、独立的部分，以响应特定的操作（事件），而不是将所有内容写在一个大块中。

想象一下你正在构建一个博客应用：
- 当用户发布评论时，你可能想要：
  - 将评论保存到数据库中。
  - 向博客主发送电子邮件。
  - 记录该操作以确保安全。

如果没有事件，你会将所有这一切塞进一个函数中。有了事件，你可以将其拆分：一部分保存评论，另一部分触发一个类似 `'comment.posted'` 的事件，独立的监听器处理电子邮件和日志记录。这保持了代码的整洁，并让你在不触及核心逻辑的情况下添加或移除特性（如通知）。

### 常见用法
- **日志记录**：记录登录或错误等操作，而不混乱主代码。
- **通知**：当某些事情发生时发送电子邮件或警报。
- **更新**：刷新缓存或通知其他系统有关更改。

## 注册事件监听器

要监听一个事件，请使用 `Flight::onEvent()`。此方法允许你定义在事件发生时应该执行的操作。

### 语法
```php
Flight::onEvent(string $event, callable $callback): void
```
- `$event`：事件的名称 (例如：`'user.login'`)。
- `$callback`：当事件被触发时要运行的函数。

### 工作原理
你通过告诉 Flight 事件发生时该做什么来“订阅”一个事件。回调可以接受事件触发时传递的参数。

Flight 的事件系统是同步的，这意味着每个事件监听器按顺序执行，一个接一个。当你触发一个事件时，该事件所有注册的监听器将在代码继续之前运行完成。这一点很重要，因为与异步事件系统不同，异步事件系统的监听器可能会并行运行或在稍后的时间运行。

### 简单示例
```php
Flight::onEvent('user.login', function ($username) {
    echo "欢迎回来，$username!";
});
```
在这里，当触发 `'user.login'` 事件时，它会以用户的名字向其问候。

### 关键点
- 你可以为同一事件添加多个监听器——它们将按照你注册的顺序运行。
- 回调可以是一个函数、一个匿名函数，或者一个类的方法。

## 触发事件

要使事件发生，请使用 `Flight::triggerEvent()`。这告诉 Flight 运行所有为该事件注册的监听器，并传递你提供的任何数据。

### 语法
```php
Flight::triggerEvent(string $event, ...$args): void
```
- `$event`：你要触发的事件名称（必须与已注册的事件匹配）。
- `...$args`：可选参数，以发送给监听器（可以是任何数量的参数）。

### 简单示例
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
这触发了 `'user.login'` 事件，并将 `'alice'` 发送给我们之前定义的监听器，它将输出：`欢迎回来，alice!`。

### 关键点
- 如果没有注册监听器，则什么也不会发生——你的应用不会崩溃。
- 使用扩展运算符 (`...`) 灵活地传递多个参数。

### 注册事件监听器

...

**停止进一步监听器**：
如果监听器返回 `false`，则该事件的后续监听器将不会执行。这允许你基于特定条件停止事件链。请记住，监听器的顺序很重要，因为第一个返回 `false` 的将阻止其余的运行。

**示例**：
```php
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) {
        logoutUser($username);
        return false; // 停止后续监听器
    }
});
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username); // 这不会被发送
});
```

## 重写事件方法

`Flight::onEvent()` 和 `Flight::triggerEvent()` 可以被[扩展](/learn/extending)，这意味着你可以重新定义它们的工作原理。这对于想要自定义事件系统的高级用户很有好处，例如添加日志记录或改变事件的分发方式。

### 示例：自定义 `onEvent`
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // 记录每个事件注册
    error_log("为新事件监听器添加：$event");
    // 调用默认行为（假设有一个内部事件系统）
    Flight::_onEvent($event, $callback);
});
```
现在，每次注册事件时，都会在继续之前进行日志记录。

### 为什么要重写？
- 添加调试或监控。
- 在某些环境中限制事件（例如，在测试时禁用）。
- 与其他事件库集成。

## 将事件放在哪里

作为初学者，你可能会想：*我应该在哪里注册应用中的所有这些事件？* Flight 的简洁性意味着没有严格的规则——你可以将它们放在任何适合你项目的地方。不过，保持它们的组织有助于你在应用增长时维护代码。以下是一些实用的选项和最佳实践，旨在符合 Flight 轻量级的特性：

### 选项 1：在主 `index.php` 中
对于小型应用或快速原型，你可以直接在 `index.php` 文件中注册事件，与路由共存。这使得一切保持在一个地方，对于优先考虑简洁性是可以的。

```php
require 'vendor/autoload.php';

// 注册事件
Flight::onEvent('user.login', function ($username) {
    error_log("$username 于 " . date('Y-m-d H:i:s') . " 登录");
});

// 定义路由
Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "登录成功!";
});

Flight::start();
```
- **优点**：简单，没有额外文件，适合小项目。
- **缺点**：随着应用增加更多事件和路由，可能会变得杂乱。

### 选项 2：独立的 `events.php` 文件
对于稍大的应用，可以考虑将事件注册移动到一个专用文件中，例如 `app/config/events.php`。在 `index.php` 中在路由之前包含此文件。这模仿了 Flight 项目中路由通常组织在 `app/config/routes.php` 的方式。

```php
// app/config/events.php
Flight::onEvent('user.login', function ($username) {
    error_log("$username 于 " . date('Y-m-d H:i:s') . " 登录");
});

Flight::onEvent('user.registered', function ($email, $name) {
    echo "发送邮件给 $email: 欢迎，$name!";
});
```

```php
// index.php
require 'vendor/autoload.php';
require 'app/config/events.php';

Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "登录成功!";
});

Flight::start();
```
- **优点**：保持 `index.php` 专注于路由，逻辑组织事件，便于查找和编辑。
- **缺点**：增加了一点结构，可能对非常小的应用来说显得过于复杂。

### 选项 3：靠近触发的位置
另一种方法是在触发事件的地方注册事件，例如在控制器或路由定义内。如果事件特定于应用的某一部分，这种方法效果很好。

```php
Flight::route('/signup', function () {
    // 在这里注册事件
    Flight::onEvent('user.registered', function ($email) {
        echo "欢迎邮件已发送给 $email!";
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "注册成功!";
});
```
- **优点**：将相关代码保持在一起，适合孤立的特性。
- **缺点**：事件注册分散，可能很难一目了然所有事件；如果不小心容易导致重复注册。

### Flight 的最佳实践
- **从简单开始**：对于小型应用，将事件放在 `index.php` 中。这样快捷且符合 Flight 的极简主义。
- **聪明地扩展**：随着应用的扩大（例如，超过5-10个事件），使用 `app/config/events.php` 文件。这是自然的提升，就像组织路由一样，可以让你的代码整洁而不增加复杂的框架。
- **避免过度设计**：除非你的应用规模很大，否则不要创建完整的“事件管理器”类或目录——Flight 追求简洁，保持轻量化。

### 提示：按目的分组
在 `events.php` 中，以注释分组相关事件（例如，把所有用户相关事件放在一起）以保持清晰度：

```php
// app/config/events.php
// 用户事件
Flight::onEvent('user.login', function ($username) {
    error_log("$username 登录");
});
Flight::onEvent('user.registered', function ($email) {
    echo "欢迎到 $email!";
});

// 页面事件
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]);
});
```

这种结构可扩展且对初学者友好。

## 初学者示例

让我们通过一些真实场景来演示事件的工作原理以及为什么它们有用。

### 示例 1：记录用户登录
```php
// 步骤 1：注册监听器
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username 于 $time 登录");
});

// 步骤 2：在应用中触发它
Flight::route('/login', function () {
    $username = 'bob'; // 假设这是来自表单
    Flight::triggerEvent('user.login', $username);
    echo "嗨，$username!";
});
```
**为什么这有用**：登录代码不需要知道日志记录的事情——它只是触发事件。你可以稍后添加更多监听器（例如，发送欢迎邮件），而无需更改路由。

### 示例 2：通知新用户
```php
// 注册新注册的监听器
Flight::onEvent('user.registered', function ($email, $name) {
    // 模拟发送电子邮件
    echo "发送邮件给 $email: 欢迎，$name!";
});

// 在有人注册时触发它
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "感谢注册!";
});
```
**为什么这有用**：注册逻辑专注于创建用户，而事件处理通知。你可以稍后添加更多监听器（例如，记录注册）而不需要更改逻辑。

### 示例 3：清除缓存
```php
// 清除缓存的监听器
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]); // 如果适用，清除会话缓存
    echo "已清除页面 $pageId 的缓存。";
});

// 当页面被编辑时触发
Flight::route('/edit-page/(@id)', function ($pageId) {
    // 假装我们更新了页面
    Flight::triggerEvent('page.updated', $pageId);
    echo "页面 $pageId 已更新。";
});
```
**为什么这有用**：编辑代码不需要关心缓存的事情——它只是发出更新的信号。应用的其他部分可以根据需要做出反应。

## 最佳实践

- **清晰命名事件**：使用特定名称，如 `'user.login'` 或 `'page.updated'`，这样显而易见它们的作用。
- **保持监听器简单**：不要在监听器中放入缓慢或复杂的任务——保持应用的快速。
- **测试你的事件**：手动触发事件以确保监听器按预期工作。
- **明智使用事件**：它们非常适合解耦，但过多可能使代码难以跟随——在合适的时候使用它们。

Flight PHP 中的事件系统以及 `Flight::onEvent()` 和 `Flight::triggerEvent()` 为你提供了一种简单而强大的方式来构建灵活的应用。通过让应用的不同部分通过事件进行通信，你可以保持代码组织良好、可重用且易于扩展。无论是记录操作、发送通知还是管理更新，事件都能帮助你做到这一点，而不让你的逻辑纠缠在一起。而且，能够重写这些方法使你有自由度来根据需要调整系统。从单个事件开始，看看它如何改变你应用的结构！

## 内置事件

Flight PHP 还附带了一些内置事件，你可以利用这些事件来挂钩到框架的生命周期。这些事件在请求/响应周期的特定时点触发，允许你在某些操作发生时执行自定义逻辑。

### 内置事件列表
- `flight.request.received`：当请求被接收、解析和处理时触发。
- `flight.route.middleware.before`：在执行前中间件后触发。
- `flight.route.middleware.after`：在执行后中间件后触发。
- `flight.route.executed`：在路由被执行和处理后触发。
- `flight.response.sent`：在响应被发送到客户端后触发。