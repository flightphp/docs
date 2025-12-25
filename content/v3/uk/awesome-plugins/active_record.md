# Flight Active Record 

Активний запис — це відображення сутності бази даних на об'єкт PHP. Простіше кажучи, якщо у вас є таблиця users у базі даних, ви можете "перекласти" рядок у цій таблиці на клас `User` та об'єкт `$user` у вашому коді. Див. [основний приклад](#basic-example).

Натисніть [тут](https://github.com/flightphp/active-record) для репозиторію на GitHub.

## Основний приклад

Припустимо, у вас є така таблиця:

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
 * Дуже рекомендується додавати властивості таблиці як коментарі тут
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// ви можете налаштувати це таким чином
		parent::__construct($database_connection, 'users');
		// або таким чином
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Тепер спостерігайте за магією!

```php
// для sqlite
$database_connection = new PDO('sqlite:test.db'); // це просто для прикладу, ви ймовірно використовуватимете реальне з'єднання з базою даних

// для mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// або mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// або mysqli з створенням не на основі об'єкта
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
// не можна використовувати $user->save() тут, інакше це вважатиме оновленням!

echo $user->id; // 2
```

І було так просто додати нового користувача! Тепер, коли в базі даних є рядок користувача, як ви його витягнете?

```php
$user->find(1); // знаходить id = 1 у базі даних і повертає його.
echo $user->name; // 'Bobby Tables'
```

А що, якщо ви хочете знайти всіх користувачів?

```php
$users = $user->findAll();
```

А з певною умовою?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Бачите, наскільки це весело? Давайте встановимо це та почнемо!

## Встановлення

Просто встановіть за допомогою Composer

```php
composer require flightphp/active-record 
```

## Використання

Це можна використовувати як самостійну бібліотеку або з PHP Framework Flight. Повністю залежить від вас.

### Самостійно
Просто переконайтеся, що ви передаєте з'єднання PDO до конструктора.

```php
$pdo_connection = new PDO('sqlite:test.db'); // це просто для прикладу, ви ймовірно використовуватимете реальне з'єднання з базою даних

$User = new User($pdo_connection);
```

> Не хочете завжди встановлювати з'єднання з базою даних у конструкторі? Див. [Керування з'єднанням з базою даних](#database-connection-management) для інших ідей!

### Реєстрація як методу у Flight
Якщо ви використовуєте PHP Framework Flight, ви можете зареєструвати клас ActiveRecord як сервіс, але чесно кажучи, це не обов'язково.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// потім ви можете використовувати це так у контролері, функції тощо.

Flight::user()->find(1);
```

## Методи `runway`

[runway](/awesome-plugins/runway) — це CLI-інструмент для Flight, який має спеціальну команду для цієї бібліотеки. 

```bash
# Використання
php runway make:record database_table_name [class_name]

# Приклад
php runway make:record users
```

Це створить новий клас у директорії `app/records/` як `UserRecord.php` з таким вмістом:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Клас ActiveRecord для таблиці users.
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
     * @var array $relations Встановлює відносини для моделі
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

## Функції CRUD

#### `find($id = null) : boolean|ActiveRecord`

Знаходить один запис і призначає його поточному об'єкту. Якщо ви передаєте `$id` якогось роду, це виконає пошук за первинним ключем з цим значенням. Якщо нічого не передано, це просто знайде перший запис у таблиці.

Додатково ви можете передати інші допоміжні методи для запиту таблиці.

```php
// знаходить запис з деякими умовами заздалегідь
$user->notNull('password')->orderBy('id DESC')->find();

// знаходить запис за конкретним id
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Знаходить всі записи в таблиці, яку ви вказуєте.

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

##### Первинні ключі на основі тексту

Якщо у вас є первинний ключ на основі тексту (наприклад, UUID), ви можете встановити значення первинного ключа перед вставкою одним з двох способів.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // або $user->save();
```

або ви можете мати автоматично згенерований первинний ключ для вас через події.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// ви також можете встановити primaryKey таким чином замість масиву вище.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // або як вам потрібно генерувати унікальні id
	}
}
```

Якщо ви не встановите первинний ключ перед вставкою, він буде встановлено на `rowid`, і база даних згенерує його для вас, але він не збережеться, оскільки це поле може не існувати в вашій таблиці. Тому рекомендується використовувати подію для автоматичного керування цим.

#### `update(): boolean|ActiveRecord`

Оновлює поточний запис у базі даних.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Вставляє або оновлює поточний запис у базі даних. Якщо запис має id, це оновить, інакше вставить.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Примітка:** Якщо у вас визначені відносини в класі, це рекурсивно збереже ці відносини, якщо вони визначені, створені та мають брудні дані для оновлення. (v0.4.0 та вище)

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

Брудні дані стосуються даних, які були змінені в записі.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// на цьому етапі нічого не є "брудним".

$user->email = 'test@example.com'; // тепер email вважається "брудним", оскільки він змінений.
$user->update();
// тепер немає даних, які є брудними, оскільки вони оновлені та збережені в базі даних

$user->password = password_hash()'newpassword'); // тепер це брудне
$user->dirty(); // передача нічого очистить всі брудні записи.
$user->update(); // нічого не оновиться, оскільки нічого не було захоплено як брудне.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // обидва name та password оновлені.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Це псевдонім для методу `dirty()`. Трохи зрозуміліше, що ви робите.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // обидва name та password оновлені.
```

#### `isDirty(): boolean` (v0.4.0)

Повертає `true`, якщо поточний запис був змінений.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Скидає поточний запис до його початкового стану. Це дуже корисно для поведінки типу циклу.
Якщо ви передасте `true`, це також скине дані запиту, які використовувалися для пошуку поточного об'єкта (поведінка за замовчуванням).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // почніть з чистого аркуша
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Після виконання методу `find()`, `findAll()`, `insert()`, `update()` або `save()` ви можете отримати SQL, який був побудований, і використовувати його для цілей налагодження.

## Методи SQL-запиту
#### `select(string $field1 [, string $field2 ... ])`

Ви можете вибрати лише кілька стовпців у таблиці, якщо бажаєте (це ефективніше для дуже широких таблиць з багатьма стовпцями)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Ви технічно можете вибрати іншу таблицю також! Чому б і ні?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Ви навіть можете приєднатися до іншої таблиці в базі даних.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Ви можете встановити деякі власні аргументи where (ви не можете встановити параметри в цій інструкції where)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Примітка щодо безпеки** - Ви можете бути спокушені зробити щось на кшталт `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. Будь ласка, НЕ РОБІТЬ ЦЬОГО!!! Це вразливе до того, що відомо як атаки SQL-ін'єкцій. Є багато статей онлайн, будь ласка, погугліть "sql injection attacks php" і ви знайдете багато статей на цю тему. Правильний спосіб обробити це з цією бібліотекою — замість цього методу `where()`, ви б зробили щось на кшталт `$user->eq('id', $id)->eq('name', $name)->find();` Якщо вам абсолютно необхідно це зробити, бібліотека `PDO` має `$pdo->quote($var)` для екранування. Тільки після використання `quote()` ви можете використовувати це в інструкції `where()`.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Групуйте ваші результати за певною умовою.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Сортуйте повернутий запит певним чином.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Обмежте кількість повернених записів. Якщо надано другий int, це буде зсув, обмеження, як у SQL.

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

Можна обгорнути ваші умови в інструкцію OR. Це робиться за допомогою методу `startWrap()` та `endWrap()` або заповненням 3-го параметра умови після поля та значення.

```php
// Метод 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// Це оцінюється як `id = 1 AND (name = 'demo' OR name = 'test')`

// Метод 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// Це оцінюється як `id = 1 OR name = 'demo'`
```

## Відносини
Ви можете встановити кілька видів відносин за допомогою цієї бібліотеки. Ви можете встановити відносини один-до-багатьох та один-до-одного між таблицями. Це вимагає трохи додаткового налаштування в класі заздалегідь.

Встановлення масиву `$relations` не важко, але вгадування правильного синтаксису може бути заплутаним.

```php
protected array $relations = [
	// ви можете назвати ключ будь-як. Назва ActiveRecord, ймовірно, хороша. Наприклад: user, contact, client
	'user' => [
		// обов'язково
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // це тип відносини

		// обов'язково
		'Some_Class', // це "інший" клас ActiveRecord, на який буде посилання

		// обов'язково
		// залежно від типу відносини
		// self::HAS_ONE = зовнішній ключ, що посилається на з'єднання
		// self::HAS_MANY = зовнішній ключ, що посилається на з'єднання
		// self::BELONGS_TO = локальний ключ, що посилається на з'єднання
		'local_or_foreign_key',
		// просто для інформації, це також приєднує лише до первинного ключа "іншої" моделі

		// необов'язково
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // додаткові умови, які ви хочете при приєднанні відносини
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// необов'язково
		'back_reference_name' // це якщо ви хочете посилатися назад на цю відносини Ex: $user->contact->user;
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

Тепер у нас налаштовані посилання, тому ми можемо використовувати їх дуже легко!

```php
$user = new User($pdo_connection);

// знаходимо найновішого користувача.
$user->notNull('id')->orderBy('id desc')->find();

// отримуємо контакти за допомогою відносини:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// або ми можемо піти іншим шляхом.
$contact = new Contact();

// знаходимо один контакт
$contact->find();

// отримуємо користувача за допомогою відносини:
echo $contact->user->name; // це ім'я користувача
```

Досить круто, еге?

### Eager Loading

#### Огляд
Eager loading розв'язує проблему N+1 запитів, завантажуючи відносини заздалегідь. Замість виконання окремого запиту для відносин кожного запису, eager loading отримує всі пов'язані дані лише в одному додатковому запиті на відносини.

> **Примітка:** Eager loading доступний лише для v0.7.0 та вище.

#### Основне використання
Використовуйте метод `with()` для вказівки, які відносини завантажити заздалегідь:
```php
// Завантажуємо користувачів з їх контактами в 2 запити замість N+1
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    foreach ($u->contacts as $contact) {
        echo $contact->email; // Без додаткового запиту!
    }
}
```

#### Кілька відносин
Завантажуйте кілька відносин одразу:
```php
$users = $user->with(['contacts', 'profile', 'settings'])->findAll();
```

#### Типи відносин

##### HAS_MANY
```php
// Eager завантажуємо всі контакти для кожного користувача
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    // $u->contacts вже завантажено як масив
    foreach ($u->contacts as $contact) {
        echo $contact->email;
    }
}
```
##### HAS_ONE
```php
// Eager завантажуємо один контакт для кожного користувача
$users = $user->with('contact')->findAll();
foreach ($users as $u) {
    // $u->contact вже завантажено як об'єкт
    echo $u->contact->email;
}
```

##### BELONGS_TO
```php
// Eager завантажуємо батьківських користувачів для всіх контактів
$contacts = $contact->with('user')->findAll();
foreach ($contacts as $c) {
    // $c->user вже завантажено
    echo $c->user->name;
}
```
##### З find()
Eager loading працює як з 
findAll()
, так і з 
find()
:

```php
$user = $user->with('contacts')->find(1);
// Користувач і всі їхні контакти завантажені в 2 запити
```
#### Переваги продуктивності
Без eager loading (проблема N+1):
```php
$users = $user->findAll(); // 1 запит
foreach ($users as $u) {
    $contacts = $u->contacts; // N запитів (один на користувача!)
}
// Всього: 1 + N запитів
```

З eager loading:

```php
$users = $user->with('contacts')->findAll(); // 2 запити всього
foreach ($users as $u) {
    $contacts = $u->contacts; // 0 додаткових запитів!
}
// Всього: 2 запити (1 для користувачів + 1 для всіх контактів)
```
Для 10 користувачів це зменшує запити з 11 до 2 — зменшення на 82%!

#### Важливі примітки
- Eager loading повністю необов'язковий — lazy loading все ще працює як раніше
- Вже завантажені відносини автоматично пропускаються
- Зворотні посилання працюють з eager loading
- Колбеки відносин поважаються під час eager loading

#### Обмеження
- Вкладене eager loading (наприклад, 
with(['contacts.addresses'])
) наразі не підтримується
- Обмеження eager завантаження через замикання не підтримуються в цій версії

## Встановлення власних даних
Іноді вам може знадобитися прикріпити щось унікальне до вашого ActiveRecord, наприклад, власний розрахунок, який може бути простішим прикріпити до об'єкта, який потім передається, скажімо, шаблону.

#### `setCustomData(string $field, mixed $value)`
Ви прикріплюєте власні дані за допомогою методу `setCustomData()`.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

А потім ви просто посилаєтеся на це як на звичайну властивість об'єкта.

```php
echo $user->page_view_count;
```

## Події

Ще одна супер крута функція цієї бібліотеки — це події. Події запускаються в певні моменти на основі певних методів, які ви викликаєте. Вони дуже корисні для автоматичного налаштування даних для вас.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Це дуже корисно, якщо вам потрібно встановити з'єднання за замовчуванням або щось подібне.

```php
// index.php або bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // не забудьте посилання &
		// ви могли б зробити це для автоматичного встановлення з'єднання
		$config['connection'] = Flight::db();
		// або це
		$self->transformAndPersistConnection(Flight::db());
		
		// Ви також можете встановити назву таблиці таким чином.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Це, ймовірно, корисно лише якщо вам потрібно маніпулювати запитом кожного разу.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// завжди запускайте id >= 0, якщо це ваш стиль
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Цей, ймовірно, корисніший, якщо вам завжди потрібно запускати деяку логіку кожного разу, коли цей запис отримується. Вам потрібно дешифрувати щось? Вам потрібно запускати власний запит підрахунку кожного разу (не ефективно, але ну)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// дешифрування чогось
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// можливо, зберігання чогось власного, як запит???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Це, ймовірно, корисно лише якщо вам потрібно маніпулювати запитом кожного разу.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// завжди запускайте id >= 0, якщо це ваш стиль
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Подібно до `afterFind()`, але ви можете зробити це для всіх записів!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// робіть щось круте, як afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Дуже корисно, якщо вам потрібно встановити деякі значення за замовчуванням кожного разу.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// встановіть деякі розумні значення за замовчуванням
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

