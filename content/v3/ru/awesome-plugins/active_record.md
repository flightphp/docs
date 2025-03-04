# Flight Active Record 

Активная запись – это отображение сущности базы данных на объект PHP. Проще говоря, если у вас есть таблица пользователей в вашей базе данных, вы можете "перевести" строку в этой таблице на класс `User` и объект `$user` в вашем коде. См. [основной пример](#basic-example).

Нажмите [здесь](https://github.com/flightphp/active-record) для просмотра репозитория на GitHub.

## Основной пример

Предположим, у вас есть следующая таблица:

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
 * Настоятельно рекомендуется добавлять свойства таблицы в виде комментариев здесь
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
// или mysqli с созданием без объекта
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('некоторый классный пароль');
$user->insert();
// или $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('некоторый классный пароль снова!!!');
$user->insert();
// нельзя использовать $user->save() здесь, иначе он подумает, что это обновление!

echo $user->id; // 2
```

И добавление нового пользователя было так же просто! Теперь, когда в базе данных есть строка пользователя, как вы можете ее извлечь?

```php
$user->find(1); // найти id = 1 в базе данных и вернуть его.
echo $user->name; // 'Bobby Tables'
```

А что если вы хотите найти всех пользователей?

```php
$users = $user->findAll();
```

Что насчет определенного условия?

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

Это можно использовать как отдельную библиотеку или с PHP-фреймворком Flight. Полностью вам решать.

### Отдельно
Просто убедитесь, что вы передаете подключение PDO в конструктор.

```php
$pdo_connection = new PDO('sqlite:test.db'); // это просто для примера, вы, вероятно, используете реальное подключение к базе данных

$User = new User($pdo_connection);
```

> Не хотите всегда устанавливать ваше подключение к базе данных в конструкторе? См. [Управление подключением к базе данных](#database-connection-management) для других идей!

### Зарегистрировать как метод в Flight
Если вы используете PHP-фреймворк Flight, вы можете зарегистрировать класс ActiveRecord как сервис, но вам честно не обязательно это делать.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// тогда вы можете использовать это так в контроллере, функции и т.д.

Flight::user()->find(1);
```

## Методы `runway`

[runway](/awesome-plugins/runway) – это CLI инструмент для Flight, который имеет специальную команду для этой библиотеки. 

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

Находит одну запись и присваивает ее текущему объекту. Если вы передаете `$id` какого-либо рода, это будет выполнять поиск по первичному ключу с этим значением. Если ничего не передается, это просто найдет первую запись в таблице.

Кроме того, вы можете передать ему другие вспомогательные методы для запроса к вашей таблице.

```php
// найти запись с некоторыми условиями заранее
$user->notNull('password')->orderBy('id DESC')->find();

// найти запись по конкретному id
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Находит все записи в таблице, которую вы указываете.

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

##### Первичные ключи на текстовой основе

Если у вас есть первичный ключ на текстовой основе (например, UUID), вы можете установить значение первичного ключа перед вставкой одним из двух способов.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // или $user->save();
```

или вы можете позволить первичному ключу автоматически генерироваться для вас через события.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// вы также можете установить первичный ключ таким образом вместо массива выше.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // или как вам нужно сгенерировать ваши уникальные идентификаторы
	}
}
```

Если вы не установите первичный ключ перед вставкой, он будет установлен на `rowid`, и база данных сгенерирует его для вас, но он не будет храниться, потому что это поле может не существовать в вашей таблице. Вот почему рекомендуется использовать событие для автоматического управления этим.

#### `update(): boolean|ActiveRecord`

Обновляет текущую запись в базе данных.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Вставляет или обновляет текущую запись в базе данных. Если у записи есть id, она будет обновлена, в противном случае будет вставлена.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Примечание:** Если у вас есть определенные отношения в классе, они также будут рекурсивно сохранены, если они были определены, созданы и имеют измененные данные для обновления. (v0.4.0 и выше)

#### `delete(): boolean`

Удаляет текущую запись из базы данных.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Вы также можете удалить несколько записей, исполняя поиск заранее.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Грязные данные относятся к данным, которые были изменены в записи.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// на данный момент ничего не "грязное".

$user->email = 'test@example.com'; // теперь email считается "грязным", поскольку он был изменен.
$user->update();
// теперь нет грязных данных, так как они были обновлены и сохранены в базе данных

$user->password = password_hash('newpassword'); // теперь это грязное
$user->dirty(); // ничего не передав, вы очистите все грязные записи.
$user->update(); // ничего не обновится, потому что ничего не было захвачено как грязное.

$user->dirty([ 'name' => 'что-то', 'password' => password_hash('другой пароль') ]);
$user->update(); // обновлены как имя, так и пароль.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Это псевдоним для метода `dirty()`. Немного более понятно, что вы делаете.

```php
$user->copyFrom([ 'name' => 'что-то', 'password' => password_hash('другой пароль') ]);
$user->update(); // обновлены как имя, так и пароль.
```

#### `isDirty(): boolean` (v0.4.0)

Возвращает `true`, если текущая запись была изменена.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Сбрасывает текущую запись в ее начальное состояние. Это действительно полезно использовать в поведениях типа цикла.
Если вы передадите `true`, он также сбросит данные запроса, которые использовались для поиска текущего объекта (поведение по умолчанию).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // начинаем с чистого листа
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

После выполнения метода `find()`, `findAll()`, `insert()`, `update()` или `save()` вы можете получить SQL, который был построен, и использовать его для отладки.

## Методы SQL Запросов
#### `select(string $field1 [, string $field2 ... ])`

Вы можете выбрать только несколько столбцов в таблице, если хотите (это более производительно на действительно широких таблицах с большим количеством столбцов)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Вы вполне можете выбрать другую таблицу! Почему бы и нет?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Вы даже можете присоединиться к другой таблице в базе данных.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Вы можете установить некоторые пользовательские аргументы where (вы не можете устанавливать параметры в этом условии where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Примечание по безопасности** - Вам может возникнуть желание сделать что-то вроде `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Пожалуйста, НЕ ДЕЛАЙТЕ ЭТОГО!!! Это подвержено тому, что называется атаками SQL-инъекций. В Интернете есть много статей, пожалуйста, поищите "sql injection attacks php", и вы найдёте много статей на эту тему. Правильный способ обработки этого с помощью этой библиотеки вместо этого метода `where()`, вы бы сделали что-то более вроде `$user->eq('id', $id)->eq('name', $name)->find();` Если вам абсолютно необходимо это делать, библиотека `PDO` имеет `$pdo->quote($var)`, чтобы экранировать это для вас. Только после того, как вы используете `quote()`, вы можете использовать это в операторе `where()`.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Группируйте ваши результаты по определенному условию.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Сортируйте возвращаемый запрос определённым образом.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Ограничьте количество возвращаемых записей. Если задано второе целое число, оно будет сдвинуто, ограничение также как в SQL.

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

