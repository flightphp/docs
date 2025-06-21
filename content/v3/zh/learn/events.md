# Flight 中的事件系统（v3.15.0+）

Flight PHP 引入了一个轻量级且直观的事件系统，让您能够在应用程序中注册和触发自定义事件。通过添加 `Flight::onEvent()` 和 `Flight::triggerEvent()`，您现在可以挂钩到应用程序生命周期的关键时刻，或者定义自己的事件，从而使代码更具模块化和可扩展性。这些方法是 Flight 的 **mappable methods**，意味着您可以覆盖它们的行为以满足您的需求。

本指南涵盖了您需要了解的一切，包括事件的价值、如何使用它们，以及一些实用示例，以帮助初学者理解它们的力量。

## 为什么使用事件？

事件允许您将应用程序的不同部分分离，从而减少它们之间的强依赖。这种分离——通常称为 **decoupling**（解耦）——使您的代码更容易更新、扩展或调试。Instead of writing everything in one big chunk, you can split your logic into smaller, independent pieces that respond to specific actions (events)。

想象一下您正在构建一个博客应用程序：
- 当用户发布评论时，您可能想要：
  - 将评论保存到数据库。
  - 发送电子邮件给博客所有者。
  - 为安全记录该操作。

Without events, you’d cram all this into one function. With events, you can split it up: one part saves the comment, another triggers an event like `'comment.posted'`, and separate listeners handle the email and logging. This keeps your code cleaner and lets you add or remove features (like notifications) without touching the core logic.

### 常见用途
- **Logging**（日志记录）：记录像登录或错误这样的操作，而不 cluttering your main code。
- **Notifications**（通知）：当某些事情发生时发送电子邮件或警报。
- **Updates**（更新）：刷新缓存或通知其他系统关于变化。

## 注册事件监听器

要监听事件，请使用 `Flight::onEvent()`。此方法允许您定义事件发生时应该做什么。

### 语法
```php
// 语法
Flight::onEvent(string $event, callable $callback): void
```
- `$event`：事件名称（例如，`'user.login'`）。
- `$callback`：事件触发时运行的函数。

### 如何工作
您通过告诉 Flight 事件发生时要做什么来“订阅”事件。回调可以接受从事件触发器传递的参数。

Flight's event system is synchronous, which means that each event listener is executed in sequence, one after another. When you trigger an event, all registered listeners for that event will run to completion before your code continues. This is important to understand as it differs from asynchronous event systems where listeners might run in parallel or at a later time.

### 简单示例
```php
// 这里，当 'user.login' 事件被触发时，它会问候用户
Flight::onEvent('user.login', function ($username) {
    echo "Welcome back, $username!";
});
```

### 关键点
- 您可以为同一事件添加多个监听器——它们将按照您注册的顺序运行。
- 回调可以是函数、匿名函数或类的方法。

## 触发事件

要使事件发生，请使用 `Flight::triggerEvent()`。这会告诉 Flight 运行为该事件注册的所有监听器，并传递您提供的任何数据。

### 语法
```php
// 语法
Flight::triggerEvent(string $event, ...$args): void
```
- `$event`：您要触发的事件名称（必须匹配已注册的事件）。
- `...$args`：可选参数，发送给监听器（可以是任意数量的参数）。

### 简单示例
```php
$username = 'alice';
// 这会触发 'user.login' 事件，并将 'alice' 发送给之前定义的监听器，它会输出：Welcome back, alice!
Flight::triggerEvent('user.login', $username);
```

### 关键点
- 如果没有注册监听器，什么都不会发生——您的应用程序不会崩溃。
- 使用 spread operator (`...`) 来灵活传递多个参数。

### 注册事件监听器

...

**Stopping Further Listeners**（停止后续监听器）：
如果一个监听器返回 `false`，则该事件的其他监听器不会被执行。这允许您基于特定条件停止事件链。请记住，监听器的顺序很重要，因为第一个返回 `false` 的监听器会阻止其余监听器运行。

**示例**：
```php
// 如果用户被封禁，则注销用户并停止后续监听器
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) {
        logoutUser($username);
        return false; // 停止后续监听器
    }
});
// 这个监听器永远不会被调用
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username); // 这个是永不发送的
});
```

## 覆盖事件方法

