# Flight Active Record

Активний запис - це відображення сутності бази даних на об'єкт PHP. Простими словами, якщо у вас є таблиця користувачів у вашій базі даних, ви можете "перекласти" рядок у цій таблиці в клас `User` і об'єкт `$user` у вашій кодовій базі. Дивіться [базовий приклад](#basic-example).

Натисніть [тут](https://github.com/flightphp/active-record) для доступу до репозиторію на GitHub.

## Базовий приклад

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
 * Клас ActiveRecord зазвичай є в однині
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
$database_connection = new PDO('sqlite:test.db'); // це лише для прикладу, ви, напевно, використовуватимете реальне з'єднання з базою даних

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
// не можна використовувати $user->save() тут, інакше це буде сприйнято як оновлення!

echo $user->id; // 2
```

І просто так легко додати нового користувача! Тепер, коли в базі даних існує рядок користувача, як його витягнути?

```php
$user->find(1); // знайти id = 1 у базі даних і повернути його.
echo $user->name; // 'Bobby Tables'
```

А що, якщо ви хочете знайти всіх користувачів?

```php
$users = $user->findAll();
```

Як щодо певної умови?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Бачите, скільки задоволення в цьому? Давайте встановимо це та почнемо!

## Встановлення

Просто встановіть за допомогою Composer

```php
composer require flightphp/active-record 
```

## Використання

Цей пакет можна використовувати як самостійна бібліотека або з PHP-фреймворком Flight. Все залежить від вас.

### Самостійно
Просто переконайтеся, що ви передали з'єднання PDO до конструктора.

```php
$pdo_connection = new PDO('sqlite:test.db'); // це лише для прикладу, ви, напевно, використовуватимете реальне з'єднання з базою даних

$User = new User($pdo_connection);
```

> Не хочете завжди встановлювати з'єднання з базою даних у конструкторі? Дивіться [Управління з'єднаннями з базами даних](#database-connection-management) для інших ідей!

### Зареєструвати як метод у Flight
Якщо ви використовуєте PHP-фреймворк Flight, ви можете зареєструвати клас ActiveRecord як сервіс, але це зовсім не обов'язково.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// після цього ви можете використовувати його ось так у контролері, функції тощо.

Flight::user()->find(1);
```

## Методи `runway`

[runway](/awesome-plugins/runway) - це CLI-інструмент для Flight, який має спеціальну команду для цієї бібліотеки.

```bash
# Використання
php runway make:record database_table_name [class_name]

# Приклад
php runway make:record users
```

Це створить новий клас у каталозі `app/records/` як `UserRecord.php` з таким вмістом:

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

Знайдіть один запис і призначте його поточному об'єкту. Якщо ви передасте `$id` якимось чином, він виконає пошук за первинним ключем з цим значенням. Якщо нічого не передано, він просто знайде перший запис у таблиці.

Додатково ви можете передати інші допоміжні методи для запиту вашої таблиці.

```php
// знайти запис з деякими умовами попередньо
$user->notNull('password')->orderBy('id DESC')->find();

// знайти запис за конкретним id
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Знаходить всі записи в таблиці, яку ви вказали.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

Повертає `true`, якщо поточний запис було гідратовано (отримано з бази даних).

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

Якщо у вас є текстовий первинний ключ (наприклад, UUID), ви можете встановити значення первинного ключа перед вставкою одним з двох способів.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // або $user->save();
```

або ви можете надати первинний ключ, який буде автоматично згенерований для вас через події.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// ви також можете встановити первинний ключ цим способом замість масиву вище.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // або якимось чином, як вам потрібно генерувати ваші унікальні id
	}
}
```

Якщо ви не встановите первинний ключ перед вставкою, він буде встановлено на `rowid`, а база даних згенерує його для вас, але він не збережеться, оскільки це поле може не існувати у вашій таблиці. Ось чому рекомендується використовувати подію, щоб автоматично впоратися з цим за вас.

#### `update(): boolean|ActiveRecord`

Оновлює поточний запис у базі даних.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Вставляє або оновлює поточний запис у базі даних. Якщо в записи є id, він оновить, в іншому випадку вставить.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Примітка:** Якщо у вас є відносини, визначені в класі, вони також зберігатимуться рекурсивно, якщо їх було визначено, ініціалізовано та були змінені дані, які потрібно оновити. (v0.4.0 та вище)

#### `delete(): boolean`

Видаляє поточний запис з бази даних.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Ви також можете видалити кілька записів, виконуючи пошук перед цим.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Брудні дані відносяться до даних, які були змінені в записі.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// нічого не є "брудним" на даний момент.

$user->email = 'test@example.com'; // тепер електронна пошта вважається "брудною", оскільки вона змінилася.
$user->update();
// тепер немає даних, які є брудними, оскільки вони були оновлені та збережені в базі даних

$user->password = password_hash('newpassword'); // тепер це брудне
$user->dirty(); // передача нічого очистить всі брудні записи.
$user->update(); // нічого не буде оновлено, оскільки нічого не було захоплено як брудне.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // і імя, і пароль оновлені.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Це псевдонім для методу `dirty()`. Це трохи ясніше, що ви робите.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // і імя, і пароль оновлені.
```

