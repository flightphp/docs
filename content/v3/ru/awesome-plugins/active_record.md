# Flight Active Record 

Активная запись — это сопоставление сущности базы данных с объектом PHP. Проще говоря, если у вас есть таблица users в вашей базе данных, вы можете "преобразовать" строку из этой таблицы в класс `User` и объект `$user` в вашей кодовой базе. Смотрите [базовый пример](#basic-example).

Нажмите [здесь](https://github.com/flightphp/active-record) для репозитория на GitHub.

## Базовый пример

Предположим, у вас есть следующая таблица:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Теперь вы можете создать новый класс для представления этой таблицы:

```php
/**
 * Класс ActiveRecord обычно в единственном числе
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
		// вы можете сделать это так
		parent::__construct($database_connection, 'users');
		// или так
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Теперь смотрите, как происходит волшебство!

```php
// для sqlite
$database_connection = new PDO('sqlite:test.db'); // это просто для примера, вы, вероятно, будете использовать реальное подключение к базе данных

// для mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// или mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// или mysqli с процедурным созданием
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
// здесь нельзя использовать $user->save(), иначе он подумает, что это обновление!

echo $user->id; // 2
```

И это было так просто — добавить нового пользователя! Теперь, когда в базе данных есть строка пользователя, как её извлечь?

```php
$user->find(1); // найти id = 1 в базе данных и вернуть его.
echo $user->name; // 'Bobby Tables'
```

А что, если вы хотите найти всех пользователей?

```php
$users = $user->findAll();
```

А как насчёт определённого условия?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Видите, как это весело? Давайте установим и начнём!

## Установка

Просто установите с помощью Composer

```php
composer require flightphp/active-record 
```

## Использование

Это можно использовать как отдельную библиотеку или с PHP-фреймворком Flight. Полностью на ваше усмотрение.

### Автономное использование
Просто убедитесь, что вы передаёте PDO-соединение в конструктор.

```php
$pdo_connection = new PDO('sqlite:test.db'); // это просто для примера, вы, вероятно, будете использовать реальное подключение к базе данных

$User = new User($pdo_connection);
```

> Не хотите всегда устанавливать соединение с базой данных в конструкторе? Смотрите [Управление подключением к базе данных](#database-connection-management) для других идей!

### Регистрация как метода в Flight
Если вы используете PHP-фреймворк Flight, вы можете зарегистрировать класс ActiveRecord как сервис, но, честно говоря, вам не обязательно это делать.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// затем вы можете использовать его так в контроллере, функции и т.д.

Flight::user()->find(1);
```

## Методы `runway`

[runway](/awesome-plugins/runway) — это CLI-инструмент для Flight, который имеет специальную команду для этой библиотеки. 

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
 * @link https://docs.flightphp.com/en/v3/awesome-plugins/active-record
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
     * @var array $relations Установите связи для модели
     *   https://docs.flightphp.com/en/v3/awesome-plugins/active-record#relationships
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

## CRUD-функции

#### `find($id = null) : boolean|ActiveRecord`

Находит одну запись и присваивает её текущему объекту. Если вы передадите `$id` какого-либо вида, он выполнит поиск по первичному ключу с этим значением. Если ничего не передано, он просто найдёт первую запись в таблице.

Дополнительно вы можете передать ему другие вспомогательные методы для запроса к таблице.

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

#### `first(): ActiveRecord` (v0.8.0)

Находит первую запись, соответствующую вашим условиям. Если вы не задали порядок, он упорядочивает по первичному ключу по возрастанию. Если ничего не совпадает, вы получаете запись обратно негидратированной, поэтому проверьте `isHydrated()`, если не уверены, что что-то вернулось.

```php
$user->eq('status', 'active')->first();
```

#### `last(): ActiveRecord` (v0.8.0)

То же, что и `first()`, но упорядочивает по первичному ключу по убыванию. Удобно для запросов "дай мне самую новую".

```php
$user->eq('status', 'active')->last();
```

#### `count(): int` (v0.8.0)

Подсчитывает строки, соответствующие вашим текущим условиям. Если у вас есть `groupBy()` в запросе, `count()` намеренно игнорирует его. Одиночный скалярный подсчёт не может представить одну строку на группу.

```php
$user->count();
$user->eq('status', 'active')->count();
```

#### `exists(): bool` (v0.8.0)

Возвращает `true`, если какая-либо запись соответствует вашим условиям. Под капотом выполняется дешёвый `SELECT 1 ... LIMIT 1`.

```php
$user->eq('name', 'Bobby')->exists(); // true
```

#### `pluck(string $column): array` (v0.8.0)

Возвращает плоский массив значений из одного столбца вместо гидратации кучи объектов. Объедините с `distinct()`, чтобы получить уникальные значения.

```php
$user->pluck('name'); // [ 'Bobby', 'Joseph' ]
$user->distinct()->pluck('status'); // [ 'active', 'inactive' ]
```

#### `ids(): array` (v0.8.0)

Сокращение для `pluck()` по первичному ключу.

```php
$user->gt('id', 0)->ids(); // [ 1, 2, 3 ]
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

Если у вас текстовый первичный ключ (например, UUID), вы можете установить значение первичного ключа перед вставкой одним из двух способов.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // или $user->save();
```

или вы можете автоматически сгенерировать первичный ключ через события.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// вы также можете установить primaryKey таким образом вместо массива выше.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // или как вам нужно генерировать ваши уникальные идентификаторы
	}
}
```

Если вы не установите первичный ключ перед вставкой, он будет установлен в `rowid`, и база данных сгенерирует его для вас, но он не сохранится, потому что это поле может не существовать в вашей таблице. Вот почему рекомендуется использовать событие для автоматической обработки этого.

#### `update(): boolean|ActiveRecord`

Обновляет текущую запись в базе данных.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `updateAttribute(string $name, mixed $value): ActiveRecord` (v0.8.0)

Обновляет один столбец в загруженной записи и сохраняет его. Это сокращение для `$user->dirty([ 'name' => $value ])->update()`. Для этого нужна загруженная запись.

```php
$user->find(1);
$user->updateAttribute('name', 'New Name');
```

#### `save(): boolean|ActiveRecord`

Вставляет или обновляет текущую запись в базе данных. Если у записи есть id, она обновится, иначе вставится.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Примечание:** Если у вас определены связи в классе, он также рекурсивно сохранит эти связи, если они были определены, созданы и имеют грязные данные для обновления. (v0.4.0 и выше)

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

#### `updateAll(array $attributes, bool $allowEmptyConditions = false): int` (v0.8.0)

Обновляет каждую запись, соответствующую вашим условиям, в одном запросе. Записи не гидратируются, и события не срабатывают — именно поэтому это быстро. Возвращает количество затронутых строк.

Он отказывается работать без условий WHERE, если вы не передадите `true` для второго аргумента. Ваше будущее "я" говорит спасибо.

```php
$user->eq('status', 'inactive')->updateAll([ 'status' => 'active' ]);

