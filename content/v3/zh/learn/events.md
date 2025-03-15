# Flight PHP 中的事件系统 (v3.15.0+)

Flight PHP 引入了一个轻量且直观的事件系统，允许您在应用程序中注册和触发自定义事件。通过 `Flight::onEvent()` 和 `Flight::triggerEvent()` 的添加，您现在可以挂钩在应用程序生命周期的关键时刻，或定义自己的事件，以使您的代码更加模块化和可扩展。这些方法是 Flight 的 **可映射方法** 的一部分，意味着您可以覆盖它们的行为以满足您的需求。

本指南涵盖了您开始使用事件所需了解的一切，包括它们的重要性、如何使用它们，以及帮助初学者理解它们强大功能的实际示例。

## 为什么使用事件？

事件允许您将应用程序的不同部分分开，以避免它们过于依赖彼此。这种分离——通常称为 **解耦**——使您的代码更易于更新、扩展或调试。您可以将逻辑拆分为更小的独立部分，以响应特定的操作（事件），而不是将所有内容写在一个大块中。

想象一下您正在构建一个博客应用程序：
- 当用户发布评论时，您可能想要：
  - 将评论保存到数据库。
  - 向博客所有者发送电子邮件。
  - 记录该操作以确保安全。

没有事件，您会将所有这些内容塞进一个函数里。使用事件，您可以将其拆开：一部分保存评论，另一部分触发事件，例如 `'comment.posted'`，而独立的监听器处理电子邮件和日志记录。这保持了代码的整洁，并允许您在不触及核心逻辑的情况下添加或删除功能（例如通知）。

### 常见用途
- **日志记录**：记录诸如登录或错误等操作，而不会使主代码变得复杂。
- **通知**：当发生某些事情时，发送电子邮件或警报。
- **更新**：刷新缓存或通知其他系统有关更改的信息。

## 注册事件监听器

要监听事件，请使用 `Flight::onEvent()`。该方法让您定义当事件发生时应执行的操作。

### 语法
```php
Flight::onEvent(string $event, callable $callback): void
```
- `$event`：您的事件名称（例如，`'user.login'`）。
- `$callback`：当事件被触发时要运行的函数。

### 它是如何工作的
您通过告诉 Flight 在事件发生时要做什么来“订阅”事件。回调可以接受从事件触发器传递的参数。

Flight 的事件系统是同步的，这意味着每个事件监听器按顺序执行，一个接一个。当您触发事件时，所有为该事件注册的监听器将在您的代码继续之前完成运行。这一点很重要，因为它与异步事件系统不同，在异步事件系统中，监听器可能会并行运行或在稍后时间执行。

### 简单示例
```php
Flight::onEvent('user.login', function ($username) {
    echo "欢迎回来，$username！";
});
```
在这里，当触发 `'user.login'` 事件时，它会通过名字向用户致意。

### 关键点
- 您可以为同一事件添加多个监听器——它们将按您注册的顺序运行。
- 回调可以是一个函数、一个匿名函数或一个类中的方法。

## 触发事件

要使事件发生，使用 `Flight::triggerEvent()`。这告诉 Flight 运行该事件所有已注册的监听器，并传递您提供的任何数据。

### 语法
```php
Flight::triggerEvent(string $event, ...$args): void
```
- `$event`：您要触发的事件名称（必须与注册的事件匹配）。
- `...$args`：要传递给监听器的可选参数（可以是任意数量的参数）。

### 简单示例
```php
$username = 'alice';
Flight::triggerEvent('user.login', $username);
```
这触发了 `'user.login'` 事件，并将 `'alice'` 发送给我们之前定义的监听器，输出将是：`欢迎回来，alice！`。

### 关键点
- 如果没有注册监听器，则什么都不会发生——您的应用不会崩溃。
- 使用扩展运算符 (`...`) 灵活地传递多个参数。

### 注册事件监听器

...

**停止进一步的监听器**：
如果某个监听器返回 `false`，则不会执行该事件的其他监听器。这使您能够根据特定条件停止事件链。请记住，监听器的顺序很重要，因为第一个返回 `false` 的监听器将阻止其余的执行。

**示例**：
```php
Flight::onEvent('user.login', function ($username) {
    if (isBanned($username)) {
        logoutUser($username);
        return false; // 停止后续监听器
    }
});
Flight::onEvent('user.login', function ($username) {
    sendWelcomeEmail($username); // 这个永远不会被发送
});
```

## 重写事件方法

