# Flight Active Record 

Активний запис - це відображення об'єкта бази даних на PHP об'єкт. Простими словами, якщо у вас є таблиця користувачів у вашій базі даних, ви можете "перекласти" рядок у цій таблиці на клас `User` і об'єкт `$user` у ваше кодове базі. Дивіться [основний приклад](#basic-example).

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

Тепер ви можете створити новий клас, щоб представляти цю таблицю:

```php
/**
 * Клас ActiveRecord зазвичай є в однині
 * 
 * Рекомендується додавати властивості таблиці як коментарі тут
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// ви можете налаштувати його таким чином
		parent::__construct($database_connection, 'users');
		// або таким чином
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Тепер спостерігайте за магією!

```php
// для sqlite
$database_connection = new PDO('sqlite:test.db'); // це просто для прикладу, ви, напевно, використовуєте реальне з’єднання з базою даних

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
// не можна використовувати $user->save() тут, інакше це буде вважатися оновленням!

echo $user->id; // 2
```

І це було так просто, щоб додати нового користувача! Тепер, коли в базі даних є рядок користувача, як витягти його?

```php
$user->find(1); // знайти id = 1 у базі даних і повернути його.
echo $user->name; // 'Bobby Tables'
```

А що, якщо ви хочете знайти всіх користувачів?

```php
$users = $user->findAll();
```

А що стосується певної умови?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Бачите, як це весело? Давайте встановимо його та почнемо!

## Встановлення

Просто встановіть за допомогою Composer

```php
composer require flightphp/active-record 
```

## Використання

Це можна використовувати як незалежну бібліотеку або з PHP фреймворком Flight. Повністю на ваш розсуд.

### Незалежно
Просто переконайтеся, що ви передаєте з'єднання PDO до конструктора.

```php
$pdo_connection = new PDO('sqlite:test.db'); // це просто для прикладу, ви, напевно, використовуєте реальне з’єднання з базою даних

$User = new User($pdo_connection);
```

> Не хочете завжди налаштовувати з'єднання з базою даних у конструкторі? Дивіться [Управління з'єднанням з базою даних](#database-connection-management) для інших ідей!

### Реєстрація як метод у Flight
Якщо ви використовуєте PHP фреймворк Flight, ви можете зареєструвати клас ActiveRecord як сервіс, але вам насправді не потрібно цього робити.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// тоді ви можете використовувати це так у контролері, функції тощо.

Flight::user()->find(1);
```

## `runway` Методи

