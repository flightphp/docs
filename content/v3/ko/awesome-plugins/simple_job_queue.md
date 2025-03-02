# 간단한 작업 큐

간단한 작업 큐는 비동기적으로 작업을 처리하는 데 사용할 수 있는 라이브러리입니다. beanstalkd, MySQL/MariaDB, SQLite 및 PostgreSQL과 함께 사용할 수 있습니다.

## 설치
```bash
composer require n0nag0n/simple-job-queue
```

## 사용법

이것이 작동하려면 큐에 작업을 추가할 수 있는 방법과 작업을 처리할 수 있는 방법(작업자)이 필요합니다. 아래는 큐에 작업을 추가하는 방법과 작업을 처리하는 방법에 대한 예입니다.

## Flight에 추가하기

이것을 Flight에 추가하는 것은 간단하며 `register()` 메서드를 사용하여 수행됩니다. 아래는 이것을 Flight에 추가하는 방법에 대한 예입니다.

```php
<?php
require 'vendor/autoload.php';

Flight::register('queue', n0nag0n\Job_Queue::class, ['mysql'], function($Job_Queue) {
	// Flight::db()에서 이미 PDO 연결이 있는 경우
	$Job_Queue->addQueueConnection(Flight::db());

	// 또는 beanstalkd/Pheanstalk를 사용하는 경우
	$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
	$Job_Queue->addQueueConnection($pheanstalk);
});
```

### 새 작업 추가하기

작업을 추가할 때는 파이프라인(큐)을 지정해야 합니다. 이는 RabbitMQ의 채널 또는 beanstalkd의 튜브에 비유할 수 있습니다.

```php
<?php
Flight::queue()->selectPipeline('send_important_emails');
Flight::queue()->addJob(json_encode([ 'something' => 'that', 'ends' => 'up', 'a' => 'string' ]));
```

### 작업자 실행하기

작업자를 실행하는 방법에 대한 예제 파일입니다.
```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// PDO 연결
$PDO = new PDO('mysql:dbname=testdb;host=127.0.0.1', 'user', 'pass');
$Job_Queue->addQueueConnection($PDO);

// 또는 beanstalkd/Pheanstalk를 사용하는 경우
$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
$Job_Queue->addQueueConnection($pheanstalk);

$Job_Queue->watchPipeline('send_important_emails');
while(true) {
	$job = $Job_Queue->getNextJobAndReserve();

	// 당신의 밤을 더 편안하게 하는 것으로 조정하세요(데이터베이스 큐의 경우만 해당, beanstalkd는 이 if 문이 필요하지 않습니다)
	if(empty($job)) {
		usleep(500000);
		continue;
	}

	echo "처리 중 {$job['id']}\n";
	$payload = json_decode($job['payload'], true);

	try {
		$result = doSomethingThatDoesSomething($payload);

		if($result === true) {
			$Job_Queue->deleteJob($job);
		} else {
			// 이것은 준비 큐에서 제거하여 나중에 가져오거나 "차기"할 수 있는 다른 큐에 넣습니다.
			$Job_Queue->buryJob($job);
		}
	} catch(Exception $e) {
		$Job_Queue->buryJob($job);
	}
}
```

### 긴 프로세스 처리하기

Supervisord는 당신의 짜임새가 될 것입니다. 이를 구현하는 방법에 대한 많은, 많은 기사를 찾아보세요. 약간의 추가 구성이 필요하지만, 프로세스를 지속적으로 실행하기 위해 노력할 가치가 있습니다.