`Flight::onEvent()` 和 `Flight::triggerEvent()` 可以被 [extended](/learn/extending)，意思是您可以重新定义它们的工作方式。这对于高级用户来说很棒，他们想要自定义事件系统，比如添加日志或更改事件分发方式。

### 示例：自定义 `onEvent`
```php
// 自定义 onEvent 方法
Flight::map('onEvent', function (string $event, callable $callback) {
    // 记录每个事件注册
    error_log("New event listener added for: $event");
    // 调用默认行为（假设有一个内部事件系统）
    Flight::_onEvent($event, $callback);
});
```
Now, every time you register an event, it logs it before proceeding.

### 为什么覆盖？
- Add debugging or monitoring（添加调试或监控）。
- Restrict events in certain environments（在某些环境中限制事件，例如在测试中禁用）。
- Integrate with a different event library（与其他事件库集成）。

## 放置事件的位置

作为初学者，您可能想知道：在应用程序中，我在哪里注册所有这些事件？Flight 的简单性意味着没有严格规则——您可以将它们放在任何适合项目的地方。但是，保持它们组织良好有助于在应用程序增长时维护代码。下面是一些实用选项和最佳实践，针对 Flight 的轻量级特性量身定制：

### 选项 1：In Your Main `index.php`
For small apps or quick prototypes, you can register events right in your `index.php` file alongside your routes. This keeps everything in one place, which is fine when simplicity is your priority.

```php
// 注册事件
require 'vendor/autoload.php';

// 注册事件
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));  // 记录登录时间
});

// 定义路由
Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Logged in!";
});

Flight::start();
```
- **Pros**（优点）：Simple, no extra files, great for small projects.
- **Cons**（缺点）：Can get messy as your app grows with more events and routes.

### 选项 2：A Separate `events.php` File
For a slightly larger app, consider moving event registrations into a dedicated file like `app/config/events.php`. Include this file in your `index.php` before your routes. This mimics how routes are often organized in `app/config/routes.php` in Flight projects.

```php
// app/config/events.php
// 用户事件
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));
});

Flight::onEvent('user.registered', function ($email, $name) {
    echo "Email sent to $email: Welcome, $name!";
});
```

```php
// index.php
require 'vendor/autoload.php';
require 'app/config/events.php';

Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Logged in!";
});

Flight::start();
```
- **Pros**（优点）：Keeps `index.php` focused on routing, organizes events logically, easy to find and edit.
- **Cons**（缺点）：Adds a tiny bit of structure, which might feel like overkill for very small apps.

### 选项 3：Near Where They’re Triggered
Another approach is to register events close to where they’re triggered, like inside a controller or route definition. This works well if an event is specific to one part of your app.

```php
Flight::route('/signup', function () {
    // 在这里注册事件
    Flight::onEvent('user.registered', function ($email) {
        echo "Welcome email sent to $email!";
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "Signed up!";
});
```
- **Pros**（优点）：Keeps related code together, good for isolated features.
- **Cons**（缺点）：Scatters event registrations, making it harder to see all events at once; risks duplicate registrations if not careful.

### Flight 的最佳实践
- **Start Simple**（从简单开始）：For tiny apps, put events in `index.php`。It’s quick and aligns with Flight’s minimalism.
- **Grow Smart**（聪明增长）：As your app expands (e.g., more than 5-10 events), use an `app/config/events.php` file. It’s a natural step up, like organizing routes, and keeps your code tidy without adding complex frameworks.
- **Avoid Over-Engineering**（避免过度工程化）：Don’t create a full-blown “event manager” class or directory unless your app gets huge—Flight thrives on simplicity, so keep it lightweight.

### 提示：Group by Purpose
In `events.php`, group related events (e.g., all user-related events together) with comments for clarity:

```php
// app/config/events.php
// 用户事件
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in");  // 记录登录
});
Flight::onEvent('user.registered', function ($email) {
    echo "Welcome to $email!";  // 发送欢迎消息
});

// 页面事件
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]);  // 清除会话缓存
});
```

This structure scales well and stays beginner-friendly.

## 初学者的示例

让我们通过一些真实场景来展示事件如何工作以及为什么它们有用。

