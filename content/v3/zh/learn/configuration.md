# 配置

## 概述

Flight 提供了一种简单的方式来配置框架的各个方面，以满足您的应用程序需求。有些是默认设置的，但您可以根据需要覆盖它们。您还可以设置自己的变量，以便在整个应用程序中使用。

清晰、分层的配置（文件默认值 + 环境机密）也有助于 [AI 编码工具](/learn/ai)：智能体学会字面量放在一处，机密放在另一处，而不是在控制器中随意发明 `$_ENV` 读取。

## 理解

您可以通过 `set` 方法设置配置值来自定义 Flight 的某些行为。

```php
Flight::set('flight.log_errors', true);
```

在结构化应用（包括 [骨架](https://github.com/flightphp/skeleton)）中，您通常从 `app/config/config.php` 加载项目设置，然后将相关键应用到 Engine（例如 `flight.base_url`、`flight.views.path`）。您还可以将一个小型配置对象注入控制器，而不是到处读取全局变量——这对测试和遵循 `AGENTS.md` 的智能体更友好。

## 基本用法

### Flight 配置选项

以下是所有可用配置设置的列表：

- **flight.base_url** `?string` - 如果 Flight 在子目录中运行，则覆盖请求的基本 URL。（默认值：null）
- **flight.case_sensitive** `bool` - URL 的区分大小写匹配。（默认值：false）
- **flight.handle_errors** `bool` - 允许 Flight 内部处理所有错误。（默认值：true）
  - 如果您希望 Flight 处理错误而不是使用默认的 PHP 行为，则需要将其设置为 true。
  - 如果您安装了 [Tracy](/awesome-plugins/tracy)，则需要将其设置为 false，以便 Tracy 可以处理错误。
  - 如果您安装了 [APM](/awesome-plugins/apm) 插件，则需要将其设置为 true，以便 APM 可以记录错误。
- **flight.log_errors** `bool` - 将错误记录到 Web 服务器的错误日志文件。（默认值：false）
  - 如果您安装了 [Tracy](/awesome-plugins/tracy)，Tracy 将根据 Tracy 配置记录错误，而不是此配置。
- **flight.debug** `bool` - 当发生错误时，在浏览器中输出详细的错误信息（异常消息、代码和堆栈跟踪）。（默认值：false）
  - **切勿在生产环境中启用此选项** — 它会泄露内部应用程序细节。仅用于本地开发或暂存环境。
  - 当为 `false` 时，会显示通用的 `500 Internal Server Error`。与 `flight.log_errors` 配合使用以在服务器端捕获错误。
- **flight.allow_method_override** `bool` - 允许通过 `X-HTTP-Method-Override` 请求头或 POST 正文中的 `_method` 字段覆盖 HTTP 方法。（默认值：true）
  - **对于不需要基于 HTML 表单的方法欺骗的应用程序，建议将其设置为 `false`**，因为它可以防止客户端通过标准 POST 表单伪造 `DELETE` 或 `PUT` 请求。
  - 有关更多详细信息，请参阅 [安全](/learn/security#flight-configuration-hardening)。
- **flight.views.path** `string` - 包含视图模板文件的目录。（默认值：./views）
- **flight.views.extension** `string` - 视图模板文件扩展名。（默认值：`.php`；官方骨架在使用 Twig 时将其设置为 `.twig`）
- **flight.views.restrict_to_path** `bool` - 当为 `true` 时，Flight 的原生 `View` 仅接受解析到 `flight.views.path` 内的模板文件。（默认值：`false`）。对于使用原生视图的应用程序，**请开启此选项**。请参阅 [安全](/learn/security#flightviewsrestrict_to_path)。
- **flight.content_length** `bool` - 设置 `Content-Length` 头。（默认值：true）
  - 如果您使用 [Tracy](/awesome-plugins/tracy)，则需要将其设置为 false，以便 Tracy 可以正确渲染。
- **flight.v2.output_buffering** `bool` - 使用传统的输出缓冲。请参阅 [迁移到 v3](migrating-to-v3)。（默认值：false）

### 加载器配置

加载器还有一个额外的配置设置。这将允许您自动加载类名中带有 `_` 的类。

```php
// 启用带下划线的类加载
// 默认为 true
Loader::$v2ClassLoading = false;
```

请记住，[自动加载](/learn/autoloading) 还取决于 **文件夹大小写** 与命名空间匹配——特别是在骨架的 `App\` + `app/Controller/` 布局中。

### 项目配置和 `.env`（骨架模式）

Flight 核心不需要 `.env` 文件。许多应用程序仅使用 PHP 配置数组。官方骨架对配置进行分层，以便机密不进入 git，同时 Runway 仍可以安全地重写 **字面量** 配置：

1. **`.env` / 真实环境** — 机密和部署覆盖（被 git 忽略）。
2. **`app/config/config.php`** — 字面量 PHP 数组默认值（从 `config_sample.php` 复制）。此文件中**不要**使用 `$_ENV[...]` 表达式：像 `runway config:set` 这样的工具可能会将其重写为静态值，并可能将机密烘焙到文件中。
3. **在引导时合并** — 对于映射的键，env 优先；应用程序代码读取配置对象或 `$app->get()`，而不是在控制器中使用 `$_ENV`。

`config_sample.php` / `config.php` 的示例结构（简化版）：

```php
<?php
// 仅字面量 — 对于骨架工作流，机密应放在 .env 中
return [
	'app' => [
		'env' => 'development',
		'debug' => true,
		'base_url' => '/',
		'timezone' => 'UTC',
	],
	'database' => [
		'driver' => 'sqlite', // 或 mysql，或 '' 以禁用
		'host' => 'localhost',
		'dbname' => '',
		'user' => '',
		'password' => '',
		'file_path' => __DIR__ . '/../../database.sqlite',
	],
	// ...
];
```

```bash
# .env.example → .env（骨架）
APP_ENV=development
APP_DEBUG=true
FLIGHT_BASE_URL=/
DB_DRIVER=sqlite
# DB_PASSWORD=...
```

这种拆分对于 [AI 友好项目](/learn/ai) 是故意的：指令可以说“默认值在 `config.php` 中，机密在 `.env` 中，注入 Config / Engine——绝不在控制器中发明环境访问。”现有应用程序可以完全忽略 `.env` 并保留单个配置文件。

### 变量

Flight 允许您保存变量，以便在应用程序的任何位置使用它们。

```php
// 保存您的变量
Flight::set('id', 123);

// 在应用程序的其他地方
$id = Flight::get('id');
```
要查看变量是否已设置，您可以这样做：

```php
if (Flight::has('id')) {
  // 执行某些操作
}
```

您可以通过以下方式清除变量：

```php
// 清除 id 变量
Flight::clear('id');

// 清除所有变量
Flight::clear();
```

> **注意：** 仅仅因为您可以设置变量并不意味着您应该这样做。请谨慎使用此功能。原因是存储在这里的任何内容都会成为全局变量。全局变量是不好的，因为它们可以从应用程序的任何位置更改，使得跟踪错误变得困难。此外，这可能会使 [单元测试](/guides/unit-testing) 之类的事情复杂化。对于控制器需要的服务和配置，优先使用构造函数注入（如骨架 + Dice 设置中所示）。

### 错误和异常

所有错误和异常都由 Flight 捕获并传递给 `error` 方法。
如果 `flight.handle_errors` 设置为 true。

默认行为是发送通用的 `HTTP 500 Internal Server Error` 响应，并附带一些错误信息。

您可以根据自己的需要 [覆盖](/learn/extending) 此行为：

```php
Flight::map('error', function (Throwable $error) {
  // 处理错误
  echo $error->getTraceAsString();
});
```

默认情况下，错误不会记录到 Web 服务器。您可以通过更改配置来启用此功能：

```php
Flight::set('flight.log_errors', true);
```

#### 404 未找到

当找不到 URL 时，Flight 调用 `notFound` 方法。默认行为是发送带有简单消息的 `HTTP 404 Not Found` 响应。

您可以根据自己的需要 [覆盖](/learn/extending) 此行为：

```php
Flight::map('notFound', function () {
  // 处理未找到
});
```

## 另请参阅
- [安装](/install) - 骨架配置、`.env` 和引导布局。
- [自动加载](/learn/autoloading) - 命名空间和文件夹大小写。
- [扩展 Flight](/learn/extending) - 如何扩展和自定义 Flight 的核心功能。
- [单元测试](/guides/unit-testing) - 如何为您的 Flight 应用程序编写单元测试。
- [AI 与开发者体验](/learn/ai) - `AGENTS.md` 和一致的项目说明。
- [Tracy](/awesome-plugins/tracy) - 用于高级错误处理和调试的插件。
- [Tracy 扩展](/awesome-plugins/tracy_extensions) - 用于将 Tracy 与 Flight 集成的扩展。
- [APM](/awesome-plugins/apm) - 用于应用程序性能监控和错误跟踪的插件。
- [安全](/learn/security) - 加固标志和机密处理。

## 故障排除
- 如果您在找出配置的所有值时遇到问题，可以执行 `var_dump(Flight::get());`
- 如果 Runway 或部署工具重写了 `config.php`，请确认机密未被提交——在使用骨架模式时，将它们保留在 `.env` 或真实环境中。

## 变更日志
- 文档 – 在视图路径设置旁边注明了 `flight.views.restrict_to_path`。
- 文档 – 记录了骨架式配置 / `.env` 分层以及新项目的 Twig 视图扩展默认值。
- v3.18.1 - 添加了 `flight.debug` 和 `flight.allow_method_override` 配置选项。
- v3.5.0 - 添加了 `flight.v2.output_buffering` 配置，以支持传统的输出缓冲行为。
- v2.0 - 添加了核心配置。