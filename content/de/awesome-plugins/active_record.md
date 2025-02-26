# Flight Aktive Datensätze

Ein aktiver Datensatz ist die Zuordnung einer Datenbankentität zu einem PHP-Objekt. Einfach gesagt, wenn Sie eine Benutzertabelle in Ihrer Datenbank haben, können Sie eine Zeile in dieser Tabelle in eine `User`-Klasse und ein `$user`-Objekt in Ihrem Code übersetzen. Siehe [grundlegendes Beispiel](#basic-example).

Klicken Sie [hier](https://github.com/flightphp/active-record) für das Repository auf GitHub.

## Grundlegendes Beispiel

Angenommen, Sie haben die folgende Tabelle:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Jetzt können Sie eine neue Klasse einrichten, um diese Tabelle darzustellen:

```php
/**
 * Eine ActiveRecord-Klasse ist normalerweise singular
 * 
 * Es wird dringend empfohlen, die Eigenschaften der Tabelle hier als Kommentare hinzuzufügen
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		// Sie können es so festlegen
		parent::__construct($database_connection, 'users');
		// oder so
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Jetzt beobachten Sie das Wunder geschehen!

```php
// für sqlite
$database_connection = new PDO('sqlite:test.db'); // das ist nur ein Beispiel, Sie würden wahrscheinlich eine echte Datenbankverbindung verwenden

// für mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// oder mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// oder mysqli mit nicht objektbasierter Erstellung
$database_connection = mysqli_connect('localhost', 'username', 'password', 'test_db');

$user = new User($database_connection);
$user->name = 'Bobby Tables';
$user->password = password_hash('ein cooles Passwort');
$user->insert();
// oder $user->save();

echo $user->id; // 1

$user->name = 'Joseph Mamma';
$user->password = password_hash('noch ein cooles Passwort!!!');
$user->insert();
// $user->save() kann hier nicht verwendet werden, sonst denkt es, es ist ein Update!

echo $user->id; // 2
```

Und so einfach war es, einen neuen Benutzer hinzuzufügen! Jetzt, wo es eine Benutzerzeile in der Datenbank gibt, wie ziehen Sie sie heraus?

```php
$user->find(1); // finde id = 1 in der Datenbank und gebe sie zurück.
echo $user->name; // 'Bobby Tables'
```

Und was ist, wenn Sie alle Benutzer finden möchten?

```php
$users = $user->findAll();
```

Und was ist mit einer bestimmten Bedingung?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Sieh dir an, wie viel Spaß das macht! Lassen Sie uns es installieren und loslegen!

## Installation

Installieren Sie einfach mit Composer

```php
composer require flightphp/active-record 
```

## Verwendung

Dies kann als eigenständige Bibliothek oder mit dem Flight PHP Framework verwendet werden. Ganz Ihnen überlassen.

### Eigenständig
Sorgen Sie einfach dafür, dass Sie eine PDO-Verbindung an den Konstruktor übergeben.

```php
$pdo_connection = new PDO('sqlite:test.db'); // das ist nur ein Beispiel, Sie würden wahrscheinlich eine echte Datenbankverbindung verwenden

$User = new User($pdo_connection);
```

> Möchten Sie Ihre Datenbankverbindung nicht immer im Konstruktor festlegen? Siehe [Datenbankverbindungsverwaltung](#database-connection-management) für weitere Ideen!

### Registrieren Sie es als Methode in Flight
Wenn Sie das Flight PHP Framework verwenden, können Sie die ActiveRecord-Klasse als Dienst registrieren, aber das müssen Sie ehrlich gesagt nicht.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// dann können Sie es so in einem Controller, einer Funktion usw. verwenden.

Flight::user()->find(1);
```

## `runway` Methoden

[runway](/awesome-plugins/runway) ist ein CLI-Tool für Flight, das einen benutzerdefinierten Befehl für diese Bibliothek hat. 

```bash
# Verwendung
php runway make:record database_table_name [class_name]

# Beispiel
php runway make:record users
```

Dies wird eine neue Klasse im Verzeichnis `app/records/` als `UserRecord.php` mit folgendem Inhalt erstellen:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * ActiveRecord-Klasse für die Benutzertabelle.
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
     * @var array $relations Legen Sie die Beziehungen für das Modell fest
     *   https://docs.flightphp.com/awesome-plugins/active-record#relationships
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

Finden Sie einen Datensatz und weisen Sie ihn dem aktuellen Objekt zu. Wenn Sie einen `$id` von irgendeiner Art übergeben, wird eine Abfrage auf dem Primärschlüssel mit diesem Wert durchgeführt. Wenn nichts übergeben wird, wird einfach der erste Datensatz in der Tabelle gefunden.

Zusätzlich können Sie ihm andere Hilfsmethoden übergeben, um Ihre Tabelle abzufragen.

```php
// Finde einen Datensatz mit einigen Bedingungen im Voraus
$user->notNull('password')->orderBy('id DESC')->find();

// Finde einen Datensatz mit einer bestimmten id
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Findet alle Datensätze in der angegebenen Tabelle.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

Gibt `true` zurück, wenn der aktuelle Datensatz hydratisiert wurde (aus der Datenbank abgerufen).

```php
$user->find(1);
// wenn ein Datensatz mit Daten gefunden wird...
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

Wenn Sie einen textbasierten Primärschlüssel (wie eine UUID) haben, können Sie den Primärschlüsselwert auf eine der beiden Arten vor dem Einfügen festlegen.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // oder $user->save();
```

oder Sie können den Primärschlüssel automatisch für Sie durch Ereignisse generieren lassen.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// Sie können auch den Primärschlüssel auf diese Weise festlegen anstelle des obigen Arrays.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // oder wie auch immer Sie Ihre eindeutigen IDs generieren müssen
	}
}
```

Wenn Sie den Primärschlüssel vor dem Einfügen nicht festlegen, wird er auf `rowid` gesetzt und die Datenbank wird ihn für Sie generieren, aber er wird nicht bestehen bleiben, da dieses Feld möglicherweise nicht in Ihrer Tabelle vorhanden ist. Deshalb wird empfohlen, das Ereignis zu verwenden, um dies automatisch für Sie zu handhaben.

#### `update(): boolean|ActiveRecord`

Aktualisiert den aktuellen Datensatz in der Datenbank.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Fügt den aktuellen Datensatz in die Datenbank ein oder aktualisiert ihn. Wenn der Datensatz eine ID hat, wird er aktualisiert, andernfalls wird er eingefügt.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Hinweis:** Wenn Sie Beziehungen in der Klasse definiert haben, werden diese Beziehungen ebenfalls rekursiv gespeichert, wenn sie definiert, instanziiert und Daten zum Aktualisieren enthalten. (v0.4.0 und höher)

#### `delete(): boolean`

Löscht den aktuellen Datensatz aus der Datenbank.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Sie können auch mehrere Datensätze löschen, indem Sie zuvor eine Suche durchführen.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Dirty-Daten beziehen sich auf die Daten, die in einem Datensatz geändert wurden.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// nichts ist "dirty" zu diesem Zeitpunkt.

$user->email = 'test@example.com'; // jetzt wird die E-Mail als "dirty" betrachtet, da sie geändert wurde.
$user->update();
// jetzt gibt es keine Daten, die "dirty" sind, da sie aktualisiert und in der Datenbank gespeichert wurden

$user->password = password_hash('newpassword'); // jetzt ist dies dirty
$user->dirty(); // nichts zu übergeben, löscht alle schmutzigen Einträge.
$user->update(); // nichts wird aktualisiert, da nichts als dirty erfasst wurde.

$user->dirty([ 'name' => 'etwas', 'password' => password_hash('ein anderes Passwort') ]);
$user->update(); // sowohl Name als auch Passwort werden aktualisiert.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Dies ist ein Alias für die `dirty()`-Methode. Es ist ein wenig klarer, was Sie tun.

```php
$user->copyFrom([ 'name' => 'etwas', 'password' => password_hash('ein anderes Passwort') ]);
$user->update(); // sowohl Name als auch Passwort werden aktualisiert.
```

#### `isDirty(): boolean` (v0.4.0)

Gibt `true` zurück, wenn der aktuelle Datensatz geändert wurde.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Setzt den aktuellen Datensatz auf seinen Anfangszustand zurück. Dies ist sehr nützlich, um es in Schleifenäquivalenten zu verwenden.
Wenn Sie `true` übergeben, werden auch die Abfragedaten zurückgesetzt, die zum Finden des aktuellen Objekts verwendet wurden (Standardverhalten).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // mit einem sauberen Blatt beginnen
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Nachdem Sie eine `find()`, `findAll()`, `insert()`, `update()` oder `save()`-Methode ausgeführt haben, können Sie das SQL erhalten, das erstellt wurde, und es zu Debugging-Zwecken verwenden.

## SQL-Abfragemethoden
#### `select(string $field1 [, string $field2 ... ])`

Sie können nur einige der Spalten in einer Tabelle auswählen, wenn Sie möchten (es ist leistungsfähiger bei wirklich breiten Tabellen mit vielen Spalten)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Technisch können Sie auch eine andere Tabelle auswählen! Warum nicht?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Sie können sogar eine andere Tabelle in der Datenbank verknüpfen.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Sie können einige benutzerdefinierte WHERE-Argumente festlegen (Sie können in dieser WHERE-Anweisung keine Parameter festlegen)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Sicherheitsnotiz** - Sie könnten versucht sein, etwas wie `$user->where("id = '{$id}' AND name = '{$name}'")->find();` zu tun. Bitte tun Sie dies NICHT!!! Dies ist anfällig für das, was als SQL-Injection-Angriffe bekannt ist. Es gibt viele Artikel online, bitte googeln Sie "sql injection attacks php" und Sie finden viele Artikel zu diesem Thema. Der richtige Weg, dies mit dieser Bibliothek zu handhaben, ist anstelle dieser `where()`-Methode sollten Sie etwas mehr wie `$user->eq('id', $id)->eq('name', $name)->find();` machen. Wenn Sie dies unbedingt tun müssen, hat die `PDO`-Bibliothek `$pdo->quote($var)`, um es für Sie zu escapen. Nur nachdem Sie `quote()` verwendet haben, können Sie es in einer `where()`-Anweisung verwenden.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Gruppieren Sie Ihre Ergebnisse nach einer bestimmten Bedingung.

```php
$user->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Sortieren Sie die zurückgegebene Abfrage auf eine bestimmte Weise.

```php
$user->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Begrenzen Sie die Anzahl der zurückgegebenen Datensätze. Wenn eine zweite Ganzzahl angegeben wird, wird sie wie in SQL verschoben.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
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

Es ist möglich, Ihre Bedingungen in einer ODER-Anweisung einzurahmen. Dies geschieht entweder mit der `startWrap()`- und `endWrap()`-Methode oder indem Sie den 3. Parameter der Bedingung nach dem Feld und dem Wert ausfüllen.

```php
// Methode 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// Dies wird ausgewertet als `id = 1 AND (name = 'demo' OR name = 'test')`

// Methode 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// Dies wird ausgewertet als `id = 1 ODER name = 'demo'`
```

## Beziehungen
Sie können mit dieser Bibliothek mehrere Arten von Beziehungen festlegen. Sie können eine Eins-zu-Viele- und Eins-zu-Eins-Beziehung zwischen Tabellen herstellen. Dies erfordert einige zusätzliche Vorbereitungen in der Klasse im Voraus.

Das Festlegen des `$relations`-Arrays ist nicht schwer, aber die richtige Syntax zu erraten, kann verwirrend sein.

```php
protected array $relations = [
	// Sie können den Schlüssel beliebig benennen. Der Name des ActiveRecord ist wahrscheinlich gut. Z. B. benutzer, kontakt, klient
	'user' => [
		// erforderlich
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // dies ist der Typ der Beziehung

		// erforderlich
		'Some_Class', // dies ist die "andere" ActiveRecord-Klasse, auf die verwiesen wird

		// erforderlich
		// abhängig vom Beziehungstyp
		// self::HAS_ONE = der Fremdschlüssel, der auf den Join verweist
		// self::HAS_MANY = der Fremdschlüssel, der auf den Join verweist
		// self::BELONGS_TO = der lokale Schlüssel, der auf den Join verweist
		'local_or_foreign_key',
		// nur zur Information: Dies verbindet sich auch nur mit dem Primärschlüssel des "anderen" Modells

		// optional
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // zusätzliche Bedingungen, die Sie beim Verknüpfen der Beziehung wünschen
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// optional
		'back_reference_name' // Dies ist, wenn Sie diese Beziehung auf sich selbst zurückverweisen möchten, z. B. $user->contact->user;
	];
]
```

```php
class User extends ActiveRecord {
	protected array $relations = [
		'contacts' => [ self::HAS_MANY, Contact::class, 'user_id' ],
		'contact' => [ self::HAS_ONE, Contact::class, 'user_id' ],
	];

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}
}

class Contact extends ActiveRecord {
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

Jetzt haben wir die Referenzen eingerichtet, damit wir sie sehr einfach verwenden können!

```php
$user = new User($pdo_connection);

// Finde den aktuellsten Benutzer.
$user->notNull('id')->orderBy('id desc')->find();

// Kontakte nach Verwendung der Beziehung abrufen:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// oder wir können den anderen Weg gehen.
$contact = new Contact();

// Finde einen Kontakt
$contact->find();

// Benutzer über die Beziehung abrufen:
echo $contact->user->name; // das ist der Benutzername
```

Ganz schön cool, oder?

## Individuelle Daten festlegen
Manchmal müssen Sie etwas Einzigartiges an Ihr ActiveRecord anhängen, wie eine benutzerdefinierte Berechnung, die es einfacher machen könnte, einfach an das Objekt anzuhängen, das dann an sagen wir eine Vorlage weitergegeben wird.

#### `setCustomData(string $field, mixed $value)`
Sie hängen die benutzerdefinierten Daten mit der Methode `setCustomData()` an.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

Und dann greifen Sie einfach darauf zu wie auf eine normale Objekt-Eigenschaft.

```php
echo $user->page_view_count;
```

## Ereignisse

Eine weitere super coole Funktion dieser Bibliothek sind Ereignisse. Ereignisse werden zu bestimmten Zeiten basierend auf bestimmten Methoden ausgelöst, die Sie aufrufen. Sie sind sehr hilfreich beim automatischen Einrichten von Daten.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Dies ist sehr hilfreich, wenn Sie eine Standardverbindung oder etwas Ähnliches festlegen müssen.

```php
// index.php oder bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // vergessen Sie den &-Verweis nicht
		// Sie könnten dies tun, um die Verbindung automatisch festzulegen
		$config['connection'] = Flight::db();
		// oder dies
		$self->transformAndPersistConnection(Flight::db());
		
		// Sie können auch auf diese Weise den Tabellennamen festlegen.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Dies ist wahrscheinlich nur nützlich, wenn Sie jede Mal eine Abfrage manipulieren müssen.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// immer id >= 0 ausführen, wenn das Ihr Ding ist
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Dies ist wahrscheinlich nützlicher, wenn Sie jedes Mal eine Logik ausführen müssen, wenn dieser Datensatz abgerufen wird. Müssen Sie etwas entschlüsseln? Müssen Sie jedes Mal eine benutzerdefinierte Zählabfrage ausführen (nicht leistungsfähig, aber egal)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// etwas entschlüsseln
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// vielleicht etwas benutzerdefiniertes speichern wie eine Abfrage???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']); 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Dies ist wahrscheinlich nur nützlich, wenn Sie jede Mal eine Abfrage manipulieren müssen.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// immer id >= 0 ausführen, wenn das Ihr Ding ist
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Ähnlich wie `afterFind()`, aber Sie können es auf alle Datensätze anwenden!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// tun Sie etwas Cooles wie afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Sehr hilfreich, wenn Sie einige Standardwerte jedes Mal festlegen müssen.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// einige solide Standardwerte festlegen
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

Vielleicht haben Sie einen Anwendungsfall, um Daten nach dem Einfügen zu ändern?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// Sie tun Sie selbst
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// oder was auch immer....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Sehr hilfreich, wenn Sie jedem Update Standardwerte festlegen müssen.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// einige solide Standardwerte festlegen
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Vielleicht haben Sie einen Anwendungsfall, um Daten nach dem Update zu ändern?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// Sie tun Sie selbst
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// oder was auch immer....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Dies ist nützlich, wenn Sie möchten, dass Ereignisse sowohl bei Inserts als auch bei Updates passieren. Ich spare Ihnen die lange Erklärung, aber ich bin mir sicher, Sie können sich denken, was es ist.

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

Nicht sicher, was Sie hier tun möchten, aber hier keine Urteile! Machen Sie weiter!

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

## Datenbankveraltungsmanagement

Wenn Sie diese Bibliothek verwenden, können Sie die Datenbankverbindung auf verschiedene Weise festlegen. Sie können die Verbindung im Konstruktor festlegen, sie über eine Konfigurationsvariable `$config['connection']` festlegen oder sie über `setDatabaseConnection()` festlegen (v0.4.1). 

```php
$pdo_connection = new PDO('sqlite:test.db'); // als Beispiel
$user = new User($pdo_connection);
// oder
$user = new User(null, [ 'connection' => $pdo_connection ]);
// oder
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Wenn Sie vermeiden möchten, jedes Mal eine `$database_connection` festzulegen, gibt es Möglichkeiten, dies zu umgehen!

```php
// index.php oder bootstrap.php
// Legen Sie dies als registrierte Klasse in Flight fest
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

// User.php
class User extends flight\ActiveRecord {
	
	public function __construct(array $config = [])
	{
		$database_connection = $config['connection'] ?? Flight::db();
		parent::__construct($database_connection, 'users', $config);
	}
}

// Und jetzt sind keine Argumente erforderlich!
$user = new User();
```

> **Hinweis:** Wenn Sie planen, Unit-Tests durchzuführen, kann dies auf diese Weise einige Herausforderungen für Unit-Tests mit sich bringen, aber insgesamt ist es, weil Sie Ihre Verbindung mit `setDatabaseConnection()` oder `$config['connection']` einfügen können, nicht zu schlecht.

Wenn Sie die Datenbankverbindung aktualisieren müssen, beispielsweise wenn Sie ein lange laufendes CLI-Skript ausführen und die Verbindung von Zeit zu Zeit aktualisieren müssen, können Sie die Verbindung mit `$your_record->setDatabaseConnection($pdo_connection)` neu festlegen.

## Beitrag

Bitte tun Sie es. :D

### Einrichtung

Wenn Sie beitragen, stellen Sie sicher, dass Sie `composer test-coverage` ausführen, um 100% Testabdeckung aufrechtzuerhalten (dies ist nicht die tatsächliche Unit-Testabdeckung, eher wie Integrationstests).

Stellen Sie außerdem sicher, dass Sie `composer beautify` und `composer phpcs` ausführen, um alle Linting-Fehler zu beheben.

## Lizenz

MIT