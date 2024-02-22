# FlightPHP Aktives Aufzeichnungsmodell

Ein Active Record mappt eine Datenbankeinheit auf ein PHP-Objekt. Einfach ausgedrückt, wenn Sie eine Benutzertabelle in Ihrer Datenbank haben, können Sie eine Zeile in dieser Tabelle in eine `User`-Klasse und ein `$user`-Objekt in Ihrem Code umwandeln. Siehe [Grundbeispiel](#grundbeispiel).

## Grundbeispiel

Angenommen, Sie haben die folgende Tabelle:

```sql
CREATE TABLE Benutzer (
	id INTEGER PRIMARY KEY,
	name TEXT,
	password TEXT
);
```

Jetzt können Sie eine neue Klasse einrichten, um diese Tabelle darzustellen:

```php
/**
* Eine Active Record-Klasse ist in der Regel im Singular
*
* Es wird dringend empfohlen, die Eigenschaften der Tabelle hier als Kommentar hinzuzufügen
*
* @property int    $id
* @property string $name
* @property string $password
*/
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// Sie können es auf diese Weise festlegen
		parent::__construct($database_connection, 'users');
		// oder auf diese Weise
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Schauen Sie sich jetzt an, was passiert!

```php
// für sqlite
$database_connection = new PDO('sqlite:test.db'); // dies ist nur ein Beispiel, Sie würden wahrscheinlich eine echte Datenbankverbindung verwenden

// für mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'benutzername', 'passwort');

// oder mysqli
$database_connection = new mysqli('localhost', 'benutzername', 'passwort', 'test_db');
// oder mysqli mit nicht objektbasierter Erstellung
$database_connection = mysqli_connect('localhost', 'benutzername', 'passwort', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('ein cooles Passwort');
$user->einfügen();
// oder $user->speichern();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('nochmal ein cooles Passwort!!!');
$user->einfügen();
// kann hier $user->speichern() nicht verwenden, sonst denkt es, dass es ein Update ist!

echo $user->id; // 2
```

Und es war genauso einfach, einen neuen Benutzer hinzuzufügen! Da jetzt eine Benutzerzeile in der Datenbank vorhanden ist, wie holen Sie sie heraus?

```php
$user->find(1); // finde id = 1 in der Datenbank und gib es zurück.
echo $user->name; // 'Bobby Tables'
```

Und wenn Sie alle Benutzer finden möchten?

```php
$benutzer = $benutzer->findeAlle();
```

Was ist mit einer bestimmten Bedingung?

```php
$benutzer = $benutzer->like('name', '%mamma%')->findAll();
```

Sehen Sie, wie viel Spaß das macht? Lassen Sie uns es installieren und loslegen!

## Installation

Einfach mit Composer installieren

```php
composer require flightphp/active-record 
```

## Verwendung

Dies kann als eigenständige Bibliothek oder mit dem Flight PHP Framework verwendet werden. Ganz Ihnen überlassen.

### Eigenständig
Stellen Sie einfach sicher, dass Sie eine PDO-Verbindung dem Konstruktor übergeben.

```php
$pdo_verbindung = new PDO('sqlite:test.db'); // dies ist nur ein Beispiel, Sie würden wahrscheinlich eine echte Datenbankverbindung verwenden

$Benutzer = new Benutzer($pdo_verbindung);
```

### Flight PHP Framework
Wenn Sie das Flight PHP Framework verwenden, können Sie die ActiveRecord-Klasse als Service registrieren (aber Sie müssen ehrlich gesagt nicht).

```php
Flight::register('benutzer', 'Benutzer', [ $pdo_verbindung ]);

// dann können Sie es so in einem Controller, einer Funktion usw. verwenden.

Flight::benutzer()->find(1);
```

## API-Referenz
### CRUD-Funktionen

#### `find($id = null) : boolean|AktiverDatensatz`

Suchen Sie einen Datensatz und weisen Sie ihn dem aktuellen Objekt zu. Wenn Sie eine `$id` irgendeiner Art übergeben, führt es eine Abfrage auf dem Primärschlüssel mit diesem Wert durch. Wenn nichts übergeben wird, wird einfach der erste Datensatz in der Tabelle gefunden.

Zusätzlich können Sie ihm weitere Hilfsmethoden übergeben, um Ihre Tabelle abzufragen.

```php
// finde einen Datensatz mit einigen Bedingungen im Voraus
$benutzer->notNull('password')->orderBy('id DESC')->find();

// finde einen Datensatz anhand einer spezifischen ID
$id = 123;
$benutzer->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Findet alle Datensätze in der von Ihnen angegebenen Tabelle.

```php
$benutzer->findeAlle();
```

#### `einfügen(): boolean|AktiverDatensatz`

Fügt den aktuellen Datensatz in die Datenbank ein.

```php
$user = new Benutzer($pdo_verbindung);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

#### `aktualisieren(): boolean|AktiverDatensatz`

Aktualisiert den aktuellen Datensatz in der Datenbank.

```php
$benutzer->greaterThan('id', 0)->orderBy('id desc')->find();
$benutzer->email = 'test@example.com';
$benutzer->update();
```

#### `löschen(): boolean`

Löscht den aktuellen Datensatz aus der Datenbank.

```php
$benutzer->gt('id', 0)->orderBy('id desc')->find();
$benutzer->delete();
```

Sie können auch mehrere Datensätze löschen, indem Sie zuvor eine Suche ausführen.

```php
$benutzer->like('name', 'Bob%')->delete();
```

#### `dirty(array $dirty = []): AktiverDatensatz`

Dirty-Daten beziehen sich auf die Daten, die in einem Datensatz geändert wurden.

```php
$benutzer->greaterThan('id', 0)->orderBy('id desc')->find();

// bis zu diesem Zeitpunkt ist nichts „dirty“.

$benutzer->email = 'test@example.com'; // jetzt wird die E-Mail als "dirty" betrachtet, da sie geändert wurde.
$user->update();
// jetzt gibt es keine „dirty“-Daten mehr, weil sie aktualisiert und in der Datenbank gespeichert wurden

$benutzer->password = password_hash()'neuespasswort'); // jetzt ist es dirty
$benutzer->dirty(); // ohne Angabe wird alle „dirty“-Einträge gelöscht.
$benutzer->update(); // nichts wird aktualisiert, da nichts als „dirty“ erfasst wurde.

$benutzer->dirty([ 'name' => 'etwas', 'password' => password_hash('ein anderes Passwort') ]);
$benutzer->update(); // sowohl Name als auch Passwort werden aktualisiert.
```

#### `zurücksetzen(bool $include_query_data = true): AktiverDatensatz`

Setzt den aktuellen Datensatz auf seinen ursprünglichen Zustand zurück. Dies ist wirklich gut für Schleifenverhalten zu verwenden.
Wenn Sie `true` übergeben, werden auch die Abfragedaten zurückgesetzt, die verwendet wurden, um das aktuelle Objekt zu finden (Standardverhalten).

```php
$benutzer = $benutzer->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_verbindung);

