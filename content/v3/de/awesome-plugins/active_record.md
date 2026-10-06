# Flight Active Record

Ein aktiver Datensatz (Active Record) bildet eine Datenbankentität auf ein PHP-Objekt ab. Einfach gesagt: Wenn du eine Tabelle `users` in deiner Datenbank hast, kannst du eine Zeile in dieser Tabelle in eine `User`-Klasse und ein `$user`-Objekt in deinem Code übersetzen. Siehe [Grundlegendes Beispiel](#basic-example).

Klicke [hier](https://github.com/flightphp/active-record) für das Repository auf GitHub.

## Grundlegendes Beispiel

Angenommen, du hast die folgende Tabelle:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Jetzt kannst du eine neue Klasse einrichten, um diese Tabelle darzustellen:

```php
/**
 * Eine ActiveRecord-Klasse ist normalerweise im Singular.
 * 
 * Es wird dringend empfohlen, die Eigenschaften der Tabelle hier als Kommentare hinzuzufügen.
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// du kannst es so setzen
		parent::__construct($database_connection, 'users');
		// oder so
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Jetzt sieh zu, wie die Magie passiert!

```php
// für sqlite
$database_connection = new PDO('sqlite:test.db'); // das ist nur ein Beispiel, du würdest wahrscheinlich eine echte Datenbankverbindung verwenden

// für mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// oder mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// oder mysqli mit nicht objekbasierter Erstellung
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('some cool password');
$user->insert();
// oder $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('some cool password again!!!');
$user->insert();
// du kannst hier nicht $user->save() verwenden, sonst wird es als Update angesehen!

echo $user->id; // 2
```

Und so einfach war es, einen neuen Benutzer hinzuzufügen! Jetzt, wo es eine Benutzerzeile in der Datenbank gibt, wie holst du sie heraus?

```php
$user->find(1); // findet id = 1 in der Datenbank und gibt sie zurück.
echo $user->name; // 'Bobby Tables'
```

Und was, wenn du alle Benutzer finden möchtest?

```php
$users = $user->findAll();
```

Und mit einer bestimmten Bedingung?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Siehst du, wie viel Spaß das macht? Lass uns es installieren und loslegen!

## Installation

Einfach mit Composer installieren:

```php
composer require flightphp/active-record 
```

## Verwendung

Dies kann als eigenständige Bibliothek oder mit dem Flight PHP Framework verwendet werden. Ganz dir überlassen.

### Eigenständig
Stelle einfach sicher, dass du eine PDO-Verbindung an den Konstruktor übergibst.

```php
$pdo_connection = new PDO('sqlite:test.db'); // das ist nur ein Beispiel, du würdest wahrscheinlich eine echte Datenbankverbindung verwenden

$User = new User($pdo_connection);
```

> Möchtest du nicht jedes Mal deine Datenbankverbindung im Konstruktor setzen? Siehe [Datenbankverbindungsverwaltung](#database-connection-management) für andere Ideen!

### Als Methode in Flight registrieren
Wenn du das Flight PHP Framework verwendest, kannst du die ActiveRecord-Klasse als Dienst registrieren, musst es aber ehrlich gesagt nicht.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// dann kannst du es so in einem Controller, einer Funktion usw. verwenden.

Flight::user()->find(1);
```

## `runway`-Methoden

[runway](/awesome-plugins/runway) ist ein CLI-Werkzeug für Flight, das einen eigenen Befehl für diese Bibliothek hat.

```bash
# Verwendung
php runway make:record database_table_name [class_name]

# Beispiel
php runway make:record users
```

Dies erstellt eine neue Klasse im Verzeichnis `app/records/` als `UserRecord.php` mit folgendem Inhalt:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * ActiveRecord-Klasse für die users-Tabelle.
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
     * @var array $relations Setzt die Beziehungen für das Modell
     *   https://docs.flightphp.com/en/v3/awesome-plugins/active-record#relationships
     */
    protected array $relations = [
		// 'relation_name' => [ self::HAS_MANY, 'RelatedClass', 'foreign_key' ],
	];

    /**
     * Konstruktor
     * @param mixed $databaseConnection Die Verbindung zur Datenbank
     */
    public function __construct($databaseConnection)
    {
        parent::__construct($databaseConnection, 'users');
    }
}
```

## CRUD-Funktionen

#### `find($id = null) : boolean|ActiveRecord`

Findet einen Datensatz und weist ihn dem aktuellen Objekt zu. Wenn du eine `$id` übergibst, wird eine Suche über den Primärschlüssel mit diesem Wert durchgeführt. Wenn nichts übergeben wird, wird einfach der erste Datensatz in der Tabelle gefunden.

Zusätzlich kannst du andere Hilfsmethoden übergeben, um deine Tabelle abzufragen.

```php
// einen Datensatz mit bestimmten Bedingungen im Vorfeld finden
$user->notNull('password')->orderBy('id DESC')->find();

// einen Datensatz über eine bestimmte ID finden
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Findet alle Datensätze in der angegebenen Tabelle.

```php
$user->findAll();
```

#### `first(): ActiveRecord` (v0.8.0)

Findet den ersten Datensatz, der deinen Bedingungen entspricht. Wenn du keine Sortierung gesetzt hast, sortiert er nach dem Primärschlüssel aufsteigend. Wenn nichts übereinstimmt, bekommst du den Datensatz unbearbeitet zurück. Prüfe also `isHydrated()`, wenn du dir nicht sicher bist, ob etwas zurückgekommen ist.

```php
$user->eq('status', 'active')->first();
```

#### `last(): ActiveRecord` (v0.8.0)

Gleiches wie `first()`, sortiert aber nach dem Primärschlüssel absteigend. Praktisch für Abfragen wie „gib mir den neuesten".

```php
$user->eq('status', 'active')->last();
```

#### `count(): int` (v0.8.0)

Zählt die Zeilen, die deinen aktuellen Bedingungen entsprechen. Wenn du ein `groupBy()` in der Abfrage hast, ignoriert `count()` dies absichtlich. Ein einzelner skalarer Zählwert kann nicht eine Zeile pro Gruppe darstellen.

```php
$user->count();
$user->eq('status', 'active')->count();
```

#### `exists(): bool` (v0.8.0)

Gibt `true` zurück, wenn irgendein Datensatz deinen Bedingungen entspricht. Unter der Haube führt es ein günstiges `SELECT 1 ... LIMIT 1` aus.

```php
$user->eq('name', 'Bobby')->exists(); // true
```

#### `pluck(string $column): array` (v0.8.0)

Gibt ein flaches Array von Werten aus einer Spalte zurück, anstatt eine Reihe von Objekten zu hydrieren. Kombiniere es mit `distinct()`, um eindeutige Werte zu erhalten.

```php
$user->pluck('name'); // [ 'Bobby', 'Joseph' ]
$user->distinct()->pluck('status'); // [ 'active', 'inactive' ]
```

#### `ids(): array` (v0.8.0)

Eine Abkürzung für `pluck()` auf dem Primärschlüssel.

```php
$user->gt('id', 0)->ids(); // [ 1, 2, 3 ]
```

#### `isHydrated(): boolean` (v0.4.0)

Gibt `true` zurück, wenn der aktuelle Datensatz hydriert (aus der Datenbank geholt) wurde.

```php
$user->find(1);
// wenn ein Datensatz mit Daten gefunden wurde...
$user->isHydrated(); // true
```

#### `insert(): boolean|ActiveRecord`

Fügt den aktuellen Datensatz in die Datenbank ein.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

##### Textbasierte Primärschlüssel

Wenn du einen textbasierten Primärschlüssel hast (z. B. eine UUID), kannst du den Primärschlüsselwert vor dem Einfügen auf eine von zwei Arten setzen.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // oder $user->save();
```

oder du kannst den Primärschlüssel automatisch durch Events generieren lassen.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// du kannst den Primärschlüssel auch so setzen, anstatt über das Array oben.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // oder wie auch immer du deine eindeutigen IDs generieren musst
	}
}
```

Wenn du den Primärschlüssel vor dem Einfügen nicht setzt, wird er auf die `rowid` gesetzt und die Datenbank generiert ihn für dich, aber er wird nicht gespeichert, da dieses Feld möglicherweise nicht in deiner Tabelle existiert. Deshalb wird empfohlen, das Event zu verwenden, um dies automatisch für dich zu erledigen.

#### `update(): boolean|ActiveRecord`

Aktualisiert den aktuellen Datensatz in der Datenbank.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `updateAttribute(string $name, mixed $value): ActiveRecord` (v0.8.0)

Aktualisiert eine einzelne Spalte eines geladenen Datensatzes und speichert ihn. Es ist eine Abkürzung für `$user->dirty([ 'name' => $value ])->update()`. Du brauchst dafür einen geladenen Datensatz.

```php
$user->find(1);
$user->updateAttribute('name', 'New Name');
```

#### `save(): boolean|ActiveRecord`

Fügt den aktuellen Datensatz in die Datenbank ein oder aktualisiert ihn. Wenn der Datensatz eine ID hat, wird aktualisiert, andernfalls eingefügt.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Hinweis:** Wenn du Beziehungen in der Klasse definiert hast, werden diese Beziehungen auch rekursiv gespeichert, wenn sie definiert, instanziiert wurden und veränderte Daten (dirty data) zu aktualisieren haben. (v0.4.0 und höher)

#### `delete(): boolean`

Löscht den aktuellen Datensatz aus der Datenbank.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Du kannst auch mehrere Datensätze löschen, indem du vorher eine Suche ausführst.

```php
$user->like('name', 'Bob%')->delete();
```

#### `updateAll(array $attributes, bool $allowEmptyConditions = false): int` (v0.8.0)

Aktualisiert alle Datensätze, die deinen Bedingungen entsprechen, in einer einzigen Anweisung. Es werden keine Datensätze hydriert und keine Events ausgelöst, genau deshalb ist es schnell. Gibt die Anzahl der betroffenen Zeilen zurück.

Es weigert sich, ohne WHERE-Bedingungen zu laufen, es sei denn, du übergibst `true` für das zweite Argument. Dein zukünftiges Ich dankt dir.

```php
$user->eq('status', 'inactive')->updateAll([ 'status' => 'active' ]);

// ja, du willst wirklich jede Zeile in der Tabelle aktualisieren
$user->updateAll([ 'status' => 'active' ], true);
```

#### `deleteAll(bool $allowEmptyConditions = false): int` (v0.8.0)

Löscht alle Datensätze, die deinen Bedingungen entsprechen, in einer einzigen Anweisung. Gleiche Sache wie bei `updateAll()`: keine Hydrierung, keine Events, und es erfordert WHERE-Bedingungen, es sei denn, du übergibst `true`. Gibt die Anzahl der gelöschten Zeilen zurück. Mit Vorsicht verwenden!

```php
$user->eq('status', 'deleted')->deleteAll();
```

#### `dirty(array  $dirty = []): ActiveRecord`

„Dirty data" bezieht sich auf Daten, die in einem Datensatz geändert wurden.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// nichts ist zu diesem Zeitpunkt "dirty".

$user->email = 'test@example.com'; // jetzt gilt "email" als "dirty", da es geändert wurde.
$user->update();
// jetzt gibt es keine "dirty" Daten, da sie aktualisiert und in der Datenbank gespeichert wurden

$user->password = password_hash()'newpassword'); // jetzt ist dies "dirty"
$user->dirty(); // wenn nichts übergeben wird, werden alle "dirty"-Einträge gelöscht.
$user->update(); // nichts wird aktualisiert, da nichts als "dirty" erfasst wurde.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // sowohl name als auch password werden aktualisiert.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Dies ist ein Alias für die `dirty()`-Methode. Es ist etwas klarer, was du tust.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // sowohl name als auch password werden aktualisiert.
```

#### `isDirty(): boolean` (v0.4.0)

Gibt `true` zurück, wenn der aktuelle Datensatz geändert wurde.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Setzt den aktuellen Datensatz auf seinen ursprünglichen Zustand zurück. Das ist sehr gut für Schleifenverhalten geeignet. Wenn du `true` übergibst, wird auch die Abfragedaten zurückgesetzt, die verwendet wurden, um das aktuelle Objekt zu finden (Standardverhalten).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // mit einem sauberen Blatt starten
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Nachdem du eine `find()`-, `findAll()`-, `insert()`-, `update()`- oder `save()`-Methode ausgeführt hast, kannst du das erstellte SQL abrufen und zu Debugging-Zwecken verwenden.

## Transaktionen

Musst du ein paar Schreibvorgänge ausführen, die alle gemeinsam erfolgreich sein müssen? Wickle sie in `transaction()` (v0.8.0). Übergib ein Callable, und der Datensatz kommt als Argument hinein. Wenn das Callable eine Exception wirft, wird alles zurückgerollt und die Exception wird für dich erneut geworfen. Andernfalls wird committet und das zurückgegeben, was dein Callable zurückgegeben hat.

```php
$user->transaction(function ($user) {
	$user->name = 'Bobby Tables';
	$user->password = password_hash('correct horse battery staple');
	$user->insert();

	$user->email = 'bobby@example.com';
	$user->update();
	// Der Commit passiert hier, wenn nichts geworfen wurde
});
```

Verschachtelte Transaktionen werden nicht unterstützt (keine Savepoints), also halte sie flach.

## SQL-Abfragemethoden
#### `select(string $field1 [, string $field2 ... ])`

Du kannst nur ein paar der Spalten in einer Tabelle auswählen, wenn du möchtest (das ist bei sehr breiten Tabellen mit vielen Spalten performanter).

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Du kannst technisch gesehen auch eine andere Tabelle wählen! Warum auch nicht?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Du kannst sogar eine andere Tabelle in der Datenbank joinen.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Du kannst eigene WHERE-Argumente setzen (du kannst keine Parameter in dieser WHERE-Anweisung setzen).

```php
$user->where('id=1 AND name="demo"')->find();
```

**Sicherheitshinweis** – Du könntest versucht sein, so etwas zu tun: `$user->where("id = '{$id}' AND name = '{$name}'")->find();`. BITTE TU DAS NICHT!!! Dies ist anfällig für sogenannte SQL-Injection-Angriffe. Es gibt viele Artikel online. Bitte suche bei Google nach „sql injection attacks php" und du wirst viele Artikel zu diesem Thema finden. Der richtige Weg, dies mit dieser Bibliothek zu handhaben, ist, anstelle dieser `where()`-Methode so etwas zu tun: `$user->eq('id', $id)->eq('name', $name)->find();`. Wenn du es unbedingt tun musst, hat die `PDO`-Bibliothek `$pdo->quote($var)`, um es für dich zu escapen. Nur nach Verwendung von `quote()` kannst du es in einer `where()`-Anweisung verwenden.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Gruppiere deine Ergebnisse nach einer bestimmten Bedingung.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Sortiere die zurückgegebene Abfrage auf eine bestimmte Weise.

```php
$user->orderBy('name DESC')->find();
```

#### `orderByColumn(string $column, string $direction = 'ASC')` (v0.7.2)

`order()` und `orderBy()` akzeptieren rohe SQL-Fragmente, was in Ordnung ist, wenn du `'name DESC'` fest codierst. Wenn der Spaltenname von Benutzereingaben stammt (z. B. eine sortierbare Tabellenkopfzeile), verwende stattdessen `orderByColumn()`. Es sind nur einfache Spaltennamen und `table.column`-Pfade erlaubt, und die Richtung muss `ASC` oder `DESC` sein, also gibt es nichts, was man injizieren könnte.

```php
// $sortColumn kommt aus der Anfrage
$user->orderByColumn($sortColumn, 'DESC')->findAll();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Begrenze die Anzahl der zurückgegebenen Datensätze. Wenn eine zweite Ganzzahl angegeben wird, ist es Offset, Limit, genau wie in SQL.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

