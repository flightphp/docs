# FlightPHP APM 文档

欢迎使用 FlightPHP APM——您应用的个人性能教练！本指南是您设置、使用和掌握 Application Performance Monitoring (APM) 与 FlightPHP 的路线图。不管您是在追踪缓慢的请求，还是只想沉迷于延迟图表，我们都已覆盖。让我们让您的应用更快，让用户更开心，并让调试会话变得轻松！

![FlightPHP APM](/images/apm.png)

## APM 为什么重要

想象一下：您的应用就像一家忙碌的餐厅。没有追踪订单花费时间或厨房哪里堵塞的方法，您就只能猜测为什么客户不满意地离开。APM 就像您的副厨——它监视每个步骤，从传入请求到数据库查询，并标记任何减缓速度的东西。缓慢的页面会失去用户（研究显示，如果网站加载超过 3 秒，53% 的用户会跳出！），APM 帮助您在问题发生前捕获这些问题。它是主动的安心——更少的“为什么这坏了？”时刻，多一些“看看这运行得多流畅！”的胜利。

## 安装

使用 Composer 开始：

```bash
composer require flightphp/apm
```

您需要：
- **PHP 7.4+**：保持与 LTS Linux 发行版兼容，同时支持现代 PHP。
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**：我们正在提升的轻量级框架。

## 支持的数据库

FlightPHP APM 当前支持以下数据库用于存储指标：

- **SQLite3**：简单、基于文件的，非常适合本地开发或小型应用。默认选项。
- **MySQL/MariaDB**：适合大型项目或生产环境，需要稳健、可扩展的存储。

您可以在配置步骤中选择数据库类型（见下文）。确保您的 PHP 环境安装了必要的扩展（如 `pdo_sqlite` 或 `pdo_mysql`）。

## 入门

以下是通往 APM 精彩的逐步指南：

### 1. 注册 APM

将以下代码放入您的 `index.php` 或 `services.php` 文件中以开始追踪：

```php
use flight\apm\logger\LoggerFactory;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);

// 如果您正在添加数据库连接
// 必须是 PdoWrapper 或 PdoQueryCapture 来自 Tracy 扩展
$pdo = new PdoWrapper('mysql:host=localhost;dbname=example', 'user', 'pass', null, true); // <-- True 要求启用 APM 中的追踪。
$Apm->addPdoConnection($pdo);
```

**这里发生了什么？**
- `LoggerFactory::create()` 获取您的配置（很快就会详细说明）并设置一个记录器——默认是 SQLite。
- `Apm` 是主角——它监听 Flight 的事件（请求、路由、错误等）并收集指标。
- `bindEventsToFlightInstance($app)` 将其绑定到您的 Flight 应用。

**专业提示：采样**
如果您的应用很繁忙，记录*每个*请求可能会过载。使用采样率（0.0 到 1.0）：

```php
$Apm = new Apm($ApmLogger, 0.1); // 记录 10% 的请求
```

这保持性能敏捷，同时仍提供可靠的数据。

### 2. 配置它

运行以下命令来创建您的 `.runway-config.json`：

```bash
php vendor/bin/runway apm:init
```

**这做了什么？**
- 启动一个向导，询问原始指标来自哪里（来源）和处理后的数据去哪里（目的地）。
- 默认是 SQLite——例如，`sqlite:/tmp/apm_metrics.sqlite` 用于来源，另一个用于目的地。
- 您将得到像这样的配置：
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

> 这个过程还会询问您是否要运行此设置的迁移。如果这是您第一次设置，答案是肯定的。

**为什么有两个位置？**
原始指标堆积很快（想想未过滤的日志）。工作人员将它们处理成结构化的目的地，用于仪表板。保持整洁！

### 3. 使用工作人员处理指标

工作人员将原始指标转换为仪表板就绪数据。运行一次：

```bash
php vendor/bin/runway apm:worker
```

**它在做什么？**
- 从您的来源读取（例如，`apm_metrics.sqlite`）。
- 处理最多 100 个指标（默认批次大小）到您的目的地。
- 在完成或没有指标剩余时停止。

