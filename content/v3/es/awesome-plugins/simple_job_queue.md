# Cola de Trabajos Simple

La Cola de Trabajos Simple es una biblioteca que se puede utilizar para procesar trabajos de forma asíncrona. Se puede usar con beanstalkd, MySQL/MariaDB, SQLite y PostgreSQL.

## Instalar
```bash
composer require n0nag0n/simple-job-queue
```

## Uso

Para que esto funcione, necesitas una manera de agregar trabajos a la cola y una manera de procesar los trabajos (un trabajador). A continuación se presentan ejemplos de cómo agregar un trabajo a la cola y cómo procesar el trabajo.

## Agregando a Flight

Agregar esto a Flight es simple y se hace utilizando el método `register()`. A continuación se muestra un ejemplo de cómo agregar esto a Flight.

```php
<?php
require 'vendor/autoload.php';

// Cambia ['mysql'] a ['beanstalkd'] si deseas usar beanstalkd
Flight::register('queue', n0nag0n\Job_Queue::class, ['mysql'], function($Job_Queue) {
	// si ya tienes una conexión PDO en Flight::db();
	$Job_Queue->addQueueConnection(Flight::db());

	// o si estás usando beanstalkd/Pheanstalk
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
// Conexión PDO
$PDO = new PDO('mysql:dbname=testdb;host=127.0.0.1', 'user', 'pass');
$Job_Queue->addQueueConnection($PDO);

// o si estás usando beanstalkd/Pheanstalk
$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
$Job_Queue->addQueueConnection($pheanstalk);

$Job_Queue->watchPipeline('send_important_emails');
while(true) {
	$job = $Job_Queue->getNextJobAndReserve();

	// ajusta lo que te haga dormir mejor por la noche (solo para colas de base de datos, beanstalkd no necesita esta declaración if)
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
			// esto lo saca de la cola lista y lo coloca en otra cola que puede ser recogida y "patada" más tarde.
			$Job_Queue->buryJob($job);
		}
	} catch(Exception $e) {
		$Job_Queue->buryJob($job);
	}
}
```

# Manejo de Procesos Largos con Supervisord

Supervisord es un sistema de control de procesos que garantiza que tus procesos de trabajo sigan funcionando continuamente. Aquí hay una guía más completa sobre cómo configurarlo con tu trabajador de Cola de Trabajos Simple:

### Instalando Supervisord

```bash
# En Ubuntu/Debian
sudo apt-get install supervisor

# En CentOS/RHEL
sudo yum install supervisor

# En macOS con Homebrew
brew install supervisor
```

### Creando un Script de Trabajador

Primero, guarda tu código de trabajador en un archivo PHP dedicado:

```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// Conexión PDO
$PDO = new PDO('mysql:dbname=your_database;host=127.0.0.1', 'username', 'password');
$Job_Queue->addQueueConnection($PDO);

// Establecer el pipeline a observar
$Job_Queue->watchPipeline('send_important_emails');

// Registrar inicio del trabajador
echo date('Y-m-d H:i:s') . " - Trabajador iniciado\n";

while(true) {
    $job = $Job_Queue->getNextJobAndReserve();

    if(empty($job)) {
        usleep(500000); // Dormir durante 0.5 segundos
        continue;
    }

    echo date('Y-m-d H:i:s') . " - Procesando trabajo {$job['id']}\n";
    $payload = json_decode($job['payload'], true);

    try {
        $result = doSomethingThatDoesSomething($payload);

        if($result === true) {
            $Job_Queue->deleteJob($job);
            echo date('Y-m-d H:i:s') . " - Trabajo {$job['id']} completado con éxito\n";
        } else {
            $Job_Queue->buryJob($job);
            echo date('Y-m-d H:i:s') . " - Trabajo {$job['id']} falló, enterrado\n";
        }
    } catch(Exception $e) {
        $Job_Queue->buryJob($job);
        echo date('Y-m-d H:i:s') . " - Excepción al procesar el trabajo {$job['id']}: {$e->getMessage()}\n";
    }
}
```

### Configurando Supervisord

Crea un archivo de configuración para tu trabajador:

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

### Opciones Clave de Configuración:

- `command`: El comando para ejecutar tu trabajador
- `directory`: Directorio de trabajo para el trabajador
- `autostart`: Iniciar automáticamente cuando supervisord inicia
- `autorestart`: Reiniciar automáticamente si el proceso sale
- `startretries`: Número de veces para intentar iniciar si falla
- `stderr_logfile`/`stdout_logfile`: Ubicaciones de los archivos de registro
- `user`: Usuario del sistema para ejecutar el proceso
- `numprocs`: Número de instancias de trabajador a ejecutar
- `process_name`: Formato de nombre para múltiples procesos de trabajador

### Gestionando Trabajadores con Supervisorctl

Después de crear o modificar la configuración:

```bash
# Recargar configuración de supervisor
sudo supervisorctl reread
sudo supervisorctl update

# Controlar procesos de trabajadores específicos
sudo supervisorctl start email_worker:*
sudo supervisorctl stop email_worker:*
sudo supervisorctl restart email_worker:*
sudo supervisorctl status email_worker:*
```

### Ejecutando Múltiples Pipelines

Para múltiples pipelines, crea archivos y configuraciones de trabajador separados:

```ini
[program:email_worker]
command=php /path/to/email_worker.php
# ... otras configuraciones ...

[program:notification_worker]
command=php /path/to/notification_worker.php
# ... otras configuraciones ...
```

### Monitoreo y Registros

Verifica los registros para monitorear la actividad del trabajador:

```bash
# Ver registros
sudo tail -f /var/log/simple_job_queue.log

# Verificar estado
sudo supervisorctl status
```

Esta configuración asegura que tus trabajadores de trabajos continúen funcionando incluso después de fallos, reinicios del servidor u otros problemas, haciendo que tu sistema de colas sea confiable para entornos de producción.