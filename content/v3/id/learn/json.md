# JSON Wrapper

## Gambaran Umum

Kelas `Json` di Flight menyediakan cara sederhana dan konsisten untuk mengkodekan dan mendekodekan data JSON dalam aplikasi Anda. Ini membungkus fungsi JSON asli PHP dengan penanganan kesalahan yang lebih baik dan beberapa pengaturan default yang membantu, membuatnya lebih mudah dan aman untuk bekerja dengan JSON.

## Pemahaman

Bekerja dengan JSON sangat umum di aplikasi PHP modern, terutama saat membangun API atau menangani permintaan AJAX. Kelas `Json` memusatkan semua pengkodean dan dekodean JSON Anda, sehingga Anda tidak perlu khawatir tentang kasus tepi yang aneh atau kesalahan kriptik dari fungsi bawaan PHP.

Fitur utama:
- Penanganan kesalahan yang konsisten (melemparkan pengecualian saat gagal)
- Opsi default untuk pengkodean/dekodean (seperti garis miring yang tidak di-escape)
- Metode utilitas untuk pencetakan cantik dan validasi

## Penggunaan Dasar

### Mengkodekan Data ke JSON

Untuk mengonversi data PHP ke string JSON, gunakan `Json::encode()`:

```php
use flight\util\Json;

$data = [
  'framework' => 'Flight',
  'version' => 3,
  'features' => ['routing', 'views', 'extending']
];

$json = Json::encode($data);
echo $json;
// Output: {"framework":"Flight","version":3,"features":["routing","views","extending"]}
```

Jika pengkodean gagal, Anda akan mendapatkan pengecualian dengan pesan kesalahan yang membantu.

### Pencetakan Cantik

Ingin JSON Anda mudah dibaca oleh manusia? Gunakan `prettyPrint()`:

```php
echo Json::prettyPrint($data);
/*
{
  "framework": "Flight",
  "version": 3,
  "features": [
    "routing",
    "views",
    "extending"
  ]
}
*/
```

### Mendekodekan String JSON

Untuk mengonversi string JSON kembali ke data PHP, gunakan `Json::decode()`:

```php
$json = '{"framework":"Flight","version":3}';
$data = Json::decode($json);
echo $data->framework; // Output: Flight
```

Jika Anda ingin array asosiatif daripada objek, berikan `true` sebagai argumen kedua:

```php
$data = Json::decode($json, true);
echo $data['framework']; // Output: Flight
```

Jika dekodean gagal, Anda akan mendapatkan pengecualian dengan pesan kesalahan yang jelas.

### Memvalidasi JSON

Periksa apakah string adalah JSON yang valid:

```php
if (Json::isValid($json)) {
  // Itu valid!
} else {
  // Bukan JSON yang valid
}
```

### Mendapatkan Kesalahan Terakhir

Jika Anda ingin memeriksa pesan kesalahan JSON terakhir (dari fungsi PHP asli):

```php
$error = Json::getLastError();
if ($error !== '') {
  echo "Last JSON error: $error";
}
```

## Penggunaan Lanjutan

Anda dapat menyesuaikan opsi pengkodean dan dekodean jika Anda membutuhkan kontrol lebih (lihat [opsi json_encode PHP](https://www.php.net/manual/en/json.constants.php)):

```php
// Encode dengan opsi HEX_TAG
$json = Json::encode($data, JSON_HEX_TAG);

// Decode dengan kedalaman khusus
$data = Json::decode($json, false, 1024);
```

## Lihat Juga

- [Collections](/learn/collections) - Untuk bekerja dengan data terstruktur yang dapat dengan mudah dikonversi ke JSON.
- [Configuration](/learn/configuration) - Cara mengonfigurasi aplikasi Flight Anda.
- [Extending](/learn/extending) - Cara menambahkan utilitas sendiri atau menimpa kelas inti.

## Pemecahan Masalah

- Jika pengkodean atau dekodean gagal, pengecualian dilemparkan—bungkus panggilan Anda dalam try/catch jika Anda ingin menangani kesalahan dengan anggun.
- Jika Anda mendapatkan hasil yang tidak diharapkan, periksa data Anda untuk referensi melingkar atau karakter non-UTF8.
- Gunakan `Json::isValid()` untuk memeriksa apakah string adalah JSON yang valid sebelum mendekode.

## Changelog

- v3.16.0 - Ditambahkan kelas utilitas pembungkus JSON.