**保持运行**
对于实时应用，您希望连续处理。以下是您的选项：

- **守护进程模式**：
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  永久运行，处理指标。适合开发或小型设置。

- **Crontab**：
  将此添加到您的 crontab（`crontab -e`）：
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  每分钟触发——适合生产。

- **Tmux/Screen**：
  启动一个可分离的会话：
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B，然后 D 来分离；`tmux attach -t apm-worker` 来重新连接
  ```
  即使您注销，也保持运行。

- **自定义调整**：
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`：一次处理 50 个指标。
  - `--max_messages 1000`：处理 1000 个指标后停止。
  - `--timeout 300`：5 分钟后退出。

**为什么费事？**
没有工作人员，您的仪表板是空的。它是原始日志和可操作洞察之间的桥梁。

### 4. 启动仪表板

查看您的应用的生命体征：

```bash
php vendor/bin/runway apm:dashboard
```

**这是什么？**
- 在 `http://localhost:8001/apm/dashboard` 上启动一个 PHP 服务器。
- 显示请求日志、缓慢路由、错误率等。

**自定义它**：
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`：从任何 IP 访问（方便远程查看）。
- `--port 8080`：如果 8001 被占用，使用不同端口。
- `--php-path`：如果 PHP 不在 PATH 中，指向它。

在浏览器中访问 URL 进行探索！

#### 生产模式

对于生产环境，您可能需要尝试一些技术来运行仪表板，因为可能有防火墙和其他安全措施。以下是一些选项：

- **使用反向代理**：设置 Nginx 或 Apache 来转发请求到仪表板。
- **SSH 隧道**：如果您能 SSH 到服务器，使用 `ssh -L 8080:localhost:8001 youruser@yourserver` 将仪表板隧道到您的本地机器。
- **VPN**：如果您的服务器在 VPN 后面，连接到它并直接访问仪表板。
- **配置防火墙**：为您的 IP 或服务器网络打开端口 8001（或您设置的任何端口）。
- **配置 Apache/Nginx**：如果您在应用前面有一个 web 服务器，您可以配置它到一个域或子域。如果您这样做，将文档根设置为 `/path/to/your/project/vendor/flightphp/apm/dashboard`。

#### 想要不同的仪表板？

如果您想的话，可以构建自己的仪表板！查看 `vendor/flightphp/apm/src/apm/presenter` 目录，获取呈现数据供您自己的仪表板的想法！

## 仪表板功能

仪表板是您的 APM 总部——以下是您将看到的内容：

- **请求日志**：每个请求，包括时间戳、URL、响应代码和总时间。点击“详情”查看中间件、查询和错误。
- **最慢请求**：占用时间最多的前 5 个请求（例如，“/api/heavy” 耗时 2.5 秒）。
- **最慢路由**：按平均时间排序的前 5 个路由——非常适合发现模式。
- **错误率**：请求失败的百分比（例如，2.3% 的 500 错误）。
- **延迟百分位**：95th (p95) 和 99th (p99) 响应时间——了解您的最坏情况。
- **响应代码图表**：可视化 200s、404s、500s 随时间变化。
- **长时间查询/中间件**：前 5 个缓慢数据库调用和中间件层。
- **缓存命中/未命中**：您的缓存节省时间的频率。

**额外功能**：
- 按“最近一小时”、“最近一天”或“最近一周”过滤。
- 切换暗模式，用于深夜会话。

**示例**：
对 `/users` 的请求可能显示：
- 总时间：150ms
- 中间件：`AuthMiddleware->handle` (50ms)
- 查询：`SELECT * FROM users` (80ms)
- 缓存：命中 `user_list` (5ms)

## 添加自定义事件

追踪任何内容——如 API 调用或支付过程：

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**它在哪里显示？**
在仪表板的请求详情下“自定义事件”——可展开并带有漂亮的 JSON 格式。

**用例**：
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->trigger('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
现在您会看到那个 API 是否拖累您的应用！

## 数据库监控

像这样追踪 PDO 查询：

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite', null, null, null, true); // <-- True 要求启用 APM 中的追踪。
$Apm->addPdoConnection($pdo);
```

