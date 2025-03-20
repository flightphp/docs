# FlightPHP APM 文档

欢迎来到 FlightPHP APM——你应用的个人性能教练！本指南是你设置、使用和掌握应用性能监控（APM）与 FlightPHP 的路线图。无论你是在追踪慢请求，还是想要对延迟图表感到著迷，我们都能帮你搞定。让我们使你的应用更快，让用户更开心，让调试过程变得轻松！

## 为什么 APM 重要

想象一下：你的应用就像一家繁忙的餐厅。没有办法追踪订单需要多长时间或厨房的瓶颈在哪里，你就只能猜测客户为什么会不高兴地离开。APM 就是你的副厨师——它监控每一个步骤，从进入的请求到数据库查询，并标记任何让你放慢速度的东西。慢页面会失去用户（研究表明，如果网站加载超过 3 秒，53% 的用户会离开！），APM 帮助你在问题出现之前就发现这些问题。这是一种主动的安心——减少“为什么会出错？”的时刻，增加“看看这个多流畅！”的胜利。

## 安装

从 Composer 开始：

```bash
composer require flightphp/apm
```

你需要：
- **PHP 7.4+**：保持与 LTS Linux 发行版的兼容，同时支持现代 PHP。
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**：我们正在提升的轻量级框架。

## 开始使用

这是你逐步达到 APM 极致的步骤：

### 1. 注册 APM

将以下代码放入 `index.php` 或 `services.php` 文件中开始追踪：

```php
use flight\apm\logger\LoggerFactory;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);
```

**这里发生了什么？**
- `LoggerFactory::create()` 获取你的配置（稍后会详细介绍），并设置一个记录器——默认是 SQLite。
- `Apm` 是明星——它监听 Flight 的事件（请求、路由、错误等）并收集指标。
- `bindEventsToFlightInstance($app)` 将所有内容绑定到你的 Flight 应用上。

**专业提示：采样**
如果你的应用很忙，记录 *每一个* 请求可能会过载。使用采样率（0.0 到 1.0）：

```php
$Apm = new Apm($ApmLogger, 0.1); // 记录 10% 的请求
```

这保持性能灵活，同时仍然给你提供坚实的数据。

### 2. 配置

运行此命令生成你的 `.runway-config.json`：

```bash
php vendor/bin/runway apm:init
```

**这做了什么？**
- 启动一个向导，询问原始指标来源（源）和处理后数据去向（目标）。
- 默认是 SQLite——例如，`sqlite:/tmp/apm_metrics.sqlite` 作为源，另一个作为目标。
- 你会得到如下配置：
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

**为什么要两个位置？**
原始指标堆积得很快（想想未过滤的日志）。工作进程将它们处理成一个结构化的目标，以便于仪表盘显示。保持整洁！

### 3. 使用工作进程处理指标

工作进程将原始指标转换为仪表盘准备的数据。运行一次：

```bash
php vendor/bin/runway apm:worker
```

**它在做什么？**
- 从你的源读取（例如，`apm_metrics.sqlite`）。
- 将多达 100 个指标（默认批量大小）处理到你的目标。
- 完成时停止或如果没有剩余指标。

**保持运行**
对于实时应用，你会希望进行持续处理。这里是你的选择：

- **守护进程模式**：
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  永久运行，处理进来的指标。非常适合开发或小型设置。

- **Cron 表**：
  将以下内容添加到你的 Cron 表（`crontab -e`）：
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  每分钟执行一次——非常适合生产环境。

- **Tmux/Screen**：
  启动一个可分离的会话：
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B，然后 D 进行分离；`tmux attach -t apm-worker` 重新连接
  ```
  即使你注销也能保持运行。

- **自定义调整**：
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: 一次处理 50 个指标。
  - `--max_messages 1000`: 在处理 1000 个指标后停止。
  - `--timeout 300`: 在 5 分钟后退出。

**为什么要费心？**
没有工作进程，你的仪表盘将为空。它是原始日志和可操作洞察之间的桥梁。

### 4. 启动仪表盘

查看你应用的生命体征：

```bash
php vendor/bin/runway apm:dashboard
```

**这是什么？**
- 在 `http://localhost:8001/apm/dashboard` 上启动一个 PHP 服务器。
- 显示请求日志、慢速路由、错误率等信息。

**自定义它**：
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: 可以从任何 IP 访问（方便远程查看）。
- `--port 8080`: 如果 8001 被占用，则使用不同的端口。
- `--php-path`: 如果 PHP 不在你的 PATH 中，请指向 PHP。

在浏览器中访问该 URL 并进行探索！

#### 生产模式

在生产环境中，你可能需要尝试一些技巧以使仪表盘正常运行，因为可能有防火墙和其他安全措施。以下是一些选项：

