# Flight Active Record 

Sebuah active record adalah pemetaan entitas basis data ke objek PHP. Sederhananya, jika Anda memiliki tabel pengguna di basis data Anda, Anda dapat "menerjemahkan" sebuah baris di tabel tersebut ke dalam kelas `User` dan objek `$user` dalam kode Anda. Lihat [contoh dasar](#basic-example).

Klik [di sini](https://github.com/flightphp/active-record) untuk repositori di GitHub.

## Contoh Dasar

Mari kita asumsikan Anda memiliki tabel berikut:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Sekarang Anda dapat mengatur kelas baru untuk mewakili tabel ini:

```php
/**
 * Sebuah kelas ActiveRecord biasanya tunggal
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
		// Anda dapat mengatur ini dengan cara ini
		parent::__construct($database_connection, 'users');
		// atau dengan cara ini
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Sekarang saksikan sihir terjadi!

```php
// untuk sqlite
$database_connection = new PDO('sqlite:test.db'); // ini hanya untuk contoh, Anda mungkin akan menggunakan koneksi basis data yang nyata

// untuk mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// atau mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// atau mysqli dengan pembuatan yang tidak berdasarkan objek
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
// tidak dapat menggunakan $user->save() di sini, atau itu akan mengira ini adalah pembaruan!

echo $user->id; // 2
```

Dan itu sangat mudah untuk menambahkan pengguna baru! Sekarang setelah ada baris pengguna di basis data, bagaimana cara Anda mengeluarkannya?

```php
$user->find(1); // cari id = 1 dalam basis data dan kembalikan.
echo $user->name; // 'Bobby Tables'
```

Dan bagaimana jika Anda ingin menemukan semua pengguna?

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

Ini dapat digunakan sebagai pustaka mandiri atau dengan Flight PHP Framework. Sepenuhnya terserah Anda.

### Mandiri
Pastikan Anda mengoper koneksi PDO ke konstruktor.

```php
$pdo_connection = new PDO('sqlite:test.db'); // ini hanya untuk contoh, Anda mungkin akan menggunakan koneksi basis data yang nyata

$User = new User($pdo_connection);
```

> Tidak ingin selalu mengatur koneksi basis data Anda di konstruktor? Lihat [Manajemen Koneksi Basis Data](#database-connection-management) untuk ide lainnya!

### Daftarkan sebagai metode dalam Flight
Jika Anda menggunakan Flight PHP Framework, Anda dapat mendaftarkan kelas ActiveRecord sebagai layanan, tetapi sejujurnya Anda tidak harus melakukannya.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// kemudian Anda dapat menggunakannya seperti ini di pengontrol, fungsi, dll.

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
 * Kelas ActiveRecord untuk tabel pengguna.
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
     * @var array $relations Menetapkan hubungan untuk model
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

Mencari satu catatan dan menetapkannya pada objek saat ini. Jika Anda mengoper `$id` dari jenis tertentu, itu akan melakukan pencarian pada kunci utama dengan nilai itu. Jika tidak ada yang dipassing, ini hanya akan menemukan catatan pertama di tabel.

Selain itu, Anda dapat mengoper metode pembantu lainnya untuk menanyakan tabel Anda.

```php
// mencari catatan dengan beberapa kondisi terlebih dahulu
$user->notNull('password')->orderBy('id DESC')->find();

// mencari catatan berdasarkan id tertentu
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Menemukan semua catatan di tabel yang Anda tentukan.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

Mengembalikan `true` jika catatan saat ini telah terhidrat (diambil dari database).

```php
$user->find(1);
// jika catatan ditemukan dengan data...
$user->isHydrated(); // true
```

#### `insert(): boolean|ActiveRecord`

Menyisipkan catatan saat ini ke dalam basis data.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

##### Kunci Utama berbasis Teks

Jika Anda memiliki kunci utama berbasis teks (seperti UUID), Anda dapat mengatur nilai kunci utama sebelum menyisipkan dalam dua cara.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // atau $user->save();
```

atau Anda dapat membiarkan kunci utama dihasilkan secara otomatis untuk Anda melalui peristiwa.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// Anda juga dapat mengatur primaryKey ini alih-alih array di atas.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // atau sesuaikan bagaimana Anda perlu menghasilkan id unik Anda
	}
}
```

Jika Anda tidak mengatur kunci utama sebelum menyisipkan, itu akan diatur ke `rowid` dan basis data akan menghasilkan untuk Anda, tetapi tidak akan dipertahankan karena bidang itu mungkin tidak ada dalam tabel Anda. Inilah sebabnya mengapa disarankan untuk menggunakan peristiwa untuk menangani ini secara otomatis untuk Anda.

#### `update(): boolean|ActiveRecord`

Memperbarui catatan saat ini ke dalam basis data.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Menyisipkan atau memperbarui catatan saat ini ke dalam basis data. Jika catatan memiliki id, itu akan memperbarui, jika tidak, itu akan menyisipkan.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Catatan:** Jika Anda memiliki hubungan yang ditentukan dalam kelas, itu akan menyimpan hubungan tersebut secara rekursif juga jika telah ditentukan, diinstansiasi, dan memiliki data yang perlu diperbarui. (v0.4.0 dan lebih baru)

#### `delete(): boolean`

Menghapus catatan saat ini dari basis data.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Anda juga dapat menghapus beberapa catatan dengan mengeksekusi pencarian terlebih dahulu.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Data "dirty" merujuk pada data yang telah diubah dalam sebuah catatan.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// tidak ada yang "dirty" pada titik ini.

$user->email = 'test@example.com'; // sekarang email dianggap "dirty" karena telah diubah.
$user->update();
// sekarang tidak ada data yang dirty karena telah diperbarui dan dipertahankan dalam basis data

$user->password = password_hash('newpassword'); // sekarang ini kotor
$user->dirty(); // melewatkan apa pun akan membersihkan semua entri yang kotor.
$user->update(); // tidak ada yang akan diperbarui karena tidak ada yang ditangkap sebagai kotor.

$user->dirty([ 'name' => 'sesuatu', 'password' => password_hash('password yang berbeda') ]);
$user->update(); // baik nama dan kata sandi diperbarui.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Ini adalah alias untuk metode `dirty()`. Ini sedikit lebih jelas tentang apa yang Anda lakukan.

```php
$user->copyFrom([ 'name' => 'sesuatu', 'password' => password_hash('password yang berbeda') ]);
$user->update(); // baik nama dan kata sandi diperbarui.
```

#### `isDirty(): boolean` (v0.4.0)

Mengembalikan `true` jika catatan saat ini telah diubah.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Mereset catatan saat ini ke keadaan awalnya. Ini sangat baik digunakan dalam perilaku tipe loop.
Jika Anda mengoper `true`, itu juga akan mereset data kueri yang digunakan untuk menemukan objek saat ini (perilaku default).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // mulai dengan slate yang bersih
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Setelah Anda menjalankan metode `find()`, `findAll()`, `insert()`, `update()`, atau `save()`, Anda dapat memperoleh SQL yang dibangun dan menggunakannya untuk tujuan debugging.

## Metode Kuery SQL
#### `select(string $field1 [, string $field2 ... ])`

Anda dapat memilih hanya beberapa kolom di tabel jika Anda mau (ini lebih efisien pada tabel yang sangat lebar dengan banyak kolom)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Anda dapat memilih tabel lain juga! Untuk apa tidak?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Anda bahkan dapat bergabung dengan tabel lain di basis data.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Anda dapat menetapkan beberapa argumen where kustom (Anda tidak dapat mengatur parameter dalam pernyataan where ini)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Catatan Keamanan** - Anda mungkin terdorong untuk melakukan sesuatu seperti `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Tolong JANGAN LAKUKAN INI!!! Ini rentan terhadap apa yang dikenal sebagai serangan SQL Injection. Ada banyak artikel di internet, silakan Google "sql injection attacks php" dan Anda akan menemukan banyak artikel tentang subjek ini. Cara yang tepat untuk menangani ini dengan perpustakaan ini adalah alih-alih metode `where()` ini, Anda akan melakukan sesuatu yang lebih seperti `$user->eq('id', $id)->eq('name', $name)->find();` Jika Anda harus melakukan ini, pustaka `PDO` memiliki `$pdo->quote($var)` untuk menghindarinya untuk Anda. Hanya setelah Anda menggunakan `quote()` Anda dapat menggunakannya dalam pernyataan `where()`.

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

#### `limit(string $limit)/limit(int $offset, int $limit)`

Batasi jumlah rekaman yang dikembalikan. Jika bilangan kedua diberikan, itu akan di-offset, batasi saja seperti di SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## Kondisi WHERE
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

Di mana `field = $value`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

Di mana `field <> $value`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

Di mana `field IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

Di mana `field IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

Di mana `field > $value`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

Di mana `field < $value`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

Di mana `field >= $value`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

Di mana `field <= $value`

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

Di mana `field LIKE $value` atau `field NOT LIKE $value`

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

Di mana `field IN($value)` atau `field NOT IN($value)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Di mana `field BETWEEN $value AND $value1`

```php
$user->between('id', [1, 2])->find();
```

### Kondisi OR

Dimungkinkan untuk membungkus kondisi Anda dalam pernyataan OR. Ini dilakukan dengan metode `startWrap()` dan `endWrap()` atau dengan mengisi parameter ke-3 dari kondisi setelah bidang dan nilai.

```php
// Metode 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// Ini akan dievaluasi menjadi `id = 1 AND (name = 'demo' OR name = 'test')`

// Metode 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// Ini akan dievaluasi menjadi `id = 1 OR name = 'demo'`
```

## Hubungan
Anda dapat mengatur beberapa jenis hubungan menggunakan pustaka ini. Anda dapat mengatur hubungan satu->banyak dan satu->satu antara tabel. Ini membutuhkan pengaturan ekstra dalam kelas sebelumnya.

Mengatur array `$relations` tidaklah sulit, tetapi menebak sintaks yang benar bisa membingungkan.

```php
protected array $relations = [
	// Anda dapat memberi nama kuncinya dengan cara apa pun yang Anda suka. Nama ActiveRecord mungkin bagus. Mis: user, contact, client
	'user' => [
		// wajib
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // ini adalah jenis hubungan

		// wajib
		'Some_Class', // ini adalah kelas ActiveRecord "lain" yang akan direferensikan

		// wajib
		// tergantung pada jenis hubungan
		// self::HAS_ONE = kunci asing yang mereferensikan gabungan
		// self::HAS_MANY = kunci asing yang mereferensikan gabungan
		// self::BELONGS_TO = kunci lokal yang mereferensikan gabungan
		'local_or_foreign_key',
		// hanya FYI, ini juga hanya bergabung dengan kunci utama model "lain"

		// opsional
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // kondisi tambahan yang Anda inginkan ketika menggabungkan hubungan
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// opsional
		'nama_referensi_kembali' // ini jika Anda ingin merujuk kembali hubungan ini kembali ke dirinya sendiri Mis: $user->contact->user;
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

// ambil kontak dengan menggunakan hubungan:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// atau kita bisa pergi ke arah yang lain.
$contact = new Contact();

// cari satu kontak
$contact->find();

// dapatkan pengguna dengan menggunakan hubungan:
echo $contact->user->name; // ini adalah nama pengguna
```

Keren kan?

## Mengatur Data Kustom
Terkadang Anda mungkin perlu melampirkan sesuatu yang unik pada ActiveRecord Anda seperti perhitungan khusus yang mungkin lebih mudah untuk dilampirkan pada objek yang kemudian akan diteruskan ke template.

#### `setCustomData(string $field, mixed $value)`
Anda melampirkan data kustom dengan metode `setCustomData()`.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

Dan kemudian Anda cukup merujuknya seperti properti objek biasa.

```php
echo $user->page_view_count;
```

## Peristiwa

Satu fitur luar biasa lainnya tentang pustaka ini adalah tentang peristiwa. Peristiwa dipicu pada saat tertentu berdasarkan metode tertentu yang Anda panggil. Mereka sangat membantu dalam menyiapkan data untuk Anda secara otomatis.

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

Ini mungkin hanya berguna jika Anda perlu manipulasi kueri setiap kali.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// selalu jalankan id >= 0 jika itu adalah yang Anda inginkan
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Yang ini mungkin lebih berguna jika Anda selalu perlu menjalankan beberapa logika setiap kali catatan ini diambil. Apakah Anda perlu mendekripsi sesuatu? Apakah Anda perlu menjalankan kueri hitung kustom setiap kali (tidak efisien tetapi tidak apa-apa)?

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
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']); 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Ini mungkin hanya berguna jika Anda perlu manipulasi kueri setiap kali.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// selalu jalankan id >= 0 jika itu adalah yang Anda inginkan
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Mirip dengan `afterFind()` tetapi Anda bisa melakukannya ke semua catatan!

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

Sangat berguna jika Anda perlu menetapkan beberapa nilai default setiap kali.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// menetapkan beberapa default yang baik
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

Mungkin Anda memiliki skenario untuk mengubah data setelah disisipkan?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// Anda melakukan Anda
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// atau apa pun....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Sangat berguna jika Anda perlu menetapkan beberapa nilai default setiap kali ada pembaruan.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// menetapkan beberapa default yang baik
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Mungkin Anda memiliki skenario untuk mengubah data setelah diperbarui?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// Anda melakukan Anda
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// atau apa pun....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Ini berguna jika Anda ingin peristiwa terjadi baik saat sisip atau pembaruan terjadi. Saya akan menghemat penjelasan panjangnya, tetapi saya yakin Anda bisa menebak apa itu.

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

Tidak yakin apa yang ingin Anda lakukan di sini, tetapi tidak ada penilaian di sini! Ayo lakukan!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'Dia adalah seorang prajurit yang berani... :cry-face:';
	} 
}
```

## Manajemen Koneksi Basis Data

Ketika Anda menggunakan pustaka ini, Anda dapat mengatur koneksi basis data dengan beberapa cara berbeda. Anda dapat mengatur koneksi di konstruktor, Anda dapat mengatur melalui variabel konfigurasi `$config['connection']` atau Anda dapat mengatur melalui `setDatabaseConnection()` (v0.4.1). 

```php
$pdo_connection = new PDO('sqlite:test.db'); // untuk contoh
$user = new User($pdo_connection);
// atau
$user = new User(null, [ 'connection' => $pdo_connection ]);
// atau
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Jika Anda ingin menghindari selalu mengatur `$database_connection` setiap kali Anda memanggil record aktif, ada cara untuk mengatasinya!

```php
// index.php atau bootstrap.php
// Set ini sebagai kelas terdaftar di Flight
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

> **Catatan:** Jika Anda berencana untuk melakukan pengujian unit, melakukan ini dapat menambah beberapa tantangan untuk pengujian unit, tetapi secara keseluruhan karena Anda dapat menyuntikkan koneksi Anda dengan `setDatabaseConnection()` atau `$config['connection']`, ini tidak terlalu buruk.

Jika Anda perlu menyegarkan koneksi basis data, misalnya jika Anda menjalankan skrip CLI yang berjalan lama dan perlu menyegarkan koneksi setiap saat, Anda dapat mengatur ulang koneksi dengan `$your_record->setDatabaseConnection($pdo_connection)`.

## Kontribusi

Silakan lakukan. :D

### Pengaturan

Saat Anda berkontribusi, pastikan Anda menjalankan `composer test-coverage` untuk mempertahankan 100% cakupan pengujian (ini bukan cakupan pengujian unit yang sebenarnya, lebih seperti pengujian integrasi).

Juga pastikan Anda menjalankan `composer beautify` dan `composer phpcs` untuk memperbaiki kesalahan linting. 

## Lisensi

MIT