**您得到什么**：
- 查询文本（例如，`SELECT * FROM users WHERE id = ?`）
- 执行时间（例如，0.015s）
- 行数（例如，42）

**注意**：
- **可选**：如果您不需要 DB 追踪，可以跳过。
- **仅 PdoWrapper**：核心 PDO 尚未钩住——敬请期待！
- **性能警告**：在数据库密集型站点上记录每个查询可能会减慢速度。使用采样（`$Apm = new Apm($ApmLogger, 0.1)`）来减轻负载。

**示例输出**：
- 查询：`SELECT name FROM products WHERE price > 100`
- 时间：0.023s
- 行数：15

## 工作人员选项

调整工作人员以符合您的喜好：

- `--timeout 300`：5 分钟后停止——适合测试。
- `--max_messages 500`：限制为 500 个指标——保持有限。
- `--batch_size 200`：一次处理 200 个——平衡速度和内存。
- `--daemon`：永不停止运行——适合实时监控。

**示例**：
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
运行一小时，一次处理 100 个指标。

## 请求 ID 在应用中

每个请求都有一个唯一的请求 ID 用于追踪。您可以在应用中使用此 ID 来关联日志和指标。例如，您可以将请求 ID 添加到一个错误页面：

```php
Flight::map('error', function($message) {
	// 获取请求 ID 从响应头 X-Flight-Request-Id
	$requestId = Flight::response()->getHeader('X-Flight-Request-Id');

	// 此外，您可以从 Flight 变量中获取它
	// 这个方法在 Swoole 或其他异步平台中可能无法正常工作。
	// $requestId = Flight::get('apm.request_id');
	
	echo "Error: $message (Request ID: $requestId)";
});
```

## 升级

如果您正在升级到 APM 的新版本，数据库迁移可能需要运行。您可以通过运行以下命令来执行：

```bash
php vendor/bin/runway apm:migrate
```
这将运行任何需要的迁移，以将数据库模式更新到最新版本。

**注意：** 如果您的 APM 数据库很大，这些迁移可能需要一些时间。您可能希望在非高峰时段运行此命令。

## 清除旧数据

为了保持数据库整洁，您可以清除旧数据。这在运行繁忙的应用并希望保持数据库大小可管理时特别有用。
您可以通过运行以下命令来执行：

```bash
php vendor/bin/runway apm:purge
```
这将从数据库中删除所有超过 30 天的数据。您可以通过将不同值传递给 `--days` 选项来调整天数：

```bash
php vendor/bin/runway apm:purge --days 7
```
这将从数据库中删除所有超过 7 天的数据。

## 故障排除

遇到问题？试试这些：

- **仪表板无数据？**
  - 工作人员运行了吗？检查 `ps aux | grep apm:worker`。
  - 配置路径匹配吗？验证 `.runway-config.json` 中的 DSN 指向真实文件。
  - 手动运行 `php vendor/bin/runway apm:worker` 来处理待处理的指标。

- **工作人员错误？**
  - 查看您的 SQLite 文件（例如，`sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`）。
  - 检查 PHP 日志中的堆栈跟踪。

- **仪表板无法启动？**
  - 端口 8001 被占用吗？使用 `--port 8080`。
  - PHP 未找到？使用 `--php-path /usr/bin/php`。
  - 防火墙阻塞？打开端口或使用 `--host localhost`。

- **太慢？**
  - 降低采样率：`$Apm = new Apm($ApmLogger, 0.05)` (5%)。
  - 减少批次大小：`--batch_size 20`。

- **未追踪异常/错误？**
  - 如果您启用了 [Tracy](https://tracy.nette.org/) 项目，它将覆盖 Flight 的错误处理。您需要禁用 Tracy，然后确保 `Flight::set('flight.handle_errors', true);` 已设置。

- **未追踪数据库查询？**
  - 确保您正在使用 `PdoWrapper` 进行数据库连接。
  - 确保在构造函数中的最后一个参数是 `true`。