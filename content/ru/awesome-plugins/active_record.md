# Active Record Фреймворка

Active Record - это сопоставление сущности базы данных с объектом PHP. Проще говоря, если у вас есть таблица пользователей в базе данных, вы можете "перевести" строку в этой таблице в класс `User` и объект `$user` в вашем кодовой базе. См. [пример](#basic-example).

## Базовый Пример

Давайте предположим, что у вас есть следующая таблица:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Теперь вы можете настроить новый класс для представления этой таблицы:

```php
/**
 * Класс ActiveRecord обычно в единственном числе
 * 
 * Крайне рекомендуется добавить свойства таблицы в виде комментариев здесь
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */
класс User расширяет flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// вы можете установить это так
		parent::__construct($database_connection, 'users');
		// или так
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Теперь посмотрите, как происходит волшебство!

```php
// для sqlite
$database_connection = new PDO('sqlite:test.db'); // это просто для примера, вы скорее всего будете использовать реальное соединение с базой данных

// для mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// или mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// или mysqli с созданием на основе не объекта
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'Бобби Тейблз';
$user->password = password_hash('крутой пароль');
$user->вставить();
// или $user->save();

echo $user->id; // 1

$user->name = 'Джозеф Мамма';
$user->password = password_hash('еще крутой пароль!!!');
$user->insert();
// нельзя использовать $user->save() здесь, иначе он подумает, что это обновление!

echo $user->id; // 2
```

И это было настолько легко добавить нового пользователя! Теперь, когда в базе данных есть строка пользователя, как ее извлечь?

```php
$user->find(1); // найти id = 1 в базе данных и вернуть его.
echo $user->name; // 'Бобби Тейблз'
```

А что, если вы хотите найти всех пользователей?

```php
$users = $user->findAll();
```

Что насчет с определенным условием?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Весело это, не правда ли? Установим его и начнем!

## Установка

Просто установите с помощью Composer

```php
composer require flightphp/active-record 
```

## Использование

Это можно использовать как автономную библиотеку, так и с фреймворком PHP Flight. Полностью на ваше усмотрение.

### Автономный режим
Просто убедитесь, что вы передаете соединение PDO в конструктор.

```php
$pdo_connection = new PDO('sqlite:test.db'); // это просто для примера, вы скорее всего будете использовать реальное соединение с базой данных

$User = new User($pdo_connection);
```

### Фреймворк PHP Flight
Если вы используете фреймворк PHP Flight, вы можете зарегистрировать класс ActiveRecord как сервис (но вам честно говоря, не обязательно).

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// затем вы можете использовать его так в контроллере, функции и т. д.

Flight::user()->find(1);
```

## Функции CRUD

#### `find($id = null) : boolean|ActiveRecord`

Находит одну запись и присваивает ее текущему объекту. Если вы передаете `$id` какой-либо, он выполнит поиск по первичному ключу с этим значением. Если ничего не передано, он просто найдет первую запись в таблице.

Кроме того, вы можете передать ему другие вспомогательные методы для запроса вашей таблицы.

```php
// найти запись с некоторыми условиями заранее
$user->notNull('password')->orderBy('id DESC')->find();

// найти запись по конкретному id
$id = 123;
$user->найти($id);
```

#### `findAll(): array<int,ActiveRecord>`

Находит все записи в указанной таблице.

```php
$user->findAll();
```

#### `insert(): boolean|ActiveRecord`

Вставляет текущую запись в базу данных.

```php
$user = new User($pdo_connection);
$user->name = 'демо';
$user->password = md5('демо');
$user->insert();
```

#### `update(): boolean|ActiveRecord`

Обновляет текущую запись в базе данных.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `delete(): boolean`

Удаляет текущую запись из базы данных.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->удалить();
```

Вы также можете удалить несколько записей, выполнив поиск заранее.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Грязные данные относятся к данным, которые были изменены в записи.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// на данный момент ничто не является "грязным".
$user->email = 'test@example.com'; // теперь электронная почта считается "грязной", так как она изменилась.
$user->update();
// теперь нет данных, которые были бы загрязнены, потому что они были обновлены и сохранены в базе данных

$user->password = password_hash()'newpassword'); // теперь это грязно
$user->dirty(); // передача ничего не очистит все грязные записи.
$user->update(); // ничто не обновится, так как ничего не было захвачено как грязное.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // и имя, и пароль обновлены.
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Сбрасывает текущую запись до начального состояния. Это действительно хорошо использовать в циклических поведениях.
Если вы передадите `true`, он также сбросит данные запроса, которые были использованы для нахождения текущего объекта (поведение по умолчанию).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // начнем с чистого листа
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

## SQL Методы Запросов
#### `select(string $field1 [, string $field2 ... ])`

Вы можете выбрать только несколько столбцов в таблице, если хотите (это более производительно на очень широких таблицах с множеством столбцов).

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Технически вы можете также выбрать другую таблицу! Почему бы и нет?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Вы можете даже присоединяться к другой таблице в базе данных.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Вы можете установить некоторые пользовательские аргументы `where` (вы не можете устанавливать параметры в этом операторе where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Примечание о безопасности** - Вас может подкусить желание сделать что-то такое, как `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Пожалуйста, НЕ ДЕЛАЙТЕ ЭТО! Это подвержено так называемым атакам SQL-инъекций. Есть много статей онлайн, пожалуйста, введите в Google "атаки на SQL-инъекции PHP", и вы найдете много статей на эту тему. Правильный способ обработки этого с помощью этой библиотеки состоит в том, чтобы вместо этого метода `where()` вы делали что-то вроде `$user->eq('id', $id)->eq('name', $name)->find();`

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Группируйте ваши результаты по определенному условию.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Сортирует возвращаемый запрос определенным образом.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Ограничивает количество записей, возвращаемых. Если передано второе целое число, то это смещение, так же как в SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## Условия WHERE
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

Где `field = $value`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

Где `field <> $value`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

Где `field IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

Где `field IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

Где `field > $value`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

Где `field < $value`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

Где `field >= $value`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

Где `field <= $value`

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

Где `field LIKE $value` или `field NOT LIKE $value`

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

Где `field IN($value)` или `field NOT IN($value)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Где `field BETWEEN $value AND $value1`

```php
$user->between('id', [1, 2])->find();
```

## Отношения
Вы можете устанавливать несколько видов отношений с использованием этой библиотеки. Вы можете определить отношения один ко многим и один к одному между таблицами. Это требует немного дополнительной настройки в классе заранее.

Установка массива `$relations` несложна, но догадаться о правильном синтаксисе может быть запутывающим.

```php
protected array $relations = [
	// вы можете назвать ключ как угодно. По имени ActiveRecord, вероятно, хорошо. Например, user, contact, client
	'user' => [
		// обязательный параметр
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // это тип отношения

		// обязательный параметр
		'Некий_Класс', // это "другой" класс ActiveRecord, который будет ссылаться на этот

		// обязательный параметр
		// в зависимости от типа отношения
		// self::HAS_ONE = внешний ключ, который ссылается на соединение
		// self::HAS_MANY = внешний ключ, который ссылается на соединение
		// self::BELONGS_TO = локальный ключ, который ссылается на соединение
		'локальный_или_внешний_ключ',
		// просто FYI, это также соединяется только с первичным ключом "другой" модели

		// необязательно
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // дополнительные условия, которые вы хотите применить при объединении отношения
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// необязательно
		'back_reference_name' // это, если вы хотите обратно ссылаться на это отношение обратно к себе, например, $user->contact->user;
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

Теперь у нас есть настроенные ссылки, поэтому мы можем использовать их очень легко!

```php
$user = new User($pdo_connection);

// найти самого последнего пользователя.
$user->notNull('id')->orderBy('id desc')->find();

// получим контакты, используя отношение:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// или мы можем пойти по-друг# Active Record Framework

Active Record adalah pemetaan entitas database ke objek PHP. Dengan kata lain, jika Anda memiliki tabel pengguna di database Anda, Anda dapat "menerjemahkan" baris dalam tabel tersebut ke kelas `User` dan objek `$user` dalam kode Anda. Lihat [contoh dasar](#basic-example).

## Contoh Dasar

Misalkan Anda memiliki tabel berikut:

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
 * Kelas ActiveRecord biasanya dalam bentuk tunggal
 * 
 * Sangat dianjurkan untuk menambahkan properti tabel sebagai komentar di sini
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

Sekarang lihat keajaiban terjadi!

```php
// untuk sqlite
$database_connection = new PDO('sqlite:test.db'); // ini hanyalah contoh, Anda mungkin akan menggunakan koneksi database nyata

// untuk mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// atau mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// atau mysqli dengan pembuatan non-object
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
// tidak bisa menggunakan $user->save() di sini karena akan dianggap sebagai pembaruan!

echo $user->id; // 2
```

Dan itu begitu mudah menambahkan pengguna baru! Sekarang bahwa ada baris pengguna di database, bagaimana cara mengambilnya?

```php
$user->find(1); // temukan id = 1 di database dan kembalikan.
echo $user->name; // 'Bobby Tables'
```

Dan jika Anda ingin menemukan semua pengguna?

```php
$users = $user->findAll();
```

Bagaimana dengan kondisi tertentu?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Lihat seberapa menyenangkannya ini? Mari instalasi dan mulai!

## Instalasi

Hanya instalasi dengan Composer

```php
composer require flightphp/active-record 
```

## Penggunaan

Ini bisa digunakan sebagai perpustakaan mandiri atau dengan Kerangka Kerja PHP Flight. Sepenuhnya terserah Anda.

### Mandiri
Pastikan Anda hanya melewati koneksi PDO ke konstruktor.

```php
$pdo_connection = new PDO('sqlite:test.db'); // ini hanyalah contoh, Anda mungkin akan menggunakan koneksi database nyata

$User = new User($pdo_connection);
```

### Kerangka Kerja PHP Flight
Jika Anda menggunakan Kerangka Kerja PHP Flight, Anda dapat mendaftarkan kelas ActiveRecord sebagai layanan (tetapi sebenarnya Anda tidak perlu melakukannya).

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// kemudian Anda dapat menggunakannya seperti ini di kontroler, dalam fungsi, dll.

Flight::user()->find(1);
```

## Fungsi CRUD

#### `find($id = null) : boolean|ActiveRecord`

Temukan satu catatan dan berikan pada objek saat ini. Jika Anda melewati `$id` tertentu, itu akan melakukan pencarian pada kunci utama dengan nilai itu. Jika tidak ada yang dilewati, itu hanya akan menemukan catatan pertama dalam tabel.

Selain itu, Anda bisa melewati metode bantu lainnya untuk mengkueri tabel Anda.

```php
// temukan catatan dengan beberapa kondisi sebelumnya
$user->notNull('password')->orderBy('id DESC')->find();

// temukan catatan dengan id tertentu
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Temukan semua catatan dalam tabel yang Anda tentukan.

```php
$user->findAll();
```

#### `insert(): boolean|ActiveRecord`

Masukkan catatan saat ini ke database.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

#### `update(): boolean|ActiveRecord`

Memperbarui catatan saat ini ke database.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `delete(): boolean`

Menghapus catatan saat ini dari database.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Anda juga dapat menghapus beberapa catatan dengan menjalankan pencarian terlebih dahulu.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Data yang kotor merujuk pada data yang telah diubah dalam sebuah catatan.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// saat ini tidak ada yang "kotor".
$user->email = 'test@example.com'; // sekarang email dianggap "kotor" karena sudah berubah.
$user->update();
// sekarang tidak ada data yang kotor karena sudah diperbarui dan dipersistensikan dalam database

$user->password = password_hash()'newpassword'); // sekarang ini kotor
$user->dirty(); // melewati tidak akan membersihkan semua entri kotor.
$user->update(); // tidak akan melakukan pembaruan karena tidak ada yang ditangkap sebagai kotor.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // baik nama maupun password diperbarui.
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Meriset catatan saat ini ke keadaan awal. Ini bisa sangat baik digunakan dalam jenis struktur berulang.

Jika Anda melewati `true`, itu juga akan mereset data query yang digunakan untuk menemukan objek saat ini (perilaku default).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // mulai dengan lempengan bersih
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

## Metode Kueri SQL
#### `select(string $field1 [, string $field2 ... ])`

Anda dapat memilih hanya beberapa kolom di tabel jika Anda mau (ini lebih efisien pada tabel yang sangat lebar dengan banyak kolom).

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Teknisnya Anda juga bisa memilih tabel lain! Mengapa tidak?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Anda bahkan bisa bergabung dengan tabel lain dalam database.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Anda dapat mengatur beberapa argumen khusus `where` (Anda tidak dapat mengatur parameter di pernyataan `where` ini).

```php
$user->where('id=1 AND name="demo"')->find();
```

**Catatan Keamanan** - Anda mungkin tergoda untuk melakukan sesuatu seperti `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Mohon JANGAN LAKUKAN HAL INI! Ini rentan terhadap apa yang dikenal sebagai serangan SQL Injection. Ada banyak artikel online, silakan Cari di Google "serangan SQL injection php" dan Anda akan menemukan banyak artikel tentang topik ini. Cara yang benar untuk menangani ini dengan perpustakaan ini adalah daripada menggunakan metode `where()` ini, Anda akan melakukan sesuatu seperti `$user->eq('id', $id)->eq('name', $name)->find();`

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Mengelompokkan hasil Anda berdasarkan kondisi tertentu.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Mengurutkan kueri yang dikembalikan dengan cara tertentu.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Membatasi jumlah catatan yang dikembalikan. Jika integer kedua diberikan, itu akan menjadi pergeseran, sama seperti dalam SQL.

```php
$user->orderBy('name DESC')->limit(0, 10)->findAll();
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

## Hubungan
Anda dapat menetapkan beberapa jenis hubungan menggunakan perpustakaan ini. Anda dapat menetapkan hubungan satu->banyak dan satu->satu antara tabel. Ini memerlukan sedikit pengaturan tambahan dalam kelas sebelumnya.

Mengatur larik `$relations` tidak sulit, tetapi menebak sintaksis yang benar mungkin membingungkan.

```php
protected array $relations = [
	// Anda dapat memberi nama kunci apa pun yang Anda inginkan. Nama ActiveRecord mungkin baik. Misalnya, user, contact, client
	'user' => [
		// wajib
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // ini adalah jenis hubungan

		// wajib
		'Some_Class', // kelas ActiveRecord "lain" ini akan merujuk

		// wajib
		// tergantung pada jenis hubungannya
		// self::HAS_ONE = kunci asing yang merujuk pada gabungan
		// self::HAS_MANY = kunci asing yang merujuk pada gabungan
		// self::BELONGS_TO = kunci lokal yang merujuk pada gabungan
		'kunci_lokal_atau_asing',
		// hanya untuk pengetahuan, ini juga hanya bergabung dengan kunci utama model "lain"

		// opsional
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // kondisi tambahan yang ingin Anda gunakan saat menggabungkan hubungan
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// opsional
		'back_reference_name' // ini jika Anda ingin kembali merujuk pada hubungan ini ke dirinya sendiri, Misalnya $ user->contact->user;
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

Sekarang kita sudah menyiapkan referensi sehingga kita bisa menggunakannya dengan sangat mudah!

```php
$user = new User($pdo_connection);

// temukan pengguna terbaru.
$user->notNull('id')->orderBy('id desc')->find();

// dapatkan kontak dengan menggunakan hubungan:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// atau kita bisa pergi ke arah lain.
$contact = new Contact();

// temukan satu kontak
$contact->find();

// dapatkan pengguna dengan menggunakan hubungan:
echo $contact->user->name; // ini adalah nama pengguna
```

Cukup keren ya?

## Menetapkan Data Khusus
Terkadang Anda mungkin perlu melampirkan sesuatu yang unik ke ActiveRecord Anda seperti perhitungan kustom yang mungkin lebih mudah untuk melampirkan pada objek yang kemudian akan dilewatkan ke template misalnya.

#### `setCustomData(string $field, mixed $value)`
Anda melampirkan data khusus dengan metode `setCustomData()`.
```php
$user->setCustomData('hitung_tampilan_halaman', $hitung_tampilan_h);
```

Dan kemudian Anda cukup merujuknya seperti properti objek normal.

```php
echo $user->hitung_tampilan_halaman;
```

## Acara

Satu fitur luar biasa lagi tentang perpustakaan ini adalah tentang acara. Acara dipicu pada waktu tertentu berdasarkan metode tertentu yang Anda panggil. Mereka sangat membantu dalam menyiapkan data untuk Anda secara otomatis.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Ini benar-benar membantu jika Anda perlu menetapkan koneksi default atau sesuatu seperti itu.

```php
// index.php atau bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // jangan lupa referensikan &
		// Anda bisa melakukan ini untuk secara otomatis mengatur koneksi
		$config['connection'] = Flight::db();
		// atau ini
		$self->transformAndPersistConnection(Flight::db());
		
		// Anda juga bisa menetapkan nama tabel dengan cara ini.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Hal ini mungkin hanya berguna jika Anda perlu manipulasi kueri setiap kali.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// selalu menjalankan id >= 0 jika itu yang Anda inginkan
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Ini mungkin lebih berguna jika Anda perlu menjalankan logika setiap kali catatan ini diambil. Apakah Anda perlu mendekripsi sesuatu? Apakah Anda perlu menjalankan kueri perhitungan khusus setiap saat (tidak efisien tetu menjalankan?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// mendekripsi sesuatu
		$self->rahasia = yourDecryptFunction($self->rahasia, $kunci);

		// mungkin menyimpan sesuatu yang kustom seperti kueri???
		$self->setCustomData('jumlah_tampilan', $self->select('COUNT(*) count')->from('tampilan_pengguna')->eq('id_pengguna', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Hal ini mungkin hanya berguna jika Anda perlu manipulasi kueri setiap kali.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// selalu jalankan id >= 0 jika itu yang Anda inginkan
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Sama seperti `afterFind()` tetapi Anda bisa melakukannya pada semua catatan sebaliknya!

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

Sangat membantu jika Anda memerlukan nilai default yang diatur setiap kali.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// atur nilai default
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

Mungkin ada kasus penggunaan Anda untuk mengubah data setelah diinsert?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// lakukan metode yang Anda inginkan
		Flight::cache()->set('id_pencatatan_terakhir', $self->id);
		// atau apapun....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Sangat membantu jika Anda memerlukan nilai default yang diatur setiap kali diupdate.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// atur nilai default
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Mungkin ada kasus penggunaan Anda untuk mengubah data setelah diupdate?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// lakukan apapun yang Anda inginkan
		Flight::cache()->set('id_pengguna_terbaru', $self->id);
		// atau apapun....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Ini bermanfaat jika Anda ingin peristiwa terjadi ketika insert atau update terjadi. Saya akan melewati penjelasan panjang, tetapi saya yakin Anda bisa menebaknya.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeSave(self $self) {
		$self->terakhir_diperbarui = gmdate('Y-m-d H:i:s');
	} 
}
```

#### `beforeDelete(ActiveRecord $ActiveRecord)/afterDelete(ActiveRecord $ActiveRecord)`

Saya tidak yakin apa yang ingin Anda lakukan di sini, tetapi tidak ada penilaian di sini! Lanjutkan!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'Dia adalah prajurit yang gagah... :cry-face:';
	} 
}
```

## Berkontribusi

Harap lakukan.

## Setup

Saat Anda berkontribusi, pastikan Anda menjalankan `composer test-coverage` untuk menjaga 100% cakupan tes (ini bukan tes unit sejati, lebih seperti pengujian integrasi).

Pastikan juga Anda menjalankan `composer beautify` dan `composer phpcs` untuk memperbaiki kesalahan linter.

## Lisensi

MIT