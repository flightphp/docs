# Активная запись Flight

Активная запись - это отображение сущности базы данных на объект PHP. Проще говоря, если у вас есть таблица пользователей в базе данных, вы можете "перевести" строку в этой таблице в класс `User` и объект `$user` в вашем коде. Смотрите [простой пример](#basic-example).

## Простой пример

Допустим, у вас есть следующая таблица:

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
 * Класс ActiveRecord обычно единственное число
 * 
 * Очень рекомендуется добавить свойства таблицы в виде комментариев здесь
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// вы можете установить это таким образом
		parent::__construct($database_connection, 'users');
		// или так
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

И вот как это работает!

```php
// для SQLite
$database_connection = new PDO('sqlite:test.db'); // это просто пример, вы, вероятно, будете использовать реальное подключение к базе данных

// для MySQL
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// или mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// или mysqli с созданием на основе не объекта
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'Бобби Тейблз';
$user->password = password_hash('некоторый крутой пароль');
$user->insert();
// или $user->save();

echo $user->id; // 1

$user->name = 'Джозеф Мамма';
$user->password = password_hash('еще крутой пароль!!!');
$user->insert();
// нельзя использовать $user->save() здесь, иначе он подумает, что это обновление!

echo $user->id; // 2
```

И это было настолько легко добавить нового пользователя! Теперь, когда есть строка пользователя в базе данных, как ее извлечь?

```php
$user->find(1); // найти id = 1 в базе данных и вернуть его.
echo $user->name; // 'Бобби Тейблз'
```

Что если вы хотите найти всех пользователей?

```php
$users = $user->findAll();
```

А как насчет определенного условия?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Посмотрите, как это весело? Давайте установим его и начнем!

## Установка

Просто установите с помощью Composer

```php
composer require flightphp/active-record 
```

## Использование

Эту библиотеку можно использовать как самостоятельную библиотеку или с фреймворком PHP Flight. Совершенно на ваше усмотрение.

### Как самостоятельно
Просто убедитесь, что вы передаете подключение PDO в конструктор.

```php
$pdo_connection = new PDO('sqlite:test.db'); // это просто пример, вы, вероятно, будете использовать реальное подключение к базе данных

$User = new User($pdo_connection);
```

### Фреймворк PHP Flight
Если вы используете фреймворк PHP Flight, вы можете зарегистрировать класс ActiveRecord в качестве сервиса (но вам честно говоря не обязательно).

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// затем вы можете использовать это в контроллере, функции и т. д.
Flight::user()->find(1);
```

## Функции CRUD

#### `find($id = null) : boolean|ActiveRecord`

Находит одну запись и назначает ее текущему объекту. Если вы передадите `$id` какого-либо вида, он выполнит поиск по первичному ключу с этим значением. Если ничего не передается, он просто найдет первую запись в таблице.

Кроме того, вы можете передать другие вспомогательные методы для запроса таблицы.

```php
// найти запись с какими-то условиями заранее
$user->notNull('password')->orderBy('id DESC')->find();

// найти запись по определенному id
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Находит все записи в указанной вами таблице.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

Возвращает `true`, если текущая запись была гидрирована (извлечена из базы данных).

```php
$user->find(1);
// если запись найдена с данными...
$user->isHydrated(); // true
```

#### `insert(): boolean|ActiveRecord`

Вставляет текущую запись в базу данных.

```php
$user = new User($pdo_connection);
$user->name = 'демо';
$user->password = md5('демо');
$user->insert();
```

##### Первичные ключи на основе текста

Если у вас есть первичный ключ на основе текста (например, UUID), вы можете установить значение первичного ключа перед вставкой одним из двух способов.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'демо';
$user->password = md5('демо');
$user->insert(); // или $user->save();
```

или можете иметь первичный ключ сгенерированным автоматически через события.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// вы также можете установить первичный ключ таким образом, а не массивом выше.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // или как вам удобно сгенерировать ваши уникальные идентификаторы
	}
}
```

Если вы не установите первичный ключ перед вставкой, он будет установлен на `rowid`, и база данных сгенерирует его для вас, но он не сохранится, потому что это поле может не существовать в вашей таблице. Поэтому рекомендуется использовать событие, чтобы автоматически обрабатывать это для вас.

#### `update(): boolean|ActiveRecord`

Обновляет текущую запись в базе данных.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Вставляет или обновляет текущую запись в базе данных. Если у записи есть id, он обновит, в противном случае вставит.

```php
$user = new User($pdo_connection);
$user->name = 'демо';
$user->password = md5('демо');
$user->save();
```

**Примечание:** Если в классе определены отношения, он рекурсивно сохранит эти отношения, если они были определены, созданы и есть данные для обновления. (v0.4.0 и выше)

#### `delete(): boolean`

Удаляет текущую запись из базы данных.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Вы также можете удалить несколько записей, выполнив поиск заранее.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Грязные данные относятся к данным, которые были изменены в записи.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// на данный момент нет "грязных" данных.

$user->email = 'test@example.com'; // теперь email считается "грязным", поскольку он изменился.
$user->update();
// теперь нет "грязных" данных, потому что они были обновлены и сохранены в базе данных

$user->password = password_hash()'newpassword'); // теперь это грязно
$user->dirty(); // передача ничего не очистит все грязные записи.
$user->update(); // ничего не обновит, потому что ни одна запись не была зафиксирована как грязная.

