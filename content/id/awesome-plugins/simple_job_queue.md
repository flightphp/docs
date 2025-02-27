# Antrian Pekerjaan Sederhana

Antrian Pekerjaan Sederhana adalah pustaka yang dapat digunakan untuk memproses pekerjaan secara asinkron. Ini dapat digunakan dengan beanstalkd, MySQL/MariaDB, SQLite, dan PostgreSQL.

## Instal
```bash
composer require n0nag0n/simple-job-queue
```

## Penggunaan

Agar ini berfungsi, Anda perlu cara untuk menambahkan pekerjaan ke antrian dan cara untuk memproses pekerjaan (pekerja). Berikut adalah contoh cara menambahkan pekerjaan ke antrian dan cara memproses pekerjaan.

## Menambahkan ke Flight

Menambahkan ini ke Flight itu sederhana dan dilakukan menggunakan metode `register()`. Berikut adalah contoh cara menambahkan ini ke Flight.

```php
<?php
require 'vendor/autoload.php';

Flight::register('queue', n0nag0n\Job_Queue::class, ['mysql'], function($Job_Queue) {
	// jika Anda sudah memiliki koneksi PDO di Flight::db();
	$Job_Queue->addQueueConnection(Flight::db());

	// atau jika Anda menggunakan beanstalkd/Pheanstalk
	$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
	$Job_Queue->addQueueConnection($pheanstalk);
});
```

### Menambahkan pekerjaan baru

Saat Anda menambahkan pekerjaan, Anda perlu menentukan jalur (antrian). Ini sebanding dengan saluran di RabbitMQ atau tabung di beanstalkd.

```php
<?php
Flight::queue()->selectPipeline('send_important_emails');
Flight::queue()->addJob(json_encode([ 'something' => 'that', 'ends' => 'up', 'a' => 'string' ]));
```

### Menjalankan pekerja

Berikut adalah contoh file tentang cara menjalankan pekerja.
```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// Koneksi PDO
$PDO = new PDO('mysql:dbname=testdb;host=127.0.0.1', 'user', 'pass');
$Job_Queue->addQueueConnection($PDO);

// atau jika Anda menggunakan beanstalkd/Pheanstalk
$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
$Job_Queue->addQueueConnection($pheanstalk);

$Job_Queue->watchPipeline('send_important_emails');
while(true) {
	$job = $Job_Queue->getNextJobAndReserve();

	// sesuaikan dengan apa pun yang membuat Anda tidur nyenyak di malam hari (hanya untuk antrian basis data, beanstalkd tidak memerlukan pernyataan if ini)
	if(empty($job)) {
		usleep(500000);
		continue;
	}

	echo "Memproses {$job['id']}\n";
	$payload = json_decode($job['payload'], true);

	try {
		$result = doSomethingThatDoesSomething($payload);

		if($result === true) {
			$Job_Queue->deleteJob($job);
		} else {
			// ini mengeluarkannya dari antrian siap dan menempatkannya di antrian lain yang dapat diambil dan "didorong" nanti.
			$Job_Queue->buryJob($job);
		}
	} catch(Exception $e) {
		$Job_Queue->buryJob($job);
	}
}
```

### Menangani proses yang panjang

Supervisord akan menjadi solusi Anda. Cari banyak artikel tentang cara menerapkannya. Ini membutuhkan sedikit konfigurasi tambahan, tetapi sangat berharga untuk menjaga proses berjalan dan aktif.