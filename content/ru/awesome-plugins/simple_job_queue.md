# Простая очередь задач

Простая очередь задач — это библиотека, которая может использоваться для асинхронной обработки задач. Она может быть использована с beanstalkd, MySQL/MariaDB, SQLite и PostgreSQL.

## Установка
```bash
composer require n0nag0n/simple-job-queue
```

## Использование

Для того чтобы это работало, вам нужно иметь способ добавления задач в очередь и способ их обработки (рабочий). Ниже приведены примеры того, как добавить задачу в очередь и как обработать задачу.

## Добавление в Flight

Добавить это в Flight просто, и это делается с помощью метода `register()`. Ниже приведен пример того, как это добавить в Flight.

```php
<?php
require 'vendor/autoload.php';

Flight::register('queue', n0nag0n\Job_Queue::class, ['mysql'], function($Job_Queue) {
	// если у вас уже есть подключение PDO на Flight::db();
	$Job_Queue->addQueueConnection(Flight::db());

	// или если вы используете beanstalkd/Pheanstalk
	$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
	$Job_Queue->addQueueConnection($pheanstalk);
});
```

### Добавление новой задачи

Когда вы добавляете задачу, вам нужно указать конвейер (очередь). Это сопоставимо с каналом в RabbitMQ или трубой в beanstalkd.

```php
<?php
Flight::queue()->selectPipeline('send_important_emails');
Flight::queue()->addJob(json_encode([ 'something' => 'that', 'ends' => 'up', 'a' => 'string' ]));
```

### Запуск рабочего

Вот пример файла о том, как запустить рабочего.
```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// Подключение PDO
$PDO = new PDO('mysql:dbname=testdb;host=127.0.0.1', 'user', 'pass');
$Job_Queue->addQueueConnection($PDO);

// или если вы используете beanstalkd/Pheanstalk
$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
$Job_Queue->addQueueConnection($pheanstalk);

$Job_Queue->watchPipeline('send_important_emails');
while(true) {
	$job = $Job_Queue->getNextJobAndReserve();

	// настройте так, как вам будет спокойнее спать по ночам (только для очередей базы данных, beanstalkd не требует этого условия)
	if(empty($job)) {
		usleep(500000);
		continue;
	}

	echo "Обработка {$job['id']}\n";
	$payload = json_decode($job['payload'], true);

	try {
		$result = doSomethingThatDoesSomething($payload);

		if($result === true) {
			$Job_Queue->deleteJob($job);
		} else {
			// это удаляет задачу из готовой очереди и помещает её в другую очередь, которая может быть подобрана и "потянута" позже.
			$Job_Queue->buryJob($job);
		}
	} catch(Exception $e) {
		$Job_Queue->buryJob($job);
	}
}
```

### Обработка долгих процессов

Supervisord станет вашим лучшим другом. Изучите множество статей о том, как это реализовать. Это требует немного дополнительной конфигурации, но стоит того, чтобы процесс оставался работающим.