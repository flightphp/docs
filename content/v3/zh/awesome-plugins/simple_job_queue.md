# 简单作业队列

简单作业队列是一个可以用于异步处理作业的库。它可以与 beanstalkd、MySQL/MariaDB、SQLite 和 PostgreSQL 一起使用。

## 安装
```bash
composer require n0nag0n/simple-job-queue
```

## 使用

为了使其正常工作，您需要有一种方式将作业添加到队列中，以及一种处理作业的方式（一个工作程序）。下面是如何将作业添加到队列以及如何处理作业的示例。

## 添加到 Flight

将其添加到 Flight 是简单的，并使用 `register()` 方法完成。下面是如何将其添加到 Flight 的示例。

```php
<?php
require 'vendor/autoload.php';

Flight::register('queue', n0nag0n\Job_Queue::class, ['mysql'], function($Job_Queue) {
	// 如果您已经在 Flight::db() 上有一个 PDO 连接；
	$Job_Queue->addQueueConnection(Flight::db());

	// 或者如果您使用的是 beanstalkd/Pheanstalk
	$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
	$Job_Queue->addQueueConnection($pheanstalk);
});
```

### 添加新作业

当您添加作业时，您需要指定一个管道（队列）。这可与 RabbitMQ 中的通道或 beanstalkd 中的管道相提并论。

```php
<?php
Flight::queue()->selectPipeline('send_important_emails');
Flight::queue()->addJob(json_encode([ 'something' => 'that', 'ends' => 'up', 'a' => 'string' ]));
```

### 运行工作程序

这是一个如何运行工作程序的示例文件。
```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// PDO 连接
$PDO = new PDO('mysql:dbname=testdb;host=127.0.0.1', 'user', 'pass');
$Job_Queue->addQueueConnection($PDO);

// 或者如果您使用的是 beanstalkd/Pheanstalk
$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
$Job_Queue->addQueueConnection($pheanstalk);

$Job_Queue->watchPipeline('send_important_emails');
while(true) {
	$job = $Job_Queue->getNextJobAndReserve();

	// 调整为适合您晚上睡得更好的任何方法（仅适用于数据库队列，如果没有此语句，beanstalkd 不需要这样）
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
			// 这会将其移出准备好的队列，并放入可以稍后被领取和“踢”的另一个队列中。
			$Job_Queue->buryJob($job);
		}
	} catch(Exception $e) {
		$Job_Queue->buryJob($job);
	}
}
```

### 处理长过程

Supervisord 将是您的好选择。查阅关于如何实现此功能的许多文章。这需要一些额外的配置，但值得去保持过程正常运行。