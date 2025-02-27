# Fila de Trabalho Simples

Fila de Trabalho Simples é uma biblioteca que pode ser usada para processar trabalhos de maneira assíncrona. Pode ser utilizada com beanstalkd, MySQL/MariaDB, SQLite e PostgreSQL.

## Instalar
```bash
composer require n0nag0n/simple-job-queue
```

## Uso

Para que isso funcione, você precisa de uma maneira de adicionar trabalhos à fila e uma maneira de processar os trabalhos (um trabalhador). Abaixo estão exemplos de como adicionar um trabalho à fila e como processar o trabalho.

## Adicionando ao Flight

Adicionar isso ao Flight é simples e é feito usando o método `register()`. Abaixo está um exemplo de como adicionar isso ao Flight.

```php
<?php
require 'vendor/autoload.php';

Flight::register('queue', n0nag0n\Job_Queue::class, ['mysql'], function($Job_Queue) {
	// se você já tiver uma conexão PDO em Flight::db();
	$Job_Queue->addQueueConnection(Flight::db());

	// ou se você estiver usando beanstalkd/Pheanstalk
	$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
	$Job_Queue->addQueueConnection($pheanstalk);
});
```

### Adicionando um novo trabalho

Quando você adiciona um trabalho, precisa especificar um pipeline (fila). Isso é comparável a um canal no RabbitMQ ou a um tubo no beanstalkd.

```php
<?php
Flight::queue()->selectPipeline('send_important_emails');
Flight::queue()->addJob(json_encode([ 'something' => 'that', 'ends' => 'up', 'a' => 'string' ]));
```

### Executando um trabalhador

Aqui está um exemplo de arquivo de como executar um trabalhador.
```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// conexão PDO
$PDO = new PDO('mysql:dbname=testdb;host=127.0.0.1', 'user', 'pass');
$Job_Queue->addQueueConnection($PDO);

// ou se você estiver usando beanstalkd/Pheanstalk
$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
$Job_Queue->addQueueConnection($pheanstalk);

$Job_Queue->watchPipeline('send_important_emails');
while(true) {
	$job = $Job_Queue->getNextJobAndReserve();

	// ajuste para o que te faz dormir melhor à noite (apenas para filas de banco de dados, beanstalkd não precisa desta instrução if)
	if(empty($job)) {
		usleep(500000);
		continue;
	}

	echo "Processando {$job['id']}\n";
	$payload = json_decode($job['payload'], true);

	try {
		$result = doSomethingThatDoesSomething($payload);

		if($result === true) {
			$Job_Queue->deleteJob($job);
		} else {
			// isso retira da fila pronta e coloca em outra fila que pode ser retirada e "chutada" mais tarde.
			$Job_Queue->buryJob($job);
		}
	} catch(Exception $e) {
		$Job_Queue->buryJob($job);
	}
}
```

### Tratando processos longos

O Supervisord vai ser a sua solução. Pesquise diversos artigos sobre como implementar isso. Leva um pouco de configuração extra, mas vale a pena para manter o processo funcionando.