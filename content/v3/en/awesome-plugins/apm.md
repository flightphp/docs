# FlightPHP APM Documentation

Welcome to FlightPHP APM—your app’s personal performance coach! This guide is your roadmap to setting up, using, and mastering Application Performance Monitoring (APM) with FlightPHP. Whether you’re hunting down slow requests or just want to geek out over latency charts, we’ve got you covered. Let’s make your app faster, your users happier, and your debugging sessions a breeze!

## Why APM Matters

Picture this: your app is a busy restaurant. Without a way to track how long orders take or where the kitchen’s bogging down, you’re guessing why customers are leaving grumpy. APM is your sous-chef—it watches every step, from incoming requests to database queries, and flags anything slowing you down. Slow pages lose users (studies say 53% bounce if a site takes over 3 seconds to load!), and APM helps you catch those issues *before* they sting. It’s proactive peace of mind—fewer “why is this broken?” moments, more “look how slick this runs!” wins.

## Installation

Get started with Composer:

```bash
composer require flightphp/apm
```

You’ll need:
- **PHP 7.4+**: Keeps us compatible with LTS Linux distros while supporting modern PHP.
- **[FlightPHP Core](https://github.com/flightphp/core) v3.15+**: The lightweight framework we’re boosting.

## Getting Started

Here’s your step-by-step to APM awesomeness:

### 1. Register the APM

Drop this into your `index.php` or a `services.php` file to start tracking:

```php
use flight\apm\logger\LoggerFactory;
use flight\Apm;

$ApmLogger = LoggerFactory::create(__DIR__ . '/../../.runway-config.json');
$Apm = new Apm($ApmLogger);
$Apm->bindEventsToFlightInstance($app);
```

**What’s happening here?**
- `LoggerFactory::create()` grabs your config (more on that soon) and sets up a logger—SQLite by default.
- `Apm` is the star—it listens to Flight’s events (requests, routes, errors, etc.) and collects metrics.
- `bindEventsToFlightInstance($app)` ties it all to your Flight app.

**Pro Tip: Sampling**
If your app’s busy, logging *every* request might overload things. Use a sample rate (0.0 to 1.0):

```php
$Apm = new Apm($ApmLogger, 0.1); // Logs 10% of requests
```

This keeps performance snappy while still giving you solid data.

### 2. Configure It

Run this to whip up your `.runway-config.json`:

```bash
php vendor/bin/runway apm:init
```

**What’s this do?**
- Launches a wizard asking where raw metrics come from (source) and where processed data goes (destination).
- Default is SQLite—e.g., `sqlite:/tmp/apm_metrics.sqlite` for source, another for destination.
- You’ll end up with a config like:
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

**Why two locations?**
Raw metrics pile up fast (think unfiltered logs). The worker processes them into a structured destination for the dashboard. Keeps things tidy!

### 3. Process Metrics with the Worker

The worker turns raw metrics into dashboard-ready data. Run it once:

```bash
php vendor/bin/runway apm:worker
```

**What’s it doing?**
- Reads from your source (e.g., `apm_metrics.sqlite`).
- Processes up to 100 metrics (default batch size) into your destination.
- Stops when done or if no metrics are left.

**Keep It Running**
For live apps, you’ll want continuous processing. Here are your options:

- **Daemon Mode**:
  ```bash
  php vendor/bin/runway apm:worker --daemon
  ```
  Runs forever, processing metrics as they come. Great for dev or small setups.

- **Crontab**:
  Add this to your crontab (`crontab -e`):
  ```bash
  * * * * * php /path/to/project/vendor/bin/runway apm:worker
  ```
  Fires every minute—perfect for production.

- **Tmux/Screen**:
  Start a detachable session:
  ```bash
  tmux new -s apm-worker
  php vendor/bin/runway apm:worker --daemon
  # Ctrl+B, then D to detach; `tmux attach -t apm-worker` to reconnect
  ```
  Keeps it running even if you log out.

- **Custom Tweaks**:
  ```bash
  php vendor/bin/runway apm:worker --batch_size 50 --max_messages 1000 --timeout 300
  ```
  - `--batch_size 50`: Process 50 metrics at a time.
  - `--max_messages 1000`: Stop after 1000 metrics.
  - `--timeout 300`: Quit after 5 minutes.

**Why bother?**
Without the worker, your dashboard’s empty. It’s the bridge between raw logs and actionable insights.

### 4. Launch the Dashboard

See your app’s vitals:

```bash
php vendor/bin/runway apm:dashboard
```

**What’s this?**
- Spins up a PHP server at `http://localhost:8001/apm/dashboard`.
- Shows request logs, slow routes, error rates, and more.

**Customize It**:
```bash
php vendor/bin/runway apm:dashboard --host 0.0.0.0 --port 8080 --php-path=/usr/local/bin/php
```
- `--host 0.0.0.0`: Accessible from any IP (handy for remote viewing).
- `--port 8080`: Use a different port if 8001’s taken.
- `--php-path`: Point to PHP if it’s not in your PATH.

Hit the URL in your browser and explore!

#### Production Mode

For production, you may have to try a few techniques to get the dashboard running since there are probably firewalls and other security measures in place. Here are a few options:

- **Use a Reverse Proxy**: Set up Nginx or Apache to forward requests to the dashboard.
- **SSH Tunnel**: If you can SSH into the server, use `ssh -L 8080:localhost:8001
youruser@yourserver` to tunnel the dashboard to your local machine.
- **VPN**: If your server is behind a VPN, connect to it and access the dashboard directly.
- **Configure Firewall**: Open port 8001 for your IP or the server’s network. (or whatever port you set it to).
- **Configure Apache/Nginx**: If you have a web server in front of your application, you can configure it to a domain or subdomain. If you do this, you'll set the document root to `/path/to/your/project/vendor/flightphp/apm/dashboard`

#### Want a different dashboard?

You can build your own dashboard if you want! Look at the vendor/flightphp/apm/src/apm/presenter directory for ideas on how to present the data for your own dashboard!

## Dashboard Features

The dashboard is your APM HQ—here’s what you’ll see:

- **Request Log**: Every request with timestamp, URL, response code, and total time. Click “Details” for middleware, queries, and errors.
- **Slowest Requests**: Top 5 requests hogging time (e.g., “/api/heavy” at 2.5s).
- **Slowest Routes**: Top 5 routes by average time—great for spotting patterns.
- **Error Rate**: Percentage of requests failing (e.g., 2.3% 500s).
- **Latency Percentiles**: 95th (p95) and 99th (p99) response times—know your worst-case scenarios.
- **Response Code Chart**: Visualize 200s, 404s, 500s over time.
- **Long Queries/Middleware**: Top 5 slow database calls and middleware layers.
- **Cache Hit/Miss**: How often your cache saves the day.

**Extras**:
- Filter by “Last Hour,” “Last Day,” or “Last Week.”
- Toggle dark mode for those late-night sessions.

**Example**:
A request to `/users` might show:
- Total Time: 150ms
- Middleware: `AuthMiddleware->handle` (50ms)
- Query: `SELECT * FROM users` (80ms)
- Cache: Hit on `user_list` (5ms)

## Adding Custom Events

Track anything—like an API call or payment process:

```php
use flight\apm\CustomEvent;

$app->eventDispatcher()->emit('apm.custom', new CustomEvent('api_call', [
    'endpoint' => 'https://api.example.com/users',
    'response_time' => 0.25,
    'status' => 200
]));
```

**Where’s it show up?**
In the dashboard’s request details under “Custom Events”—expandable with pretty JSON formatting.

**Use Case**:
```php
$start = microtime(true);
$apiResponse = file_get_contents('https://api.example.com/data');
$app->eventDispatcher()->emit('apm.custom', new CustomEvent('external_api', [
    'url' => 'https://api.example.com/data',
    'time' => microtime(true) - $start,
    'success' => $apiResponse !== false
]));
```
Now you’ll see if that API’s dragging your app down!

## Database Monitoring

Track PDO queries like this:

```php
use flight\database\PdoWrapper;

$pdo = new PdoWrapper('sqlite:/path/to/db.sqlite');
$Apm->addPdoConnection($pdo);
```

**What You Get**:
- Query text (e.g., `SELECT * FROM users WHERE id = ?`)
- Execution time (e.g., 0.015s)
- Row count (e.g., 42)

**Heads Up**:
- **Optional**: Skip this if you don’t need DB tracking.
- **PdoWrapper Only**: Core PDO isn’t hooked yet—stay tuned!
- **Performance Warning**: Logging every query on a DB-heavy site can slow things down. Use sampling (`$Apm = new Apm($ApmLogger, 0.1)`) to lighten the load.

**Example Output**:
- Query: `SELECT name FROM products WHERE price > 100`
- Time: 0.023s
- Rows: 15

## Worker Options

Tune the worker to your liking:

- `--timeout 300`: Stops after 5 minutes—good for testing.
- `--max_messages 500`: Caps at 500 metrics—keeps it finite.
- `--batch_size 200`: Processes 200 at once—balances speed and memory.
- `--daemon`: Runs non-stop—ideal for live monitoring.

**Example**:
```bash
php vendor/bin/runway apm:worker --daemon --batch_size 100 --timeout 3600
```
Runs for an hour, processing 100 metrics at a time.

## Troubleshooting

Stuck? Try these:

- **No Dashboard Data?**
  - Is the worker running? Check `ps aux | grep apm:worker`.
  - Config paths match? Verify `.runway-config.json` DSNs point to real files.
  - Run `php vendor/bin/runway apm:worker` manually to process pending metrics.

- **Worker Errors?**
  - Peek at your SQLite files (e.g., `sqlite3 /tmp/apm_metrics.sqlite "SELECT * FROM apm_metrics_log LIMIT 5"`).
  - Check PHP logs for stack traces.

- **Dashboard Won’t Start?**
  - Port 8001 in use? Use `--port 8080`.
  - PHP not found? Use `--php-path /usr/bin/php`.
  - Firewall blocking? Open the port or use `--host localhost`.

- **Too Slow?**
  - Lower the sample rate: `$Apm = new Apm($ApmLogger, 0.05)` (5%).
  - Reduce batch size: `--batch_size 20`.