[runway](https://docs.flightphp.com/awesome-plugins/runway) - це CLI інструмент для Flight, який має спеціальну команду для цієї бібліотеки. 

```bash
# Використання
php runway make:record database_table_name [class_name]

# Приклад
php runway make:record users
```

Це створить новий клас у каталозі `app/records/` як `UserRecord.php` з наступним вмістом:

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

Знайти один запис і присвоїти його поточному об'єкту. Якщо ви передаєте `$id` якесь значення, то вона виконає пошук по первинному ключу з цим значенням. Якщо нічого не передано, вона просто знайде перший запис у таблиці.

Додатково ви можете передати йому інші допоміжні методи для запиту до вашої таблиці.

```php
// знайти запис з деякими умовами заздалегідь
$user->notNull('password')->orderBy('id DESC')->find();

// знайти запис за певним id
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Знаходить усі записи в таблиці, яку ви вкажете.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

Повертає `true`, якщо поточний запис був гідратизований (отриманий з бази даних).

```php
$user->find(1);
// якщо запис знайдений з даними...
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

Якщо у вас є текстовий первинний ключ (такий як UUID), ви можете встановити значення первинного ключа перед вставкою одним із двох способів.

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
		// ви також можете встановити первинний ключ таким чином замість масиву вище.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // або як вам потрібно згенерувати ваші унікальні ідентифікатори
	}
}
```

Якщо ви не встановите первинний ключ перед вставкою, він буде встановлено на `rowid`, і база даних згенерує його для вас, але він не буде зберігатися, оскільки це поле може не існувати
у вашій таблиці. Ось чому рекомендується використовувати цю подію, щоб автоматично обробляти це 
за вас.

#### `update(): boolean|ActiveRecord`

Оновлює поточний запис у базі даних.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Вставляє або оновлює поточний запис у базі даних. Якщо запис має id, він оновиться, інакше буде вставлено.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Примітка:** Якщо у вас є зв'язки, визначені в класі, вони також будуть рекурсивно зберігатися, якщо вони були визначені, створені та мають брудні дані для оновлення. (v0.4.0 і вище)

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

#### `dirty(array  $dirty = []): ActiveRecord`

Брудні дані відносяться до даних, які були змінені в запису.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// на даний момент нічого не є "брудним".

$user->email = 'test@example.com'; // тепер електронна пошта вважається "брудною", оскільки була змінена.
$user->update();
// тепер немає даних, які є брудними, оскільки вони були оновлені та збережені в базі даних

$user->password = password_hash()'newpassword'); // тепер це брудно
$user->dirty(); // не передаючи нічого, ви очистите всі брудні записи.
$user->update(); // нічого не буде оновлено, оскільки нічого не було захоплено як брудне.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // і ім'я, і пароль оновлені.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Це псевдоним для методу `dirty()`. Це трохи ясніше, що ви робите.

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

Скидає поточний запис до початкового стану. Це дійсно добре використовувати в поведінках на кшталт циклу.
Якщо ви передаєте `true`, вона також скине дані запиту, які використовувалися для знаходження поточного об’єкта (за замовчуванням).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // почати з чистого листа
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Після виконання методу `find()`, `findAll()`, `insert()`, `update()` або `save()` ви можете отримати SQL, який був побудований, і використовувати його для налагодження.

## Методи SQL запиту
#### `select(string $field1 [, string $field2 ... ])`

Ви можете вибрати лише деякі стовпці в таблиці, якщо хочете (це більш ефективно на дійсно широких таблицях з багатьма стовпцями)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Ви технічно можете вибрати й іншу таблицю! Чому б і ні?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Ви навіть можете здійснити з'єднання з іншою таблицею в базі даних.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Ви можете встановити деякі користувацькі умови where (ви не можете встановлювати параметри в цій умові where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Примітка безпеки** - Ви могли б подумати зробити щось на кшталт `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Будь ласка, НЕ РОБІТЬ ЦЬОГО!!! Це піддається так званим атакам SQL Injection. Є багато статей в Інтернеті, будь ласка, Google "sql injection attacks php", і ви знайдете багато статей на цю тему. Правильний спосіб впоратися з цим за допомогою цієї бібліотеки - замість цього методу `where()` ви повинні зробити щось на кшталт `$user->eq('id', $id)->eq('name', $name)->find();`. Якщо ви абсолютно повинні це зробити, бібліотека `PDO` має `$pdo->quote($var)`, щоб екранувати це для вас. Тільки після використання `quote()` ви можете використовувати це в операторі `where()`.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Групуйте результати за певною умовою.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Сортуйте повернуті запити певним чином.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Обмежте кількість повернених записів. Якщо передано друге ціле число, він буде зсувом, обмеженням, як у SQL.

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

## Відносини
Ви можете встановити кілька видів зв’язків, використовуючи цю бібліотеку. Ви можете встановити один->багато та один->один зв'язки між таблицями. Це вимагає деякого додаткового налаштування в класі заздалегідь.

Встановлення масиву `$relations` не є важким, але вгадувати правильний синтаксис може бути заплутаним.

```php
protected array $relations = [
	// ви можете називати ключ будь-яким бажаним. Назва ActiveRecord, напевно, підходить. Напр. user, contact, client
	'user' => [
		// обов'язково
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // це тип зв'язку

		// обов'язково
		'Some_Class', // це "інший" клас ActiveRecord, на який буде посилатися

		// обов'язково
		// залежно від типу зв'язку
		// self::HAS_ONE = зовнішній ключ, що посилається на з’єднання
		// self::HAS_MANY = зовнішній ключ, що посилається на з’єднання
		// self::BELONGS_TO = локальний ключ, що посилається на з’єднання
		'local_or_foreign_key',
		// просто для довідки, це також тільки з'єднує з первинним ключем "іншої" моделі

		// необов'язково
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // додаткові умови, які ви хочете під час з'єднання зі зв'язком
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// необов'язково
		'back_reference_name' // це якщо ви хочете посилатися на цю зв'язок назад до себе Наприклад: $user->contact->user;
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

Тепер у нас є налаштовані посилання, тому ми можемо використовувати їх дуже легко!

```php
$user = new User($pdo_connection);

// знайти найновішого користувача.
$user->notNull('id')->orderBy('id desc')->find();

// отримати контакти, використовуючи зв'язок:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// або можемо піти іншим шляхом.
$contact = new Contact();

// знайти один контакт
$contact->find();