#### `distinct()` (v0.8.0)

Fügt `DISTINCT` zu deiner nächsten Abfrage hinzu. Es funktioniert bei normalen Selects und bei `pluck()`. `count()` ignoriert es, da das Setzen von `DISTINCT` auf eine einzelne Aggregatzeile nichts bewirkt.

```php
$user->distinct()->pluck('status'); // [ 'active', 'inactive' ]
```

## WHERE-Bedingungen
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

Wo `field = $value`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

Wo `field <> $value`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

Wo `field IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

Wo `field IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

Wo `field > $value`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

Wo `field < $value`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

Wo `field >= $value`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

Wo `field <= $value`

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

Wo `field LIKE $value` oder `field NOT LIKE $value`

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

Wo `field IN($value)` oder `field NOT IN($value)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Wo `field BETWEEN $value AND $value1`

```php
$user->between('id', [1, 2])->find();
```

### ODER-Bedingungen

Es ist möglich, deine Bedingungen in eine ODER-Anweisung zu packen. Dies geschieht entweder mit den Methoden `startWrap()` und `endWrap()` oder indem du den dritten Parameter der Bedingung nach dem Feld und dem Wert ausfüllst.

```php
// Methode 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// Dies wird ausgewertet zu `id = 1 AND (name = 'demo' OR name = 'test')`

