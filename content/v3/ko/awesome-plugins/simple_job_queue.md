# 간단한 작업 큐

간단한 작업 큐는 비동기적으로 작업을 처리하는 데 사용할 수 있는 라이브러리입니다. beanstalkd, MySQL/MariaDB, SQLite 및 PostgreSQL과 함께 사용할 수 있습니다.

## 설치
```bash
composer require n0nag0n/simple-job-queue
```

## 사용법

이 기능이 작동하려면 작업을 큐에 추가하는 방법과 작업을 처리하는 방법(작업자)이 필요합니다. 아래는 큐에 작업을 추가하는 방법과 작업을 처리하는 방법의 예입니다.


## Flight에 추가하기

Flight에 이를 추가하는 것은 간단하며 `register()` 메서드를 사용하여 수행됩니다. 아래는 이를 Flight에 추가하는 방법의 예입니다.

```php
<?php
require 'vendor/autoload.php';

// beanstalkd를 사용하려면 ['mysql']을 ['beanstalkd']로 변경하십시오.
Flight::register('queue', n0nag0n\Job_Queue::class, ['mysql'], function($Job_Queue) {
	// Flight::db();에서 이미 PDO 연결이 있는 경우
	$Job_Queue->addQueueConnection(Flight::db());

	// 또는 beanstalkd/Pheanstalk를 사용하는 경우
	$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
	$Job_Queue->addQueueConnection($pheanstalk);
});
```

### 새로운 작업 추가하기

작업을 추가할 때는 파이프라인(큐)을 지정해야 합니다. 이는 RabbitMQ의 채널 또는 beanstalkd의 튜브에 해당합니다.

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

	// 당신이 편안하게 잘 수 있도록 조정하세요 (데이터베이스 큐에만 해당, beanstalkd는 이 조건문이 필요하지 않습니다)
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
			// 이는 준비된 큐에서 제거하고 나중에拾게 하거나 "찔러넣기"할 수 있는 다른 큐에 넣습니다.
			$Job_Queue->buryJob($job);
		}
	} catch(Exception $e) {
		$Job_Queue->buryJob($job);
	}
}
```

# Supervisord로 긴 프로세스 처리하기

Supervisord는 작업자 프로세스가 지속적으로 실행되는 것을 보장하는 프로세스 제어 시스템입니다. 다음은 간단한 작업 큐 작업자와 함께 설정하는 방법에 대한 보다 완전한 가이드입니다:

### Supervisord 설치하기

```bash
# Ubuntu/Debian에서
sudo apt-get install supervisor

# CentOS/RHEL에서
sudo yum install supervisor

# macOS에서 Homebrew를 사용하여
brew install supervisor
```

### 작업자 스크립트 만들기

우선, 작업자 코드를 전용 PHP 파일에 저장합니다:

```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// PDO 연결
$PDO = new PDO('mysql:dbname=your_database;host=127.0.0.1', 'username', 'password');
$Job_Queue->addQueueConnection($PDO);

// 감시할 파이프라인 설정
$Job_Queue->watchPipeline('send_important_emails');

// 작업자 시작 로그
echo date('Y-m-d H:i:s') . " - 작업자 시작됨\n";

while(true) {
    $job = $Job_Queue->getNextJobAndReserve();

    if(empty($job)) {
        usleep(500000); // 0.5초 동안 일시 정지
        continue;
    }

    echo date('Y-m-d H:i:s') . " - 작업 {$job['id']} 처리 중\n";
    $payload = json_decode($job['payload'], true);

    try {
        $result = doSomethingThatDoesSomething($payload);

        if($result === true) {
            $Job_Queue->deleteJob($job);
            echo date('Y-m-d H:i:s') . " - 작업 {$job['id']} 성공적으로 완료됨\n";
        } else {
            $Job_Queue->buryJob($job);
            echo date('Y-m-d H:i:s') . " - 작업 {$job['id']} 실패, 묻힘\n";
        }
    } catch(Exception $e) {
        $Job_Queue->buryJob($job);
        echo date('Y-m-d H:i:s') . " - 작업 {$job['id']} 처리 중 예외: {$e->getMessage()}\n";
    }
}
```

### Supervisord 구성하기

작업자에 대한 구성 파일을 생성합니다:

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

### 주요 구성 옵션:

- `command`: 작업자를 실행할 명령
- `directory`: 작업자의 작업 디렉토리
- `autostart`: supervisord 시작 시 자동으로 시작
- `autorestart`: 프로세스가 종료되면 자동으로 다시 시작
- `startretries`: 실패 시 시작을 재시도할 횟수
- `stderr_logfile`/`stdout_logfile`: 로그 파일 위치
- `user`: 프로세스를 실행할 시스템 사용자
- `numprocs`: 실행할 작업자 인스턴스 수
- `process_name`: 여러 작업자 프로세스의 이름 형식

### Supervisorctl로 작업자 관리하기

구성을 생성하거나 수정한 후:

```bash
# supervisor 구성 다시 로드
sudo supervisorctl reread
sudo supervisorctl update

# 특정 작업자 프로세스 제어
sudo supervisorctl start email_worker:*
sudo supervisorctl stop email_worker:*
sudo supervisorctl restart email_worker:*
sudo supervisorctl status email_worker:*
```

### 여러 파이프라인 실행하기

여러 개의 파이프라인을 위해 별도의 작업자 파일 및 구성을 생성합니다:

```ini
[program:email_worker]
command=php /path/to/email_worker.php
# ... 기타 설정 ...

[program:notification_worker]
command=php /path/to/notification_worker.php
# ... 기타 설정 ...
```

### 모니터링 및 로그

로그를 확인하여 작업자 활동을 모니터링합니다:

```bash
# 로그 보기
sudo tail -f /var/log/simple_job_queue.log

# 상태 확인
sudo supervisorctl status
```

이 설정은 작업 큐 시스템이 충돌, 서버 재부팅 또는 기타 문제 발생 후에도 계속 실행되도록 하여 프로덕션 환경에서 신뢰할 수 있는 시스템이 되도록 보장합니다.