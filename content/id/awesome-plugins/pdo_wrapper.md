# Kelas Pembantu PDO PdoWrapper

Flight hadir dengan kelas pembantu untuk PDO. Kelas ini memungkinkan Anda untuk dengan mudah melakukan kueri ke basis data Anda dengan semua persiapan/eksekusi/fetchAll() yang membingungkan. Ini sangat menyederhanakan cara Anda dapat melakukan kueri ke basis data Anda. Setiap hasil baris dikembalikan sebagai kelas Koleksi Flight yang memungkinkan Anda mengakses data Anda melalui sintaks array atau sintaks objek.

## Mendaftar Kelas Pembantu PDO

```php
// Daftarkan kelas pembantu PDO
Flight::register('db', \flight\database\PdoWrapper::class, ['mysql:host=localhost;dbname=cool_db_name', 'user', 'pass', [
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\'',
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_STRINGIFY_FETCHES => false,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
	]
]);
```

## Penggunaan
Objek ini memperluas PDO sehingga semua metode PDO normal tersedia. Metode berikut ditambahkan untuk mempermudah kueri ke basis data:

### `runQuery(string $sql, array $params = []): PDOStatement`
Gunakan ini untuk INSERTS, UPDATES, atau jika Anda berencana menggunakan SELECT dalam loop while

```php
$db = Flight::db();
$statement = $db->runQuery("SELECT * FROM table WHERE something = ?", [ $something ]);
while($row = $statement->fetch()) {
	// ...
}

// Atau menulis ke basis data
$db->runQuery("INSERT INTO table (name) VALUES (?)", [ $name ]);
$db->runQuery("UPDATE table SET name = ? WHERE id = ?", [ $name, $id ]);
```

### `fetchField(string $sql, array $params = []): mixed`
Mengambil field pertama dari kueri

```php
$db = Flight::db();
$count = $db->fetchField("SELECT COUNT(*) FROM table WHERE something = ?", [ $something ]);
```

### `fetchRow(string $sql, array $params = []): array`
Mengambil satu baris dari kueri

```php
$db = Flight::db();
$row = $db->fetchRow("SELECT id, name FROM table WHERE id = ?", [ $id ]);
echo $row['name'];
// atau
echo $row->name;
```

### `fetchAll(string $sql, array $params = []): array`
Mengambil semua baris dari kueri

```php
$db = Flight::db();
$rows = $db->fetchAll("SELECT id, name FROM table WHERE something = ?", [ $something ]);
foreach($rows as $row) {
	echo $row['name'];
	// atau
	echo $row->name;
}
```

## Catatan dengan sintaks `IN()`
Ini juga memiliki pembungkus yang berguna untuk pernyataan `IN()`. Anda cukup memasukkan tanda tanya tunggal sebagai placeholder untuk `IN()` dan kemudian array nilai. Berikut adalah contoh seperti apa itu:

```php
$db = Flight::db();
$name = 'Bob';
$company_ids = [1,2,3,4,5];
$rows = $db->fetchAll("SELECT id, name FROM table WHERE name = ? AND company_id IN (?)", [ $name, $company_ids ]);
```

## Contoh Lengkap

```php
// Contoh rute dan cara Anda akan menggunakan pembungkus ini
Flight::route('/users', function () {
	// Dapatkan semua pengguna
	$users = Flight::db()->fetchAll('SELECT * FROM users');

	// Streaming semua pengguna
	$statement = Flight::db()->runQuery('SELECT * FROM users');
	while ($user = $statement->fetch()) {
		echo $user['name'];
		// atau echo $user->name;
	}

	// Dapatkan satu pengguna
	$user = Flight::db()->fetchRow('SELECT * FROM users WHERE id = ?', [123]);

	// Dapatkan satu nilai
	$count = Flight::db()->fetchField('SELECT COUNT(*) FROM users');

	// Sintaks IN() khusus untuk membantu (pastikan IN ditulis dengan huruf kapital)
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [[1,2,3,4,5]]);
	// Anda juga dapat melakukan ini
	$users = Flight::db()->fetchAll('SELECT * FROM users WHERE id IN (?)', [ '1,2,3,4,5']);

	// Memasukkan pengguna baru
	Flight::db()->runQuery("INSERT INTO users (name, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
	$insert_id = Flight::db()->lastInsertId();

	// Memperbarui pengguna
	Flight::db()->runQuery("UPDATE users SET name = ? WHERE id = ?", ['Bob', 123]);

	// Menghapus pengguna
	Flight::db()->runQuery("DELETE FROM users WHERE id = ?", [123]);

	// Dapatkan jumlah baris yang terpengaruh
	$statement = Flight::db()->runQuery("UPDATE users SET name = ? WHERE name = ?", ['Bob', 'Sally']);
	$affected_rows = $statement->rowCount();

});
```