$user->dirty([ 'name' => 'нечто', 'password' => password_hash('другой пароль') ]);
$user->update(); // и имя, и пароль обновлены.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Это псевдоним для метода `dirty()`. Он немного яснее, что вы делаете.

```php
$user->copyFrom([ 'name' => 'нечто', 'password' => password_hash('другой пароль') ]);
$user->update(); // и имя, и пароль обновлены.
```

#### `isDirty(): boolean` (v0.4.0)

Возвращает `true`, если текущая запись была изменена.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@mail.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Сбрасывает текущую запись в начальное состояние. Это действительно хорошо использовать в циклических поведениях.
Если вы передадите `true`, это также сбросит данные запроса, которые были использованы для нахождения текущего объекта (поведение по умолчанию).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // начать с чистого листа
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

После выполнения метода `find()`, `findAll()`, `insert()`, `update()` или `save()` вы можете получить SQL, который был построен, и использовать его для отладки.

## Методы запросов SQL

#### `select(string $field1 [, string $field2 ... ])`

Вы можете выбрать только несколько столбцов в таблице, если пожелаете (это более производительно на чрезмерно широких таблицах с многими столбцами).

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Вы технически можете выбирать другую таблицу тоже! Почему бы и нет?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Вы даже можете присоединиться к другой таблице в базе данных.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Вы можете установить некоторые пользовательские условия where (в этом операторе where нельзя устанавливать параметры).

```php
$user->where('id=1 AND name="demo"')->find();
```

