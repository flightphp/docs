# Проста черга завдань

Проста черга завдань - це бібліотека, яка може використовуватися для асинхронної обробки завдань. Її можна використовувати з beanstalkd, MySQL/MariaDB, SQLite і PostgreSQL.

## Встановлення
```bash
composer require n0nag0n/simple-job-queue
```

## Використання

Щоб це працювало, вам потрібен спосіб додати завдання до черги та спосіб обробляти завдання (робітник). Нижче наведені приклади того, як додати завдання до черги та як обробити завдання.

## Додавання до Flight

Додавання цього до Flight є простим і здійснюється за допомогою методу `register()`. Нижче наведено приклад того, як додати це до Flight.

```php
<?php
require 'vendor/autoload.php';

// Змініть ['mysql'] на ['beanstalkd'], якщо ви хочете використовувати beanstalkd
Flight::register('queue', n0nag0n\Job_Queue::class, ['mysql'], function($Job_Queue) {
	// якщо у вас вже є з'єднання PDO в Flight::db();
	$Job_Queue->addQueueConnection(Flight::db());

	// або якщо ви використовуєте beanstalkd/Pheanstalk
	$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
	$Job_Queue->addQueueConnection($pheanstalk);
});
```

### Додавання нового завдання

Коли ви додаєте завдання, вам потрібно вказати конвеєр (чергу). Це можна порівняти з каналом у RabbitMQ або трубою в beanstalkd.

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

	// налаштуйте так, як вам краще спати вночі (тільки для черг бази даних, beanstalkd не потребує цієї умови)
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
			// це забирає його з готової черги та ставить його в іншу чергу, яку можна буде забрати та "вибити" пізніше.
			$Job_Queue->buryJob($job);
		}
	} catch(Exception $e) {
		$Job_Queue->buryJob($job);
	}
}
```

# Обробка тривалих процесів із Supervisord

Supervisord - це система контролю процесів, яка забезпечує безперервну роботу ваших робочих процесів. Ось більш детальний посібник із налаштування його з вашим робітником Simple Job Queue:

### Встановлення Supervisord

```bash
# На Ubuntu/Debian
sudo apt-get install supervisor

# На CentOS/RHEL
sudo yum install supervisor

# На macOS з Homebrew
brew install supervisor
```

### Створення скрипта робітника

Спочатку збережіть код вашого робітника в окремому файлі PHP:

```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// З'єднання PDO
$PDO = new PDO('mysql:dbname=your_database;host=127.0.0.1', 'username', 'password');
$Job_Queue->addQueueConnection($PDO);

// Встановіть конвеєр для моніторингу
$Job_Queue->watchPipeline('send_important_emails');

// Лог початку робітника
echo date('Y-m-d H:i:s') . " - Робітник запущений\n";

while(true) {
    $job = $Job_Queue->getNextJobAndReserve();

    if(empty($job)) {
        usleep(500000); // Спати 0,5 секунди
        continue;
    }

    echo date('Y-m-d H:i:s') . " - Обробка завдання {$job['id']}\n";
    $payload = json_decode($job['payload'], true);

    try {
        $result = doSomethingThatDoesSomething($payload);

        if($result === true) {
            $Job_Queue->deleteJob($job);
            echo date('Y-m-d H:i:s') . " - Завдання {$job['id']} успішно завершено\n";
        } else {
            $Job_Queue->buryJob($job);
            echo date('Y-m-d H:i:s') . " - Завдання {$job['id']} не вдалося, закопано\n";
        }
    } catch(Exception $e) {
        $Job_Queue->buryJob($job);
        echo date('Y-m-d H:i:s') . " - Виключення при обробці завдання {$job['id']}: {$e->getMessage()}\n";
    }
}
```

### Налаштування Supervisord

Створіть файл конфігурації для вашого робітника:

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

### Основні параметри конфігурації:

- `command`: Команда для запуску вашого робітника
- `directory`: Робоча папка для робітника
- `autostart`: Автоматичний запуск при запуску supervisord
- `autorestart`: Автоматичний перезапуск, якщо процес завершується
- `startretries`: Кількість спроб перезапустити, якщо це не вдається
- `stderr_logfile`/`stdout_logfile`: Місцезнаходження файлів журналу
- `user`: Системний користувач для запуску процесу
- `numprocs`: Кількість екземплярів робітника для запуску
- `process_name`: Формат іменування для кількох процесів робітників

### Управління робітниками за допомогою Supervisorctl

Після створення або модифікації конфігурації:

```bash
# Перезавантаження конфігурації супервайзера
sudo supervisorctl reread
sudo supervisorctl update

# Управління конкретними процесами робітників
sudo supervisorctl start email_worker:*
sudo supervisorctl stop email_worker:*
sudo supervisorctl restart email_worker:*
sudo supervisorctl status email_worker:*
```

### Запуск кількох конвеєрів

Для кількох конвеєрів створіть окремі файли робітників і конфігурації:

```ini
[program:email_worker]
command=php /path/to/email_worker.php
# ... інші конфігурації ...

[program:notification_worker]
command=php /path/to/notification_worker.php
# ... інші конфігурації ...
```

### Моніторинг та журнали

Перевірте журнали, щоб контролювати активність робітника:

```bash
# Перегляд журналів
sudo tail -f /var/log/simple_job_queue.log

# Перевірка статусу
sudo supervisorctl status
```

Ця настройка забезпечує неперервну роботу ваших робочих процесів, навіть після аварій, перезавантажень сервера або інших проблем, що робить вашу систему черги надійною для виробничих середовищ.