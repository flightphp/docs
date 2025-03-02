# File d'attente des tâches simple

File d'attente des tâches simple est une bibliothèque qui peut être utilisée pour traiter des tâches de manière asynchrone. Elle peut être utilisée avec beanstalkd, MySQL/MariaDB, SQLite et PostgreSQL.

## Installation
```bash
composer require n0nag0n/simple-job-queue
```

## Utilisation

Pour que cela fonctionne, vous devez avoir un moyen d'ajouter des tâches à la file d'attente et un moyen de traiter les tâches (un travailleur). Voici des exemples de la manière d'ajouter une tâche à la file d'attente et de traiter la tâche.


## Ajout à Flight

Ajouter cela à Flight est simple et se fait en utilisant la méthode `register()`. Voici un exemple de la manière d'ajouter cela à Flight.

```php
<?php
require 'vendor/autoload.php';

Flight::register('queue', n0nag0n\Job_Queue::class, ['mysql'], function($Job_Queue) {
	// si vous avez déjà une connexion PDO sur Flight::db();
	$Job_Queue->addQueueConnection(Flight::db());

	// ou si vous utilisez beanstalkd/Pheanstalk
	$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
	$Job_Queue->addQueueConnection($pheanstalk);
});
```

### Ajout d'une nouvelle tâche

Lorsque vous ajoutez une tâche, vous devez spécifier un pipeline (file d'attente). Cela est comparable à un canal dans RabbitMQ ou un tube dans beanstalkd.

```php
<?php
Flight::queue()->selectPipeline('send_important_emails');
Flight::queue()->addJob(json_encode([ 'something' => 'that', 'ends' => 'up', 'a' => 'string' ]));
```

### Exécution d'un travailleur

Voici un exemple de fichier sur la manière d'exécuter un travailleur.
```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// connexion PDO
$PDO = new PDO('mysql:dbname=testdb;host=127.0.0.1', 'user', 'pass');
$Job_Queue->addQueueConnection($PDO);

// ou si vous utilisez beanstalkd/Pheanstalk
$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
$Job_Queue->addQueueConnection($pheanstalk);

$Job_Queue->watchPipeline('send_important_emails');
while(true) {
	$job = $Job_Queue->getNextJobAndReserve();

	// ajustez à ce qui vous fait dormir mieux la nuit (pour les files d'attente de base de données uniquement, beanstalkd n'a pas besoin de cette instruction if)
	if(empty($job)) {
		usleep(500000);
		continue;
	}

	echo "Traitement de {$job['id']}\n";
	$payload = json_decode($job['payload'], true);

	try {
		$result = doSomethingThatDoesSomething($payload);

		if($result === true) {
			$Job_Queue->deleteJob($job);
		} else {
			// cela le retire de la file d'attente prête et le met dans une autre file d'attente qui peut être récupérée et "repoussée" plus tard.
			$Job_Queue->buryJob($job);
		}
	} catch(Exception $e) {
		$Job_Queue->buryJob($job);
	}
}
```

### Gestion des processus longs

Supervisord sera votre allié. Consultez les nombreux, nombreux articles sur la manière de l'implémenter. Cela nécessite un peu de configuration supplémentaire, mais cela en vaut la peine pour maintenir le processus en cours d'exécution.