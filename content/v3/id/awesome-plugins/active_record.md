# Flight Active Record 

Active record adalah pemetaan entitas basis data ke objek PHP. Secara sederhana, jika Anda memiliki tabel users di basis data Anda, Anda dapat "menerjemahkan" baris dalam tabel tersebut ke kelas `User` dan objek `$user` di kode Anda. Lihat [contoh dasar](#basic-example).

Klik [di sini](https://github.com/flightphp/active-record) untuk repositori di GitHub.

## Contoh Dasar

Misalkan Anda memiliki tabel berikut:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Sekarang Anda dapat membuat kelas baru untuk merepresentasikan tabel ini:

```php
/**
 * Kelas ActiveRecord biasanya bersifat tunggal
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
		// atau dengan cara ini
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Sekarang saksikan keajaiban itu terjadi!

```php
// untuk sqlite
$database_connection = new PDO('sqlite:test.db'); // ini hanya contoh, Anda mungkin menggunakan koneksi basis data nyata

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
// tidak bisa menggunakan $user->save() di sini atau itu akan mengira itu pembaruan!

echo $user->id; // 2
```

Dan itu semudah itu untuk menambahkan pengguna baru! Sekarang ada baris pengguna di basis data, bagaimana Anda mengambilnya?

```php
$user->find(1); // cari id = 1 di basis data dan kembalikan itu.
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

Lihat betapa menyenangkannya ini? Mari instal dan mulai!

## Instalasi

Cukup instal dengan Composer

```php
composer require flightphp/active-record 
```

## Penggunaan

Ini dapat digunakan sebagai pustaka mandiri atau dengan Flight PHP Framework. Sepenuhnya terserah Anda.

### Mandiri
Pastikan Anda mengirimkan koneksi PDO ke konstruktor.

```php
$pdo_connection = new PDO('sqlite:test.db'); // ini hanya contoh, Anda mungkin menggunakan koneksi basis data nyata

$User = new User($pdo_connection);
```

> Tidak ingin selalu mengatur koneksi basis data di konstruktor? Lihat [Manajemen Koneksi Basis Data](#database-connection-management) untuk ide-ide lain!

### Daftarkan sebagai metode di Flight
Jika Anda menggunakan Flight PHP Framework, Anda dapat mendaftarkan kelas ActiveRecord sebagai layanan, tapi sebenarnya tidak harus.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// kemudian Anda dapat menggunakannya seperti ini di controller, fungsi, dll.

Flight::user()->find(1);
```

## Metode `runway`

[runway](/awesome-plugins/runway) adalah alat CLI untuk Flight yang memiliki perintah khusus untuk pustaka ini. 

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
 * @link https://docs.flightphp.com/awesome-plugins/active-record
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
     * @var array $relations Atur hubungan untuk model
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
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

Cari satu rekaman dan tetapkan ke objek saat ini. Jika Anda mengirimkan `$id` tertentu, itu akan melakukan pencarian pada kunci utama dengan nilai tersebut. Jika tidak ada yang dikirimkan, itu hanya akan mencari rekaman pertama di tabel.

Selain itu, Anda dapat mengirimkan metode pembantu lain untuk memquery tabel Anda.

```php
// cari rekaman dengan beberapa kondisi sebelumnya
$user->notNull('password')->orderBy('id DESC')->find();

// cari rekaman berdasarkan id tertentu
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Mencari semua rekaman di tabel yang Anda tentukan.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

Mengembalikan `true` jika rekaman saat ini telah dihidrasi (diambil dari basis data).

```php
$user->find(1);
// jika rekaman ditemukan dengan data...
$user->isHydrated(); // true
```

#### `insert(): boolean|ActiveRecord`

Memasukkan rekaman saat ini ke basis data.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

##### Kunci Utama Berbasis Teks

Jika Anda memiliki kunci utama berbasis teks (seperti UUID), Anda dapat mengatur nilai kunci utama sebelum memasukkan dengan salah satu dari dua cara.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // atau $user->save();
```

atau Anda dapat memiliki kunci utama yang dihasilkan secara otomatis untuk Anda melalui event.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// Anda juga dapat mengatur primaryKey dengan cara ini daripada array di atas.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // atau bagaimana pun Anda perlu menghasilkan id unik Anda
	}
}
```

Jika Anda tidak mengatur kunci utama sebelum memasukkan, itu akan diatur ke `rowid` dan 
basis data akan menghasilkannya untuk Anda, tapi itu tidak akan bertahan karena field tersebut mungkin tidak ada
di tabel Anda. Inilah mengapa disarankan untuk menggunakan event untuk menangani ini secara otomatis 
untuk Anda.

#### `update(): boolean|ActiveRecord`

Memperbarui rekaman saat ini ke basis data.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Memasukkan atau memperbarui rekaman saat ini ke basis data. Jika rekaman memiliki id, itu akan memperbarui, jika tidak itu akan memasukkan.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Catatan:** Jika Anda memiliki hubungan yang didefinisikan di kelas, itu akan secara rekursif menyimpan hubungan tersebut juga jika mereka telah didefinisikan, diinstansiasi dan memiliki data kotor untuk diperbarui. (v0.4.0 dan di atas)

#### `delete(): boolean`

Menghapus rekaman saat ini dari basis data.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Anda juga dapat menghapus beberapa rekaman dengan menjalankan pencarian sebelumnya.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Data kotor merujuk pada data yang telah diubah dalam rekaman.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// tidak ada yang "kotor" pada titik ini.

$user->email = 'test@example.com'; // sekarang email dianggap "kotor" karena telah diubah.
$user->update();
// sekarang tidak ada data yang kotor karena telah diperbarui dan disimpan di basis data

$user->password = password_hash()'newpassword'); // sekarang ini kotor
$user->dirty(); // mengirimkan tidak ada akan membersihkan semua entri kotor.
$user->update(); // tidak ada yang akan diperbarui karena tidak ada yang ditangkap sebagai kotor.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // baik name maupun password diperbarui.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Ini adalah alias untuk metode `dirty()`. Ini sedikit lebih jelas apa yang Anda lakukan.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // baik name maupun password diperbarui.
```

#### `isDirty(): boolean` (v0.4.0)

Mengembalikan `true` jika rekaman saat ini telah diubah.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Mengatur ulang rekaman saat ini ke keadaan awalnya. Ini sangat bagus untuk digunakan dalam perilaku tipe loop.
Jika Anda mengirimkan `true` itu juga akan mengatur ulang data query yang digunakan untuk menemukan objek saat ini (perilaku default).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // mulai dengan slate bersih
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Setelah Anda menjalankan metode `find()`, `findAll()`, `insert()`, `update()`, atau `save()` Anda dapat memperoleh SQL yang dibangun dan menggunakannya untuk tujuan debugging.

## Metode Query SQL
#### `select(string $field1 [, string $field2 ... ])`

Anda dapat memilih hanya beberapa kolom di tabel jika Anda suka (ini lebih performant pada tabel lebar yang sangat banyak kolomnya)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Anda secara teknis dapat memilih tabel lain juga! Mengapa tidak?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Anda bahkan dapat bergabung ke tabel lain di basis data.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Anda dapat mengatur beberapa argumen where kustom (Anda tidak dapat mengatur params di pernyataan where ini)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Catatan Keamanan** - Anda mungkin tergoda untuk melakukan sesuatu seperti `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Tolong JANGAN LAKUKAN INI!!! Ini rentan terhadap apa yang dikenal sebagai serangan SQL Injection. Ada banyak artikel online, silakan Google "sql injection attacks php" dan Anda akan menemukan banyak artikel tentang subjek ini. Cara yang tepat untuk menangani ini dengan pustaka ini adalah daripada metode `where()` ini, Anda akan melakukan sesuatu seperti `$user->eq('id', $id)->eq('name', $name)->find();` Jika Anda benar-benar harus melakukan ini, pustaka `PDO` memiliki `$pdo->quote($var)` untuk melarikan diri untuk Anda. Hanya setelah Anda menggunakan `quote()` Anda dapat menggunakannya dalam pernyataan `where()`.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Kelompokkan hasil Anda berdasarkan kondisi tertentu.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Urutkan query yang dikembalikan dengan cara tertentu.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Batasi jumlah rekaman yang dikembalikan. Jika int kedua diberikan, itu akan menjadi offset, limit seperti di SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## Kondisi WHERE
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

Where `field = $value`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

Where `field <> $value`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

Where `field IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

Where `field IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

Where `field > $value`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

Where `field < $value`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

Where `field >= $value`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

Where `field <= $value`

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

Where `field LIKE $value` atau `field NOT LIKE $value`

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

Where `field IN($value)` atau `field NOT IN($value)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Where `field BETWEEN $value AND $value1`

```php
$user->between('id', [1, 2])->find();
```

### Kondisi OR

Mungkin untuk membungkus kondisi Anda dalam pernyataan OR. Ini dilakukan dengan metode `startWrap()` dan `endWrap()` atau dengan mengisi parameter ke-3 dari kondisi setelah field dan value.

```php
// Metode 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// Ini akan dievaluasi ke `id = 1 AND (name = 'demo' OR name = 'test')`

// Metode 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// Ini akan dievaluasi ke `id = 1 OR name = 'demo'`
```

## Hubungan
Anda dapat mengatur beberapa jenis hubungan menggunakan pustaka ini. Anda dapat mengatur hubungan one->many dan one->one antara tabel. Ini memerlukan sedikit pengaturan tambahan di kelas sebelumnya.

Mengatur array `$relations` tidak sulit, tapi menebak sintaks yang benar bisa membingungkan.

```php
protected array $relations = [
	// Anda dapat menamai kunci apa saja yang Anda suka. Nama ActiveRecord mungkin bagus. Contoh: user, contact, client
	'user' => [
		// diperlukan
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // ini adalah jenis hubungan

		// diperlukan
		'Some_Class', // ini adalah kelas ActiveRecord "lain" yang akan dirujuk

		// diperlukan
		// tergantung pada jenis hubungan
		// self::HAS_ONE = kunci asing yang merujuk ke join
		// self::HAS_MANY = kunci asing yang merujuk ke join
		// self::BELONGS_TO = kunci lokal yang merujuk ke join
		'local_or_foreign_key',
		// hanya FYI, ini juga hanya bergabung ke kunci utama dari model "lain"

		// opsional
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // kondisi tambahan yang Anda inginkan saat bergabung hubungan
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// opsional
		'back_reference_name' // ini jika Anda ingin merujuk balik hubungan ini kembali ke dirinya sendiri Contoh: $user->contact->user;
	];
]
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

Sekarang kita telah mengatur referensi sehingga kita dapat menggunakannya dengan sangat mudah!

```php
$user = new User($pdo_connection);

// cari pengguna terbaru.
$user->notNull('id')->orderBy('id desc')->find();

// dapatkan kontak dengan menggunakan hubungan:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// atau kita bisa pergi ke arah lain.
$contact = new Contact();

// cari satu kontak
$contact->find();

// dapatkan pengguna dengan menggunakan hubungan:
echo $contact->user->name; // ini adalah nama pengguna
```

Cukup keren ya?

### Eager Loading

#### Gambaran Umum
Eager loading menyelesaikan masalah query N+1 dengan memuat hubungan di muka. Daripada menjalankan query terpisah untuk hubungan setiap rekaman, eager loading mengambil semua data terkait hanya dalam satu query tambahan per hubungan.

> **Catatan:** Eager loading hanya tersedia untuk v0.7.0 dan di atas.

#### Penggunaan Dasar
Gunakan metode `with()` untuk menentukan hubungan mana yang akan dimuat secara eager:
```php
// Muat pengguna dengan kontak mereka dalam 2 query daripada N+1
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    foreach ($u->contacts as $contact) {
        echo $contact->email; // Tidak ada query tambahan!
    }
}
```

#### Multiple Relations
Muat beberapa hubungan sekaligus:
```php
$users = $user->with(['contacts', 'profile', 'settings'])->findAll();
```

#### Jenis Hubungan

##### HAS_MANY
```php
// Eager load semua kontak untuk setiap pengguna
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
// Eager load satu kontak untuk setiap pengguna
$users = $user->with('contact')->findAll();
foreach ($users as $u) {
    // $u->contact sudah dimuat sebagai objek
    echo $u->contact->email;
}
```

##### BELONGS_TO
```php
// Eager load pengguna induk untuk semua kontak
$contacts = $contact->with('user')->findAll();
foreach ($contacts as $c) {
    // $c->user sudah dimuat
    echo $c->user->name;
}
```
##### Dengan find()
Eager loading bekerja dengan baik 
findAll()
 dan 
find()
:

```php
$user = $user->with('contacts')->find(1);
// Pengguna dan semua kontak mereka dimuat dalam 2 query
```
#### Manfaat Performa
Tanpa eager loading (masalah N+1):
```php
$users = $user->findAll(); // 1 query
foreach ($users as $u) {
    $contacts = $u->contacts; // N query (satu per pengguna!)
}
// Total: 1 + N query
```

Dengan eager loading:

```php
$users = $user->with('contacts')->findAll(); // 2 query total
foreach ($users as $u) {
    $contacts = $u->contacts; // 0 query tambahan!
}
// Total: 2 query (1 untuk pengguna + 1 untuk semua kontak)
```
Untuk 10 pengguna, ini mengurangi query dari 11 menjadi 2 - pengurangan 82%!

#### Catatan Penting
- Eager loading sepenuhnya opsional - lazy loading masih bekerja seperti sebelumnya
- Hubungan yang sudah dimuat secara otomatis dilewati
- Back references bekerja dengan eager loading
- Callback hubungan dihormati selama eager loading

#### Keterbatasan
- Eager loading bersarang (mis., 
with(['contacts.addresses'])
) saat ini tidak didukung
- Batasan eager load melalui closure tidak didukung dalam versi ini

## Mengatur Data Kustom
Kadang-kadang Anda mungkin perlu melampirkan sesuatu yang unik ke ActiveRecord Anda seperti perhitungan kustom yang mungkin lebih mudah untuk hanya melampirkannya ke objek yang kemudian diteruskan ke template.

#### `setCustomData(string $field, mixed $value)`
Anda melampirkan data kustom dengan metode `setCustomData()`.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

Dan kemudian Anda cukup merujuknya seperti properti objek normal.

```php
echo $user->page_view_count;
```

## Event

Satu fitur super hebat lagi tentang pustaka ini adalah tentang event. Event dipicu pada waktu tertentu berdasarkan metode tertentu yang Anda panggil. Mereka sangat sangat membantu dalam mengatur data untuk Anda secara otomatis.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Ini sangat membantu jika Anda perlu mengatur koneksi default atau sesuatu seperti itu.

```php
// index.php atau bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // jangan lupa referensi &
		// Anda bisa melakukan ini untuk secara otomatis mengatur koneksi
		$config['connection'] = Flight::db();
		// atau ini
		$self->transformAndPersistConnection(Flight::db());
		
		// Anda juga dapat mengatur nama tabel dengan cara ini.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Ini mungkin hanya berguna jika Anda perlu manipulasi query setiap kali.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// selalu jalankan id >= 0 jika itu gaya Anda
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Yang ini mungkin lebih berguna jika Anda selalu perlu menjalankan beberapa logika setiap kali rekaman ini diambil. Apakah Anda perlu mendekripsi sesuatu? Apakah Anda perlu menjalankan query hitung kustom setiap kali (tidak performant tapi terserah)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// mendekripsi sesuatu
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// mungkin menyimpan sesuatu kustom seperti query???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Ini mungkin hanya berguna jika Anda perlu manipulasi query setiap kali.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// selalu jalankan id >= 0 jika itu gaya Anda
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Mirip dengan `afterFind()` tapi Anda bisa melakukannya untuk semua rekaman!

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

Sangat membantu jika Anda perlu beberapa nilai default yang diatur setiap kali.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// atur beberapa default yang masuk akal
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

Mungkin Anda memiliki kasus pengguna untuk mengubah data setelah dimasukkan?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// lakukan apa yang Anda mau
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// atau apa pun....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Sangat membantu jika Anda perlu beberapa nilai default yang diatur setiap kali pada pembaruan.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// atur beberapa default yang masuk akal
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Mungkin Anda memiliki kasus pengguna untuk mengubah data setelah diperbarui?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// lakukan apa yang Anda mau
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// atau apa pun....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Ini berguna jika Anda ingin event terjadi baik saat insert atau update. Saya akan menghemat penjelasan panjang, tapi saya yakin Anda bisa menebak apa itu.

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

Tidak yakin apa yang ingin Anda lakukan di sini, tapi tidak ada penilaian di sini! Silakan!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'He was a brave soldier... :cry-face:';
	} 
}
```

## Manajemen Koneksi Basis Data

Saat Anda menggunakan pustaka ini, Anda dapat mengatur koneksi basis data dengan beberapa cara berbeda. Anda dapat mengatur koneksi di konstruktor, Anda dapat mengaturnya melalui variabel config `$config['connection']` atau Anda dapat mengaturnya melalui `setDatabaseConnection()` (v0.4.1). 

```php
$pdo_connection = new PDO('sqlite:test.db'); // misalnya
$user = new User($pdo_connection);
// atau
$user = new User(null, [ 'connection' => $pdo_connection ]);
// atau
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Jika Anda ingin menghindari selalu mengatur `$database_connection` setiap kali Anda memanggil active record, ada cara untuk itu!

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

// Dan sekarang, tidak ada argumen yang diperlukan!
$user = new User();
```

> **Catatan:** Jika Anda berencana untuk unit testing, melakukannya dengan cara ini dapat menambah beberapa tantangan untuk unit testing, tapi secara keseluruhan karena Anda dapat menyuntikkan 
koneksi Anda dengan `setDatabaseConnection()` atau `$config['connection']` itu tidak terlalu buruk.

Jika Anda perlu menyegarkan koneksi basis data, misalnya jika Anda menjalankan skrip CLI yang panjang dan perlu menyegarkan koneksi setiap beberapa saat, Anda dapat mengatur ulang koneksi dengan `$your_record->setDatabaseConnection($pdo_connection)`.

## Berkontribusi

Silakan lakukan. :D

### Pengaturan

Saat Anda berkontribusi, pastikan Anda menjalankan `composer test-coverage` untuk mempertahankan cakupan tes 100% (ini bukan cakupan unit test yang sebenarnya, lebih seperti pengujian integrasi).

Juga pastikan Anda menjalankan `composer beautify` dan `composer phpcs` untuk memperbaiki kesalahan linting apa pun.

## Lisensi

MIT