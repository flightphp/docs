# Metode API Framework

Flight dirancang agar mudah digunakan dan dipahami. Berikut adalah kumpulan lengkap
metode untuk framework. Ini terdiri dari metode inti, yang merupakan metode statis biasa, dan metode yang dapat diperluas, yang merupakan metode yang dipetakan yang dapat difilter
atau di-override.

## Metode Inti

Metode ini adalah inti dari framework dan tidak dapat di-override.

```php
Flight::map(string $name, callable $callback, bool $pass_route = false) // Membuat metode framework kustom.
Flight::register(string $name, string $class, array $params = [], ?callable $callback = null) // Mendaftar kelas ke metode framework.
Flight::unregister(string $name) // Mencopot pendaftaran kelas dari metode framework.
Flight::before(string $name, callable $callback) // Menambahkan filter sebelum metode framework.
Flight::after(string $name, callable $callback) // Menambahkan filter setelah metode framework.
Flight::path(string $path) // Menambahkan jalur untuk mengautoload kelas.
Flight::get(string $key) // Mendapatkan variabel yang ditetapkan oleh Flight::set().
Flight::set(string $key, mixed $value) // Mengatur variabel dalam mesin Flight.
Flight::has(string $key) // Memeriksa apakah variabel telah diatur.
Flight::clear(array|string $key = []) // Menghapus variabel.
Flight::init() // Menginisialisasi framework ke pengaturan default.
Flight::app() // Mendapatkan instance objek aplikasi.
Flight::request() // Mendapatkan instance objek permintaan.
Flight::response() // Mendapatkan instance objek respons.
Flight::router() // Mendapatkan instance objek router.
Flight::view() // Mendapatkan instance objek tampilan.
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
Flight::redirect(string $url, int $code) // Mengarahkan ke URL lain.
Flight::download(string $filePath) // Mengunduh file.
Flight::render(string $file, array $data, ?string $key = null) // Membangun file template.
Flight::error(Throwable $error) // Mengirimkan respons HTTP 500.
Flight::notFound() // Mengirimkan respons HTTP 404.
Flight::etag(string $id, string $type = 'string') // Melakukan caching HTTP ETag.
Flight::lastModified(int $time) // Melakukan caching HTTP modifikasi terakhir.
Flight::json(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Mengirimkan respons JSON.
Flight::jsonp(mixed $data, string $param = 'jsonp', int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Mengirimkan respons JSONP.
Flight::jsonHalt(mixed $data, int $code = 200, bool $encode = true, string $charset = 'utf8', int $option) // Mengirimkan respons JSON dan menghentikan framework.
```

Metode kustom yang ditambahkan dengan `map` dan `register` juga dapat difilter. Untuk contoh tentang cara memetakan metode ini, lihat panduan [Memperluas Flight](/learn/extending).