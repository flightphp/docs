# Активная запись Flight

Активная запись - это отображение сущности базы данных на объект PHP. Проще говоря, если у вас есть таблица пользователей в вашей базе данных, вы можете "перевести" строку в этой таблице в класс `User` и объект `$user` в вашем коде. Смотрите [простой пример](#простой-пример).

Нажмите [здесь](https://github.com/flightphp/active-record) для репозитория на GitHub.

## Простой пример

Допустим, у вас есть следующая таблица:

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
 * Класс ActiveRecord обычно в единственном числе
 * 
 * Для наглядности рекомендуется добавить свойства таблицы в виде комментариев здесь
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// можно установить так
		parent::__construct($database_connection, 'users');
		// или так
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Теперь посмотрите, как это легко!

```php
// для sqlite
$database_connection = new PDO('sqlite:test.db'); // это просто пример, обычно вы бы использовали реальное соединение с базой данных

// для mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// или mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// или mysqli с созданием не на основе объекта
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'Бобби Тейблз';
$user->password = password_hash('какой-то крутой пароль');
$user->insert();
// или $user->save();

echo $user->id; // 1

$user->name = 'Джозеф Мамма';
$user->password = password_hash('крутой пароль снова!!!');
$user->insert();
// здесь нельзя использовать $user->save(), иначе он подумает, что это обновление!

echo $user->id; // 2
```

И это было настолько легко добавить нового пользователя! Теперь, когда есть строка пользователя в базе данных, как ее извлечь?

```php
$user->find(1); // найдите id = 1 в базе данных и верните его.
echo $user->name; // 'Бобби Тейблз'
```

Что, если вы хотите найти всех пользователей?

```php
$users = $user->findAll();
```

А что насчет определенного условия?

```php
$users = $user->like('name', '%мамма%')->findAll();
```

Вот как это весело! Установим его и начнем!

## Установка

Просто установите с помощью Composer

```php
composer require flightphp/active-record 
```

## Использование

Эту библиотеку можно использовать как автономно, так и с фреймворком Flight PHP. Полностью на ваше усмотрение.

### Автономно
Убедитесь, что вы передаете соединение PDO в конструктор.

```php
$pdo_connection = new PDO('sqlite:test.db'); // это просто пример, обычно вы бы использовали реальное соединение с базой данных

$User = new User($pdo_connection);
```

> Вы не хотите всегда устанавливать соединение с базой данных в конструкторе? См. [Управление соединением с базой данных](#управление-соединением-с-базой-данных) для других идей!

### Регистрация как метод в Flight
Если вы используете фреймворк Flight PHP, вы можете зарегистрировать класс ActiveRecord как службу, но честно говоря, это не обязательно.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// затем вы можете использовать его так в контроллере, функции и т. д.

Flight::user()->find(1);
```

## Методы `runway`

[runway](https://docs.flightphp.com/awesome-plugins/runway) - это инструмент командной строки для Flight, который имеет пользовательскую команду для этой библиотеки. 

```bash
# Использование
php runway make:record имя_таблицы_базы_данных [имя_класса]

# Пример
php runway make:record users
```

Это создаст новый класс в каталоге `app/records/` под названием `UserRecord.php` со следующим содержанием:

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

Находит одну запись и присваивает ее текущему объекту. Если вы передаете `$id`, он выполнит поиск по первичному ключу с этим значением. Если ничего не передается, он просто найдет первую запись в таблице.

Кроме того, вы можете передавать другие вспомогательные методы для запроса вашей таблицы.

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

Возвращает `true`, если текущая запись была считана с базы данных.

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

или вы можете автоматически сгенерировать первичный ключ через события.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// вы также можете установить primaryKey таким образом, а не в массиве выше.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // или как иначе нужно создавать уникальные идентификаторы
	}
}
```

Если вы не установите первичный ключ перед вставкой, он будет установлен на `rowid`, и база данных сгенерирует его для вас, но он не сохранится, потому что это поле может отсутствовать в вашей таблице. Поэтому рекомендуется использовать событие для автоматической обработки этого для вас.

#### `update(): boolean|ActiveRecord`

Обновляет текущую запись в базе данных.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Вставляет или обновляет текущую запись в базе данных. Если у записи есть идентификатор, он будет обновлен, в противном случае он будет вставлен.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Примечание:** Если у вас в классе определены отношения, они также будут рекурсивно сохраняться, если они были определены, инициализированы и имеют данные для обновления. (v0.4.0 и выше)

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

// на данный момент ничего не является "грязным".
$user->email = 'test@example.com'; // теперь электронная почта считается "грязной", так как она изменилась.
$user->update();
// теперь нет грязных данных, потому что они были обновлены и сохранены в базе данных

$user->password = password_hash()'newpassword'); // теперь это грязные данные
$user->dirty(); // передача ничего не очистит все грязные записи.
$user->update(); // ничего не обновится, потому что ничего не было помечено как грязное.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // обновятся и имя и пароль.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Это псевдоним для метода `dirty()`. Это немного более ясно, чем вы это делаете.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // обновятся и имя и пароль.
```

#### `isDirty(): boolean` (v0.4.0)

Возвращает `true`, если текущая запись была изменена.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Сбрасывает текущую запись в начальное состояние. Это действительно хорошо использовать в циклических поведениях.
Если передать `true`, он также сбросит данные запроса, которые были использованы для нахождения текущего объекта (поведение по умолчанию).

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

После выполнения методов `find()`, `findAll()`, `insert()`, `update()` или `save()` вы можете получить построенный SQL и использовать его в целях отладки.

## Методы запроса SQL
#### `select(string $field1 [, string $field2 ... ])`

Вы можете выбирать только несколько столбцов из таблицы (это более производительно на очень широких таблицах с множеством столбцов).

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Технически вы можете выбрать другую таблицу тоже! Почему бы и нет?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Вы даже можете присоединиться к другой таблице в базе данных.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Вы можете установить некоторые пользовательские условия where (в этом where вы не можете устанавливать параметры)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Примечание по безопасности** - Вас может увлечь что-то вроде `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. ПОЖАЛУЙСТА, НЕ ДЕЛАЙТЕ ЭТО!!! Это уязвимо для так называемых атак SQL-инъекций. В интернете есть много статей, пожалуйста, загуглите "sql injection attacks php", и вы найдете много статей по этой теме. Правильный способ обработки этого с использованием этой библиотеки заключается в том, что вместо этого метода `where()` вы бы сделали что-то вроде `$user->eq('id', $id)->eq('name', $name)->find();`. Если вам действительно нужно это сделать, библиотека `PDO` имеет `$pdo->quote($var)`, чтобы экранировать его для вас. Только после использования `quote()` вы можете использовать его в операторе `where()`.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Группируйте ваши результаты по определенному условию.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Сортируйте возвращенный запрос определенным способом.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Ограничивают количество возвращаемых записей. Если передан второй int, это смещение, ограничение также как в SQL.

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

Где `self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO

```CppI"родитель"hasMany`表示一对多关系。`self::HAS_ONE`表示一对一关系。`self::BELONGS_TO`表示从属关系。```