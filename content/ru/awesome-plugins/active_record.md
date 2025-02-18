# Flight Active Record 

Активная запись - это отображение сущности базы данных на объект PHP. Проще говоря, если у вас есть таблица пользователей в вашей базе данных, вы можете "перевести" строку в этой таблице в класс `User` и объект `$user` в вашем коде. См. [базовый пример](#basic-example).

Нажмите [здесь](https://github.com/flightphp/active-record) для получения репозитория на GitHub.

## Базовый пример

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
 * Класс ActiveRecord обычно единственный
 * 
 * Настоятельно рекомендуется добавить свойства таблицы как комментарии здесь
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// вы можете задать это таким образом
		parent::__construct($database_connection, 'users');
		// или таким образом
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Теперь наблюдайте, как происходит волшебство!

```php
// для sqlite
$database_connection = new PDO('sqlite:test.db'); // это просто для примера, вы, вероятно, используете реальное соединение с базой данных

// для mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// или mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// или mysqli с созданием без объекта
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

И так легко добавить нового пользователя! Теперь, когда в базе данных есть строка пользователя, как же ее извлечь?

```php
$user->find(1); // найти id = 1 в базе данных и вернуть его.
echo $user->name; // 'Bobby Tables'
```

А что если вы хотите найти всех пользователей?

```php
$users = $user->findAll();
```

Как насчет определенного условия?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Видите, сколько всего это принесло? Давайте установим это и начнем!

## Установка

Просто установите с помощью Composer

```php
composer require flightphp/active-record 
```

## Использование

Это можно использовать как отдельную библиотеку, так и с PHP-фреймворком Flight. Полностью зависит от вас.

### Отдельно
Просто убедитесь, что вы передаете соединение PDO в конструктор.

```php
$pdo_connection = new PDO('sqlite:test.db'); // это просто для примера, вы, вероятно, используете реальное соединение с базой данных

$User = new User($pdo_connection);
```

> Не хотите всегда устанавливать соединение с базой данных в конструкторе? См. [Управление соединениями с базой данных](#database-connection-management) для других идей!

### Зарегистрировать как метод в Flight
Если вы используете PHP-фреймворк Flight, вы можете зарегистрировать класс ActiveRecord как сервис, но вам это совсем не обязательно.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// затем вы можете использовать это так в контроллере, функции и т.д.

Flight::user()->find(1);
```

## Методы `runway`

[runway](https://docs.flightphp.com/awesome-plugins/runway) - это инструмент CLI для Flight, который имеет пользовательскую команду для этой библиотеки.

```bash
# Использование
php runway make:record database_table_name [class_name]

# Пример
php runway make:record users
```

Это создаст новый класс в каталоге `app/records/` как `UserRecord.php` со следующим содержимым:

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
     * @var array $relations Установить связи для модели
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [
		// 'relation_name' => [ self::HAS_MANY, 'RelatedClass', 'foreign_key' ],
	];

    /**
     * Конструктор
     * @param mixed $databaseConnection Соединение с базой данных
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

## Функции CRUD

#### `find($id = null) : boolean|ActiveRecord`

Найдите одну запись и назначьте её текущему объекту. Если вы передаете `$id` какого-либо типа, это выполнит поиск по первичному ключу с этим значением. Если ничего не передано, он просто найдет первую запись в таблице.

Дополнительно вы можете передавать другие вспомогательные методы, чтобы запросить вашу таблицу.

```php
// найдите запись с некоторыми условиями заранее
$user->notNull('password')->orderBy('id DESC')->find();

// найдите запись по конкретному id
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Находит все записи в указанной вами таблице.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

Возвращает `true`, если текущая запись была загружена (получена из базы данных).

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

Если у вас есть текстовый первичный ключ (например, UUID), вы можете установить значение первичного ключа перед вставкой одним из двух способов.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // или $user->save();
```

или вы можете автоматически сгенерировать первичный ключ для вас через события.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// вы также можете установить первичный ключ таким образом вместо массива выше.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // или как вам нужно генерировать ваши уникальные id
	}
}
```

Если вы не установите первичный ключ перед вставкой, он будет установлен на `rowid`, и база данных сгенерирует его для вас, но он не сохранится, так как этого поля может не быть в вашей таблице. Поэтому рекомендуется использовать событие, чтобы автоматически управлять этим для вас.

#### `update(): boolean|ActiveRecord`

Обновляет текущую запись в базе данных.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Вставляет или обновляет текущую запись в базе данных. Если запись имеет id, она будет обновлена, в противном случае будет вставлена.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Примечание:** Если у вас есть определенные связи в классе, они также будут рекурсивно сохранять эти связи, если они были определены, инстанциированы и имеют "грязные" данные для обновления. (v0.4.0 и выше)

#### `delete(): boolean`

Удаляет текущую запись из базы данных.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Вы также можете удалить несколько записей, выполняя поиск заранее.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

"Грязные" данные относятся к данным, которые были изменены в записи.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// на этом этапе ничего не является "грязным".

$user->email = 'test@example.com'; // теперь email считается "грязным", поскольку он был изменён.
$user->update();
// теперь нет данных, которые являются грязными, так как они были обновлены и сохранены в базе данных

$user->password = password_hash()'newpassword'); // теперь это грязное
$user->dirty(); // если ничего не передать, все загрязненные записи будут очищены.
$user->update(); // ничего не будет обновлено, так как ничего не было захвачено как грязное.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // и имя, и пароль обновлены.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Это псевдоним для метода `dirty()`. Это немного более ясно, что вы делаете.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // и имя, и пароль обновлены.
```

#### `isDirty(): boolean` (v0.4.0)

Возвращает `true`, если текущая запись была изменена.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Сбрасывает текущую запись в её начальное состояние. Это очень полезно использовать в цикличных операциях.
Если вы передаете `true`, он также сбросит данные запроса, которые использовались для поиска текущего объекта (поведение по умолчанию).

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

После того, как вы выполните метод `find()`, `findAll()`, `insert()`, `update()` или `save()`, вы можете получить SQL, который был построен и использовать его для отладки.

## Методы SQL-запросов
#### `select(string $field1 [, string $field2 ... ])`

Вы можете выбрать только несколько столбцов в таблице, если хотите (это более производительно для действительно широких таблиц с большим количеством столбцов)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Вы также можете технически выбрать другую таблицу! Почему бы и нет?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Вы можете даже присоединить другую таблицу в базе данных.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Вы можете установить некоторые пользовательские аргументы where (вы не можете установить параметры в этом условии where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Примечание по безопасности** - Вы можете быть склонны делать что-то вроде `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Пожалуйста, НИКОГДА НЕ ДЕЛАЙТЕ ЭТОГО!!! Это подвержено тому, что известно как атаки SQL-инъекций. В интернете множество статей, пожалуйста, погуглите "sql injection attacks php" и вы найдете много статей на эту тему. Правильный способ справиться с этим с помощью этой библиотеки вместо этого метода `where()` будет что-то вроде `$user->eq('id', $id)->eq('name', $name)->find();`. Если вам это абсолютно необходимо, библиотека `PDO` имеет `$pdo->quote($var)`, чтобы экранировать его для вас. Только после использования `quote()` вы сможете использовать это в выражении `where()`.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Группируйте ваши результаты по определенному условию.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Сортируйте возвращаемый запрос определенным образом.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Ограничьте количество возвращаемых записей. Если передано второе целое число, оно будет смещением, лимитом, как в SQL.

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

Возможно обернуть ваши условия в оператор ИЛИ. Это делается либо с помощью методов `startWrap()` и `endWrap()`, либо заполнив 3-й параметр условия после поля и значения.

```php
// Метод 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// Это будет эквивалентно `id = 1 AND (name = 'demo' OR name = 'test')`

// Метод 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// Это будет эквивалентно `id = 1 OR name = 'demo'`
```

## Связи
Вы можете установить несколько типов связей, используя эту библиотеку. Вы можете установить связи один->многие и один->один между таблицами. Это требует небольшой дополнительной настройки в классе заранее.

Установка массива `$relations` не сложна, но угадать правильный синтаксис может быть запутанным.

```php
protected array $relations = [
	// вы можете называть ключ как угодно. Имя ActiveRecord, вероятно, хорошо. Например: user, contact, client
	'user' => [
		// обязательный
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // это тип связи

		// обязательный
		'Some_Class', // это другой класс ActiveRecord, на который будет ссылка

		// обязательный
		// в зависимости от типа отношения
		// self::HAS_ONE = внешний ключ, который ссылается на соединение
		// self::HAS_MANY = внешний ключ, который ссылается на соединение
		// self::BELONGS_TO = локальный ключ, который ссылается на соединение
		'local_or_foreign_key',
		// просто для справки, это также только соединяется с первичным ключом "другой" модели

		// необязательный
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // дополнительные условия, которые вы хотите при соединении
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// необязательный
		'back_reference_name' // это если вы хотите сделать обратную ссылку этой связи обратно к себе. Например: $user->contact->user;
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

Теперь у нас настроены ссылки, так что мы можем использовать их очень легко!

```php
$user = new User($pdo_connection);

// найдите самого последнего пользователя.
$user->notNull('id')->orderBy('id desc')->find();

// получите контакты, используя связь:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// или мы можем сделать наоборот.
$contact = new Contact();

// найдите один контакт
$contact->find();

// получите пользователя, используя связь:
echo $contact->user->name; // это имя пользователя
```

Здорово, правда?

## Установка пользовательских данных
Иногда вам может понадобиться прикрепить что-то уникальное к вашему ActiveRecord, например, пользовательский расчет, который будет проще просто прикрепить к объекту, который затем будет передан, например, в шаблон.

#### `setCustomData(string $field, mixed $value)`
Вы прикрепляете пользовательские данные с помощью метода `setCustomData()`.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

А затем вы просто ссылаетесь на это как на обычное свойство объекта.

```php
echo $user->page_view_count;
```

## События

Еще одна супер классная функция этой библиотеки - это события. События срабатывают в определенные моменты времени на основе определенных методов, которые вы вызываете. Они очень полезны для автоматической настройки данных для вас.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Это очень полезно, если вам нужно установить подключение по умолчанию или что-то подобное.

```php
// index.php или bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // не забудьте о & ссылке
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

Это, вероятно, будет полезно только в том случае, если вам нужно манипулировать запросом каждый раз.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// всегда запускайте id >= 0, если вам это подходит
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Это, вероятно, будет более полезно, если вам всегда нужно запускать некоторую логику каждый раз, когда эта запись извлекается. Вам нужно что-то расшифровать? Вам нужно запустить пользовательский запрос на подсчет каждый раз (не эффективно, но что ж)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// расшифровываете что-то
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// возможно, сохранить что-то пользовательское, как запрос???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']); 
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
		// всегда запускайте id >= 0, если вам это подходит
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Аналогично `afterFind()`, но вы можете сделать это для всех записей сразу!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// сделайте что-то интересное, как в afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Это действительно полезно, если вам нужно установить некоторые значения по умолчанию каждый раз.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// установите некоторые хорошие значения по умолчанию
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

Возможно, у вас есть случай, когда нужно изменить данные после вставки?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// вы делаете, что хотите
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
		// установите некоторые хорошие значения по умолчанию
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Может быть, у вас есть случай для изменения данных после того, как они обновлены?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// вы делаете, что хотите
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// или что-то еще....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Это полезно, если вы хотите, чтобы события происходили как при вставках, так и при обновлениях. Я не буду утомлять вас долгим объяснением, но вы, вероятно, догадываетесь, что это такое.

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

Не знаю, что вы хотите сделать здесь, но никаких предвзятостей здесь быть не должно! Просто вперед!

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

## Управление соединением с базой данных

Когда вы используете эту библиотеку, вы можете установить соединение с базой данных несколькими способами. Вы можете установить соединение в конструкторе, вы можете установить его через конфигурационную переменную `$config['connection']` или вы можете установить его через `setDatabaseConnection()` (v0.4.1). 

```php
$pdo_connection = new PDO('sqlite:test.db'); // например
$user = new User($pdo_connection);
// или
$user = new User(null, [ 'connection' => $pdo_connection ]);
// или
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Если вы хотите избежать установки `$database_connection` каждый раз, когда вы вызываете активную запись, есть способы это обойти!

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

// И теперь, аргументы не нужны!
$user = new User();
```

> **Примечание:** Если вы планируете проводить юнит тестирование, делать это может быть немного сложнее, но в целом, так как вы можете внедрить ваше 
соединение с помощью `setDatabaseConnection()` или `$config['connection']`, это не так уж плохо.

Если вам нужно обновить соединение с базой данных, например, если вы запускаете долгое CLI-приложение и вам нужно периодически обновлять соединение, вы можете повторно установить соединение с помощью `$your_record->setDatabaseConnection($pdo_connection)`.

## Участие

Пожалуйста, сделайте это. :D

### Настройка

Когда вы вносите вклад, убедитесь, что вы запускаете `composer test-coverage`, чтобы поддерживать 100% покрытие тестами (это не совсем покрытие юнит тестов, больше похоже на интеграционное тестирование).

Также убедитесь, что вы запускаете `composer beautify` и `composer phpcs`, чтобы исправить любые ошибки форматирования.

## Лицензия

MIT