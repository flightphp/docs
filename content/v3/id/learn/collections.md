# Koleksi

## Gambaran Umum

Kelas `Collection` di Flight adalah utilitas yang berguna untuk mengelola kumpulan data. Ini memungkinkan Anda mengakses dan memanipulasi data menggunakan notasi array maupun objek, membuat kode Anda lebih bersih dan fleksibel.

## Pemahaman

`Collection` pada dasarnya adalah pembungkus sekitar array, tetapi dengan beberapa kemampuan tambahan. Anda dapat menggunakannya seperti array, mengulanginya, menghitung item-nya, dan bahkan mengakses item seolah-olah itu adalah properti objek. Ini sangat berguna ketika Anda ingin meneruskan data terstruktur di aplikasi Anda, atau ketika Anda ingin membuat kode Anda sedikit lebih mudah dibaca.

Koleksi mengimplementasikan beberapa antarmuka PHP:
- `ArrayAccess` (sehingga Anda dapat menggunakan sintaks array)
- `Iterator` (sehingga Anda dapat mengulang dengan `foreach`)
- `Countable` (sehingga Anda dapat menggunakan `count()`)
- `JsonSerializable` (sehingga Anda dapat dengan mudah mengonversi ke JSON)

## Penggunaan Dasar

### Membuat Koleksi

Anda dapat membuat koleksi dengan hanya meneruskan array ke konstruktornya:

```php
use flight\util\Collection;

$data = [
  'name' => 'Flight',
  'version' => 3,
  'features' => ['routing', 'views', 'extending']
];

$collection = new Collection($data);
```

### Mengakses Item

Anda dapat mengakses item menggunakan notasi array atau objek:

```php
// Notasi array
echo $collection['name']; // Output: Flight

// Notasi objek
echo $collection->version; // Output: 3
```

Jika Anda mencoba mengakses kunci yang tidak ada, Anda akan mendapatkan `null` alih-alih kesalahan.

### Mengatur Item

Anda juga dapat mengatur item menggunakan notasi yang sama:

```php
// Notasi array
$collection['author'] = 'Mike Cao';

// Notasi objek
$collection->license = 'MIT';
```

### Memeriksa dan Menghapus Item

Periksa apakah item ada:

```php
if (isset($collection['name'])) {
  // Lakukan sesuatu
}

if (isset($collection->version)) {
  // Lakukan sesuatu
}
```

Hapus item:

```php
unset($collection['author']);
unset($collection->license);
```

### Mengulang Koleksi

Koleksi dapat diulang, sehingga Anda dapat menggunakannya dalam loop `foreach`:

```php
foreach ($collection as $key => $value) {
  echo "$key: $value\n";
}
```

### Menghitung Item

Anda dapat menghitung jumlah item dalam koleksi:

```php
echo count($collection); // Output: 4
```

### Mendapatkan Semua Kunci atau Data

Dapatkan semua kunci:

```php
$keys = $collection->keys(); // ['name', 'version', 'features', 'license']
```

Dapatkan semua data sebagai array:

```php
$data = $collection->getData();
```

### Membersihkan Koleksi

Hapus semua item:

```php
$collection->clear();
```

### Serialisasi JSON

Koleksi dapat dengan mudah dikonversi ke JSON:

```php
echo json_encode($collection);
// Output: {"name":"Flight","version":3,"features":["routing","views","extending"],"license":"MIT"}
```

## Penggunaan Lanjutan

Anda dapat mengganti array data internal sepenuhnya jika diperlukan:

```php
$collection->setData(['foo' => 'bar']);
```

Koleksi sangat berguna ketika Anda ingin meneruskan data terstruktur antar komponen, atau ketika Anda ingin menyediakan antarmuka yang lebih berorientasi objek untuk data array.

## Lihat Juga

- [Requests](/learn/requests) - Pelajari cara menangani permintaan HTTP dan bagaimana koleksi dapat digunakan untuk mengelola data permintaan.
- [PDO Wrapper](/learn/pdo-wrapper) - Pelajari cara menggunakan pembungkus PDO di Flight dan bagaimana koleksi dapat digunakan untuk mengelola hasil basis data.

## Pemecahan Masalah

- Jika Anda mencoba mengakses kunci yang tidak ada, Anda akan mendapatkan `null` alih-alih kesalahan.
- Ingatlah bahwa koleksi tidak bersifat rekursif: array bersarang tidak secara otomatis dikonversi menjadi koleksi.
- Jika Anda perlu mereset koleksi, gunakan `$collection->clear()` atau `$collection->setData([])`.

## Changelog

- v3.0 - Peningkatan petunjuk tipe dan dukungan PHP 8+.
- v1.0 - Rilis awal kelas Collection.