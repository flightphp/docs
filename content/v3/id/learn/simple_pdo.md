# Kelas Pembantu SimplePdo PDO

## Gambaran Umum

Kelas `SimplePdo` di Flight adalah pembantu modern dan kaya fitur untuk bekerja dengan database menggunakan PDO. Ia memperluas `PdoWrapper` dan menambahkan metode pembantu yang nyaman untuk operasi database umum seperti `insert()`, `update()`, `delete()`, dan transaksi. Ia menyederhanakan tugas database, mengembalikan hasil sebagai [Collections](/learn/collections) untuk akses mudah, dan mendukung pencatatan query serta pemantauan kinerja aplikasi (APM) untuk kasus penggunaan lanjutan.

## Pemahaman

Kelas `SimplePdo` dirancang untuk membuat bekerja dengan database di PHP menjadi jauh lebih mudah. Daripada mengelola pernyataan yang disiapkan, mode pengambilan, dan operasi SQL yang verbose, Anda mendapatkan metode yang bersih dan sederhana untuk tugas umum. Setiap baris dikembalikan sebagai Collection, sehingga Anda dapat menggunakan notasi array (`$row['name']`) dan notasi objek (`$row->name`).

Kelas ini adalah superset dari `PdoWrapper`, yang berarti ia mencakup semua fungsionalitas `PdoWrapper` ditambah metode pembantu tambahan yang membuat kode Anda lebih bersih dan mudah dipelihara. Jika Anda saat ini menggunakan `PdoWrapper`, upgrade ke `SimplePdo` adalah hal yang sederhana karena ia memperluas `PdoWrapper`.

Anda dapat mendaftarkan `SimplePdo` sebagai layanan bersama di Flight, dan kemudian menggunakannya di mana saja di aplikasi Anda melalui `Flight::db()`.

## Penggunaan Dasar

### Mendaftarkan SimplePdo

Pertama, daftarkan kelas `SimplePdo` dengan Flight:

```php
Flight::register('db', \flight\database\SimplePdo::class, [
    'mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
]);
```

> **CATATAN**
>
> Jika Anda tidak menentukan `PDO::ATTR_DEFAULT_FETCH_MODE`, `SimplePdo` akan secara otomatis mengaturnya ke `PDO::FETCH_ASSOC` untuk Anda.

Sekarang Anda dapat menggunakan `Flight::db()` di mana saja untuk mendapatkan koneksi database Anda.

### Menjalankan Query

#### `runQuery()`

`function runQuery(string $sql, array $params = []): PDOStatement`

Gunakan ini untuk INSERT, UPDATE, atau ketika Anda ingin mengambil hasil secara manual:

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

`function fetchRow(string $sql, array $params = []): ?Collection`

Dapatkan satu baris sebagai Collection (akses array/objek):

```php
$user = Flight::db()->fetchRow("SELECT * FROM users WHERE id = ?", [123]);
echo $user['name'];
// atau
echo $user->name;
```

> **TIPS**
>
> `SimplePdo` secara otomatis menambahkan `LIMIT 1` ke query `fetchRow()` jika belum ada, membuat query Anda lebih efisien.

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

#### `fetchColumn()`

`function fetchColumn(string $sql, array $params = []): array`

Ambil satu kolom sebagai array:

```php
$ids = Flight::db()->fetchColumn("SELECT id FROM users WHERE active = ?", [1]);
// Mengembalikan: [1, 2, 3, 4, 5]
```

#### `fetchPairs()`

`function fetchPairs(string $sql, array $params = []): array`

Ambil hasil sebagai pasangan kunci-nilai (kolom pertama sebagai kunci, kedua sebagai nilai):

```php
$userNames = Flight::db()->fetchPairs("SELECT id, name FROM users");
// Mengembalikan: [1 => 'John', 2 => 'Jane', 3 => 'Bob']
```

### Menggunakan Placeholder `IN()`

Anda dapat menggunakan satu `?` tunggal dalam klausa `IN()` dan meneruskan array:

```php
$ids = [1, 2, 3];
$users = Flight::db()->fetchAll("SELECT * FROM users WHERE id IN (?)", [$ids]);
```

## Metode Pembantu

Salah satu keunggulan utama `SimplePdo` dibandingkan `PdoWrapper` adalah penambahan metode pembantu yang nyaman untuk operasi database umum.

### `insert()`

`function insert(string $table, array $data): string`

Masukkan satu atau lebih baris dan kembalikan ID sisipan terakhir.

**Sisipan tunggal:**

```php
$id = Flight::db()->insert('users', [
    'name' => 'John',
    'email' => 'john@example.com'
]);
```

**Sisipan massal:**

```php
$id = Flight::db()->insert('users', [
    ['name' => 'John', 'email' => 'john@example.com'],
    ['name' => 'Jane', 'email' => 'jane@example.com'],
]);
```

### `update()`

`function update(string $table, array $data, string $where, array $whereParams = []): int`

Perbarui baris dan kembalikan jumlah baris yang terpengaruh:

```php
$affected = Flight::db()->update(
    'users',
    ['name' => 'Jane', 'email' => 'jane@example.com'],
    'id = ?',
    [1]
);
```

