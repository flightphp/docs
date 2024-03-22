# Flight Active Record

Активная запись - это сопоставление сущности базы данных с объектом PHP. Проще говоря, если у вас есть таблица пользователей в вашей базе данных, вы можете "перевести" строку в этой таблице в класс `User` и объект `$user` в вашем кодовой базе. См. [пример использования](#basic-example).

## Базовый пример

Давайте предположим, что у вас есть следующая таблица:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Теперь вы можете создать новый класс, чтобы представить эту таблицу:

```php
/**
 * Класс Active Record обычно в единственном числе
 * 
 * Крайне рекомендуется добавить свойства таблицы в виде комментариев здесь
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// вы можете установить это так
		parent::__construct($database_connection, 'users');
		// или так
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Теперь посмотрите, как волшебство начинает работать!

```php
// для sqlite
$database_connection = new PDO('sqlite:test.db'); // это просто для примера, вы, вероятно, будете использовать реальное соединение с базой данных

// для mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// или mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// или mysqli с созданием без объекта
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'Бобби Тейблс';
$user->password = password_hash('некоторый крутой пароль');
$user->insert();
// или $user->save();

echo $user->id; // 1

$user->name = 'Джозеф Мамма';
$user->password = password_hash('еще один крутой пароль!!!');
$user->insert();
// нельзя использовать $user->save() здесь, иначе он подумает, что это обновление!

echo $user->id; // 2
```

И это было настолько просто добавить нового пользователя! Теперь, когда в базе данных есть строка пользователя, как вы ее извлекаете?

```php
$user->find(1); // находит id = 1 в базе данных и возвращает его.
echo $user->name; // 'Бобби Тейблс'
```

Что, если вы хотите найти всех пользователей?

```php
$users = $user->findAll();
```

А если вы хотите найти соответствующее условие?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Вам нравится этот процесс? Установим его и начнем!

## Установка

Просто установите с помощью Composer

```php
composer require flightphp/active-record 
```

## Использование

Это можно использовать как самостоятельную библиотеку, так и с фреймворком Flight PHP. Совершенно на ваше усмотрение.

### Самостоятельно
Просто убедитесь, что вы передаете соединение PDO в конструктор.

```php
$pdo_connection = new PDO('sqlite:test.db'); // это просто для примера, вы, вероятно, будете использовать реальное соединение с базой данных

$User = new User($pdo_connection);
```

### Фреймворк Flight PHP
Если вы используете фреймворк Flight PHP, вы можете зарегистрировать класс ActiveRecord в качестве сервиса (но честно говоря, это делать не обязательно).

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// тогда вы можете использовать его таким образом в контроллере, функции и т.д.

Flight::user()->find(1);
```

## Функции CRUD

#### `find($id = null) : boolean|ActiveRecord`

Находит одну запись и присваивает ее текущему объекту. Если вы передаете `$id`, он выполнит поиск по первичному ключу с этим значением. Если ничего не передается, он просто найдет первую запись в таблице.

Дополнительно вы можете передать ему другие вспомогательные методы для запроса вашей таблицы.

```php
// найти запись с определенными условиями заранее
$user->notNull('password')->orderBy('id DESC')->find();

// найти запись по определенному идентификатору
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Находит все записи в указанной таблице.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

Возвращает `true`, если текущая запись была загружена (извлечена из базы данных).

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

#### `update(): boolean|ActiveRecord`

Обновляет текущую запись в базе данных.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Вставляет или обновляет текущую запись в базе данных. Если у записи есть идентификатор, она будет обновлена, в противном случае она будет вставлена.

```php
$user = new User($pdo_connection);
$user->name = 'демо';
$user->password = md5('демо');
$user->save();
```

**Примечание:** Если в классе определены отношения, оно рекурсивно сохранит также эти отношения, если они были определены, созданы и содержат данные для обновления. (v0.4.0 и выше)

#### `delete(): boolean`

Удаляет текущую запись из базы данных.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Вы также можете удалить несколько записей, выполнить поиск заранее.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

"Грязные" данные относятся к данным, которые были изменены в записи.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// на данный момент ничего не является "грязным".
$user->email = 'test@example.com'; // теперь электронная почта считается "грязной", так как она изменилась
$user->update();
// теперь нет данных, которые считаются "грязными", потому что они были обновлены и сохранены в базе данных

$user->password = password_hash()'newpassword'); // теперь это "грязное" значение
$user->dirty(); // ничего явно передано, поэтому все грязные записи очищены.
$user->update(); // ничего не обновится, так как ничего не было отмечено как "грязное"

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // обновятся и имя, и пароль.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Это псевдоним для метода `dirty()`. Он понятнее, что именно вы делаете.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // как имя, так и пароль будут обновлены.
```

#### `isDirty(): boolean` (v0.4.0)

Возвращает `true`, если текущая запись была изменена.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Сбрасывает текущую запись к ее начальному состоянию. Это действительно полезно использовать в циклическом поведении.
Если вы передадите `true`, это также сбросит данные запроса, которые были использованы для нахождения текущего объекта (поведение по умолчанию).

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

#### `getBuiltSql(): string` (v0.4.1)

После выполнения методов `find()`, `findAll()`, `insert()`, `update()` или `save()` вы можете получить построенный SQL и использовать его для отладки.

## Методы SQL-запросов
#### `select(string $field1 [, string $field2 ... ])`

Вы можете выбрать только несколько столбцов в таблице, если хотите (это более производительно для очень широких таблиц с многими столбцами)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Вы также можете выбрать другую таблицу! Почему бы и нет?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Вы даже можете присоединиться к другой таблице в базе данных.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Вы можете установить пользовательские условия where (в этом where-выражении нельзя устанавливать параметры)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Примечание безопасности** - Вас может подстерегать соблазн сделать что-то вроде `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. ПОЖАЛУЙСТА, НЕ ДЕЛАЙТЕ ЭТО!!! Это уязвимо для так называемых атак внедрения SQL. Есть много статей в Интернете, пожалуйста, погуглите "SQL injection attacks php", и вы найдете много статей на эту тему. Правильным способом обработки этого с использованием этой библиотеки является, вместо метода `where()`, использовать что-то вроде `$user->eq('id', $id)->eq('name', $name)->find();` Если вам абсолютно необходимо это сделать, библиотека `PDO` имеет метод `$pdo->quote($var)`, который заэкранирует его для вас. Только после использования `quote()` вы можете использовать его в `where()` выражении.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Группирует ваши результаты по определенному условию.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Сортирует возвращенный запрос определенным образом.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Ограничивает количество возвращаемых записей. Если передан второй int, это будет смещение, и предел, как в SQL.

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
Вы можете установить несколько видов отношений с использованием этой библиотеки. Вы можете установить отношения один->многие и один->один между таблицами. Для этого требуется немного дополнительной настройки в классе заранее.

Установка массива `$relations` не сложна, но угадывание правильного синтаксиса может быть запутывающим.

```php
protected array $relations = [
	// вы можете назвать ключ как угодно. Имя ActiveRecord, вероятно, подойдет. Например, user, contact, client
	'user' => [
		// обязательно
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // это тип отношения

		// обязательно
		'Some_Class', // это "другой" класс ActiveRecord, на который будет ссылка

		// обязательно
		// в зависимости от типа отношения
		// self::HAS_ONE = внешний ключ, который ссылается на объединение
		self::HAS_MANY = внешний ключ, который ссылается на объединение
		// self::BELONGS_TO = локальный ключ, который ссылается на объединение
		'local_or_foreign_key',
		// просто FYI, это также соединяется только с первичным ключом "другой" модели

		// необязательно
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // дополнительные условия, которые вы хотите использовать при соединении отношения
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// необязательно
		'back_reference_name' // это, если вы хотите обратную ссылку этого отношения обратно к себе Ex: $user->contact->user;
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

Теперь у нас настроены ссылки, чтобы мы могли легко использовать их!

```php
$user = new User($pdo_connection);

// найдем самого последнего пользователя.
$user->notNull('id')->orderBy('id desc')->find();

// получить контакты, используя отношение:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// или можно пойти иным путем.
$contact = new Contact();

// найти один контакт
$contact->find();

// получить пользователя, используя отношение:
echo $contact->user->name; // это имя пользователя
```

Довольно прикольно, да?

## Установка пользовательских данных
Иногда вам может потребоваться присоединить к вашей Active Record что-то уникальное, такое как пользовательский расчет, который может быть проще всего присоединить к объекту и передать, скажем, шаблону.

#### `setCustomData(string $field, mixed $value)`
Вы присоединяете пользовательские данные с помощью метода `setCustomData()`.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

И затем просто обращаетесь к нему, как к обычному свойству объекта.

```php
echo $user->page_view_count;
```

## События

Еще одна потрясающая особенность этой библиотеки - это события. События вызываются в определенные моменты времени на основе определенных методов, которые вы вызываете. Они очень полезны для автоматической настройки данных для вас.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Это действительно полезно, если вам нужно установить соединение по умолчанию или что-то подобное.

```php
// index.php или bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // не забудьте ссылку на &
		// вы могли бы сделать это, чтобы автоматически установить соединение
		$config['connection'] = Flight::db();
		// или так
		$self->transformAndPersistConnection(Flight::db());
		
		// Вы также можете задать имя таблицы таким образом.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Это вероятно полезно только в случае необходимости манипулировать запросом каждый раз.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// всегда выполняйте id >= 0, если это ваше предпочтение
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Вероятно, этот метод более полезен, если вам всегда нужно выполнять какую-то логику каждый раз, когда эта запись выбирается. Нужно ли вам расшифровать что-то? Нужно ли вам выполнить пользовательский запрос на подсчет каждый раз (не производительный, но как бы то ни было)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// расшифровка чего-то
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// возможно, сохранение чего-то пользовательского, такого как запрос???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```