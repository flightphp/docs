# Cola de Trabajos Simple

La Cola de Trabajos Simple es una biblioteca que se puede usar para procesar trabajos de manera asincrónica. Se puede utilizar con beanstalkd, MySQL/MariaDB, SQLite y PostgreSQL.

## Instalación
```bash
composer require n0nag0n/simple-job-queue
```

## Uso

Para que esto funcione, necesitas una forma de agregar trabajos a la cola y una forma de procesar los trabajos (un trabajador). A continuación se presentan ejemplos de cómo agregar un trabajo a la cola y cómo procesar el trabajo.

## Agregando a Flight

Agregar esto a Flight es simple y se hace utilizando el método `register()`. A continuación se muestra un ejemplo de cómo agregar esto a Flight.

```php
<?php
require 'vendor/autoload.php';

Flight::register('queue', n0nag0n\Job_Queue::class, ['mysql'], function($Job_Queue) {
	// si ya tienes una conexión PDO en Flight::db();
	$Job_Queue->addQueueConnection(Flight::db());

	// o si estás utilizando beanstalkd/Pheanstalk
	$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
	$Job_Queue->addQueueConnection($pheanstalk);
});
```

### Agregando un nuevo trabajo

Cuando agregas un trabajo, necesitas especificar un pipeline (cola). Esto es comparable a un canal en RabbitMQ o un tubo en beanstalkd.

```php
<?php
Flight::queue()->selectPipeline('send_important_emails');
Flight::queue()->addJob(json_encode([ 'something' => 'that', 'ends' => 'up', 'a' => 'string' ]));
```

### Ejecutando un trabajador

Aquí hay un archivo de ejemplo de cómo ejecutar un trabajador.
```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// conexión PDO
$PDO = new PDO('mysql:dbname=testdb;host=127.0.0.1', 'user', 'pass');
$Job_Queue->addQueueConnection($PDO);

// o si estás utilizando beanstalkd/Pheanstalk
$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
$Job_Queue->addQueueConnection($pheanstalk);

$Job_Queue->watchPipeline('send_important_emails');
while(true) {
	$job = $Job_Queue->getNextJobAndReserve();

	// ajusta a lo que te haga dormir mejor por la noche (solo para colas de base de datos, beanstalkd no necesita esta declaración if)
	if(empty($job)) {
		usleep(500000);
		continue;
	}

	echo "Procesando {$job['id']}\n";
	$payload = json_decode($job['payload'], true);

	try {
		$result = doSomethingThatDoesSomething($payload);

		if($result === true) {
			$Job_Queue->deleteJob($job);
		} else {
			// esto lo saca de la cola lista y lo pone en otra cola que se puede recoger y "patentar" más tarde.
			$Job_Queue->buryJob($job);
		}
	} catch(Exception $e) {
		$Job_Queue->buryJob($job);
	}
}
```

### Manejo de procesos largos

Supervisord será tu aliado. Busca los muchos, muchos artículos sobre cómo implementarlo. Requiere un poco de configuración adicional, pero vale la pena mantener el proceso en funcionamiento.