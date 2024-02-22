# FlightPHP Активная запись 

Активная запись - это отображение сущности базы данных на объект PHP. Проще говоря, если у вас есть таблица пользователей в вашей базе данных, вы можете "перевести" строку в этой таблице в класс `User` и объект `$user` в вашем кодовой базе. См. [простой пример](#basic-example).

## Простой пример

Допустим, у вас есть следующая таблица:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Теперь вы можете настроить новый класс, чтобы представить эту таблицу:

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
class Пользователь расширяет flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// вы можете установить это так
		parent::__construct($database_connection, 'users');
		// или так
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Теперь посмотрите, как это происходит!

```php
// для sqlite
$database_connection = new PDO('sqlite:test.db'); // это просто для примера, вероятно, вы будете использовать реальное подключение к базе данных

// для mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'имя_пользователя', 'пароль');

// или mysqli
$database_connection = new mysqli('localhost', 'имя_пользователя', 'пароль', 'test_db');
// или mysqli с созданием не объекта
$database_connection = mysqli_connect('localhost', 'имя_пользователя', 'пароль', 'test_db');

$user = new User($database_connection);
$user->name = 'Бобби Тейблс';
$user->password = password_hash('какой-то крутой пароль');
$user->insert();
// или $user->save();

echo $user->id; // 1

$user->name = 'Джозеф Мамма';
$user->password = password_hash('еще один крутой пароль!!!');
$user->insert();
// нельзя использовать $user->save() здесь, иначе он подумает, что это обновление!

echo $user->id; // 2
```

И это было настолько легко добавить нового пользователя! Теперь, когда есть строка пользователя в базе данных, как её извлечь?

```php
$user->find(1); // найти id = 1 в базе данных и вернуть его.
echo $user->name; // 'Бобби Тейблс'
```

Что, если вы хотите найти всех пользователей?

```php
$users = $user->findAll();
```

Что насчет определенного условия?

```php
$users = $user->like('name', '%мамма%')->findAll();
```

Весело, не правда ли? Давайте установим это и начнем!

## Установка

Просто установите с помощью Composer

```php
composer require flightphp/active-record 
```

## Использование

Это можно использовать как самостоятельную библиотеку или с фреймворком Flight PHP. Вам решать.

### Независимое использование
Просто убедитесь, что передаете соединение PDO конструктору.

```php
$pdo_connection = new PDO('sqlite:test.db'); // это просто для примера, вероятно, вы будете использовать реальное подключение к базе данных

$User = new User($pdo_connection);
```

### Фреймворк Flight PHP
Если вы используете фреймворк Flight PHP, вы можете зарегистрировать класс ActiveRecord как сервис (но, честно говоря, вам это и не обязательно).

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// затем вы можете использовать это так в контроллере, функции и т. д.

Flight::user()->find(1);
```

## Справочник по API
### Функции CRUD

#### `find($id = null) : boolean|ActiveRecord`

Найдите одну запись и присвойте ее текущему объекту. Если вы передаете `$id` какого-либо типа, он выполнит поиск по первичному ключу с этим значением. Если ничего не передается, он просто найдет первую запись в таблице.

Кроме того, вы можете передать другие вспомогательные методы для запроса вашей таблицы.

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
$user->delete();
```

Вы также можете удалить несколько записей, выполнив поиск заранее.

```php
$user->like('name', 'Боб%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Грязные данные относятся к данным, которые были изменены в записи.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// на данный момент ничего не является "грязным".
$user->email = 'test@example.com'; // теперь электронная почта считается "грязной", так как она изменилась.
$user->update();
// теперь нет данных, которые считаются "грязными", потому что они были обновлены и сохранены в базе данных

$user->password = password_hash()'новый пароль'); // теперь это "грязные" данные
$user->dirty(); // передача ничего не очистит все грязные записи.
$user->update(); // ничего не обновится, потому что ничего не было зафиксировано как "грязное".

$user->dirty([ 'name' => 'что-то', 'password' => password_hash('другой пароль') ]);
$user->update(); // оба имени и пароля обновлены.
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Сбрасывает текущую запись до ее начального состояния. Это действительно удобно использовать в циклическом поведении. Если вы передаете `true`, он также сброcит данные запроса, которые были использованы для поиска текущего объекта (поведение по умолчанию).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // начните с чистого листа
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

### Методы запроса SQL
#### `select(string $field1 [, string $field2 ... ])`

Вы можете выбрать только несколько столбцов в таблице, если хотите (это более производительно на очень широких таблицах с многими столбцами)

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
$user->join('контакты', 'контакты.user_id = пользователи.id')->find();
```

#### `where(string $where_conditions)`

Вы можете установить некоторые пользовательские условия where (в этом операторе where вы не можете устанавливать параметры)

```php
$user->where('id=1 AND name="демо"')->find();
```

**Примечание по безопасности** - Вы могли соблазниться сделать что-то вроде `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Пожалуйста, НЕ ДЕЛАЙТЕ ЭТО!!! Это подвержено так называемым атакам SQL-инъекций. В интернете есть много статей, пожалуйста, погуглите "атаки SQL-инъекций php", и вы найдете много статей по этой теме. Правильный способ обработки этого с использованием этой библиотеки - вместо этого метода `where()`, вы бы сделали что-то вроде `$user->eq('id', $id)->eq('name', $name)->find();`

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Группируйте свои результаты по конкретному условию.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Сортируйте возвращенный запрос по определенному пути.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Ограничивает количество возвращаемых записей. Если передан второй int, это будет смещение, предел как в SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

### Условия WHERE
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

Где `поле = $значение`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

Где `поле <> $значение`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

Где `поле IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

Где `поле IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

Где `поле > $значение`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

Где `поле < $значение`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

Где `поле >= $значение`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

Где `поле <= $значение`

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

Где `поле LIKE $значение` или `поле NOT LIKE $значение`

```php
$user->like('name', 'д')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

Где `поле IN($значение)` или `поле NOT IN($значение)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Где `поле BETWEEN $значение AND $значение1`

```php
$user->between('id', [1, 2])->find();
```

### Отношения
Вы можете установить несколько видов отношений с помощью этой библиотеки. Вы можете установить отношения один-ко-многим и один-к-одному между таблицами. Для этого требуется немного дополнительной настройки в классе заранее.

Установка массива `$relations` не сложна, но угадывать правильный синтаксис может быть запутывающим.

```php
protected array $relations = [
	// вы можете назвать ключ как угодно. Название ActiveRecord, вероятно, хорошее. Например: пользователь, контакт, клиент
	'любая_активная_запись' => [
		// обязательно
		self::HAS_ONE, // это тип отношения

		// обязательно
		'Некий_Класс', // это "другой" класс ActiveRecord, на который будет ссылаться это отношение

		// обязательно
		'local_key', // это локальный ключ, который ссылается на соединение.
		// кстати, он также соединяется только с первичным ключом "другой" модели

		// необязательно
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // пользовательские методы, которые вы хотите выполнить. [] если не хотите ничего.

		// необязательно
		'back_reference_name' // если вы хотите обратиться к этому отношению обратно к самому себе, например: $пользователь->контакт->пользователь;
	];
]
```

```php
class User расширяет ActiveRecord{
	protected array $relations = [
		'контакты' => [ self::HAS_MANY, Contact::class, 'user_id' ],
		'контакт' => [ self::HAS_ONE, Contact::class, 'user_id' ],
	];

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}
}