// Methode 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// Dies wird ausgewertet zu `id = 1 OR name = 'demo'`
```

## Scopes

Scopes (v0.8.0) sind wiederverwendbare Abfrageketten, die als einfache Instanzmethoden in deiner Klasse definiert werden und `$this` zurückgeben. Sobald du einen geschrieben hast, verhält er sich wie jede andere Abfragemethode.

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

// und jetzt lesen sich deine Abfragen wie Sätze
(new User($pdo_connection))->active()->recent(30)->findAll();
```

Du kannst einen Scope auch mit `scope()` namentlich aufrufen, was praktisch ist, wenn der Scopename von irgendwo anders in deinem Code kommt. Es wird eine `BadMethodCallException` geworfen, wenn die Methode nicht existiert.

```php
$user->scope('active')->findAll();
$user->scope('recent', 30)->findAll();
```

## Beziehungen
Du kannst mit dieser Bibliothek verschiedene Arten von Beziehungen festlegen. Du kannst 1:n- und 1:1-Beziehungen zwischen Tabellen festlegen. Das erfordert etwas zusätzliche Einrichtung in der Klasse im Vorfeld.

Das `$relations`-Array zu setzen ist nicht schwer, aber die richtige Syntax zu erraten, kann verwirrend sein.

```php
protected array $relations = [
	// du kannst den Schlüssel beliebig benennen. Der Name des ActiveRecords ist wahrscheinlich gut. Z.B.: user, contact, client
	'user' => [
		// erforderlich
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // dies ist die Art der Beziehung

		// erforderlich
		'Some_Class', // dies ist die "andere" ActiveRecord-Klasse, auf die verwiesen wird

		// erforderlich
		// abhängig von der Art der Beziehung
		// self::HAS_ONE = der Fremdschlüssel, der auf den Join verweist
		// self::HAS_MANY = der Fremdschlüssel, der auf den Join verweist
		// self::BELONGS_TO = der lokale Schlüssel, der auf den Join verweist
		'local_or_foreign_key',
		// nur zur Info: dies verbindet auch nur mit dem Primärschlüssel des "anderen" Modells

		// optional
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // zusätzliche Bedingungen, die du beim Joinen der Beziehung möchtest
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// optional
		'back_reference_name' // das ist, wenn du diese Beziehung zurück auf sich selbst referenzieren möchtest, z.B.: $user->contact->user;
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

Jetzt haben wir die Referenzen eingerichtet, sodass wir sie sehr einfach verwenden können!

```php
$user = new User($pdo_connection);