`Flight::onEvent()` 和 `Flight::triggerEvent()` 可以被 [扩展](/learn/extending)，这意味着您可以重新定义它们的工作方式。这对于想要自定义事件系统的高级用户非常有用，比如添加日志记录或更改事件的分发方式。

### 示例：自定义 `onEvent`
```php
Flight::map('onEvent', function (string $event, callable $callback) {
    // 记录每个事件的注册
    error_log("新增事件监听器添加：$event");
    // 调用默认行为（假设是一个内部事件系统）
    Flight::_onEvent($event, $callback);
});
```
现在，每次您注册事件时，它都会在继续之前记录下来。

### 为什么重写？
- 添加调试或监控。
- 在某些环境中限制事件（例如，在测试中禁用）。
- 与不同的事件库集成。

## 将事件放在哪里

作为初学者，您可能会想：*我应该在应用程序中在哪里注册所有这些事件？* Flight 的简单性意味着没有严格的规则——您可以将它们放在适合您项目的任何地方。然而，保持事件的组织性有助于在应用程序增长时维护代码。以下是一些实用的选项和最佳实践，专门针对 Flight 的轻量特性：

### 选项 1：在您的主要 `index.php`
对于小型应用或快速原型，您可以直接在 `index.php` 文件中注册事件，与路由放在一起。这将所有内容放在一个地方，当简便性是您的优先考虑时，这是可以的。

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
    echo "已登录！";
});

Flight::start();
```
- **优点**：简单，没有额外文件，非常适合小项目。
- **缺点**：随着应用程序使用更多事件和路由，将会变得混乱。

### 选项 2：一个单独的 `events.php` 文件
对于稍大的应用程序，考虑将事件注册移动到一个专用文件，如 `app/config/events.php`。在路由之前将此文件包含到您的 `index.php` 中。这与在 Flight 项目中的 `app/config/routes.php` 中组织路由的方式类似。

```php
// app/config/events.php
Flight::onEvent('user.login', function ($username) {
    error_log("$username 于 " . date('Y-m-d H:i:s') . " 登录");
});

Flight::onEvent('user.registered', function ($email, $name) {
    echo "发送到 $email 的电子邮件：欢迎，$name！";
});
```

```php
// index.php
require 'vendor/autoload.php';
require 'app/config/events.php';

Flight::route('/login', function () {
    $username = 'bob';
    Flight::triggerEvent('user.login', $username);
    echo "已登录！";
});

Flight::start();
```
- **优点**：使 `index.php` 专注于路由，逻辑地组织事件，易于查找和编辑。
- **缺点**：增加了一点结构，对于非常小的应用可能会觉得过于复杂。

### 选项 3：在触发它们的地方附近
另一种方法是在触发事件的地方附近注册事件，例如在控制器或路由定义中。这对于特定于应用程序某一部分的事件效果很好。

```php
Flight::route('/signup', function () {
    // 在这里注册事件
    Flight::onEvent('user.registered', function ($email) {
        echo "欢迎邮件发送到 $email！";
    });

    $email = 'jane@example.com';
    Flight::triggerEvent('user.registered', $email);
    echo "已注册！";
});
```
- **优点**：将相关代码放在一起，适合独立特性。
- **缺点**：事件注册分散，使得难以一次查看所有事件；如果不小心还可能有重复注册的风险。

### Flight 的最佳实践
- **从简单开始**：对于小型应用，将事件放在 `index.php` 中。这快速且与 Flight 的简约性相符合。
- **智能增长**：随着应用程序的扩展（例如，超过 5-10 个事件），使用 `app/config/events.php` 文件。这是一个自然的进步，类似于组织路由，同时保持代码整洁而不增加复杂的框架。
- **避免过度工程**：除非您的应用程序变得庞大，否则不要创建一个完整的“事件管理器”类或目录——Flight 以简单著称，因此保持轻量。

### 小贴士：按目的分组
在 `events.php` 中，将相关事件分组（例如，将所有与用户相关的事件放在一起），并添加注释以增加清晰度：

```php
// app/config/events.php
// 用户事件
Flight::onEvent('user.login', function ($username) {
    error_log("$username 登录");
});
Flight::onEvent('user.registered', function ($email) {
    echo "欢迎来到 $email！";
});

// 页面事件
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]);
});
```

这种结构扩展良好，并且对初学者友好。

## 初学者示例

让我们通过一些现实场景展示事件的工作原理以及它们的价值。

### 示例 1：记录用户登录
```php
// 第 1 步：注册监听器
Flight::onEvent('user.login', function ($username) {
    $time = date('Y-m-d H:i:s');
    error_log("$username 于 $time 登录");
});