- **使用反向代理**：设置 Nginx 或 Apache 将请求转发到仪表盘。
- **SSH 隧道**：如果你可以 SSH 进入服务器，使用 `ssh -L 8080:localhost:8001 youruser@yourserver` 将仪表盘隧道到本地机器。
- **VPN**：如果你的服务器在 VPN 后面，连接到 VPN 并直接访问仪表盘。
- **配置防火墙**：为你的 IP 或服务器网络打开 8001 端口（或你设置的任意端口）。
- **配置 Apache/Nginx**：如果你在应用程序前有 Web 服务器，可以将其配置到域名或子域。如果这样做，你需要将文档根设置为 `/path/to/your/project/vendor/flightphp/apm/dashboard`。

#### 想要不同的仪表盘？

如果你愿意，你可以构建自己的仪表盘！查看 `vendor/flightphp/apm/src/apm/presenter` 目录，获取关于如何呈现数据以供自己仪表盘的想法！

## 仪表盘功能

仪表盘是你的 APM HQ——这里是你将看到的内容：

- **请求日志**：每个请求的时间戳、URL、响应代码和总时间。点击“详细信息”可查看中间件、查询和错误。
- **最慢请求**：占用时间最多的前 5 个请求（例如，“/api/heavy”耗时 2.5 秒）。
- **最慢路由**：按平均时间排序的前 5 个路由——非常适合发现模式。
- **错误率**：失败请求的百分比（例如，2.3% 500s）。
- **延迟百分位**：95%（p95）和99%（p99）响应时间——了解你的最坏情况。
- **响应代码图表**：可视化一段时间内的 200s、404s、500s。
- **长查询/中间件**：前 5 个慢数据库调用和中间件层。
- **缓存命中/未命中**：你的缓存有多常发挥作用。

**额外功能**：
- 按“最后一小时”、“昨天”或“上周”进行过滤。
- 切换深色模式以适应那些深夜的工作。

**示例**：
对 `/users` 的请求可能显示：
- 总时间：150ms
- 中间件： `AuthMiddleware->handle`（50ms）
- 查询： `SELECT * FROM users`（80ms）
- 缓存： 在 `user_list` 上命中（5ms）

## 添加自定义事件

追踪任何内容，例如 API 调用或支付过程：

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->emit('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**它显示在哪里？**
在仪表盘的请求详细信息下方“自定义事件”中——可展开并以漂亮的 JSON 格式显示。

**使用案例**：
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->emit('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
现在你将看到该 API 是否拖慢了你的应用！

## 数据库监控

如下追踪 PDO 查询：

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite');
$Apm->addPdoConnection($pdo);
```

**你得到了什么**：
- 查询文本（例如，`SELECT * FROM users WHERE id = ?`）
- 执行时间（例如，0.015s）
- 行计数（例如，42）

**注意**：
- **可选**：如果你不需要数据库跟踪，可以跳过此步骤。
- **仅限 PdoWrapper**：核心 PDO 尚未连接——敬请期待！
- **性能警告**：在数据库负荷较重的网站上记录每个查询可能会导致减速。使用采样（`$Apm = new Apm($ApmLogger, 0.1)`）来减轻负担。

**示例输出**：
- 查询： `SELECT name FROM products WHERE price > 100`
- 时间： 0.023s
- 行数： 15

## 工作进程选项

根据你的喜好调整工作进程：

- `--timeout 300`: 在 5 分钟后停止——适合测试。
- `--max_messages 500`: 限制在 500 个指标——保持有限。
- `--batch_size 200`: 一次处理 200 个——平衡速度和内存。
- `--daemon`: 持续运行——理想用于实时监控。

**示例**：
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
连续运行一个小时，每次处理 100 个指标。

## 故障排除

卡住了？试试这些方法：

- **没有仪表盘数据？**
  - 工作进程在运行吗？检查 `ps aux | grep apm:worker`。
  - 配置路径匹配吗？确保 `.runway-config.json` 中的 DSN 指向真实文件。
  - 手动运行 `php vendor/bin/runway apm:worker` 以处理待处理指标。

- **工作进程错误？**
  - 查看你的 SQLite 文件（例如，`sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`）。
  - 检查 PHP 日志是否有堆栈跟踪。

- **仪表盘无法启动？**
  - 8001 端口已经在使用？使用 `--port 8080`。
  - 找不到 PHP？使用 `--php-path /usr/bin/php`。
  - 防火墙阻止？打开端口或使用 `--host localhost`。

- **太慢了？**
  - 降低采样率： `$Apm = new Apm($ApmLogger, 0.05)`（5%）。
  - 减少批量大小： `--batch_size 20`。