Возможно, вы захотите обернуть ваши условия в оператор ИЛИ. Это делается либо с помощью методов `startWrap()` и `endWrap()`, либо путем заполнения 3-го параметра условия после поля и значения.

```php
// Метод 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// Это будет эквивалентно `id = 1 AND (name = 'demo' OR name = 'test')`

// Метод 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// Это будет эквивалентно `id = 1 OR name = 'demo'`
```

## Отношения
Вы можете установить несколько видов отношений, используя эту библиотеку. Вы можете установить отношения один->много и один->один между таблицами. Это требует небольшой дополнительной настройки в классе заранее.

Установка массива `$relations` не сложна, но угадать правильный синтаксис может быть сложно.

```php
protected array $relations = [
	// вы можете назвать ключ как угодно. Имя ActiveRecord, вероятно, хорошее. Например: user, contact, client
	'user' => [
		// обязательно
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // это тип отношения

		// обязательно
		'Some_Class', // это "другой" ActiveRecord, на который это будет ссылаться

		// обязательно
		// в зависимости от типа отношения
		// self::HAS_ONE = внешний ключ, который ссылается на соединение
		// self::HAS_MANY = внешний ключ, который ссылается на соединение
		// self::BELONGS_TO = локальный ключ, который ссылается на соединение
		'local_or_foreign_key',
		// просто FYI, это также соединяется только с первичным ключом "другой" модели

		// опционально
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // дополнительные условия, которые вы хотите при соединении с отношением
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// опционально
		'back_reference_name' // это если вы хотите ссылаться на это отношение обратно на себя, например: $user->contact->user;
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

Теперь у нас есть установленные ссылки, так что мы можем использовать их очень легко!

```php
$user = new User($pdo_connection);

