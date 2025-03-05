# 简单作业队列

简单作业队列是一个可以用于异步处理作业的库。它可以与 beanstalkd、MySQL/MariaDB、SQLite 和 PostgreSQL 一起使用。

## 安装
```bash
composer require n0nag0n/simple-job-queue
```

## 用法

为了使这一切正常工作，您需要一种将作业添加到队列的方法以及一种处理作业的方法（工作者）。下面是如何将作业添加到队列以及如何处理作业的示例。

## 添加到 Flight

将其添加到 Flight 是简单的，并且使用 `register()` 方法完成。以下是如何将其添加到 Flight 的示例。

```php
<?php
require 'vendor/autoload.php';

// 如果您想使用 beanstalkd，请将 ['mysql'] 更改为 ['beanstalkd']
Flight::register('queue', n0nag0n\Job_Queue::class, ['mysql'], function($Job_Queue) {
	// 如果您已经在 Flight::db(); 上有一个 PDO 连接
	$Job_Queue->addQueueConnection(Flight::db());

	// 或者如果您正在使用 beanstalkd/Pheanstalk
	$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
	$Job_Queue->addQueueConnection($pheanstalk);
});
```

### 添加新作业

当您添加作业时，您需要指定一个管道（队列）。这可以与 RabbitMQ 中的通道或 beanstalkd 中的管道相媲美。

```php
<?php
Flight::queue()->selectPipeline('send_important_emails');
Flight::queue()->addJob(json_encode([ 'something' => 'that', 'ends' => 'up', 'a' => 'string' ]));
```

### 运行工作者

以下是如何运行工作者的示例文件。
```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// PDO 连接
$PDO = new PDO('mysql:dbname=testdb;host=127.0.0.1', 'user', 'pass');
$Job_Queue->addQueueConnection($PDO);

// 或者如果您正在使用 beanstalkd/Pheanstalk
$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
$Job_Queue->addQueueConnection($pheanstalk);

$Job_Queue->watchPipeline('send_important_emails');
while(true) {
	$job = $Job_Queue->getNextJobAndReserve();

	// 调整为任何让您晚上睡得更好的方法（仅适用于数据库队列，如果没有必要，这个 if 语句不会影响 beanstalkd）
	if(empty($job)) {
		usleep(500000);
		continue;
	}

	echo "正在处理 {$job['id']}\n";
	$payload = json_decode($job['payload'], true);

	try {
		$result = doSomethingThatDoesSomething($payload);

		if($result === true) {
			$Job_Queue->deleteJob($job);
		} else {
			// 这将其从准备好的队列中移除，并放入可以被提取和“踢出”的另一个队列中。
			$Job_Queue->buryJob($job);
		}
	} catch(Exception $e) {
		$Job_Queue->buryJob($job);
	}
}
```

# 处理长时间运行的进程与 Supervisord

Supervisord 是一个进程控制系统，确保您的工作进程持续运行。以下是与您的简单作业队列工作者一起设置它的更完整指南：

### 安装 Supervisord

```bash
# 在 Ubuntu/Debian 上
sudo apt-get install supervisor

# 在 CentOS/RHEL 上
sudo yum install supervisor

# 在 macOS 上使用 Homebrew
brew install supervisor
```

### 创建工作者脚本

首先，将您的工作者代码保存到一个专用的 PHP 文件中：

```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// PDO 连接
$PDO = new PDO('mysql:dbname=your_database;host=127.0.0.1', 'username', 'password');
$Job_Queue->addQueueConnection($PDO);

// 设置要监视的管道
$Job_Queue->watchPipeline('send_important_emails');

// 记录工作者启动
echo date('Y-m-d H:i:s') . " - 工作者已启动\n";

while(true) {
    $job = $Job_Queue->getNextJobAndReserve();

    if(empty($job)) {
        usleep(500000); // 睡眠 0.5 秒
        continue;
    }

    echo date('Y-m-d H:i:s') . " - 正在处理作业 {$job['id']}\n";
    $payload = json_decode($job['payload'], true);

    try {
        $result = doSomethingThatDoesSomething($payload);

        if($result === true) {
            $Job_Queue->deleteJob($job);
            echo date('Y-m-d H:i:s') . " - 作业 {$job['id']} 成功完成\n";
        } else {
            $Job_Queue->buryJob($job);
            echo date('Y-m-d H:i:s') . " - 作业 {$job['id']} 失败，已埋藏\n";
        }
    } catch(Exception $e) {
        $Job_Queue->buryJob($job);
        echo date('Y-m-d H:i:s') . " - 处理作业 {$job['id']} 时出现异常: {$e->getMessage()}\n";
    }
}
```

### 配置 Supervisord

为您的工作者创建一个配置文件：

```ini
[program:email_worker]
command=php /path/to/worker.php
directory=/path/to/project
autostart=true
autorestart=true
startretries=3
stderr_logfile=/var/log/simple_job_queue_err.log
stdout_logfile=/var/log/simple_job_queue.log
user=www-data
numprocs=2
process_name=%(program_name)s_%(process_num)02d
```

### 主要配置选项：

- `command`: 运行工作者的命令
- `directory`: 工作者的工作目录
- `autostart`: 当 supervisord 启动时自动启动
- `autorestart`: 如果进程退出，自动重新启动
- `startretries`: 如果启动失败，重试启动的次数
- `stderr_logfile`/`stdout_logfile`: 日志文件位置
- `user`: 以哪个系统用户身份运行进程
- `numprocs`: 要运行的工作实例数量
- `process_name`: 多个工作进程的命名格式

### 使用 Supervisorctl 管理工作者

创建或修改配置后：

```bash
# 重新加载 supervisord 配置
sudo supervisorctl reread
sudo supervisorctl update

# 控制特定的工作进程
sudo supervisorctl start email_worker:*
sudo supervisorctl stop email_worker:*
sudo supervisorctl restart email_worker:*
sudo supervisorctl status email_worker:*
```

### 运行多个管道

对于多个管道，创建单独的工作者文件和配置：

```ini
[program:email_worker]
command=php /path/to/email_worker.php
# ... 其他配置 ...

[program:notification_worker]
command=php /path/to/notification_worker.php
# ... 其他配置 ...
```

### 监控和日志

检查日志以监视工作者活动：

```bash
# 查看日志
sudo tail -f /var/log/simple_job_queue.log

# 检查状态
sudo supervisorctl status
```

该设置确保您的作业工作者在崩溃、服务器重启或其他问题后继续运行，使您的队列系统在生产环境中可靠。