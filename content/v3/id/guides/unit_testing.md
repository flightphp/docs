# Pengujian Unit di Flight PHP dengan PHPUnit

Panduan ini memperkenalkan pengujian unit di Flight PHP menggunakan [PHPUnit](https://phpunit.de/), ditujukan untuk pemula yang ingin memahami *mengapa* pengujian unit penting dan bagaimana menerapkannya secara praktis. Kami akan fokus pada pengujian *perilaku*—memastikan aplikasi Anda melakukan apa yang diharapkan, seperti mengirim email atau menyimpan catatan—bukan perhitungan sepele. Kami akan mulai dengan [route handler](/learn/routing) sederhana dan maju ke [controller](/learn/routing) yang lebih kompleks, menggabungkan [dependency injection](/learn/dependency-injection-container) (DI) dan mocking layanan pihak ketiga.

## Mengapa Pengujian Unit?

Pengujian unit memastikan kode Anda berperilaku seperti yang diharapkan, menangkap bug sebelum mencapai produksi. Ini sangat berharga di Flight, di mana routing ringan dan fleksibilitas dapat menyebabkan interaksi kompleks. Bagi pengembang solo atau tim, pengujian unit berfungsi sebagai jaring pengaman, mendokumentasikan perilaku yang diharapkan dan mencegah regresi saat Anda mengunjungi ulang kode nanti. Mereka juga meningkatkan desain: kode yang sulit diuji sering menandakan kelas yang terlalu kompleks atau terikat erat.

Tidak seperti contoh sederhana (misalnya, menguji `x * y = z`), kami akan fokus pada perilaku dunia nyata, seperti memvalidasi input, menyimpan data, atau memicu tindakan seperti email. Tujuan kami adalah membuat pengujian mudah diakses dan bermakna.

## Prinsip Panduan Umum

1. **Uji Perilaku, Bukan Implementasi**: Fokus pada hasil (misalnya, "email dikirim" atau "catatan disimpan") daripada detail internal. Ini membuat pengujian tahan terhadap refactoring.
2. **Berhenti menggunakan `Flight::`**: Metode statis Flight sangat nyaman, tapi membuat pengujian sulit. Anda harus terbiasa menggunakan variabel `$app` dari `$app = Flight::app();`. `$app` memiliki semua metode yang sama dengan `Flight::`. Anda masih bisa menggunakan `$app->route()` atau `$this->app->json()` di controller Anda dll. Anda juga harus menggunakan router Flight yang sebenarnya dengan `$router = $app->router()` dan kemudian Anda bisa menggunakan `$router->get()`, `$router->post()`, `$router->group()` dll. Lihat [Routing](/learn/routing).
3. **Jaga Pengujian Cepat**: Pengujian cepat mendorong eksekusi yang sering. Hindari operasi lambat seperti panggilan database di pengujian unit. Jika Anda memiliki pengujian lambat, itu tanda Anda sedang menulis pengujian integrasi, bukan pengujian unit. Pengujian integrasi adalah ketika Anda benar-benar melibatkan database nyata, panggilan HTTP nyata, pengiriman email nyata dll. Mereka memiliki tempatnya, tapi mereka lambat dan bisa tidak stabil, artinya kadang gagal karena alasan yang tidak diketahui. 
4. **Gunakan Nama Deskriptif**: Nama pengujian harus dengan jelas menggambarkan perilaku yang diuji. Ini meningkatkan keterbacaan dan pemeliharaan.
5. **Hindari Globals Seperti Wabah**: Minimalkan penggunaan `$app->set()` dan `$app->get()`, karena mereka bertindak seperti state global, memerlukan mock di setiap pengujian. Lebih suka DI atau container DI (lihat [Dependency Injection Container](/learn/dependency-injection-container)). Bahkan menggunakan metode `$app->map()` secara teknis adalah "global" dan harus dihindari demi DI. Gunakan pustaka sesi seperti [flightphp/session](https://github.com/flightphp/session) sehingga Anda bisa mock objek sesi di pengujian Anda. **Jangan** panggil [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php) secara langsung di kode Anda karena itu menyuntikkan variabel global ke kode Anda, membuatnya sulit diuji.
6. **Gunakan Dependency Injection**: Suntik dependensi (misalnya, [`PDO`](https://www.php.net/manual/en/class.pdo.php), mailer) ke controller untuk mengisolasi logika dan menyederhanakan mocking. Jika Anda memiliki kelas dengan terlalu banyak dependensi, pertimbangkan untuk merestrukturisasinya menjadi kelas yang lebih kecil yang masing-masing memiliki tanggung jawab tunggal mengikuti prinsip [SOLID](https://en.wikipedia.org/wiki/SOLID).
7. **Mock Layanan Pihak Ketiga**: Mock database, klien HTTP (cURL), atau layanan email untuk menghindari panggilan eksternal. Uji satu atau dua lapis dalam, tapi biarkan logika inti berjalan. Misalnya, jika aplikasi Anda mengirim pesan teks, Anda **TIDAK** ingin benar-benar mengirim pesan teks setiap kali menjalankan pengujian karena biaya itu akan bertambah (dan akan lebih lambat). Sebaliknya, mock layanan pesan teks dan hanya verifikasi bahwa kode Anda memanggil layanan pesan teks dengan parameter yang benar.
8. **Bidik Cakupan Tinggi, Bukan Kesempurnaan**: Cakupan baris 100% bagus, tapi itu tidak benar-benar berarti bahwa semuanya di kode Anda diuji seperti yang seharusnya (silakan teliti [branch/path coverage di PHPUnit](https://localheinz.com/articles/2023/03/22/collecting-line-branch-and-path-coverage-with-phpunit/)). Prioritaskan perilaku kritis (misalnya, pendaftaran pengguna, respons API dan menangkap respons gagal).
9. **Gunakan Controller untuk Route**: Di definisi route Anda, gunakan controller bukan closures. `flight\Engine $app` disuntikkan ke setiap controller melalui constructor secara default. Di pengujian, gunakan `$app = new Flight\Engine()` untuk menginisialisasi Flight dalam pengujian, suntikkan ke controller Anda, dan panggil metode secara langsung (misalnya, `$controller->register()`). Lihat [Extending Flight](/learn/extending) dan [Routing](/learn/routing).
10. **Pilih gaya mocking dan patuhi itu**: PHPUnit mendukung beberapa gaya mocking (misalnya, prophecy, mock built-in), atau Anda bisa menggunakan kelas anonim yang memiliki manfaat sendiri seperti code completion, rusak jika Anda mengubah definisi metode, dll. Hanya konsisten di seluruh pengujian Anda. Lihat [PHPUnit Mock Objects](https://docs.phpunit.de/en/12.3/test-doubles.html#test-doubles).
11. **Gunakan visibilitas `protected` untuk metode/properti yang ingin Anda uji di subclass**: Ini memungkinkan Anda untuk menimpa mereka di subclass pengujian tanpa membuatnya public, ini sangat berguna untuk mock kelas anonim.

## Menyiapkan PHPUnit

Pertama, siapkan [PHPUnit](https://phpunit.de/) di proyek Flight PHP Anda menggunakan Composer untuk pengujian yang mudah. Lihat panduan [PHPUnit Getting Started](https://phpunit.readthedocs.io/en/12.3/installation.html) untuk detail lebih lanjut.

1. Di direktori proyek Anda, jalankan:
   ```bash
   composer require --dev phpunit/phpunit
   ```
   Ini menginstal PHPUnit terbaru sebagai dependensi pengembangan.

2. Buat direktori `tests` di root proyek Anda untuk file pengujian.

3. Tambahkan skrip pengujian ke `composer.json` untuk kenyamanan:
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

Sekarang ketika pengujian Anda dibangun, Anda bisa menjalankan `composer test` untuk mengeksekusi pengujian.

## Menguji Route Handler Sederhana

Mari mulai dengan [route](/learn/routing) dasar yang memvalidasi input email pengguna. Kami akan menguji perilakunya: mengembalikan pesan sukses untuk email valid dan kesalahan untuk yang tidak valid. Untuk validasi email, kami menggunakan [`filter_var`](https://www.php.net/manual/en/function.filter-var.php).

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
			$responseArray = ['status' => 'error', 'message' => 'Invalid email'];
		} else {
			$responseArray = ['status' => 'success', 'message' => 'Valid email'];
		}

		$this->app->json($responseArray);
	}
}
```

Untuk menguji ini, buat file pengujian. Lihat [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles) untuk lebih lanjut tentang struktur pengujian:

```php
// tests/UserControllerTest.php
use PHPUnit\Framework\TestCase;
use Flight;
use flight\Engine;

class UserControllerTest extends TestCase {

    public function testValidEmailReturnsSuccess() {
		$app = new Engine();
		$request = $app->request();
		$request->data->email = 'test@example.com'; // Simulate POST data
		$UserController = new UserController($app);
		$UserController->register($request->data->email);
        $response = $app->response()->getBody();
		$output = json_decode($response, true);
        $this->assertEquals('success', $output['status']);
        $this->assertEquals('Valid email', $output['message']);
    }

    public function testInvalidEmailReturnsError() {
		$app = new Engine();
		$request = $app->request();
		$request->data->email = 'invalid-email'; // Simulate POST data
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
- Kami mensimulasikan data POST menggunakan kelas request. Jangan gunakan globals seperti `$_POST`, `$_GET`, dll karena membuat pengujian lebih rumit (Anda harus selalu mereset nilai-nilai itu atau pengujian lain mungkin meledak).
- Semua controller secara default akan memiliki instance `flight\Engine` yang disuntikkan ke dalamnya bahkan tanpa container DIC yang disiapkan. Ini membuat lebih mudah untuk menguji controller secara langsung.
- Tidak ada penggunaan `Flight::` sama sekali, membuat kode lebih mudah diuji.
- Pengujian memverifikasi perilaku: status dan pesan yang benar untuk email valid/tidak valid.

Jalankan `composer test` untuk memverifikasi route berperilaku seperti yang diharapkan. Untuk lebih lanjut tentang [requests](/learn/requests) dan [responses](/learn/responses) di Flight, lihat dokumen terkait.

## Menggunakan Dependency Injection untuk Controller yang Dapat Diuji

Untuk skenario yang lebih kompleks, gunakan [dependency injection](/learn/dependency-injection-container) (DI) untuk membuat controller dapat diuji. Hindari globals Flight (misalnya, `Flight::set()`, `Flight::map()`, `Flight::register()`) karena mereka bertindak seperti state global, memerlukan mock untuk setiap pengujian. Sebaliknya, gunakan container DI Flight, [DICE](https://github.com/Level-2/Dice), [PHP-DI](https://php-di.org/) atau DI manual.

Mari gunakan [`flight\database\PdoWrapper`](/learn/pdo-wrapper) daripada PDO mentah. Wrapper ini jauh lebih mudah untuk dimock dan diuji unit!

Berikut adalah controller yang menyimpan pengguna ke database dan mengirim email selamat datang:

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
			// adding the return here helps unit testing to stop execution
			return $this->app->jsonHalt(['status' => 'error', 'message' => 'Invalid email']);
		}

		$this->db->runQuery('INSERT INTO users (email) VALUES (?)', [$email]);
		$this->mailer->sendWelcome($email);

		return $this->app->json(['status' => 'success', 'message' => 'User registered']);
    }
}
```

**Poin Kunci**:
- Controller bergantung pada instance [`PdoWrapper`](/learn/pdo-wrapper) dan `MailerInterface` (layanan email pihak ketiga pura-pura).
- Dependensi disuntikkan melalui constructor, menghindari globals.

### Menguji Controller dengan Mocks

Sekarang, mari uji perilaku `UserController`: memvalidasi email, menyimpan ke database, dan mengirim email. Kami akan mock database dan mailer untuk mengisolasi controller.

```php
// tests/UserControllerDICTest.php
use PHPUnit\Framework\TestCase;

class UserControllerDICTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {

		// Sometimes mixing mocking styles is necessary
		// Here we use PHPUnit's built-in mock for PDOStatement
		$statementMock = $this->createMock(PDOStatement::class);
		$statementMock->method('execute')->willReturn(true);
		// Using an anonymous class to mock PdoWrapper
        $mockDb = new class($statementMock) extends PdoWrapper {
			protected $statementMock;
			public function __construct($statementMock) {
				$this->statementMock = $statementMock;
			}

			// When we mock it this way, we are not really making a database call.
			// We can further setup this to alter the PDOStatement mock to simulate failures, etc.
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

    public function testInvalidEmailSkipsSaveAndEmail() {
		 $mockDb = new class() extends PdoWrapper {
			// An empty constructor bypasses the parent constructor
			public function __construct() {}
            public function runQuery(string $sql, array $params = []): PDOStatement {
                throw new Exception('Should not be called');
            }
        };
        $mockMailer = new class implements MailerInterface {
            public $sentEmail = null;
            public function sendWelcome($email): bool {
                throw new Exception('Should not be called');
            }
        };
		$app = new Engine();
		$app->request()->data->email = 'invalid-email';

		// Need to map jsonHalt to avoid exiting
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
- Kami mock `PdoWrapper` dan `MailerInterface` untuk menghindari panggilan database atau email nyata.
- Pengujian memverifikasi perilaku: email valid memicu sisipan database dan pengiriman email; email tidak valid melewatkan keduanya.
- Mock dependensi pihak ketiga (misalnya, `PdoWrapper`, `MailerInterface`), membiarkan logika controller berjalan.

### Mocking Terlalu Banyak

Hati-hati jangan mock terlalu banyak kode Anda. Biarkan saya beri contoh di bawah tentang mengapa ini mungkin hal buruk menggunakan `UserController` kami. Kami akan ubah pemeriksaan itu menjadi metode yang disebut `isEmailValid` (menggunakan `filter_var`) dan penambahan baru lainnya menjadi metode terpisah yang disebut `registerUser`.

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
			// adding the return here helps unit testing to stop execution
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

Dan sekarang pengujian unit yang terlalu dimock yang sebenarnya tidak menguji apa-apa:

```php
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase {
    public function testValidEmailSavesAndSendsEmail() {
		$app = new Engine();
		$app->request()->data->email = 'test@example.com';
		// we are skipping the extra dependency injection here cause it's "easy"
        $controller = new class($app) extends UserControllerDICV2 {
			protected $app;
			// Bypass the deps in the construct
			public function __construct($app) {
				$this->app = $app;
			}

			// We'll just force this to be valid.
			protected function isEmailValid($email) {
				return true; // Always return true, bypassing real validation
			}

			// Bypass the actual DB and mailer calls
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

Hore kami memiliki pengujian unit dan mereka lulus! Tapi tunggu, bagaimana jika saya benar-benar mengubah cara kerja internal `isEmailValid` atau `registerUser`? Pengujian saya masih akan lulus karena saya telah mock semua fungsionalitas. Biarkan saya tunjukkan apa yang saya maksud.

```php
// UserControllerDICV2.php
class UserControllerDICV2 {

	// ... other methods ...

	protected function isEmailValid($email) {
		// Changed logic
		$validEmail = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
		// Now it should only have a specific domain
		$validDomain = strpos($email, '@example.com') !== false; 
		return $validEmail && $validDomain;
	}
}
```

Jika saya jalankan pengujian unit di atas, mereka masih lulus! Tapi karena saya tidak menguji perilaku (benar-benar membiarkan sebagian kode berjalan), saya berpotensi mengkode bug yang menunggu untuk terjadi di produksi. Pengujian harus dimodifikasi untuk memperhitungkan perilaku baru, dan juga kebalikan ketika perilaku bukan seperti yang kami harapkan.

## Contoh Lengkap

Anda bisa menemukan contoh lengkap proyek Flight PHP dengan pengujian unit di GitHub: [n0nag0n/flight-unit-tests-guide](https://github.com/n0nag0n/flight-unit-tests-guide).
Untuk pemahaman lebih dalam, lihat [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles).

## Kesalahan Umum

- **Over-Mocking**: Jangan mock setiap dependensi; biarkan beberapa logika (misalnya, validasi controller) berjalan untuk menguji perilaku nyata. Lihat [Unit Testing and SOLID Principles](/learn/unit-testing-and-solid-principles).
- **Global State**: Menggunakan variabel PHP global (misalnya, [`$_SESSION`](https://www.php.net/manual/en/reserved.variables.session.php), [`$_COOKIE`](https://www.php.net/manual/en/reserved.variables.cookie.php)) secara berat membuat pengujian rapuh. Hal yang sama berlaku dengan `Flight::`. Refactor untuk melewatkan dependensi secara eksplisit.
- **Setup Kompleks**: Jika setup pengujian merepotkan, kelas Anda mungkin memiliki terlalu banyak dependensi atau tanggung jawab yang melanggar prinsip [SOLID](/learn/unit-testing-and-solid-principles).

## Skala dengan Pengujian Unit

Pengujian unit bersinar di proyek yang lebih besar atau saat mengunjungi ulang kode setelah berbulan-bulan. Mereka mendokumentasikan perilaku dan menangkap regresi, menghemat Anda dari belajar ulang aplikasi Anda. Bagi pengembang solo, uji jalur kritis (misalnya, pendaftaran pengguna, pemrosesan pembayaran). Bagi tim, pengujian memastikan perilaku konsisten di seluruh kontribusi. Lihat [Why Frameworks?](/learn/why-frameworks) untuk lebih lanjut tentang manfaat menggunakan framework dan pengujian.

Sumbangkan tips pengujian Anda sendiri ke repositori dokumentasi Flight PHP!

_Ditulis oleh [n0nag0n](https://github.com/n0nag0n) 2025_