# Metode API Framework

Flight dirancang untuk mudah digunakan dan dipahami. Berikut adalah
set lengkap metode untuk framework. Ini terdiri dari metode inti, yang merupakan metode statis biasa, dan metode yang dapat diperluas, yang merupakan metode yang dipetakan yang dapat disaring atau ditimpa.

## Metode Inti

Metode-metode ini adalah inti dari framework dan tidak dapat ditimpa.

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Membuat metode framework khusus.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Mendaftarkan kelas ke metode framework.
Flight::unregister(string $name) // Menghapus pendaftaran kelas pada metode framework.
Flight::before(string $name, callable $callback) // Menambahkan filter sebelum metode framework.
Flight::after(string $name, callable $callback) // Menambahkan filter setelah metode framework.
Flight::path(string $path) // Menambahkan jalur untuk memuat kelas secara otomatis.
Flight::get(string $key) // Mengambil variabel yang disetel oleh Flight::set().
Flight::set(string $key, mixed $value) // Menyetel variabel dalam mesin Flight.
Flight::has(string $key) // Memeriksa apakah sebuah variabel disetel.
Flight::clear(array|string $key = []) // Menghapus sebuah variabel.
Flight::init() // Menginisialisasi framework ke pengaturan defaultnya.
Flight::app() // Mengambil instance objek aplikasi.
Flight::request() // Mengambil instance objek permintaan.
Flight::response() // Mengambil instance objek respons.
Flight::router() // Mengambil instance objek router.
Flight::view() // Mengambil instance objek tampilan.
```

## Metode yang Dapat Diperluas

```php
Flight::start() // Memulai framework.
Flight::stop() // Menghentikan framework dan mengirimkan respons.
Flight::halt(int $code = 200, string $message = '') // Menghentikan framework dengan kode status dan pesan opsional.
Flight::route(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Memetakan pola URL ke callback.
Flight::post(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Memetakan pola URL permintaan POST ke callback.
Flight::put(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Memetakan pola URL permintaan PUT ke callback.
Flight::patch(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Memetakan pola URL permintaan PATCH ke callback.
Flight::delete(string $pattern, callable $callback, bool $pass_route = false, string $alias = '') // Memetakan pola URL permintaan DELETE ke callback.
Flight::group(string $pattern, callable $callback) // Membuat pengelompokan untuk URL, pola harus berupa string.
Flight::getUrl(string $name, array $params = []) // Menghasilkan URL berdasarkan alias rute.
Flight::redirect(string $url, int $code) // Mengalihkan ke URL lain.
Flight::download(string $filePath) // Mengunduh sebuah file.
Flight::render(string $file, array $data, ?string $key = null) // Merender file template.
Flight::error(Throwable $error) // Mengirimkan respons HTTP 500.
Flight::notFound() // Mengirimkan respons HTTP 404.
Flight::etag(string $id, string $type = 'string') // Melakukan caching HTTP ETag.
Flight::lastModified(int $time) // Melakukan caching HTTP terakhir dimodifikasi.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Mengirimkan respons JSON.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Mengirimkan respons JSONP.
Flight::jsonHalt(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Mengirimkan respons JSON dan menghentikan framework.
Flight::onEvent(string $event, callable $callback) // Mendaftarkan pendengar acara.
Flight::triggerEvent(string $event, ...$args) // Memicu sebuah acara.
```

Metode kustom apa pun yang ditambahkan dengan `map` dan `register` juga dapat disaring. Untuk contoh tentang bagaimana memetakan metode ini, lihat panduan [Memperluas Flight](/learn/extending).