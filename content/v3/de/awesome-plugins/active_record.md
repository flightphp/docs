# Flight Active Record

Ein Active Record ist eine Zuordnung einer Datenbank-Entität zu einem PHP-Objekt. Einfach gesagt: Wenn Sie eine Tabelle `users` in Ihrer Datenbank haben, können Sie eine Zeile in dieser Tabelle in eine `User`-Klasse und ein `$user`-Objekt in Ihrem Codebase "übersetzen". Siehe [einfaches Beispiel](#basic-example).

Klicken Sie [hier](https://github.com/flightphp/active-record) für das Repository auf GitHub.

## Einfaches Beispiel

Nehmen wir an, Sie haben die folgende Tabelle:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Nun können Sie eine neue Klasse einrichten, um diese Tabelle darzustellen:

```php
/**
 * Eine ActiveRecord-Klasse ist normalerweise Singular
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
		// Sie können es auf diese Weise einstellen
		parent::__construct($database_connection, 'users');
		// oder auf diese Weise
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Nun beobachten Sie, wie die Magie geschieht!

```php
// Für SQLite
$database_connection = new PDO('sqlite:test.db'); // Dies ist nur ein Beispiel, Sie würden wahrscheinlich eine echte Datenbankverbindung verwenden

// Für MySQL
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// oder MySQLi
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// oder MySQLi mit nicht-objektbasierter Erstellung
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
// Hier können Sie $user->save() nicht verwenden, da es sonst als Update interpretiert wird!

echo $user->id; // 2
```

Und so einfach war es, einen neuen Benutzer hinzuzufügen! Nun, da eine Benutzerzeile in der Datenbank vorhanden ist, wie holen Sie sie heraus?

```php
$user->find(1); // Findet id = 1 in der Datenbank und gibt es zurück.
echo $user->name; // 'Bobby Tables'
```

Und was, wenn Sie alle Benutzer finden möchten?

```php
$users = $user->findAll();
```

Was ist mit einer bestimmten Bedingung?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Sehen Sie, wie viel Spaß das macht? Lassen Sie uns es installieren und loslegen!

## Installation

Einfach mit Composer installieren

```php
composer require flightphp/active-record 
```

## Verwendung

Dies kann als eigenständige Bibliothek oder mit dem Flight PHP Framework verwendet werden. Ganz nach Ihrem Wunsch.

### Eigenständig
Stellen Sie sicher, dass Sie eine PDO-Verbindung an den Konstruktor übergeben.

```php
$pdo_connection = new PDO('sqlite:test.db'); // Dies ist nur ein Beispiel, Sie würden wahrscheinlich eine echte Datenbankverbindung verwenden

$User = new User($pdo_connection);
```

> Möchten Sie nicht immer die Datenbankverbindung im Konstruktor festlegen? Sehen Sie [Datenbankverbindungsverwaltung](#database-connection-management) für andere Ideen!

### Als Methode in Flight registrieren
Wenn Sie das Flight PHP Framework verwenden, können Sie die ActiveRecord-Klasse als Service registrieren, müssen es aber ehrlich gesagt nicht tun.

```php
Flight::register('user', 'User', [ $pdo_connection ]);

// Dann können Sie es in einem Controller, einer Funktion usw. so verwenden.

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

Dies erstellt eine neue Klasse im Verzeichnis `app/records/` als `UserRecord.php` mit folgendem Inhalt:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * ActiveRecord-Klasse für die users-Tabelle.
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
     * @var array $relations Setzt die Beziehungen für das Modell
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

Findet einen Datensatz und weist ihn dem aktuellen Objekt zu. Wenn Sie eine `$id` übergeben, führt es eine Suche im Primärschlüssel mit diesem Wert durch. Wenn nichts übergeben wird, findet es einfach den ersten Datensatz in der Tabelle.

Zusätzlich können Sie andere Hilfsmethoden übergeben, um Ihre Tabelle abzufragen.

```php
// Findet einen Datensatz mit einigen Bedingungen im Voraus
$user->notNull('password')->orderBy('id DESC')->find();

// Findet einen Datensatz anhand einer spezifischen ID
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Findet alle Datensätze in der von Ihnen angegebenen Tabelle.

```php
$user->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

Gibt `true` zurück, wenn der aktuelle Datensatz hydriert wurde (aus der Datenbank abgerufen).

```php
$user->find(1);
// Wenn ein Datensatz mit Daten gefunden wird...
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

Wenn Sie einen textbasierten Primärschlüssel haben (wie eine UUID), können Sie den Primärschlüsselwert vor dem Einfügen auf eine von zwei Arten festlegen.

```php
$user = new User($pdo_connection, [ 'primaryKey' => 'uuid' ]);
$user->uuid = 'some-uuid';
$user->name = 'demo';
$user->password = md5('demo');
$user->insert(); // oder $user->save();
```

Oder Sie lassen den Primärschlüssel automatisch durch Ereignisse generieren.

```php
class User extends flight\ActiveRecord {
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users', [ 'primaryKey' => 'uuid' ]);
		// Sie können den primaryKey auch auf diese Weise statt im Array oben festlegen.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // oder wie auch immer Sie Ihre eindeutigen IDs generieren müssen
	}
}
```

Wenn Sie den Primärschlüssel vor dem Einfügen nicht festlegen, wird er auf `rowid` gesetzt und die Datenbank generiert ihn für Sie, aber er wird nicht persistiert, da dieses Feld möglicherweise nicht in Ihrer Tabelle existiert. Deshalb wird empfohlen, das Ereignis zu verwenden, um dies automatisch für Sie zu handhaben.

#### `update(): boolean|ActiveRecord`

Aktualisiert den aktuellen Datensatz in der Datenbank.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `save(): boolean|ActiveRecord`

Fügt den aktuellen Datensatz in die Datenbank ein oder aktualisiert ihn. Wenn der Datensatz eine ID hat, wird er aktualisiert, andernfalls eingefügt.

```php
$user = new User($pdo_connection);
$user->name = 'demo';
$user->password = md5('demo');
$user->save();
```

**Hinweis:** Wenn Sie Beziehungen in der Klasse definiert haben, speichert er rekursiv auch diese Beziehungen, wenn sie definiert, instanziiert und schmutzige Daten zum Aktualisieren haben. (v0.4.0 und höher)

#### `delete(): boolean`

Löscht den aktuellen Datensatz aus der Datenbank.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

Sie können auch mehrere Datensätze löschen, indem Sie vorher eine Suche ausführen.

```php
$user->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Schmutzige Daten beziehen sich auf die Daten, die in einem Datensatz geändert wurden.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// Bis zu diesem Punkt ist nichts "schmutzig".

$user->email = 'test@example.com'; // Nun gilt email als "schmutzig", da es geändert wurde.
$user->update();
// Nun gibt es keine schmutzigen Daten mehr, da sie aktualisiert und in der Datenbank persistiert wurden

$user->password = password_hash()'newpassword'); // Nun ist das schmutzig
$user->dirty(); // Ohne Parameter löscht es alle schmutzigen Einträge.
$user->update(); // Nichts wird aktualisiert, da nichts als schmutzig erfasst wurde.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // Sowohl name als auch password werden aktualisiert.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Dies ist ein Alias für die `dirty()`-Methode. Es ist etwas klarer, was Sie tun.