// найдите самого последнего пользователя.
$user->notNull('id')->orderBy('id desc')->find();

// получите контакты, используя отношение:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// или мы можем пойти другим путем.
$contact = new Contact();

// найдите один контакт
$contact->find();

// получите пользователя, используя отношение:
echo $contact->user->name; // это имя пользователя
```

Классно, да?

## Установка пользовательских данных
Иногда вам может потребоваться прикрепить что-то уникальное к вашей ActiveRecord, например, пользовательский расчет, который, возможно, будет легче просто прикрепить к объекту, который затем будет передан, скажем, шаблону.

#### `setCustomData(string $field, mixed $value)`
Вы прикрепляете пользовательские данные с помощью метода `setCustomData()`.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

А затем вы просто ссылаетесь на это, как на обычное свойство объекта.

```php
echo $user->page_view_count;
```

## События

Одним из супер классных преимуществ этой библиотеки являются события. События срабатывают в определенные моменты на основе определенных методов, которые вы вызываете. Они очень полезны для автоматической настройки данных для вас.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Это действительно полезно, если вам нужно установить подключение по умолчанию или что-то подобное.

```php
// index.php или bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // не забывайте о референции &
		// вы можете сделать это, чтобы автоматически установить подключение
		$config['connection'] = Flight::db();
		// или это
		$self->transformAndPersistConnection(Flight::db());
		
		// Вы также можете установить имя таблицы таким образом.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Это, вероятно, будет полезно, только если вам нужно манипулировать запросом каждый раз.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// всегда выполняйте id >= 0, если это ваше желание
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Эта один, вероятно, более полезна, если вам всегда нужно выполнять какую-либо логику каждый раз, когда эта запись извлекается. Нужно ли вам расшифровать что-то? Вам нужно запустить пользовательский запрос на подсчет каждый раз (невыгодно, но что ж)?

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
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']); 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Это, вероятно, будет полезно, только если вам нужно манипулировать запросом каждый раз.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// всегда выполняйте id >= 0, если это ваше желание
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Похоже на `afterFind()`, но вы можете делать это со всеми записями сразу!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// сделайте что-то классное, как и в afterFind()
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
		// задайте некоторые надежные значения по умолчанию
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

Возможно, у вас есть случай использования для изменения данных послеINSERT?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// делайте что хотите
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// или что-то еще...
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

	protected function beforeUpdate(self $self) {
		// задайте некоторые надежные значения по умолчанию
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Возможно, у вас есть случай использования для изменения данных после его обновления?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterUpdate(self $self) {
		// делайте что хотите
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// или что-то еще...
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Это полезно, если вы хотите, чтобы события происходили как при вставках, так и при обновлениях. Я сэкономлю вам длинное объяснение, но я уверен, что вы можете догадаться, что это такое.

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

Не уверен, что вы хотите сделать здесь, но никаких предвзятостей! Дерзайте!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'Он был смелым солдатом... :cry-face:';
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

Если вы хотите избежать постоянного указания `$database_connection` каждый раз, когда вы вызываете активную запись, есть способы обойти это!

```php
// index.php или bootstrap.php
// Установите это как зарегистрированный класс в Flight
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

// User.php
class User extends flight\ActiveRecord {
	
	public function __construct(array $config = [])
	{
		$database_connection = $config['connection'] ?? Flight::db();
		parent::__construct($database_connection, 'users', $config);
	}
}

// И теперь, без аргументов не требуется!
$user = new User();
```

> **Примечание:** Если вы планируете unit-тестирование, делать это может создать некоторые проблемы с unit-тестами, но в целом, потому что вы можете инъектировать ваше 
подключение с помощью `setDatabaseConnection()` или `$config['connection']`, это не слишком плохо.

Если вам нужно обновить подключение к базе данных, например, если вы запускаете долгое CLI-скрипт и вам нужно периодически обновлять подключение, вы можете переустановить соединение с помощью `$your_record->setDatabaseConnection($pdo_connection)`.

## Участие

Пожалуйста, сделайте это. :D

### Настройка

Когда вы участвуете, убедитесь, что вы выполняете команду `composer test-coverage`, чтобы поддерживать 100% покрытие тестами (это не истинное покрытие юнит-тестами, больше похоже на интеграционное тестирование).

Также убедитесь, что вы выполняете `composer beautify` и `composer phpcs`, чтобы исправить любые ошибки линтинга.

## Лицензия

MIT