# Flight Active Record 

Active Record — это сопоставление сущности базы данных с объектом PHP. Проще говоря, если у вас есть таблица users в базе данных, вы можете "перевести" строку в этой таблице в класс `User` и объект `$user` в вашем коде. См. [базовый пример](#basic-example).

Нажмите [здесь](https://github.com/flightphp/active-record) для просмотра репозитория на GitHub.

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
 * Класс ActiveRecord обычно используется в единственном числе
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

Теперь наблюдайте за магией!

```php
// для sqlite
$database_connection = new PDO('sqlite:test.db'); // это просто для примера, вы, вероятно, используете реальное соединение с базой данных

// для mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// или mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// или mysqli с созданием на основе не-объекта
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
// здесь нельзя использовать $user->save(), иначе оно подумает, что это обновление!

echo $user->id; // 2
```

И было так просто добавить нового пользователя! Теперь, когда в базе данных есть строка пользователя, как вы её извлечёте?

```php
$user->find(1); // найти id = 1 в базе данных и вернуть его.
echo $user->name; // 'Bobby Tables'
```

А что, если вы хотите найти всех пользователей?

```php
$users = $user->findAll();
```

А с определённым условием?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Видите, насколько это весело? Давайте установим его и начнём!

## Установка

Просто установите с помощью Composer

```php
composer require flightphp/active-record 
```

## Использование

Это можно использовать как самостоятельную библиотеку или с фреймворком Flight PHP. Полностью на ваше усмотрение.

### Автономно
Просто убедитесь, что вы передаёте соединение PDO в конструктор.

```php
$pdo_connection = new PDO('sqlite:test.db'); // это просто для примера, вы, вероятно, используете реальное соединение с базой данных

$User = new User($pdo_connection);
```

> Не хотите всегда устанавливать соединение с базой данных в конструкторе? См. [Управление соединением с базой данных](#database-connection-management) для других идей!

### Регистрация как метода в Flight
Если вы используете фреймворк Flight PHP, вы можете зарегистрировать класс ActiveRecord как сервис, но честно говоря, это не обязательно.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// затем вы можете использовать его так в контроллере, функции и т.д.

Flight::user()->find(1);
```

## Методы `runway`

[runway](/awesome-plugins/runway) — это CLI-инструмент для Flight, который имеет пользовательскую команду для этой библиотеки. 

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
 * Класс ActiveRecord для таблицы users.
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
     * @var array $relations Установка отношений для модели
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

Найти одну запись и присвоить её текущему объекту. Если вы передадите `$id` какого-либо рода, он выполнит поиск по первичному ключу с этим значением. Если ничего не передано, он просто найдёт первую запись в таблице.

Кроме того, вы можете передать другие вспомогательные методы для запроса таблицы.

```php
// найти запись с некоторыми условиями заранее
$user->notNull('password')->orderBy('id DESC')->find();

// найти запись по конкретному id
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Находит все записи в указанной таблице.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

Возвращает `true`, если текущая запись была гидратирована (загружена из базы данных).

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

##### Первичные ключи на основе текста

Если у вас есть первичный ключ на основе текста (например, UUID), вы можете установить значение первичного ключа перед вставкой одним из двух способов.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // или $user->save();
```

или вы можете позволить первичному ключу генерироваться автоматически для вас через события.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// вы также можете установить primaryKey таким образом вместо массива выше.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // или как вам нужно генерировать уникальные id
	}
}
```

Если вы не установите первичный ключ перед вставкой, он будет установлен как `rowid`, и база данных сгенерирует его для вас, но он не сохранится, потому что это поле может не существовать в вашей таблице. Поэтому рекомендуется использовать событие для автоматической обработки этого.

#### `update(): boolean|ActiveRecord`

Обновляет текущую запись в базе данных.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Вставляет или обновляет текущую запись в базу данных. Если запись имеет id, она обновится, иначе вставится.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Примечание:** Если у вас определены отношения в классе, он рекурсивно сохранит эти отношения, если они определены, инстанцированы и имеют изменённые данные для обновления. (v0.4.0 и выше)

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

// на данный момент ничего не "грязное".

$user->email = 'test@example.com'; // теперь email считается "грязным", поскольку оно изменилось.
$user->update();
// теперь нет данных, которые являются грязными, потому что они обновлены и сохранены в базе данных

$user->password = password_hash()'newpassword'); // теперь это грязное
$user->dirty(); // передача ничего очистит все грязные записи.
$user->update(); // ничего не обновится, потому что ничего не было захвачено как грязное.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // и name, и password обновлены.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Это псевдоним для метода `dirty()`. Это немного яснее, что вы делаете.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // и name, и password обновлены.
```

#### `isDirty(): boolean` (v0.4.0)

Возвращает `true`, если текущая запись была изменена.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Сбрасывает текущую запись в её начальное состояние. Это очень полезно для циклов. Если вы передадите `true`, он также сбросит данные запроса, которые использовались для поиска текущего объекта (поведение по умолчанию).

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

После выполнения метода `find()`, `findAll()`, `insert()`, `update()` или `save()` вы можете получить построенный SQL и использовать его для отладки.

## Методы SQL-запросов
#### `select(string $field1 [, string $field2 ... ])`

Вы можете выбрать только несколько столбцов в таблице, если хотите (это более производительно для очень широких таблиц с многими столбцами)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Вы технически можете выбрать другую таблицу тоже! Почему бы и нет?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Вы даже можете присоединить другую таблицу в базе данных.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Вы можете установить некоторые пользовательские аргументы where (вы не можете установить параметры в этом операторе where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Примечание по безопасности** — Вас может соблазнить сделать что-то вроде `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Пожалуйста, НЕ ДЕЛАЙТЕ ЭТОГО!!! Это подвержено тому, что известно как атаки SQL-инъекций. В интернете много статей, пожалуйста, погуглите "sql injection attacks php" и вы найдёте много статей по этой теме. Правильный способ обработки этого с этой библиотекой — вместо этого метода `where()` вы бы сделали что-то вроде `$user->eq('id', $id)->eq('name', $name)->find();` Если вам абсолютно необходимо это сделать, библиотека `PDO` имеет `$pdo->quote($var)` для экранирования. Только после использования `quote()` вы можете использовать его в операторе `where()`.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Группируйте ваши результаты по определённому условию.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Сортируйте возвращаемый запрос определённым образом.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Ограничьте количество возвращаемых записей. Если дано второе целое число, оно будет offset, limit точно как в SQL.

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

### Условия OR

Возможно обернуть ваши условия в оператор OR. Это делается либо с помощью метода `startWrap()` и `endWrap()`, либо заполняя 3-й параметр условия после поля и значения.

```php
// Метод 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// Это вычислится как `id = 1 AND (name = 'demo' OR name = 'test')`

// Метод 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// Это вычислится как `id = 1 OR name = 'demo'`
```

## Отношения
Вы можете установить несколько видов отношений с помощью этой библиотеки. Вы можете установить отношения один-ко-многим и один-к-одному между таблицами. Это требует немного дополнительной настройки в классе заранее.

Установка массива `$relations` не сложна, но угадывание правильного синтаксиса может быть запутанным.

```php
protected array $relations = [
	// вы можете назвать ключ как угодно. Название ActiveRecord, вероятно, хорошо. Пример: user, contact, client
	'user' => [
		// обязательно
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // это тип отношения

		// обязательно
		'Some_Class', // это "другой" класс ActiveRecord, на который будет ссылка

		// обязательно
		// в зависимости от типа отношения
		// self::HAS_ONE = внешний ключ, который ссылается на соединение
		// self::HAS_MANY = внешний ключ, который ссылается на соединение
		// self::BELONGS_TO = локальный ключ, который ссылается на соединение
		'local_or_foreign_key',
		// просто FYI, это также присоединяется только к первичному ключу "другой" модели

		// опционально
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // дополнительные условия, которые вы хотите при присоединении отношения
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// опционально
		'back_reference_name' // это если вы хотите обратную ссылку на это отношение обратно на себя Пример: $user->contact->user;
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

Теперь у нас настроены ссылки, чтобы мы могли использовать их очень легко!

```php
$user = new User($pdo_connection);

// найти самого последнего пользователя.
$user->notNull('id')->orderBy('id desc')->find();

// получить контакты, используя отношение:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// или мы можем пойти в обратную сторону.
$contact = new Contact();

// найти один контакт
$contact->find();

// получить пользователя, используя отношение:
echo $contact->user->name; // это имя пользователя
```

Довольно круто, eh?

### Жадная загрузка

#### Обзор
Жадная загрузка решает проблему N+1 запросов, загружая отношения заранее. Вместо выполнения отдельного запроса для отношений каждой записи, жадная загрузка извлекает все связанные данные всего в одном дополнительном запросе на отношение.

> **Примечание:** Жадная загрузка доступна только для v0.7.0 и выше.

#### Базовое использование
Используйте метод `with()` для указания, какие отношения жадно загружать:
```php
// Загрузить пользователей с их контактами в 2 запроса вместо N+1
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    foreach ($u->contacts as $contact) {
        echo $contact->email; // Нет дополнительного запроса!
    }
}
```

#### Несколько отношений
Загружайте несколько отношений одновременно:
```php
$users = $user->with(['contacts', 'profile', 'settings'])->findAll();
```

#### Типы отношений

##### HAS_MANY
```php
// Жадно загрузить все контакты для каждого пользователя
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    // $u->contacts уже загружен как массив
    foreach ($u->contacts as $contact) {
        echo $contact->email;
    }
}
```
##### HAS_ONE
```php
// Жадно загрузить один контакт для каждого пользователя
$users = $user->with('contact')->findAll();
foreach ($users as $u) {
    // $u->contact уже загружен как объект
    echo $u->contact->email;
}
```

##### BELONGS_TO
```php
// Жадно загрузить родительских пользователей для всех контактов
$contacts = $contact->with('user')->findAll();
foreach ($contacts as $c) {
    // $c->user уже загружен
    echo $c->user->name;
}
```
##### С find()
Жадная загрузка работает как с 
findAll()
, так и с 
find()
:

```php
$user = $user->with('contacts')->find(1);
// Пользователь и все их контакты загружены в 2 запроса
```
#### Преимущества производительности
Без жадной загрузки (проблема N+1):
```php
$users = $user->findAll(); // 1 запрос
foreach ($users as $u) {
    $contacts = $u->contacts; // N запросов (по одному на пользователя!)
}
// Итого: 1 + N запросов
```

С жадной загрузкой:

```php
$users = $user->with('contacts')->findAll(); // всего 2 запроса
foreach ($users as $u) {
    $contacts = $u->contacts; // 0 дополнительных запросов!
}
// Итого: 2 запроса (1 для пользователей + 1 для всех контактов)
```
Для 10 пользователей это уменьшает запросы с 11 до 2 — снижение на 82%!

#### Важные примечания
- Жадная загрузка полностью опциональна — ленивая загрузка работает как раньше
- Уже загруженные отношения автоматически пропускаются
- Обратные ссылки работают с жадной загрузкой
- Колбэки отношений уважаются во время жадной загрузки

#### Ограничения
- Вложенная жадная загрузка (например, 
with(['contacts.addresses'])
) в настоящее время не поддерживается
- Ограничения жадной загрузки через замыкания не поддерживаются в этой версии

## Установка пользовательских данных
Иногда вам может понадобиться прикрепить что-то уникальное к вашему ActiveRecord, например, пользовательский расчёт, который может быть проще прикрепить к объекту, который затем передаётся, скажем, в шаблон.

#### `setCustomData(string $field, mixed $value)`
Вы прикрепляете пользовательские данные с помощью метода `setCustomData()`.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

А затем вы просто ссылаетесь на него как на обычное свойство объекта.

```php
echo $user->page_view_count;
```

## События

Ещё одна супер крутая функция этой библиотеки — это события. События срабатывают в определённые моменты на основе определённых методов, которые вы вызываете. Они очень полезны для автоматической настройки данных для вас.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Это очень полезно, если вам нужно установить соединение по умолчанию или что-то вроде того.

```php
// index.php или bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // не забудьте ссылку &
		// вы могли бы сделать это для автоматической установки соединения
		$config['connection'] = Flight::db();
		// или это
		$self->transformAndPersistConnection(Flight::db());
		
		// Вы также можете установить имя таблицы таким образом.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Это, вероятно, полезно только если вам нужно манипулировать запросом каждый раз.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// всегда выполняйте id >= 0, если это ваш стиль
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Этот, вероятно, более полезен, если вам всегда нужно выполнять некоторую логику каждый раз, когда эта запись извлекается. Нужно ли вам расшифровать что-то? Нужно ли выполнять пользовательский запрос подсчёта каждый раз (не производительно, но ладно)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// расшифровка чего-то
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// возможно, хранение чего-то пользовательского, как запрос???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Это, вероятно, полезно только если вам нужно манипулировать запросом каждый раз.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// всегда выполняйте id >= 0, если это ваш стиль
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Похоже на `afterFind()`, но вы можете сделать это для всех записей!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// сделайте что-то крутое, как afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Очень полезно, если вам нужно установить некоторые значения по умолчанию каждый раз.

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

