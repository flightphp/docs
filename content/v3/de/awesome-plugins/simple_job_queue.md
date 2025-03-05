# Einfache Job-Warteschlange

Die einfache Job-Warteschlange ist eine Bibliothek, die verwendet werden kann, um Jobs asynchron zu verarbeiten. Sie kann mit beanstalkd, MySQL/MariaDB, SQLite und PostgreSQL verwendet werden.

## Installation
```bash
composer require n0nag0n/simple-job-queue
```

## Verwendung

Damit dies funktioniert, benötigen Sie eine Möglichkeit, Jobs zur Warteschlange hinzuzufügen, und eine Möglichkeit, die Jobs zu verarbeiten (einen Worker). Im Folgenden finden Sie Beispiele, wie man einen Job zur Warteschlange hinzufügt und wie man den Job verarbeitet.

## Hinzufügen zu Flight

Das Hinzufügen dieses Codes zu Flight ist einfach und erfolgt mit der Methode `register()`. Unten finden Sie ein Beispiel, wie Sie dies zu Flight hinzufügen.

```php
<?php
require 'vendor/autoload.php';

// Ändern Sie ['mysql'] in ['beanstalkd'], wenn Sie beanstalkd verwenden möchten
Flight::register('queue', n0nag0n\Job_Queue::class, ['mysql'], function($Job_Queue) {
	// Wenn Sie bereits eine PDO-Verbindung zu Flight::db() haben;
	$Job_Queue->addQueueConnection(Flight::db());

	// Oder wenn Sie beanstalkd/Pheanstalk verwenden
	$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
	$Job_Queue->addQueueConnection($pheanstalk);
});
```

### Einen neuen Job hinzufügen

Wenn Sie einen Job hinzufügen, müssen Sie eine Pipeline (Warteschlange) angeben. Dies ist vergleichbar mit einem Kanal in RabbitMQ oder einem Tube in beanstalkd.

```php
<?php
Flight::queue()->selectPipeline('send_important_emails');
Flight::queue()->addJob(json_encode([ 'something' => 'that', 'ends' => 'up', 'a' => 'string' ]));
```

### Einen Worker ausführen

Hier ist eine Beispieldatei, wie man einen Worker ausführt.
```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// PDO-Verbindung
$PDO = new PDO('mysql:dbname=testdb;host=127.0.0.1', 'user', 'pass');
$Job_Queue->addQueueConnection($PDO);

// Oder wenn Sie beanstalkd/Pheanstalk verwenden
$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
$Job_Queue->addQueueConnection($pheanstalk);

$Job_Queue->watchPipeline('send_important_emails');
while(true) {
	$job = $Job_Queue->getNextJobAndReserve();

	// Passen Sie es an, was Ihnen nachts besser schlafen lässt (nur für Datenbankwarteschlangen, beanstalkd benötigt diese if-Anweisung nicht)
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
			// Dies entfernt es aus der bereitstehenden Warteschlange und legt es in eine andere Warteschlange, die später aufgegriffen und "getreten" werden kann.
			$Job_Queue->buryJob($job);
		}
	} catch(Exception $e) {
		$Job_Queue->buryJob($job);
	}
}
```

# Lange Prozesse mit Supervisord verwalten

Supervisord ist ein Prozesskontrollsystem, das sicherstellt, dass Ihre Worker-Prozesse kontinuierlich laufen. Hier ist eine umfassendere Anleitung, wie Sie es mit Ihrem einfachen Job-Queue-Worker einrichten:

### Supervisord installieren

```bash
# Auf Ubuntu/Debian
sudo apt-get install supervisor

# Auf CentOS/RHEL
sudo yum install supervisor

# Auf macOS mit Homebrew
brew install supervisor
```

### Erstellen eines Worker-Skripts

Zuerst speichern Sie Ihren Worker-Code in einer dedizierten PHP-Datei:

```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// PDO-Verbindung
$PDO = new PDO('mysql:dbname=your_database;host=127.0.0.1', 'username', 'password');
$Job_Queue->addQueueConnection($PDO);

// Setzen Sie die Pipeline, die überwacht werden soll
$Job_Queue->watchPipeline('send_important_emails');

// Protokolliere den Start des Workers
echo date('Y-m-d H:i:s') . " - Worker gestartet\n";

while(true) {
    $job = $Job_Queue->getNextJobAndReserve();

    if(empty($job)) {
        usleep(500000); // Schlafen für 0,5 Sekunden
        continue;
    }

    echo date('Y-m-d H:i:s') . " - Verarbeite Job {$job['id']}\n";
    $payload = json_decode($job['payload'], true);

    try {
        $result = doSomethingThatDoesSomething($payload);

        if($result === true) {
            $Job_Queue->deleteJob($job);
            echo date('Y-m-d H:i:s') . " - Job {$job['id']} erfolgreich abgeschlossen\n";
        } else {
            $Job_Queue->buryJob($job);
            echo date('Y-m-d H:i:s') . " - Job {$job['id']} fehlgeschlagen, beerdigt\n";
        }
    } catch(Exception $e) {
        $Job_Queue->buryJob($job);
        echo date('Y-m-d H:i:s') . " - Ausnahme bei der Verarbeitung des Jobs {$job['id']}: {$e->getMessage()}\n";
    }
}
```

### Konfigurieren von Supervisord

Erstellen Sie eine Konfigurationsdatei für Ihren Worker:

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

### Wichtige Konfigurationsoptionen:

- `command`: Der Befehl zum Ausführen Ihres Workers
- `directory`: Arbeitsverzeichnis für den Worker
- `autostart`: Automatisch starten, wenn supervisord startet
- `autorestart`: Automatisch neu starten, wenn der Prozess beendet wird
- `startretries`: Anzahl der Versuche, den Start zu wiederholen, wenn er fehlschlägt
- `stderr_logfile`/`stdout_logfile`: Speicherorte der Protokolldateien
- `user`: Systembenutzer, unter dem der Prozess ausgeführt werden soll
- `numprocs`: Anzahl der auszuführenden Worker-Instanzen
- `process_name`: Namensformat für mehrere Worker-Prozesse

### Worker mit Supervisorctl verwalten

Nach dem Erstellen oder Modifizieren der Konfiguration:

```bash
# Supervisor-Konfiguration neu laden
sudo supervisorctl reread
sudo supervisorctl update

# Steuerung spezifischer Worker-Prozesse
sudo supervisorctl start email_worker:*
sudo supervisorctl stop email_worker:*
sudo supervisorctl restart email_worker:*
sudo supervisorctl status email_worker:*
```

### Mehrere Pipelines ausführen

Für mehrere Pipelines erstellen Sie separate Worker-Dateien und Konfigurationen:

```ini
[program:email_worker]
command=php /path/to/email_worker.php
# ... andere Konfigurationen ...

[program:notification_worker]
command=php /path/to/notification_worker.php
# ... andere Konfigurationen ...
```

### Überwachen und Protokolle

Überprüfen Sie die Protokolle, um die Aktivität der Worker zu überwachen:

```bash
# Protokolle anzeigen
sudo tail -f /var/log/simple_job_queue.log

# Status überprüfen
sudo supervisorctl status
```

Dieses Setup sorgt dafür, dass Ihre Job-Worker auch nach Abstürzen, Serverneustarts oder anderen Problemen weiterlaufen, was Ihr Warteschlangensystem zuverlässig für Produktionsumgebungen macht.