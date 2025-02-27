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

### Handling long processes

Supervisord is going to be your jam. Look up the many, many articles on how to implement this. It takes a little bit of extra configuration, but it's worth it to keep the process up and running.
