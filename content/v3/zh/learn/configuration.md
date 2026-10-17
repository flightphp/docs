# 配置

## 概述

Flight 提供了一种简单的方式来配置框架的各个方面，以满足你的应用需求。有些配置是默认设置的，但你可以根据需要覆盖它们。你也可以设置自己的变量，以便在整个应用中使用。

清晰、分层的配置（文件默认值 + 环境密钥）也有助于 [AI 编码工具](/learn/ai)：智能体会知道字面量放在一个地方、密钥放在另一个地方，而不是在控制器里自创 `$_ENV` 读取。

## 了解

你可以通过 `set` 方法设置配置值，来自定义 Flight 的某些行为。

```php
Flight::set('flight.log_errors', true);
```

在结构化应用中（包括[骨架项目](https://github.com/flightphp/skeleton)），通常从 `app/config/config.php` 加载项目设置，然后将相关键应用到 Engine（例如 `flight.base_url`、`flight.views.path`）。你也可以向控制器注入一个小型配置对象，而不是到处读取全局变量——这对测试以及遵循 `AGENTS.md` 的智能体更友好。

## 基本用法

### Flight 配置选项

以下是所有可用配置设置的列表：

- **flight.base_url** `?string` - 如果 Flight 运行在子目录中，则覆盖请求的基础 URL。（默认值：null）
- **flight.case_sensitive** `bool` - URL 的区分大小写匹配。（默认值：false）
- **flight.handle_errors** `bool` - 允许 Flight 在内部处理所有错误。（默认值：true）
  - 如果你希望 Flight 而不是 PHP 默认行为来处理错误，此项需要为 true。
  - 如果你安装了 [Tracy](/awesome-plugins/tracy)，则希望将此设为 false，以便 Tracy 可以处理错误。
  - 如果你安装了 [APM](/awesome-plugins/apm) 插件，则希望将此设为 true，以便 APM 可以记录错误。
- **flight.log_errors** `bool` - 将错误记录到 Web 服务器的错误日志文件。（默认值：false）
  - 如果你安装了 [Tracy](/awesome-plugins/tracy)，Tracy 将根据 Tracy 的配置记录错误，而不是此配置。
- **flight.debug** `bool` - 当发生错误时，在浏览器中输出详细错误信息（异常消息、代码和堆栈跟踪）。（默认值：false）
  - **切勿在生产环境中启用此选项**——它会泄露应用内部细节。仅用于本地开发或预发布环境。
  - 当为 `false` 时，将改为显示通用的 `500 Internal Server Error`。与 `flight.log_errors` 配合以在服务器端捕获错误。
- **flight.allow_method_override** `bool` - 允许通过 `X-HTTP-Method-Override` 请求头或 POST 正文中的 `_method` 字段覆盖 HTTP 方法。（默认值：true）
  - 对于不需要基于 HTML 表单进行方法伪造的应用，**建议将此设为 `false`**，因为它可以防止客户端通过标准 POST 表单伪造 `DELETE` 或 `PUT` 请求。
  - 更多详情请参见[安全性](/learn/security#flight-configuration-hardening)。
- **flight.views.path** `string` - 包含视图模板文件的目录。（默认值：./views）
- **flight.views.extension** `string` - 视图模板文件扩展名。（默认值：`.php`；使用 Twig 时，官方骨架项目将其设为 `.twig`）
- **flight.views.restrict_to_path** `bool` - 当为 `true` 时，Flight 的原生 `View` 仅接受解析后位于 `flight.views.path` 内的模板文件。（默认值：`false`）。对于使用原生视图的应用，**请开启此项**。参见[安全性](/learn/security#flightviewsrestrict_to_path)。
- **flight.content_length** `bool` - 设置 `Content-Length` 头。（默认值：true）
  - 如果你使用的是 [Tracy](/awesome-plugins/tracy)，则需要将其设为 false，以便 Tracy 可以正确渲染。
- **flight.v2.output_buffering** `bool` - 使用旧版输出缓冲。参见[迁移到 v3](migrating-to-v3)。（默认值：false）

### 加载器配置

加载器还有一个额外的配置设置。它允许你自动加载类名中包含 `_` 的类。

```php
// 启用带下划线的类加载
// 默认为 true
Loader::$v2ClassLoading = false;
```

请记住，[自动加载](/learn/autoloading)还取决于**文件夹大小写**是否与命名空间匹配——尤其是在骨架项目的 `App\` + `app/Controller/` 布局中。

### 项目配置与 `.env`（骨架模式）

Flight 核心不要求使用 `.env` 文件。许多应用只使用 PHP 配置数组。官方骨架项目对配置进行分层，使密钥不会进入 git，同时 Runway 仍能安全地重写**字面量**配置：

1. **`.env` / 真实环境** — 密钥和部署覆盖项（被 gitignore 忽略）。
2. **`app/config/config.php`** — PHP 字面量数组默认值（从 `config_sample.php` 复制）。此文件内最好**不要**包含 `$_ENV[...]` 表达式：像 `runway config:set` 这样的工具可能会将其重写为静态值，并可能把密钥固化到文件中。
3. **启动时合并** — 对于映射的键，环境变量优先；应用代码读取配置对象或 `$app->get()`，而不是在控制器中读取 `$_ENV`。

`config_sample.php` / `config.php` 的示例结构（简化版）：

```php
<?php
// 仅字面量——在骨架工作流中，密钥应放在 .env 中
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
# .env.example → .env（骨架项目）
APP_ENV=development
APP_DEBUG=true
FLIGHT_BASE_URL=/
DB_DRIVER=sqlite
# DB_PASSWORD=...
```

这种拆分是专为[AI 友好项目](/learn/ai)设计的：说明可以写成“默认值放在 `config.php`，密钥放在 `.env`，注入 Config / Engine——绝不要在控制器中自创环境变量访问。”现有应用可以完全忽略 `.env`，并保留单个配置文件。

### 变量

Flight 允许你保存变量，以便它们可以在应用中的任何地方使用。

```php
// 保存你的变量
Flight::set('id', 123);

// 在应用的其他地方
$id = Flight::get('id');
```
要检查变量是否已设置，可以这样做：

```php
if (Flight::has('id')) {
  // 执行某些操作
}
```

你可以这样清除变量：

```php
// 清除 id 变量
Flight::clear('id');

// 清除所有变量
Flight::clear();
```

> **注意：** 仅仅因为你可以设置变量，并不意味着你应该这样做。请谨慎使用此功能。原因在于，存储在这里的任何内容都会变成全局变量。全局变量很糟糕，因为它们可以在应用的任何地方被更改，导致难以追踪 bug。此外，这还会使[单元测试](/guides/unit-testing)之类的事情复杂化。对于控制器所需的服务和配置，优先使用构造函数注入（如骨架项目 + Dice 设置中那样）。

### 错误与异常

当 `flight.handle_errors` 设为 true 时，所有错误和异常都会被 Flight 捕获并传递给 `error` 方法。

默认行为是发送一个通用的 `HTTP 500 Internal Server Error` 响应，并附带一些错误信息。

你可以根据自己的需要[覆盖](/learn/extending)此行为：

```php
Flight::map('error', function (Throwable $error) {
  // 处理错误
  echo $error->getTraceAsString();
});
```

默认情况下，错误不会记录到 Web 服务器。你可以通过更改配置来启用：

```php
Flight::set('flight.log_errors', true);
```

#### 404 未找到

当找不到 URL 时，Flight 会调用 `notFound` 方法。默认行为是发送一个带有简单消息的 `HTTP 404 Not Found` 响应。

你可以根据自己的需要[覆盖](/learn/extending)此行为：

```php
Flight::map('notFound', function () {
  // 处理未找到
});
```

## 另请参阅
- [安装](/install) - 骨架项目配置、`.env` 和启动布局。
- [自动加载](/learn/autoloading) - 命名空间和文件夹大小写。
- [扩展 Flight](/learn/extending) - 如何扩展和自定义 Flight 的核心功能。
- [单元测试](/guides/unit-testing) - 如何为你的 Flight 应用编写单元测试。
- [AI 与开发者体验](/learn/ai) - `AGENTS.md` 和一致的项目说明。
- [Tracy](/awesome-plugins/tracy) - 用于高级错误处理和调试的插件。
- [Tracy 扩展](/awesome-plugins/tracy_extensions) - 用于将 Tracy 与 Flight 集成的扩展。
- [APM](/awesome-plugins/apm) - 用于应用性能监控和错误跟踪的插件。
- [安全性](/learn/security) - 加固标志和密钥处理。

## 故障排查
- 如果你在查找配置的所有值时遇到问题，可以执行 `var_dump(Flight::get());`
- 如果 Runway 或部署工具重写了 `config.php`，请确认密钥没有被提交——使用骨架模式时，请将它们保留在 `.env` 或真实环境中。

## 更新日志
- 文档 – 在视图路径设置旁注明了 `flight.views.restrict_to_path`。
- 文档 – 记录了骨架式配置 / `.env` 分层，以及新项目的 Twig 视图扩展名默认值。
- v3.18.1 - 添加了 `flight.debug` 和 `flight.allow_method_override` 配置选项。
- v3.5.0 - 添加了 `flight.v2.output_buffering` 配置，以支持旧版输出缓冲行为。
- v2.0 - 添加了核心配置。