class Contact расширяет ActiveRecord{
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

// получить контакты, используя отношение:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// или мы можем идти в другую сторону.
$contact = new Contact();

// найти один контакт
$contact->find();

// получить пользователя, использелая отношение:
echo $contact->user->name; // это имя пользователя
```

Довольно здорово, правда?

### Установка пользовательских данных
Иногда вам может потребоваться добавить что-то уникальное к вашей ActiveRecord, такое как пользовательский расчет, который может быть проще просто присоединить к объекту, который затем будет передан, скажем, шаблону.

#### `setCustomData(string $field, mixed $value)`
Вы присоединяете пользовательские данные методом `setCustomData()`.
```php
$user->setCustomData('просмотр_страницы_счётчик', $просмотр_страницы_количество);
```

И затем просто ссылайтесь на него как на обычное свойство объекта.

```php
echo $user->просмотр_страницы_счётчик;
```

### События

Еще одна потрясающая особенность этой библиотеки - это события. События вызываются в определенные моменты времени в зависимости от определенных методов, которые вы вызываете. Они очень полезны при настройке данных для вас автоматически.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Это действительно полезно, если вам нужно установить соединение по умолчанию или что-то подобное.

```php
// index.php или bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User расширяет flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // не забудьте ссылку на &
		// вы могли бы сделать так, чтобы автоматически устанавливалось соединение
		$config['connection'] = Flight::db();
		// или так
		$self->transformAndPersistConnection(Flight::db());
		
