# FlightPHP Активная запись

Активная запись представляет собой привязку сущности базы данных к объекту PHP. Проще говоря, если у вас есть таблица пользователей в вашей базе данных, вы можете "перевести" строку в этой таблице в класс `User` и объект `$user` в вашем кодовой базе. Смотрите [пример простой версии](#basic-example).

## Простой Пример

Давайте предположим, у вас есть следующая таблица:

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
 * Класс ActiveRecord обычно является в единственном числе
 * 
 * Очень рекомендуется добавлять свойства таблицы в качестве комментариев здесь
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
		// или вот так
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Теперь наблюдайте волшебство!

```php
// для sqlite
$database_connection = new PDO('sqlite:test.db'); // это просто для примера, вероятно, вы бы использовали реальное соединение с базой данных

// для mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// или mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// или mysqli с созданием без использования объектов
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
// нельзя использовать $user->save() здесь, иначе он подумает, что это обновление!

echo $user->id; // 2
```

И это было так легко добавить нового пользователя! Теперь, когда есть строка пользователя в базе данных, как ее извлечь?

```php
$user->find(1); // найти id = 1 в базе данных и вернуть его.
echo $user->name; // 'Bobby Tables'
```

И если вы хотите найти всех пользователей?

```php
$users = $user->findAll();
```

Что насчет определенного условия?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Сколько же весело это! Давайте установим его и начнем!

## Установка

Просто установите с помощью Composer

```php
composer require flightphp/active-record 
```

## Использование

Это можно использовать как самостоятельную библиотеку или вместе с Flight PHP Framework. Полностью на ваше усмотрение.

### Самостоятельно
Просто убедитесь, что вы передаете соединение PDO в конструктор.

```php
$pdo_connection = new PDO('sqlite:test.db'); // это просто для примера, вероятно, вы бы использовали реальное соединение с базой данных

$User = new User($pdo_connection);
```

### Фреймворк Flight PHP
Если вы используете фреймворк Flight PHP, вы можете зарегистрировать класс ActiveRecord как службу (но честно говоря, это не обязательно).

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// затем вы можете использовать его вот так в контроллере, функции и т. д.

Flight::user()->find(1);
```

## API Справка
### Функции CRUD

#### `find($id = null) : boolean|ActiveRecord`

Найти одну запись и присвоить ее текущему объекту. Если вы передаете `$id` какого-либо вида, он выполнит поиск по первичному ключу с этим значением. Если ничего не передается, он просто найдет первую запись в таблице.

Дополнительно вы можете передать другие вспомогательные методы для запроса к вашей таблице.

```php
// найти запись с некоторыми условиями заранее
$user->notNull('password')->orderBy('id DESC')->find();

// найти запись по определенному идентификатору
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Находит все записи в указанной Вами таблице.

```php
$user->findAll();
```

#### `insert(): boolean|ActiveRecord`

Вставляет текущую запись в базу данных.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
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
$user->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Грязные данные относятся к данным, которые были изменены в записи.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// ничего "грязного" до этого момента.
$user->email = 'test@example.com'; // теперь электронная почта считается "грязной", так как она была изменена.
$user->update();
// теперь нет данных, которые являются "грязными", потому что они были обновлены и сохранены в базе данных

$user->password = password_hash()'newpassword'); // теперь это грязно
$user->dirty(); // передача ничего не очистит все грязные записи.
$user->update(); // ничего не будет обновлено, так как ничего не было учтено как грязное.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // оба имени и пароля обновлены.
```

### Методы запроса SQL
#### `select(string $field1 [, string $field2 ... ])`

Вы можете выбирать только несколько столбцов в таблице, если хотите (это более производительно на очень широких таблицах с множеством столбцов)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Также можно выбрать другую таблицу! Почему бы и нет?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Можно также присоединиться к другой таблице в базе данных.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Можно установить некоторые пользовательские аргументы where (в этом where-выражении нельзя устанавливать параметры)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Примечание по безопасности** - Вы могли бы попытаться сделать что-то вроде `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Пожалуйста, НЕ ДЕЛАЙТЕ ЭТО!!! Это подвержено так называемым атакам SQL-инъекций. Есть много статей онлайн, пожалуйста, погуглите "sql injection attacks php" и вы найдете много статей по этой теме. Правильный способ обработки этого с этой библиотекой состоит в том, что вместо этого метода `where()`, вы бы сделали что-то типа `$user->eq('id', $id)->eq('name', $name)->find();`

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Группировать ваши результаты по определенному условию.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Сортировать возвращаемый запрос определенным способом.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Ограничить количество возвращаемых записей. Если второй целочисленный аргумент предан, он будет смещением, как в SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

### Условия WHERE
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

### Отношения
Вы можете установить несколько видов отношений с помощью этой библиотеки. Вы можете установить отношения один->многие и один->один между таблицами. Для этого требуется небольшая дополнительная настройка в классе заранее.

Установка массива `$relations` не сложна, но неправильный синтаксис может вызвать путаницу.

```php
protected array $relations = [
	// вы можете назвать ключ как угодно. Имя ActiveRecord вероятно хорошее. Например: user, contact, client
	'whatever_active_record' => [
		// обязательно
		self::HAS_ONE, // это тип отношения

		// обязательно
		'Some_Class', // это "другой" класс ActiveRecord, на который будет ссылаться это отношение

		// обязательно
		'local_key', // это локальный ключ, который ссылается на соединение
		// просто для вашей информации, это также присоединяется только к первичному ключу "другой" модели

		// необязательно
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // пользовательские методы, которые вы хотите выполнить. [] если вы не хотите ничего.

		// необязательно
		'back_reference_name' // это, если вы хотите вернуться к этому отношению обратно к самому себе, например: $user->contact->user;
	];
]
```

```php
class User extends ActiveRecord {
	protected array $relations = [
		'contacts' => [ self::HAS_MANY, Contact::class, 'user_id' ],
		'contact' => [ self::HAS_ONE, Contact::class, 'user_id' ],
	];

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}
}

class Contact extends ActiveRecord {
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

// найти самого недавнего пользователя.
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

### Установка Нестандартных Данных
Иногда вам может понадобиться прикрепить что-то уникальное к вашему ActiveRecord, например, пользовательский расчет, который может быть проще просто прикрепить к объекту, который затем будет передан, скажем, в шаблон.

#### `setCustomData(string $field, mixed $value)`
Вы присоединяете пользовательские данные с помощью метода `setCustomData()`.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

Затем вы просто ссылаетесь на это как на обычное свойство объекта.

```php
echo $user->page_view_count;
```

### События

Еще одна потрясающая особенность этой библиотеки заключается в событиях. События срабатывают в определенные моменты времени, основанные на определенных вызовах методов. Они очень полезны для автоматической настройки данных для вас.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Это действительно полезно, если вам нужно установить соединение по умолчанию или что-то в этом роде.

```php
// index.php или bootstrap.php
Flight::register('db', 'PDO### Contributing

Пожалуйста, делайте.

### Настройка

При внесении вклада убедитесь, что запускаете `composer test-coverage`, чтобы поддерживать 100% покрытие тестами (это нестриктное покрытие модульными тестами, а скорее интеграционное тестирование).

Также убедитесь, что запускаете `composer beautify` и `composer phpcs` для исправления всех ошибок линтинга.

### Лицензия

MIT