# Flight Active Record

Active Record adalah pemetaan entitas basis data ke objek PHP. Secara sederhana, jika Anda memiliki tabel `users` di basis data, Anda dapat "menerjemahkan" sebuah baris dalam tabel tersebut ke kelas `User` dan objek `$user` dalam kode Anda. Lihat [contoh dasar](#basic-example).

Klik [di sini](https://github.com/flightphp/active-record) untuk repositori di GitHub.

## Contoh Dasar

Anggaplah Anda memiliki tabel berikut:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Sekarang Anda dapat membuat kelas baru untuk mewakili tabel ini:

```php
/**
 * Kelas ActiveRecord biasanya berbentuk tunggal
 * 
 * Sangat disarankan untuk menambahkan properti tabel sebagai komentar di sini
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// Anda dapat mengaturnya dengan cara ini
		parent::__construct($database_connection, 'users');
		// atau cara ini
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Sekarang lihat keajaiban terjadi!

```php
// untuk sqlite
$database_connection = new PDO('sqlite:test.db'); // ini hanya contoh, biasanya Anda menggunakan koneksi basis data asli

// untuk mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// atau mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// atau mysqli dengan pembuatan berbasis non-objek
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('some cool password');
$user->insert();
// atau $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('some cool password again!!!');
$user->insert();
// tidak bisa menggunakan $user->save() di sini atau akan dianggap sebagai pembaruan!

echo $user->id; // 2
```

Dan semudah itu untuk menambahkan pengguna baru! Sekarang setelah ada baris pengguna di basis data, bagaimana cara mengambilnya?

```php
$user->find(1); // cari id = 1 di basis data dan kembalikan.
echo $user->name; // 'Bobby Tables'
```

Dan bagaimana jika Anda ingin mencari semua pengguna?

```php
$users = $user->findAll();
```

Bagaimana dengan kondisi tertentu?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Lihat betapa menyenangkannya ini? Mari kita instal dan mulai!

## Instalasi

Cukup instal dengan Composer

```php
composer require flightphp/active-record 
```

## Penggunaan

Ini dapat digunakan sebagai library mandiri atau dengan Flight PHP Framework. Terserah Anda sepenuhnya.

### Mandiri
Pastikan Anda memberikan koneksi PDO ke konstruktor.

```php
$pdo_connection = new PDO('sqlite:test.db'); // ini hanya contoh, biasanya Anda menggunakan koneksi basis data asli

$User = new User($pdo_connection);
```

> Tidak ingin selalu mengatur koneksi basis data di konstruktor? Lihat [Manajemen Koneksi Basis Data](#database-connection-management) untuk ide lainnya!

### Mendaftarkan sebagai metode di Flight
Jika Anda menggunakan Flight PHP Framework, Anda dapat mendaftarkan kelas ActiveRecord sebagai layanan, tetapi sejujurnya Anda tidak perlu melakukannya.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// kemudian Anda dapat menggunakannya seperti ini di controller, fungsi, dll.

Flight::user()->find(1);
```

## Metode `runway`

[runway](/awesome-plugins/runway) adalah alat CLI untuk Flight yang memiliki perintah khusus untuk library ini.

```bash
# Penggunaan
php runway make:record database_table_name [class_name]

# Contoh
php runway make:record users
```

Ini akan membuat kelas baru di direktori `app/records/` sebagai `UserRecord.php` dengan konten berikut:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Kelas ActiveRecord untuk tabel users.
 * @link https://docs.flightphp.com/en/v3/awesome-plugins/active-record
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property string $created_dt
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations Tetapkan relasi untuk model
     *   https://docs.flightphp.com/en/v3/awesome-plugins/active-record#relationships
     */
    protected array $relations = [
		// 'relation_name' => [ self::HAS_MANY, 'RelatedClass', 'foreign_key' ],
	];

    /**
     * Konstruktor
     * @param mixed $databaseConnection Koneksi ke basis data
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

## Fungsi CRUD

#### `find($id = null) : boolean|ActiveRecord`

Temukan satu record dan tetapkan ke objek saat ini. Jika Anda memberikan `$id` dengan nilai tertentu, maka akan melakukan pencarian pada kunci utama dengan nilai tersebut. Jika tidak ada yang diberikan, maka hanya akan mencari record pertama dalam tabel.

Selain itu, Anda dapat memberikan metode pembantu lainnya untuk membuat kueri ke tabel Anda.

```php
// temukan record dengan beberapa kondisi sebelumnya
$user->notNull('password')->orderBy('id DESC')->find();

// temukan record berdasarkan id tertentu
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Menemukan semua record dalam tabel yang Anda tentukan.

```php
$user->findAll();
```

#### `first(): ActiveRecord` (v0.8.0)

Menemukan record pertama yang cocok dengan kondisi Anda. Jika Anda belum mengatur pengurutan, maka akan diurutkan berdasarkan kunci utama secara ascending. Jika tidak ada yang cocok, Anda mendapatkan record kembali tanpa hidrasi, jadi periksa `isHydrated()` jika Anda tidak yakin ada data yang kembali.

```php
$user->eq('status', 'active')->first();
```

#### `last(): ActiveRecord` (v0.8.0)

Sama seperti `first()` tetapi diurutkan berdasarkan kunci utama secara descending. Berguna untuk kueri "berikan saya yang terbaru".

```php
$user->eq('status', 'active')->last();
```

#### `count(): int` (v0.8.0)

Menghitung baris yang cocok dengan kondisi Anda saat ini. Jika Anda memiliki `groupBy()` dalam kueri, `count()` sengaja mengabaikannya. Satu hitungan skalar tidak dapat mewakili satu baris per grup.

```php
$user->count();
$user->eq('status', 'active')->count();
```

#### `exists(): bool` (v0.8.0)

Mengembalikan `true` jika ada record yang cocok dengan kondisi Anda. Di dalamnya menjalankan `SELECT 1 ... LIMIT 1` yang murah.

```php
$user->eq('name', 'Bobby')->exists(); // true
```

#### `pluck(string $column): array` (v0.8.0)

Mengembalikan array datar berisi nilai dari satu kolom, bukan menghidrasi banyak objek. Gabungkan dengan `distinct()` untuk mendapatkan nilai unik.

```php
$user->pluck('name'); // [ 'Bobby', 'Joseph' ]
$user->distinct()->pluck('status'); // [ 'active', 'inactive' ]
```

#### `ids(): array` (v0.8.0)

Jalan pintas untuk `pluck()` pada kunci utama.

```php
$user->gt('id', 0)->ids(); // [ 1, 2, 3 ]
```

#### `isHydrated(): boolean` (v0.4.0)

Mengembalikan `true` jika record saat ini telah dihidrasi (diambil dari basis data).

```php
$user->find(1);
// jika record ditemukan dengan data...
$user->isHydrated(); // true
```

#### `insert(): boolean|ActiveRecord`

Menyisipkan record saat ini ke dalam basis data.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

##### Kunci Utama Berbasis Teks

Jika Anda memiliki kunci utama berbasis teks (seperti UUID), Anda dapat mengatur nilai kunci utama sebelum menyisipkan dengan salah satu dari dua cara.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // atau $user->save();
```

atau Anda dapat membuat kunci utama dihasilkan secara otomatis melalui event.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// Anda juga dapat mengatur primaryKey dengan cara ini alih-alih array di atas.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // atau sesuai kebutuhan Anda untuk menghasilkan id unik
	}
}
```

Jika Anda tidak mengatur kunci utama sebelum menyisipkan, maka akan diatur ke `rowid` dan basis data akan menghasilkannya untuk Anda, tetapi tidak akan bertahan karena bidang tersebut mungkin tidak ada dalam tabel Anda. Inilah sebabnya disarankan menggunakan event untuk menangani ini secara otomatis untuk Anda.

#### `update(): boolean|ActiveRecord`

Memperbarui record saat ini ke dalam basis data.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `updateAttribute(string $name, mixed $value): ActiveRecord` (v0.8.0)

Memperbarui satu kolom pada record yang sudah dimuat dan menyimpannya. Ini adalah jalan pintas untuk `$user->dirty([ 'name' => $value ])->update()`. Anda memerlukan record yang sudah dimuat untuk ini.

```php
$user->find(1);
$user->updateAttribute('name', 'New Name');
```

#### `save(): boolean|ActiveRecord`

Menyisipkan atau memperbarui record saat ini ke dalam basis data. Jika record memiliki id, maka akan memperbarui, jika tidak maka akan menyisipkan.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Catatan:** Jika Anda memiliki relasi yang didefinisikan di kelas, relasi tersebut juga akan disimpan secara rekursif jika telah didefinisikan, dibuat, dan memiliki data dirty untuk diperbarui. (v0.4.0 dan seterusnya)

#### `delete(): boolean`

Menghapus record saat ini dari basis data.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Anda juga dapat menghapus beberapa record dengan melakukan pencarian sebelumnya.

```php
$user->like('name', 'Bob%')->delete();
```

#### `updateAll(array $attributes, bool $allowEmptyConditions = false): int` (v0.8.0)

Memperbarui semua record yang cocok dengan kondisi Anda dalam satu pernyataan. Tidak ada record yang dihidrasi dan tidak ada event yang dijalankan, itulah mengapa ini cepat. Mengembalikan jumlah baris yang terpengaruh.

Ini menolak untuk berjalan tanpa kondisi WHERE kecuali Anda memberikan `true` untuk argumen kedua. Diri Anda di masa depan berterima kasih.

```php
$user->eq('status', 'inactive')->updateAll([ 'status' => 'active' ]);

// ya, Anda benar-benar ingin memperbarui setiap baris dalam tabel
$user->updateAll([ 'status' => 'active' ], true);
```

#### `deleteAll(bool $allowEmptyConditions = false): int` (v0.8.0)

Menghapus semua record yang cocok dengan kondisi Anda dalam satu pernyataan. Sama seperti `updateAll()`: tanpa hidrasi, tanpa event, dan membutuhkan kondisi WHERE kecuali Anda memberikan `true`. Mengembalikan jumlah baris yang dihapus. Gunakan dengan hati-hati!

```php
$user->eq('status', 'deleted')->deleteAll();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Data dirty merujuk pada data yang telah diubah dalam sebuah record.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// tidak ada yang "dirty" pada titik ini.

$user->email = 'test@example.com'; // sekarang email dianggap "dirty" karena telah diubah.
$user->update();
// sekarang tidak ada data yang dirty karena telah diperbarui dan disimpan di basis data

$user->password = password_hash()'newpassword'); // sekarang ini dirty
$user->dirty(); // memberikan tanpa argumen akan menghapus semua entri dirty.
$user->update(); // tidak ada yang diperbarui karena tidak ada yang ditangkap sebagai dirty.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // name dan password sama-sama diperbarui.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Ini adalah alias untuk metode `dirty()`. Sedikit lebih jelas apa yang Anda lakukan.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // name dan password sama-sama diperbarui.
```

#### `isDirty(): boolean` (v0.4.0)

Mengembalikan `true` jika record saat ini telah diubah.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Mengatur ulang record saat ini ke keadaan awalnya. Ini sangat bagus untuk digunakan dalam perilaku seperti perulangan. Jika Anda memberikan `true`, maka juga akan mengatur ulang data kueri yang digunakan untuk menemukan objek saat ini (perilaku default).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // mulai dari awal yang bersih
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Setelah Anda menjalankan metode `find()`, `findAll()`, `insert()`, `update()`, atau `save()`, Anda dapat memperoleh SQL yang telah dibuat dan menggunakannya untuk tujuan debugging.

## Transaksi

Perlu menjalankan beberapa penulisan yang semuanya harus berhasil bersama? Bungkus dengan `transaction()` (v0.8.0). Berikan callable dan record akan masuk sebagai argumen. Jika callable tersebut melempar, semuanya akan di-rollback dan exception akan dilempar ulang untuk Anda. Jika tidak, maka akan commit dan mengembalikan apa pun yang dikembalikan oleh callable Anda.

```php
$user->transaction(function ($user) {
	$user->name = 'Bobby Tables';
	$user->password = password_hash('correct horse battery staple');
	$user->insert();

	$user->email = 'bobby@example.com';
	$user->update();
	// commit terjadi di sini jika tidak ada yang dilempar
});
```

Transaksi bersarang tidak didukung (tanpa savepoint), jadi tetap buat transaksi tetap datar.

## Metode Kueri SQL
#### `select(string $field1 [, string $field2 ... ])`

Anda dapat memilih hanya beberapa kolom dalam tabel jika diinginkan (ini lebih performa untuk tabel yang sangat lebar dengan banyak kolom)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Anda juga secara teknis dapat memilih tabel lain! Mengapa tidak?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Anda bahkan dapat join ke tabel lain dalam basis data.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Anda dapat mengatur beberapa argumen where khusus (Anda tidak dapat mengatur parameter dalam pernyataan where ini)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Catatan Keamanan** - Anda mungkin tergoda untuk melakukan sesuatu seperti `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. HARAP JANGAN LAKUKAN INI!!! Ini rentan terhadap apa yang disebut serangan SQL Injection. Ada banyak artikel online, silakan Google "sql injection attacks php" dan Anda akan menemukan banyak artikel tentang topik ini. Cara yang benar untuk menangani ini dengan library ini adalah alih-alih menggunakan metode `where()`, Anda harus melakukan sesuatu seperti `$user->eq('id', $id)->eq('name', $name)->find();`. Jika Anda benar-benar harus melakukannya, library `PDO` memiliki `$pdo->quote($var)` untuk meng-escape untuk Anda. Hanya setelah Anda menggunakan `quote()`, Anda dapat menggunakannya dalam pernyataan `where()`.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Kelompokkan hasil Anda berdasarkan kondisi tertentu.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Urutkan kueri yang dikembalikan dengan cara tertentu.

```php
$user->orderBy('name DESC')->find();
```

#### `orderByColumn(string $column, string $direction = 'ASC')` (v0.7.2)

`order()` dan `orderBy()` menerima fragmen SQL mentah, yang tidak masalah jika Anda menulis `'name DESC'` secara hardcode. Jika nama kolom berasal dari input pengguna (misalnya header tabel yang dapat diurutkan), gunakan `orderByColumn()` sebagai gantinya. Hanya nama kolom biasa dan jalur `table.column` yang diizinkan, dan arah harus `ASC` atau `DESC`, jadi tidak ada yang bisa disuntikkan.

```php
// $sortColumn berasal dari request
$user->orderByColumn($sortColumn, 'DESC')->findAll();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Batasi jumlah record yang dikembalikan. Jika diberikan int kedua, maka akan menjadi offset, limit seperti di SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

#### `distinct()` (v0.8.0)

Menambahkan `DISTINCT` ke kueri berikutnya. Ini berfungsi pada select normal dan dengan `pluck()`. `count()` mengabaikannya, karena menempatkan `DISTINCT` pada satu baris agregat tidak melakukan apa-apa.

```php
$user->distinct()->pluck('status'); // [ 'active', 'inactive' ]
```

## Kondisi WHERE
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

Dimana `field = $value`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

Dimana `field <> $value`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

Dimana `field IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

Dimana `field IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

Dimana `field > $value`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

Dimana `field < $value`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

Dimana `field >= $value`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

Dimana `field <= $value`

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

Dimana `field LIKE $value` atau `field NOT LIKE $value`

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

Dimana `field IN($value)` atau `field NOT IN($value)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Dimana `field BETWEEN $value AND $value1`

```php
$user->between('id', [1, 2])->find();
```

### Kondisi OR

Anda dapat membungkus kondisi Anda dalam pernyataan OR. Ini dilakukan dengan metode `startWrap()` dan `endWrap()` atau dengan mengisi parameter ke-3 dari kondisi setelah field dan value.

```php
// Metode 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// Ini akan dievaluasi menjadi `id = 1 AND (name = 'demo' OR name = 'test')`

// Metode 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// Ini akan dievaluasi menjadi `id = 1 OR name = 'demo'`
```

## Scopes

Scopes (v0.8.0) adalah rantai kueri yang dapat digunakan kembali, didefinisikan sebagai metode instance biasa di kelas Anda yang mengembalikan `$this`. Setelah Anda menulisnya, ia dapat dirantai seperti metode kueri lainnya.

```php
class User extends flight\ActiveRecord {

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	public function active(): self
	{
		return $this->eq('status', 'active');
	}

	public function recent(int $days = 7): self
	{
		return $this->ge('created_at', date('Y-m-d', strtotime("-{$days} days")));
	}
}

// dan sekarang kueri Anda dibaca seperti kalimat
(new User($pdo_connection))->active()->recent(30)->findAll();
```

Anda juga dapat memanggil scope berdasarkan nama dengan `scope()`, yang berguna ketika nama scope berasal dari tempat lain dalam kode Anda. Ini melempar `BadMethodCallException` jika metode tidak ada.

```php
$user->scope('active')->findAll();
$user->scope('recent', 30)->findAll();
```

## Relasi
Anda dapat mengatur beberapa jenis relasi menggunakan library ini. Anda dapat mengatur relasi satu->banyak dan satu->satu antar tabel. Ini membutuhkan sedikit pengaturan tambahan di kelas sebelumnya.

Mengatur array `$relations` tidak sulit, tetapi menebak sintaks yang benar bisa membingungkan.

```php
protected array $relations = [
	// Anda dapat memberi nama kunci apa pun yang Anda suka. Nama ActiveRecord mungkin bagus. Contoh: user, contact, client
	'user' => [
		// wajib
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // ini adalah jenis relasi

		// wajib
		'Some_Class', // ini adalah kelas ActiveRecord "lain" yang akan dirujuk

		// wajib
		// tergantung pada jenis relasi
		// self::HAS_ONE = kunci asing yang merujuk ke join
		// self::HAS_MANY = kunci asing yang merujuk ke join
		// self::BELONGS_TO = kunci lokal yang merujuk ke join
		'local_or_foreign_key',
		// sekadar informasi, ini juga hanya join ke kunci utama model "lain"

		// opsional
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // kondisi tambahan yang Anda inginkan saat join relasi
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// opsional
		'back_reference_name' // ini jika Anda ingin melakukan back reference relasi ini ke dirinya sendiri Contoh: $user->contact->user;
	]
];
```

```php
class User extends ActiveRecord{
	protected array $relations = [
		'contacts' => [ self::HAS_MANY, Contact::class, 'user_id' ],
		'contact' => [ self::HAS_ONE, Contact::class, 'user_id' ],
	];

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}
}

class Contact extends ActiveRecord{
	protected array $relations = [
		'user' => [ self::BELONGS_TO, User::class, 'user_id' ],
		'user_with_backref' => [ self::BELONGS_TO, User::class, 'user_id', [], 'contact' ],
	];
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'contacts');
	}
}
```

Sekarang kita telah memiliki referensi yang diatur sehingga kita dapat menggunakannya dengan sangat mudah!

```php
$user = new User($pdo_connection);

// temukan pengguna terbaru.
$user->notNull('id')->orderBy('id desc')->find();

// ambil kontak menggunakan relasi:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// atau kita bisa pergi ke arah sebaliknya.
$contact = new Contact();

// temukan satu kontak
$contact->find();

// ambil pengguna menggunakan relasi:
echo $contact->user->name; // ini adalah nama pengguna
```

Cukup keren bukan?

### Eager Loading

#### Ringkasan
Eager loading memecahkan masalah kueri N+1 dengan memuat relasi lebih awal. Alih-alih menjalankan kueri terpisah untuk relasi setiap record, eager loading mengambil semua data terkait hanya dalam satu kueri tambahan per relasi.

> **Catatan:** Eager loading hanya tersedia untuk v0.7.0 dan seterusnya.

#### Penggunaan Dasar
Gunakan metode `with()` untuk menentukan relasi mana yang akan dimuat secara eager:
```php
// Muat pengguna beserta kontak mereka dalam 2 kueri, bukan N+1
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    foreach ($u->contacts as $contact) {
        echo $contact->email; // Tidak ada kueri tambahan!
    }
}
```

#### Beberapa Relasi
Muat beberapa relasi sekaligus:
```php
$users = $user->with(['contacts', 'profile', 'settings'])->findAll();
```

#### Tipe Relasi

##### HAS_MANY
```php
// Muat semua kontak untuk setiap pengguna
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    // $u->contacts sudah dimuat sebagai array
    foreach ($u->contacts as $contact) {
        echo $contact->email;
    }
}
```
##### HAS_ONE
```php
// Muat satu kontak untuk setiap pengguna
$users = $user->with('contact')->findAll();
foreach ($users as $u) {
    // $u->contact sudah dimuat sebagai objek
    echo $u->contact->email;
}
```

##### BELONGS_TO
```php
// Muat pengguna induk untuk semua kontak
$contacts = $contact->with('user')->findAll();
foreach ($contacts as $c) {
    // $c->user sudah dimuat
    echo $c->user->name;
}
```
##### Dengan find()
Eager loading berfungsi dengan `findAll()` dan `find()`:

```php
$user = $user->with('contacts')->find(1);
// Pengguna dan semua kontak mereka dimuat dalam 2 kueri
```
#### Manfaat Performa
Tanpa eager loading (masalah N+1):
```php
$users = $user->findAll(); // 1 kueri
foreach ($users as $u) {
    $contacts = $u->contacts; // N kueri (satu per pengguna!)
}
// Total: 1 + N kueri
```

Dengan eager loading:

```php
$users = $user->with('contacts')->findAll(); // total 2 kueri
foreach ($users as $u) {
    $contacts = $u->contacts; // 0 kueri tambahan!
}
// Total: 2 kueri (1 untuk pengguna + 1 untuk semua kontak)
```
Untuk 10 pengguna, ini mengurangi kueri dari 11 menjadi 2 - pengurangan 82%!

#### Catatan Penting
- Eager loading sepenuhnya opsional - lazy loading tetap berfungsi seperti sebelumnya
- Relasi yang sudah dimuat otomatis dilewati
- Back reference berfungsi dengan eager loading
- Callback relasi dihormati selama eager loading

#### Keterbatasan
- Eager loading bersarang (misalnya `with(['contacts.addresses'])`) saat ini tidak didukung
- Batasan eager load melalui closure tidak didukung di versi ini

## Mengatur Data Kustom
Terkadang Anda mungkin perlu melampirkan sesuatu yang unik ke ActiveRecord Anda, misalnya perhitungan kustom yang mungkin lebih mudah dilampirkan ke objek yang nantinya akan diteruskan ke template.

#### `setCustomData(string $field, mixed $value)`
Anda melampirkan data kustom dengan metode `setCustomData()`.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

Dan kemudian Anda cukup merujuknya seperti properti objek biasa.

```php
echo $user->page_view_count;
```

## Timestamps

Jika tabel Anda memiliki kolom `created_at` dan `updated_at`, Anda dapat meminta library untuk mengisinya secara otomatis (v0.8.0). Atur `protected bool $timestamps = true;` di kelas Anda dan itu akan mengisi `created_at` dan `updated_at` saat Anda insert, dan `updated_at` saat Anda update. Formatnya adalah `Y-m-d H:i:s`. Jika Anda mengatur salah satu kolom tersebut sendiri, library akan membiarkan nilai Anda tetap apa adanya.

```php
class User extends flight\ActiveRecord {
	protected bool $timestamps = true;

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}
}
```

Tabel Anda memang membutuhkan kolom-kolom tersebut, jika tidak, insert dan update akan gagal.

## Event

Satu lagi fitur yang sangat keren dari library ini adalah event. Event dipicu pada waktu tertentu berdasarkan metode tertentu yang Anda panggil. Event sangat sangat membantu dalam mengatur data secara otomatis untuk Anda.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Ini sangat membantu jika Anda perlu mengatur koneksi default atau semacamnya.

```php
// index.php atau bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // jangan lupa referensi &
		// Anda dapat melakukan ini untuk mengatur koneksi secara otomatis
		$config['connection'] = Flight::db();
		// atau ini
		$self->transformAndPersistConnection(Flight::db());
		
		// Anda juga dapat mengatur nama tabel dengan cara ini.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Ini kemungkinan hanya berguna jika Anda perlu memanipulasi kueri setiap saat.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// selalu jalankan id >= 0 jika itu yang Anda suka
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Yang ini kemungkinan lebih berguna jika Anda selalu perlu menjalankan beberapa logika setiap kali record ini diambil. Apakah Anda perlu mendekripsi sesuatu? Apakah Anda perlu menjalankan kueri hitung kustom setiap saat (tidak performa, tapi terserah)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// mendekripsi sesuatu
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// mungkin menyimpan sesuatu yang kustom seperti kueri???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Ini kemungkinan hanya berguna jika Anda perlu memanipulasi kueri setiap saat.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// selalu jalankan id >= 0 jika itu yang Anda suka
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Mirip dengan `afterFind()` tetapi Anda melakukannya untuk semua record!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// lakukan sesuatu yang keren seperti afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Sangat membantu jika Anda perlu mengatur beberapa nilai default setiap saat.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// atur beberapa default yang baik
		if(!$self->created_date) {
			$self->created_date = gmdate('Y-m-d');
		}

		if(!$self->password) {
			$self->password = password_hash((string) microtime(true));
		}
	} 
}
```

#### `afterInsert(ActiveRecord $ActiveRecord)`

Mungkin Anda memiliki kasus penggunaan untuk mengubah data setelah disisipkan?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// Anda lakukan sesuka Anda
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// atau apa pun....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Sangat membantu jika Anda perlu mengatur beberapa nilai default setiap kali melakukan pembaruan.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// atur beberapa default yang baik
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Mungkin Anda memiliki kasus penggunaan untuk mengubah data setelah diperbarui?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// Anda lakukan sesuka Anda
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// atau apa pun....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Ini berguna jika Anda ingin event terjadi baik saat insert maupun update. Saya akan menghemat penjelasan panjang, tapi saya yakin Anda bisa menebaknya.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeSave(self $self) {
		$self->last_updated = gmdate('Y-m-d H:i:s');
	} 
}
```

#### `beforeDelete(ActiveRecord $ActiveRecord)/afterDelete(ActiveRecord $ActiveRecord)`

Tidak yakin apa yang ingin Anda lakukan di sini, tapi tidak ada penilaian! Silakan!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'Dia adalah prajurit yang pemberani... :cry-face:';
	} 
}
```

## Manajemen Koneksi Basis Data

Saat Anda menggunakan library ini, Anda dapat mengatur koneksi basis data dalam beberapa cara berbeda. Anda dapat mengatur koneksi di konstruktor, mengaturnya melalui variabel konfigurasi `$config['connection']`, atau mengaturnya melalui `setDatabaseConnection()` (v0.4.1).

```php
$pdo_connection = new PDO('sqlite:test.db'); // misalnya
$user = new User($pdo_connection);
// atau
$user = new User(null, [ 'connection' => $pdo_connection ]);
// atau
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Jika Anda ingin menghindari selalu mengatur `$database_connection` setiap kali memanggil active record, ada cara untuk mengakalinya!

```php
// index.php atau bootstrap.php
// Atur ini sebagai kelas terdaftar di Flight
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

// User.php
class User extends flight\ActiveRecord {
	
	public function __construct(array $config = [])
	{
		$database_connection = $config['connection'] ?? Flight::db();
		parent::__construct($database_connection, 'users', $config);
	}
}

// Dan sekarang, tanpa argumen diperlukan!
$user = new User();
```

> **Catatan:** Jika Anda berencana melakukan unit testing, melakukan dengan cara ini dapat menambah beberapa tantangan untuk unit testing, tetapi secara keseluruhan karena Anda dapat menyuntikkan koneksi Anda dengan `setDatabaseConnection()` atau `$config['connection']`, ini tidak terlalu buruk.

Jika Anda perlu menyegarkan koneksi basis data, misalnya jika Anda menjalankan skrip CLI yang berjalan lama dan perlu menyegarkan koneksi sesekali, Anda dapat mengatur ulang koneksi dengan `$your_record->setDatabaseConnection($pdo_connection)`.

## Kontribusi

Silakan berkontribusi. :D

### Setup

Saat Anda berkontribusi, pastikan Anda menjalankan `composer test-coverage` untuk mempertahankan cakupan tes 100% (ini bukan cakupan unit test sebenarnya, lebih seperti pengujian integrasi).

Juga pastikan Anda menjalankan `composer beautify` dan `composer phpcs` untuk memperbaiki kesalahan lint.

## Lisensi

MIT