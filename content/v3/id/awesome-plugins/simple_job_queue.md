# Antrean Pekerjaan Sederhana

Antrean Pekerjaan Sederhana adalah sebuah pustaka yang dapat digunakan untuk memproses pekerjaan secara asinkron. Ini dapat digunakan dengan beanstalkd, MySQL/MariaDB, SQLite, dan PostgreSQL.

## Instal
```bash
composer require n0nag0n/simple-job-queue
```

## Penggunaan

Agar ini dapat berfungsi, Anda memerlukan cara untuk menambahkan pekerjaan ke antrean dan cara untuk memproses pekerjaan (pekerja). Berikut adalah contoh tentang cara menambahkan pekerjaan ke antrean dan cara memproses pekerjaan.

## Menambahkan ke Flight

Menambahkan ini ke Flight sangat sederhana dan dilakukan dengan menggunakan metode `register()`. Berikut adalah contoh cara menambahkan ini ke Flight.

```php
<?php
require 'vendor/autoload.php';

// Ubah ['mysql'] menjadi ['beanstalkd'] jika Anda ingin menggunakan beanstalkd
Flight::register('queue', n0nag0n\Job_Queue::class, ['mysql'], function($Job_Queue) {
	// jika Anda sudah memiliki koneksi PDO di Flight::db();
	$Job_Queue->addQueueConnection(Flight::db());

	// atau jika Anda menggunakan beanstalkd/Pheanstalk
	$pheanstalk = Pheanstalk\Pheanstalk::create('127.0.0.1');
	$Job_Queue->addQueueConnection($pheanstalk);
});
```

### Menambahkan pekerjaan baru

Saat Anda menambahkan pekerjaan, Anda perlu menentukan sebuah pipeline (antrean). Ini sebanding dengan sebuah saluran di RabbitMQ atau sebuah tabung di beanstalkd.

```php
<?php
Flight::queue()->selectPipeline('send_important_emails');
Flight::queue()->addJob(json_encode([ 'something' => 'that', 'ends' => 'up', 'a' => 'string' ]));
```

### Menjalankan seorang pekerja

Berikut adalah contoh file tentang cara menjalankan seorang pekerja.
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

	// sesuaikan dengan apa pun yang membuat Anda tidur lebih nyenyak di malam hari (hanya untuk antrean basis data, beanstalkd tidak memerlukan pernyataan if ini)
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
			// ini mengeluarkannya dari antrean siap dan menempatkannya dalam antrean lain yang dapat diambil dan "dikejutkan" nanti.
			$Job_Queue->buryJob($job);
		}
	} catch(Exception $e) {
		$Job_Queue->buryJob($job);
	}
}
```

# Menangani Proses Panjang dengan Supervisord

Supervisord adalah sistem kontrol proses yang memastikan bahwa proses pekerja Anda tetap berjalan terus-menerus. Berikut adalah panduan yang lebih lengkap tentang cara mengaturnya dengan pekerja Antrean Pekerjaan Sederhana Anda:

### Menginstal Supervisord

```bash
# Di Ubuntu/Debian
sudo apt-get install supervisor

# Di CentOS/RHEL
sudo yum install supervisor

# Di macOS dengan Homebrew
brew install supervisor
```

### Membuat Skrip Pekerja

Pertama, simpan kode pekerja Anda ke dalam file PHP yang didedikasikan:

```php
<?php

require 'vendor/autoload.php';

$Job_Queue = new n0nag0n\Job_Queue('mysql');
// Koneksi PDO
$PDO = new PDO('mysql:dbname=your_database;host=127.0.0.1', 'username', 'password');
$Job_Queue->addQueueConnection($PDO);

// Tentukan pipeline untuk diawasi
$Job_Queue->watchPipeline('send_important_emails');

// Catat awal pekerja
echo date('Y-m-d H:i:s') . " - Pekerja dimulai\n";

while(true) {
    $job = $Job_Queue->getNextJobAndReserve();

    if(empty($job)) {
        usleep(500000); // Tidur selama 0.5 detik
        continue;
    }

    echo date('Y-m-d H:i:s') . " - Memproses pekerjaan {$job['id']}\n";
    $payload = json_decode($job['payload'], true);

    try {
        $result = doSomethingThatDoesSomething($payload);

        if($result === true) {
            $Job_Queue->deleteJob($job);
            echo date('Y-m-d H:i:s') . " - Pekerjaan {$job['id']} berhasil diselesaikan\n";
        } else {
            $Job_Queue->buryJob($job);
            echo date('Y-m-d H:i:s') . " - Pekerjaan {$job['id']} gagal, dibuang\n";
        }
    } catch(Exception $e) {
        $Job_Queue->buryJob($job);
        echo date('Y-m-d H:i:s') . " - Pengecualian saat memproses pekerjaan {$job['id']}: {$e->getMessage()}\n";
    }
}
```

### Mengkonfigurasi Supervisord

Buat file konfigurasi untuk pekerja Anda:

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

### Opsi Konfigurasi Utama:

- `command`: Perintah untuk menjalankan pekerja Anda
- `directory`: Direktori kerja untuk pekerja
- `autostart`: Mulai secara otomatis saat supervisord dimulai
- `autorestart`: Mulai ulang secara otomatis jika proses keluar
- `startretries`: Jumlah kali untuk mencoba memulai jika gagal
- `stderr_logfile`/`stdout_logfile`: Lokasi file log
- `user`: Pengguna sistem untuk menjalankan proses
- `numprocs`: Jumlah instance pekerja yang akan dijalankan
- `process_name`: Format penamaan untuk beberapa proses pekerja

### Mengelola Pekerja dengan Supervisorctl

Setelah membuat atau mengubah konfigurasi:

```bash
# Muat ulang konfigurasi supervisor
sudo supervisorctl reread
sudo supervisorctl update

# Kontrol proses pekerja tertentu
sudo supervisorctl start email_worker:*
sudo supervisorctl stop email_worker:*
sudo supervisorctl restart email_worker:*
sudo supervisorctl status email_worker:*
```

### Menjalankan Beberapa Pipeline

Untuk beberapa pipeline, buat file pekerja dan konfigurasi terpisah:

```ini
[program:email_worker]
command=php /path/to/email_worker.php
# ... konfigurasi lainnya ...

[program:notification_worker]
command=php /path/to/notification_worker.php
# ... konfigurasi lainnya ...
```

### Memantau dan Log

Periksa log untuk memantau aktivitas pekerja:

```bash
# Lihat log
sudo tail -f /var/log/simple_job_queue.log

# Periksa status
sudo supervisorctl status
```

Pengaturan ini memastikan pekerja pekerjaan Anda terus berjalan meskipun setelah kerusakan, reboot server, atau masalah lainnya, menjadikan sistem antrean Anda andal untuk lingkungan produksi.