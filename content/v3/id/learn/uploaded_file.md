# Penanganan File yang Diunggah

## Gambaran Umum

Kelas `UploadedFile` di Flight memudahkan dan aman untuk menangani unggahan file dalam aplikasi Anda. Ini membungkus detail proses unggahan file PHP, memberikan cara yang sederhana dan berorientasi objek untuk mengakses informasi file dan memindahkan file yang diunggah.

## Pemahaman

Ketika pengguna mengunggah file melalui formulir, PHP menyimpan informasi tentang file tersebut di superglobal `$_FILES`. Di Flight, Anda jarang berinteraksi langsung dengan `$_FILES`. Sebaliknya, objek `Request` milik Flight (dapat diakses melalui `Flight::request()`) menyediakan metode `getUploadedFiles()` yang mengembalikan array objek `UploadedFile`, membuat penanganan file jauh lebih nyaman dan kuat.

Kelas `UploadedFile` menyediakan metode untuk:
- Mendapatkan nama file asli, tipe MIME, ukuran, dan lokasi sementara
- Memeriksa kesalahan unggahan
- Memindahkan file yang diunggah ke lokasi permanen

Kelas ini membantu Anda menghindari kesalahan umum dengan unggahan file, seperti penanganan kesalahan atau memindahkan file dengan aman.

## Penggunaan Dasar

### Mengakses File yang Diunggah dari Permintaan

Cara yang direkomendasikan untuk mengakses file yang diunggah adalah melalui objek permintaan:

```php
Flight::route('POST /upload', function() {
    // Untuk field formulir bernama <input type="file" name="myFile">
    $uploadedFiles = Flight::request()->getUploadedFiles();
    $file = $uploadedFiles['myFile'];

    // Sekarang Anda dapat menggunakan metode UploadedFile
    if ($file->getError() === UPLOAD_ERR_OK) {
        $file->moveTo('/path/to/uploads/' . $file->getClientFilename());
        echo "File berhasil diunggah!";
    } else {
        echo "Unggahan gagal: " . $file->getError();
    }
});
```

### Menangani Beberapa Unggahan File

Jika formulir Anda menggunakan `name="myFiles[]"` untuk beberapa unggahan, Anda akan mendapatkan array objek `UploadedFile`:

```php
Flight::route('POST /upload', function() {
    // Untuk field formulir bernama <input type="file" name="myFiles[]">
    $uploadedFiles = Flight::request()->getUploadedFiles();
    foreach ($uploadedFiles['myFiles'] as $file) {
        if ($file->getError() === UPLOAD_ERR_OK) {
            $file->moveTo('/path/to/uploads/' . $file->getClientFilename());
            echo "Diunggah: " . $file->getClientFilename() . "<br>";
        } else {
            echo "Gagal mengunggah: " . $file->getClientFilename() . "<br>";
        }
    }
});
```

### Membuat Instance UploadedFile Secara Manual

Biasanya, Anda tidak akan membuat `UploadedFile` secara manual, tetapi Anda bisa jika diperlukan:

```php
use flight\net\UploadedFile;

$file = new UploadedFile(
  $_FILES['myfile']['name'],
  $_FILES['myfile']['type'],
  $_FILES['myfile']['size'],
  $_FILES['myfile']['tmp_name'],
  $_FILES['myfile']['error']
);
```

### Mengakses Informasi File

Anda dapat dengan mudah mendapatkan detail tentang file yang diunggah:

```php
echo $file->getClientFilename();   // Nama file asli dari komputer pengguna
echo $file->getClientMediaType();  // Tipe MIME (misalnya, image/png)
echo $file->getSize();             // Ukuran file dalam byte
echo $file->getTempName();         // Jalur file sementara di server
echo $file->getError();            // Kode kesalahan unggahan (0 berarti tidak ada kesalahan)
```

### Memindahkan File yang Diunggah

Setelah memvalidasi file, pindahkan ke lokasi permanen:

```php
try {
  $file->moveTo('/path/to/uploads/' . $file->getClientFilename());
  echo "File berhasil diunggah!";
} catch (Exception $e) {
  echo "Unggahan gagal: " . $e->getMessage();
}
```

Metode `moveTo()` akan melempar pengecualian jika ada yang salah (seperti kesalahan unggahan atau masalah izin).

### Menangani Kesalahan Unggahan

Jika ada masalah selama unggahan, Anda dapat mendapatkan pesan kesalahan yang dapat dibaca oleh manusia:

```php
if ($file->getError() !== UPLOAD_ERR_OK) {
  // Anda dapat menggunakan kode kesalahan atau menangkap pengecualian dari moveTo()
  echo "Ada kesalahan saat mengunggah file.";
}
```

## Lihat Juga

- [Requests](/learn/requests) - Pelajari cara mengakses file yang diunggah dari permintaan HTTP dan lihat lebih banyak contoh unggahan file.
- [Configuration](/learn/configuration) - Cara mengonfigurasi batas unggahan dan direktori di PHP.
- [Extending](/learn/extending) - Cara menyesuaikan atau memperluas kelas inti Flight.

## Pemecahan Masalah

- Selalu periksa `$file->getError()` sebelum memindahkan file.
- Pastikan direktori unggahan Anda dapat ditulis oleh server web.
- Jika `moveTo()` gagal, periksa pesan pengecualian untuk detail.
- Pengaturan `upload_max_filesize` dan `post_max_size` PHP dapat membatasi unggahan file.
- Untuk beberapa unggahan file, selalu loop melalui array objek `UploadedFile`.

## Changelog

- v3.12.0 - Menambahkan kelas `UploadedFile` ke objek permintaan untuk penanganan file yang lebih mudah.