# Tracy

Tracy adalah penangan kesalahan yang luar biasa yang dapat digunakan dengan Flight. Ia memiliki sejumlah panel yang dapat membantu Anda dalam mendebug aplikasi Anda. Ini juga sangat mudah untuk diperluas dan menambahkan panel Anda sendiri. Tim Flight telah membuat beberapa panel khusus untuk proyek Flight dengan plugin [flightphp/tracy-extensions](https://github.com/flightphp/tracy-extensions).

## Instalasi

Instal dengan composer. Dan Anda akan ingin menginstal ini tanpa versi dev karena Tracy dilengkapi dengan komponen penanganan kesalahan produksi.

```bash
composer require tracy/tracy
```

## Konfigurasi Dasar

Ada beberapa opsi konfigurasi dasar untuk memulai. Anda dapat membaca lebih lanjut tentang mereka di [Dokumentasi Tracy](https://tracy.nette.org/en/configuring).

```php

require 'vendor/autoload.php';

use Tracy\Debugger;

// Mengaktifkan Tracy
Debugger::enable();
// Debugger::enable(Debugger::DEVELOPMENT) // kadang-kadang Anda harus eksplisit (juga Debugger::PRODUCTION)
// Debugger::enable('23.75.345.200'); // Anda juga dapat menyediakan array alamat IP

// Di sinilah kesalahan dan pengecualian akan dicatat. Pastikan direktori ini ada dan dapat ditulisi.
Debugger::$logDirectory = __DIR__ . '/../log/';
Debugger::$strictMode = true; // tampilkan semua kesalahan
// Debugger::$strictMode = E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED; // semua kesalahan kecuali pemberitahuan kadaluarsa
if (Debugger::$showBar) {
    $app->set('flight.content_length', false); // jika bilah Debugger terlihat, maka panjang konten tidak dapat diatur oleh Flight

	// Ini khusus untuk Ekstensi Tracy untuk Flight jika Anda telah menyertakannya
	// jika tidak, silakan komentari ini.
	new TracyExtensionLoader($app);
}
```

## Tips Berguna

Saat Anda mendebug kode Anda, ada beberapa fungsi yang sangat berguna untuk mengeluarkan data untuk Anda.

- `bdump($var)` - Ini akan mencetak variabel ke Tracy Bar di panel terpisah.
- `dumpe($var)` - Ini akan mencetak variabel dan kemudian mati segera.