### 示例 1：记录用户登录
```php
// 步骤 1：注册监听器
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');  // 获取当前时间
    error_log("$username logged in at $time");
});

// 步骤 2：在应用程序中触发它
Flight::route('/login', function () {
    $username = 'bob';  // 假装这是从表单中获取的
    Flight::triggerEvent('user.login', $username);
    echo "Hi, $username!";
});
```
**Why It’s Useful**（为什么有用）：登录代码不需要知道日志记录——它只需触发事件。您可以稍后添加更多监听器（例如，发送欢迎电子邮件）而无需更改路由。

### 示例 2：通知新用户
```php
// 监听器用于新注册
Flight::onEvent('user.registered', function ($email, $name) {
    // 模拟发送电子邮件
    echo "Email sent to $email: Welcome, $name!";
});

// 在有人注册时触发它
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Thanks for signing up!";
});
```
**Why It’s Useful**（为什么有用）：注册逻辑专注于创建用户，而事件处理通知。您可以稍后添加更多监听器（例如，记录注册）。

### 示例 3：清除缓存
```php
// 监听器用于清除缓存
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]);  // 清除会话缓存如果适用
    echo "Cache cleared for page $pageId.";
});

// 在编辑页面时触发
Flight::route('/edit-page/(@id)', function ($pageId) {
    // 假装我们更新了页面
    Flight::triggerEvent('page.updated', $pageId);
    echo "Page $pageId updated.";
});
```
**Why It’s Useful**（为什么有用）：编辑代码不关心缓存——它只需发出更新信号。应用程序的其他部分可以根据需要做出反应。

## 最佳实践

- **Name Events Clearly**（清晰命名事件）：使用像 `'user.login'` 或 `'page.updated'` 这样的具体名称，以便一目了然。
- **Keep Listeners Simple**（保持监听器简单）：不要在监听器中放置缓慢或复杂的任务——保持应用程序快速。
- **Test Your Events**（测试您的事件）：手动触发它们以确保监听器按预期工作。
- **Use Events Wisely**（明智使用事件）：它们非常适合解耦，但如果太多可能会使代码难以跟踪——仅在合适时使用。

Flight PHP 中的事件系统，通过 `Flight::onEvent()` 和 `Flight::triggerEvent()`，为您提供了一种简单却强大的方式来构建灵活的应用程序。通过让应用程序的不同部分通过事件相互通信，您可以保持代码组织良好、可重用且易于扩展。无论您是在记录操作、发送通知还是管理更新，事件都能帮助您做到这一点而不 tangled your logic。而且，由于能够覆盖这些方法，您有自由来定制系统。From a single event, and watch how it transforms your app’s structure!

## 内置事件

Flight PHP 附带了一些内置事件，您可以使用它们来挂钩框架的生命周期。这些事件在请求/响应周期的特定点被触发，允许您在某些操作发生时执行自定义逻辑。

### 内置事件列表
- **flight.request.received**： `function(Request $request)` 在请求被接收、解析和处理时触发。
- **flight.error**： `function(Throwable $exception)` 在请求生命周期中发生错误时触发。
- **flight.redirect**： `function(string $url, int $status_code)` 在启动重定向时触发。
- **flight.cache.checked**： `function(string $cache_key, bool $hit, float $executionTime)` 在为特定键检查缓存时触发，以及缓存命中或未命中。
- **flight.middleware.before**： `function(Route $route)` 在执行 before middleware 后触发。
- **flight.middleware.after**： `function(Route $route)` 在执行 after middleware 后触发。
- **flight.middleware.executed**： `function(Route $route, $middleware, string $method, float $executionTime)` 在任何 middleware 执行后触发。
- **flight.route.matched**： `function(Route $route)` 在路由匹配但尚未运行时触发。
- **flight.route.executed**： `function(Route $route, float $executionTime)` 在路由执行和处理后触发。`$executionTime` 是执行路由（调用控制器等）所花费的时间。
- **flight.view.rendered**： `function(string $template_file_path, float $executionTime)` 在渲染视图后触发。`$executionTime` 是渲染模板所花费的时间。**注意：如果您覆盖了 `render` 方法，您需要重新触发此事件。**
- **flight.response.sent**： `function(Response $response, float $executionTime)` 在将响应发送给客户端后触发。`$executionTime` 是构建响应所花费的时间。