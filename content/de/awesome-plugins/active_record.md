# Flug aktiver Datensatz

Ein aktiver Datensatz ordnet eine Datenbankeinheit einem PHP-Objekt zu. Einfach ausgedrückt, wenn Sie eine Benutzertabelle in Ihrer Datenbank haben, können Sie eine Zeile in dieser Tabelle in eine `Benutzer`-Klasse und ein `$benutzer`-Objekt in Ihrem Code übersetzen. Siehe [Grundbeispiel](#grundbeispiel).

Klicken Sie [hier](https://github.com/flightphp/active-record) für das Repository auf GitHub.

## Grundbeispiel

Nehmen wir an, Sie haben die folgende Tabelle:

```sql
CREATE TABLE benutzer (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	passwort TEXT 
);
```

Jetzt können Sie eine neue Klasse einrichten, die diese Tabelle darstellt:

```php
/**
 * Eine ActiveRecord-Klasse ist in der Regel im Singular
 * 
 * Es wird dringend empfohlen, die Eigenschaften der Tabelle hier als Kommentare hinzuzufügen
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class Benutzer erstreckt sich flight\ActiveRecord {
	public function __construct($datenbank_verbindung)
	{
		// Sie können es auf diese Weise festlegen
		parent::__construct($datenbank_verbindung, 'benutzer');
		// oder so
		parent::__construct($datenbank_verbindung, null, [ 'tabelle' => 'benutzer']);
	}
}
```

Schauen Sie nun, wie die Magie passiert!

```php
// für SQLite
$datenbank_verbindung = new PDO('sqlite:test.db'); // Dies ist nur ein Beispiel, Sie würden wahrscheinlich eine echte Datenbankverbindung verwenden

// für MySQL
$datenbank_verbindung = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'benutzername', 'passwort');

// oder mysqli
$datenbank_verbindung = new mysqli('localhost', 'benutzername', 'passwort', 'test_db');
// oder mysqli mit nicht-objektbasierter Erstellung
$datenbank_verbindung = mysqli_connect('localhost', 'benutzername', 'passwort', 'test_db');

$benutzer = new Benutzer($datenbank_verbindung);
$benutzer->name = 'Bobby Tables';
$benutzer->password = password_hash('Ein cooles Passwort');
$benutzer->einfügen();
// oder $benutzer->speichern();

echo $benutzer->id; // 1

$benutzer->name = 'Joseph Mamma';
$benutzer->password = password_hash('nochmal ein cooles Passwort!!!');
$benutzer->insert();
// Hier kann $user->save() nicht verwendet werden, da sonst angenommen wird, dass es ein Update ist!

echo $benutzer->id; // 2
```

Und es war so einfach, einen neuen Benutzer hinzuzufügen! Jetzt, da es einen Benutzerdatensatz in der Datenbank gibt, wie können Sie ihn herausholen?

```php
$benutzer->find(1); // Finde id = 1 in der Datenbank und gib ihn zurück.
echo $benutzer->name; // 'Bobby Tables'
```

Und was ist, wenn Sie alle Benutzer finden möchten?

```php
$benutzer = $user->findAll();
```

Was ist mit einer bestimmten Bedingung?

```php
$benutzer = $user->like('name', '%mamma%')->findAll();
```

Sehen Sie, wie viel Spaß das macht? Lassen Sie uns es installieren und loslegen!

## Installation

Einfach mit Composer installieren

```php
composer require flightphp/active-record 
```

## Verwendung

Dies kann als eigenständige Bibliothek oder mit dem Flight PHP Framework verwendet werden. Ganz wie Sie möchten.

### Eigenständig
Stellen Sie einfach sicher, dass Sie eine PDO-Verbindung an den Konstruktor übergeben.

```php
$pdo_verbindung = new PDO('sqlite:test.db'); // Dies ist nur ein Beispiel, Sie würden wahrscheinlich eine echte Datenbankverbindung verwenden.

$Benutzer = new Benutzer($pdo_verbindung);
```

> Möchten Sie Ihre Datenbankverbindung nicht immer im Konstruktor festlegen? Sehen Sie sich [Verwaltung der Datenbankverbindung](#verwaltung-der-datenbankverbindung) für andere Ideen an!

### Als Methode in Flight registrieren
Wenn Sie das Flight PHP Framework nutzen, können Sie die ActiveRecord-Klasse als Dienst registrieren, aber Sie müssen dies ehrlich gesagt nicht tun.

```php
Flight::register('benutzer', 'Benutzer', [ $pdo_verbindung ]);

// dann können Sie es in einem Controller, einer Funktion usw. verwenden

Flight::benutzer()->find(1);
```

## Methoden von `runway`

[runway](https://docs.flightphp.com/awesome-plugins/runway) ist ein CLI-Tool für Flight, das über ein benutzerdefiniertes Steuerungsprogramm für diese Bibliothek verfügt.

```bash
# Verwendung
php runway make:record database_table_name [class_name]

# Beispiel
php runway make:record benutzer
```

Dies erstellt eine neue Klasse im Verzeichnis `app/records/` als `UserRecord.php` mit folgendem Inhalt:

```php
<?php

declare(strict_types=1);

namespace app\records;

/**
 * ActiveRecord-Klasse für die Benutzertabelle.
 * @link https://docs.flightphp.com/awesome-plugins/active-record
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password_hash
 * @property string $created_dt
 */
class UserRecord extends \flight\ActiveRecord
{
    /**
     * @var array $relations Setzt die Beziehungen für das Modell
     *   https://docs.flightphp.com/awesome-plugins/active-record#verknüpfungen
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
        parent::__construct($databaseConnection, 'benutzer');
    }
}
```

## CRUD-Funktionen

#### `find($id = null) : boolean|ActiveRecord`

Sucht einen Datensatz und weist ihn dem aktuellen Objekt zu. Wenn Sie eine `$id` von irgendeiner Art übergeben, erfolgt eine Abfrage des Primärschlüssels mit diesem Wert. Wenn nichts übergeben wird, wird einfach der erste Datensatz in der Tabelle gefunden.

Zusätzlich können Sie ihm andere Hilfsmethoden übergeben, um Ihre Tabelle abzufragen.

```php
// Suchen Sie einen Datensatz mit bestimmten Bedingungen im Voraus
$benutzer->notNull('password')->orderBy('id DESC')->find();

// Suchen Sie einen Datensatz anhand einer bestimmten ID
$id = 123;
$benutzer->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Findet alle Datensätze in der von Ihnen angegebenen Tabelle.

```php
$benutzer->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

Gibt `true` zurück, wenn der aktuelle Datensatz geholt (aus der Datenbank abgerufen) wurde.

```php
$benutzer->find(1);
// wenn ein Datensatz mit Daten gefunden wird...
$benutzer->isHydrated(); // true
```

#### `insert(): boolean|ActiveRecord`

Fügt den aktuellen Datensatz in die Datenbank ein.

```php
$benutzer = new Benutzer($pdo_verbindung);
$benutzer->name = 'Demo';
$benutzer->password = md5('Demo');
$benutzer->insert();
```

##### Textbasierte Primärschlüssel

Wenn Sie einen textbasierten Primärschlüssel haben (wie z.B. eine UUID), können Sie den Primärschlüsselwert vor dem Einfügen auf zwei Arten festlegen.

```php
$benutzer = new Benutzer($pdo_verbindung, [ 'primaryKey' => 'uuid' ]);
$benutzer->uuid = 'Einige-UUID';
$benutzer->name = 'Demo';
$benutzer->password = md5('Demo');
$benutzer->insert(); // oder $benutzer->save();
```

oder Sie können den Primärschlüssel automatisch generieren lassen, indem Sie Ereignisse verwenden.

```php
class User extends flight\ActiveRecord {
	public function __construct($datenbank_verbindung)
	{
		parent::__construct($datenbank_verbindung, 'benutzer', [ 'primaryKey' => 'uuid' ]);
		// können Sie auch den Primärschlüssel auf diese Weise festlegen, anstatt des obigen Arrays.
		$this->primaryKey = 'uuid';
	}

	protected function beforeInsert(self $self) {
		$self->uuid = uniqid(); // oder wie auch immer Sie Ihre eindeutigen IDs generieren müssen
	}
}
```

Wenn Sie den Primärschlüssel vor dem Einfügen nicht festlegen, wird er auf den `rowid` gesetzt und die Datenbank wird ihn für Sie generieren, aber er wird nicht gespeichert, da dieses Feld in Ihrer Tabelle möglicherweise nicht existiert. Deshalb wird empfohlen, das Ereignis zu verwenden, um dies automatisch für Sie zu behandeln.

#### `update(): boolean|ActiveRecord`

Aktualisiert den aktuellen Datensatz in der Datenbank.

```php
$benutzer->greaterThan('id', 0)->orderBy('id desc')->find();
$benutzer->email = 'test@example.com';
$benutzer->update();
```

#### `save(): boolean|ActiveRecord`

Fügt den aktuellen Datensatz in die Datenbank ein oder aktualisiert ihn. Wenn der Datensatz eine ID hat, wird er aktualisiert, andernfalls wird er eingefügt.

```php
$benutzer = new Benutzer($pdo_verbindung);
$benutzer->name = 'Demo';
$benutzer->password = md5('Demo');
$benutzer->save();
```

**Anmerkung:** Wenn in der Klasse Beziehungen definiert sind, werden diese Beziehungen rekursiv gespeichert, wenn sie definiert, instanziiert und schmutzige Daten zum Aktualisieren haben (ab Version 0.4.0).

#### `delete(): boolean`

Löscht den aktuellen Datensatz aus der Datenbank.

```php
$benutzer->gt('id', 0)->orderBy('id desc')->find();
$benutzer->delete();
```

Sie können auch mehrere Datensätze löschen, indem Sie zuerst eine Suche durchführen.

```php
$benutzer->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Schmutzige Daten beziehen sich auf die Daten, die in einem Datensatz geändert wurden.

```php
$benutzer->greaterThan('id', 0)->orderBy('id desc')->find();

// Es gibt bis zu diesem Zeitpunkt nichts "schmutziges".
$benutzer->email = 'test@example.com'; // Jetzt gilt die E-Mail-Adresse als "schmutzig", da sie geändert wurde.
$benutzer->update();
// Jetzt gibt es keine schmutzigen Daten mehr, weil sie aktualisiert und in der Datenbank gespeichert wurden.

$benutzer->password = password_hash()'neuespasswort'); // Jetzt ist dies schmutzig
$benutzer->dirty(); // Das Übergeben von nichts löscht alle schmutzigen Einträge.
$benutzer->update(); // Nichts wird aktualisiert, da nichts als schmutzig erfasst wurde.

$benutzer->dirty([ 'name' => 'etwas', 'password' => password_hash('ein anderes Passwort') ]);
$benutzer->update(); // sowohl Name als auch Passwort werden aktualisiert.
```

#### `copyFrom(array $data): ActiveRecord` (v0.4.0)

Dies ist ein Alias für die Methode `dirty()`. Es ist etwas klarer, was Sie tun.

```php
$benutzer->copyFrom([ 'name' => 'etwas', 'password' => password_hash('ein anderes Passwort') ]);
$benutzer->update(); // sowohl Name als auch Passwort werden aktualisiert.
```

#### `isDirty(): boolean` (v0.4.0)

Gibt `true` zurück, wenn der aktuelle Datensatz geändert wurde.

```php
$benutzer->greaterThan('id', 0)->orderBy('id desc')->find();
$benutzer->email = 'test@email.com';
$benutzer->isDirty(); // true
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Setzt den aktuellen Datensatz auf seinen Ausgangszustand zurück. Dies ist wirklich gut für Schleifenverhalten.
Wenn Sie `true` übergeben, werden auch die Abfragedaten zurückgesetzt, die zum Finden des aktuellen Objekts verwendet wurden (Standardverhalten).

```php
$benutzer = $benutzer->greaterThan('id', 0)->orderBy('id desc')->find();
$user_company = new UserCompany($pdo_verbindung);

foreach($benutzer as $benutzer) {
	$user_company->reset(); // mit einem sauberen Blatt beginnen
	$user_company->user_id = $benutzer->id;
	$user_company->company_id = $some_company_id;
	$user_company->insert();
}
```

#### `getBuiltSql(): string` (v0.4.1)

Nach Ausführung einer `find()`, `findAll()`, `insert()`, `update()` oder `save()`-Methode können Sie den erstellten SQL abrufen und für Debug-Zwecke verwenden.

## SQL-Abfragemethoden
#### `select(string $field1 [, string $field2 ... ])`

Sie können nur einige der Spalten in einer Tabelle auswählen, wenn Sie möchten (was bei sehr breiten Tabellen mit vielen Spalten performanter ist).

```php
$benutzer->select('id', 'name')->find();
```

#### `from(string $table)`

Sie können theoretisch auch eine andere Tabelle auswählen! Warum nicht?!

```php
$benutzer->select('id', 'name')->from('benutzer')->find();
```

#### `join(string $table_name, string $join_condition)`

Sie können sogar mit einer anderen Tabelle in der Datenbank verknüpfen.

```php
$benutzer->join('kontakte', 'kontakte.benutzer_id = benutzer.id')->find();
```

#### `where(string $where_conditions)`

Sie können benutzerdefinierte where-Argumente festlegen (Sie können in dieser where-Anweisung keine Parameter festlegen)

```php
$benutzer->where('id=1 AND name="demo"')->find();
```

**Sicherheitshinweis** - Sie könnten versucht sein, etwas wie `$benutzer->where("id = '{$id}' AND name = '{$name}'")->find();` zu tun. Bitte TUN SIE DAS NICHT!!! Dies ist anfällig für das, was als SQL-Injection-Angriffe bekannt ist. Es gibt viele Artikel online, suchen Sie bitte nach "SQL-Injektionsangriffen php", und Sie finden viele Artikel zu diesem Thema. Der richtige Umgang damit mit dieser Bibliothek ist anstelle dieser `where()`-Methode etwas wie `$benutzer->eq('id', $id)->eq('name', $name)->find();` zu tun. Wenn Sie dies unbedingt tun müssen, bietet die `PDO`-Bibliothek `$pdo->quote($var)` an, um es für Sie zu escapen. Erst nach Verwendung von `quote()` können Sie es in einer `where()`-Anweisung verwenden.

#### `group(string $group_by_statement)/groupBy(string $group_by_statement)`

Gruppieren Sie Ihre Ergebnisse nach einer bestimmten Bedingung.

```php
$benutzer->select('COUNT(*) as count')->groupBy('name')->findAll();
```

#### `order(string $order_by_statement)/orderBy(string $order_by_statement)`

Sortieren Sie die zurückgegebene Abfrage auf eine bestimmte Weise.

```php
$benutzer->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Begrenzen Sie die Anzahl der zurückgegebenen Datensätze. Wenn ein zweites Int übergeben wird, wird es wie in SQL als Offset und Limit verwendet.

```php
$benutzer->orderby('name DESC')->limit(0, 10)->findAll();
```

## WO-Bedingungen
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
#### `lessThanOrEqual(string $field, mixed $value) /le(string $field, mixed $value) / lte(string $field, mixed $value)`

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

## Beziehungen
Sie können mit dieser Bibliothek verschiedene Arten von Beziehungen festlegen. Sie können zwischen Tabellen eine-eine und eine-viele Beziehungen festlegen. Dafür ist eine kleine zusätzliche Einrichtung in der Klasse erforderlich.

Das Setzen des `$relations`-Arrays ist nicht schwer, aber das Erraten der richtigen Syntax kann verwirrend sein.

```php
protected array $relations = [
	// Sie können den Schlüssel beliebig benennen. Der Name des ActiveRecords ist wahrscheinlich gut. z.B.: user, contact, client
	'user' => [
		// erforderlich
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // dies ist der Beziehungstyp

		// erforderlich
		'Some_Class', // dies ist die "andere" ActiveRecord-Klasse, auf die verwiesen wird

		// erforderlich
	// je nach Beziehungstyp
	// self::HAS_ONE = der Fremdschlüssel, der auf das Join verweist
	// self::HAS_MANY = der Fremdschlüssel, der auf das Join verweist
	// self::BELONGS_TO = der lokale Schlüssel, der auf das Join verweist
		'lokal_oder_fremdschlüssel',
		// nur zur Information, verknüpft dies auch nur mit dem Primärschlüssel des "anderen" Modells

		// optional
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // zusätzliche Bedingungen, die Sie bei der Verknüpfung der Beziehung wünschen
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// optional
		'back_reference_name' // dies ist, wenn Sie diese Beziehung zurückverfolgen möchten zurück zu sich selbst z.B.: $user->contact->user;
	];
]
```

```php
class User extends ActiveRecord{
	protected array $relations = [
		'kontakte' => [ self::HAS_MANY, Kontakt::class, 'benutzer_id' ],
		'kontakt' => [ self::HAS_ONE, Kontakt::class, 'benutzer_id' ],
	];

	public function __construct($datenbank_verbindung)
	{
		parent::__construct($datenbank_verbindung, 'benutzer');
	}
}

class Contact extends ActiveRecord{
	protected array $relations = [
		'user' => [ self::BELONGS_TO, User::class, 'benutzer_id' ],
		'user_with_backref' => [ self::BELONGS_TO, User::class, 'benutzer_id', [], 'contact' ],
	];
	public function __construct($datenbank_verbindung)
	{
		parent::__construct($datenbank_verbindung, 'kontakte');
	}
}
```

Jetzt sind die Referenzen eingerichtet, sodass wir sie sehr einfach verwenden können!

```php
$user = new User($pdo_verbindung);

// finden Sie den neuesten Benutzer.
$benutzer->notNull('id')->orderBy('id desc')->find();

// holen Sie Kontakte, indem Sie die Beziehung verwenden:
foreach($benutzer->kontakte as $kontakt) {
	echo $kontakt->id;
}

// oder wir können den anderen Weg gehen.
$kontakt = new Kontakt();

// finde einen Kontakt
$kontakt->find();

// holen Sie sich den Benutzer, indem Sie die Beziehung verwenden:
echo $kontakt->user->name; // das ist der Benutzername
```

Ziemlich cool, oder?

## Festlegen von benutzerdefinierten Daten
Manchmal müssen Sie Ihrem Active Record etwas Einzigartiges anhängen, wie z.B. eine benutzerdefinierte Berechnung, die möglicherweise einfacher am Objekt angehängt werden kann, das dann an eine Vorlage übergeben wird.

#### `setCustomData(string $field, mixed $value)`
Sie hängen die benutzerdefinierten Daten mit der Methode `setCustomData()` an.
```php
$benutzer->setCustomData('page_view_count', $page_view_count);
```

Und dann beziehen Sie sich einfach darauf wie auf eine normale Objekteigenschaft.

```php
echo $benutzer->page_view_count;
```

## Ereignisse

Ein weiteres super tolles Feature dieser Bibliothek sind Ereignisse. Ereignisse werden zu bestimmten Zeitpunkten basierend auf bestimmten von Ihnen aufgerufenen Methoden ausgelöst. Sie sind sehr hilfreich bei der Datenbearbeitung für Sie automatisch einzurichten.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Dies ist sehr hilfreich, wenn Sie eine Standardverbindung oder so setzen müssen.

```php
// index.php oder bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // don't forget the & reference
	// Sie könnten dies tun, um automatisch die Verbindung zu setzen
$config['connection'] = Flight::db();
		// oder so
		$self->transformAndPersistConnection(Flight::db());
		
		// Sie können auch den Tabellennamen auf diese Weise festlegen.
	$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Das ist wahrscheinlich nur nützlich, wenn Sie jedes Mal eine Abfrage-Manipulation benötigen.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datenbank_verbindung)
	{
		parent::__construct($datenbank_verbindung, 'users');
	}

	protected function beforeFind(self $self) {
// immer die ID >= 0 laufen lassen, wenn das Ihr Ding ist
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Dies ist wahrscheinlich nützlicher, wenn Sie jedes Mal, wenn dieser Datensatz geholt wird, etwas Logik ausführen müssen. Müssen Sie etwas entschlüsseln? Müssen Sie jedes Mal eine benutzerdefinierte Zählabfrage ausführen (nicht performant, aber egal)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datenbank_verbindung)
	{
		parent::__construct($datenbank_verbindung, 'users');
	}

	protected function afterFind(self $self) {
// etwas entschlüsseln
		$self->secret = yourDecryptFunction($self->secret, $some_key);

// vielleicht etwas benutzerdefiniertes speichern wie eine Abfrage???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Das ist wahrscheinlich nur nützlich, wenn Sie jedes Mal eine Abfrage-Manipulation benötigen.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datenbank_verbindung)
	{
		parent::__construct($datenbank_verbindung, 'users');
	}

	protected function beforeFindAll(self $self) {
// immer die ID >= 0 laufen lassen, wenn das Ihr Ding ist
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Ähnlich wie `afterFind()`, aber Sie können es auf alle Datensätze anwenden!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datenbank_verbindung)
	{
		parent::__construct($datenbank_verbindung, 'users');
	}

	protected function afterFindAll(array $results) {

foreach($results as $self) {
// mache etwas Cooleres wie afterFind()
}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Wirklich hilfreich, wenn Sie jedes Mal einige Standardwerte setzen müssen.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datenbank_verbindung)
	{
		parent::__construct($datenbank_verbindung, 'users');
	}

	protected function beforeInsert(self $self) {
// einige vernünftige Standardwerte setzen
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

Vielleicht haben Sie einen Anwendungsfall zum Ändern von Daten nach dem Einfügen?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datenbank_verbindung)
	{
		parent::__construct($datenbank_verbindung, 'users');
	}

	protected function afterInsert(self $self) {
// Sie tun, was Sie für richtig halten
		Flight::cache()->set('most_recent_insert_id', $self->id);
// oder was auch immer....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Wirklich hilfreich, wenn Sie jedes Mal einige Standardwerte beim Update setzen müssen.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datenbank_verbindung)
	{
		parent::__construct($datenbank_verbindung, 'users');
	}

	protected function beforeInsert(self $self) {
// einige vernünftige Standardwerte setzen
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Vielleicht haben Sie einen Anwendungsfall zum Ändern von Daten nach dem Update?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datenbank_verbindung)
	{
		parent::__construct($datenbank_verbindung, 'users');
	}

	protected function afterInsert(self $self) {
// Sie tun, was Sie für richtig halten
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
// oder was auch immer....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Dies ist nützlich, wenn Sie Ereignisse sowohl bei Inserts als auch bei Updates wünschen. Ich erspare Ihnen die lange Erklärung, aber ich bin sicher, Sie können erraten, was es ist.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datenbank_verbindung)
	{
		parent::__construct($datenbank_verbindung, 'users');
	}

	protected function beforeSave(self $self) {
		$self->last_updated = gmdate('Y-m-d H:i:s');
	} 
}
```

#### `beforeDelete(ActiveRecord $ActiveRecord)/afterDelete(ActiveRecord $ActiveRecord)`

Nicht sicher, was Sie hier tun möchten, aber keine Urteile hier! Ran an die Arbeit!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datenbank_verbindung)
	{
		parent::__construct($datenbank_verbindung, 'users');
	}

	protected function beforeDelete(self $self) {
echo 'Er war ein tapferer Soldat... :cry-face:';
	} 
}
```

## Verwaltung der Datenbankverbindung

Wenn Sie diese Bibliothek verwenden, können Sie die Datenbankverbindung auf verschiedene Arten festlegen. Sie können die Verbindung im Konstruktor festlegen, Sie können sie über eine Konfigurationsvariable `$config['connection']` festlegen oder Sie können sie über `setDatabaseConnection()` setzen (ab Version 0.4.1).

```php
$pdo_verbindung = new PDO('sqlite:test.db'); // zum Beispiel
$benutzer = new Benutzer($pdo_verbindung);
// oder
$benutzer = new Benutzer(null, [ 'connection' => $pdo_verbindung ]);
// oder
$benutzer = new Benutzer();
$benutzer->setDatabaseConnection($pdo_verbindung);
```

Wenn Sie die Datenbankverbindung aktualisieren müssen, z. B. wenn Sie ein lang laufendes CLI-Skript ausführen und die Verbindung alle paar Minuten aktualisieren müssen, können Sie die Verbindung mit `$your_record->setDatabaseConnection($pdo_verbindung)` erneut setzen.

## Beiträge

Bitte tun Sie das. :D

### Einrichtung

Wenn Sie beitragen, stellen Sie sicher, dass Sie `composer test-coverage` ausführen, um eine Testabdeckung von 100 % aufrechtzuerhalten (dies ist keine echte Unit-Testabdeckung, sondern eher eine Integrationstestabdeckung).

Stellen Sie außerdem sicher, dass Sie `composer beautify` und `composer phpcs` ausführen, um Fehler in der Formatierung zu beheben.

## Lizenz

MIT