foreach($users as $user) {
	$user_company->zurücksetzen(); // mit einem sauberen Blatt beginnen
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

### SQL-Abfragemethoden
#### `select(string $field1 [, string $field2 ... ])`

Sie können nur einige der Spalten in einer Tabelle auswählen, wenn Sie möchten (es ist performanter auf sehr breiten Tabellen mit vielen Spalten)

```php
$benutzer->select('id', 'name')->find();
```

#### `from(string $table)`

Sie können technisch gesehen eine andere Tabelle auswählen! Warum auch nicht?!

```php
$benutzer->select('id', 'name')->from('benutzer')->find();
```

#### `join(string $table_name, string $join_condition)`

Sie können sogar mit einer anderen Tabelle in der Datenbank verbinden.

```php
$benutzer->join('kontakte', 'kontakte.benutzer_id = benutzer.id')->find();
```

#### `where(string $where_conditions)`

Sie können benutzerdefinierte where-Argumente festlegen (Sie können in dieser where-Anweisung keine Parameter setzen)

```php
$benutzer->where('id=1 AND name="demo"')->find();
```

**Sicherheitshinweis** - Sie könnten in Versuchung geraten, etwas wie `$benutzer->where("id = '{$id}' AND name = '{$name}'")->find();` zu tun. Bitte TUN SIE DAS NICHT!!! Dies ist anfällig für sogenannte SQL-Injection-Angriffe. Es gibt viele Artikel online, suchen Sie bitte nach "SQL-Injection-Angriffe in PHP", und Sie werden viele Artikel zu diesem Thema finden. Der richtige Umgang damit in dieser Bibliothek wäre anstelle dieser `where()`-Methode etwas wie `$benutzer->eq('id', $id)->eq('name', $name)->find();`

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Gruppieren Sie Ihre Ergebnisse nach einer bestimmten Bedingung.

```php
$benutzer->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Sortieren Sie die abgerufene Abfrage auf eine bestimmte Weise.

```php
$benutzer->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Begrenzen Sie die Anzahl der zurückgegebenen Datensätze. Wenn ein zweites int übergeben wird, handelt es sich um Offset, Limit wie in SQL.

```php
$benutzer->orderby('name DESC')->limit(0, 10)->findeAlle();
```

### WHERE-Bedingungen
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

Wo `field = $value`

```php
$benutzer->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

Wo `field <> $value`

```php
$benutzer->ne('id', 1)->find();
```

#### `isNull(string $field)`

Wo `field IS NULL`

```php
$benutzer->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

Wo `field IS NOT NULL`

```php
$benutzer->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

Wo `field > $value`

```php
$benutzer->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

Wo `field < $value`

```php
$benutzer->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

Wo `field >= $value`

```php
$benutzer->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

Wo `field <= $value`

```php
$benutzer->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

Wo `field LIKE $value` oder `field NOT LIKE $value`

```php
$benutzer->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

Wo `field IN($value)` oder `field NOT IN($value)`

```php
$benutzer->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Wo `field BETWEEN $value AND $value1`

```php
$benutzer->between('id', [1, 2])->find();
```

### Beziehungen
Sie können mit dieser Bibliothek verschiedene Arten von Beziehungen einrichten. Sie können One-to-Many- und One-to-One-Beziehungen zwischen Tabellen festlegen. Dies erfordert etwas zusätzliche Konfiguration in der Klasse im Voraus.

Es ist nicht schwer, das `$relations`-Array festzulegen, aber das richtige Syntax erraten kann verwirrend sein.

```php
geschütztes Array $relations = [
	// Sie können den Schlüssel beliebig nennen. Der Name des Active Records ist wahrscheinlich gut. Z.B.: Benutzer, Kontakt, Client
	'what_ever_active_record' => [
		// erforderlich
		self::HAS_ONE, // dies ist der Beziehungstyp

		// erforderlich
		'Einige_Klasse', // dies ist die "andere" ActiveRecord-Klasse, auf die verwiesen wird

		// erforderlich
		'local_key', // dies ist der lokale Schlüssel, der auf den Join verweist.
		// nur zur Information, dies verbindet auch nur mit dem Primärschlüssel des "anderen" Modells

		// optional
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // benutzerdefinierte Methoden, die Sie ausführen möchten. [] wenn Sie keine möchten.

		// optional
		'back_reference_name' // dies ist, wenn Sie diese Beziehung selbst zurückreferenzieren möchten z.B.: $user->contact->user;
	];
]
```

```php
class User extends ActiveRecord{
	geschütztes Array $relations = [
		'kontakte' => [ self::HAS_MANY, Kontakt::class, 'benutzer_id' ],
		'kontakt' => [ self::HAS_ONE, Kontakt::class, 'benutzer_id' ],
	];

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'benutzer');
	}
}