> **CATATAN**
>
> `rowCount()` SQLite mengembalikan jumlah baris di mana data benar-benar berubah. Jika Anda memperbarui baris dengan nilai yang sama yang sudah dimilikinya, `rowCount()` akan mengembalikan 0. Ini berbeda dari perilaku MySQL saat menggunakan `PDO::MYSQL_ATTR_FOUND_ROWS`.

### `delete()`

`function delete(string $table, string $where, array $whereParams = []): int`

Hapus baris dan kembalikan jumlah baris yang dihapus:

```php
$deleted = Flight::db()->delete('users', 'id = ?', [1]);
```

### `transaction()`

`function transaction(callable $callback): mixed`

Jalankan callback dalam transaksi. Transaksi secara otomatis melakukan commit pada sukses atau rollback pada kesalahan:

```php
$result = Flight::db()->transaction(function($db) {
    $db->insert('users', ['name' => 'John']);
    $db->insert('logs', ['action' => 'user_created']);
    return $db->lastInsertId();
});
```

Jika ada pengecualian yang dilemparkan dalam callback, transaksi secara otomatis di-rollback dan pengecualian dilemparkan kembali.

## Penggunaan Lanjutan

### Pencatatan Query & APM

Jika Anda ingin melacak kinerja query, aktifkan pelacakan APM saat mendaftarkan:

```php
Flight::register('db', \flight\database\SimplePdo::class, [
    'mysql:host=localhost;dbname=cool_db_name',
    'user',
    'pass',
    [/* opsi PDO */],
    [
        'trackApmQueries' => true,
        'maxQueryMetrics' => 1000
    ]
]);
```

Setelah menjalankan query, Anda dapat mencatatnya secara manual, tetapi APM akan mencatatnya secara otomatis jika diaktifkan:

```php
Flight::db()->logQueries();
```

Ini akan memicu event (`flight.db.queries`) dengan metrik koneksi dan query, yang dapat Anda dengarkan menggunakan sistem event Flight.

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

    // Dapatkan satu pengguna
    $user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

    // Dapatkan satu nilai
    $count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

    // Dapatkan satu kolom
    $ids = Flight::db()->fetchColumn('SELECT id FROM users');

    // Dapatkan pasangan kunci-nilai
    $userNames = Flight::db()->fetchPairs('SELECT id, name FROM users');

    // Sintaks IN() khusus
    $users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);

    // Sisipkan pengguna baru
    $id = Flight::db()->insert('users', [
        'name' => 'Bob',
        'email' => 'bob@example.com'
    ]);

    // Sisipkan massal pengguna
    Flight::db()->insert('users', [
        ['name' => 'Bob', 'email' => 'bob@example.com'],
        ['name' => 'Jane', 'email' => 'jane@example.com']
    ]);

    // Perbarui pengguna
    $affected = Flight::db()->update('users', ['name' => 'Bob'], 'id = ?', [123]);

    // Hapus pengguna
    $deleted = Flight::db()->delete('users', 'id = ?', [123]);

    // Gunakan transaksi
    $result = Flight::db()->transaction(function($db) {
        $db->insert('users', ['name' => 'John', 'email' => 'john@example.com']);
        $db->insert('audit_log', ['action' => 'user_created']);
        return $db->lastInsertId();
    });
});
```

## Migrasi dari PdoWrapper

Jika Anda saat ini menggunakan `PdoWrapper`, migrasi ke `SimplePdo` adalah hal yang sederhana:

1. **Perbarui pendaftaran Anda:**
   ```php
   // Lama
   Flight::register('db', \flight\database\PdoWrapper::class, [ /* ... */ ]);
   
   // Baru
   Flight::register('db', \flight\database\SimplePdo::class, [ /* ... */ ]);
   ```

2. **Semua metode `PdoWrapper` yang ada bekerja di `SimplePdo`** - Tidak ada perubahan yang merusak. Kode Anda yang ada akan terus bekerja.

3. **Secara opsional gunakan metode pembantu baru** - Mulai gunakan `insert()`, `update()`, `delete()`, dan `transaction()` untuk menyederhanakan kode Anda.

## Lihat Juga

- [Collections](/learn/collections) - Pelajari cara menggunakan kelas Collection untuk akses data yang mudah.
- [PdoWrapper](/learn/pdo-wrapper) - Kelas pembantu PDO lama (deprecated).

## Pemecahan Masalah

- Jika Anda mendapatkan kesalahan tentang koneksi database, periksa DSN, nama pengguna, kata sandi, dan opsi Anda.
- Semua baris dikembalikan sebagai Collections—jika Anda membutuhkan array biasa, gunakan `$collection->getData()`.
- Untuk query `IN (?)`, pastikan untuk meneruskan array.
- Jika Anda mengalami masalah memori dengan pencatatan query di proses yang berjalan lama, sesuaikan opsi `maxQueryMetrics`.

## Changelog

- v3.18.0 - Rilis awal SimplePdo dengan metode pembantu untuk insert, update, delete, dan transaksi.