// finde den neuesten Benutzer.
$user->notNull('id')->orderBy('id desc')->find();

// Kontakte über die Beziehung abrufen:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// oder wir können den umgekehrten Weg gehen.
$contact = new Contact();

// einen Kontakt finden
$contact->find();

// Benutzer über die Beziehung abrufen:
echo $contact->user->name; // das ist der Benutzername
```

Ziemlich cool, oder?

### Eager Loading

#### Übersicht
Eager Loading löst das N+1-Abfrageproblem, indem Beziehungen im Voraus geladen werden. Anstatt für jede Beziehung jedes Datensatzes eine separate Abfrage auszuführen, lädt Eager Loading alle zugehörigen Daten in nur einer zusätzlichen Abfrage pro Beziehung.

> **Hinweis:** Eager Loading ist nur für v0.7.0 und höher verfügbar.

#### Grundlegende Verwendung
Verwende die `with()`-Methode, um anzugeben, welche Beziehungen eager geladen werden sollen:
```php
// Benutzer mit ihren Kontakten in 2 Abfragen laden statt N+1
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    foreach ($u->contacts as $contact) {
        echo $contact->email; // Keine zusätzliche Abfrage!
    }
}
```

#### Mehrere Beziehungen
Lade mehrere Beziehungen auf einmal:
```php
$users = $user->with(['contacts', 'profile', 'settings'])->findAll();
```

#### Beziehungstypen

##### HAS_MANY
```php
// Alle Kontakte für jeden Benutzer eager laden
$users = $user->with('contacts')->findAll();
foreach ($users as $u) {
    // $u->contacts ist bereits als Array geladen
    foreach ($u->contacts as $contact) {
        echo $contact->email;
    }
}
```
##### HAS_ONE
```php
// Einen Kontakt für jeden Benutzer eager laden
$users = $user->with('contact')->findAll();
foreach ($users as $u) {
    // $u->contact ist bereits als Objekt geladen
    echo $u->contact->email;
}
```

##### BELONGS_TO
```php
// Übergeordnete Benutzer für alle Kontakte eager laden
$contacts = $contact->with('user')->findAll();
foreach ($contacts as $c) {
    // $c->user ist bereits geladen
    echo $c->user->name;
}
```
##### Mit find()
Eager Loading funktioniert sowohl mit `findAll()` als auch mit `find()`:

```php
$user = $user->with('contacts')->find(1);
// Benutzer und alle seine Kontakte in 2 Abfragen geladen
```
#### Leistungsvorteile
Ohne Eager Loading (N+1-Problem):
```php
$users = $user->findAll(); // 1 Abfrage
foreach ($users as $u) {
    $contacts = $u->contacts; // N Abfragen (eine pro Benutzer!)
}
// Gesamt: 1 + N Abfragen
```

Mit Eager Loading:

```php
$users = $user->with('contacts')->findAll(); // 2 Abfragen gesamt
foreach ($users as $u) {
    $contacts = $u->contacts; // 0 zusätzliche Abfragen!
}
// Gesamt: 2 Abfragen (1 für Benutzer + 1 für alle Kontakte)
```
Bei 10 Benutzern reduziert das die Abfragen von 11 auf 2 – eine Reduktion um 82 %!

#### Wichtige Hinweise
- Eager Loading ist vollständig optional – Lazy Loading funktioniert weiterhin wie zuvor
- Bereits geladene Beziehungen werden automatisch übersprungen
- Rückwärtsreferenzen funktionieren mit Eager Loading
- Beziehungs-Callbacks werden beim Eager Loading berücksichtigt

#### Einschränkungen
- Verschachteltes Eager Loading (z. B. `with(['contacts.addresses'])`) wird derzeit nicht unterstützt
- Eager-Load-Einschränkungen über Closures werden in dieser Version nicht unterstützt

## Benutzerdefinierte Daten setzen
Manchmal musst du etwas Einzigartiges an dein ActiveRecord anhängen, z. B. eine benutzerdefinierte Berechnung, die möglicherweise einfacher direkt an das Objekt angehängt werden kann, das dann z. B. an ein Template übergeben wird.

#### `setCustomData(string $field, mixed $value)`
Du hängst die benutzerdefinierten Daten mit der `setCustomData()`-Methode an.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

Und dann referenzierst du es einfach wie eine normale Objekteigenschaft.

```php
echo $user->page_view_count;
```

## Zeitstempel

Wenn deine Tabelle `created_at`- und `updated_at`-Spalten hat, kann die Bibliothek sie für dich ausfüllen (v0.8.0). Setze `protected bool $timestamps = true;` in deiner Klasse, und sie setzt `created_at` und `updated_at` beim Einfügen und `updated_at` beim Aktualisieren. Das Format ist `Y-m-d H:i:s`. Wenn du eine der Spalten selbst setzt, lässt die Bibliothek deinen Wert in Ruhe.

```php
class User extends flight\ActiveRecord {
	protected bool $timestamps = true;

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}
}
```

Deine Tabelle muss diese Spalten tatsächlich enthalten, sonst schlagen Einfügungen und Aktualisierungen fehl.

## Events

Ein weiteres super tolles Feature dieser Bibliothek sind Events. Events werden zu bestimmten Zeitpunkten basierend auf bestimmten aufgerufenen Methoden ausgelöst. Sie sind sehr, sehr hilfreich, um Daten automatisch für dich einzurichten.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Das ist wirklich hilfreich, wenn du eine Standardverbindung oder etwas Ähnliches setzen musst.

```php
// index.php oder bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // denke an die &-Referenz
		// du könntest dies tun, um die Verbindung automatisch zu setzen
		$config['connection'] = Flight::db();
		// oder dies
		$self->transformAndPersistConnection(Flight::db());
		
		// Du kannst den Tabellennamen auch so setzen.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Dies ist wahrscheinlich nur nützlich, wenn du jedes Mal eine Abfrage manipulieren musst.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// immer id >= 0 ausführen, wenn das dein Ding ist
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Dies ist wahrscheinlich nützlicher, wenn du immer einige Logik ausführen musst, jedes Mal wenn dieser Datensatz abgerufen wird. Musst du etwas entschlüsseln? Musst du jedes Mal eine benutzerdefinierte Zählabfrage ausführen (nicht performant, aber egal)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// etwas entschlüsseln
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// vielleicht etwas Benutzerdefiniertes speichern, wie eine Abfrage???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Dies ist wahrscheinlich nur nützlich, wenn du jedes Mal eine Abfrage manipulieren musst.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// immer id >= 0 ausführen, wenn das dein Ding ist
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Ähnlich wie `afterFind()`, aber du kannst es für alle Datensätze tun!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// etwas Cooles tun, wie in afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Wirklich hilfreich, wenn du jedes Mal Standardwerte setzen musst.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// ein paar solide Standardwerte setzen
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

