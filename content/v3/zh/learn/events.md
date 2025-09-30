# 事件管理器

_自 v3.15.0 起_

## 概述

事件允许您在应用程序中注册和触发自定义行为。通过添加 `Flight::onEvent()` 和 `Flight::triggerEvent()`，您现在可以钩入应用程序生命周期的关键时刻，或者定义自己的事件（例如通知和电子邮件），使您的代码更模块化和可扩展。这些方法是 Flight 的 [mappable methods](/learn/extending) 的一部分，这意味着您可以根据需要覆盖它们的行为。

## 理解

事件允许您将应用程序的不同部分分离，从而避免它们过于依赖彼此。这种分离——通常称为**解耦**——使您的代码更容易更新、扩展或调试。与将所有内容写成一个大块不同，您可以将逻辑拆分为更小、更独立的片段，这些片段响应特定操作（事件）。

想象您正在构建一个博客应用程序：
- 当用户发布评论时，您可能想要：
  - 将评论保存到数据库。
  - 向博客所有者发送电子邮件。
  - 为安全记录操作。

没有事件，您会将所有这些塞到一个函数中。有了事件，您可以将其拆分：一部分保存评论，另一部分触发像 `'comment.posted'` 这样的事件，单独的监听器处理电子邮件和日志记录。这使您的代码更干净，并允许您添加或移除功能（例如通知），而无需触及核心逻辑。

### 常见用例

大多数情况下，事件适用于可选但不是系统绝对核心的部分。例如，以下是好的但如果因某种原因失败，您的应用程序仍应正常工作：

- **日志记录**：记录像登录或错误这样的操作，而不杂乱主代码。
- **通知**：当某事发生时发送电子邮件或警报。
- **缓存更新**：刷新缓存或通知其他系统关于更改。

然而，假设您有一个忘记密码功能。那应该是您核心功能的一部分，而不是事件，因为如果那封电子邮件没有发送出去，用户就无法重置密码并使用您的应用程序。

## 基本用法

Flight 的事件系统围绕两个主要方法构建：`Flight::onEvent()` 用于注册事件监听器和 `Flight::triggerEvent()` 用于触发事件。以下是您如何使用它们：

### 注册事件监听器

要监听事件，请使用 `Flight::onEvent()`。此方法允许您定义事件发生时应该做什么。

```php
Flight::onEvent(string $event, callable $callback): void
```

- `$event`：您事件的一个名称（例如，`'user.login'`）。
- `$callback`：事件触发时运行的函数。

您通过告诉 Flight 事件发生时要做什么来“订阅”事件。回调可以接受从事件触发器传递的参数。

Flight 的事件系统是同步的，这意味着每个事件监听器按顺序一个接一个执行。当您触发事件时，所有注册的监听器将运行到完成，然后您的代码才会继续。这一点很重要，因为它不同于异步事件系统，其中监听器可能并行运行或在稍后时间运行。

#### 简单示例
```php
Flight::onEvent('user.login', function ($username) {
    echo "Welcome back, $username!";

	// you can send an email if the login is from a new location
});
```
在这里，当 `'user.login'` 事件被触发时，它会以用户名问候用户，并且如果需要，还可以包括发送电子邮件的逻辑。

> **注意：** 回调可以是函数、匿名函数或类的方法。

### 触发事件

要使事件发生，请使用 `Flight::triggerEvent()`。这告诉 Flight 运行为该事件注册的所有监听器，并传递您提供的所有数据。

```php
Flight::triggerEvent(string $event, ...$args): void
```

- `$event`：您正在触发的イベント名称（必须匹配注册的事件）。
- `...$args`：发送给监听器的可选参数（可以是任意数量的参数）。

#### 简单示例
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
这会触发 `'user.login'` 事件并将 `'alice'` 发送给我们先前定义的监听器，它将输出：`Welcome back, alice!`。

- 如果没有注册监听器，什么都不会发生——您的应用程序不会崩溃。
- 使用展开运算符 (`...`) 来灵活传递多个参数。

### 停止事件

如果监听器返回 `false`，则不会执行该事件的其他监听器。这允许您基于特定条件停止事件链。请记住，监听器的顺序很重要，因为第一个返回 `false` 的监听器将停止其余的运行。

**示例**：
```php
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) {
        logoutUser($username);
        return false; // Stops subsequent listeners
    }
});
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username); // this is never sent
});
```

