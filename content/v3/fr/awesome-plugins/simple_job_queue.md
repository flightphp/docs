# File d'attente de travail simple

La file d'attente de travail simple est une bibliothèque qui peut être utilisée pour traiter des travaux de manière asynchrone. Elle peut être utilisée avec beanstalkd, MySQL/MariaDB, SQLite et PostgreSQL.

## Installation
```bash
composer require n0nag0n/simple-job-queue
```

## Utilisation

Pour que cela fonctionne, vous devez avoir un moyen d'ajouter des travaux à la file d'attente et un moyen de traiter les travaux (un travailleur). Ci-dessous, des exemples de la façon d'ajouter un travail à la file d'attente et de traiter le travail.

## Ajout à Flight

Ajouter cela à Flight est simple et se fait en utilisant la méthode `register()`. Ci-dessous un exemple de la façon d'ajouter cela à Flight.

```php
<?php
require 'vendor/autoload.php';

// Changez ['mysql'] en ['beanstalkd'] si vous voulez utiliser beanstalkd
Flight::register('queue', n0nag0n\Job_Queue::class, ['mysql'], function($Job_Queue) {
	// si vous avez déjà une connexion PDO sur Flight::db();
	$Job_Queue->addQueueConnection(Flight::db());

	// ou si vous utilisez beanstalkd/Pheanstalk
	$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
	$Job_Queue->addQueueConnection($pheanstalk);
});
```

### Ajout d'un nouveau travail

Lorsque vous ajoutez un travail, vous devez spécifier un pipeline (file d'attente). Cela est comparable à un canal dans RabbitMQ ou un tube dans beanstalkd.

```php
<?php
Flight::queue()->selectPipeline('send_important_emails');
Flight::queue()->addJob(json_encode([ 'something' => 'that', 'ends' => 'up', 'a' => 'string' ]));
```

### Exécution d'un travailleur

Voici un exemple de fichier sur comment exécuter un travailleur.
```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// Connexion PDO
$PDO = new PDO('mysql:dbname=testdb;host=127.0.0.1', 'user', 'pass');
$Job_Queue->addQueueConnection($PDO);

// ou si vous utilisez beanstalkd/Pheanstalk
$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
$Job_Queue->addQueueConnection($pheanstalk);

$Job_Queue->watchPipeline('send_important_emails');
while(true) {
	$job = $Job_Queue->getNextJobAndReserve();

	// ajustez selon ce qui vous fait dormir mieux la nuit (pour les files d'attente de base de données uniquement, beanstalkd n'a pas besoin de cette instruction conditionnelle)
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
			// cela le retire de la file d'attente prête et le met dans une autre file d'attente qui peut être récupérée et "kickée" plus tard.
			$Job_Queue->buryJob($job);
		}
	} catch(Exception $e) {
		$Job_Queue->buryJob($job);
	}
}
```

# Gestion des processus longs avec Supervisord

Supervisord est un système de contrôle de processus qui garantit que vos processus travailleurs restent en cours d'exécution en continu. Voici un guide plus complet sur la façon de le configurer avec votre travailleur Simple Job Queue :

### Installation de Supervisord

```bash
# Sur Ubuntu/Debian
sudo apt-get install supervisor

# Sur CentOS/RHEL
sudo yum install supervisor

# Sur macOS avec Homebrew
brew install supervisor
```

### Création d'un script de travailleur

Tout d'abord, enregistrez votre code de travailleur dans un fichier PHP dédié :

```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// Connexion PDO
$PDO = new PDO('mysql:dbname=your_database;host=127.0.0.1', 'username', 'password');
$Job_Queue->addQueueConnection($PDO);

// Définir le pipeline à surveiller
$Job_Queue->watchPipeline('send_important_emails');

// Journaliser le début du travailleur
echo date('Y-m-d H:i:s') . " - Travailleur démarré\n";

while(true) {
    $job = $Job_Queue->getNextJobAndReserve();

    if(empty($job)) {
        usleep(500000); // Dormir pendant 0,5 secondes
        continue;
    }

    echo date('Y-m-d H:i:s') . " - Traitement du travail {$job['id']}\n";
    $payload = json_decode($job['payload'], true);

    try {
        $result = doSomethingThatDoesSomething($payload);

        if($result === true) {
            $Job_Queue->deleteJob($job);
            echo date('Y-m-d H:i:s') . " - Travail {$job['id']} terminé avec succès\n";
        } else {
            $Job_Queue->buryJob($job);
            echo date('Y-m-d H:i:s') . " - Travail {$job['id']} échoué, enterré\n";
        }
    } catch(Exception $e) {
        $Job_Queue->buryJob($job);
        echo date('Y-m-d H:i:s') . " - Exception lors du traitement du travail {$job['id']}: {$e->getMessage()}\n";
    }
}
```

### Configuration de Supervisord

Créez un fichier de configuration pour votre travailleur :

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

### Options de configuration clés :

- `command`: La commande pour exécuter votre travailleur
- `directory`: Répertoire de travail pour le travailleur
- `autostart`: Démarrer automatiquement lorsque supervisord démarre
- `autorestart`: Redémarrer automatiquement si le processus se termine
- `startretries`: Nombre de fois à réessayer de démarrer s'il échoue
- `stderr_logfile`/`stdout_logfile`: Emplacements des fichiers journaux
- `user`: Utilisateur système pour exécuter le processus
- `numprocs`: Nombre d'instances du travailleur à exécuter
- `process_name`: Format de nommage pour plusieurs processus de travailleur

### Gestion des travailleurs avec Supervisorctl

Après avoir créé ou modifié la configuration :

```bash
# Recharger la configuration du superviseur
sudo supervisorctl reread
sudo supervisorctl update

# Contrôler des processus de travailleur spécifiques
sudo supervisorctl start email_worker:*
sudo supervisorctl stop email_worker:*
sudo supervisorctl restart email_worker:*
sudo supervisorctl status email_worker:*
```

### Exécution de plusieurs pipelines

Pour plusieurs pipelines, créez des fichiers de travailleurs et des configurations distincts :

```ini
[program:email_worker]
command=php /path/to/email_worker.php
# ... autres configurations ...

[program:notification_worker]
command=php /path/to/notification_worker.php
# ... autres configurations ...
```

### Surveillance et journaux

Vérifiez les journaux pour surveiller l'activité du travailleur :

```bash
# Voir les journaux
sudo tail -f /var/log/simple_job_queue.log

# Vérifier le statut
sudo supervisorctl status
```

Cette configuration garantit que vos travailleurs de tâches continuent d'exécuter même après des plantages, des redémarrages de serveur ou d'autres problèmes, rendant votre système de file d'attente fiable pour les environnements de production.