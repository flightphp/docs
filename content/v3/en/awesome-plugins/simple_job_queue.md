# Simple Job Queue

Simple Job Queue is a library that can be used to process jobs asynchronously. It can be used with beanstalkd, MySQL/MariaDB, SQLite, and PostgreSQL.

## Install
```bash
composer require n0nag0n/simple-job-queue
```

## Usage

In order for this to work, you need a way to add jobs to the queue and a way to process the jobs (a worker). Below are examples of how to add a job to the queue and how to process the job.


## Adding to Flight

Adding this to Flight is simple and is done using the `register()` method. Below is an example of how to add this to Flight.

```php
<?php
require 'vendor/autoload.php';

// Change ['mysql'] to ['beanstalkd'] if you want to use beanstalkd
Flight::register('queue', n0nag0n\Job_Queue::class, ['mysql'], function($Job_Queue) {
	// if you have a PDO connection already on Flight::db();
	$Job_Queue->addQueueConnection(Flight::db());

	// or if you're using beanstalkd/Pheanstalk
	$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
	$Job_Queue->addQueueConnection($pheanstalk);
});
```

### Adding a new job

When you add a job, you need to specify a pipeline (queue). This is comparable to a channel in RabbitMQ or a tube in beanstalkd.

```php
<?php
Flight::queue()->selectPipeline('send_important_emails');
Flight::queue()->addJob(json_encode([ 'something' => 'that', 'ends' => 'up', 'a' => 'string' ]));
```

### Running a worker

Here is an example file of how to run a worker.
```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// PDO connection
$PDO = new PDO('mysql:dbname=testdb;host=127.0.0.1', 'user', 'pass');
$Job_Queue->addQueueConnection($PDO);

// or if you're using beanstalkd/Pheanstalk
$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
$Job_Queue->addQueueConnection($pheanstalk);

$Job_Queue->watchPipeline('send_important_emails');
while(true) {
	$job = $Job_Queue->getNextJobAndReserve();

	// adjust to whatever makes you sleep better at night (for database queues only, beanstalkd does not need this if statement)
	if(empty($job)) {
		usleep(500000);
		continue;
	}

	echo "Processing {$job['id']}\n";
	$payload = json_decode($job['payload'], true);

	try {
		$result = doSomethingThatDoesSomething($payload);

		if($result === true) {
			$Job_Queue->deleteJob($job);
		} else {
			// this takes it out of the ready queue and puts it in another queue that can be picked up and "kicked" later.
			$Job_Queue->buryJob($job);
		}
	} catch(Exception $e) {
		$Job_Queue->buryJob($job);
	}
}
```

# Handling Long Processes with Supervisord

Supervisord is a process control system that ensures your worker processes stay running continuously. Here's a more complete guide on setting it up with your Simple Job Queue worker:

### Installing Supervisord

```bash
# On Ubuntu/Debian
sudo apt-get install supervisor

# On CentOS/RHEL
sudo yum install supervisor

# On macOS with Homebrew
brew install supervisor
```

### Creating a Worker Script

First, save your worker code to a dedicated PHP file:

```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// PDO connection
$PDO = new PDO('mysql:dbname=your_database;host=127.0.0.1', 'username', 'password');
$Job_Queue->addQueueConnection($PDO);

// Set the pipeline to watch
$Job_Queue->watchPipeline('send_important_emails');

// Log start of worker
echo date('Y-m-d H:i:s') . " - Worker started\n";

while(true) {
    $job = $Job_Queue->getNextJobAndReserve();

    if(empty($job)) {
        usleep(500000); // Sleep for 0.5 seconds
        continue;
    }

    echo date('Y-m-d H:i:s') . " - Processing job {$job['id']}\n";
    $payload = json_decode($job['payload'], true);

    try {
        $result = doSomethingThatDoesSomething($payload);

        if($result === true) {
            $Job_Queue->deleteJob($job);
            echo date('Y-m-d H:i:s') . " - Job {$job['id']} completed successfully\n";
        } else {
            $Job_Queue->buryJob($job);
            echo date('Y-m-d H:i:s') . " - Job {$job['id']} failed, buried\n";
        }
    } catch(Exception $e) {
        $Job_Queue->buryJob($job);
        echo date('Y-m-d H:i:s') . " - Exception processing job {$job['id']}: {$e->getMessage()}\n";
    }
}
```

### Configuring Supervisord

Create a configuration file for your worker:

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

### Key Configuration Options:

- `command`: The command to run your worker
- `directory`: Working directory for the worker
- `autostart`: Start automatically when supervisord starts
- `autorestart`: Restart automatically if the process exits
- `startretries`: Number of times to retry starting if it fails
- `stderr_logfile`/`stdout_logfile`: Log file locations
- `user`: System user to run the process as
- `numprocs`: Number of worker instances to run
- `process_name`: Naming format for multiple worker processes

### Managing Workers with Supervisorctl

After creating or modifying the configuration:

```bash
# Reload supervisor configuration
sudo supervisorctl reread
sudo supervisorctl update

# Control specific worker processes
sudo supervisorctl start email_worker:*
sudo supervisorctl stop email_worker:*
sudo supervisorctl restart email_worker:*
sudo supervisorctl status email_worker:*
```

### Running Multiple Pipelines

For multiple pipelines, create separate worker files and configurations:

```ini
[program:email_worker]
command=php /path/to/email_worker.php
# ... other configs ...

[program:notification_worker]
command=php /path/to/notification_worker.php
# ... other configs ...
```

### Monitoring and Logs

Check logs to monitor worker activity:

```bash
# View logs
sudo tail -f /var/log/simple_job_queue.log

# Check status
sudo supervisorctl status
```

This setup ensures your job workers continue running even after crashes, server reboots, or other issues, making your queue system reliable for production environments.