# FlightPHP APM 文档

欢迎使用 FlightPHP APM——您的应用专属性能教练！本指南是您设置、使用并掌握 FlightPHP 应用性能监控（APM）的路线图。无论您是想要排查缓慢请求，还是只想深入研究延迟图表，我们都能满足您的需求。让您的应用更快、用户更满意、调试过程更轻松！

点击查看 Flight 文档站点的仪表盘 [演示](https://flightphp-docs-apm.sky-9.com/apm/dashboard)。

![FlightPHP APM](/images/apm.png)

## 为什么 APM 很重要

想象一下：您的应用是一家繁忙的餐厅。如果没有办法跟踪订单需要多长时间或厨房在哪里卡住，您就只能猜测为什么顾客不满意地离开。APM 就像是您的副厨——它关注每一个步骤，从传入请求到数据库查询，并标记任何拖慢您速度的问题。页面加载慢会导致用户流失（研究表明，如果网站加载超过 3 秒，53% 的用户会离开！），而 APM 帮助您在问题造成影响之前 *就* 发现它们。这是主动的安心——更少“为什么坏了？”的时刻，更多“看这运行得多流畅！”的胜利。

## 安装

使用 Composer 开始：

```bash
composer require flightphp/apm
```

您需要：
- **PHP 7.4+**：既兼容 LTS Linux 发行版，又支持现代 PHP。
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**：我们要增强的轻量级框架。

## 支持的数据库

FlightPHP APM 目前支持以下数据库来存储指标：

- **SQLite3**：简单、基于文件，非常适合本地开发或小型应用。大多数设置中的默认选项。
- **MySQL/MariaDB**：适用于需要强大、可扩展存储的大型项目或生产环境。

您可以在配置步骤中选择数据库类型（见下文）。确保您的 PHP 环境已安装所需的扩展（例如 `pdo_sqlite` 或 `pdo_mysql`）。

## 快速开始

以下是您走向 APM 卓越的分步指南：

### 1. 注册 APM

将以下内容放入您的 `index.php` 或 `services.php` 文件中以开始跟踪：

```php
// 使用 LoggerFactory 根据配置文件创建日志记录器
use flight\apm\logger\LoggerFactory;
use flight\database\SimplePdo;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);

// 如果您要添加数据库连接
// 更推荐使用 SimplePdo（或在开发环境中使用 Tracy Extensions 的 PdoQueryCapture）。
// 通过 options 数组（第 5 个参数）启用 APM 查询跟踪。
$pdo = new SimplePdo('mysql:host=localhost;dbname=example', 'user', 'pass', null, [
	'trackApmQueries' => true, // 必须为 true 才能捕获查询以用于 APM
]);
$Apm->addPdoConnection($pdo);
```

**这里发生了什么？**
- `LoggerFactory::create()` 获取您的配置（稍后会详细介绍）并设置一个日志记录器——默认使用 SQLite。
- `Apm` 是核心——它监听 Flight 的事件（请求、路由、错误等）并收集指标。
- `bindEventsToFlightInstance($app)` 将其全部绑定到您的 Flight 应用。

**专业提示：采样**
如果您的应用很繁忙，记录 *每个* 请求可能会过载。使用采样率（0.0 到 1.0）：

```php
$Apm = new Apm($ApmLogger, 0.1); // 记录 10% 的请求
```

这样既保持性能灵活，又能为您提供可靠的数据。

### 2. 配置它

运行以下命令来创建您的 `.runway-config.json`：

```bash
php vendor/bin/runway apm:init
```

**这是做什么的？**
- 启动一个向导，询问原始指标来自哪里（源）以及处理后的数据去哪里（目标）。
- 默认是 SQLite——例如 `sqlite:/tmp/apm_metrics.sqlite` 作为源，另一个作为目标。
- 您最终会得到一个类似这样的配置：
  ```json
  {
    "apm": {
      "source_type": "sqlite",
      "source_db_dsn": "sqlite:/tmp/apm_metrics.sqlite",
      "storage_type": "sqlite",
      "dest_db_dsn": "sqlite:/tmp/apm_metrics_processed.sqlite"
    }
  }
  ```

> 此过程还会询问您是否为此设置运行迁移。如果您是第一次设置，答案是“是”。

**为什么需要两个位置？**
原始指标堆积得很快（可以想象为未过滤的日志）。worker 将其处理成结构化的目标数据，供仪表盘使用。保持整洁！

### 3. 使用 Worker 处理指标

Worker 将原始指标转换为可直接在仪表盘上使用的数据。运行一次：

```bash
php vendor/bin/runway apm:worker
```

**它在做什么？**
- 从您的源（例如 `apm_metrics.sqlite`）读取数据。
- 将最多 100 条指标（默认批处理大小）处理到您的目标中。
- 处理完毕或没有剩余指标时停止。

**保持运行**
对于实际应用，您需要持续处理。以下是您的选择：

- **守护进程模式**：
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  永远运行，随来随处理指标。非常适合开发或小型设置。

- **Crontab**：
  将以下内容添加到您的 crontab（`crontab -e`）：
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  每分钟执行一次——非常适合生产环境。

- **Tmux/Screen**：
  启动一个可分离的会话：
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B，然后按 D 分离；使用 `tmux attach -t apm-worker` 重新连接
  ```
  即使您注销也能保持运行。

- **自定义调整**：
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`：每次处理 50 条指标。
  - `--max_messages 1000`：处理 1000 条指标后停止。
  - `--timeout 300`：5 分钟后退出。

**为什么要费心？**
没有 worker，您的仪表盘将是空的。它是原始日志与可操作洞察之间的桥梁。

### 4. 启动仪表盘

查看您应用的各项关键指标：

```bash
php vendor/bin/runway apm:dashboard
```

**这是什么？**
- 在 `http://localhost:8001/apm/dashboard` 启动一个 PHP 服务器。
- 显示请求日志、慢路由、错误率等等。

**自定义**：
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`：可从任何 IP 访问（方便远程查看）。
- `--port 8080`：如果 8001 被占用，使用其他端口。
- `--php-path`：如果 PHP 不在 PATH 中，指定 PHP 路径。

在浏览器中打开 URL 开始探索！

#### 生产模式

对于生产环境，您可能需要尝试几种技术才能让仪表盘运行，因为可能存在防火墙和其他安全措施。以下是一些选择：

- **使用反向代理**：设置 Nginx 或 Apache 将请求转发到仪表盘。
- **SSH 隧道**：如果您可以通过 SSH 访问服务器，请使用 `ssh -L 8080:localhost:8001 youruser@yourserver` 将仪表盘隧道到您的本地机器。
- **VPN**：如果您的服务器在 VPN 后面，请连接 VPN 并直接访问仪表盘。
- **配置防火墙**：为您的 IP 或服务器网络打开 8001 端口（或您设置的任何端口）。
- **配置 Apache/Nginx**：如果您的应用程序前面有 Web 服务器，您可以将其配置为某个域名或子域名。将该主机指向 `dashboard/index.php`（内置服务器将其用作路由器）。静态文件位于其旁边的 `dashboard/css` 和 `dashboard/js` 中。
- **基本身份验证**：在 runway 配置中设置 `apm.dashboard_auth.user` 和 `apm.dashboard_auth.password`。如果任一值为空，仪表盘保持开放。这是一个共享密码，不是应用程序用户账户。

#### 想要不同的仪表盘？

如果您愿意，也可以构建自己的仪表盘！查看 vendor/flightphp/apm/src/apm/presenter 目录，了解如何为您的仪表盘呈现数据的思路！

## 仪表盘功能

仪表盘首先显示数字，然后是图表，接着是请求日志。

- **统计信息行**：请求数、错误率、p95、p99、平均延迟、每分钟吞吐量以及缓存命中率。
- **Worker 积压**：`apm_metrics_log` 中仍有多少条原始指标。数字较高意味着 worker 落后了。如果积压为 0 且仪表盘为空，则表示尚未收集到任何数据。
- **延迟图表**：同一时间桶内的平均延迟和请求数。最近一小时使用 5 分钟桶，最近一天使用 15 分钟桶，最近一周使用 6 小时桶。自定义范围会选择保持约 40 个点的桶。时间以 UTC 存储，并以您选择的时区绘制。
- **状态图表**：堆叠的 `2xx`、`3xx`、`4xx` 和 `5xx` 计数。点击一个系列可按该状态类别筛选日志。
- **慢路由、中间件、查询和请求**：各五个。查询会分组，因此 `WHERE id = 1` 和 `WHERE id = 2` 会计为一条语句。点击路由、慢请求或错误组可筛选日志。
- **自定义事件**：您的应用触发的每种自定义事件类型，包括计数、最后看到的时间，以及所选时间窗口内的小趋势线。按计数排序。它会遵循时间范围、“隐藏机器人”和其他筛选器。点击一个类型可按该类型筛选日志。
- **请求日志**：方法、URL、状态、时间、内存、主机和机器人标记。打开行时加载详细信息，包括时间条、视图、查询参数、错误跟踪和自定义事件。
- **隐藏机器人**：将机器人流量从聚合结果和日志中排除。搜索爬虫和 LLM 爬虫（GPTBot、ClaudeBot、PerplexityBot、SearxNG 等）会被标记为机器人。
- **自定义范围**：除了最近一小时、一天或一周之外，还可以选择 from/to 时间窗口。
- **筛选器**：URL、状态、请求 ID、路由、IP、主机、会话、用户代理、自定义事件类型、事件数据和错误消息。查询字符串可在刷新后保留这些筛选器。

**示例**：
对 `/users` 的请求可能显示：
- 总时间：150ms
- 中间件：`AuthMiddleware->handle`（50ms）
- 查询：`SELECT * FROM users`（80ms）
- 视图渲染时间
- 缓存：命中 `user_list`（5ms）

**示例**：
对 `/users` 的请求可能显示：
- 总时间：150ms
- 中间件：`AuthMiddleware->handle`（50ms）
- 查询：`SELECT * FROM users`（80ms）
- 缓存：命中 `user_list`（5ms）

## 添加自定义事件

跟踪任何内容——例如 API 调用或支付流程：

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**它在哪里显示？**
在仪表盘的请求详细信息中的“自定义事件”下——可展开并带有漂亮的 JSON 格式化。“自定义事件”面板也会为您统计每种类型，因此一个快速的 `docs.markdown_served` 事件就足以回答“这种情况多久发生一次？”

**使用场景**：
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
现在您就能看到该 API 是否拖慢了您的应用！

## 数据库监控

像这样跟踪 PDO 查询：

```php
use flight\database\SimplePdo;

$pdo = new SimplePdo('sqlite:/path/to/db.sqlite', null, null, null, [
	'trackApmQueries' => true, // 必须为 true 才能捕获查询以用于 APM
]);
$Apm->addPdoConnection($pdo);
```

**您将获得**：
- 查询文本（例如 `SELECT * FROM users WHERE id = ?`）
- 执行时间（例如 0.015s）
- 行数（例如 42）

**注意**：
- **可选**：如果不需要数据库跟踪，可以跳过此步骤。
- **SimplePdo（推荐）**：使用 `SimplePdo` 并设置 `trackApmQueries => true`。已弃用的 `PdoWrapper` 仍然有效（第 5 个构造函数参数为 `true`）。原生核心 PDO 尚未接入——敬请期待！
- **性能警告**：在数据库密集型站点上记录每个查询可能会降低速度。使用采样（`$Apm = new Apm($ApmLogger, 0.1)`）来减轻负载。

**示例输出**：
- 查询：`SELECT name FROM products WHERE price > 100`
- 时间：0.023s
- 行数：15

## Worker 选项

根据您的需求调整 worker：

- `--timeout 300`：5 分钟后停止——适合测试。
- `--max_messages 500`：最多处理 500 条指标——使其有限。
- `--batch_size 200`：一次处理 200 条——平衡速度和内存。
- `--daemon`：不停运行——适合实时监控。

**示例**：
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
运行一小时，每次处理 100 条指标。

## 应用中的请求 ID

每个请求都有一个唯一的请求 ID 用于跟踪。您可以在应用中使用此 ID 来关联日志和指标。例如，您可以将请求 ID 添加到错误页面：

```php
Flight::map('error', function($message) {
	// 从响应头 X-Flight-Request-Id 获取请求 ID
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// 您也可以从 Flight 变量中获取
	// 此方法在 swoole 或其他异步平台上可能无法正常工作。
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## 升级

如果您要升级到较新版本的 APM，可能需要运行数据库迁移。您可以通过运行以下命令来完成：

```bash
php vendor/bin/runway apm:migrate
```
这将运行任何必要的迁移，将数据库模式更新到最新版本。

**注意：** 如果您的 APM 数据库很大，这些迁移可能需要一些时间。请在非高峰时段运行它们。worker 可以继续运行。索引迁移不会重写已收集的行。

### 升级超过 0.5.2（仪表盘和索引）

0.5.2 仅扩展了机器人检测。下面的索引文件在 0.5.2 之后的版本中附带。

首先备份目标数据库。复制 SQLite 文件，或对 MySQL/MariaDB 模式执行 `mysqldump`。

```bash
composer update flightphp/apm
php vendor/bin/runway apm:migrate
```

这将应用：

- SQLite：`0006-dest-indexes.sql`（`request_dt` 索引在 `0005` 重建 `apm_requests` 时被删除）
- MySQL/MariaDB：`0002-dest-indexes.sql`（`request_dt` 和其他仪表盘筛选器）

`apm:migrate` 记录文件名，下次会跳过。运行两次是安全的。`apm:purge` 将 `request_dt` 与 UTC 截止时间进行比较，这与行的存储方式一致。如果服务器时区以前不是 UTC，此升级后的第一次清理可能会保留不同的时间窗口。

`X-Flight-Request-Id` 与该请求存储的 `request_token` 匹配。

### 从 0.4.3 -> 0.5.0 升级

如果您要从 0.4.3 升级到 0.5.0，您需要运行以下命令：

```bash
php vendor/bin/runway apm:config-migrate
```

这会将您的配置从使用 `.runway-config.json` 文件的旧格式迁移到将键/值存储在 `config.php` 文件中的新格式。

## 清理旧数据

为了保持数据库整洁，您可以清理旧数据。如果您正在运行繁忙的应用并希望保持数据库大小可控，这尤其有用。
您可以通过运行以下命令来完成：

```bash
php vendor/bin/runway apm:purge
```
这将从数据库中删除所有超过 30 天的数据。您可以通过向 `--days` 选项传入不同的值来调整天数：

```bash
php vendor/bin/runway apm:purge --days 7
```
这将从数据库中删除所有超过 7 天的数据。

## 故障排除

卡住了？试试这些：

- **没有仪表盘数据？**
  - worker 是否在运行？检查 `ps aux | grep apm:worker`。
  - 配置路径是否匹配？验证 `.runway-config.json` 中的 DSN 是否指向真实文件。
  - 手动运行 `php vendor/bin/runway apm:worker` 以处理待处理的指标。

- **Worker 错误？**
  - 查看您的