#### `isDirty(): boolean` (v0.4.0)

Повертає `true`, якщо поточний запис був змінений.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Скидає поточний запис до його початкового стану. Це дуже добре використовувати в циклі типу поведінки.
Якщо ви передасте `true`, він також скине дані запиту, які використовувалися для знаходження поточного об'єкта (поведінка за замовчуванням).

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

Після того, як ви виконаєте метод `find()`, `findAll()`, `insert()`, `update()` або `save()`, ви можете отримати SQL, який було побудовано, і використовувати його для відлагодження.

## Методи SQL запитів
#### `select(string $field1 [, string $field2 ... ])`

Ви можете вибрати лише кілька стовпців у таблиці, якщо хочете (це більш продуктивно для дійсно широких таблиць із багатьма стовпцями)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Ви можете технічно вибрати іншу таблицю! Чому б і ні?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Ви навіть можете об'єднатися з іншою таблицею в базі даних.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Ви можете встановити деякі користувальницькі аргументи where (не можете встановити параметри в цьому реченні where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Запобіжний захід** - ви можете бути спокушені зробити щось подібне до `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Будь ласка, НЕ РОБТЕ ЦЬОГО!!! Це піддається так званим атакам SQL-ін'єкцій. Є багато статей в Інтернеті, будь ласка, Google "sql injection attacks php", і ви знайдете багато статей на цю тему. Правильний спосіб впоратися з цим за допомогою цієї бібліотеки - замість цього методу `where()` ви будете робити щось на кшталт `$user->eq('id', $id)->eq('name', $name)->find();` Якщо ви абсолютно повинні зробити це, бібліотека `PDO` має `$pdo->quote($var)`, щоб екранувати це для вас. Лише після того, як ви використаєте `quote()`, ви можете використовувати це в реченні `where()`.

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

Обмежте кількість повернутих записів. Якщо вказано другий int, він буде зсунуто, що обмежить, як у SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## УМОВИ WHERE
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

Можливо, обернути ваші умови в оператор OR. Це зроблено або за допомогою методів `startWrap()` та `endWrap()`, або шляхом заповнення 3-го параметра умови після поля та значення.

```php
// Метод 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// Це буде оцінюватися як `id = 1 AND (name = 'demo' OR name = 'test')`

// Метод 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// Це буде оцінюватися як `id = 1 OR name = 'demo'`
```

## Відносини
Ви можете встановити кілька видів відносин, використовуючи цю бібліотеку. Ви можете встановити один->багато та один->один відносини між таблицями. Це потребує деякої додаткової настройки в класі заздалегідь.

Встановлення масиву `$relations` не є важким, але вгадувати правильний синтаксис може бути заплутаним.

```php
protected array $relations = [
	// ви можете назвати ключ як завгодно. Ім'я ActiveRecord, ймовірно, підходить. Наприклад: user, contact, client
	'user' => [
		// обов'язково
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // це тип відносини

		// обов'язково
		'Some_Class', // це інший клас ActiveRecord, на який це посилається

		// обов'язково
		// залежно від типу відношення
		// self::HAS_ONE = зовнішній ключ, що посилається на з'єднання
		// self::HAS_MANY = зовнішній ключ, що посилається на з'єднання
		// self::BELONGS_TO = локальний ключ, що посилається на з'єднання
		'local_or_foreign_key',
		// просто для вашої інформації, це також лише з'єднання з первинним ключем "іншої" моделі

		// необов'язково
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // додаткові умови, які ви хочете під час з'єднання
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// необов'язково
		'back_reference_name' // це якщо ви хочете зворотне посилання на цю відношення до самого себе Наприклад: $user->contact->user;
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

Тепер у нас є налаштування посилань, щоб ми могли використовувати їх дуже легко!

```php
$user = new User($pdo_connection);

// знайти найновішого користувача.
$user->notNull('id')->orderBy('id desc')->find();

