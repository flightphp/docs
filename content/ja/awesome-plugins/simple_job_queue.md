# シンプルジョブキュー

シンプルジョブキューは、ジョブを非同期で処理するために使用できるライブラリです。beanstalkd、MySQL/MariaDB、SQLite、およびPostgreSQLと一緒に使用することができます。

## インストール
```bash
composer require n0nag0n/simple-job-queue
```

## 使用法

これを機能させるためには、ジョブをキューに追加する方法と、ジョブを処理する方法（ワーカー）が必要です。以下に、ジョブをキューに追加する方法と、そのジョブを処理する方法の例を示します。

## Flightへの追加

これをFlightに追加するのは簡単で、`register()`メソッドを使用して行います。以下は、これをFlightに追加する方法の例です。

```php
<?php
require 'vendor/autoload.php';

Flight::register('queue', n0nag0n\Job_Queue::class, ['mysql'], function($Job_Queue) {
	// すでにFlight::db()でPDO接続がある場合
	$Job_Queue->addQueueConnection(Flight::db());

	// またはbeanstalkd/Pheanstalkを使用している場合
	$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
	$Job_Queue->addQueueConnection($pheanstalk);
});
```

### 新しいジョブの追加

ジョブを追加する際は、パイプライン（キュー）を指定する必要があります。これは、RabbitMQのチャネルやbeanstalkdのチューブに相当します。

```php
<?php
Flight::queue()->selectPipeline('send_important_emails');
Flight::queue()->addJob(json_encode([ 'something' => 'that', 'ends' => 'up', 'a' => 'string' ]));
```

### ワーカーの実行

ワーカーを実行する方法の例ファイルはこちらです。
```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// PDO接続
$PDO = new PDO('mysql:dbname=testdb;host=127.0.0.1', 'user', 'pass');
$Job_Queue->addQueueConnection($PDO);

// またはbeanstalkd/Pheanstalkを使用している場合
$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
$Job_Queue->addQueueConnection($pheanstalk);

$Job_Queue->watchPipeline('send_important_emails');
while(true) {
	$job = $Job_Queue->getNextJobAndReserve();

	// あなたが夜中に気持ちよく眠れるように調整します（データベースキューのみ、beanstalkdはこのif文を必要としません）
	if(empty($job)) {
		usleep(500000);
		continue;
	}

	echo "処理中 {$job['id']}\n";
	$payload = json_decode($job['payload'], true);

	try {
		$result = doSomethingThatDoesSomething($payload);

		if($result === true) {
			$Job_Queue->deleteJob($job);
		} else {
			// これは準備キューから取り出し、後で拾われることができる別のキューに入れます。
			$Job_Queue->buryJob($job);
		}
	} catch(Exception $e) {
		$Job_Queue->buryJob($job);
	}
}
```

### 長いプロセスの処理

Supervisordはあなたにとって最適な選択となるでしょう。これを実装する方法についての多くの記事を探してみてください。少し追加の設定が必要ですが、プロセスを稼働させ続ける価値があります。