### 覆盖事件方法

`Flight::onEvent()` 和 `Flight::triggerEvent()` 可以[扩展](/learn/extending)，这意味着您可以重新定义它们的工作方式。这对于想要自定义事件系统的先进用户很棒，例如添加日志或更改事件分发方式。

#### 示例：自定义 `onEvent`
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // Log every event registration
    error_log("New event listener added for: $event");
    // Call the default behavior (assuming an internal event system)
    Flight::_onEvent($event, $callback);
});
```
现在，每次您注册事件时，它都会在继续之前记录它。

#### 为什么覆盖？
- 添加调试或监控。
- 在某些环境中限制事件（例如，在测试中禁用）。
- 与不同的イベント库集成。

### 将事件放置在哪里

如果您对项目中的事件概念是新手，您可能会想知道：*我在应用程序中在哪里注册所有这些事件？* Flight 的简单性意味着没有严格规则——您可以根据项目需要将它们放置在任何地方。然而，保持它们组织化有助于在应用程序增长时维护您的代码。以下是一些实用的选项和最佳实践，针对 Flight 的轻量级特性量身定制：

#### 选项 1：在您的主 `index.php` 中
对于小型应用程序或快速原型，您可以在 `index.php` 文件中与路由一起注册事件。这将一切保持在一个地方，当简单性是您的优先级时，这很合适。

```php
require 'vendor/autoload.php';

// Register events
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in at " . date('Y-m-d H:i:s'));
});

// Define routes
Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "Logged in!";
});

Flight::start();
```
- **优点**：简单，没有额外文件，非常适合小型项目。
- **缺点**：随着应用程序增长，事件和路由增多时可能会变得杂乱。

#### 选项 2：单独的 `events.php` 文件
对于稍大型的应用程序，请考虑将事件注册移动到一个专用文件，如 `app/config/events.php`。在您的 `index.php` 中在路由之前包含此文件。这模仿了 Flight 项目中路由通常在 `app/config/routes.php` 中的组织方式。

```php
// app/config/events.php
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
- **优点**：保持 `index.php` 专注于路由，逻辑组织事件，易于查找和编辑。
- **缺点**：添加了一点结构，对于非常小的应用程序可能感觉过度。

#### 选项 3：在触发它们的地方附近
另一种方法是在触发它们的地方附近注册事件，例如在控制器或路由定义内部。这如果事件特定于应用程序的一个部分则工作良好。

```php
Flight::route('/signup', function () {
    // Register event here
    Flight::onEvent('user.registered', function ($email) {
        echo "Welcome email sent to $email!";
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "Signed up!";
});
```
- **优点**：保持相关代码在一起，适合孤立功能。
- **缺点**：分散事件注册，使一次性查看所有事件更难；如果不小心，可能有重复注册的风险。

#### Flight 的最佳实践
- **从简单开始**：对于小型应用程序，将事件放在 `index.php` 中。它快速且符合 Flight 的极简主义。
- **智能增长**：随着应用程序扩展（例如，超过 5-10 个事件），使用 `app/config/events.php` 文件。这是自然的升级步骤，就像组织路由一样，并保持您的代码整洁，而无需添加复杂框架。
- **避免过度工程**：除非您的应用程序变得巨大，否则不要创建一个完整的“事件管理器”类或目录——Flight 以简单为生，所以保持轻量级。

#### 提示：按目的分组
在 `events.php` 中，使用注释将相关事件分组（例如，所有用户相关事件放在一起）以提高清晰度：

```php
// app/config/events.php
// User Events
Flight::onEvent('user.login', function ($username) {
    error_log("$username logged in");
});
Flight::onEvent('user.registered', function ($email) {
    echo "Welcome to $email!";
});

// Page Events
Flight::onEvent('page.updated', function ($pageId) {
    Flight::cache()->delete("page_$pageId");
});
```

这种结构扩展良好且保持对初学者的友好。

### 真实世界示例

让我们通过一些真实世界场景来展示事件如何工作以及为什么它们有用。

#### 示例 1：记录用户登录
```php
// Step 1: Register a listener
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username logged in at $time");
});

// Step 2: Trigger it in your app
Flight::route('/login', function () {
    $username = 'bob'; // Pretend this comes from a form
    Flight::triggerEvent('user.login', $username);
    echo "Hi, $username!";
});
```
**为什么有用**：登录代码不需要知道日志记录——它只需触发事件。您可以稍后添加更多监听器（例如，发送欢迎电子邮件），而无需更改路由。

