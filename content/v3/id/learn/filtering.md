# Penyaringan

Flight memungkinkan Anda untuk menyaring metode sebelum dan setelah mereka dipanggil. Tidak ada
hook yang telah ditentukan sebelumnya yang perlu Anda ingat. Anda dapat menyaring salah satu dari metode kerangka kerja default
serta metode kustom apa pun yang telah Anda peta.

Fungsi filter terlihat seperti ini:

```php
function (array &$params, string &$output): bool {
  // Kode penyaring
}
```

Dengan menggunakan variabel yang diteruskan, Anda dapat memanipulasi parameter input dan/atau output.

Anda dapat menjalankan filter sebelum sebuah metode dengan melakukan:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  // Lakukan sesuatu
});
```

Anda dapat menjalankan filter setelah sebuah metode dengan melakukan:

```php
Flight::after('start', function (array &$params, string &$output): bool {
  // Lakukan sesuatu
});
```

Anda dapat menambahkan sebanyak mungkin filter yang Anda inginkan ke metode mana pun. Mereka akan dipanggil dalam
urutan di mana mereka dideklarasikan.

Berikut adalah contoh proses penyaringan:

```php
// Peta metode kustom
Flight::map('hello', function (string $name) {
  return "Halo, $name!";
});

// Tambahkan filter sebelum
Flight::before('hello', function (array &$params, string &$output): bool {
  // Manipulasi parameter
  $params[0] = 'Fred';
  return true;
});

// Tambahkan filter setelah
Flight::after('hello', function (array &$params, string &$output): bool {
  // Manipulasi output
  $output .= " Semoga harimu menyenangkan!";
  return true;
});

// Panggil metode kustom
echo Flight::hello('Bob');
```

Ini seharusnya menampilkan:

```
Halo Fred! Semoga harimu menyenangkan!
```

Jika Anda telah mendefinisikan beberapa filter, Anda dapat memutus rantai dengan mengembalikan `false`
di salah satu fungsi filter Anda:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'satu';
  return true;
});

Flight::before('start', function (array &$params, string &$output): bool {
  echo 'dua';

  // Ini akan mengakhiri rantai
  return false;
});

// Ini tidak akan dipanggil
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'tiga';
  return true;
});
```

Catatan, metode inti seperti `map` dan `register` tidak dapat disaring karena mereka
dipanggil secara langsung dan tidak dipanggil secara dinamis.