		// Вы также можете установить имя таблицы таким образом.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Это, вероятно, полезно только в случае манипулирования запросом каждый раз.

```php
class User расширяет flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// всегда запускать id >= 0, если вам это понравилось
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Этот метод, вероятно, более полезен, если вам всегда нужно выполнить какую-то логику каждый раз, когда эта запись выбирается. Вам нужно расшифровать что-то? Вам нужно выполнить нестандартный запрос на количество каждый раз (неэффективно, но как ваше)?

```php
class User расширяет flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// расшифровываем что-то
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		//, возможно, хранение что-то пользовательское, как запрос???
		$self->setCustomData('просмотр_счётчик', $self->select('COUNT(*) count')->from('Просмотры_пользователя')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Это, вероятно, полезно только в случае манипулирования запросом каждый раз.

```php
class User расширяет flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// всегда запускать id >= 0, если вам это понравилось
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Подобно `afterFind()`, но вы можете это применить ко всем записям вместо одной!

```php
class User расширяет flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// сделать что-то крутое, как послеFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

По-настоящему полезно, если вам нужно установить какие-то дефолтные значения каждый раз.

```php
class User расширяет flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// установить какие-то звучные значения по умолчанию
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

Может быть у вас есть случай для изменения данных после их вставки?

```php
class User расширяет flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// делайте, что вам удобно
		Flight::cache()->set('самый_последний_id_вставки', $self->id);
		// или что угодно....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

По-настоящему полезно, если вам нужно установить какие-то дефолтные значения каждый раз при обновлении.

```php
class User расширяет flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// установить какие-то звучные значения по умолчанию при обновлении
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Возможно, у вас есть случай для изменения данных после их обновления?

```php
class User расширяет flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// делайте, что вам удобно
		Flight::cache()->set('самый_последний_обновленный_id_пользователя', $self->id);
		// или что угодно....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Это полезно, если вы хотите, чтобы события происходили как при вставке, так и при обновлении. Я не буду давать подробные объяснения, но я уверен, что вы можете догадаться, что это такое.

```php
class User расширяет flight\ActiveRecord {
	
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

Не уверен, что вы хотели бы сделать здесь, но здесь нет суждений! Дерзайте!

```php
class User расширяет flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'Он был храбрым солдатом... :cry-face:';
	} 
}
```

## Contributing

Пожалуйста.

### Настройка

Когда вы делаете вклад, убедитесь, что запускаете `composer test-coverage`, чтобы поддерживать 100% покрытие тестов (это не истинное покрытие тестов, а скорее тестирование интеграции).

Также убедитесь, что запускаете `composer beautify` и `composer phpcs`, чтобы исправить любые ошибки линтинга.

## Лицензия

MIT