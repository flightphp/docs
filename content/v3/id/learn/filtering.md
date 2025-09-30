# Penyaringan

## Gambaran Umum

Flight memungkinkan Anda menyaring [metode yang dipetakan](/learn/extending) sebelum dan sesudah mereka dipanggil.

## Pemahaman
Tidak ada hook yang telah ditentukan sebelumnya yang perlu Anda hafal. Anda dapat menyaring metode kerangka kerja default apa pun serta metode kustom apa pun yang telah Anda petakan.

Fungsi filter terlihat seperti ini:

```php
/**
 * @param array $params Parameter yang diteruskan ke metode yang disaring.
 * @param string $output (hanya penyanggaan output v2) Output dari metode yang disaring.
 * @return bool Kembalikan true/void atau jangan kembalikan untuk melanjutkan rantai, false untuk memutus rantai.
 */
function (array &$params, string &$output): bool {
  // Kode filter
}
```

Dengan menggunakan variabel yang diteruskan, Anda dapat memanipulasi parameter input dan/atau output.

Anda dapat menjalankan filter sebelum metode dengan melakukan:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  // Lakukan sesuatu
});
```

Anda dapat menjalankan filter setelah metode dengan melakukan:

```php
Flight::after('start', function (array &$params, string &$output): bool {
  // Lakukan sesuatu
});
```

Anda dapat menambahkan sebanyak filter yang Anda inginkan ke metode apa pun. Mereka akan dipanggil dalam urutan yang mereka dinyatakan.

Berikut adalah contoh proses penyaringan:

```php
// Petakan metode kustom
Flight::map('hello', function (string $name) {
  return "Hello, $name!";
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
  $output .= " Have a nice day!";
  return true;
});

// Panggil metode kustom
echo Flight::hello('Bob');
```

Ini seharusnya menampilkan:

```
Hello Fred! Have a nice day!
```

Jika Anda telah mendefinisikan beberapa filter, Anda dapat memutus rantai dengan mengembalikan `false`
dalam fungsi filter mana pun:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'one';
  return true;
});

Flight::before('start', function (array &$params, string &$output): bool {
  echo 'two';

  // Ini akan mengakhiri rantai
  return false;
});

// Ini tidak akan dipanggil
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'three';
  return true;
});
```

> **Catatan:** Metode inti seperti `map` dan `register` tidak dapat disaring karena mereka
dipanggil secara langsung dan tidak dipanggil secara dinamis. Lihat [Memperluas Flight](/learn/extending) untuk informasi lebih lanjut.

## Lihat Juga
- [Memperluas Flight](/learn/extending)

## Pemecahan Masalah
- Pastikan Anda mengembalikan `false` dari fungsi filter Anda jika Anda ingin rantai berhenti. Jika Anda tidak mengembalikan apa pun, rantai akan berlanjut.

## Log Perubahan
- v2.0 - Rilis Awal.