**Примечание по безопасности** - Вас может подстерегать идея что-то типа `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. ПОЖАЛУЙСТА, НЕ ДЕЛАЙТЕ ЭТО! Это подвержено так называемым атакам инъекций SQL. В Интернете есть много статей, пожалуйста, найдите в Google "атаки инъекций SQL php" и вы найдете много статей на эту тему. Правильный способ обработки этого с помощью этой библиотеки состоит в том, чтобы вместо метода `where()` вы делали что-то вроде `$user->eq('id', $id)->eq('name', $name)->find();` Если вам действительно нужно это сделать, в библиотеке `PDO` есть `$pdo->quote($var)`, чтобы заэкранировать его. Только после использования `quote()` можно использовать в выражении `where()`.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Группируйте результаты по определенному условию.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Сортируйте возвращенный запрос определенным образом.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Ограничьте количество возвращаемых записей. Если указан второй аргумент int, это будет смещение, а лимит как в SQL.

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
$user->like('name', 'де')->find();
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
Вы можете устанавливать несколько видов отношений с помощью этой библиотеки. Вы можете установить отношения один-ко-многим и один-к-одному между таблицами. Для этого немного предварительной настройки в классе.

Установка массива `$relations` не сложна, но догадаться о правильном синтаксисе может быть запутывающим.

```php
protected array $relations = [
	// вы можете назвать ключ как угодно. Хорошее название ActiveRecord
	'user' => [
		// обязательно
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // это тип отношения

		// обязательно
		'Some_Class', // это класс другой ActiveRecord, на который будет ссылаться это

		// обязательно
		// в зависимости от типа отношения
		// self::HAS_ONE = внешний ключ, который ссылается на соединение
		// self::HAS_MANY = внешний ключ, который ссылается на соединение
		// self::BELONGS_TO = локальный ключ, который ссылается на соединение
		'local_or_foreign_key',
		// только FYI, это также присоединяется к первичному ключу "другой" модели

		// необязательно
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // дополнительные условия, которые вы хотите при присоединении отношения
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// необязательно
		'back_reference_name' // это, если вы хотите обратно ссылаться на это отношение к самому себе, например $user->contact->user;
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

Теперь у нас настроены ссылки, поэтому мы можем использовать их очень легко!

```php
$user = new User($pdo_connection);

// найти самого последнего пользователя.
$user->notNull('id')->orderBy('id desc')->find();

// получить контакты, используя отношение:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// или мы можем пойти в другую сторону.
$contact = new Contact();

// найти один контакт
$contact->find();

// получить пользователя, используя отношение:
echo $contact->user->name; // это имя пользователя
```

Довольно круто, да?

## Установка пользовательских данных
Иногда вам может понадобиться прикрепить что-то уникальное к вашей ActiveRecord, например, пользовательский расчет, который может быть проще прикрепить к объекту, который затем будет передан, скажем, в шаблон.

#### `setCustomData(string $field, mixed $value)`
Вы прикрепляете пользовательские данные методом `setCustomData()`.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

А затем вы просто обращаетесь к нему как к обычному свойству объекта.

```php
echo $user->page_view_count;
```

## События

Одна из еще одной потрясающей функции этой библиотеки - события. События вызываются в определенные моменты, основываясь на определенных методах, которые вы вызываете. Они очень полезны для настройки данных автоматически.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Это действительно полезно, если вам нужно установить стандартное соединение или что-то в этом роде.

```php
// index.php или bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // не забудьте ссылку &
		// вы могли бы сделать это, чтобы автоматически установить соединение
		$config['connection'] = Flight::db();
		// или это
		$self->transformAndPersistConnection(Flight::db());
		
		// Вы также можете установить имя таблицы таким образом.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Вероятно, это полезно только в том случае, если вам нужно какое-то манипулирование запросом каждый раз.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// всегда выполнить id >= 0, если это ваше желание
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Это вероятно более полезно, если вам всегда нужно выполнять какую-то логику каждый раз, когда эта запись извлекается. Нужно ли вам расшифровать что-то? Нужно ли вам выполнить пользовательский запрос на подсчет каждый раз (не эффективно, но все равно)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// расшифровка чего-то
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// возможно, сохранение чего-то пользовательского, например, запроса???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Вероятно, это полезно только в том случае, если вам нужно какое-то манипулирование запросом каждый раз.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// всегда выполнить id >= 0, если это ваше желание
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Похоже на `afterFind()`, но вы можете применить его ко всем записям!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// сделайте что-то крутое, как и послеFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Очень полезно, если вам нужны какие-то стандартные значения каждый раз.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// установить какие-то стандартные значения
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

Возможно у вас есть случай использования для изменения данных после их вставки?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// сделайте что-то
		Flight::cache()->set('most_recent_insert_id', $self->id);
		
		// или что угодно еще....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Очень полезно, если вам нужны какие-то стандартные значения каждый раз при обновлении.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// установить какие-то стандартные значения
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Возможно у вас есть случай использования для изменения данных после их обновления?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// сделайте что-то
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// или что угодно....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Это полезно, если вы хотите, чтобы события происходили как при вставке, так и при обновлении. Я не буду вдаваться в долгое объяснение, но уверен, вы можете догадаться, что это.

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

Не уверен, что вы хотели бы здесь сделать, но здесь не будет суждений! Отправляйтесь!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'Он был храбрым солдатом... :cry-face:';
	} 
}
```

## Управление подключением к базе данных

Когда вы используете эту библиотеку, вы можете установить подключение к базе данных несколькими способами. Вы можете установить подключение в конструкторе, вы можете установить его через переменную конфигурации `$config['connection']` или вы можете установить его через `setDatabaseConnection()` (v0.4.1). 

```php
$pdo_connection = new PDO('sqlite:test.db'); // например
$user = new User($pdo_connection);
// или
$user = new User(null, [ 'connection' => $pdo_connection ]);
// или
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Если вам нужно обновить подключение к базе данных, например, если вы запускаете долго работающий сценарий CLI и нужно обновлять соединение время от времени, вы можете переустановить подключение с помощью `$your_record->setDatabaseConnection($pdo_connection)`.

## Вклад

Пожалуйста, проходите. :D

## Настройка

Когда вы вносите вклад, убедитесь, что вы запускаете `composer test-coverage`, чтобы поддерживать 100% покрытие тестов (это не истинное покрытие модульных тестов, а скорее тестирование интеграции).

Также убедитесь, что вы запускаете `composer beautify` и `composer phpcs`, чтобы исправить все ошибки линтинга.

## Лицензия

MIT