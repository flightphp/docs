# Membangun Blog Sederhana dengan Flight PHP

Panduan ini memandu Anda melalui pembuatan blog dasar menggunakan framework Flight PHP. Anda akan mengatur proyek, mendefinisikan rute, mengelola pos dengan JSON, dan merendernya dengan mesin templating Latte—semuanya menunjukkan kesederhanaan dan fleksibilitas Flight. Pada akhir panduan, Anda akan memiliki blog fungsional dengan halaman utama, halaman pos individu, dan formulir pembuatan.

## Prasyarat
- **PHP 7.4+**: Terinstal di sistem Anda.
- **Composer**: Untuk manajemen ketergantungan.
- **Editor Teks**: Editor apa pun seperti VS Code atau PHPStorm.
- Pengetahuan dasar tentang PHP dan pengembangan web.

## Langkah 1: Siapkan Proyek Anda

Mulailah dengan membuat direktori proyek baru dan menginstal Flight melalui Composer.

1. **Buat Direktori**:
   ```bash
   mkdir flight-blog
   cd flight-blog
   ```

2. **Instal Flight**:
   ```bash
   composer require flightphp/core
   ```

3. **Buat Direktori Publik**:
   Flight menggunakan titik masuk tunggal (`index.php`). Buat folder `public/` untuk itu:
   ```bash
   mkdir public
   ```

4. **`index.php` Dasar**:
   Buat `public/index.php` dengan rute "hello world" yang sederhana:
   ```php
   <?php
   require '../vendor/autoload.php';

   Flight::route('/', function () {
       echo 'Halo, Flight!';
   });

   Flight::start();
   ```

5. **Jalankan Server Bawaan**:
   Uji pengaturan Anda dengan server pengembangan PHP:
   ```bash
   php -S localhost:8000 -t public/
   ```
   Kunjungi `http://localhost:8000` untuk melihat "Halo, Flight!".

## Langkah 2: Atur Struktur Proyek Anda

Untuk pengaturan yang bersih, struktur proyek Anda seperti ini:

```text
flight-blog/
├── app/
│   ├── config/
│   └── views/
├── data/
├── public/
│   └── index.php
├── vendor/
└── composer.json
```

- `app/config/`: File konfigurasi (misalnya, acara, rute).
- `app/views/`: Template untuk merender halaman.
- `data/`: File JSON untuk menyimpan pos blog.
- `public/`: Root web dengan `index.php`.

## Langkah 3: Instal dan Konfigurasi Latte

Latte adalah mesin templating ringan yang terintegrasi dengan baik dengan Flight.

1. **Instal Latte**:
   ```bash
   composer require latte/latte
   ```

2. **Konfigurasikan Latte di Flight**:
   Perbarui `public/index.php` untuk mendaftarkan Latte sebagai mesin tampilan:
   ```php
   <?php
   require '../vendor/autoload.php';

   use Latte\Engine;

   Flight::register('view', Engine::class, [], function ($latte) {
       $latte->setTempDirectory(__DIR__ . '/../cache/');
       $latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../app/views/'));
   });

   Flight::route('/', function () {
       Flight::view()->render('home.latte', ['title' => 'Blog Saya']);
   });

   Flight::start();
   ```

3. **Buat Template Layout: 
Di `app/views/layout.latte`**:
```html
<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
</head>
<body>
    <header>
        <h1>Blog Saya</h1>
        <nav>
            <a href="/">Beranda</a> | 
            <a href="/create">Buat Pos</a>
        </nav>
    </header>
    <main>
        {block content}{/block}
    </main>
    <footer>
        <p>&copy; {date('Y')} Blog Flight</p>
    </footer>
</body>
</html>
```

4. **Buat Template Beranda**:
   Di `app/views/home.latte`:
   ```html
   {extends 'layout.latte'}

	{block content}
		<h2>{$title}</h2>
		<ul>
		{foreach $posts as $post}
			<li><a href="/post/{$post['slug']}">{$post['title']}</a></li>
		{/foreach}
		</ul>
	{/block}
   ```
   Mulai ulang server jika Anda keluar dan kunjungi `http://localhost:8000` untuk melihat halaman yang dirender.

5. **Buat File Data**:

   Gunakan file JSON untuk mensimulasikan database untuk kesederhanaan.

   Di `data/posts.json`:
   ```json
   [
       {
           "slug": "first-post",
           "title": "Pos Pertama Saya",
           "content": "Ini adalah pos blog pertama saya dengan Flight PHP!"
       }
   ]
   ```

## Langkah 4: Definisikan Rute

Pisahkan rute Anda ke dalam file konfigurasi untuk organisasi yang lebih baik.

1. **Buat `routes.php`**:
   Di `app/config/routes.php`:
   ```php
   <?php
   Flight::route('/', function () {
       Flight::view()->render('home.latte', ['title' => 'Blog Saya']);
   });

   Flight::route('/post/@slug', function ($slug) {
       Flight::view()->render('post.latte', ['title' => 'Pos: ' . $slug, 'slug' => $slug]);
   });

   Flight::route('GET /create', function () {
       Flight::view()->render('create.latte', ['title' => 'Buat Pos']);
   });
   ```