// отримати користувача, використовуючи зв'язок:
echo $contact->user->name; // це ім'я користувача
```

Досить круто, так?

## Встановлення користувальницьких даних
Іноді вам може знадобитися прикріпити щось унікальне до вашого ActiveRecord, наприклад, спеціальне обчислення, яке може бути легше просто прикріпити до об’єкта, який потім буде передано, наприклад, шаблону.

#### `setCustomData(string $field, mixed $value)`
Ви прикріпляєте користувальницькі дані за допомогою методу `setCustomData()`.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

А потім ви просто посилаєтеся на це як на звичайну властивість об'єкта.

```php
echo $user->page_view_count;
```

## Події

Ще одна надзвичайно крута функція цієї бібліотеки - це події. Події спрацьовують у певний час на основі певних методів, які ви викликаєте. Вони дуже корисні для автоматичного налаштування даних для вас.

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

	protected function onConstruct(self $self, array &$config) { // не забудьте про & посилання
		// ви могли б зробити це, щоб автоматично встановити з'єднання
		$config['connection'] = Flight::db();
		// або так
		$self->transformAndPersistConnection(Flight::db());
		
		// Ви також можете встановити назву таблиці таким чином.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Це, мабуть, буде корисно лише за умови, що вам потрібно змінити запит щоразу.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// завжди виконую id >= 0, якщо це ваше
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Це, мабуть, більш корисно, якщо вам щоразу потрібно виконувати якусь логіку щоразу, коли цей запис отримується. Вам потрібно дещо розшифрувати? Вам потрібно виконати запит на підрахунок щоразу (неефективно, але в будь-якому разі)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// розшифрування чогось
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// можливо, збереження чогось спеціального, як запит???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Це, мабуть, буде корисно лише за умови, що вам потрібно змінити запит щоразу.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// завжди виконую id >= 0, якщо це ваше
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Подібно до `afterFind()`, але ви можете зробити це з усіма записами!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// зробіть щось класне, як в afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Дуже корисно, якщо вам потрібно встановити деякі значення за замовчуванням щоразу.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// задайте добрі значення за замовчуванням
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

Можливо, у вас є випадок зміни даних після їх вставки?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// ви робите своє
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// або що-небудь....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Дуже корисно, якщо вам потрібно встановити деякі значення за замовчуванням щоразу при оновленні.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeUpdate(self $self) {
		// задайте добрі значення за замовчуванням
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Можливо, у вас є випадок зміни даних після оновлення?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterUpdate(self $self) {
		// ви робите своє
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// або що-небудь....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Це корисно, якщо ви хочете, щоб події відбувалися як під час вставок, так і під час оновлень. Я заощаджу вам довге пояснення, але я впевнений, що ви можете здогадатися, що це так.

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

Не знаю, що ви хочете робити тут, але я не суджу! Дій!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'Він був відважним солдатом... :cry-face:';
	} 
}
```

## Управління з'єднанням з базою даних

Коли ви використовуєте цю бібліотеку, ви можете налаштувати з'єднання з базою даних кількома різними способами. Ви можете налаштувати з'єднання в конструкторі, ви можете налаштувати його через змінну конфігурації `$config['connection']`, або ви можете налаштувати його через `setDatabaseConnection()` (v0.4.1). 

```php
$pdo_connection = new PDO('sqlite:test.db'); // наприклад
$user = new User($pdo_connection);
// або
$user = new User(null, [ 'connection' => $pdo_connection ]);
// або
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Якщо ви хочете уникнути необхідності встановлювати `$database_connection` щоразу, коли викликаєте активний запис, є способи уникнути цього!

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

// І тепер не потрібно аргументів!
$user = new User();
```

> **Примітка:** Якщо ви плануєте одиничне тестування, робити це таким чином може бути складно для одиничного тестування, але взагалі, оскільки ви можете впровадити своє 
з'єднання за допомогою `setDatabaseConnection()` або `$config['connection']`, це не надто погано.

Якщо вам потрібно оновити з'єднання з базою даних, наприклад, якщо ви запускаєте тривалу CLI програму і потрібно оновити з'єднання час від часу, ви можете перенастроїти з'єднання за допомогою `$your_record->setDatabaseConnection($pdo_connection)`.

## Співпраця

Будь ласка, зробіть це. :D

### Настройка

Коли ви будете робити внески, обов'язково запустіть `composer test-coverage`, щоб підтримувати 100% покриття тестами (це не справжнє покриття одиничних тестів, швидше інтеграційне тестування).

Також не забудьте запустити `composer beautify` і `composer phpcs`, щоб виправити всі помилки синтаксису.

## Ліцензія

MIT