// да, вы действительно хотите обновить каждую строку в таблице
$user->updateAll([ 'status' => 'active' ], true);
```

#### `deleteAll(bool $allowEmptyConditions = false): int` (v0.8.0)

Удаляет каждую запись, соответствующую вашим условиям, в одном запросе. Та же история, что и с `updateAll()`: без гидратации, без событий, и требует условий WHERE, если вы не передадите `true`. Возвращает количество удалённых строк. Используйте с осторожностью!

```php
$user->eq('status', 'deleted')->deleteAll();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Грязные данные относятся к данным, которые были изменены в записи.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// на данный момент ничего не является "грязным".

$user->email = 'test@example.com'; // теперь email считается "грязным", так как он изменён.
$user->update();
// теперь нет данных, которые являются грязными, потому что они были обновлены и сохранены в базе данных

$user->password = password_hash()'newpassword'); // теперь это грязное
$user->dirty(); // передача ничего не очистит все грязные записи.
$user->update(); // ничего не обновится, так как ничего не было захвачено как грязное.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // и имя, и пароль обновлены.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Это псевдоним для метода `dirty()`. Немного яснее, что вы делаете.

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

Сбрасывает текущую запись в её начальное состояние. Это очень хорошо использовать в циклических поведениях.
Если вы передадите `true`, он также сбросит данные запроса, которые использовались для поиска текущего объекта (поведение по умолчанию).

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