// отримати контакти, використовуючи відношення:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// або можемо піти іншим шляхом.
$contact = new Contact();

// знайти одного контакту
$contact->find();

// отримати користувача, використовуючи відношення:
echo $contact->user->name; // це ім'я користувача
```

Досить круто, правда?

## Встановлення користувацьких даних
Іноді вам може знадобитися прикріпити щось унікальне до вашого ActiveRecord, наприклад, обчислення, яке може бути простіше просто прикріпити до об'єкта, який потім буде переданий, наприклад, шаблону.

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

Ще одна супер класна функція цієї бібліотеки - це події. Події викликаються у певний час на основі певних методів, які ви викликаєте. Вони дуже корисні для автоматичного налаштування даних.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Це дійсно корисно, якщо вам потрібно налаштувати підключення за замовчуванням або щось подібне.

```php
// index.php або bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // не забувайте про & посилання
		// ви можете зробити це, щоб автоматично встановити з'єднання
		$config['connection'] = Flight::db();
		// або це
		$self->transformAndPersistConnection(Flight::db());
		
		// Ви також можете встановити ім'я таблиці таким чином.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Це, мабуть, тільки корисно, якщо вам потрібна маніпуляція запитом кожного разу.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// завжди виконуйте id >= 0, якщо це ваша тема
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Це, мабуть, більш корисно, якщо вам завжди потрібно виконувати якусь логіку щоразу, коли цей запис витягується. Вам потрібно розшифрувати щось? Вам потрібно виконати спеціальний запит підрахунку щоразу (непродуктивно, але що вже)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// розшифровка чогось
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// можливо, зберігати щось особливе, наприклад, запит???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Це, мабуть, тільки корисно, якщо вам потрібно маніпулювати запитом кожного разу.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// завжди виконуйте id >= 0, якщо це ваша тема
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Схоже на `afterFind()`, але ви можете виконати це для всіх записів!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// зробіть щось круте, як у afterFind()
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
		// встановіть якісь звичні значення
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

Можливо, у вас є випадок використання для зміни даних після їх вставлення?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// ви робите, що хочете
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// або що завгодно....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Дуже корисно, якщо вам потрібно деякі значення за замовчуванням, встановлені щоразу під час оновлення.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// встановіть якісь звичні значення
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Можливо, у вас є випадок використання для зміни даних після його оновлення?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// ви робите, що хочете
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// або що завгодно....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Це корисно, якщо ви хочете, щоб події відбувалися як під час вставок, так і під час оновлень. Я не буду занадто довго пояснювати, але я впевнений, що ви можете вгадати, що це.

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

Не впевнений, що ви хотіли б тут зробити, але... без суджень! Йдіть на це!

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

## Управління з'єднаннями з базами даних

Коли ви використовуєте цю бібліотеку, ви можете встановити з'єднання з базою даних кількома різними способами. Ви можете встановити з'єднання в конструкторі, ви можете встановити його через змінну конфігурації `$config['connection']`, або ви можете встановити його за допомогою `setDatabaseConnection()` (v0.4.1). 

```php
$pdo_connection = new PDO('sqlite:test.db'); // для прикладу
$user = new User($pdo_connection);
// або
$user = new User(null, [ 'connection' => $pdo_connection ]);
// або
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Якщо ви хочете уникнути постійного встановлення `$database_connection` щоразу, коли ви викликаєте активний запис, є способи обійти це!

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

// І тепер, без аргументів!
$user = new User();
```

> **Примітка:** Якщо ви плануєте тестування одиниць, робити це таким чином може привнести деякі виклики в тестуванні одиниць, але в цілому, оскільки ви можете ін'єктувати своє 
з'єднання за допомогою `setDatabaseConnection()` або `$config['connection']`, це не так погано.

Якщо вам потрібно оновити з'єднання з базою даних, наприклад, якщо ви виконуєте довготривалу CLI-скрипт і вам потрібно оновлювати з'єднання час від часу, ви можете перевстановити з'єднання за допомогою `$your_record->setDatabaseConnection($pdo_connection)`.

## Участь

Будь ласка, зробіть це. :D

### Налаштування

Коли ви берете участь, переконайтеся, що ви запускаєте `composer test-coverage`, щоб підтримувати 100% покриття тестами (це не є справжнім покриттям тестами одиниць, швидше на кшталт інтеграційного тестування).

Також переконайтеся, що ви запускаєте `composer beautify` та `composer phpcs`, щоб виправити будь-які помилки форматування.

## Ліцензія

MIT