```php
$user->copyFrom([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // Sowohl name als auch password werden aktualisiert.
```

#### `isDirty(): boolean` (v0.4.0)

Gibt `true` zurück, wenn der aktuelle Datensatz geändert wurde.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@email.com';
$user->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Setzt den aktuellen Datensatz auf seinen anfänglichen Zustand zurück. Das ist wirklich gut für Schleifenverhalten zu verwenden. Wenn Sie `true` übergeben, setzt es auch die Abfragedaten zurück, die verwendet wurden, um das aktuelle Objekt zu finden (Standardverhalten).

```php
$users = $user->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_connection);

foreach($users as $user) {
	$user_company->reset(); // Mit einer sauberen Tafel beginnen
	$user_company->user_id = $user->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Nachdem Sie eine `find()`, `findAll()`, `insert()`, `update()` oder `save()`-Methode ausgeführt haben, können Sie den generierten SQL-Code abrufen und für Debugging-Zwecke verwenden.

## SQL-Abfragemethoden
#### `select(string $field1 [, string $field2 ... ])`

Sie können nur einige Spalten in einer Tabelle auswählen, wenn Sie möchten (es ist performanter bei wirklich breiten Tabellen mit vielen Spalten)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Sie können technisch eine andere Tabelle wählen! Warum nicht?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Sie können sogar zu einer anderen Tabelle in der Datenbank joinen.

```php
$user->join('contacts', 'contacts.user_id = users.id')->find();
```

#### `where(string $where_conditions)`

Sie können einige benutzerdefinierte where-Argumente setzen (Sie können in dieser where-Anweisung keine Parameter setzen)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Sicherheitshinweis** - Sie könnten versucht sein, etwas wie `$user->where("id = '{$id}' AND name = '{$name}'")->find();` zu tun. Bitte TUN SIE DAS NICHT!!! Das ist anfällig für SQL-Injection-Angriffe. Es gibt viele Artikel online, suchen Sie bitte nach "sql injection attacks php" und Sie finden viele Artikel zu diesem Thema. Der richtige Weg, das mit dieser Bibliothek zu handhaben, ist, anstelle dieser `where()`-Methode etwas wie `$user->eq('id', $id)->eq('name', $name)->find();` zu tun. Wenn Sie es absolut tun müssen, hat die `PDO`-Bibliothek `$pdo->quote($var)`, um es für Sie zu escapen. Nur nach der Verwendung von `quote()` können Sie es in einer `where()`-Anweisung verwenden.

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

Begrenzen Sie die Anzahl der zurückgegebenen Datensätze. Wenn eine zweite Ganzzahl gegeben ist, wird sie als offset, limit genau wie in SQL verwendet.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

## WHERE-Bedingungen
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

Where `field = $value`

```php
$user->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