После выполнения методов `find()`, `findAll()`, `insert()`, `update()` или `save()` вы можете получить SQL, который был построен, и использовать его для отладки.

## Транзакции

Нужно выполнить несколько записей, которые все должны успешно завершиться вместе? Оберните их в `transaction()` (v0.8.0). Передайте ему callable, и запись передаётся в качестве аргумента. Если callable выбрасывает исключение, всё откатывается, и исключение перебрасывается для вас. В противном случае он фиксирует и возвращает то, что вернул ваш callable.

```php
$user->transaction(function ($user) {
	$user->name = 'Bobby Tables';
	$user->password = password_hash('correct horse battery staple');
	$user->insert();

	$user->email = 'bobby@example.com';
	$user->update();
	// здесь происходит коммит, если ничего не выброшено
});
```

Вложенные транзакции не поддерживаются (нет savepoints), поэтому держите их плоскими.

## Методы SQL-запросов
#### `select(string $field1 [, string $field2 ... ])`

Вы можете выбрать только несколько столбцов в таблице, если хотите (это более производительно на очень широких таблицах с множеством столбцов)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Технически вы можете выбрать и другую таблицу! Почему бы и нет?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Вы даже можете присоединиться к другой таблице в базе данных.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Вы можете установить некоторые пользовательские аргументы where (вы не можете устанавливать параметры в этом операторе where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Примечание по безопасности** - Вас может соблазнить сделать что-то вроде `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. ПОЖАЛУЙСТА, НЕ ДЕЛАЙТЕ ЭТОГО!!! Это уязвимо для того, что известно как атаки SQL-инъекций. В интернете есть много статей, пожалуйста, загуглите "sql injection attacks php", и вы найдёте много статей на эту тему. Правильный способ обработки этого с помощью этой библиотеки — вместо этого метода `where()` вы должны сделать что-то вроде `$user->eq('id', $id)->eq('name', $name)->find();`. Если вам абсолютно необходимо сделать это, библиотека `PDO` имеет `$pdo->quote($var)` для экранирования. Только после использования `quote()` вы можете использовать его в операторе `where()`.

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

#### `orderByColumn(string $column, string $direction = 'ASC')` (v0.7.2)

`order()` и `orderBy()` принимают необработанные фрагменты SQL, что нормально, когда вы жёстко кодируете `'name DESC'`. Если имя столбца поступает из пользовательского ввода (например, сортируемый заголовок таблицы), используйте вместо этого `orderByColumn()`. Разрешены только простые имена столбцов и пути `table.column`, а направление должно быть `ASC` или `DESC`, так что вводить нечего.

```php
// $sortColumn приходит из запроса
$user->orderByColumn($sortColumn, 'DESC')->findAll();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Ограничьте количество возвращаемых записей. Если задан второй int, он будет offset, limit, как в SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

#### `distinct()` (v0.8.0)

Добавляет `DISTINCT` к вашему следующему запросу. Это работает с обычным select и с `pluck()`. `count()` игнорирует его, так как добавление `DISTINCT` к одной агрегатной строке ничего не делает.

```php
$user->distinct()->pluck('status'); // [ 'active', 'inactive' ]
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

Возможно обернуть ваши условия в оператор OR. Это делается либо с помощью методов `startWrap()` и `endWrap()`, либо путём заполнения 3-го параметра условия после поля и значения.

```php
// Метод 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// Это вычислится как `id = 1 AND (name = 'demo' OR name = 'test')`

// Метод 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// Это вычислится как `id = 1 OR name = 'demo'`
```

## Области видимости

Области видимости (v0.8.0) — это переиспользуемые цепочки запросов, определённые как обычные методы экземпляра вашего класса, возвращающие `$this`. Как только вы написали одну, она связывается как любой другой метод запроса.

```php
class User extends flight\ActiveRecord {

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	public function active(): self
	{
		return $this->eq('status', 'active');
	}

	public function recent(int $days = 7): self
	{
		return $this->ge('created_at', date('Y-m-d', strtotime("-{$days} days")));
	}
}

// и теперь ваши запросы читаются как предложения
(new User($pdo_connection))->active()->recent(30)->findAll();
```

Вы также можете вызвать область видимости по имени с помощью `scope()`, что удобно, когда имя области видимости приходит откуда-то ещё из вашего кода. Он выбрасывает `BadMethodCallException`, если метод не существует.

```php
$user->scope('active')->findAll();
$user->scope('recent', 30)->findAll();
```

## Связи
Вы можете установить несколько видов связей с помощью этой библиотеки. Вы можете установить связи один->многие и один->один между таблицами. Это требует небольшой дополнительной настройки в классе заранее.

Настройка массива `$relations` не сложна, но угадывание правильного синтаксиса может быть запутанным.

```php
protected array $relations = [
	// вы можете назвать ключ как угодно. Имя ActiveRecord, вероятно, хорошо. Например: user, contact, client
	'user' => [
		// обязательно
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // это тип связи

		// обязательно
		'Some_Class', // это "другой" класс ActiveRecord, на который будет ссылаться

		// обязательно
		// в зависимости от типа связи
		// self::HAS_ONE = внешний ключ, который ссылается на соединение
		// self::HAS_MANY = внешний ключ, который ссылается на соединение
		// self::BELONGS_TO = локальный ключ, который ссылается на соединение
		'local_or_foreign_key',
		// просто для информации, это также присоединяется только к первичному ключу "другой" модели

		// необязательно
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // дополнительные условия, которые вы хотите при соединении связи
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// необязательно
		'back_reference_name' // это если вы хотите обратную ссылку этой связи на саму себя. Например: $user->contact->user;
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

// найти самого последнего пользователя.
$user->notNull('id')->orderBy('id desc')->find();

// получить контакты, используя связь:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// или мы можем пойти другим путём.
$contact = new Contact();

// найти один контакт
$contact->find();

// получить пользователя, используя связь:
echo $contact->user->name; // это имя пользователя
```

Довольно круто, да?

### Жадная загрузка

#### Обзор
Жадная загрузка решает проблему N+1 запросов, загружая связи заранее. Вместо выполнения отдельного запроса для связей каждой записи, жадная загрузка извлекает все связанные данные всего за один дополнительный запрос на связь.

> **Примечание:** Жадная загрузка доступна только для v0.7.0 и выше.

#### Базовое использование
Используйте метод `with()`, чтобы указать, какие связи загружать жадно:
```php
// Загрузить пользователей с их контактами за 2 запроса вместо N+1
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    foreach ($u->contacts as $contact) {
        echo $contact->email; // Никаких дополнительных запросов!
    }
}
```

#### Несколько связей
Загружайте несколько связей одновременно:
```php
$users = $user->with(['contacts', 'profile', 'settings'])->findAll();
```

#### Типы связей

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
// Пользователь и все его контакты загружены за 2 запроса
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
$users = $user->with('contacts')->findAll(); // 2 запроса всего
foreach ($users as $u) {
    $contacts = $u->contacts; // 0 дополнительных запросов!
}
// Итого: 2 запроса (1 для пользователей + 1 для всех контактов)
```
Для 10 пользователей это сокращает количество запросов с 11 до 2 — снижение на 82%!

#### Важные примечания
- Жадная загрузка полностью необязательна — ленивая загрузка по-прежнему работает как раньше
- Уже загруженные связи автоматически пропускаются
- Обратные ссылки работают с жадной загрузкой
- Обратные вызовы связей учитываются во время жадной загрузки

#### Ограничения
- Вложенная жадная загрузка (например, 
with(['contacts.addresses'])
) в настоящее время не поддерживается
- Ограничения жадной загрузки через замыкания не поддерживаются в этой версии

## Установка пользовательских данных
Иногда вам может понадобиться прикрепить что-то уникальное к вашему ActiveRecord, например, пользовательский расчёт, который может быть проще просто прикрепить к объекту, который затем будет передан, скажем, в шаблон.

#### `setCustomData(string $field, mixed $value)`
Вы прикрепляете пользовательские данные с помощью метода `setCustomData()`.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

А затем вы просто ссылаетесь на них как на обычное свойство объекта.

```php
echo $user->page_view_count;
```

## Метки времени

Если ваша таблица имеет столбцы `created_at` и `updated_at`, вы можете позволить библиотеке заполнить их за вас (v0.8.0). Установите `protected bool $timestamps = true;` в вашем классе, и она установит `created_at` и `updated_at` при вставке, и `updated_at` при обновлении. Формат — `Y-m-d H:i:s`. Если вы установите любой из столбцов самостоятельно, библиотека оставит ваше значение без изменений.

```php
class User extends flight\ActiveRecord {
	protected bool $timestamps = true;

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}
}
```

Ваша таблица действительно должна иметь эти столбцы, иначе вставки и обновления завершатся ошибкой.

## События

Ещё одна суперклассная функция этой библиотеки — события. События запускаются в определённые моменты на основе определённых методов, которые вы вызываете. Они очень-очень полезны для автоматической настройки данных для вас.

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

	protected function onConstruct(self $self, array &$config) { // не забудьте ссылку &
		// вы можете сделать это, чтобы автоматически установить соединение
		$config['connection'] = Flight::db();
		// или так
		$self->transformAndPersistConnection(Flight::db());
		
		// Вы также можете установить имя таблицы таким образом.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Это, вероятно, полезно только в том случае, если вам нужна манипуляция запросом каждый раз.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// всегда выполнять id >= 0, если это ваше
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Это, вероятно, более полезно, если вам всегда нужно выполнять некоторую логику каждый раз, когда эта запись извлекается. Нужно что-то расшифровать? Нужно выполнить пользовательский запрос подсчёта каждый раз (непроизводительно, но как угодно)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// расшифровка чего-либо
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// может быть, сохранение чего-то пользовательского, например, запроса???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Это, вероятно, полезно только в том случае, если вам нужна манипуляция запросом каждый раз.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// всегда выполнять id >= 0, если это ваше
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Похоже на `afterFind()`, но вы можете сделать это со всеми записями вместо этого!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// сделайте что-нибудь крутое, как в afterFind()
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

Может быть, у вас есть вариант использования для изменения данных после вставки?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// делайте что хотите
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// или что-то ещё....
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

Может быть, у вас есть вариант использования для изменения данных после обновления?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// делайте что хотите
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// или что-то ещё....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Это полезно, если вы хотите, чтобы события происходили как при вставках, так и при обновлениях. Я избавлю вас от долгих объяснений, но я уверен, вы можете догадаться, что это такое.

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

Не уверен, что вы захотите здесь сделать, но никаких суждений! Вперёд!

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

## Управление подключением к базе данных

Когда вы используете эту библиотеку, вы можете установить соединение с базой данных несколькими способами. Вы можете установить соединение в конструкторе, вы можете установить его через переменную конфигурации `$config['connection']` или вы можете установить его через `setDatabaseConnection()` (v0.4.1). 

```php
$pdo_connection = new PDO('sqlite:test.db'); // например
$user = new User($pdo_connection);
// или
$user = new User(null, [ 'connection' => $pdo_connection ]);
// или
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Если вы хотите избежать постоянной установки `$database_connection` каждый раз, когда вы вызываете активную запись, есть способы обойти это!

```php
// index.php или bootstrap.php
// Зарегистрируйте это как зарегистрированный класс в Flight
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

> **Примечание:** Если вы планируете модульное тестирование, такой подход может добавить некоторые сложности в модульное тестирование, но в целом, поскольку вы можете внедрить ваше соединение с помощью `setDatabaseConnection()` или `$config['connection']`, это не так уж плохо.

Если вам нужно обновить соединение с базой данных, например, если вы запускаете длительный CLI-скрипт и вам нужно обновлять соединение время от времени, вы можете переустановить соединение с помощью `$your_record->setDatabaseConnection($pdo_connection)`.

## Участие в разработке

Пожалуйста, участвуйте. :D

### Настройка

Когда вы вносите свой вклад, убедитесь, что вы запускаете `composer test-coverage`, чтобы поддерживать 100% покрытие тестами (это не истинное покрытие модульными тестами, скорее интеграционное тестирование).

Также убедитесь, что вы запускаете `composer beautify` и `composer phpcs`, чтобы исправить любые ошибки линтинга.

## Лицензия

MIT