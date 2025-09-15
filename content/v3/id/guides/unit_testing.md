# Pengujian Unit di Flight PHP dengan PHPUnit

Panduan ini memperkenalkan pengujian unit di Flight PHP menggunakan [PHPUnit](https://phpunit.de/), ditujukan untuk pemula yang ingin memahami *mengapa* pengujian unit penting dan bagaimana menerapkannya secara praktis. Kami akan fokus pada pengujian *perilaku*—memastikan aplikasi Anda berfungsi seperti yang diharapkan, seperti mengirim email atau menyimpan catatan—bukan perhitungan sepele. Kami akan mulai dengan penangan rute sederhana [/learn/routing](/learn/routing) dan maju ke [controller](/learn/routing) yang lebih kompleks, dengan memasukkan [dependency injection](/learn/dependency-injection-container) (DI) dan mocking layanan pihak ketiga.

## Mengapa Pengujian Unit?

Pengujian unit memastikan kode Anda berperilaku seperti yang diharapkan, menangkap bug sebelum mencapai produksi. Ini sangat berharga di Flight, di mana routing ringan dan fleksibilitas dapat menyebabkan interaksi yang kompleks. Bagi pengembang solo atau tim, tes unit bertindak sebagai jaring pengaman, mendokumentasikan perilaku yang diharapkan dan mencegah regresi saat Anda mengunjungi kode lagi nanti. Tes juga meningkatkan desain: kode yang sulit diuji sering menandakan kelas yang terlalu kompleks atau terlalu ketat.

Tidak seperti contoh sederhana (misalnya, menguji `x * y = z`), kami akan fokus pada perilaku dunia nyata, seperti memvalidasi input, menyimpan data, atau memicu aksi seperti email. Tujuan kami adalah membuat pengujian mudah diakses dan bermakna.

## Prinsip Panduan Umum

1. **Uji Perilaku, Bukan Implementasi**: Fokus pada hasil (misalnya, “email terkirim” atau “catatan disimpan”) daripada detail internal. Ini membuat tes lebih tahan terhadap refactoring.
2. **Berhenti menggunakan `Flight::`**: Metode statis Flight sangat nyaman, tetapi menyulitkan pengujian. Anda harus terbiasa menggunakan variabel `$app` dari `$app = Flight::app();`. `$app` memiliki semua metode yang sama dengan `Flight::`. Anda masih bisa menggunakan `$app->route()` atau `$this->app->json()` di controller Anda, dll. Anda juga harus menggunakan router Flight yang sebenarnya dengan `$router = $app->router()` dan kemudian menggunakan `$router->get()`, `$router->post()`, `$router->group()`, dll. Lihat [Routing](/learn/routing).
3. **Jaga Tes Cepat**: Tes yang cepat mendorong eksekusi yang sering. Hindari operasi lambat seperti panggilan basis data di tes unit. Jika Anda memiliki tes lambat, itu menandakan Anda sedang menulis tes integrasi, bukan tes unit. Tes integrasi melibatkan basis data nyata, panggilan HTTP nyata, pengiriman email nyata, dll. Mereka memiliki tempatnya, tetapi lambat dan bisa tidak stabil, artinya terkadang gagal tanpa alasan yang jelas. 
4. **Gunakan Nama yang Deskriptif**: Nama tes harus dengan jelas menggambarkan perilaku yang diuji. Ini meningkatkan keterbacaan dan pemeliharaan.
5. **Hindari Globals Seperti Wabah**: Minimalisir penggunaan `$app->set()` dan `$app->get()`, karena bertindak seperti status global, yang memerlukan mocking di setiap tes. Lebih disukai gunakan DI atau kontainer DI (lihat [Dependency Injection Container](/learn/dependency-injection-container)). Bahkan menggunakan metode `$app->map()` secara teknis adalah "global" dan harus dihindari demi DI. Gunakan pustaka sesi seperti [flightphp/session](https://github.com/flightphp/session) sehingga Anda bisa mocking objek sesi di tes Anda. **Jangan** memanggil [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php) langsung di kode Anda karena itu menyuntikkan variabel global ke kode Anda, membuatnya sulit diuji.
6. **Gunakan Dependency Injection**: Suntikkan dependensi (misalnya, [`PDO`](https://www.php.net/manual/en/class.pdo.php), mailer) ke controller untuk mengisolasi logika dan menyederhanakan mocking. Jika Anda memiliki kelas dengan terlalu banyak dependensi, pertimbangkan untuk merefactornya menjadi kelas yang lebih kecil yang masing-masing memiliki satu tanggung jawab sesuai [prinsip SOLID](https://en.wikipedia.org/wiki/SOLID).
7. **Mock Layanan Pihak Ketiga**: Mock basis data, klien HTTP (cURL), atau layanan email untuk menghindari panggilan eksternal. Uji satu atau dua lapis dalam, tetapi biarkan logika inti berjalan. Misalnya, jika aplikasi Anda mengirim pesan teks, Anda **TIDAK** ingin benar-benar mengirim pesan teks setiap kali menjalankan tes karena biaya akan bertambah (dan akan lebih lambat). Sebaliknya, mock layanan pesan teks dan hanya verifikasi bahwa kode Anda memanggil layanan pesan teks dengan parameter yang benar.
8. **Tujuannya Adalah Cakupan Tinggi, Bukan Kesempurnaan**: Cakupan baris 100% bagus, tetapi itu tidak berarti segala sesuatu dalam kode Anda diuji dengan benar (silakan teliti [branch/path coverage di PHPUnit](https://localheinz.com/articles/2023/03/22/collecting-line-branch-and-path-coverage-with-phpunit/)). Prioritaskan perilaku kritis (misalnya, pendaftaran pengguna, respons API, dan menangkap respons gagal).
9. **Gunakan Controller untuk Rute**: Di definisi rute Anda, gunakan controller bukan closures. Objek `flight\Engine $app` disuntikkan ke setiap controller melalui konstruktor secara default. Di tes, gunakan `$app = new Flight\Engine()` untuk menginisialisasi Flight dalam tes, suntikkan ke controller Anda, dan panggil metode secara langsung (misalnya, `$controller->register()`). Lihat [Extending Flight](/learn/extending) dan [Routing](/learn/routing).
10. **Pilih Gaya Mocking dan Patuhi Itu**: PHPUnit mendukung beberapa gaya mocking (misalnya, prophecy, mocking bawaan), atau Anda bisa menggunakan kelas anonim yang memiliki manfaatnya sendiri seperti penyelesaian kode, pecah jika Anda mengubah definisi metode, dll. Hanya saja, konsisten di seluruh tes Anda. Lihat [PHPUnit Mock Objects](https://docs.phpunit.de/en/12.3/test-doubles.html#test-doubles).
11. **Gunakan visibilitas `protected` untuk metode/properti yang ingin Anda uji di subkelas**: Ini memungkinkan Anda menimpa mereka di subkelas tes tanpa membuatnya public, ini sangat berguna untuk mocking kelas anonim.

## Menyiapkan PHPUnit

Pertama, siapkan [PHPUnit](https://phpunit.de/) di proyek Flight PHP Anda menggunakan Composer untuk pengujian yang mudah. Lihat [Panduan Memulai PHPUnit](https://phpunit.readthedocs.io/en/12.3/installation.html) untuk detail lebih lanjut.

1. Di direktori proyek Anda, jalankan:
   ```bash
   composer require --dev phpunit/phpunit
   ```
   Ini menginstal PHPUnit terbaru sebagai dependensi pengembangan.

2. Buat direktori `tests` di root proyek untuk file tes.

3. Tambahkan skrip tes ke `composer.json` untuk kemudahan:
   ```json
   // other composer.json content
   "scripts": {
       "test": "phpunit --configuration phpunit.xml"
   }
   ```

4. Buat file `phpunit.xml` di root:
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

Sekarang saat tes Anda dibuat, Anda bisa menjalankan `composer test` untuk mengeksekusi tes.

## Menguji Penangan Rute Sederhana

Mari mulai dengan rute [sederhana](/learn/routing) yang memvalidasi input email pengguna. Kami akan menguji perilakunya: mengembalikan pesan sukses untuk email yang valid dan kesalahan untuk yang tidak valid. Untuk validasi email, kami menggunakan [`filter_var`](https://www.php.net/manual/en/function.filter-var.php).

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
		$responseArray = [];
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$responseArray = ['status' => 'error', 'message' => 'Invalid email'];  // Pesan kesalahan untuk email tidak valid
		} else {
			$responseArray = ['status' => 'success', 'message' => 'Valid email'];  // Pesan sukses untuk email valid
		}

		$this->app->json($responseArray);
	}
}
```

Untuk menguji ini, buat file tes. Lihat [Pengujian Unit dan Prinsip SOLID](/learn/unit-testing-and-solid-principles) untuk lebih lanjut tentang struktur tes:

```php
// tests/UserControllerTest.php
use PHPUnit\Framework\TestCase;
use Flight;
use flight\Engine;

class UserControllerTest extends TestCase {

    public function testValidEmailReturnsSuccess() {  // Menguji jika email valid mengembalikan sukses
		$app = new Engine();
		$request = $app->request();
		$request->data->email = 'test@example.com';  // Mensimulasikan data POST
		$UserController = new UserController($app);
		$UserController->register($request->data->email);
        $response = $app->response()->getBody();
		$output = json_decode($response, true);
        $this->assertEquals('success', $output['status']);
        $this->assertEquals('Valid email', $output['message']);
    }

    public function testInvalidEmailReturnsError() {  // Menguji jika email tidak valid mengembalikan kesalahan
		$app = new Engine();
		$request = $app->request();
		$request->data->email = 'invalid-email';  // Mensimulasikan data POST
		$UserController = new UserController($app);
		$UserController->register($request->data->email);
		$response = $app->response()->getBody();
		$output = json_decode($response, true);
		$this->assertEquals('error', $output['status']);
		$this->assertEquals('Invalid email', $output['message']);
	}
}
```

**Poin Kunci**:
- Kami mensimulasikan data POST menggunakan kelas request. Jangan gunakan globals seperti `$_POST`, `$_GET`, dll karena membuat pengujian lebih rumit (Anda harus selalu mereset nilai-nilai tersebut atau tes lain mungkin gagal).
- Semua controller secara default akan memiliki instance `flight\Engine` disuntikkan ke dalamnya bahkan tanpa kontainer DIC yang disiapkan. Ini membuat pengujian controller langsung lebih mudah.
- Tidak ada penggunaan `Flight::` sama sekali, membuat kode lebih mudah diuji.
- Tes memverifikasi perilaku: status dan pesan yang benar untuk email valid/tidak valid.

Jalankan `composer test` untuk memverifikasi rute berperilaku seperti yang diharapkan. Untuk lebih lanjut tentang [requests](/learn/requests) dan [responses](/learn/responses) di Flight, lihat dokumen yang relevan.

## Menggunakan Dependency Injection untuk Controller yang Dapat Diuji

Untuk skenario yang lebih kompleks, gunakan [dependency injection](/learn/dependency-injection-container) (DI) untuk membuat controller dapat diuji. Hindari globals Flight (misalnya, `Flight::set()`, `Flight::map()`, `Flight::register()`) karena bertindak seperti status global, yang memerlukan mocking untuk setiap tes. Sebaliknya, gunakan kontainer DI Flight, [DICE](https://github.com/Level-2/Dice), [PHP-DI](https://php-di.org/) atau DI manual.

Mari gunakan [`flight\database\PdoWrapper`](/awesome-plugins/pdo-wrapper) daripada PDO mentah. Wrapper ini jauh lebih mudah dimock dan diuji unit!

Berikut adalah controller yang menyimpan pengguna ke basis data dan mengirim email selamat datang:

```php
use flight\database\PdoWrapper;

class UserController {
    protected $app;
    protected $db;
    protected $mailer;

    public function __construct(Engine $app, PdoWrapper $db, MailerInterface $mailer) {
        $this->app = $app;
        $this->db = $db;
        $this->mailer = $mailer;
    }

    public function register() {
		$email = $this->app->request()->data->email;
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			// Menambahkan return di sini membantu pengujian unit untuk menghentikan eksekusi
			return $this->app->jsonHalt(['status' => 'error', 'message' => 'Invalid email']);
		}

		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);

		return $this->app->json(['status' => 'success', 'message' => 'User registered']);
    }
}
```

**Poin Kunci**:
- Controller bergantung pada instance [`PdoWrapper`](/awesome-plugins/pdo-wrapper) dan `MailerInterface` (layanan email pihak ketiga pura-pura).
- Dependensi disuntikkan melalui konstruktor, menghindari globals.

### Menguji Controller dengan Mock

Sekarang, mari uji perilaku `UserController`: memvalidasi email, menyimpan ke basis data, dan mengirim email. Kami akan mock basis data dan mailer untuk mengisolasi controller.

```php
// tests/UserControllerDICTest.php
use PHPUnit\Framework\TestCase;

class UserControllerDICTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {  // Menguji jika email valid menyimpan dan mengirim email

		// Kadang mencampur gaya mocking diperlukan
		// Di sini kami menggunakan mock bawaan PHPUnit untuk PDOStatement
		$statementMock = $this->createMock(PDOStatement::class);
		$statementMock->method('execute')->willReturn(true);
		// Menggunakan kelas anonim untuk mock PdoWrapper
        $mockDb = new class($statementMock) extends PdoWrapper {
			protected $statementMock;
			public function __construct($statementMock) {
				$this->statementMock = $statementMock;
			}

			// Saat kami mock, kami tidak benar-benar melakukan panggilan basis data.
			// Kami bisa mengatur lebih lanjut untuk mensimulasikan kegagalan, dll.
            public function runQuery(string $sql, array $params = []): PDOStatement {
                return $this->statementMock;
            }
        };
        $mockMailer = new class implements MailerInterface {
            public $sentEmail = null;
            public function sendWelcome($email): bool {
                $this->sentEmail = $email;
                return true;	
            }
        };
		$app = new Engine();
		$app->request()->data->email = 'test@example.com';
        $controller = new UserControllerDIC($app, $mockDb, $mockMailer);
        $controller->register();
		$response = $app->response()->getBody();
		$result = json_decode($response, true);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('User registered', $result['message']);
        $this->assertEquals('test@example.com', $mockMailer->sentEmail);
    }

    public function testInvalidEmailSkipsSaveAndEmail() {  // Menguji jika email tidak valid melewati penyimpanan dan pengiriman email
		 $mockDb = new class() extends PdoWrapper {
			// Konstruktor kosong melewati konstruktor induk
			public function __construct() {}
            public function runQuery(string $sql, array $params = []): PDOStatement {
                throw new Exception('Should not be called');  // Tidak seharusnya dipanggil
            }
        };
        $mockMailer = new class implements MailerInterface {
            public $sentEmail = null;
            public function sendWelcome($email): bool {
                throw new Exception('Should not be called');  // Tidak seharusnya dipanggil
            }
        };
		$app = new Engine();
		$app->request()->data->email = 'invalid-email';

		// Perlu memetakan jsonHalt untuk menghindari keluar
		$app->map('jsonHalt', function($data) use ($app) {
			$app->json($data, 400);
		});
        $controller = new UserControllerDIC($app, $mockDb, $mockMailer);
        $controller->register();
        $response = $app->response()->getBody();
        $result = json_decode($response, true);
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Invalid email', $result['message']);
    }
}
```

**Poin Kunci**:
- Kami mock `PdoWrapper` dan `MailerInterface` untuk menghindari panggilan basis data atau email nyata.
- Tes memverifikasi perilaku: email valid memicu penyisipan basis data dan pengiriman email; email tidak valid melewati keduanya.
- Mock dependensi pihak ketiga (misalnya, `PdoWrapper`, `MailerInterface`), membiarkan logika controller berjalan.

### Mocking Terlalu Banyak

Hati-hati jangan mock terlalu banyak dari kode Anda. Biarkan saya beri contoh mengapa ini mungkin buruk menggunakan `UserController`. Kami akan ubah pemeriksaan menjadi metode bernama `isEmailValid` (menggunakan `filter_var`) dan tambahan baru menjadi metode terpisah bernama `registerUser`.

```php
use flight\database\PdoWrapper;
use flight\Engine;

// UserControllerDICV2.php
class UserControllerDICV2 {
	protected $app;
    protected $db;
    protected $mailer;

    public function __construct(Engine $app, PdoWrapper $db, MailerInterface $mailer) {
        $this->app = $app;
        $this->db = $db;
        $this->mailer = $mailer;
    }

    public function register() {
		$email = $this->app->request()->data->email;
		if (!$this->isEmailValid($email)) {
			// Menambahkan return di sini membantu pengujian unit untuk menghentikan eksekusi
			return $this->app->jsonHalt(['status' => 'error', 'message' => 'Invalid email']);
		}

		$this->registerUser($email);

		$this->app->json(['status' => 'success', 'message' => 'User registered']);
    }

	protected function isEmailValid($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
	}

	protected function registerUser($email) {
		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);
	}
}
```

Dan sekarang tes unit yang terlalu dimock yang tidak menguji apa-apa:

```php
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {  // Menguji jika email valid menyimpan dan mengirim email
		$app = new Engine();
		$app->request()->data->email = 'test@example.com';
		// Kami melewatkan injeksi dependensi ekstra karena "mudah"
        $controller = new class($app) extends UserControllerDICV2 {
			protected $app;
			// Melewati dependensi di konstruktor
			public function __construct($app) {
				$this->app = $app;
			}

			// Kami hanya memaksa ini menjadi valid.
			protected function isEmailValid($email) {
				return true;  // Selalu kembalikan true, melewati validasi sebenarnya
			}

			// Melewati panggilan DB dan mailer sebenarnya
			protected function registerUser($email) {
				return false;
			}
		};
        $controller->register();
		$response = $app->response()->getBody();
		$result = json_decode($response, true);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('User registered', $result['message']);
    }
}
```

Hore, kami memiliki tes unit dan mereka lulus! Tapi tunggu, bagaimana jika saya benar-benar mengubah kerja internal dari `isEmailValid` atau `registerUser`? Tes saya masih akan lulus karena saya telah mock semua fungsionalitas. Biarkan saya tunjukkan apa yang saya maksud.

```php
// UserControllerDICV2.php
class UserControllerDICV2 {

	// ... metode lainnya ...

	protected function isEmailValid($email) {
		// Logika yang diubah
		$validEmail = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
		// Sekarang harus hanya memiliki domain tertentu
		$validDomain = strpos($email, '@example.com') !== false; 
		return $validEmail && $validDomain;
	}
}
```

Jika saya menjalankan tes unit di atas, mereka masih lulus! Tapi karena saya tidak menguji perilaku (membiarkan sebagian kode berjalan), saya berpotensi membuat bug yang menunggu di produksi. Tes harus dimodifikasi untuk memperhitungkan perilaku baru, dan juga kebalikan dari apa yang kami harapkan.

## Contoh Lengkap

Anda bisa menemukan contoh lengkap proyek Flight PHP dengan tes unit di GitHub: [n0nag0n/flight-unit-tests-guide](https://github.com/n0nag0n/flight-unit-tests-guide).
Untuk panduan lebih lanjut, lihat [Pengujian Unit dan Prinsip SOLID](/learn/unit-testing-and-solid-principles) dan [Penyelesaian Masalah](/learn/troubleshooting).

## Kesalahan Umum

- **Over-Mocking**: Jangan mock setiap dependensi; biarkan beberapa logika (misalnya, validasi controller) berjalan untuk menguji perilaku sebenarnya. Lihat [Pengujian Unit dan Prinsip SOLID](/learn/unit-testing-and-solid-principles).
- **Global State**: Menggunakan variabel PHP global (misalnya, [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php), [`$_COOKIE`](https://www.php.net/manual/en/reserved.variables.cookie.php)) secara berat membuat tes rapuh. Hal yang sama berlaku untuk `Flight::`. Refactor untuk melewatkan dependensi secara eksplisit.
- **Pengaturan Kompleks**: Jika pengaturan tes rumit, kelas Anda mungkin memiliki terlalu banyak dependensi atau tanggung jawab yang melanggar prinsip [SOLID](https://en.wikipedia.org/wiki/SOLID).

## Menskala dengan Tes Unit

Tes unit bersinar di proyek yang lebih besar atau saat mengunjungi kode setelah berbulan-bulan. Mereka mendokumentasikan perilaku dan menangkap regresi, menyelamatkan Anda dari belajar ulang aplikasi Anda. Bagi pengembang solo, uji jalur kritis (misalnya, pendaftaran pengguna, pemrosesan pembayaran). Bagi tim, tes memastikan perilaku konsisten di seluruh kontribusi. Lihat [Mengapa Framework?](/learn/why-frameworks) untuk lebih lanjut tentang manfaat menggunakan framework dan tes.

Sumbangkan tips pengujian Anda sendiri ke repositori dokumentasi Flight PHP!

_Ditulis oleh [n0nag0n](https://github.com/n0nag0n) 2025_