#### 示例 2：通知新用户
```php
// Listener for new registrations
Flight::onEvent('user.registered', function ($email, $name) {
    // Simulate sending an email
    echo "Email sent to $email: Welcome, $name!";
});

// Trigger it when someone signs up
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "Thanks for signing up!";
});
```
**为什么有用**：注册逻辑专注于创建用户，而事件处理通知。您可以稍后添加更多监听器（例如，记录注册）。

#### 示例 3：清除缓存
```php
// Listener to clear a cache
Flight::onEvent('page.updated', function ($pageId) {
	// if using the flightphp/cache plugin
    Flight::cache()->delete("page_$pageId");
    echo "Cache cleared for page $pageId.";
});

// Trigger when a page is edited
Flight::route('/edit-page/(@id)', function ($pageId) {
    // Pretend we updated the page
    Flight::triggerEvent('page.updated', $pageId);
    echo "Page $pageId updated.";
});
```
**为什么有用**：编辑代码不关心缓存——它只需信号更新。应用程序的其他部分可以根据需要反应。

### 最佳实践

- **清晰命名事件**：使用像 `'user.login'` 或 `'page.updated'` 这样的具体名称，以便明显它们做什么。
- **保持监听器简单**：不要在监听器中放置缓慢或复杂任务——保持您的应用程序快速。
- **测试您的事件**：手动触发它们以确保监听器按预期工作。
- **明智使用事件**：它们对于解耦很棒，但太多可能会使您的代码难以跟随——在有意义时使用它们。

Flight PHP 中的事件系统，使用 `Flight::onEvent()` 和 `Flight::triggerEvent()`，为您提供了一种简单却强大的方式来构建灵活的应用程序。通过让应用程序的不同部分通过事件相互通信，您可以保持您的代码组织化、可重用且易于扩展。无论您是在记录操作、发送通知还是管理更新，事件都能帮助您在不纠缠逻辑的情况下完成它。而且，通过覆盖这些方法的能力，您有自由来定制系统以满足您的需求。从单个事件开始小规模，并观察它如何转变您的应用程序结构！

### 内置事件

Flight PHP 带有几个内置事件，您可以使用它们来钩入框架的生命周期。这些事件在请求/响应周期的特定点触发，允许您在某些操作发生时执行自定义逻辑。

#### 内置事件列表
- **flight.request.received**: `function(Request $request)` 当请求被接收、解析和处理时触发。
- **flight.error**: `function(Throwable $exception)` 当请求生命周期中发生错误时触发。
- **flight.redirect**: `function(string $url, int $status_code)` 当重定向被启动时触发。
- **flight.cache.checked**: `function(string $cache_key, bool $hit, float $executionTime)` 当缓存被检查特定键时以及缓存命中或未命中时触发。
- **flight.middleware.before**: `function(Route $route)`在 before 中间件执行后触发。
- **flight.middleware.after**: `function(Route $route)` 在 after 中间件执行后触发。
- **flight.middleware.executed**: `function(Route $route, $middleware, string $method, float $executionTime)` 在任何中间件执行后触发
- **flight.route.matched**: `function(Route $route)` 当路由匹配但尚未运行时触发。
- **flight.route.executed**: `function(Route $route, float $executionTime)` 在路由执行和处理后触发。`$executionTime` 是执行路由（调用控制器等）所需的时间。
- **flight.view.rendered**: `function(string $template_file_path, float $executionTime)` 在视图渲染后触发。`$executionTime` 是渲染模板所需的时间。**注意：如果您覆盖 `render` 方法，您需要重新触发此事件。**
- **flight.response.sent**: `function(Response $response, float $executionTime)` 在响应发送给客户端后触发。`$executionTime` 是构建响应所需的时间。

## 另请参阅
- [扩展 Flight](/learn/extending) - 如何扩展和自定义 Flight 的核心功能。
- [缓存](/awesome-plugins/php_file_cache) - 使用事件在页面更新时清除缓存的示例。

## 故障排除
- 如果您没有看到事件监听器被调用，请确保在触发事件之前注册它们。注册顺序很重要。

## 更新日志
- v3.15.0 - 将事件添加到 Flight。