Можливо, у вас є випадок використання для зміни даних після вставки?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// робіть, що хочете
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// або що завгодно....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Дуже корисно, якщо вам потрібно встановити деякі значення за замовчуванням кожного разу під час оновлення.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// встановіть деякі розумні значення за замовчуванням
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Можливо, у вас є випадок використання для зміни даних після оновлення?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// робіть, що хочете
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// або що завгодно....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Це корисно, якщо ви хочете, щоб події відбувалися як під час вставок, так і під час оновлень. Я пощаджу вас довгим поясненням, але я впевнений, що ви можете здогадатися, що це таке.

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

Не впевнений, що ви хотіли б зробити тут, але ніяких суджень! Рухайтеся!

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

## Керування з'єднанням з базою даних

Коли ви використовуєте цю бібліотеку, ви можете встановити з'єднання з базою даних кількома різними способами. Ви можете встановити з'єднання в конструкторі, ви можете встановити його через змінну конфігурації `$config['connection']` або ви можете встановити його через `setDatabaseConnection()` (v0.4.1). 

```php
$pdo_connection = new PDO('sqlite:test.db'); // для прикладу
$user = new User($pdo_connection);
// або
$user = new User(null, [ 'connection' => $pdo_connection ]);
// або
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Якщо ви хочете уникнути завжди встановлювати `$database_connection` кожного разу, коли викликаєте active record, є способи обійти це!

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

// І тепер не потрібні аргументи!
$user = new User();
```

> **Примітка:** Якщо ви плануєте unit-тестування, робити це таким чином може додати деякі виклики до unit-тестування, але загалом, оскільки ви можете інжектувати ваше з'єднання з `setDatabaseConnection()` або `$config['connection']`, це не так погано.

Якщо вам потрібно оновити з'єднання з базою даних, наприклад, якщо ви запускаєте довготривалий CLI-скрипт і потрібно оновлювати з'єднання час від часу, ви можете переустановити з'єднання з `$your_record->setDatabaseConnection($pdo_connection)`.

## Співпраця

Будь ласка, робіть. :D

### Налаштування

Коли ви сприяєте, переконайтеся, що ви запускаєте `composer test-coverage`, щоб підтримувати 100% покриття тестами (це не справжнє покриття unit-тестів, більше як інтеграційне тестування).

Також переконайтеся, що ви запускаєте `composer beautify` та `composer phpcs`, щоб виправити будь-які помилки лінтингу.

## Ліцензія

MIT