Vielleicht hast du einen Anwendungsfall, um Daten zu ändern, nachdem sie eingefügt wurden?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// mach dein Ding
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// oder was auch immer....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Wirklich hilfreich, wenn du bei einem Update jedes Mal Standardwerte setzen musst.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// ein paar solide Standardwerte setzen
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Vielleicht hast du einen Anwendungsfall, um Daten zu ändern, nachdem sie aktualisiert wurden?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// mach dein Ding
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// oder was auch immer....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Das ist nützlich, wenn du möchtest, dass Events sowohl bei Einfügungen als auch bei Updates passieren. Ich erspare dir die lange Erklärung, aber ich bin sicher, du kannst dir denken, was es ist.

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

Ich bin mir nicht sicher, was du hier tun möchtest, aber hier gibt es keine Verurteilungen! Leg los!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'Er war ein tapferer Soldat... :cry-face:';
	} 
}
```

## Datenbankverbindungsverwaltung

Wenn du diese Bibliothek verwendest, kannst du die Datenbankverbindung auf verschiedene Arten setzen. Du kannst die Verbindung im Konstruktor setzen, über eine Konfigurationsvariable `$config['connection']` oder über `setDatabaseConnection()` (v0.4.1).

```php
$pdo_connection = new PDO('sqlite:test.db'); // zum Beispiel
$user = new User($pdo_connection);
// oder
$user = new User(null, [ 'connection' => $pdo_connection ]);
// oder
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Wenn du vermeiden möchtest, jedes Mal eine `$database_connection` setzen zu müssen, wenn du einen Active Record aufrufst, gibt es Wege, das zu umgehen!

