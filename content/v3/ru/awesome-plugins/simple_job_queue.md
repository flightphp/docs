# Простой очередь задач

Простой очередь задач - это библиотека, которая может использоваться для обработки задач асинхронно. Она может быть использована с beanstalkd, MySQL/MariaDB, SQLite и PostgreSQL.

## Установка
```bash
composer require n0nag0n/simple-job-queue
```

## Использование

Чтобы это работало, вам нужен способ добавлять задачи в очередь и способ обрабатывать задачи (рабочий процесс). Ниже приведены примеры того, как добавить задачу в очередь и как обработать задачу.

## Добавление в Flight

Добавить это в Flight просто, и это делается с помощью метода `register()`. Ниже приведен пример того, как добавить это в Flight.

```php
<?php
require 'vendor/autoload.php';

// Замените ['mysql'] на ['beanstalkd'], если хотите использовать beanstalkd
Flight::register('queue', n0nag0n\Job_Queue::class, ['mysql'], function($Job_Queue) {
	// если у вас уже есть соединение PDO на Flight::db();
	$Job_Queue->addQueueConnection(Flight::db());

	// или если вы используете beanstalkd/Pheanstalk
	$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
	$Job_Queue->addQueueConnection($pheanstalk);
});
```

### Добавление новой задачи

Когда вы добавляете задачу, вам нужно указать конвейер (очередь). Это сравнимо с каналом в RabbitMQ или трубой в beanstalkd.

```php
<?php
Flight::queue()->selectPipeline('send_important_emails');
Flight::queue()->addJob(json_encode([ 'something' => 'that', 'ends' => 'up', 'a' => 'string' ]));
```

### Запуск рабочего процесса

Вот пример файла того, как запустить рабочего процесса.
```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// Соединение PDO
$PDO = new PDO('mysql:dbname=testdb;host=127.0.0.1', 'user', 'pass');
$Job_Queue->addQueueConnection($PDO);

// или если вы используете beanstalkd/Pheanstalk
$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
$Job_Queue->addQueueConnection($pheanstalk);

$Job_Queue->watchPipeline('send_important_emails');
while(true) {
	$job = $Job_Queue->getNextJobAndReserve();

	// настройте это так, как вам будет спокойнее (только для очередей базы данных, beanstalkd не нуждается в этом условии)
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
			// это убирает его из очереди готовых и помещает в другую очередь, которую можно будет забрать и «ударить» позже.
			$Job_Queue->buryJob($job);
		}
	} catch(Exception $e) {
		$Job_Queue->buryJob($job);
	}
}
```

# Обработка длительных процессов с помощью Supervisord

Supervisord - это система управления процессами, которая обеспечивает постоянную работу ваших процессов рабочих. Вот более полное руководство по настройке его с вашим рабочим процессом Простой очереди задач:

### Установка Supervisord

```bash
# На Ubuntu/Debian
sudo apt-get install supervisor

# На CentOS/RHEL
sudo yum install supervisor

# На macOS с Homebrew
brew install supervisor
```

### Создание скрипта рабочего процесса

Сначала сохраните ваш код рабочего процесса в отдельный файл PHP:

```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// Соединение PDO
$PDO = new PDO('mysql:dbname=your_database;host=127.0.0.1', 'username', 'password');
$Job_Queue->addQueueConnection($PDO);

// Установите конвейер для наблюдения
$Job_Queue->watchPipeline('send_important_emails');

// Запись начала работы рабочего процесса
echo date('Y-m-d H:i:s') . " - Рабочий процесс запущен\n";

while(true) {
    $job = $Job_Queue->getNextJobAndReserve();

    if(empty($job)) {
        usleep(500000); // Спите 0,5 секунды
        continue;
    }

    echo date('Y-m-d H:i:s') . " - Обработка задачи {$job['id']}\n";
    $payload = json_decode($job['payload'], true);

    try {
        $result = doSomethingThatDoesSomething($payload);

        if($result === true) {
            $Job_Queue->deleteJob($job);
            echo date('Y-m-d H:i:s') . " - Задача {$job['id']} успешно завершена\n";
        } else {
            $Job_Queue->buryJob($job);
            echo date('Y-m-d H:i:s') . " - Задача {$job['id']} не удалась, похоронена\n";
        }
    } catch(Exception $e) {
        $Job_Queue->buryJob($job);
        echo date('Y-m-d H:i:s') . " - Исключение при обработке задачи {$job['id']}: {$e->getMessage()}\n";
    }
}
```

### Настройка Supervisord

Создайте файл конфигурации для вашего рабочего процесса:

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

### Основные параметры конфигурации:

- `command`: Команда для запуска вашего рабочего процесса
- `directory`: Рабочая директория для рабочего процесса
- `autostart`: Автоматически запускать при запуске supervisord
- `autorestart`: Автоматически перезапускать, если процесс выходит
- `startretries`: Количество попыток перезапуска, если он завершится с ошибкой
- `stderr_logfile`/`stdout_logfile`: Пути к файлам журналов
- `user`: Системный пользователь, от имени которого будет запущен процесс
- `numprocs`: Количество экземпляров рабочего процесса для запуска
- `process_name`: Форматирование имен для нескольких процессов рабочих

### Управление рабочими процессами с помощью Supervisorctl

После создания или изменения конфигурации:

```bash
# Перезагрузить конфигурацию супервайзера
sudo supervisorctl reread
sudo supervisorctl update

# Управление конкретными процессами рабочих
sudo supervisorctl start email_worker:*
sudo supervisorctl stop email_worker:*
sudo supervisorctl restart email_worker:*
sudo supervisorctl status email_worker:*
```

### Запуск нескольких конвейеров

Для нескольких конвейеров создайте отдельные файлы рабочих процессов и конфигурации:

```ini
[program:email_worker]
command=php /path/to/email_worker.php
# ... другие настройки ...

[program:notification_worker]
command=php /path/to/notification_worker.php
# ... другие настройки ...
```

### Мониторинг и журналы

Проверьте журналы для мониторинга активности рабочих процессов:

```bash
# Просмотр журналов
sudo tail -f /var/log/simple_job_queue.log

# Проверка состояния
sudo supervisorctl status
```

Эта настройка гарантирует, что ваши рабочие процессы задач продолжают работать даже после сбоев, перезагрузок сервера или других проблем, делая вашу систему очередей надежной для производственных сред.