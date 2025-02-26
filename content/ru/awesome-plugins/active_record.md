# Flight Active Record 

Активная запись — это сопоставление сущности базы данных с объектом PHP. Говоря простыми словами, если у вас есть таблица пользователей в вашей базе данных, вы можете "перевести" строку в этой таблице в класс `User` и объект `$user` в вашем коде. См. [основной пример](#basic-example).

Нажмите [здесь](https://github.com/flightphp/active-record) для репозитория на GitHub.

## Основной пример

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
 * Настоятельно рекомендуется добавить свойства таблицы в качестве комментариев здесь
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// вы можете установить его таким образом
		parent::__construct($database_connection, 'users');
		// или таким образом
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Теперь смотрите, как происходит магия!

```php
// для sqlite
$database_connection = new PDO('sqlite:test.db'); // это просто для примера, вы, вероятно, используете реальное подключение к базе данных

// для mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// или mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// или mysqli с не объектным основанием
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('some cool password');
$user->insert();
// или $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('some cool password again!!!');
$user->insert();
// нельзя использовать $user->save() здесь, иначе это будет считаться обновлением!

echo $user->id; // 2
```

И было так легко добавить нового пользователя! Теперь, когда в базе данных есть строка пользователя, как же получить ее?

```php
$user->find(1); // найти id = 1 в базе данных и вернуть его.
echo $user->name; // 'Bobby Tables'
```

А что если вы хотите найти всех пользователей?

```php
$users = $user->findAll();
```

А что по поводу определенного условия?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Видите, как это весело? Давайте установим это и начнем!

## Установка

Просто установите с помощью Composer

```php
composer require flightphp/active-record 
```

## Использование

Это может использоваться как отдельная библиотека или с PHP фреймворком Flight. Полностью на ваше усмотрение.

### Отдельно
Просто убедитесь, что вы передаете подключение PDO в конструктор.

```php
$pdo_connection = new PDO('sqlite:test.db'); // это просто для примера, вы, вероятно, используете реальное подключение к базе данных

$User = new User($pdo_connection);
```

> Не хотите каждый раз устанавливать подключение к базе данных в конструкторе? См. [Управление подключением к базе данных](#database-connection-management) для других идей!

### Регистрировать как метод в Flight
Если вы используете PHP фреймворк Flight, вы можете зарегистрировать класс ActiveRecord как сервис, но вам это на самом деле не обязательно.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// затем вы можете использовать это вот так в контроллере, функции и т.д.

Flight::user()->find(1);
```

## Методы `runway`

[runway](/awesome-plugins/runway) — это инструмент CLI для Flight, который имеет специальную команду для этой библиотеки. 

```bash
# Использование
php runway make:record database_table_name [class_name]

# Пример
php runway make:record users
```

Это создаст новый класс в директории `app/records/` как `UserRecord.php` со следующим содержимым:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Класс ActiveRecord для таблицы пользователей.
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
     * @var array $relations Установите отношения для модели
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [
		// 'relation_name' => [ self::HAS_MANY, 'RelatedClass', 'foreign_key' ],
	];

    /**
     * Конструктор
     * @param mixed $databaseConnection Подключение к базе данных
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

## Функции CRUD

#### `find($id = null) : boolean|ActiveRecord`

Находит одну запись и присваивает ее текущему объекту. Если вы передаете `$id` какого-либо типа, он выполнит поиск по первичному ключу с этим значением. Если ничего не передается, он просто найдет первую запись в таблице.

Дополнительно вы можете передать ему другие вспомогательные методы для запроса вашей таблицы.

```php
// найти запись с некоторыми условиями заранее
$user->notNull('password')->orderBy('id DESC')->find();

// найти запись по конкретному id
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Находит все записи в указанной вами таблице.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

Возвращает `true`, если текущая запись была гидратирована (извлечена из базы данных).

```php
$user->find(1);
// если запись найдена с данными...
$user->isHydrated(); // true
```

#### `insert(): boolean|ActiveRecord`

Вставляет текущую запись в базу данных.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

##### Текстовые первичные ключи

Если у вас есть первичный ключ на основе текста (например, UUID), вы можете установить значение первичного ключа перед вставкой одним из двух способов.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // или $user->save();
```

или вы можете сгенерировать первичный ключ автоматически через события.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// вы также можете установить первичный ключ таким образом, а не используя массив выше.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // или как вам нужно сгенерировать ваши уникальные идентификаторы
	}
}
```

Если вы не установите первичный ключ перед вставкой, он будет установлен на `rowid`, и 
база данных сгенерирует его для вас, но он не сохранится, потому что это поле может не существовать
в вашей таблице. Вот почему рекомендуется использовать событие для автоматического управления этим 
для вас.

#### `update(): boolean|ActiveRecord`

Обновляет текущую запись в базе данных.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Вставляет или обновляет текущую запись в базе данных. Если у записи есть id, она обновит, в противном случае вставит.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Примечание:** Если у вас есть определенные отношения в классе, они также будут рекурсивно сохранены, если они были определены, инстанцированы и имеют "грязные" данные для обновления. (v0.4.0 и выше)

#### `delete(): boolean`

Удаляет текущую запись из базы данных.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Вы также можете удалить несколько записей, выполнив предварительный поиск.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Грязные данные — это данные, которые были изменены в записи.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// на этом этапе ничего не является "грязным".

$user->email = 'test@example.com'; // теперь email считается "грязным", поскольку он изменен.
$user->update();
// теперь нет грязных данных, потому что они были обновлены и сохранены в базе данных

$user->password = password_hash()'newpassword'); // теперь это грязно
$user->dirty(); // если ничего не передать, все грязные записи будут очищены.
$user->update(); // ничего не обновится, так как ничего не было захвачено как грязное.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // оба name и password обновляются.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Это алиас для метода `dirty()`. Это немного понятнее, что вы делаете.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // оба name и password обновляются.
```

#### `isDirty(): boolean` (v0.4.0)

Возвращает `true`, если текущая запись была изменена.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Сбрасывает текущую запись в ее начальное состояние. Это действительно удобно использовать в циклах.
Если вы передаете `true`, это также сбросит данные запроса, которые использовались для поиска текущего объекта (поведение по умолчанию).

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

После того как вы выполните метод `find()`, `findAll()`, `insert()`, `update()` или `save()`, вы можете получить SQL, который был построен, и использовать его для отладки.

## Методы SQL-запросов
#### `select(string $field1 [, string $field2 ... ])`

Если хотите, вы можете выбрать только несколько столбцов в таблице (это более производительно для очень широких таблиц с множеством колонок)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Вы также можете выбрать другую таблицу! Почему бы и нет?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Вы даже можете соединиться с другой таблицей в базе данных.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Вы можете установить некоторые пользовательские условия where (вы не можете установить параметры в этом операторе where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Примечание безопасности** - Вы можете быть искушены сделать что-то вроде `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Пожалуйста, НЕ ДЕЛАЙТЕ ЭТОГО!!! Это уязвимо для того, что известно как атака SQL-инъекции. Существует множество статей в интернете, просто поищите "sql injection attacks php", и вы найдете много статей на эту тему. Правильный способ справиться с этим с помощью этой библиотеки: вместо этого метода `where()` вы могли бы сделать что-то вроде `$user->eq('id', $id)->eq('name', $name)->find();` Если вы абсолютно должны это сделать, библиотека `PDO` имеет `$pdo->quote($var)`, чтобы экранировать это за вас. Только после того, как вы используете `quote()`, вы можете использовать это в операторе `where()`.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Группируйте свои результаты по определенному условию.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Сортируйте возвращаемый запрос определенным образом.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Ограничьте количество возвращаемых записей. Если передан второй int, он будет смещен, ограничение, как в SQL.

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

### Условия ИЛИ

Возможно обернуть ваши условия в оператор ИЛИ. Это делается либо с помощью метода `startWrap()` и `endWrap()`, либо путем заполнения 3-го параметра условия после поля и значения.

```php
// Метод 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// Это будет эквивалентно `id = 1 AND (name = 'demo' OR name = 'test')`

// Метод 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// Это будет эквивалентно `id = 1 OR name = 'demo'`
```

## Отношения
Вы можете установить несколько видов отношений с помощью этой библиотеки. Вы можете устанавливать отношения один->многие и один->один между таблицами. Это требует немного дополнительной настройки в классе заранее.

Установка массива `$relations` несложна, но угадать правильный синтаксис может быть затруднительно.

```php
protected array $relations = [
	// вы можете назвать ключ как угодно. Имя ActiveRecord, вероятно, подойдет. Например: user, contact, client
	'user' => [
		// обязательно
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // это тип отношения

		// обязательно
		'Some_Class', // это "другой" класс ActiveRecord, на который будет делаться ссылка

		// обязательно
		// в зависимости от типа отношения
		// self::HAS_ONE = внешний ключ, который ссылается на соединение
		// self::HAS_MANY = внешний ключ, который ссылается на соединение
		// self::BELONGS_TO = локальный ключ, который ссылается на соединение
		'local_or_foreign_key',
		// просто FYI, это также только соединяется с первичным ключом "другой" модели

		// опционально
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // дополнительные условия, которые вам нужны при соединении
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// опционально
		'back_reference_name' // это если вы хотите сделать обратную ссылку на это отношение к себе Ex: $user->contact->user;
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

Теперь у нас есть настроенные ссылки, и мы можем использовать их очень легко!

```php
$user = new User($pdo_connection);

// найти самого последнего пользователя.
$user->notNull('id')->orderBy('id desc')->find();

// получить контакты, используя отношение:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// или можем пойти в обратном направлении.
$contact = new Contact();

// найти один контакт
$contact->find();

// получить пользователя, используя отношение:
echo $contact->user->name; // это имя пользователя
```

Довольно круто, не правда ли?

## Установка пользовательских данных
Иногда вам может потребоваться прикрепить что-то уникальное к вашей ActiveRecord, например, пользовательское вычисление, которое может быть проще прикрепить к объекту, который затем передается, скажем, в шаблон.

#### `setCustomData(string $field, mixed $value)`
Вы прикрепляете пользовательские данные с помощью метода `setCustomData()`.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

И затем вы просто ссылаетесь на него как на обычное свойство объекта.

```php
echo $user->page_view_count;
```

## События

Еще одна супер классная функция этой библиотеки связана с событиями. События вызываются в определенное время, основанное на определенных вызванных вами методах. Они очень полезны для автоматической настройки данных.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Это действительно полезно, если вам нужно установить подключение по умолчанию или что-то в этом роде.

```php
// index.php или bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // не забудьте о ссылке &
		// вы можете сделать это, чтобы автоматически установить соединение
		$config['connection'] = Flight::db();
		// или это
		$self->transformAndPersistConnection(Flight::db());
		
		// Вы также можете установить имя таблицы таким образом.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Это, вероятно, будет полезно только в том случае, если вам нужен манипуляция запросом каждый раз.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// всегда выполняйте id >= 0, если это ваше
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Этот метод, вероятно, будет более полезен, если вам нужно всегда выполнять какую-то логику каждый раз, когда эта запись извлекается. Вам нужно расшифровать что-то? Вам нужно каждый раз выполнять пользовательский запрос на количество (не производительно, но что ж)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// расшифровка чего-то
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// возможно, сохранение чего-то пользовательского, как запрос???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Это, вероятно, будет полезно только в том случае, если вам нужно манипулировать запросом каждый раз.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// всегда выполняйте id >= 0, если это ваше
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Похоже на `afterFind()`, но вы можете сделать это со всеми записями!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// сделайте что-то крутое, как в afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Действительно полезно, если вам нужно установить некоторые значения по умолчанию каждый раз.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// установите некоторые разумные значения по умолчанию
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

Возможно, у вас есть вариант изменить данные после их вставки?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// делайте, что хотите
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// или что-то еще....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Действительно полезно, если вам нужно установить некоторые значения по умолчанию каждый раз при обновлении.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// установите некоторые разумные значения по умолчанию
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Возможно, у вас есть вариант изменить данные после их обновления?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// делайте, что хотите
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// или что-то еще....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Это полезно, если вы хотите, чтобы события происходили как при вставках, так и при обновлениях. Я опущу длинное объяснение, но, я уверен, вы можете догадаться, что это такое.

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

Не уверен, что бы вы хотели здесь сделать, но никаких суждений здесь нет! Давайте!

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

Когда вы используете эту библиотеку, вы можете установить подключение к базе данных несколькими способами. Вы можете установить соединение в конструкторе, вы можете установить его через переменную конфигурации `$config['connection']` или вы можете установить его через `setDatabaseConnection()` (v0.4.1). 

```php
$pdo_connection = new PDO('sqlite:test.db'); // для примера
$user = new User($pdo_connection);
// или
$user = new User(null, [ 'connection' => $pdo_connection ]);
// или
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Если вы хотите избежать установки `$database_connection` каждый раз, когда вы вызываете активную запись, существуют способы обойти это!

```php
// index.php или bootstrap.php
// Установите это в качестве зарегистрированного класса в Flight
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

// User.php
class User extends flight\ActiveRecord {
	
	public function __construct(array $config = [])
	{
		$database_connection = $config['connection'] ?? Flight::db();
		parent::__construct($database_connection, 'users', $config);
	}
}

// И теперь, без аргументов!
$user = new User();
```

> **Примечание:** Если вы планируете проводить юнит-тестирование, делать это способом может создать определенные сложности, но в целом, поскольку вы можете внедрить ваше 
соединение с помощью `setDatabaseConnection()` или `$config['connection']`, это не так уж плохо.

Если вам нужно обновить подключение к базе данных, например, если вы запускаете длительный CLI-скрипт и вам необходимо периодически обновлять соединение, вы можете повторно установить соединение с помощью `$your_record->setDatabaseConnection($pdo_connection)`.

## Вклад

Пожалуйста, сделайте это. :D

### Настройка

Когда вы вносите свой вклад, обязательно выполните `composer test-coverage`, чтобы поддерживать 100% покрытие тестами (это не совсем покрытие юнит-тестами, скорее, интеграционное тестирование).

Также убедитесь, что вы выполняете `composer beautify` и `composer phpcs`, чтобы исправить любые ошибки форматирования. 

## Лицензия

MIT