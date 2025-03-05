# Vienkārša Darba Rinda

Vienkārša Darba Rinda ir bibliotēka, ko var izmantot, lai apstrādātu darbus asinkroni. To var izmantot ar beanstalkd, MySQL/MariaDB, SQLite un PostgreSQL.

## Instalēt
```bash
composer require n0nag0n/simple-job-queue
```

## Izmantošana

Lai tas darbotos, jums nepieciešams veids, kā pievienot darbus rindai un veids, kā apstrādāt darbus (darbinieks). Tālāk ir sniegti piemēri, kā pievienot darbu rindai un kā apstrādāt darbu.

## Pievienošana Flight

Šīs pievienošana Flight ir vienkārša un tiek veikta, izmantojot metodi `register()`. Tālāk ir piemērs, kā to pievienot Flight.

```php
<?php
require 'vendor/autoload.php';

// Mainiet ['mysql'] uz ['beanstalkd'], ja vēlaties izmantot beanstalkd
Flight::register('queue', n0nag0n\Job_Queue::class, ['mysql'], function($Job_Queue) {
	// ja jums jau ir PDO savienojums ar Flight::db();
	$Job_Queue->addQueueConnection(Flight::db());

	// vai, ja izmantojat beanstalkd/Pheanstalk
	$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
	$Job_Queue->addQueueConnection($pheanstalk);
});
```

### Jauna darba pievienošana

Kad pievienojat darbu, jums jānorāda caurule (rinda). Tas ir salīdzināms ar kanālu RabbitMQ vai cauruli beanstalkd.

```php
<?php
Flight::queue()->selectPipeline('send_important_emails');
Flight::queue()->addJob(json_encode([ 'something' => 'that', 'ends' => 'up', 'a' => 'string' ]));
```

### Darbinieka palaišana

Šeit ir piemēra fails, kā palaist darbinieku.
```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// PDO savienojums
$PDO = new PDO('mysql:dbname=testdb;host=127.0.0.1', 'user', 'pass');
$Job_Queue->addQueueConnection($PDO);

// vai, ja izmantojat beanstalkd/Pheanstalk
$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
$Job_Queue->addQueueConnection($pheanstalk);

$Job_Queue->watchPipeline('send_important_emails');
while(true) {
	$job = $Job_Queue->getNextJobAndReserve();

	// pielāgojiet, kā jums labāk guļ naktī (tikai datu bāzu rindām, beanstalkd šī instrukcija nav nepieciešama)
	if(empty($job)) {
		usleep(500000);
		continue;
	}

	echo "Apstrādā {$job['id']}\n";
	$payload = json_decode($job['payload'], true);

	try {
		$result = doSomethingThatDoesSomething($payload);

		if($result === true) {
			$Job_Queue->deleteJob($job);
		} else {
			// tas izņem to no gatavo rindu un ievieto citā rindā, kuru var paņemt un "izsist" vēlāk.
			$Job_Queue->buryJob($job);
		}
	} catch(Exception $e) {
		$Job_Queue->buryJob($job);
	}
}
```

# Ilgu Procesu Apstrāde ar Supervisord

Supervisord ir procesu kontroles sistēma, kas nodrošina, ka jūsu darbinieku procesi paliek aktīvi nepārtraukti. Šeit ir detalizētāks ceļvedis, kā to iestatīt ar savu Vienkāršo Darba Rindu darbinieku:

### Supervisord instalēšana

```bash
# Uz Ubuntu/Debian
sudo apt-get install supervisor

# Uz CentOS/RHEL
sudo yum install supervisor

# Uz macOS ar Homebrew
brew install supervisor
```

### Darbinieka skripta izveide

Vispirms saglabājiet savu darbinieka kodu veltītā PHP failā:

```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// PDO savienojums
$PDO = new PDO('mysql:dbname=your_database;host=127.0.0.1', 'username', 'password');
$Job_Queue->addQueueConnection($PDO);

// Iestatiet cauruli, kuru vēlaties uzraudzīt
$Job_Queue->watchPipeline('send_important_emails');

// Ierakstiet darbinieka sākumu
echo date('Y-m-d H:i:s') . " - Darbinieks uzsākts\n";

while(true) {
    $job = $Job_Queue->getNextJobAndReserve();

    if(empty($job)) {
        usleep(500000); // Miega 0.5 sekundes
        continue;
    }

    echo date('Y-m-d H:i:s') . " - Apstrādā darba {$job['id']}\n";
    $payload = json_decode($job['payload'], true);

    try {
        $result = doSomethingThatDoesSomething($payload);

        if($result === true) {
            $Job_Queue->deleteJob($job);
            echo date('Y-m-d H:i:s') . " - Darbs {$job['id']} veiksmīgi pabeigts\n";
        } else {
            $Job_Queue->buryJob($job);
            echo date('Y-m-d H:i:s') . " - Darbs {$job['id']} neizdevās, aprakts\n";
        }
    } catch(Exception $e) {
        $Job_Queue->buryJob($job);
        echo date('Y-m-d H:i:s') . " - Izņēmums apstrādājot darbu {$job['id']}: {$e->getMessage()}\n";
    }
}
```

### Supervisord konfigurēšana

Izveidojiet konfigurācijas failu savam darbiniekam:

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

### Galvenās konfigurācijas opcijas:

- `command`: Komanda, lai palaistu jūsu darbinieku
- `directory`: Darba direktorija darbiniekam
- `autostart`: Sākt automātiski, kad supervisord sāk
- `autorestart`: Automātiski restartēt, ja process izbeidzas
- `startretries`: Cik reizes mēģināt sākt, ja tas neizdodas
- `stderr_logfile`/`stdout_logfile`: Ieraksta failu atrašanās vietas
- `user`: Sistēmas lietotājs, kas palaiž procesu
- `numprocs`: Darbinieku instanču skaits
- `process_name`: Nosaukuma formāts vairākiem darbinieku procesiem

### Darbinieku pārvaldība ar Supervisorctl

Pēc konfigurācijas izveides vai modificēšanas:

```bash
# Pārlādēt supervisor konfigurāciju
sudo supervisorctl reread
sudo supervisorctl update

# Kontrolēt konkrētus darbinieku procesus
sudo supervisorctl start email_worker:*
sudo supervisorctl stop email_worker:*
sudo supervisorctl restart email_worker:*
sudo supervisorctl status email_worker:*
```

### Vairāku Cauruļu Palaišana

Vairāku cauruļu gadījumā izveidojiet atsevišķus darbinieku failus un konfigurācijas:

```ini
[program:email_worker]
command=php /path/to/email_worker.php
# ... citas konfigurācijas ...

[program:notification_worker]
command=php /path/to/notification_worker.php
# ... citas konfigurācijas ...
```

### Uzraudzība un Ieraksti

Pārbaudiet ierakstus, lai uzraudzītu darbinieku aktivitāti:

```bash
# Apskatīt ierakstus
sudo tail -f /var/log/simple_job_queue.log

# Pārbaudīt statusu
sudo supervisorctl status
```

Šis iestatījums nodrošina, ka jūsu darba darbinieki turpina darboties, pat pēc avārijām, servera restartēšanas vai citiem jautājumiem, padarot jūsu rindu sistēmu uzticamu ražošanas vidēm.