// 第 2 步：在您的应用中触发它
Flight::route('/login', function () {
    $username = 'bob'; // 假装这是来自表单的
    Flight::triggerEvent('user.login', $username);
    echo "嗨，$username！";
});
```
**这有何用处**：登录代码不需要知道日志记录——它只需触发事件。您可以稍后添加更多监听器（例如，发送欢迎邮件），而无需更改路由。

### 示例 2：通知新用户
```php
// 新注册的监听器
Flight::onEvent('user.registered', function ($email, $name) {
    // 模拟发送电子邮件
    echo "发送邮件到 $email：欢迎，$name！";
});

// 当有人注册时触发
Flight::route('/signup', function () {
    $email = 'jane@example.com';
    $name = 'Jane';
    Flight::triggerEvent('user.registered', $email, $name);
    echo "感谢注册！";
});
```
**这有何用处**：注册逻辑专注于创建用户，而事件处理通知。您可以稍后添加更多监听器（例如，记录注册）。

### 示例 3：清除缓存
```php
// 清除缓存的监听器
Flight::onEvent('page.updated', function ($pageId) {
    unset($_SESSION['pages'][$pageId]); // 如果适用，清除会话缓存
    echo "页面 $pageId 的缓存已清除。";
});

// 当编辑页面时触发
Flight::route('/edit-page/(@id)', function ($pageId) {
    // 假装我们更新了页面
    Flight::triggerEvent('page.updated', $pageId);
    echo "页面 $pageId 已更新。";
});
```
**这有何用处**：编辑代码不关心缓存——它只发出更新信号。应用程序的其他部分可以根据需要作出反应。

## 最佳实践

- **清晰命名事件**：使用具体的名称，如 `'user.login'` 或 `'page.updated'`，这样一目了然它们的作用。
- **保持监听器简单**：不要在监听器中放置缓慢或复杂的任务——保持应用程序快速。
- **测试事件**：手动触发它们，以确保监听器按预期工作。
- **明智地使用事件**：它们很适合解耦，但过多可能使代码难以理解——当合适时使用它们。

Flight PHP 的事件系统，结合 `Flight::onEvent()` 和 `Flight::triggerEvent()`，为您提供了一种构建灵活应用程序的简单而强大的方式。通过允许应用程序的不同部分通过事件进行通信，您可以保持代码的组织性、可重用性，并易于扩展。无论您是在记录操作、发送通知还是管理更新，事件有助于您在不纠缠逻辑的情况下实现这些功能。此外，通过能够重写这些方法，您有自由度根据需要定制系统。从一个简单的事件开始，看看它如何改变您应用的结构！

## 内置事件

Flight PHP 附带了一些内置事件，您可以使用这些事件来挂接到框架的生命周期。这些事件在请求/响应周期的特定点被触发，允许您在发生某些操作时执行自定义逻辑。

### 内置事件列表
- **flight.request.received**：`function(Request $request)` 当请求被接收、解析和处理时触发。
- **flight.error**：`function(Throwable $exception)` 在请求生命周期中发生错误时触发。
- **flight.redirect**：`function(string $url, int $status_code)` 当发起重定向时触发。
- **flight.cache.checked**：`function(string $cache_key, bool $hit, float $executionTime)` 调用时触发，检查特定键的缓存，及缓存是命中还是未命中。
- **flight.middleware.before**：`function(Route $route)` 在执行前中间件之后触发。
- **flight.middleware.after**：`function(Route $route)` 在执行后中间件之后触发。
- **flight.middleware.executed**：`function(Route $route, $middleware, string $method, float $executionTime)` 在执行任何中间件之后触发
- **flight.route.matched**：`function(Route $route)` 当路由匹配时触发，但尚未运行。
- **flight.route.executed**：`function(Route $route, float $executionTime)` 在路由执行和处理后触发。`$executionTime` 是执行路由（调用控制器等）所花费的时间。
- **flight.view.rendered**：`function(string $template_file_path, float $executionTime)` 在视图渲染之后触发。`$executionTime` 是渲染模板所需的时间。**注意：如果您重写了 `render` 方法，则需要重新触发此事件。**
- **flight.response.sent**：`function(Response $response, float $executionTime)` 在响应发送给客户端后触发。`$executionTime` 是构建响应所需的时间。