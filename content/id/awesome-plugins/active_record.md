# Flight Active Record

Sebuah rekaman aktif adalah pemetaan entitas basis data ke dalam objek PHP. Secara sederhana, jika Anda memiliki tabel pengguna di basis data Anda, Anda dapat "mentranslasi" sebuah baris di tabel tersebut menjadi kelas `User` dan objek `$user` dalam basis kode Anda. Lihat [contoh dasar](#basic-example).

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

Sekarang Anda dapat menyiapkan kelas baru untuk merepresentasikan tabel ini:

```php
/**
 * Kelas ActiveRecord biasanya tunggal
 * 
 * Sangat direkomendasikan untuk menambahkan properti dari tabel sebagai komentar di sini
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// Anda dapat menetapkannya dengan cara ini
		parent::__construct($database_connection, 'users');
		// atau dengan cara ini
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Sekarang saksikan keajaiban terjadi!

```php
// untuk sqlite
$database_connection = new PDO('sqlite:test.db'); // ini hanya untuk contoh, Anda mungkin akan menggunakan koneksi basis data yang nyata

// untuk mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// atau mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// atau mysqli dengan penciptaan non-objek
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
// tidak bisa menggunakan $user->save() di sini atau akan berpikir ini adalah pembaruan!

echo $user->id; // 2
```

Dan begitu mudahnya untuk menambahkan pengguna baru! Sekarang ada baris pengguna di basis data, bagaimana cara mengambilnya keluar?

```php
$user->find(1); // cari id = 1 di basis data dan kembalikan.
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

Ini bisa digunakan sebagai pustaka mandiri atau dengan Flight PHP Framework. Sepenuhnya terserah Anda.

### Mandiri
Pastikan Anda melewatkan koneksi PDO ke konstruktor.

```php
$pdo_connection = new PDO('sqlite:test.db'); // ini hanya untuk contoh, Anda mungkin akan menggunakan koneksi basis data yang nyata

$User = new User($pdo_connection);
```

> Tidak ingin selalu menetapkan koneksi basis data Anda di konstruktor? Lihat [Manajemen Koneksi Basis Data](#database-connection-management) untuk ide lainnya!

### Daftarkan sebagai metode dalam Flight
Jika Anda menggunakan Flight PHP Framework, Anda dapat mendaftar kelas ActiveRecord sebagai layanan, tetapi sebenarnya Anda tidak perlu.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// kemudian Anda dapat menggunakannya seperti ini di kontroler, fungsi, dll.

Flight::user()->find(1);
```

## Metode `runway`

[runway](https://docs.flightphp.com/awesome-plugins/runway) adalah alat CLI untuk Flight yang memiliki perintah khusus untuk pustaka ini.

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
     * @var array $relations Tetapkan hubungan untuk model
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

Temukan satu rekaman dan tetapkan ke objek saat ini. Jika Anda melewatkan `$id` dari jenis tertentu, itu akan melakukan lookup pada kunci utama dengan nilai tersebut. Jika tidak ada yang dilewatkan, itu hanya akan menemukan rekaman pertama di tabel.

Selain itu, Anda dapat melewatkan metode bantu lainnya untuk kueri tabel Anda.

```php
// temukan rekaman dengan beberapa kondisi sebelumnya
$user->notNull('password')->orderBy('id DESC')->find();

// temukan rekaman berdasarkan id tertentu
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Menemukan semua rekaman dalam tabel yang Anda tentukan.

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

Menyisipkan rekaman saat ini ke dalam basis data.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

##### Kunci Utama Berbasis Teks

Jika Anda memiliki kunci utama berbasis teks (seperti UUID), Anda dapat menetapkan nilai kunci utama sebelum menyisipkan dengan salah satu dari dua cara.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // atau $user->save();
```

atau Anda dapat membuat kunci utama secara otomatis untuk Anda melalui peristiwa.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// Anda juga dapat menetapkan primaryKey ini dengan cara ini sebagai pengganti array di atas.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // atau bagaimana pun Anda perlu membuat id unik Anda
	}
}
```

Jika Anda tidak menetapkan kunci utama sebelum menyisipkan, itu akan disetel ke `rowid` dan basis data akan menghasilkan untuk Anda, tetapi tidak akan bertahan karena field tersebut mungkin tidak ada di tabel Anda. Inilah sebabnya mengapa disarankan untuk menggunakan peristiwa untuk menangani ini secara otomatis untuk Anda.

#### `update(): boolean|ActiveRecord`

Memperbarui rekaman saat ini ke dalam basis data.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Menyisipkan atau memperbarui rekaman saat ini ke dalam basis data. Jika rekaman memiliki id, itu akan diperbarui, jika tidak akan disisipkan.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Catatan:** Jika Anda memiliki hubungan yang ditentukan dalam kelas, itu akan secara rekursif menyimpan hubungan tersebut juga jika telah ditentukan, diinstansiasi, dan memiliki data kotor untuk diperbarui. (v0.4.0 dan lebih tinggi)

#### `delete(): boolean`

Menghapus rekaman saat ini dari basis data.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Anda juga dapat menghapus beberapa rekaman dengan melakukan pencarian terlebih dahulu.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Data kotor mengacu pada data yang telah diubah dalam suatu rekaman.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// tidak ada yang "kotor" pada saat ini.

$user->email = 'test@example.com'; // sekarang email dianggap "kotor" karena sudah diubah.
$user->update();
// sekarang tidak ada data yang kotor karena sudah diperbarui dan dipertahankan di basis data

$user->password = password_hash('newpassword'); // sekarang ini kotor
$user->dirty(); // melewatkan tidak ada yang akan membersihkan semua entri kotor.
$user->update(); // tidak ada yang akan memperbarui karena tidak ada yang tertangkap sebagai kotor.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // baik nama dan password diperbarui.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Ini adalah alias untuk metode `dirty()`. Ini sedikit lebih jelas tentang apa yang Anda lakukan.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // baik nama dan password diperbarui.
