# Vienkārša Darbu Rinda

Vienkārša Darbu Rinda ir bibliotēka, ko var izmantot, lai apstrādātu darbus asinhroni. To var izmantot ar beanstalkd, MySQL/MariaDB, SQLite un PostgreSQL.

## Instalēt
```bash
composer require n0nag0n/simple-job-queue
```

## Lietošana

Lai tas darbotos, jums ir nepieciešams veids, kā pievienot darbus rindai, un veids, kā apstrādāt darbus (darbinieks). Zemāk ir piemēri, kā pievienot darbu rindai un kā apstrādāt darbu.

## Pievienošana Flight

Šī pievienošana Flight ir vienkārša un to var izdarīt, izmantojot metodi `register()`. Zemāk ir piemērs, kā to pievienot Flight.

```php
<?php
require 'vendor/autoload.php';

Flight::register('queue', n0nag0n\Job_Queue::class, ['mysql'], function($Job_Queue) {
	// ja jums jau ir PDO savienojums ar Flight::db();
	$Job_Queue->addQueueConnection(Flight::db());

	// vai ja izmantojat beanstalkd/Pheanstalk
	$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
	$Job_Queue->addQueueConnection($pheanstalk);
});
```

### Jauna darba pievienošana

Kad pievienojat darbu, jums ir jānorāda caurule (rinda). Tas ir salīdzināms ar kanālu RabbitMQ vai cauruli beanstalkd.

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

// vai ja izmantojat beanstalkd/Pheanstalk
$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
$Job_Queue->addQueueConnection($pheanstalk);

$Job_Queue->watchPipeline('send_important_emails');
while(true) {
	$job = $Job_Queue->getNextJobAndReserve();

	// pielāgojiet to, lai jūs labāk gulētu naktī (tikai datu bāzes rindām, beanstalkd šai izteiksmei nav nepieciešams)
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
			// tas izņem to no gatavās rindas un ievieto to citā rindā, kuru var pickup un "kick" vēlāk.
			$Job_Queue->buryJob($job);
		}
	} catch(Exception $e) {
		$Job_Queue->buryJob($job);
	}
}
```

### Ilgu procesu apstrāde

Supervizors būs jūsu draugs. Meklējiet daudz, daudz rakstu par to, kā to īstenot. Tam nepieciešama nedaudz papildu konfigurācija, bet tas ir tā vērts, lai process turpinātu darboties.