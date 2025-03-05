# シンプルジョブキュー

シンプルジョブキューは、非同期でジョブを処理するために使用できるライブラリです。beanstalkd、MySQL/MariaDB、SQLite、およびPostgreSQLで使用できます。

## インストール
```bash
composer require n0nag0n/simple-job-queue
```

## 使用法

これを機能させるには、キューにジョブを追加する方法と、ジョブを処理する方法（ワーカー）が必要です。以下は、ジョブをキューに追加する方法と、そのジョブを処理する方法の例です。


## Flightへの追加

これをFlightに追加するのは簡単で、`register()`メソッドを使用して行います。以下は、これをFlightに追加する方法の例です。

```php
<?php
require 'vendor/autoload.php';

// beanstalkdを使用する場合は、['mysql']を['beanstalkd']に変更してください
Flight::register('queue', n0nag0n\Job_Queue::class, ['mysql'], function($Job_Queue) {
	// Flight::db()で既にPDO接続がある場合
	$Job_Queue->addQueueConnection(Flight::db());

	// または、beanstalkd/Pheanstalkを使用している場合
	$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
	$Job_Queue->addQueueConnection($pheanstalk);
});
```

### 新しいジョブの追加

ジョブを追加する場合、パイプライン（キュー）を指定する必要があります。これは、RabbitMQのチャネルやbeanstalkdのチューブに相当します。

```php
<?php
Flight::queue()->selectPipeline('send_important_emails');
Flight::queue()->addJob(json_encode([ 'something' => 'that', 'ends' => 'up', 'a' => 'string' ]));
```

### ワーカーの実行

ここにワーカーを実行する方法のサンプルファイルがあります。
```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// PDO接続
$PDO = new PDO('mysql:dbname=testdb;host=127.0.0.1', 'user', 'pass');
$Job_Queue->addQueueConnection($PDO);

// または、beanstalkd/Pheanstalkを使用している場合
$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
$Job_Queue->addQueueConnection($pheanstalk);

$Job_Queue->watchPipeline('send_important_emails');
while(true) {
	$job = $Job_Queue->getNextJobAndReserve();

	// あなたが夜に良く眠れるように調整してください（データベースキューのみ、beanstalkdではこのif文は必要ありません）
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
			// これはレディキューから取り出し、後で拾って「キック」できる別のキューに入れます。
			$Job_Queue->buryJob($job);
		}
	} catch(Exception $e) {
		$Job_Queue->buryJob($job);
	}
}
```

# Supervisordを使用した長いプロセスの処理

Supervisordは、ワーカープロセスが継続的に実行されることを保証するプロセス制御システムです。シンプルジョブキューワーカーの設定に関するより完全なガイドは次のとおりです。

### Supervisordのインストール

```bash
# Ubuntu/Debian上
sudo apt-get install supervisor

# CentOS/RHEL上
sudo yum install supervisor

# Homebrewを使用したmacOS上
brew install supervisor
```

### ワーカースクリプトの作成

最初に、ワーカーコードを専用のPHPファイルに保存します。

```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// PDO接続
$PDO = new PDO('mysql:dbname=your_database;host=127.0.0.1', 'username', 'password');
$Job_Queue->addQueueConnection($PDO);

// 監視するパイプラインを設定
$Job_Queue->watchPipeline('send_important_emails');

// ワーカーの開始をログに記録
echo date('Y-m-d H:i:s') . " - ワーカーが開始されました\n";

while(true) {
    $job = $Job_Queue->getNextJobAndReserve();

    if(empty($job)) {
        usleep(500000); // 0.5秒間スリープ
        continue;
    }

    echo date('Y-m-d H:i:s') . " - ジョブ {$job['id']}を処理中\n";
    $payload = json_decode($job['payload'], true);

    try {
        $result = doSomethingThatDoesSomething($payload);

        if($result === true) {
            $Job_Queue->deleteJob($job);
            echo date('Y-m-d H:i:s') . " - ジョブ {$job['id']}は正常に完了しました\n";
        } else {
            $Job_Queue->buryJob($job);
            echo date('Y-m-d H:i:s') . " - ジョブ {$job['id']}が失敗し、埋められました\n";
        }
    } catch(Exception $e) {
        $Job_Queue->buryJob($job);
        echo date('Y-m-d H:i:s') . " - ジョブ {$job['id']}の処理中に例外が発生しました: {$e->getMessage()}\n";
    }
}
```

### Supervisordの設定

ワーカーのための設定ファイルを作成します。

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

### 主要な設定オプション:

- `command`: ワーカーを実行するコマンド
- `directory`: ワーカーの作業ディレクトリ
- `autostart`: supervisordが起動するときに自動的に開始
- `autorestart`: プロセスが終了した場合に自動的に再起動
- `startretries`: 失敗した場合に再起動を試みる回数
- `stderr_logfile`/`stdout_logfile`: ログファイルの場所
- `user`: プロセスを実行するシステムユーザー
- `numprocs`: 実行するワーカーインスタンスの数
- `process_name`: 複数のワーカープロセスの命名形式

### Supervisorctlによるワーカーの管理

設定を作成または変更した後：

```bash
# supervisor設定を再読み込み
sudo supervisorctl reread
sudo supervisorctl update

# 特定のワーカープロセスを制御
sudo supervisorctl start email_worker:*
sudo supervisorctl stop email_worker:*
sudo supervisorctl restart email_worker:*
sudo supervisorctl status email_worker:*
```

### 複数のパイプラインの実行

複数のパイプラインの場合、別々のワーカーファイルと設定を作成します。

```ini
[program:email_worker]
command=php /path/to/email_worker.php
# ... その他の設定 ...

[program:notification_worker]
command=php /path/to/notification_worker.php
# ... その他の設定 ...
```

### 監視とログ

ワーカーのアクティビティを監視するために、ログを確認します。

```bash
# ログを表示
sudo tail -f /var/log/simple_job_queue.log

# ステータスを確認
sudo supervisorctl status
```

この設定により、ジョブワーカーはクラッシュ、サーバーの再起動、またはその他の問題が発生しても継続的に実行されることが保証され、プロダクション環境におけるキューシステムの信頼性が高まります。