2. **Perbarui `index.php`**:
   Termasuk file rute:
   ```php
   <?php
   require '../vendor/autoload.php';

   use Latte\Engine;

   Flight::register('view', Engine::class, [], function ($latte) {
       $latte->setTempDirectory(__DIR__ . '/../cache/');
       $latte->setLoader(new \Latte\Loaders\FileLoader(__DIR__ . '/../app/views/'));
   });

   require '../app/config/routes.php';

   Flight::start();
   ```

## Langkah 5: Simpan dan Ambil Pos Blog

Tambahkan metode untuk memuat dan menyimpan pos.

1. **Tambahkan Metode Pos**:
   Di `index.php`, tambahkan metode untuk memuat pos:
   ```php
   Flight::map('posts', function () {
       $file = __DIR__ . '/../data/posts.json';
       return json_decode(file_get_contents($file), true);
   });
   ```

2. **Perbarui Rute**:
   Modifikasi `app/config/routes.php` untuk menggunakan pos:
   ```php
   <?php
   Flight::route('/', function () {
       $posts = Flight::posts();
       Flight::view()->render('home.latte', [
           'title' => 'Blog Saya',
           'posts' => $posts
       ]);
   });

   Flight::route('/post/@slug', function ($slug) {
       $posts = Flight::posts();
       $post = array_filter($posts, fn($p) => $p['slug'] === $slug);
       $post = reset($post) ?: null;
       if (!$post) {
           Flight::notFound();
           return;
       }
       Flight::view()->render('post.latte', [
           'title' => $post['title'],
           'post' => $post
       ]);
   });

   Flight::route('GET /create', function () {
       Flight::view()->render('create.latte', ['title' => 'Buat Pos']);
   });
   ```

## Langkah 6: Buat Template

Perbarui template Anda untuk menampilkan pos.

1. **Halaman Pos (`app/views/post.latte`)**:
   ```html
   {extends 'layout.latte'}

	{block content}
		<h2>{$post['title']}</h2>
		<div class="post-content">
			<p>{$post['content']}</p>
		</div>
	{/block}
   ```

## Langkah 7: Tambahkan Pembuatan Pos

Tangani pengiriman formulir untuk menambahkan pos baru.

1. **Formulir Buat (`app/views/create.latte`)**:
   ```html
   {extends 'layout.latte'}

	{block content}
		<h2>{$title}</h2>
		<form method="POST" action="/create">
			<div class="form-group">
				<label for="title">Judul:</label>
				<input type="text" name="title" id="title" required>
			</div>
			<div class="form-group">
				<label for="content">Konten:</label>
				<textarea name="content" id="content" required></textarea>
			</div>
			<button type="submit">Simpan Pos</button>
		</form>
	{/block}
   ```

2. **Tambahkan Rute POST**:
   Di `app/config/routes.php`:
   ```php
   Flight::route('POST /create', function () {
       $request = Flight::request();
       $title = $request->data['title'];
       $content = $request->data['content'];
       $slug = strtolower(str_replace(' ', '-', $title));

       $posts = Flight::posts();
       $posts[] = ['slug' => $slug, 'title' => $title, 'content' => $content];
       file_put_contents(__DIR__ . '/../../data/posts.json', json_encode($posts, JSON_PRETTY_PRINT));

       Flight::redirect('/');
   });
   ```

3. **Uji Coba**:
   - Kunjungi `http://localhost:8000/create`.
   - Kirim pos baru (misalnya, “Pos Kedua” dengan beberapa konten).
   - Periksa halaman utama untuk melihatnya terdaftar.

## Langkah 8: Perbaiki dengan Penanganan Kesalahan

Timpakan metode `notFound` untuk pengalaman 404 yang lebih baik.

Di `index.php`:
```php
Flight::map('notFound', function () {
    Flight::view()->render('404.latte', ['title' => 'Halaman Tidak Ditemukan']);
});
```

Buat `app/views/404.latte`:
```html
{extends 'layout.latte'}

{block content}
    <h2>404 - {$title}</h2>
    <p>Maaf, halaman tersebut tidak ada!</p>
{/block}
```

## Langkah Selanjutnya
- **Tambahkan Gaya**: Gunakan CSS di template Anda untuk tampilan yang lebih baik.
- **Database**: Ganti `posts.json` dengan database seperti SQLite menggunakan `PdoWrapper`.
- **Validasi**: Tambahkan cek untuk slug duplikat atau input kosong.
- **Middleware**: Implementasikan autentikasi untuk pembuatan pos.

## Kesimpulan

Anda telah membangun blog sederhana dengan Flight PHP! Panduan ini menunjukkan fitur inti seperti routing, templating dengan Latte, dan menangani pengiriman formulir—semuanya tetap ringan. Jelajahi dokumentasi Flight untuk fitur-fitur lebih lanjut untuk membawa blog Anda lebih jauh!