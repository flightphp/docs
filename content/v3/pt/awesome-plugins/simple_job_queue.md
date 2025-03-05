# Fila de Trabalho Simples

A Fila de Trabalho Simples é uma biblioteca que pode ser usada para processar trabalhos de forma assíncrona. Pode ser usada com beanstalkd, MySQL/MariaDB, SQLite e PostgreSQL.

## Instalação
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

// Mude ['mysql'] para ['beanstalkd'] se você quiser usar beanstalkd
Flight::register('queue', n0nag0n\Job_Queue::class, ['mysql'], function($Job_Queue) {
	// se você já tiver uma conexão PDO em Flight::db();
	$Job_Queue->addQueueConnection(Flight::db());

	// ou se você estiver usando beanstalkd/Pheanstalk
	$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
	$Job_Queue->addQueueConnection($pheanstalk);
});
```

### Adicionando um novo trabalho

Quando você adiciona um trabalho, precisa especificar um pipeline (fila). Isso é comparável a um canal no RabbitMQ ou um tubo no beanstalkd.

```php
<?php
Flight::queue()->selectPipeline('send_important_emails');
Flight::queue()->addJob(json_encode([ 'something' => 'that', 'ends' => 'up', 'a' => 'string' ]));
```

### Executando um trabalhador

Aqui está um arquivo de exemplo de como executar um trabalhador.
```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// Conexão PDO
$PDO = new PDO('mysql:dbname=testdb;host=127.0.0.1', 'user', 'pass');
$Job_Queue->addQueueConnection($PDO);

// ou se você estiver usando beanstalkd/Pheanstalk
$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
$Job_Queue->addQueueConnection($pheanstalk);

$Job_Queue->watchPipeline('send_important_emails');
while(true) {
	$job = $Job_Queue->getNextJobAndReserve();

	// ajuste para o que te faz dormir melhor à noite (para filas de banco de dados apenas, beanstalkd não precisa dessa instrução if)
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
			// isso o tira da fila de prontos e o coloca em outra fila que pode ser coletada e "chutada" mais tarde.
			$Job_Queue->buryJob($job);
		}
	} catch(Exception $e) {
		$Job_Queue->buryJob($job);
	}
}
```

# Manipulando Processos Longos com Supervisord

O Supervisord é um sistema de controle de processos que garante que seus processos de trabalhadores permaneçam em execução continuamente. Aqui está um guia mais completo sobre como configurá-lo com seu trabalhador da Fila de Trabalho Simples:

### Instalando o Supervisord

```bash
# No Ubuntu/Debian
sudo apt-get install supervisor

# No CentOS/RHEL
sudo yum install supervisor

# No macOS com Homebrew
brew install supervisor
```

### Criando um Script de Trabalhador

Primeiro, salve seu código de trabalhador em um arquivo PHP dedicado:

```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// Conexão PDO
$PDO = new PDO('mysql:dbname=your_database;host=127.0.0.1', 'username', 'password');
$Job_Queue->addQueueConnection($PDO);

// Defina o pipeline a ser monitorado
$Job_Queue->watchPipeline('send_important_emails');

// Registre o início do trabalhador
echo date('Y-m-d H:i:s') . " - Trabalhador iniciado\n";

while(true) {
    $job = $Job_Queue->getNextJobAndReserve();

    if(empty($job)) {
        usleep(500000); // Durma por 0,5 segundos
        continue;
    }

    echo date('Y-m-d H:i:s') . " - Processando trabalho {$job['id']}\n";
    $payload = json_decode($job['payload'], true);

    try {
        $result = doSomethingThatDoesSomething($payload);

        if($result === true) {
            $Job_Queue->deleteJob($job);
            echo date('Y-m-d H:i:s') . " - Trabalho {$job['id']} completado com sucesso\n";
        } else {
            $Job_Queue->buryJob($job);
            echo date('Y-m-d H:i:s') . " - Trabalho {$job['id']} falhou, enterrado\n";
        }
    } catch(Exception $e) {
        $Job_Queue->buryJob($job);
        echo date('Y-m-d H:i:s') . " - Exceção ao processar o trabalho {$job['id']}: {$e->getMessage()}\n";
    }
}
```

### Configurando o Supervisord

Crie um arquivo de configuração para seu trabalhador:

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

### Principais Opções de Configuração:

- `command`: O comando para executar seu trabalhador
- `directory`: Diretório de trabalho para o trabalhador
- `autostart`: Iniciar automaticamente quando o supervisord iniciar
- `autorestart`: Reiniciar automaticamente se o processo sair
- `startretries`: Número de tentativas para reiniciar se falhar
- `stderr_logfile`/`stdout_logfile`: Localizações dos arquivos de log
- `user`: Usuário do sistema para executar o processo
- `numprocs`: Número de instâncias de trabalhador a serem executadas
- `process_name`: Formato de nomenclatura para vários processos de trabalhadores

### Gerenciando Trabalhadores com Supervisorctl

Após criar ou modificar a configuração:

```bash
# Recarregar a configuração do supervisor
sudo supervisorctl reread
sudo supervisorctl update

# Controlar processos de trabalhadores específicos
sudo supervisorctl start email_worker:*
sudo supervisorctl stop email_worker:*
sudo supervisorctl restart email_worker:*
sudo supervisorctl status email_worker:*
```

### Executando Múltiplos Pipelines

Para múltiplos pipelines, crie arquivos de trabalhadores e configurações separados:

```ini
[program:email_worker]
command=php /path/to/email_worker.php
# ... outras configurações ...

[program:notification_worker]
command=php /path/to/notification_worker.php
# ... outras configurações ...
```

### Monitoramento e Logs

Verifique os logs para monitorar a atividade dos trabalhadores:

```bash
# Ver logs
sudo tail -f /var/log/simple_job_queue.log

# Verificar status
sudo supervisorctl status
```

Essa configuração garante que seus trabalhadores de tarefas continuem em execução mesmo após falhas, reinicializações de servidor ou outros problemas, tornando seu sistema de fila confiável para ambientes de produção.