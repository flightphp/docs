# Kelas Pembantu PDO PdoWrapper

## Gambaran Umum

Kelas `PdoWrapper` di Flight adalah pembantu yang ramah untuk bekerja dengan database menggunakan PDO. Ini menyederhanakan tugas database umum, menambahkan beberapa metode yang berguna untuk mengambil hasil, dan mengembalikan hasil sebagai [Collections](/learn/collections) untuk akses yang mudah. Ini juga mendukung pencatatan query dan pemantauan kinerja aplikasi (APM) untuk kasus penggunaan lanjutan.

## Pemahaman

Bekerja dengan database di PHP bisa sedikit verbose, terutama saat menggunakan PDO secara langsung. `PdoWrapper` memperluas PDO dan menambahkan metode yang membuat querying, fetching, dan penanganan hasil jauh lebih mudah. Alih-alih mengelola pernyataan yang disiapkan dan mode fetch, Anda mendapatkan metode sederhana untuk tugas umum, dan setiap baris dikembalikan sebagai Collection, sehingga Anda dapat menggunakan notasi array atau objek.

Anda dapat mendaftarkan `PdoWrapper` sebagai layanan bersama di Flight, dan kemudian menggunakannya di mana saja di aplikasi Anda melalui `Flight::db()`.

## Penggunaan Dasar

### Mendaftarkan Pembantu PDO

Pertama, daftarkan kelas `PdoWrapper` dengan Flight:

```php
Flight::register('db', \flight\database\PdoWrapper::class, [
    'mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
]);
```

Sekarang Anda dapat menggunakan `Flight::db()` di mana saja untuk mendapatkan koneksi database Anda.

### Menjalankan Query

#### `runQuery()`

`function runQuery(string $sql, array $params = []): PDOStatement`

Gunakan ini untuk INSERT, UPDATE, atau saat Anda ingin mengambil hasil secara manual:

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM users WHERE status = ?", ['active']);
while ($row = $statement->fetch()) {
    // $row adalah array
}
```

Anda juga dapat menggunakannya untuk penulisan:

```php
$db->runQuery("INSERT INTO users (name) VALUES (?)", ['Alice']);
$db->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 1]);
```

#### `fetchField()`

`function fetchField(string $sql, array $params = []): mixed`

Dapatkan satu nilai tunggal dari database:

```php
$count = Flight::db()->fetchField("SELECT COUNT(*) FROM users WHERE status = ?", ['active']);
```

#### `fetchRow()`

`function fetchRow(string $sql, array $params = []): Collection`

Dapatkan satu baris sebagai Collection (akses array/objek):

```php
$user = Flight::db()->fetchRow("SELECT * FROM users WHERE id = ?", [123]);
echo $user['name'];
// atau
echo $user->name;
```

#### `fetchAll()`

`function fetchAll(string $sql, array $params = []): array<Collection>`

Dapatkan semua baris sebagai array dari Collections:

```php
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE status = ?", ['active']);
foreach ($users as $user) {
    echo $user['name'];
    // atau
    echo $user->name;
}
```

### Menggunakan Placeholder `IN()`

Anda dapat menggunakan satu `?` tunggal dalam klausa `IN()` dan meneruskan array atau string yang dipisahkan koma:

```php
$ids = [1, 2, 3];
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", [$ids]);
// atau
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", ['1,2,3']);
```

## Penggunaan Lanjutan

### Pencatatan Query & APM

Jika Anda ingin melacak kinerja query, aktifkan pelacakan APM saat mendaftarkan:

```php
Flight::register('db', \flight\database\PdoWrapper::class, [
    'mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [/* options */], true // param terakhir mengaktifkan APM
]);
```

Setelah menjalankan query, Anda dapat mencatatnya secara manual tetapi APM akan mencatatnya secara otomatis jika diaktifkan:

```php
Flight::db()->logQueries();
```

Ini akan memicu sebuah event (`flight.db.queries`) dengan metrik koneksi dan query, yang dapat Anda dengarkan menggunakan sistem event Flight.

### Contoh Lengkap

```php
Flight::route('/users', function () {
    // Dapatkan semua pengguna
    $users = Flight::db()->fetchAll('SELECT * FROM users');

    // Stream semua pengguna
    $statement = Flight::db()->runQuery('SELECT * FROM users');
    while ($user = $statement->fetch()) {
        echo $user['name'];
    }

    // Dapatkan satu pengguna tunggal
    $user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

    // Dapatkan satu nilai tunggal
    $count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

    // Sintaks IN() khusus
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', ['1,2,3,4,5']);

    // Sisipkan pengguna baru
    Flight::db()->runQuery("INSERT INTO users (name, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
    $insert_id = Flight::db()->lastInsertId();

    // Perbarui pengguna
    Flight::db()->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 123]);

    // Hapus pengguna
    Flight::db()->runQuery("DELETE FROM users WHERE id = ?", [123]);

    // Dapatkan jumlah baris yang terpengaruh
    $statement = Flight::db()->runQuery("UPDATE users SET name = ? WHERE name = ?", ['Bob', 'Sally']);
    $affected_rows = $statement->rowCount();
});
```

## Lihat Juga

- [Collections](/learn/collections) - Pelajari cara menggunakan kelas Collection untuk akses data yang mudah.

## Pemecahan Masalah

- Jika Anda mendapatkan kesalahan tentang koneksi database, periksa DSN, nama pengguna, kata sandi, dan opsi Anda.
- Semua baris dikembalikan sebagai Collections—jika Anda membutuhkan array biasa, gunakan `$collection->getData()`.
- Untuk query `IN (?)`, pastikan untuk meneruskan array atau string yang dipisahkan koma.

## Changelog

- v3.2.0 - Rilis awal PdoWrapper dengan metode query dan fetch dasar.