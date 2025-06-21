# FlightPHP Sesi - Penanganan Sesi Berbasis File Ringan

Ini adalah plugin penanganan sesi berbasis file yang ringan untuk [Flight PHP Framework](https://docs.flightphp.com/). Ini menyediakan solusi sederhana namun kuat untuk mengelola sesi, dengan fitur seperti pembacaan sesi non-blocking, enkripsi opsional, fungsi auto-commit, dan mode uji untuk pengembangan. Data sesi disimpan dalam file, menjadikannya ideal untuk aplikasi yang tidak memerlukan basis data.

Jika Anda ingin menggunakan basis data, periksa plugin [ghostff/session](/awesome-plugins/ghost-session) yang memiliki banyak fitur serupa tetapi dengan backend basis data.

Kunjungi [repositori Github](https://github.com/flightphp/session) untuk kode sumber lengkap dan detail.

## Instalasi

Instal plugin melalui Composer:

```bash
composer require flightphp/session
```

## Penggunaan Dasar

Berikut adalah contoh sederhana cara menggunakan plugin `flightphp/session` dalam aplikasi Flight Anda:

```php
require 'vendor/autoload.php';

use flight\Session;

$app = Flight::app();

// Daftarkan layanan sesi
$app->register('session', Session::class);

// Contoh rute dengan penggunaan sesi
Flight::route('/login', function() {
    $session = Flight::session();
    $session->set('user_id', 123);
    $session->set('username', 'johndoe');
    $session->set('is_admin', false);

    echo $session->get('username'); // Keluaran: johndoe
    echo $session->get('preferences', 'default_theme'); // Keluaran: default_theme

    if ($session->get('user_id')) {
        Flight::json(['message' => 'Pengguna telah masuk!', 'user_id' => $session->get('user_id')]);
    }
});

Flight::route('/logout', function() {
    $session = Flight::session();
    $session->clear(); // Hapus semua data sesi
    Flight::json(['message' => 'Berhasil keluar']);
});

Flight::start();
```

### Poin Kunci
- **Non-Blocking**: Menggunakan `read_and_close` secara default untuk memulai sesi, mencegah masalah penguncian sesi.
- **Auto-Commit**: Diaktifkan secara default, sehingga perubahan disimpan secara otomatis saat shutdown kecuali dinonaktifkan.
- **File Storage**: Sesi disimpan di direktori temp sistem di bawah `/flight_sessions` secara default.

## Konfigurasi

Anda dapat menyesuaikan penanganan sesi dengan meneruskan array opsi saat mendaftarkan:

```php
// Ya, ini array ganda :)
$app->register('session', Session::class, [ [
    'save_path' => '/custom/path/to/sessions',         // Direktori untuk file sesi
	'prefix' => 'myapp_',                              // Awalan untuk file sesi
    'encryption_key' => 'a-secure-32-byte-key-here',   // Aktifkan enkripsi (32 byte direkomendasikan untuk AES-256-CBC)
    'auto_commit' => false,                            // Nonaktifkan auto-commit untuk kontrol manual
    'start_session' => true,                           // Mulai sesi secara otomatis (default: true)
    'test_mode' => false,                              // Aktifkan mode uji untuk pengembangan
    'serialization' => 'json',                         // Metode serialisasi: 'json' (default) atau 'php' (legacy)
] ]);
```

### Opsi Konfigurasi
| Option            | Description                                      | Default Value                     |
|-------------------|--------------------------------------------------|-----------------------------------|
| `save_path`       | Direktori tempat file sesi disimpan              | `sys_get_temp_dir() . '/flight_sessions'` |
| `prefix`          | Awalan untuk file sesi yang disimpan             | `sess_`                           |
| `encryption_key`  | Kunci untuk enkripsi AES-256-CBC (opsional)      | `null` (tanpa enkripsi)           |
| `auto_commit`     | Auto-simpan data sesi saat shutdown              | `true`                            |
| `start_session`   | Mulai sesi secara otomatis                       | `true`                            |
| `test_mode`       | Jalankan dalam mode uji tanpa memengaruhi sesi PHP | `false`                           |
| `test_session_id` | ID sesi khusus untuk mode uji (opsional)         | Dibuat acak jika tidak disetel    |
| `serialization`   | Metode serialisasi: 'json' (default, aman) atau 'php' (legacy, mengizinkan objek) | `'json'` |

## Mode Serialisasi

Secara default, pustaka ini menggunakan **serialisasi JSON** untuk data sesi, yang aman dan mencegah kerentanan injeksi objek PHP. Jika Anda perlu menyimpan objek PHP dalam sesi (tidak direkomendasikan untuk sebagian besar aplikasi), Anda dapat memilih serialisasi PHP legacy:

- `'serialization' => 'json'` (default):
  - Hanya array dan primitif yang diizinkan dalam data sesi.
  - Lebih aman: kebal terhadap injeksi objek PHP.
  - File diawali dengan `J` (JSON biasa) atau `F` (JSON terenkripsi).
- `'serialization' => 'php'`:
  - Mengizinkan penyimpanan objek PHP (gunakan dengan hati-hati).
  - File diawali dengan `P` (serialisasi PHP biasa) atau `E` (serialisasi PHP terenkripsi).

**Catatan:** Jika Anda menggunakan serialisasi JSON, upaya untuk menyimpan objek akan melemparkan pengecualian.

## Penggunaan Lanjutan

### Commit Manual
Jika Anda menonaktifkan auto-commit, Anda harus secara manual melakukan commit perubahan:

```php
$app->register('session', Session::class, ['auto_commit' => false]);

Flight::route('/update', function() {
    $session = Flight::session();
    $session->set('key', 'value');
    $session->commit(); // Simpan perubahan secara eksplisit
});
```

### Keamanan Sesi dengan Enkripsi
Aktifkan enkripsi untuk data sensitif:

```php
$app->register('session', Session::class, [
    'encryption_key' => 'your-32-byte-secret-key-here'
]);

Flight::route('/secure', function() {
    $session = Flight::session();
    $session->set('credit_card', '4111-1111-1111-1111'); // Dienkripsi secara otomatis
    echo $session->get('credit_card'); // Didekripsi saat pengambilan
});
```

### Regenerasi Sesi
Regenerasikan ID sesi untuk keamanan (misalnya, setelah masuk):

```php
Flight::route('/post-login', function() {
    $session = Flight::session();
    $session->regenerate(); // ID baru, simpan data
    // ATAU
    $session->regenerate(true); // ID baru, hapus data lama
});
```

### Contoh Middleware
Lindungi rute dengan otentikasi berbasis sesi:

```php
Flight::route('/admin', function() {
    Flight::json(['message' => 'Selamat datang di panel admin']);
})->addMiddleware(function() {
    $session = Flight::session();
    if (!$session->get('is_admin')) {
        Flight::halt(403, 'Akses ditolak');
    }
});
```

Ini hanya contoh sederhana cara menggunakannya dalam middleware. Untuk contoh yang lebih mendalam, lihat dokumentasi [middleware](/learn/middleware).

## Metode

Kelas `Session` menyediakan metode-metode berikut:

- `set(string $key, $value)`: Menyimpan nilai dalam sesi.
- `get(string $key, $default = null)`: Mengambil nilai, dengan default opsional jika kunci tidak ada.
- `delete(string $key)`: Menghapus kunci tertentu dari sesi.
- `clear()`: Menghapus semua data sesi, tetapi mempertahankan nama file sesi yang sama.
- `commit()`: Menyimpan data sesi saat ini ke sistem file.
- `id()`: Mengembalikan ID sesi saat ini.
- `regenerate(bool $deleteOldFile = false)`: Meregenerasikan ID sesi termasuk membuat file sesi baru, mempertahankan semua data lama dan file lama tetap ada. Jika `$deleteOldFile` adalah `true`, file sesi lama dihapus.
- `destroy(string $id)`: Menghancurkan sesi berdasarkan ID dan menghapus file sesi dari sistem. Ini bagian dari `SessionHandlerInterface` dan `$id` diperlukan. Penggunaan khas adalah `$session->destroy($session->id())`.
- `getAll()` : Mengembalikan semua data dari sesi saat ini.

Semua metode kecuali `get()` dan `id()` mengembalikan instance `Session` untuk chaining.

## Mengapa Menggunakan Plugin Ini?

- **Ringan**: Tidak ada ketergantungan eksternal—hanya file.
- **Non-Blocking**: Menghindari penguncian sesi dengan `read_and_close` secara default.
- **Aman**: Mendukung enkripsi AES-256-CBC untuk data sensitif.
- **Fleksibel**: Opsi auto-commit, mode uji, dan kontrol manual.
- **Flight-Native**: Dibuat khusus untuk kerangka Flight.

## Detail Teknis

- **Format Penyimpanan**: File sesi diawali dengan `sess_` dan disimpan di `save_path` yang dikonfigurasi. Awalan konten file:
  - `J`: JSON biasa (default, tanpa enkripsi)
  - `F`: JSON terenkripsi (default dengan enkripsi)
  - `P`: Serialisasi PHP biasa (legacy, tanpa enkripsi)
  - `E`: Serialisasi PHP terenkripsi (legacy dengan enkripsi)
- **Enkripsi**: Menggunakan AES-256-CBC dengan IV acak per tulis sesi saat `encryption_key` disediakan. Enkripsi berfungsi untuk kedua mode serialisasi JSON dan PHP.
- **Serialisasi**: JSON adalah metode default dan paling aman. Serialisasi PHP tersedia untuk penggunaan legacy/tingkat lanjut, tetapi kurang aman.
- **Garbage Collection**: Mengimplementasikan `SessionHandlerInterface::gc()` untuk membersihkan sesi yang kedaluwarsa.

## Berkontribusi

Kontribusi diterima! Fork [repositori](https://github.com/flightphp/session), buat perubahan Anda, dan kirimkan pull request. Laporkan bug atau sarankan fitur melalui pelacak isu Github.

## Lisensi

Plugin ini dilisensikan di bawah Lisensi MIT. Lihat [repositori Github](https://github.com/flightphp/session) untuk detail.