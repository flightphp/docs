# FlightPHP Sesi - Pengelola Sesi Berbasis File yang Ringan

Ini adalah plugin pengelola sesi berbasis file yang ringan untuk [Flight PHP Framework](https://docs.flightphp.com/). Ini memberikan solusi sederhana namun kuat untuk mengelola sesi, dengan fitur seperti pembacaan sesi non-blocking, enkripsi opsional, fungsionalitas auto-commit, dan mode uji untuk pengembangan. Data sesi disimpan dalam file, menjadikannya ideal untuk aplikasi yang tidak memerlukan basis data.

Jika Anda ingin menggunakan basis data, periksa plugin [ghostff/session](/awesome-plugins/ghost-session) dengan banyak fitur yang sama tetapi dengan backend basis data.

Kunjungi [repositori Github](https://github.com/flightphp/session) untuk kode sumber lengkap dan detailnya.

## Instalasi

Instal plugin melalui Composer:

```bash
composer require flightphp/session
```

## Penggunaan Dasar

Berikut adalah contoh sederhana tentang cara menggunakan plugin `flightphp/session` dalam aplikasi Flight Anda:

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

    echo $session->get('username'); // Menghasilkan: johndoe
    echo $session->get('preferences', 'default_theme'); // Menghasilkan: default_theme

    if ($session->get('user_id')) {
        Flight::json(['message' => 'Pengguna sudah login!', 'user_id' => $session->get('user_id')]);
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
- **Non-Blocking**: Menggunakan `read_and_close` untuk memulai sesi secara default, mencegah masalah penguncian sesi.
- **Auto-Commit**: Diaktifkan secara default, sehingga perubahan disimpan secara otomatis saat dimatikan kecuali dinonaktifkan.
- **Penyimpanan File**: Sesi disimpan di direktori temp sistem di bawah `/flight_sessions` secara default.

## Konfigurasi

Anda dapat menyesuaikan pengelola sesi dengan melewatkan array opsi saat mendaftar:

```php
$app->register('session', Session::class, [
    'save_path' => '/custom/path/to/sessions',         // Direktori untuk file sesi
    'encryption_key' => 'a-secure-32-byte-key-here',   // Aktifkan enkripsi (32 byte disarankan untuk AES-256-CBC)
    'auto_commit' => false,                            // Nonaktifkan auto-commit untuk kontrol manual
    'start_session' => true,                           // Mulai sesi secara otomatis (default: true)
    'test_mode' => false                               // Aktifkan mode uji untuk pengembangan
]);
```

### Opsi Konfigurasi
| Opsi              | Deskripsi                                      | Nilai Default                     |
|-------------------|--------------------------------------------------|-----------------------------------|
| `save_path`       | Direktori tempat file sesi disimpan              | `sys_get_temp_dir() . '/flight_sessions'` |
| `encryption_key`  | Kunci untuk enkripsi AES-256-CBC (opsional)     | `null` (tidak ada enkripsi)      |
| `auto_commit`     | Simpan data sesi secara otomatis saat dimatikan  | `true`                            |
| `start_session`   | Mulai sesi secara otomatis                       | `true`                            |
| `test_mode`       | Jalankan dalam mode uji tanpa mempengaruhi sesi PHP | `false`                       |
| `test_session_id` | ID sesi kustom untuk mode uji (opsional)        | Dihasilkan secara acak jika tidak disetel |

## Penggunaan Lanjut

### Manual Commit
Jika Anda menonaktifkan auto-commit, Anda harus melakukan commit perubahan secara manual:

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
    echo $session->get('credit_card'); // Didekripsi saat diambil
});
```

### Regenerasi Sesi
Regenerasi ID sesi untuk keamanan (misalnya, setelah login):

```php
Flight::route('/post-login', function() {
    $session = Flight::session();
    $session->regenerate(); // ID baru, pertahankan data
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

Ini hanyalah contoh sederhana tentang cara menggunakan ini dalam middleware. Untuk contoh yang lebih mendalam, lihat dokumentasi [middleware](/learn/middleware).

## Metode

Kelas `Session` menyediakan metode ini:

- `set(string $key, $value)`: Menyimpan sebuah nilai dalam sesi.
- `get(string $key, $default = null)`: Mengambil sebuah nilai, dengan nilai default opsional jika kunci tidak ada.
- `delete(string $key)`: Menghapus kunci tertentu dari sesi.
- `clear()`: Menghapus semua data sesi.
- `commit()`: Menyimpan data sesi saat ini ke sistem file.
- `id()`: Mengembalikan ID sesi saat ini.
- `regenerate(bool $deleteOld = false)`: Regenerasi ID sesi, pilihan untuk menghapus data lama.

Semua metode kecuali `get()` dan `id()` mengembalikan instance `Session` untuk chaining.

## Mengapa Menggunakan Plugin Ini?

- **Ringan**: Tidak ada dependensi eksternal—hanya file.
- **Non-Blocking**: Menghindari penguncian sesi dengan `read_and_close` secara default.
- **Aman**: Mendukung enkripsi AES-256-CBC untuk data sensitif.
- **Fleksibel**: Opsi auto-commit, mode uji, dan kontrol manual.
- **Flight-Native**: Dibangun khusus untuk framework Flight.

## Detail Teknis

- **Format Penyimpanan**: File sesi diawali dengan `sess_` dan disimpan di `save_path` yang dikonfigurasi. Data terenkripsi menggunakan awalan `E`, data teks biasa menggunakan `P`.
- **Enkripsi**: Menggunakan AES-256-CBC dengan IV acak per penulisan sesi saat kunci `encryption_key` disediakan.
- **Pengumpulan Sampah**: Mengimplementasikan `SessionHandlerInterface::gc()` PHP untuk membersihkan sesi yang telah kedaluwarsa.

## Berkontribusi

Kontribusi sangat diterima! Fork [repositori](https://github.com/flightphp/session), lakukan perubahan Anda, dan kirim pull request. Laporkan bug atau sarankan fitur melalui pelacak masalah Github.

## Lisensi

Plugin ini dilisensikan di bawah Lisensi MIT. Lihat [repositori Github](https://github.com/flightphp/session) untuk detailnya.