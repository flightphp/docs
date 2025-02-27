# Простий вузол завдань

Простий вузол завдань - це бібліотека, яку можна використовувати для асинхронної обробки завдань. Її можна використовувати з beanstalkd, MySQL/MariaDB, SQLite та PostgreSQL.

## Встановлення
```bash
composer require n0nag0n/simple-job-queue
```

## Використання

Щоб це працювало, вам потрібен спосіб додавання завдань до черги та спосіб обробки завдань (робітник). Нижче наведено приклади того, як додати завдання до черги та як обробити завдання.

## Додавання до Flight

Додавання цього до Flight просте і виконується за допомогою методу `register()`. Нижче наведено приклад того, як додати це до Flight.

```php
<?php
require 'vendor/autoload.php';

Flight::register('queue', n0nag0n\Job_Queue::class, ['mysql'], function($Job_Queue) {
	// якщо у вас вже є з'єднання PDO з Flight::db();
	$Job_Queue->addQueueConnection(Flight::db());

	// або якщо ви використовуєте beanstalkd/Pheanstalk
	$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
	$Job_Queue->addQueueConnection($pheanstalk);
});
```

### Додавання нового завдання

Коли ви додаєте завдання, вам потрібно вказати конвеєр (чергу). Це порівнюється з каналом у RabbitMQ або трубкою в beanstalkd.

```php
<?php
Flight::queue()->selectPipeline('send_important_emails');
Flight::queue()->addJob(json_encode([ 'something' => 'that', 'ends' => 'up', 'a' => 'string' ]));
```

### Запуск робітника

Ось приклад файлу про те, як запустити робітника.
```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// З'єднання PDO
$PDO = new PDO('mysql:dbname=testdb;host=127.0.0.1', 'user', 'pass');
$Job_Queue->addQueueConnection($PDO);

// або якщо ви використовуєте beanstalkd/Pheanstalk
$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
$Job_Queue->addQueueConnection($pheanstalk);

$Job_Queue->watchPipeline('send_important_emails');
while(true) {
	$job = $Job_Queue->getNextJobAndReserve();

	// налаштуйте на те, що дозволяє вам спати спокійніше вночі (тільки для черг бази даних, beanstalkd не потребує цього оператору if)
	if(empty($job)) {
		usleep(500000);
		continue;
	}

	echo "Обробка {$job['id']}\n";
	$payload = json_decode($job['payload'], true);

	try {
		$result = doSomethingThatDoesSomething($payload);

		if($result === true) {
			$Job_Queue->deleteJob($job);
		} else {
			// це виймає його з готової черги та поміщає в іншу чергу, яку можна буде забрати та "вибити" пізніше.
			$Job_Queue->buryJob($job);
		}
	} catch(Exception $e) {
		$Job_Queue->buryJob($job);
	}
}
```

### Обробка тривалих процесів

Supervisord буде вашим другом. Ознайомтеся з багатьма, багатьма статтями про те, як це реалізувати. Це потребує трохи додаткової конфігурації, але варте того, щоб процес залишався активним.