```php
// index.php oder bootstrap.php
// Dies als registrierte Klasse in Flight setzen
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

// User.php
class User extends flight\ActiveRecord {
	
	public function __construct(array $config = [])
	{
		$database_connection = $config['connection'] ?? Flight::db();
		parent::__construct($database_connection, 'users', $config);
	}
}

// Und jetzt, keine Argumente erforderlich!
$user = new User();
```

> **Hinweis:** Wenn du Unit-Tests planst, kann dieser Ansatz einige Herausforderungen für Unit-Tests mit sich bringen, aber insgesamt ist es nicht zu schlimm, da du deine Verbindung mit `setDatabaseConnection()` oder `$config['connection']` injizieren kannst.

Wenn du die Datenbankverbindung auffrischen musst, zum Beispiel wenn du ein langlaufendes CLI-Skript ausführst und die Verbindung ab und zu auffrischen musst, kannst du die Verbindung mit `$your_record->setDatabaseConnection($pdo_connection)` neu setzen.

## Mitwirken

Bitte tu es. :D

### Einrichtung

Wenn du einen Beitrag leistest, stelle sicher, dass du `composer test-coverage` ausführst, um 100% Testabdeckung zu erhalten (das ist keine echte Unit-Test-Abdeckung, eher Integrationstests).

Stelle außerdem sicher, dass du `composer beautify` und `composer phpcs` ausführst, um etwaige Lint-Fehler zu beheben.

## Lizenz

MIT