Возможно, у вас есть случай использования для изменения данных после вставки?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// делайте, что хотите
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// или что угодно....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Очень полезно, если вам нужно установить некоторые значения по умолчанию каждый раз при обновлении.

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

Возможно, у вас есть случай использования для изменения данных после обновления?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// делайте, что хотите
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// или что угодно....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Это полезно, если вы хотите, чтобы события происходили как при вставках, так и при обновлениях. Я пощажу вас от длинного объяснения, но уверен, вы можете угадать, что это такое.

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

Не уверен, что вы захотите здесь сделать, но никаких суждений! Действуйте!

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

## Управление соединением с базой данных

При использовании этой библиотеки вы можете установить соединение с базой данных несколькими способами. Вы можете установить соединение в конструкторе, вы можете установить его через переменную конфигурации `$config['connection']` или вы можете установить его через `setDatabaseConnection()` (v0.4.1). 

```php
$pdo_connection = new PDO('sqlite:test.db'); // для примера
$user = new User($pdo_connection);
// или
$user = new User(null, [ 'connection' => $pdo_connection ]);
// или
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Если вы хотите избежать установки `$database_connection` каждый раз при вызове active record, есть способы обойти это!

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

// И теперь аргументы не требуются!
$user = new User();
```

> **Примечание:** Если вы планируете unit-тестирование, делать это таким образом может добавить некоторые вызовы для unit-тестирования, но в целом, поскольку вы можете инжектировать ваше 
соединение с `setDatabaseConnection()` или `$config['connection']`, это не так плохо.

Если вам нужно обновить соединение с базой данных, например, если вы запускаете длительный CLI-скрипт и нужно обновлять соединение каждые несколько минут, вы можете переустановить соединение с `$your_record->setDatabaseConnection($pdo_connection)`.

## Вклад

Пожалуйста, сделайте. :D

### Настройка

При вкладе убедитесь, что вы запускаете `composer test-coverage` для поддержания 100% покрытия тестами (это не настоящее покрытие unit-тестами, больше как интеграционное тестирование).

Также убедитесь, что вы запускаете `composer beautify` и `composer phpcs` для исправления любых ошибок линтинга.

## Лицензия

MIT