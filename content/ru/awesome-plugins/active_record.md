# Активная запись FlightPHP

Активная запись - это отображение сущности базы данных на объект PHP. Проще говоря, если у вас есть таблица пользователей в вашей базе данных, вы можете "преобразовать" строку в этой таблице в класс `User` и объект `$user` в вашем кодовой базе. См. [простой пример](#Простой-пример).

## Простой пример

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
 * Класс ActiveRecord обычно существует в единственном числе
 * 
 * Настоятельно рекомендуется добавить свойства таблицы в виде комментариев здесь
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// можно установить таким образом
		parent::__construct($database_connection, 'users');
		// или таким образом
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Теперь посмотрим, как это работает!

```php
// для sqlite
$database_connection = new PDO('sqlite:test.db'); // это просто для примера, вы вероятно будете использовать реальное соединение с базой данных

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
// не здесь использовать $user->save() или это будет понимать как обновление!

echo $user->id; // 2
```

И вот насколько просто добавить нового пользователя! Теперь, когда в базе данных есть запись пользователя, как ее извлечь?

```php
$user->find(1); // найти id = 1 в базе данных и вернуть его.
echo $user->name; // 'Bobby Tables'
```

Что если вы хотите найти всех пользователей?

```php
$users = $user->findAll();
```

Что насчет определенного условия?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Весело, не правда ли? Давайте установим это и начнем!

## Установка

Просто установите с помощью Composer

```php
composer require flightphp/active-record 
```

## Использование

Это можно использовать как автономную библиотеку или с фреймворком Flight PHP. Полностью на ваше усмотрение.

### Автономный режим
Достаточно передать соединение PDO в конструктор.

```php
$pdo_connection = new PDO('sqlite:test.db'); // это просто для примера, вы вероятно будете использовать реальное соединение с базой данных

$User = new User($pdo_connection);
```

### Фреймворк Flight PHP
Если вы используете фреймворк Flight PHP, вы можете зарегистрировать класс ActiveRecord в качестве сервиса (но честно говоря, вам не обязательно).

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// затем вы можете использовать его таким образом в контроллере, функции и т. д.

Flight::user()->find(1);
```

## API Reference
### Функции CRUD

#### `find($id = null) : boolean|ActiveRecord`

Найти одну запись и присвоить ее текущему объекту. Если вы передаете `$id`, он выполнит поиск по первичному ключу с этим значением. Если ничего не передается, он просто найдет первую запись в таблице.

Кроме того, вы можете передать ему другие вспомогательные методы для запроса вашей таблицы.

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

Необработанные данные относятся к данным, которые были изменены в записи.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// на данный момент ничего не "грязное".

$user->email = 'test@example.com'; // теперь электронная почта считается "грязной", поскольку она изменена.
$user->update();
// в настоящее время нет данных, которые являются грязными, так как они были обновлены и сохранены в базе данных

$user->password = password_hash()'newpassword'); // теперь это грязно
$user->dirty(); // передача ничего не приведет к очистке всех грязных записей.
$user->update(); // ничего не будет обновлено, потому что ничего не было захвачено в грязном состоянии.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // оба имени и пароля обновлены.
```

### Методы запросов SQL
#### `select(string $field1 [, string $field2 ... ])`

Вы можете выбирать только некоторые столбцы в таблице, если хотите (это более производительно на очень широких таблицах с множеством столбцов)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Вы также можете выбрать другую таблицу! Почему бы и нет?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Вы также можете присоединиться к другой таблице в базе данных.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Вы можете установить некоторые пользовательские аргументы where (вы не можете установить параметры в этом операторе where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Примечание о безопасности** - Вас может побудить сделать что-то вроде `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Пожалуйста, НЕ ДЕЛАЙТЕ ЭТО!!! Это можно подвергнуться т.н. атакам внедрения SQL. Существует много статей в Интернете, пожалуйста, загуглите "атаки SQL-инъекции PHP" и вы найдете много статей на эту тему. Правильный способ обработки этого с помощью этой библиотеки - вместо этого `where()` метода, вы бы сделали что-то вроде `$user->eq('id', $id)->eq('name', $name)->find();`

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Группируйте ваши результаты по определенному условию.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Сортируйте возвращаемый запрос определенным способом.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Ограничить количество возвращаемых записей. Если передано второе целое число, это будет смещение, предел, как в SQL.

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
Вы можете устанавливать несколько видов отношений, используя эту библиотеку. Вы можете установить отношения один -> много и один -> одно между таблицами. Для этого требуется небольшая дополнительная настройка в классе заранее.

Установка массива `$relations` несложна, но правильный синтаксис может быть запутанным.

```php
protected array $relations = [
	// вы можете назвать ключ по вашему усмотрению. Название ActiveRecord, вероятно, хорошее. Например, user, contact, client
	'whatever_active_record' => [
		// обязательно
		self::HAS_ONE, // это тип отношения

		// обязательно
		'Some_Class', // это класс "другой" ActiveRecord, которая будет ссылаться на этот

		// обязательно
		'local_key', // это локальный ключ, который ссылается на соединение.
		// кстати, это также присоединяется только к первичному ключу "другой" модели

		// необязательно
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // пользовательские методы, которые вы хотите выполнить. [] если вы не хотите ничего.

		// необязательно
		'back_reference_name' // это, если вы хотите обратную ссылку на это отношение обратно к себе. Например, $user->contact->user;
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

Теперь мы настроили ссылки, чтобы мы могли использовать их очень легко!

```php
$user = new User($pdo_connection);

// найти последнего пользователя.
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

Довольно круто, не так ли?

### Установка пользовательских данных
Иногда вам может понадобиться прикрепить что-то уникальное к вашему ActiveRecord, такое как пользовательский расчет, который может быть проще всего присоединить к объекту, который затем будет передан, скажем, в шаблон.

#### `setCustomData(string $field, mixed $value)`
Вы добавляете пользовательские данные с помощью метода `setCustomData()`.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

И просто ссылайтесь на него как на обычное свойство объекта.

```php
echo $user->page_view_count;
```

### События

Еще одна потрясающая возможность этой библиотеки - это события. События срабатывают в определенные моменты на основе определенных вами методов. Они очень полезны для автоматической настройки данных для вас.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Это действительно полезно, если вам нужно установить соединение по умолчанию или что-то в этом роде.

```php
// index.php или bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config```markdown
# FlightPHP Активная запись

Активная запись - это отображение сущности базы данных на объект PHP. Проще говоря, если у вас есть таблица пользователей в вашей базе данных, вы можете "преобразовать" строку в этой таблице в класс `User` и объект `$user` в вашем кодовой базе. См. [простой пример](#basic-example).

## Простой пример

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
 * Класс ActiveRecord обычно существует в единственном числе
 * 
 * Настоятельно рекомендуется добавить свойства таблицы в виде комментариев здесь
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// можно установить таким образом
		parent::__construct($database_connection, 'users');
		// или таким образом
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Теперь посмотрим, как это работает!

```php
// для sqlite
$database_connection = new PDO('sqlite:test.db'); // это просто для примера, вы вероятно будете использовать реальное соединение с базой данных

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
// не здесь использовать $user->save() или это будет понимать как обновление!

echo $user->id; // 2
```

И вот насколько просто добавить нового пользователя! Теперь, когда в базе данных есть запись пользователя, как ее извлечь?

```php
$user->find(1); // найти id = 1 в базе данных и вернуть его.
echo $user->name; // 'Bobby Tables'
```

Что если вы хотите найти всех пользователей?

```php
$users = $user->findAll();
```

Что насчет определенного условия?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Весело, не правда ли? Давайте установим это и начнем!

## Установка

Просто установите с помощью Composer

```php
composer require flightphp/active-record 
```

## Использование

Это можно использовать как автономную библиотеку или с фреймворком Flight PHP. Полностью на ваше усмотрение.

### Автономный режим
Достаточно передать соединение PDO в конструктор.

```php
$pdo_connection = new PDO('sqlite:test.db'); // это просто для примера, вы вероятно будете использовать реальное соединение с базой данных

$User = new User($pdo_connection);
```

### Фреймворк Flight PHP
Если вы используете фреймворк Flight PHP, вы можете зарегистрировать класс ActiveRecord в качестве сервиса (но честно говоря, вам не обязательно).

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// затем вы можете использовать его таким образом в контроллере, функции и т. д.

Flight::user()->find(1);
```

## API Reference
### Функции CRUD

#### `find($id = null) : boolean|ActiveRecord`

Найти одну запись и присвоить ее текущему объекту. Если вы передаете `$id`, он выполнит поиск по первичному ключу с этим значением. Если ничего не передается, он просто найдет первую запись в таблице.

Кроме того, вы можете передать ему другие вспомогательные методы для запроса вашей таблицы.

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

...

#### `beforeDelete(ActiveRecord $ActiveRecord)/afterDelete(ActiveRecord $ActiveRecord)`

Несмотря на то, что здесь не приведен пример кода, это действие может вызываться в определенные моменты в процессе удаления записи.

## Contributing

Милости прошу.

### Setup

Когда вы вносите вклад, убедитесь, что вы запускаете `composer test-coverage` для поддержания 100% охвата тестов (это не точное покрытие тестами, скорее интеграционное тестирование).

Также убедитесь, что вы запускаете `composer beautify` и `composer phpcs` для исправления ошибок линтинга.

## License

MIT
```