class Kontakt extends ActiveRecord{
	geschütztes Array $relations = [
		'user' => [ self::BELONGS_TO, Benutzer::class, 'benutzer_id' ],
		'user_with_backref' => [ self::BELONGS_TO, Benutzer::class, 'benutzer_id', [], 'kontakt' ],
	];
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'kontakte');
	}
}
```

Jetzt haben wir die Referenzen eingerichtet, sodass wir sie sehr einfach verwenden können!

```php
$benutzer = new Benutzer($pdo_verbindung);

// finde den neuesten Benutzer.
$benutzer->notNull('id')->orderBy('id desc')->find();

// erhalte Kontakte, indem du die Beziehung verwendest:
foreach($benutzer->kontakte as $kontakt) {
	echo $kontakt->id;
}

// oder wir können den anderen Weg gehen.
$kontakt = new Kontakt();

// finde einen Kontakt
$kontakt->find();

// erhalte Benutzer, indem du die Beziehung verwendest:
echo $kontakt->user->name; // dies ist der Benutzername
```

Ziemlich cool, oder?

### Festlegen benutzerdefinierter Daten
Manchmal müssen Sie etwas Einzigartiges an Ihrem Active Record anhängen, wie zum Beispiel eine benutzerdefinierte Berechnung, die möglicherweise einfacher anzuhängen ist, als sie später an ein Template zu übergeben.

#### `setCustomData(string $field, mixed $value)`
Sie fügen die benutzerdefinierten Daten mit der Methode `setCustomData()` an.
```php
$benutzer->setCustomData('Seitenaufrufe', $seitenaufrufe);
```

Und dann verweisen Sie einfach darauf wie auf eine normale Objekteigenschaft.

```php
echo $benutzer->Seitenaufrufe;
```

### Ereignisse

Ein weiteres wirklich großartiges Feature dieser Bibliothek sind Ereignisse. Ereignisse werden zu bestimmten Zeiten basierend auf bestimmten von Ihnen aufgerufenen Methoden ausgelöst. Sie sind sehr, sehr hilfreich für die Einrichtung von Daten für Sie automatisch.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Das ist wirklich hilfreich, wenn Sie beispielsweise eine Standardverbindung einrichten müssen.

```php
// index.php oder bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	geschützte Funktion onConstruct(self $self, array &$config) { // vergessen Sie nicht die & Referenz
		// Sie könnten dies tun, um automatisch die Verbindung einzustellen
		$config['Verbindung'] = Flight::db();
		// oder dies
		$self->transformAndPersistConnection(Flight::db());
		
		// Sie können auch den Tabellennamen so festlegen.
		$config['tabelle'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Dies ist wahrscheinlich# FlightPHP Активная запись 

Активная запись - это сопоставление сущности базы данных с объектом PHP. Проще говоря, если у вас есть таблица пользователей в базе данных, вы можете "перевести" строку в этой таблице в класс `User` и объект `$user` в вашем коде. См. [базовый пример](#базовый-пример).

## Базовый Пример

Допустим, у вас есть следующая таблица:

```sql
CREATE TABLE пользователи (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Теперь вы можете настроить новый класс для представления этой таблицы:

```php
/**
 * Класс Active Record обычно в единственном числе
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
		// вы можете установить это так
		parent::__construct($database_connection, 'users');
		// или так
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Теперь посмотрите, что происходит!

```php
// для sqlite
$database_connection = new PDO('sqlite:test.db'); // это всего лишь пример, вы, вероятно, будете использовать реальное подключение к базе данных

// для mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'имя_пользователя', 'пароль');

// или mysqli
$database_connection = new mysqli('localhost', 'имя_пользователя', 'пароль', 'test_db');
// или mysqli с созданием не на основе объекта
$database_connection = mysqli_connect('localhost', 'имя_пользователя', 'пароль', 'test_db');

$user = new User($database_connection);
$user->name = 'Бобби Тэйблз';
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

И вот вы добавили нового пользователя! Теперь, когда в базе данных есть строка пользователя, как ее получить?

```php
$user->find(1); // найти id = 1 в базе данных и вернуть его.
echo $user->name; // 'Бобби Тэйблз'
```

Что, если вы хотите найти всех пользователей?

```php
$users = $user->findAll();
```

Как насчет определенного условия?

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

Это может использоваться как самостоятельная библиотека или с фреймворком Flight PHP. Полностью на ваше усмотрение.

### Стандалоне
Просто убедитесь, что передаете соединение PDO в конструктор.

```php
$pdo_connection = new PDO('sqlite:test.db'); // это всего лишь пример, вы, вероятно, будете использовать реальное подключение к базе данных

$User = new User($pdo_connection);
```

### Фреймворк Flight PHP
Если вы используете фреймворк Flight PHP, вы можете зарегистрировать класс ActiveRecord как сервис (но, честно говоря, вам и этого делать не обязательно).

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// затем вы можете использовать его так в контроллере, функции и т. д.

Flight::user()->find(1);
```

## Справочное API
### Функции CRUD

#### `find($id = null) : boolean|ActiveRecord`

Находит одну запись и присваивает ее текущему объекту. Если вы передаете `$id` какого-либо вида, он выполнит поиск по первичному ключу с этим значением. Если ничего не передается, он просто найдет первую запись в таблице.

Кроме того, вы можете передать ему другие вспомогательные методы для запроса вашей таблицы.

```php
// найти запись с некоторыми условиями заранее
$user->notNull('password')->orderBy('id DESC')->find();

// найти запись по определенному идентификатору
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Находит все записи в указанной вами таблице.

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

Также вы можете удалить несколько записей после выполнения запроса.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array $dirty = []): ActiveRecord`

Под "dirty" подразумеваются данные, которые были изменены в записи.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// на данном этапе ничего не считается "dirty".

$user->email = 'test@example.com'; // теперь email считается "dirty", поскольку он изменился.
$user->update();
// теперь нет данных, считаемых "dirty", потому что они были обновлены и сохранены в базе данных

$user->password = password_hash()'новыйпароль'); // теперь это "dirty"
$user->dirty(); // если ничего не передать, все "dirty" записи будут удалены.
$user->update(); // ничего не обновится, так как ничего не было зафиксировано как "dirty".

$user->dirty([ 'name' => 'что-то', 'password' => password_hash('другой пароль') ]);
$user->update(); // оба поля name и password обновлены.
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Сбрасывает текущую запись к ее исходному состоянию. Это действительно хорошо использовать в циклических операциях. Если передать `true`, то также будет сброшены данные запроса, использованные для нахождения текущего объекта (поведение по умолчанию).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // начать с чистого листа
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

### Методы запроса SQL
#### `select(string $field1 [, string $field2 ... ])`

Вы можете выбрать только некоторые из столбцов в таблице, если хотите (это более производительно на очень широких таблицах с множеством столбцов)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Технически, вы можете выбрать другую таблицу тоже! Как почему бы и нет?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Вы даже можете присоединиться к другой таблице в базе данных.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Вы можете установить некоторые пользовательские условия where (в этом операторе where вы не можете устанавливать параметры)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Примечание о безопасности** - Вас может подстегнуть что-то вроде `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. ПОЖАЛУЙСТА, НЕ ДЕЛАЙТЕ ЭТО!!! Это подвержено так называемым SQL-инъекциям. В интернете есть много статей, пожалуйста, используйте поиск по запросу "sql injection attacks php" и вы найдете множество статей по этой теме. Правильным способом обработки этого с использованием этой библиотеки вместо этого метода where() будет что-то вроде `$user->eq('id', $id)->eq('name', $name)->find();`

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Группируйте свои результаты по определенному условию.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Сортирует возвращенный запрос определенным образом.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Ограничивает количество возвращаемых записей. Если передается второе int, это будет смещение, ограничивает как в SQL.

```php
$user->orderBy('name DESC')->limit(0, 10)->findAll();
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
$user->like('name', 'де')->find();
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
Вы можете установить несколько типов отношений, используя эту библиотеку. Вы можете установить отношения один-ко-многим и один-к-одному между таблицами. Для этого требуется некоторая дополнительная настройка в классе заранее.

Установка массива `$relations` несложна, но правильный синтаксис может вызвать путаницу.

```php
защищенный массив $relations = [
	// вы можете назвать ключ как угодно. Имя Active Record, вероятно, подходит. Например: user, contact, client
	'whatever_active_record' => [
		// обязательно
		self::HAS_ONE, // это тип отношения

		// обязательно
		'Some_Class', // это "другой" класс Active Record, на который будет ссылаться этот

		// обязательно
		'local_key', // это локальный ключ, который ссылается на соединение.
		// просто для информации, это также соединяется только с первичным ключом "другой" модели

		// необязательно
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // пользовательские методы, которые вы хотите выполнить. [] если вы не хотите ничего.

		// необязательно
		'back_reference_name' // это, если вы хотите сделать обратную ссы́лку на это отношение обратно к себе, например: $user->contact->user;
	];
]
```

```php
class User extends ActiveRecord{
	защищенный массив $relations = [
		'контакты' => [ self::HAS_MANY, Contact::class, 'user_id' ],
		'контакт' => [ self::HAS_ONE, Contact::class, 'user_id' ],
	];

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'пользователи');
	}
}

class Contact extends ActiveRecord{
	защищенный массив $relations = [
		'пользователь' => [ self::BELONGS_TO, User::class, 'user_id' ],
		'пользователь_с_обратной_ссылкой' => [ self::BELONGS_TO, User::class, 'user_id', [], 'contact' ],
	];
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'контакты');
	}
}
```

Теперь у нас настроены ссылки, чтобы мы могли использовать их очень легко!

```php
$user = new User($pdo_connection);

// найти самого последнего пользователя
$user->notNull('id')->orderBy('id desc')->find();

// получить контакты, используя отношение:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// или мы можем пойти другим путем.
$contact = new Contact();

// найти один контакт
$contact->find();

// получение пользователя с использованием отношения:
echo $contact->user->name; // это имя пользователя
```

Довольно прикольно, да?

### Установка пользовательских данных
Иногда вам может потребоваться добавить что-то уникальное к вашему Active Record, напрримеру пользовательский расчет, который может быть проще присоединить к объекту и далее передать, скажем, в шаблон.

#### `setCustomData(string $field, mixed $value)`
Вы прикрепляете пользовательские данные с помощью метода `setCustomData()`.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

И затем просто обращаетесь к нему как к обычному свойству объекта.

```php
echo $user->page_view_count;
```

### События

Еще одна потрясающая особенность этой библиотеки это события. События срабатывают в определенные моменты на основе определенных вами вызываемых методов. Они очень полезны для настройки данных для вас автоматически.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Это действительно полезно, если вам нужно установить стандартное соединение или что-то в этом роде.

```php
// index.php или bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

protected function onConstruct(self $self, array &$config) { // не забудьте ссылку на &
// вы можете сделать это, чтобы автоматически устанавливать соединение
$config['connection'] = Flight::db();
// или это
$self->transformAndPersistConnection(Flight::db());

// Вы также можете установить имя таблицы вот так
$config['table'] = 'users';
} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Это, вероятно, полезно только в том случае, если вам нужно каждый раз выполнить манипуляцию запросом.

```php
class User extends flight\ActiveRecord {

public function __construct($database_connection)
{
parent::__construct($database_connection, 'users');
}

protected function beforeFind(self $self) {
// всегда запускать id >= 0 если это ваше желание
$self->gte('id', 0); 
} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Этот метод, вероятно, более полезен, если вам всегда нужно выполнить некоторую логику при каждом получении этой записи. Необходимо расшифровать что-либо? Необходимо выполнить пользовательский запрос на подсчет каждый раз (неоптимально, но все равно)?

```php
class User extends flight\ActiveRecord {

public function __construct($database_connection)
{
parent::__construct($database_connection, 'users');
}

protected function afterFind(self $self) {
// расшифровка чего-либо
$self->secret = yourDecryptFunction($self->secret, $some_key);

// может быть хранить что-то пользовательское, как запрос???
$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Это, вероятно, полезно только в том случае, если вам нужно каждый раз выполнить манипуляцию запросом.

```php
class User extends flight\ActiveRecord {

public function __construct($database_connection)
{
parent::__construct($database_connection, 'users');
}

protected function beforeFindAll(self $self) {
// всегда запускать id >= 0 если это ваше желание
$self->gte('id', 0); 
} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Подобно `afterFind()`, но вы можете использовать его для всех записей вместо только для одной!

```php
class User extends flight\ActiveRecord {

public function __construct($database_connection)
{
parent::__construct($database_connection, 'users');
}

protected function afterFindAll(array $results) {

foreach($results as $self) {
// делайте что-нибудь крутое подобное afterFind()
}
} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Действительно полезно, если вам нужно каждый раз устанавливать некоторые значения по умолчанию.

```php
class User extends flight\ActiveRecord {

public function __construct($database_connection)
{
parent::__construct($database_connection, 'users');
}

protected function beforeInsert(self $self) {
// устанавливайте некоторые стандартные значения
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

Возможно, у вас есть применение, где данные нужно изменить после того, как они были вставлены?

```php
class User extends flight\ActiveRecord {

public function __construct($database_connection)
{
parent::__construct($database_connection, 'users');
}

protected function afterInsert(self $self) {
// вы делаете, что хотите
Flight::cache()->set('most_recent_insert_id', $self->id);
// или что угодно еще....
} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Действительно полезно, если вам нужно каждый раз устанавливать некоторые значения по умолчанию при обновлении.

```php
class User extends flight\ActiveRecord {

public function __construct($database_connection)
{
parent::__construct($database_connection, 'users');
}

protected function beforeInsert(self $self) {
// устанавливайте некоторые стандартные значения
if(!$self->updated_date) {
$self->updated_date = gmdate('Y-m-d');
}
} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Возможно, у вас есть применение, где данные нужно изменить после того, как они были обновлены?

```php
class User extends flight\ActiveRecord {

public function __construct($database_connection)
{
parent::__construct($database_connection, 'users');
}

protected function afterInsert(self $self) {
// вы делаете, что хотите
Flight::cache()->set('most_recently_updated_user_id', $self->id);
// или что угодно....
} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Это полезно, если вы хотите, чтобы события происходили как при вставке, так и при обновлении. Я сэкономлю вам длинное объяснение, но я уверен, что вы можете догадаться, что это такое.

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

Не знаю, что вы хотели бы сделать здесь, но здесь нет суждений! Дерзайте!

```php
class User extends flight\ActiveRecord {
	
public function __construct($database_connection)
{
parent::__construct($database_connection, 'users');
}

protected function beforeDelete(self $self) {
echo 'Он был храбрым солдатом... :cry-face:';
} 
}
```

## Принятие вкладов

Пожалуйста сделайте. 

### Настройка

При внесении вклада убедитесь, что запускаете `composer test-coverage`, чтобы поддерживать 100% тестовое покрытие (это не истинное тестовое покрытие, а скорее интеграционное тестирование).

Также убедитесь, что запускаете `composer beautify` и `composer phpcs`, чтобы исправить любые ошибки стиля кода.

## Лицензия

MIT