Where `field <> $value`

```php
$user->ne('id', 1)->find();
```

#### `isNull(string $field)`

Where `field IS NULL`

```php
$user->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

Where `field IS NOT NULL`

```php
$user->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

Where `field > $value`

```php
$user->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

Where `field < $value`

```php
$user->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

Where `field >= $value`

```php
$user->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

Where `field <= $value`

```php
$user->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

Where `field LIKE $value` oder `field NOT LIKE $value`

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

Where `field IN($value)` oder `field NOT IN($value)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Where `field BETWEEN $value AND $value1`

```php
$user->between('id', [1, 2])->find();
```

### OR-Bedingungen

Es ist möglich, Ihre Bedingungen in einer OR-Anweisung zu umschließen. Dies geschieht entweder mit den Methoden `startWrap()` und `endWrap()` oder indem Sie den 3. Parameter der Bedingung nach Feld und Wert ausfüllen.

```php
// Methode 1
$user->eq('id', 1)->startWrap()->eq('name', 'demo')->or()->eq('name', 'test')->endWrap('OR')->find();
// Dies wird zu `id = 1 AND (name = 'demo' OR name = 'test')` ausgewertet

// Methode 2
$user->eq('id', 1)->eq('name', 'demo', 'OR')->find();
// Dies wird zu `id = 1 OR name = 'demo'` ausgewertet
```

## Beziehungen
Mit dieser Bibliothek können Sie mehrere Arten von Beziehungen festlegen. Sie können one-to-many- und one-to-one-Beziehungen zwischen Tabellen festlegen. Dies erfordert eine etwas zusätzliche Einrichtung in der Klasse im Voraus.

Das Festlegen des `$relations`-Arrays ist nicht schwer, aber das Erraten der korrekten Syntax kann verwirrend sein.

```php
protected array $relations = [
	// Sie können den Schlüssel beliebig benennen. Der Name des ActiveRecord ist wahrscheinlich gut. Beispiel: user, contact, client
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
		// Nur so nebenbei, dies joinet auch nur zum Primärschlüssel des "anderen" Modells

		// optional
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // zusätzliche Bedingungen, die Sie beim Joinen der Beziehung wollen
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// optional
		'back_reference_name' // dies ist, wenn Sie diese Beziehung zurück auf sich selbst referenzieren möchten, z. B. $user->contact->user;
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

Nun haben wir die Referenzen eingerichtet, sodass wir sie sehr einfach verwenden können!

```php
$user = new User($pdo_connection);

// Finden Sie den neuesten Benutzer.
$user->notNull('id')->orderBy('id desc')->find();

// Kontakte mit der Beziehung abrufen:
foreach($user->contacts as $contact) {
	echo $contact->id;
}

// Oder wir können es umgekehrt tun.
$contact = new Contact();

// Einen Kontakt finden
$contact->find();

// Benutzer mit der Beziehung abrufen:
echo $contact->user->name; // Dies ist der Benutzername
```

Ziemlich cool, oder?

### Eager Loading

#### Überblick
Eager Loading löst das N+1-Abfrageproblem, indem Beziehungen im Voraus geladen werden. Anstatt für jede Beziehung eines Datensatzes eine separate Abfrage auszuführen, holt Eager Loading alle verwandten Daten in nur einer zusätzlichen Abfrage pro Beziehung.

> **Hinweis:** Eager Loading ist nur ab v0.7.0 verfügbar.

#### Grundlegende Verwendung
Verwenden Sie die `with()`-Methode, um anzugeben, welche Beziehungen eager geladen werden sollen:
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
Mehrere Beziehungen auf einmal laden:
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
// Elternbenutzer für alle Kontakte eager laden
$contacts = $contact->with('user')->findAll();
foreach ($contacts as $c) {
    // $c->user ist bereits geladen
    echo $c->user->name;
}
```
##### Mit find()
Eager Loading funktioniert sowohl mit 
findAll()
 als auch 
find()
:

```php
$user = $user->with('contacts')->find(1);
// Benutzer und alle ihre Kontakte in 2 Abfragen geladen
```
#### Leistungsverbesserungen
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
Für 10 Benutzer reduziert das die Abfragen von 11 auf 2 - eine Reduktion um 82%!

#### Wichtige Hinweise
- Eager Loading ist vollständig optional - Lazy Loading funktioniert weiterhin wie zuvor
- Bereits geladene Beziehungen werden automatisch übersprungen
- Back-Referenzen funktionieren mit Eager Loading
- Beziehungs-Callbacks werden während des Eager Loadings berücksichtigt

#### Einschränkungen
- Verschachteltes Eager Loading (z. B. 
with(['contacts.addresses'])
) wird derzeit nicht unterstützt
- Eager-Load-Einschränkungen über Closures werden in dieser Version nicht unterstützt

## Benutzerdefinierte Daten festlegen
Manchmal müssen Sie etwas Einzigartiges an Ihr ActiveRecord anhängen, wie eine benutzerdefinierte Berechnung, die einfacher sein könnte, einfach an das Objekt angehängt zu werden, das dann z. B. an eine Vorlage übergeben wird.

#### `setCustomData(string $field, mixed $value)`
Sie hängen die benutzerdefinierten Daten mit der `setCustomData()`-Methode an.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

Und dann referenzieren Sie es einfach wie eine normale Objekteigenschaft.

```php
echo $user->page_view_count;
```

## Ereignisse

Eine weitere super coole Funktion dieser Bibliothek sind Ereignisse. Ereignisse werden zu bestimmten Zeiten ausgelöst, basierend auf bestimmten Methoden, die Sie aufrufen. Sie sind sehr hilfreich, um Daten automatisch für Sie einzurichten.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Das ist wirklich hilfreich, wenn Sie eine Standardverbindung oder Ähnliches festlegen müssen.

```php
// index.php oder bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // Vergessen Sie nicht die & Referenz
		// Sie könnten das tun, um die Verbindung automatisch zu setzen
		$config['connection'] = Flight::db();
		// oder das
		$self->transformAndPersistConnection(Flight::db());
		
		// Sie können auch den Tabellennamen auf diese Weise festlegen.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Das ist wahrscheinlich nur nützlich, wenn Sie jede Abfrage manipulieren müssen.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFind(self $self) {
		// Immer id >= 0 ausführen, wenn das Ihr Ding ist
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Diese ist wahrscheinlich nützlicher, wenn Sie immer etwas Logik ausführen müssen, jedes Mal, wenn dieser Datensatz abgerufen wird. Müssen Sie etwas entschlüsseln? Müssen Sie eine benutzerdefinierte Zählabfrage ausführen (nicht performant, aber egal)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFind(self $self) {
		// Etwas entschlüsseln
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// Vielleicht etwas Benutzerdefiniertes speichern wie eine Abfrage???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Das ist wahrscheinlich nur nützlich, wenn Sie jede Abfrage manipulieren müssen.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeFindAll(self $self) {
		// Immer id >= 0 ausführen, wenn das Ihr Ding ist
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Ähnlich wie `afterFind()`, aber Sie können es für alle Datensätze tun!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// Etwas Cooles tun wie afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Wirklich hilfreich, wenn Sie einige Standardwerte jedes Mal festlegen müssen.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// Einige vernünftige Standardwerte setzen
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
		// Machen Sie, was Sie wollen
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// oder was auch immer....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Wirklich hilfreich, wenn Sie einige Standardwerte jedes Mal bei einer Aktualisierung festlegen müssen.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function beforeInsert(self $self) {
		// Einige vernünftige Standardwerte setzen
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Vielleicht haben Sie einen Anwendungsfall, um Daten nach der Aktualisierung zu ändern?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

	protected function afterInsert(self $self) {
		// Machen Sie, was Sie wollen
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// oder was auch immer....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Das ist nützlich, wenn Sie Ereignisse haben möchten, die sowohl bei Einfügungen als auch bei Aktualisierungen auftreten. Ich spare mir die lange Erklärung, aber ich bin sicher, Sie können erraten, was es ist.

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

Nicht sicher, was Sie hier tun möchten, aber keine Urteile hier! Legen Sie los!

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

## Datenbankverbindungsverwaltung

Wenn Sie diese Bibliothek verwenden, können Sie die Datenbankverbindung auf einige verschiedene Weisen festlegen. Sie können die Verbindung im Konstruktor festlegen, über eine Konfigurationsvariable `$config['connection']` oder über `setDatabaseConnection()` (v0.4.1). 

```php
$pdo_connection = new PDO('sqlite:test.db'); // als Beispiel
$user = new User($pdo_connection);
// oder
$user = new User(null, [ 'connection' => $pdo_connection ]);
// oder
$user = new User();
$user->setDatabaseConnection($pdo_connection);
```

Wenn Sie vermeiden möchten, immer eine `$database_connection` jedes Mal festzulegen, wenn Sie ein Active Record aufrufen, gibt es Wege darum!

```php
// index.php oder bootstrap.php
// Dies als registrierte Klasse in Flight festlegen
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

// User.php
class User extends flight\ActiveRecord {
	
	public function __construct(array $config = [])
	{
		$database_connection = $config['connection'] ?? Flight::db();
		parent::__construct($database_connection, 'users', $config);
	}
}

// Und nun keine Argumente erforderlich!
$user = new User();
```

> **Hinweis:** Wenn Sie Unit-Tests planen, kann das auf diese Weise einige Herausforderungen für Unit-Tests hinzufügen, aber insgesamt, da Sie Ihre Verbindung mit `setDatabaseConnection()` oder `$config['connection']` injizieren können, ist es nicht zu schlecht.

Wenn Sie die Datenbankverbindung aktualisieren müssen, z. B. wenn Sie ein langes CLI-Skript ausführen und die Verbindung alle paar Mal aktualisieren müssen, können Sie die Verbindung mit `$your_record->setDatabaseConnection($pdo_connection)` neu setzen.

## Mitwirkung

Bitte tun Sie das. :D

### Einrichtung

Wenn Sie beitragen, stellen Sie sicher, dass Sie `composer test-coverage` ausführen, um 100% Testabdeckung zu wahren (das ist keine echte Unit-Test-Abdeckung, eher wie Integrationstests).

Stellen Sie auch sicher, dass Sie `composer beautify` und `composer phpcs` ausführen, um Linting-Fehler zu beheben.

## Lizenz

MIT