# Pengujian Unit

## Gambaran Umum

Pengujian unit di Flight membantu Anda memastikan aplikasi Anda berperilaku sesuai harapan, menangkap bug lebih awal, dan membuat kode dasar Anda lebih mudah dipelihara. Flight dirancang untuk bekerja dengan lancar dengan [PHPUnit](https://phpunit.de/), framework pengujian PHP paling populer.

## Pemahaman

Pengujian unit memeriksa perilaku potongan kecil aplikasi Anda (seperti controller atau service) secara terisolasi. Di Flight, ini berarti menguji bagaimana rute, controller, dan logika Anda merespons input yang berbeda—tanpa bergantung pada status global atau layanan eksternal nyata.

Prinsip kunci:
- **Uji perilaku, bukan implementasi:** Fokus pada apa yang dilakukan kode Anda, bukan bagaimana cara melakukannya.
- **Hindari status global:** Gunakan injeksi dependensi daripada `Flight::set()` atau `Flight::get()`.
- **Mock layanan eksternal:** Ganti hal-hal seperti database atau mailer dengan double pengujian.
- **Jaga pengujian cepat dan terfokus:** Pengujian unit tidak boleh menyentuh database atau API nyata.

## Penggunaan Dasar

### Menyiapkan PHPUnit

1. Instal PHPUnit dengan Composer:
   ```bash
   composer require --dev phpunit/phpunit
   ```
2. Buat direktori `tests` di root proyek Anda.
3. Tambahkan skrip pengujian ke `composer.json` Anda:
   ```json
   "scripts": {
       "test": "phpunit --configuration phpunit.xml"
   }
   ```
4. Buat file `phpunit.xml`:
   ```xml
   <?xml version="1.0" encoding="UTF-8"?>
   <phpunit bootstrap="vendor/autoload.php">
       <testsuites>
           <testsuite name="Flight Tests">
               <directory>tests</directory>
           </testsuite>
       </testsuites>
   </phpunit>
   ```

Sekarang Anda dapat menjalankan pengujian dengan `composer test`.

### Menguji Penangan Rute Sederhana

Misalkan Anda memiliki rute yang memvalidasi email:

```php
// index.php
$app->route('POST /register', [ UserController::class, 'register' ]);

// UserController.php
class UserController {
    protected $app;
    public function __construct(flight\Engine $app) {
        $this->app = $app;
    }
    public function register() {
        $email = $this->app->request()->data->email;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->app->json(['status' => 'error', 'message' => 'Invalid email']);
        }
        return $this->app->json(['status' => 'success', 'message' => 'Valid email']);
    }
}
```

Pengujian sederhana untuk controller ini:

```php
use PHPUnit\Framework\TestCase;
use flight\Engine;

class UserControllerTest extends TestCase {
    public function testValidEmailReturnsSuccess() {
        $app = new Engine();
        $app->request()->data->email = 'test@example.com';
        $controller = new UserController($app);
        $controller->register();
        $response = $app->response()->getBody();
        $output = json_decode($response, true);
        $this->assertEquals('success', $output['status']);
        $this->assertEquals('Valid email', $output['message']);
    }

    public function testInvalidEmailReturnsError() {
        $app = new Engine();
        $app->request()->data->email = 'invalid-email';
        $controller = new UserController($app);
        $controller->register();
        $response = $app->response()->getBody();
        $output = json_decode($response, true);
        $this->assertEquals('error', $output['status']);
        $this->assertEquals('Invalid email', $output['message']);
    }
}
```

**Tips:**
- Simulasikan data POST menggunakan `$app->request()->data`.
- Hindari menggunakan static `Flight::` di pengujian Anda—gunakan instance `$app`.

### Menggunakan Injeksi Dependensi untuk Controller yang Dapat Diuji

Injeksi dependensi (seperti database atau mailer) ke dalam controller Anda untuk membuatnya mudah dimock di pengujian:

```php
use flight\database\PdoWrapper;

class UserController {
    protected $app;
    protected $db;
    protected $mailer;
    public function __construct($app, $db, $mailer) {
        $this->app = $app;
        $this->db = $db;
        $this->mailer = $mailer;
    }
    public function register() {
        $email = $this->app->request()->data->email;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->app->json(['status' => 'error', 'message' => 'Invalid email']);
        }
        $this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
        $this->mailer->sendWelcome($email);
        return $this->app->json(['status' => 'success', 'message' => 'User registered']);
    }
}
```

Dan pengujian dengan mock:

```php
use PHPUnit\Framework\TestCase;

class UserControllerDICTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {
        $mockDb = $this->createMock(flight\database\PdoWrapper::class);
        $mockDb->method('runQuery')->willReturn(true);
        $mockMailer = new class {
            public $sentEmail = null;
            public function sendWelcome($email) { $this->sentEmail = $email; return true; }
        };
        $app = new flight\Engine();
        $app->request()->data->email = 'test@example.com';
        $controller = new UserController($app, $mockDb, $mockMailer);
        $controller->register();
        $response = $app->response()->getBody();
        $result = json_decode($response, true);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('User registered', $result['message']);
        $this->assertEquals('test@example.com', $mockMailer->sentEmail);
    }
}
```

## Penggunaan Lanjutan

- **Mocking:** Gunakan mock bawaan PHPUnit atau kelas anonim untuk mengganti dependensi.
- **Menguji controller secara langsung:** Instansiasi controller dengan `Engine` baru dan mock dependensi.
- **Hindari over-mocking:** Biarkan logika nyata berjalan jika memungkinkan; hanya mock layanan eksternal.

## Lihat Juga

- [Panduan Pengujian Unit](/guides/unit-testing) - Panduan komprehensif tentang praktik terbaik pengujian unit.
- [Container Injeksi Dependensi](/learn/dependency-injection-container) - Cara menggunakan DIC untuk mengelola dependensi dan meningkatkan kemampuan pengujian.
- [Memperluas](/learn/extending) - Cara menambahkan helper sendiri atau mengoverride kelas inti.
- [Wrapper PDO](/learn/pdo-wrapper) - Menyederhanakan interaksi database dan lebih mudah dimock di pengujian.
- [Permintaan](/learn/requests) - Menangani permintaan HTTP di Flight.
- [Respons](/learn/responses) - Mengirim respons ke pengguna.
- [Pengujian Unit dan Prinsip SOLID](/learn/unit-testing-and-solid-principles) - Pelajari bagaimana prinsip SOLID dapat meningkatkan pengujian unit Anda.

## Pemecahan Masalah

- Hindari menggunakan status global (`Flight::set()`, `$_SESSION`, dll.) di kode dan pengujian Anda.
- Jika pengujian Anda lambat, mungkin Anda sedang menulis pengujian integrasi—mock layanan eksternal untuk menjaga pengujian unit tetap cepat.
- Jika pengaturan pengujian rumit, pertimbangkan untuk merestrukturisasi kode Anda untuk menggunakan injeksi dependensi.

## Changelog

- v3.15.0 - Ditambahkan contoh untuk injeksi dependensi dan mocking.