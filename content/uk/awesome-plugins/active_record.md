# Flight Active Record 

Активний запис - це відображення сутності бази даних в об'єкті PHP. Простими словами, якщо у вас є таблиця користувачів у вашій базі даних, ви можете "перекласти" рядок у цій таблиці в клас `User` і об'єкт `$user` у вашій кодовій базі. Дивіться [основний приклад](#basic-example).

Натисніть [тут](https://github.com/flightphp/active-record), щоб перейти до репозиторію на GitHub.

## Основний приклад

Припустимо, у вас є наступна таблиця:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Тепер ви можете створити новий клас для представлення цієї таблиці:

```php
/**
 * Клас ActiveRecord зазвичай є одиничним
 * 
 * Цілком рекомендовано додати властивості таблиці як коментарі тут
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// ви можете встановити його таким чином
		parent::__construct($database_connection, 'users');
		// або таким чином
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Тепер спостерігайте, як відбувається магія!

```php
// для sqlite
$database_connection = new PDO('sqlite:test.db'); // це лише для прикладу, ви, напевно, використовували б реальне з'єднання з базою даних

// для mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// або mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// або mysqli з не об'єктним створенням
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('some cool password');
$user->insert();
// або $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('some cool password again!!!');
$user->insert();
// не можна використовувати $user->save() тут, інакше він подумає, що це оновлення!

echo $user->id; // 2
```

І це було так легко додати нового користувача! Тепер, коли у базі даних є рядок користувача, як витягнути його?

```php
$user->find(1); // знайти id = 1 у базі даних та повернути його.
echo $user->name; // 'Bobby Tables'
```

А що, якщо ви хочете знайти всіх користувачів?

```php
$users = $user->findAll();
```

Що з певною умовою?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Бачите, як це весело? Давайте встановимо це і розпочнемо!

## Встановлення

Просто встановіть з Composer

```php
composer require flightphp/active-record 
```

## Використання

Це може використовуватися як окрема бібліотека або з PHP Framework Flight. Повністю на ваш вибір.

### Окремо
Просто переконайтеся, що ви передали з'єднання PDO в конструктор.

```php
$pdo_connection = new PDO('sqlite:test.db'); // це лише для прикладу, ви, напевно, використовували б реальне з'єднання з базою даних

$User = new User($pdo_connection);
```

> Не хочете щоразу встановлювати з'єднання з базою даних у конструкторі? Дивіться [Управління з'єднаннями з базою даних](#database-connection-management) для інших ідей!

### Зареєструвати як метод у Flight
Якщо ви використовуєте PHP Framework Flight, ви можете зареєструвати клас ActiveRecord як сервіс, але насправді вам не потрібно цього робити.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// тоді ви можете використовувати це так у контроллері, функції тощо.

Flight::user()->find(1);
```

## `runway` Методи

[runway](https://docs.flightphp.com/awesome-plugins/runway) - це інструмент CLI для Flight, який має власну команду для цієї бібліотеки.

```bash
# Використання
php runway make:record database_table_name [class_name]

# Приклад
php runway make:record users
```

Це створить новий клас у директорії `app/records/` під назвою `UserRecord.php` з наступним вмістом:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Клас ActiveRecord для таблиці користувачів.
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
     * @var array $relations Встановіть відносини для моделі
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [
		// 'relation_name' => [ self::HAS_MANY, 'RelatedClass', 'foreign_key' ],
	];

    /**
     * Конструктор
     * @param mixed $databaseConnection З’єднання з базою даних
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

## CRUD функції

#### `find($id = null) : boolean|ActiveRecord`

Знайти один запис та призначити в поточний об'єкт. Якщо ви передаєте `$id` якось, то проведе пошук за первинним ключем з цим значенням. Якщо нічого не передано, просто знайде перший запис у таблиці.

Додатково ви можете передати інші допоміжні методи для запиту вашої таблиці.

```php
// знайти запис з деякими умовами заздалегідь
$user->notNull('password')->orderBy('id DESC')->find();

// знайти запис за конкретним id
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Знаходить усі записи у вказаній таблиці.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

Повертає `true`, якщо поточний запис був активованим (отримано з бази даних).

```php
$user->find(1);
// якщо запис знайдено з даними...
$user->isHydrated(); // true
```

#### `insert(): boolean|ActiveRecord`

Вставляє поточний запис у базу даних.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

##### Текстові первинні ключі

Якщо у вас є текстовий первинний ключ (такий як UUID), ви можете встановити значення первинного ключа перед вставкою одним з двох способів.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // або $user->save();
```

або ви можете автоматично згенерувати первинний ключ для вас через події.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// ви також можете встановити primaryKey таким чином, замість масиву вище.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // або як вам потрібно згенерувати свої унікальні id
	}
}
```

Якщо ви не встановите первинний ключ перед вставкою, він буде встановлений в `rowid`, і 
база даних згенерує його для вас, але він не збережеться, оскільки це поле може не існувати
у вашій таблиці. Ось чому рекомендовано використовувати подію, щоб автоматично обробити це 
для вас.

#### `update(): boolean|ActiveRecord`

Оновлює поточний запис у базі даних.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Вставляє або оновлює поточний запис у базі даних. Якщо у запису є id, він оновлює, в іншому випадку вставляє.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Примітка:** Якщо ви визначили відносини в класі, вони також рекурсивно збережуться, якщо вони були визначені, створені і мають неточні дані для оновлення. (v0.4.0 і вище)

#### `delete(): boolean`

Видаляє поточний запис з бази даних.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Ви також можете видалити кілька записів, виконавши пошук заздалегідь.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

"Брудні" дані відносяться до даних, які були змінені в запису.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// на даний момент нічого не є "брудним".

$user->email = 'test@example.com'; // тепер електронна пошта вважається "брудною", оскільки вона була змінена.
$user->update();
// тепер немає даних, які є брудними, оскільки вони були оновлені та збережені в базі даних

$user->password = password_hash('newpassword'); // тепер це брудно
$user->dirty(); // передача нічого очищає всі брудні значення.
$user->update(); // нічого не оновиться, оскільки нічого не було захоплено як брудне.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // і ім'я, і пароль оновлені.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Це псевдонім для методу `dirty()`. Це трішки ясніше, що ви робите.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // і ім'я, і пароль оновлені.
```

#### `isDirty(): boolean` (v0.4.0)

Повертає `true`, якщо поточний запис був змінений.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Скидає поточний запис до його початкового стану. Це дуже корисно використовувати в цикліх.
Якщо ви передасте `true`, то також скине дані запиту, які використовувалися для знаходження поточного об'єкта (за замовчуванням).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // почати з чистого аркуша
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Після виконання методу `find()`, `findAll()`, `insert()`, `update()`, або `save()` ви можете отримати SQL, який був створений, і використовувати його для відлагодження.

## Методи SQL-запитів
#### `select(string $field1 [, string $field2 ... ])`

Ви можете вибрати тільки кілька стовпців у таблиці, якщо хочете (це ефективніше на дуже широких таблицях з багатьма стовпцями)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Ви можете технічно вибрати іншу таблицю! Чому б не спробувати?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Ви навіть можете об'єднати іншу таблицю в базі даних.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Ви можете встановити деякі власні умови (ви не можете встановлювати параметри в цій умові where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Примітка безпеки** - Ви можете бути спокушені зробити щось на зразок `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Будь ласка, НЕ РОБІТЬ ЦЬОГО!!! Це схильне до атак SQL-ін'єкції. Є безліч статей в Інтернеті, будь ласка, знайдіть "sql injection attacks php" і ви знайдете багато статей на цю тему. Правильний спосіб обробити це з цією бібліотекою - замість цього методу `where()`, ви б стали на щось більш схоже на `$user->eq('id', $id)->eq('name', $name)->find();` Якщо вам дійсно потрібно це зробити, бібліотека `PDO` має `$pdo->quote($var)`, щоб у відповідності з її екрануванням. Тільки після того, як ви використаєте `quote()`, ви можете використовувати це в операторі `where()`.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Групуйте свої результати за певною умовою.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Сортуйте повернутий запит певним чином.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Обмежте кількість повернених записів. Якщо буде надано друге ціле число, воно буде зміщене, обмежене, як у SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## Умови WHERE
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

Де `field = $value`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

Де `field <> $value`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

Де `field IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

Де `field IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

Де `field > $value`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

Де `field < $value`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

Де `field >= $value`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

Де `field <= $value`

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

Де `field LIKE $value` або `field NOT LIKE $value`

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

Де `field IN($value)` або `field NOT IN($value)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Де `field BETWEEN $value AND $value1`

```php
$user->between('id', [1, 2])->find();
```

### Умови OR

Можливо обгорнути ваші умови в оператор OR. Це робиться або за допомогою методу `startWrap()` та `endWrap()`, або заповнивши 3-й параметр умови після поля та значення.

```php
// Метод 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// Це оцінюється в `id = 1 AND (name = 'demo' OR name = 'test')`

// Метод 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// Це оцінюється в `id = 1 OR name = 'demo'`
```

## Відносини
Ви можете встановити кілька видів відносин, використовуючи цю бібліотеку. Ви можете встановити один->багато та один->один відносини між таблицями. Це вимагає трохи додаткового налаштування в класі заздалегідь.

Встановлення масиву `$relations` не є складним, але вгадувати правильний синтаксис може бути складно.

```php
protected array $relations = [
	// ви можете назвати ключ як вам завгодно. Ім'я ActiveRecord, напевно, добре. Наприклад: user, contact, client
	'user' => [
		// обов'язково
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // це тип відношення

		// обов'язково
		'Some_Class', // це "інший" клас ActiveRecord, на який це посилається

		// обов'язково
		// в залежності від типу відношення
		// self::HAS_ONE = зовнішній ключ, що посилається на сполучення
		// self::HAS_MANY = зовнішній ключ, що посилається на сполучення
		// self::BELONGS_TO = локальний ключ, що посилається на сполучення
		'local_or_foreign_key',
		// просто для вашої інформації, це також лише з'єднує з первинним ключем "іншої" моделі

		// необов'язково
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // додаткові умови, які ви хочете, коли з'єднуєте відношення
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// необов'язково
		'back_reference_name' // це якщо ви хочете повернути це відношення назад до самого себе Наприклад: $user->contact->user;
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

Тепер у нас налаштовані посилання, так що ми можемо використовувати їх дуже легко!

```php
$user = new User($pdo_connection);

// знайти найбільшого користувача.
$user->notNull('id')->orderBy('id desc')->find();

// отримати контакти за допомогою відношення:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// або ми можемо піти іншим шляхом.
$contact = new Contact();

// знайти один контакт
$contact->find();

// отримати користувача за допомогою відношення:
echo $contact->user->name; // це ім'я користувача
```

Досить круто, чи не так?

## Встановлення налаштування даних
Іноді вам може знадобитися прикріпити щось унікальне до вашого ActiveRecord, наприклад, спеціальний обчислення, яке може бути простіше просто прикріпити до об'єкта, який потім буде передано, скажімо, в шаблон.

#### `setCustomData(string $field, mixed $value)`
Ви прикріплюєте користувацькі дані за допомогою методу `setCustomData()`.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

А потім ви просто посилаєтеся на це, як на звичайну властивість об'єкта.

```php
echo $user->page_view_count;
```

## Події

Ще одна супер класна функція цієї бібліотеки - це події. Події спрацьовують у певні часи на основі певних методів, які ви викликаєте. Вони є дуже корисними для автоматичного налаштування даних.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Це дуже корисно, якщо вам потрібно встановити стандартне з'єднання або щось подібне.

```php
// index.php або bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // не забувайте про посилання &
		// ви могли б зробити це, щоб автоматично встановити з'єднання
		$config['connection'] = Flight::db();
		// або це
		$self->transformAndPersistConnection(Flight::db());
		
		// Ви також можете встановити ім'я таблиці таким чином.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Це, ймовірно, лише корисно, якщо вам потрібно маніпулювати запитом щоразу.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// завжди виконувати id >= 0, якщо це ваше
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Ця подія, ймовірно, більше корисна, якщо вам потрібно завжди виконувати якусь логіку щоразу, коли цей запис отримується. Вам потрібно розшифрувати що-небудь? Вам потрібно провести спеціальний підрахунок запитів кожного разу (не продуктивно, але будь ласка...)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// розшифровка чогось
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// можливо, зберігаючи щось спеціальне, наприклад, запит???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']); 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Це, ймовірно, лише корисно, якщо вам потрібно маніпулювати запитом щоразу.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// завжди виконувати id >= 0, якщо це ваше
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Схоже на `afterFind()`, але ви маєте можливість зробити це для всіх записів!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// зробити щось круте, як в afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Дуже корисно, якщо вам потрібно встановити стандартні значення щоразу.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// встановіть якісь звичайні значення
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

Можливо, у вас є випадок використання для зміни даних після їх вставки?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// робіть свою справу
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// або будь-що інше...
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Дуже корисно, якщо вам потрібно встановити стандартні значення щоразу при оновленні.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// встановіть якісь звичайні значення
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Можливо, у вас є випадок використання для зміни даних після їх оновлення?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// робіть свою справу
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// або будь-що інше...
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Це корисно, якщо ви хочете, щоб події відбувалися як під час вставок, так і під час оновлень. Я не буду завантажувати вас довгим поясненням, але я впевнений, що ви можете здогадатися, що це так.

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

Не впевнений, що ви хочете зробити тут, але я не осуджую! Давайте!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'Він був сміливим солдатом... :cry-face:';
	} 
}
```

## Управління з'єднаннями з базою даних

Коли ви використовуєте цю бібліотеку, ви можете встановити з'єднання з базою даних кількома способами. Ви можете встановити з'єднання в конструкторі, ви можете встановити його через змінну конфігурації `$config['connection']`, або ви можете встановити його через `setDatabaseConnection()` (v0.4.1). 

```php
$pdo_connection = new PDO('sqlite:test.db'); // для прикладу
$user = new User($pdo_connection);
// або
$user = new User(null, [ 'connection' => $pdo_connection ]);
// або
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Якщо ви хочете уникнути постійне встановлення `$database_connection` щоразу, коли ви викликаєте активний запис, існують обхідні шляхи!

```php
// index.php або bootstrap.php
// Установіть це як зареєстрований клас у Flight
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

// User.php
class User extends flight\ActiveRecord {
	
	public function __construct(array $config = [])
	{
		$database_connection = $config['connection'] ?? Flight::db();
		parent::__construct($database_connection, 'users', $config);
	}
}

// А тепер, без аргументів!
$user = new User();
```

> **Примітка:** Якщо ви плануєте одиничне тестування, робити це таким чином може справити деякі труднощі в одиничному тестуванні, але загалом, оскільки ви можете впроваджувати своє 
з'єднання з `setDatabaseConnection()` або `$config['connection']`, це не так вже й погано.

Якщо вам потрібно оновити з'єднання з базою даних, наприклад, якщо ви запускаєте тривале CLI-скрипт і вам потрібно оновити з'єднання час від часу, ви можете перенастроїти з'єднання за допомогою `$your_record->setDatabaseConnection($pdo_connection)`.

## Участь

Будь ласка, беріть участь. :D

### Налаштування

Коли ви долучаєтеся, переконайтеся, що ви запускаєте `composer test-coverage`, щоб підтримувати 100% покриття тестів (це не справжнє покриття одиничних тестів, більше схоже на інтеграційне тестування).

Також переконайтеся, що ви запускаєте `composer beautify` та `composer phpcs`, щоб виправити всі помилки форматування. 

## Ліцензія

MIT