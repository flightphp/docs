# Flight Active Record

Активний запис (Active Record) — це відображення сутності бази даних на PHP-об’єкт. Простими словами, якщо у вашій базі даних є таблиця `users`, ви можете «перекласти» рядок із цієї таблиці в клас `User` та об’єкт `$user` у вашому коді. Дивіться [базовий приклад](#basic-example).

Натисніть [тут](https://github.com/flightphp/active-record), щоб переглянути репозиторій на GitHub.

## Базовий приклад

Припустимо, у вас є така таблиця:

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
 * Клас ActiveRecord зазвичай в однині
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
		// ви можете задати це так
		parent::__construct($database_connection, 'users');
		// або так
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Тепер спостерігайте за магією!

```php
// для sqlite
$database_connection = new PDO('sqlite:test.db'); // це лише для прикладу, ви, ймовірно, використовуєте реальне підключення до бази даних

// для mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// або mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// або mysqli без створення на основі об'єкта
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
// тут не можна використовувати $user->save(), інакше це буде вважатися оновленням!

echo $user->id; // 2
```

І це було так просто — додати нового користувача! Тепер, коли в базі даних є рядок користувача, як його отримати?

```php
$user->find(1); // знайти id = 1 у базі даних і повернути його.
echo $user->name; // 'Bobby Tables'
```

А що як ви хочете знайти всіх користувачів?

```php
$users = $user->findAll();
```

А з певною умовою?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Бачите, як це весело? Давайте встановимо це та почнемо!

## Встановлення

Просто встановіть за допомогою Composer

```php
composer require flightphp/active-record 
```

## Використання

Це можна використовувати як окрему бібліотеку або разом із PHP-фреймворком Flight. Цілком на ваш розсуд.

### Окремо
Просто переконайтеся, що ви передаєте PDO-підключення в конструктор.

```php
$pdo_connection = new PDO('sqlite:test.db'); // це лише для прикладу, ви, ймовірно, використовуєте реальне підключення до бази даних

$User = new User($pdo_connection);
```

> Не хочете завжди задавати підключення до бази даних у конструкторі? Перегляньте [Керування підключенням до бази даних](#database-connection-management) для інших ідей!

### Реєстрація як метод у Flight
Якщо ви використовуєте PHP-фреймворк Flight, ви можете зареєструвати клас ActiveRecord як сервіс, але, чесно кажучи, це не обов’язково.

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

Це створить новий клас у каталозі `app/records/` як `UserRecord.php` із наступним вмістом:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * Клас ActiveRecord для таблиці users.
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
     * @var array $relations Встановлює зв'язки для моделі
     *   https://docs.flightphp.com/en/v3/awesome-plugins/active-record#relationships
     */
    protected array $relations = [
		// 'relation_name' => [ self::HAS_MANY, 'RelatedClass', 'foreign_key' ],
	];

    /**
     * Конструктор
     * @param mixed $databaseConnection Підключення до бази даних
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

## CRUD-функції

#### `find($id = null) : boolean|ActiveRecord`

Знаходить один запис і призначає його поточному об’єкту. Якщо ви передаєте якийсь `$id`, він виконує пошук за первинним ключем із цим значенням. Якщо нічого не передано, він просто знаходить перший запис у таблиці.

Також ви можете передати йому інші допоміжні методи для запиту до вашої таблиці.

```php
// знайти запис із певними умовами заздалегідь
$user->notNull('password')->orderBy('id DESC')->find();

// знайти запис за конкретним id
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Знаходить усі записи в таблиці, яку ви вкажете.

```php
$user->findAll();
```

#### `first(): ActiveRecord` (v0.8.0)

Знаходить перший запис, який відповідає вашим умовам. Якщо ви не задали порядок сортування, він сортує за первинним ключем за зростанням. Якщо нічого не збігається, ви отримуєте запис без гідрації, тому перевіряйте `isHydrated()`, якщо не впевнені, що щось повернулося.

```php
$user->eq('status', 'active')->first();
```

#### `last(): ActiveRecord` (v0.8.0)

Те саме, що й `first()`, але сортує за первинним ключем за спаданням. Зручно для запитів на кшталт «дай мені найновіший».

```php
$user->eq('status', 'active')->last();
```

#### `count(): int` (v0.8.0)

Підраховує рядки, які відповідають вашим поточним умовам. Якщо у запиті є `groupBy()`, `count()` навмисно його ігнорує. Одне скалярне число не може представляти один рядок для кожної групи.

```php
$user->count();
$user->eq('status', 'active')->count();
```

#### `exists(): bool` (v0.8.0)

Повертає `true`, якщо будь-який запис відповідає вашим умовам. Під капотом виконується дешевий запит `SELECT 1 ... LIMIT 1`.

```php
$user->eq('name', 'Bobby')->exists(); // true
```

#### `pluck(string $column): array` (v0.8.0)

Повертає простий масив значень з однієї колонки замість гідрації купи об’єктів. Поєднуйте з `distinct()`, щоб отримати унікальні значення.

```php
$user->pluck('name'); // [ 'Bobby', 'Joseph' ]
$user->distinct()->pluck('status'); // [ 'active', 'inactive' ]
```

#### `ids(): array` (v0.8.0)

Скорочення для `pluck()` за первинним ключем.

```php
$user->gt('id', 0)->ids(); // [ 1, 2, 3 ]
```

#### `isHydrated(): boolean` (v0.4.0)

Повертає `true`, якщо поточний запис був гідратований (отриманий із бази даних).

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

Якщо у вас текстовий первинний ключ (наприклад, UUID), ви можете встановити значення первинного ключа перед вставкою одним із двох способів.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // або $user->save();
```

або ви можете дозволити автоматичну генерацію первинного ключа через події.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// ви також можете встановити primaryKey таким чином замість масиву вище.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // або як вам потрібно генерувати унікальні ідентифікатори
	}
}
```

Якщо ви не встановите первинний ключ перед вставкою, він буде встановлений на `rowid`, і база даних згенерує його для вас, але він не збережеться, оскільки такого поля може не існувати у вашій таблиці. Тому рекомендується використовувати подію для автоматичного оброблення цього.

#### `update(): boolean|ActiveRecord`

Оновлює поточний запис у базі даних.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `updateAttribute(string $name, mixed $value): ActiveRecord` (v0.8.0)

Оновлює одну колонку на завантаженому записі та зберігає його. Це скорочення для `$user->dirty([ 'name' => $value ])->update()`. Для цього потрібен завантажений запис.

```php
$user->find(1);
$user->updateAttribute('name', 'New Name');
```

#### `save(): boolean|ActiveRecord`

Вставляє або оновлює поточний запис у базі даних. Якщо запис має id, він оновлюється, інакше вставляється.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Примітка:** Якщо у класі визначені зв’язки, вони також будуть рекурсивно збережені, якщо вони були визначені, інстанційовані та мають брудні дані для оновлення. (v0.4.0 і вище)

#### `delete(): boolean`

Видаляє поточний запис із бази даних.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Ви також можете видалити кілька записів, виконавши пошук заздалегідь.

```php
$user->like('name', 'Bob%')->delete();
```

#### `updateAll(array $attributes, bool $allowEmptyConditions = false): int` (v0.8.0)

Оновлює всі записи, що відповідають вашим умовам, одним запитом. Жодні записи не гідратуються, і жодні події не спрацьовують — саме тому це швидко. Повертає кількість зачеплених рядків.

Він відмовляється працювати без умов WHERE, якщо ви не передасте `true` другим аргументом. Ваше майбутнє «я» дякує вам.

```php
$user->eq('status', 'inactive')->updateAll([ 'status' => 'active' ]);

// так, ви дійсно хочете оновити кожен рядок у таблиці
$user->updateAll([ 'status' => 'active' ], true);
```

#### `deleteAll(bool $allowEmptyConditions = false): int` (v0.8.0)

Видаляє всі записи, що відповідають вашим умовам, одним запитом. Те саме, що й `updateAll()`: без гідрації, без подій, і вимагає умови WHERE, якщо ви не передасте `true`. Повертає кількість видалених рядків. Використовуйте з обережністю!

```php
$user->eq('status', 'deleted')->deleteAll();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Брудні дані — це дані, які були змінені в записі.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// на цей момент нічого не є «брудним»

$user->email = 'test@example.com'; // тепер email вважається «брудним», оскільки він змінився.
$user->update();
// тепер немає брудних даних, оскільки вони були оновлені та збережені в базі даних

$user->password = password_hash()'newpassword'); // тепер це брудно
$user->dirty(); // передача без аргументів очистить усі брудні записи.
$user->update(); // нічого не оновиться, оскільки нічого не було захоплено як брудне.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // оновлюються і name, і password.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Це псевдонім для методу `dirty()`. Трохи зрозуміліше, що ви робите.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // оновлюються і name, і password.
```

#### `isDirty(): boolean` (v0.4.0)

Повертає `true`, якщо поточний запис був змінений.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Скидає поточний запис до його початкового стану. Це дуже корисно для циклічних поведінок. Якщо ви передасте `true`, також буде скинуто дані запиту, які використовувалися для пошуку поточного об’єкта (поведінка за замовчуванням).

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

Після виконання методу `find()`, `findAll()`, `insert()`, `update()` або `save()` ви можете отримати створений SQL і використати його для налагодження.

## Транзакції

Потрібно виконати кілька записів, які мають успішно завершитися разом? Обгорніть їх у `transaction()` (v0.8.0). Передайте йому callable, і запис надійде як аргумент. Якщо callable кидає виняток, усе відкочується, і виняток повторно викидається для вас. Інакше він фіксує зміни та повертає те, що повернув ваш callable.

```php
$user->transaction(function ($user) {
	$user->name = 'Bobby Tables';
	$user->password = password_hash('correct horse battery staple');
	$user->insert();

	$user->email = 'bobby@example.com';
	$user->update();
	// фіксація відбувається тут, якщо нічого не викинуло виняток
});
```

Вкладені транзакції не підтримуються (без savepoints), тому тримайте їх плоскими.

## Методи SQL-запитів
#### `select(string $field1 [, string $field2 ... ])`

Ви можете вибрати лише кілька колонок у таблиці, якщо хочете (це продуктивніше для дуже широких таблиць із багатьма колонками)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Технічно ви можете вибрати й іншу таблицю! Чому б і ні?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Ви навіть можете приєднати іншу таблицю в базі даних.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Ви можете задати власні умови where (у цьому виразі where не можна задавати параметри)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Примітка щодо безпеки** — Вам може захотітися зробити щось на кшталт `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. БУДЬ ЛАСКА, НЕ РОБІТЬ ЦЬОГО!!! Це вразливе до так званих SQL-ін’єкцій. В інтернеті багато статей, будь ласка, загугліть «sql injection attacks php», і ви знайдете багато статей на цю тему. Правильний спосіб обробки цього за допомогою цієї бібліотеки — замість методу `where()` використовувати щось на кшталт `$user->eq('id', $id)->eq('name', $name)->find();`. Якщо вам абсолютно необхідно це зробити, бібліотека `PDO` має `$pdo->quote($var)` для екранування. Лише після використання `quote()` ви можете використовувати це у виразі `where()`.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Групуйте результати за певною умовою.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Сортуйте повернений запит певним чином.

```php
$user->orderBy('name DESC')->find();
```

#### `orderByColumn(string $column, string $direction = 'ASC')` (v0.7.2)

`order()` і `orderBy()` приймають сирі SQL-фрагменти, що нормально, коли ви жорстко кодуєте `'name DESC'`. Якщо назва колонки надходить від користувача (наприклад, із заголовка таблиці, яку можна сортувати), використовуйте натомість `orderByColumn()`. Дозволені лише прості назви колонок і шляхи `table.column`, а напрямок має бути `ASC` або `DESC`, тому нічого вставити не можна.

```php
// $sortColumn надходить із запиту
$user->orderByColumn($sortColumn, 'DESC')->findAll();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Обмежте кількість повернених записів. Якщо передано друге ціле число, воно буде зсувом, лімітом, як у SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

#### `distinct()` (v0.8.0)

Додає `DISTINCT` до вашого наступного запиту. Працює зі звичайним select і з `pluck()`. `count()` ігнорує його, оскільки додавання `DISTINCT` до одного агрегованого рядка нічого не робить.

```php
$user->distinct()->pluck('status'); // [ 'active', 'inactive' ]
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

Можна обгорнути ваші умови у вираз OR. Це робиться за допомогою методів `startWrap()` та `endWrap()` або заповненням 3-го параметра умови після поля та значення.

```php
// Метод 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// Це буде обчислюватися як `id = 1 AND (name = 'demo' OR name = 'test')`

// Метод 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// Це буде обчислюватися як `id = 1 OR name = 'demo'`
```

## Скоупи

Скоупи (v0.8.0) — це багаторазові ланцюжки запитів, визначені як звичайні методи екземпляра у вашому класі, які повертають `$this`. Після того як ви написали один, він ланцюжково працює, як і будь-який інший метод запиту.

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

// і тепер ваші запити читаються як речення
(new User($pdo_connection))->active()->recent(30)->findAll();
```

Ви також можете викликати скоуп за ім’ям за допомогою `scope()`, що зручно, коли ім’я скоупа надходить з іншого місця у вашому коді. Він кидає `BadMethodCallException`, якщо метод не існує.

```php
$user->scope('active')->findAll();
$user->scope('recent', 30)->findAll();
```

## Зв’язки
Ви можете встановити кілька типів зв’язків за допомогою цієї бібліотеки. Ви можете встановити зв’язки один-до-багатьох і один-до-одного між таблицями. Для цього потрібне невелике додаткове налаштування в класі заздалегідь.

Встановити масив `$relations` не складно, але вгадати правильний синтаксис може бути заплутано.

```php
protected array $relations = [
	// ви можете назвати ключ як завгодно. Назва ActiveRecord, ймовірно, підійде. Напр.: user, contact, client
	'user' => [
		// обов'язково
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // це тип зв'язку

		// обов'язково
		'Some_Class', // це "інший" клас ActiveRecord, на який буде посилання

		// обов'язково
		// залежно від типу зв'язку
		// self::HAS_ONE = зовнішній ключ, який посилається на з'єднання
		// self::HAS_MANY = зовнішній ключ, який посилається на з'єднання
		// self::BELONGS_TO = локальний ключ, який посилається на з'єднання
		'local_or_foreign_key',
		// просто для інформації: це також з'єднується лише з первинним ключем "іншої" моделі

		// необов'язково
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // додаткові умови, які ви хочете застосувати при з'єднанні зв'язку
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// необов'язково
		'back_reference_name' // це якщо ви хочете повернути це посилання на сам зв'язок. Напр.: $user->contact->user;
	]
];
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

Тепер у нас налаштовані посилання, тож ми можемо дуже легко їх використовувати!

```php
$user = new User($pdo_connection);

// знайти найновішого користувача.
$user->notNull('id')->orderBy('id desc')->find();

// отримати контакти за допомогою зв'язку:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// або ми можемо піти іншим шляхом.
$contact = new Contact();

// знайти один контакт
$contact->find();

// отримати користувача за допомогою зв'язку:
echo $contact->user->name; // це ім'я користувача
```

Досить круто, правда?

### Жадібне завантаження

#### Огляд
Жадібне завантаження вирішує проблему N+1 запитів, завантажуючи зв’язки заздалегідь. Замість виконання окремого запиту для зв’язків кожного запису, жадібне завантаження отримує всі пов’язані дані лише одним додатковим запитом на кожен зв’язок.

> **Примітка:** Жадібне завантаження доступне лише для версії v0.7.0 і вище.

#### Базове використання
Використовуйте метод `with()`, щоб указати, які зв’язки завантажувати жадібно:
```php
// Завантажити користувачів разом із їхніми контактами двома запитами замість N+1
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    foreach ($u->contacts as $contact) {
        echo $contact->email; // Жодного додаткового запиту!
    }
}
```

#### Кілька зв’язків
Завантажуйте кілька зв’язків одночасно:
```php
$users = $user->with(['contacts', 'profile', 'settings'])->findAll();
```

#### Типи зв’язків

##### HAS_MANY
```php
// Жадібно завантажити всі контакти для кожного користувача
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    // $u->contacts уже завантажено як масив
    foreach ($u->contacts as $contact) {
        echo $contact->email;
    }
}
```
##### HAS_ONE
```php
// Жадібно завантажити один контакт для кожного користувача
$users = $user->with('contact')->findAll();
foreach ($users as $u) {
    // $u->contact уже завантажено як об'єкт
    echo $u->contact->email;
}
```

##### BELONGS_TO
```php
// Жадібно завантажити батьківських користувачів для всіх контактів
$contacts = $contact->with('user')->findAll();
foreach ($contacts as $c) {
    // $c->user уже завантажено
    echo $c->user->name;
}
```
##### З `find()`
Жадібне завантаження працює як із `findAll()`, так і з `find()`:

```php
$user = $user->with('contacts')->find(1);
// Користувача та всі його контакти завантажено двома запитами
```
#### Переваги продуктивності
Без жадібного завантаження (проблема N+1):
```php
$users = $user->findAll(); // 1 запит
foreach ($users as $u) {
    $contacts = $u->contacts; // N запитів (по одному для кожного користувача!)
}
// Разом: 1 + N запитів
```

Із жадібним завантаженням:

```php
$users = $user->with('contacts')->findAll(); // 2 запити загалом
foreach ($users as $u) {
    $contacts = $u->contacts; // 0 додаткових запитів!
}
// Разом: 2 запити (1 для користувачів + 1 для всіх контактів)
```
Для 10 користувачів це скорочує кількість запитів з 11 до 2 — зменшення на 82%!

#### Важливі примітки
- Жадібне завантаження повністю необов’язкове — ліниве завантаження продовжує працювати, як і раніше
- Уже завантажені зв’язки автоматично пропускаються
- Зворотні посилання працюють із жадібним завантаженням
- Callback-функції зв’язків враховуються під час жадібного завантаження

#### Обмеження
- Вкладене жадібне завантаження (наприклад, `with(['contacts.addresses'])`) наразі не підтримується
- Обмеження жадібного завантаження через замикання не підтримуються в цій версії

## Встановлення власних даних

Іноді вам може знадобитися прикріпити до вашого ActiveRecord щось унікальне, наприклад власне обчислення, яке може бути простіше просто прикріпити до об’єкта, а потім передати, скажімо, у шаблон.

#### `setCustomData(string $field, mixed $value)`

Ви прикріплюєте власні дані за допомогою методу `setCustomData()`.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

А потім просто посилаєтеся на нього, як на звичайну властивість об’єкта.

```php
echo $user->page_view_count;
```

## Часопозначки (Timestamps)

Якщо ваша таблиця має колонки `created_at` та `updated_at`, ви можете дозволити бібліотеці заповнювати їх за вас (v0.8.0). Встановіть `protected bool $timestamps = true;` у вашому класі, і вона встановить `created_at` та `updated_at` під час insert, а `updated_at` — під час update. Формат: `Y-m-d H:i:s`. Якщо ви самі встановлюєте будь-яку з цих колонок, бібліотека залишає ваше значення без змін.

```php
class User extends flight\ActiveRecord {
	protected bool $timestamps = true;

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}
}
```

Ваша таблиця насправді повинна мати ці колонки, інакше вставки та оновлення не виконаються.

## Події

Ще одна супер класна функція цієї бібліотеки — події. Події спрацьовують у певний час на основі певних методів, які ви викликаєте. Вони дуже-дуже корисні для автоматичного налаштування даних.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Це дуже корисно, якщо вам потрібно встановити підключення за замовчуванням або щось подібне.

```php
// index.php або bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // не забудьте посилання &
		// ви можете зробити це для автоматичного встановлення підключення
		$config['connection'] = Flight::db();
		// або це
		$self->transformAndPersistConnection(Flight::db());
		
		// Ви також можете встановити назву таблиці таким чином.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Це, ймовірно, корисно лише якщо вам потрібна маніпуляція запитом щоразу.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// завжди виконувати id >= 0, якщо це ваша фішка
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Цей, ймовірно, більш корисний, якщо вам завжди потрібно виконувати якусь логіку щоразу, коли цей запис отримується. Потрібно щось розшифрувати? Потрібно щоразу виконувати власний підрахунковий запит (не продуктивно, але байдуже)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// розшифрувати щось
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// можливо, зберегти щось власне, наприклад запит???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Це, ймовірно, корисно лише якщо вам потрібна маніпуляція запитом щоразу.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// завжди виконувати id >= 0, якщо це ваша фішка
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Схоже на `afterFind()`, але ви робите це для всіх записів одразу!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// зробіть щось круте, наприклад, як у afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Дуже корисно, якщо вам потрібно щоразу встановлювати деякі значення за замовчуванням.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// встановити надійні значення за замовчуванням
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
		// або щось інше....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Дуже корисно, якщо вам потрібно щоразу встановлювати деякі значення за замовчуванням під час оновлення.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// встановити надійні значення за замовчуванням
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
		// або щось інше....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Це корисно, якщо ви хочете, щоб події відбувалися як під час вставки, так і під час оновлення. Я позбавлю вас довгого пояснення, але впевнений, що ви можете здогадатися, що це.

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

Не знаю, що б ви хотіли тут зробити, але без засуджень! Дерзайте!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'Він був хоробрим солдатом... :cry-face:';
	} 
}
```

## Керування підключенням до бази даних

Коли ви використовуєте цю бібліотеку, ви можете встановити підключення до бази даних кількома різними способами. Ви можете встановити підключення в конструкторі, через змінну конфігурації `$config['connection']` або через `setDatabaseConnection()` (v0.4.1).

```php
$pdo_connection = new PDO('sqlite:test.db'); // наприклад
$user = new User($pdo_connection);
// або
$user = new User(null, [ 'connection' => $pdo_connection ]);
// або
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Якщо ви хочете уникнути постійного встановлення `$database_connection` щоразу, коли викликаєте active record, є способи це обійти!

```php
// index.php або bootstrap.php
// Зареєструйте це як зареєстрований клас у Flight
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

// User.php
class User extends flight\ActiveRecord {
	
	public function __construct(array $config = [])
	{
		$database_connection = $config['connection'] ?? Flight::db();
		parent::__construct($database_connection, 'users', $config);
	}
}

// І тепер аргументи не потрібні!
$user = new User();
```

> **Примітка:** Якщо ви плануєте модульне тестування, такий спосіб може додати певні труднощі, але загалом, оскільки ви можете впровадити своє підключення через `setDatabaseConnection()` або `$config['connection']`, це не так вже й погано.

Якщо вам потрібно оновити підключення до бази даних, наприклад, якщо ви запускаєте довготривалий CLI-скрипт і вам потрібно час від часу оновлювати підключення, ви можете перевстановити підключення за допомогою `$your_record->setDatabaseConnection($pdo_connection)`.

## Долучення до розробки

Будь ласка, долучайтеся. :D

### Налаштування

Коли ви долучаєтеся, переконайтеся, що ви запускаєте `composer test-coverage`, щоб підтримувати 100% покриття тестами (це не справжнє покриття модульними тестами, а скоріше інтеграційне тестування).

Також переконайтеся, що ви запускаєте `composer beautify` та `composer phpcs`, щоб виправити будь-які помилки лінтингу.

## Ліцензія

MIT