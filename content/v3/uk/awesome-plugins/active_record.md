# Flight Active Record

Активний запис - це відображення сутності бази даних на об'єкт PHP. Простими словами, якщо у вас є таблиця користувачів у вашій базі даних, ви можете "перекласти" рядок у цій таблиці в клас `User` і об'єкт `$user` у вашому коді. Дивіться [основний приклад](#basic-example).

Натисніть [тут](https://github.com/flightphp/active-record) для доступу до репозиторію на GitHub.

## Основний приклад

Припустимо, у вас є наступна таблиця:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Тепер ви можете налаштувати новий клас для представлення цієї таблиці:

```php
/**
 * Клас ActiveRecord зазвичай є одниною
 * 
 * Рекомендується додати властивості таблиці як коментарі тут
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// ви можете встановити таким чином
		parent::__construct($database_connection, 'users');
		// або таким чином
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Тепер спостерігайте, як відбувається магія!

```php
// для sqlite
$database_connection = new PDO('sqlite:test.db'); // це лише для прикладу, ви, ймовірно, будете використовувати реальне з'єднання з базою даних

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
// не можна використовувати $user->save() тут, інакше воно вважатиме, що це оновлення!

echo $user->id; // 2
```

І це було так легко додати нового користувача! Тепер, коли в базі даних є рядок користувача, як витягти його?

```php
$user->find(1); // знайти id = 1 у базі даних і повернути його.
echo $user->name; // 'Bobby Tables'
```

А що якщо ви хочете знайти всіх користувачів?

```php
$users = $user->findAll();
```

Що щодо певної умови?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Бачите, як це весело? Давайте встановимо це і почнемо!

## Встановлення

Просто встановіть за допомогою Composer

```php
composer require flightphp/active-record 
```

## Використання

Це можна використовувати як окрему бібліотеку або з PHP Framework Flight. Цілком на ваш розсуд.

### Окремо
Просто переконайтеся, що передали з'єднання PDO в конструктор.

```php
$pdo_connection = new PDO('sqlite:test.db'); // це лише для прикладу, ви, ймовірно, будете використовувати реальне з'єднання з базою даних

$User = new User($pdo_connection);
```

> Не хочете завжди встановлювати з'єднання з базою даних у конструкторі? Дивіться [Управління з'єднаннями з базою даних](#database-connection-management) для інших ідей!

### Зареєструвати як метод у Flight
Якщо ви використовуєте PHP Framework Flight, ви можете зареєструвати клас ActiveRecord як сервіс, але насправді вам не потрібно.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// тоді ви можете використовувати це так у контролері, функції тощо.

Flight::user()->find(1);
```

## Методи `runway`

[runway](/awesome-plugins/runway) - це інструмент CLI для Flight, який має користувацьку команду для цієї бібліотеки.

```bash
# Використання
php runway make:record database_table_name [class_name]

# Приклад
php runway make:record users
```

Це створить новий клас у каталозі `app/records/` під назвою `UserRecord.php` з наступним вмістом:

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
     * @var array $relations Встановіть зв'язки для моделі
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
     */
    protected array $relations = [
		// 'relation_name' => [ self::HAS_MANY, 'RelatedClass', 'foreign_key' ],
	];

    /**
     * Конструктор
     * @param mixed $databaseConnection З'єднання з базою даних
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

## CRUD функції

#### `find($id = null) : boolean|ActiveRecord`

Знайдіть один запис і присвойте його поточному об'єкту. Якщо ви передасте `$id` будь-якого типу, він виконає пошук за первинним ключем з цим значенням. Якщо нічого не передано, він просто знайде перший запис у таблиці.

Додатково ви можете передати йому інші допоміжні методи для запиту вашої таблиці.

```php
// знайти запис з якимись умовами заздалегідь
$user->notNull('password')->orderBy('id DESC')->find();

// знайти запис за конкретним id
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Знайдіть усі записи в зазначеній вами таблиці.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

Повертає `true`, якщо поточний запис був гідратований (отриманий з бази даних).

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

або ви можете мати первинний ключ, автоматично згенерований для вас через події.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// ви можете також встановити primaryKey таким чином замість масиву вище.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // або як вам потрібно генерувати ваші унікальні id
	}
}
```

Якщо ви не встановите первинний ключ перед вставкою, він буде встановлений на `rowid`, і 
база даних згенерує його для вас, але не збережеться, оскільки цього поля може не бути
в вашій таблиці. Це чому рекомендується використовувати подію, щоб автоматично обробити це 
для вас.

#### `update(): boolean|ActiveRecord`

Оновлює поточний запис у базі даних.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Вставляє або оновлює поточний запис у базі даних. Якщо запис має id, він оновить, в іншому випадку вставить.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Примітка:** Якщо у вас є визначені в класі зв'язки, він рекурсивно збереже ці зв'язки також, якщо вони були визначені, ініційовані та мають "брудні" дані для оновлення. (v0.4.0 і вище)

#### `delete(): boolean`

Видаляє поточний запис з бази даних.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Ви також можете видалити кілька записів, виконуючи пошук заздалегідь.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array $dirty = []): ActiveRecord`

"Брудні" дані стосуються даних, які були змінені в запису.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// на даний момент нічого не є "брудним".

$user->email = 'test@example.com'; // тепер email вважається "брудним", оскільки він змінився.
$user->update();
// тепер немає даних, які є брудними, оскільки вони були оновлені та збережені в базі даних

$user->password = password_hash('newpassword'); // тепер це брудно
$user->dirty(); // передання нічого очистить усі брудні записи.
$user->update(); // нічого не буде оновлено, оскільки нічого не було захоплено як брудне.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // і ім'я, і пароль оновлюються.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Це псевдонім для методу `dirty()`. Це трохи ясніше, що ви робите.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // і ім'я, і пароль оновлюються.
```

#### `isDirty(): boolean` (v0.4.0)

Повертає `true`, якщо поточний запис був змінений.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Скидає поточний запис до його початкового стану. Це дійсно добре використовувати в циклі.
Якщо ви передасте `true`, він також скине дані запиту, які були використані для знаходження поточного об'єкта (за замовчуванням).

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

Після того, як ви запустите `find()`, `findAll()`, `insert()`, `update()`, або `save()` метод, ви можете отримати SQL, який був побудований, і використовувати його для налагодження.

## Методи SQL Запитів
#### `select(string $field1 [, string $field2 ... ])`

Ви можете вибрати лише кілька стовпців у таблиці, якщо хочете (це ефективніше на справді широких таблицях з багатьма стовпцями)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Ви також можете вибрати іншу таблицю! Чому б і ні?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Ви також можете виконати з'єднання з іншою таблицею в базі даних.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Ви можете встановити деякі власні аргументи where (ви не можете встановити параметри в цьому операторі where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Примітка безпеки** - Ви можете бути спокушені зробити щось на зразок `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Будь ласка, НЕ РОБІТЬ ЦЬОГО!!! Це піддається атакам SQL-ін'єкцій. Є багато статей в Інтернеті, будь ласка, Google "sql injection attacks php", і ви знайдете багато статей на цю тему. Правильний спосіб обробити це з цією бібліотекою - замість цього методу `where()`, ви повинні зробити щось на зразок `$user->eq('id', $id)->eq('name', $name)->find();` Якщо вам дійсно потрібно це зробити, бібліотека `PDO` має `$pdo->quote($var)`, щоб екранізувати його для вас. Тільки після того, як ви використовуєте `quote()`, ви можете використовувати це в операторі `where()`.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Групуйте свої результати за певною умовою.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Сортуйте повернуті запити певним чином.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Обмежте кількість повернених записів. Якщо задано другий int, він буде зсувати, обмежуючи так, як у SQL.

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

Можливо обернути ваші умови в оператор OR. Це здійснюється або за допомогою методів `startWrap()` та `endWrap()`, або заповнюючи третій параметр умови після поля та значення.

```php
// Метод 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// Це буде оцінено як `id = 1 AND (name = 'demo' OR name = 'test')`

// Метод 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// Це буде оцінено як `id = 1 OR name = 'demo'`
```

## Взаємовідносини
Ви можете налаштувати декілька видів взаємовідносин, використовуючи цю бібліотеку. Ви можете налаштувати один->багато та один->один взаємовідносини між таблицями. Це потребує незначної додаткової підготовки у класі заздалегідь.

Налаштування масиву `$relations` не є важким, але вгадування правильного синтаксису може бути заплутаним.

```php
protected array $relations = [
	// ви можете назвати ключ будь-як. Назва класу ActiveRecord, мабуть, підійде. Наприклад: user, contact, client
	'user' => [
		// обов'язково
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // це тип взаємовідносини

		// обов'язково
		'Some_Class', // це "інший" клас ActiveRecord, з яким це буде посилатися

		// обов'язково
		// в залежності від типу взаємовідносини
		// self::HAS_ONE = зовнішній ключ, що посилається на з'єднання
		// self::HAS_MANY = зовнішній ключ, що посилається на з'єднання
		// self::BELONGS_TO = локальний ключ, що посилається на з'єднання
		'local_or_foreign_key',
		// до вашого відома, це також лише приєднує до первинного ключа "іншої" моделі

		// необов'язково
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // додаткові умови, які ви хочете при приєднанні зв'язку
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// необов'язково
		'back_reference_name' // це якщо ви хочете повернути цю взаємовідносину назад до себе, наприклад: $user->contact->user;
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

Тепер у нас налаштовані посилання, щоб ми могли використовувати їх дуже зручно!

```php
$user = new User($pdo_connection);

// знайти найостаннішого користувача.
$user->notNull('id')->orderBy('id desc')->find();

// отримати контакти, використовуючи зв'язок:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// або ми можемо піти в іншу сторону.
$contact = new Contact();

// знайти один контакт
$contact->find();

// отримати користувача за допомогою зв'язку:
echo $contact->user->name; // це ім'я користувача
```

Досить круто, чи не так?

## Налаштування нестандартних даних
Іноді вам може знадобитися прикріпити щось унікальне до вашого ActiveRecord, наприклад, нестандартне обчислення, яке може бути легше просто прикріпити до об'єкта, який потім буде переданий, скажімо, шаблону.

#### `setCustomData(string $field, mixed $value)`
Ви прикріплюєте нестандартні дані за допомогою методу `setCustomData()`.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

І тоді ви просто посилаєтеся на це, як на звичайну властивість об'єкта.

```php
echo $user->page_view_count;
```

## Події

Ще одна надзвичайно класна функція цієї бібліотеки - це події. Події спрацьовують в певний час на основі певних методів, які ви викликаєте. Вони дуже корисні для автоматичного налаштування даних.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Це дійсно корисно, якщо вам потрібно встановити з'єднання за замовчуванням або щось подібне.

```php
// index.php або bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // не забувайте про посилання &
		// ви могли б зробити це, щоб автоматично налаштувати з'єднання
		$config['connection'] = Flight::db();
		// або так
		$self->transformAndPersistConnection(Flight::db());
		
		// Також ви можете встановити ім'я таблиці таким чином.
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

Ця функція, ймовірно, корисніша, якщо ви завжди повинні виконувати якусь логіку кожного разу, коли цей запис отримується. Вам потрібно розшифрувати щось? Вам потрібно виконати налаштування запиту кожен раз (не продуктивно, але байдуже)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// розшифрування чогось
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// можливо, зберігання чогось нестандартного, як запит???
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

Подібно до `afterFind()`, але ви можете зробити це для усіх записів!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// зробити щось класне, як і при afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Дійсно корисно, якщо вам потрібно встановити деякі значення за замовчуванням щоразу.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// встановити якісь правильні значення за замовчуванням
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

Можливо, у вас є випадок для зміни даних після їх вставки?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// робіть що хочете
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// або що завгодно....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Дійсно корисно, якщо вам потрібно встановити деякі значення за замовчуванням щоразу на оновлення.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeUpdate(self $self) {
		// встановити якісь правильні значення за замовчуванням
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Можливо, у вас є випадок для зміни даних після їх оновлення?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterUpdate(self $self) {
		// робіть що хочете
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// або що завгодно....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Це корисно, якщо ви хочете, щоб події відбувалися як під час вставок, так і оновлень. Я вас не буде дратувати довгим поясненням, але ви, напевно, можете здогадатися, що це таке.

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

Не впевнений, що ви хочете зробити тут, але без суджень! Робіть, як хочете!

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

Коли ви користуєтеся цією бібліотекою, ви можете встановити з'єднання з базою даних кількома різними способами. Ви можете встановити з'єднання в конструкторі, ви можете встановити його через змінну конфігурації `$config['connection']` або ви можете встановити його через `setDatabaseConnection()` (v0.4.1).

```php
$pdo_connection = new PDO('sqlite:test.db'); // для прикладу
$user = new User($pdo_connection);
// або
$user = new User(null, [ 'connection' => $pdo_connection ]);
// або
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Якщо ви хочете уникнути постійного встановлення `$database_connection` щоразу, коли ви викликаєте активний запис, існують варіанти!

```php
// index.php або bootstrap.php
// Встановіть це як зареєстрований клас у Flight
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

// User.php
class User extends flight\ActiveRecord {
	
	public function __construct(array $config = [])
	{
		$database_connection = $config['connection'] ?? Flight::db();
		parent::__construct($database_connection, 'users', $config);
	}
}

// І тепер, аргументи не потрібні!
$user = new User();
```

> **Примітка:** Якщо ви плануєте юніт-тестування, робити це може додати певні труднощі до юніт-тестування, але в цілому, оскільки ви можете впроваджувати ваше 
з'єднання за допомогою `setDatabaseConnection()` або `$config['connection']`, це не так вже й погано.

Якщо вам потрібно оновити з'єднання з базою даних, наприклад, якщо ви запускаєте тривале CLI-скрипт і вам потрібно періодично оновлювати з'єднання, ви можете заново встановити з'єднання за допомогою `$your_record->setDatabaseConnection($pdo_connection)`.

## Внески

Будь ласка, робіть це. :D

### Налаштування

Коли ви будете брати участь, переконайтеся, що ви запустили `composer test-coverage`, щоб підтримувати 100% покриття тестами (це не 100% покриття юніт-тестами, а скоріше інтеграційне тестування).

Також переконайтеся, що ви запустили `composer beautify` та `composer phpcs`, щоб виправити будь-які помилки синтаксису.

## Ліцензія

MIT