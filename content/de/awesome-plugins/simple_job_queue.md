# Einfache Job-Warteschlange

Die einfache Job-Warteschlange ist eine Bibliothek, die verwendet werden kann, um Jobs asynchron zu verarbeiten. Sie kann mit beanstalkd, MySQL/MariaDB, SQLite und PostgreSQL verwendet werden.

## Installation
```bash
composer require n0nag0n/simple-job-queue
```

## Verwendung

Damit dies funktioniert, benötigen Sie eine Möglichkeit, Jobs zur Warteschlange hinzuzufügen, und eine Möglichkeit, die Jobs zu verarbeiten (ein Worker). Im Folgenden finden Sie Beispiele, wie ein Job zur Warteschlange hinzugefügt und wie der Job verarbeitet wird.

## Hinzufügen zu Flight

Das Hinzufügen zu Flight ist einfach und erfolgt mit der Methode `register()`. Im Folgenden ein Beispiel, wie dies zu Flight hinzugefügt wird.

```php
<?php
require 'vendor/autoload.php';

Flight::register('queue', n0nag0n\Job_Queue::class, ['mysql'], function($Job_Queue) {
	// Wenn Sie bereits eine PDO-Verbindung auf Flight::db(); haben
	$Job_Queue->addQueueConnection(Flight::db());

	// oder wenn Sie beanstalkd/Pheanstalk verwenden
	$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
	$Job_Queue->addQueueConnection($pheanstalk);
});
```

### Hinzufügen eines neuen Jobs

Wenn Sie einen Job hinzufügen, müssen Sie eine Pipeline (Warteschlange) angeben. Dies ist vergleichbar mit einem Kanal in RabbitMQ oder einem Rohr in beanstalkd.

```php
<?php
Flight::queue()->selectPipeline('send_important_emails');
Flight::queue()->addJob(json_encode([ 'something' => 'that', 'ends' => 'up', 'a' => 'string' ]));
```

### Ausführen eines Workers

Hier ist eine Beispiel-Datei, wie man einen Worker ausführt.
```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// PDO-Verbindung
$PDO = new PDO('mysql:dbname=testdb;host=127.0.0.1', 'user', 'pass');
$Job_Queue->addQueueConnection($PDO);

// oder wenn Sie beanstalkd/Pheanstalk verwenden
$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
$Job_Queue->addQueueConnection($pheanstalk);

$Job_Queue->watchPipeline('send_important_emails');
while(true) {
	$job = $Job_Queue->getNextJobAndReserve();

	// Passen Sie dies an, um besser zu schlafen (nur für Datenbankwarteschlangen, beanstalkd benötigt diese If-Anweisung nicht)
	if(empty($job)) {
		usleep(500000);
		continue;
	}

	echo "Verarbeite {$job['id']}\n";
	$payload = json_decode($job['payload'], true);

	try {
		$result = doSomethingThatDoesSomething($payload);

		if($result === true) {
			$Job_Queue->deleteJob($job);
		} else {
			// Dies nimmt es aus der bereitgestellten Warteschlange und legt es in eine andere Warteschlange, die später abgeholt und "angestoßen" werden kann.
			$Job_Queue->buryJob($job);
		}
	} catch(Exception $e) {
		$Job_Queue->buryJob($job);
	}
}
```

### Umgang mit langen Prozessen

Supervisord wird Ihr Freund sein. Suchen Sie nach den vielen, vielen Artikeln, wie Sie dies umsetzen können. Es erfordert ein wenig zusätzliche Konfiguration, aber es lohnt sich, den Prozess am Laufen zu halten.