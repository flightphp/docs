# Flugt Aktives Aufzeichnen

Eine aktive Aufzeichnung ist die Zuordnung einer Datenbankeinheit zu einem PHP-Objekt. Einfach ausgedrückt, wenn Sie eine Benutzertabelle in Ihrer Datenbank haben, können Sie eine Zeile in dieser Tabelle in eine `Benutzer`-Klasse und ein `$benutzer`-Objekt in Ihrem Code übersetzen. Siehe [Grundbeispiel](#grundbeispiel).

## Grundbeispiel

Angenommen, Sie haben die folgende Tabelle:

```sql
CREATE TABLE benutzer (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	passwort TEXT 
);
```

Jetzt können Sie eine neue Klasse einrichten, um diese Tabelle darzustellen:

```php
/**
 * Eine ActiveRecord-Klasse ist normalerweise im Singular
 * 
 * Es wird dringend empfohlen, die Eigenschaften der Tabelle hier als Kommentare hinzuzufügen
 * 
 * @property int    $id
 * @property string $name
 * @property string $passwort
 */ 
class Benutzer erstreckt Flug\ActiveRecord {
	public function __construct($datenbankverbindung)
	{
		// Sie können es so festlegen
		parent::__construct($datenbankverbindung, 'benutzer');
		// oder so
		parent::__construct($datenbankverbindung, null, [ 'tabelle' => 'benutzer']);
	}
}
```

Schauen Sie jetzt, wie die Magie passiert!

```php
// für sqlite
$datenbankverbindung = new PDO('sqlite:test.db'); // das dient nur als Beispiel, Sie würden wahrscheinlich eine echte Datenbankverbindung verwenden

// für mysql
$datenbankverbindung = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'benutzername', 'passwort');

// oder mysqli
$datenbankverbindung = new mysqli('localhost', 'benutzername', 'passwort', 'test_db');
// oder mysqli mit nicht-objektbasierter Erstellung
$datenbankverbindung = mysqli_connect('localhost', 'benutzername', 'passwort', 'test_db');

$benutzer = new Benutzer($datenbankverbindung);
$benutzer->name = 'Bobby Tables';
$benutzer->passwort = password_hash('ein cooles Passwort');
$benutzer->einfügen();
// oder $benutzer->speichern();

echo $benutzer->id; // 1

$benutzer->name = 'Joseph Mamma';
$benutzer->passwort = password_hash('nochmal ein cooles Passwort!!!');
$benutzer->einfügen();
// $benutzer->speichern() kann hier nicht verwendet werden, sonst denkt es, dass es ein Update ist!

echo $benutzer->id; // 2
```

Und es war so einfach, einen neuen Benutzer hinzuzufügen! Jetzt, da es eine Benutzerzeile in der Datenbank gibt, wie holen Sie sie heraus?

```php
$benutzer->find(1); // Finde id = 1 in der Datenbank und gib sie zurück.
echo $benutzer->name; // 'Bobby Tables'
```

Und wenn Sie alle Benutzer finden möchten?

```php
$benutzer = $benutzer->findAll();
```

Was ist mit einer bestimmten Bedingung?

```php
$benutzer = $benutzer->like('name', '%mamma%')->findAll();
```

Sehen Sie, wie viel Spaß das macht? Lass uns das installieren und loslegen!

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
$pdo_verbindung = new PDO('sqlite:test.db'); // das dient nur als Beispiel, Sie würden wahrscheinlich eine echte Datenbankverbindung verwenden

$Benutzer = new Benutzer($pdo_verbindung);
```

### Flight PHP Framework
Wenn Sie das Flight PHP Framework verwenden, können Sie die ActiveRecord-Klasse als Dienst registrieren (aber Sie müssen es wirklich nicht).

```php
Flight::register('benutzer', 'Benutzer', [ $pdo_verbindung ]);

// dann können Sie es in einem Controller, einer Funktion usw. so verwenden

Flight::benutzer()->find(1);
```

## CRUD-Funktionen

#### `find($id = null) : boolean|ActiveRecord`

Finden Sie einen Datensatz und weisen Sie ihn dem aktuellen Objekt zu. Wenn Sie eine Art `$id` übergeben, wird eine Abfrage zum Primärschlüssel mit diesem Wert ausgeführt. Wenn nichts übergeben wird, wird einfach der erste Datensatz in der Tabelle gefunden.

Zusätzlich können Sie ihm andere Hilfsmethoden übergeben, um Ihre Tabelle abzufragen.

```php
// Finden eines Datensatzes mit bestimmten Bedingungen im Vorfeld
$benutzer->notNull('passwort')->orderBy('id DESC')->find();

// Finden eines Datensatzes nach einer bestimmten ID
$id = 123;
$benutzer->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Findet alle Datensätze in der von Ihnen angegebenen Tabelle.

```php
$benutzer->findAll();
```

#### `einfügen(): boolean|ActiveRecord`

Fügt den aktuellen Datensatz in die Datenbank ein.

```php
$benutzer = new Benutzer($pdo_verbindung);
$benutzer->name = 'demo';
$benutzer->passwort = md5('demo');
$benutzer->einfügen();
```

#### `aktualisieren(): boolean|ActiveRecord`

Aktualisiert den aktuellen Datensatz in der Datenbank.

```php
$benutzer->greaterThan('id', 0)->orderBy('id desc')->find();
$benutzer->email = 'test@example.com';
$benutzer->aktualisieren();
```

#### `löschen(): boolean`

Löscht den aktuellen Datensatz aus der Datenbank.

```php
$benutzer->gt('id', 0)->orderBy('id desc')->find();
$benutzer->löschen();
```

Sie können auch mehrere Datensätze löschen, indem Sie eine Suche im Vorfeld durchführen.

```php
$benutzer->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Schmutzige Daten beziehen sich auf Daten, die in einem Datensatz geändert wurden.

```php
$benutzer->greaterThan('id', 0)->orderBy('id desc')->find();

// Zu diesem Zeitpunkt gibt es nichts "schmutziges".

$benutzer->email = 'test@example.com'; // jetzt wird die E-Mail als "schmutzig" betrachtet, da sie geändert wurde.
$benutzer->aktualisieren();
// jetzt gibt es keine schmutzigen Daten, da sie aktualisiert und in der Datenbank gespeichert wurden

$benutzer->passwort = password_hash()'neues passwort'); // jetzt ist das schmutzig
$benutzer->schmutzig(); // wenn Sie nichts übergeben, werden alle schmutzigen Einträge gelöscht.
$benutzer->aktualisieren(); // es wird nichts aktualisiert, da nichts als schmutzig erfasst wurde.

$benutzer->schmutzig([ 'name' => 'etwas', 'passwort' => password_hash('ein anderes passwort') ]);
$benutzer->aktualisieren(); // sowohl Name als auch Passwort werden aktualisiert.
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Setzt den aktuellen Datensatz auf seinen ursprünglichen Zustand zurück. Dies ist wirklich nützlich bei Schleifenverhalten.
Wenn Sie `true` übergeben, werden auch die Abfragedaten zurückgesetzt, die verwendet wurden, um das aktuelle Objekt zu finden (Standardverhalten).

```php
$benutzer = $benutzer->greaterThan('id', 0)->orderBy('id desc')->find();
$benutzer_firma = new BenutzerFirma($pdo_verbindung);

foreach($benutzer as $benutzer) {
	$benutzer_firma->reset(); // mit einem sauberen Blatt beginnen
	$benutzer_firma->benutzer_id = $benutzer->id;
	$benutzer_firma->firmen_id = $some_company_id;
	$benutzer_firma->einfügen();
}
```

## SQL-Abfragemethoden
#### `select(string $field1 [, string $field2 ... ])`

Sie können nur einige der Spalten in einer Tabelle auswählen, wenn Sie möchten (es ist leistungsfähiger bei sehr breiten Tabellen mit vielen Spalten)

```php
$benutzer->select('id', 'name')->find();
```

#### `from(string $table)`

Sie können technisch gesehen auch eine andere Tabelle wählen! Warum zum Teufel auch nicht?!

```php
$benutzer->select('id', 'name')->from('benutzer')->find();
```

#### `join(string $table_name, string $join_condition)`

Sie können sogar zu einer anderen Tabelle in der Datenbank gehen.

```php
$benutzer->join('kontakte', 'kontakte.benutzer_id = benutzer.id')->find();
```

#### `where(string $where_conditions)`

Sie können benutzerdefinierte WHERE-Argumente festlegen (Sie können keine Parameter in dieser WHERE-Anweisung festlegen)

```php
$benutzer->where('id=1 AND name="demo"')->find();
```

**Sicherheitshinweis** - Sie könnten versucht sein, etwas wie `$benutzer->where("id = '{$id}' AND name = '{$name}'")->find()` zu tun. Bitte TUN SIE DIES NICHT!!! Dies ist anfällig für sogenannte SQL-Injektionsangriffe. Es gibt viele Artikel online, bitte googeln Sie "SQL-Injektionsangriffe php" und Sie finden viele Artikel zu diesem Thema. Der richtige Umgang damit mit dieser Bibliothek besteht darin, anstelle dieser `where()`-Methode etwas wie `$benutzer->eq('id', $id)->eq('name', $name)->find()` zu tun.

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

Begrenzen Sie die Anzahl der zurückgegebenen Datensätze. Wenn ein zweiter Int übergeben wird, wird er wie in SQL versetzt, um ein Limit zu setzen.

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

## Beziehungen
Sie können mit dieser Bibliothek mehrere Arten von Beziehungen festlegen. Sie können Ein-zu-viele- und Ein-zu-eine Beziehungen zwischen Tabellen festlegen. Dies erfordert jedoch etwas zusätzliche Einrichtung in der Klasse im Voraus.

Das Festlegen des `$relations`-Arrays ist nicht schwer, aber das richtige Syntax zu erraten kann verwirrend sein.

```php
geschütztes Array $relations = [
	// Sie können den Schlüssel beliebig nennen. Der Name der ActiveRecord ist wahrscheinlich gut. Beispiel: benutzer, kontakt, kunde
	'benutzer' => [
		// erforderlich
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // das ist der Typ der Beziehung

		// erforderlich
		'Some_Class', // das ist die "andere" ActiveRecord-Klasse, auf die verwiesen wird

		// erforderlich
		// je nach Beziehungstyp
		// self::HAS_ONE = der Fremdschlüssel, der das Join referenziert
		// self::HAS_MANY = der Fremdschlüssel, der das Join referenziert
		// self::BELONGS_TO = der lokale Schlüssel, der das Join referenziert
		'lokaler_oder_fremder_schlüssel',
		// nur zur Information, das wird auch nur an den Primärschlüssel des "anderen" Modells angehängt

		// optional
		[ 'eq' => [ 'benutzer_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // zusätzliche Bedingungen, die Sie bei der Verbindung der Beziehung möchten
		// $datensatz->eq('benutzer_id', 5)->select('COUNT(*) as count')->limit(5))

		// optional
		'back_reference_name' // das ist, wenn Sie diese Beziehung rückwärts auf sich selbst beziehen möchten, z.B. $benutzer->kontakt->benutzer;
	];
]
```

```php
class Benutzer erstreckt ActiveRecord{
	geschütztes Array $relations = [
		'kontakte' => [ self::HAS_MANY, Kontakt::class, 'benutzer_id' ],
		'kontakt' => [ self::HAS_ONE, Kontakt::class, 'benutzer_id' ],
	];

	public function __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'benutzer');
	}
}

class Kontakt erstreckt ActiveRecord{
	geschütztes Array $relations = [
		'benutzer' => [ self::BELONGS_TO, Benutzer::class, 'benutzer_id' ],
		'benutzer_mit_backref' => [ self::BELONGS_TO, Benutzer::class, 'benutzer_id', [], 'kontakt' ],
	];
	public function __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'kontakte');
	}
}
```

Jetzt haben wir die Verweise eingerichtet, so dass wir sie sehr einfach verwenden können!

```php
$benutzer = new Benutzer($pdo_verbindung);

// den neuesten benutzer finden.
$benutzer->notNull('id')->orderBy('id desc')->find();

// kontakte finden, indem Sie die Beziehung verwenden:
foreach($benutzer->kontakte as $kontakt) {
	echo $kontakt->id;
}

// oder wir können den anderen Weg gehen.
$kontakt = new Kontakt();

// einen Kontakt finden
$kontakt->find();

// benutzer finden, indem Sie die Beziehung verwenden:
echo $kontakt->benutzer->name; // das ist der Benutzername
```

Ziemlich cool, oder?

## Festlegen benutzerdefinierter Daten
Manchmal müssen Sie Ihrem ActiveRecord etwas Einzigartiges anhängen, z.B. eine benutzerdefinierte Berechnung, die möglicherweise einfacher wäre, einfach an das Objekt anzuhängen und dann z.B. an ein Template übergeben zu werden.

#### `setCustomData(string $field, mixed $value)`
Sie hängen die benutzerdefinierten Daten mit der `setCustomData()`-Methode an.
```php
$benutzer->setCustomData('seitenaufrufszahl', $seitenaufrufszahl);
```

Und dann verweisen Sie einfach darauf wie auf eine normale Objekteigenschaft.

```php
echo $benutzer->seitenaufrufszahl;
```

## Ereignisse

Ein weiteres super geniales Feature dieser# Flugt Aktives Aufzeichnen

Eine aktive Aufzeichnung ist die Zuordnung einer Datenbankeinheit zu einem PHP-Objekt. Einfach ausgedrückt, wenn Sie eine Benutzertabelle in Ihrer Datenbank haben, können Sie eine Zeile in dieser Tabelle in eine `Benutzer`-Klasse und ein `$benutzer`-Objekt in Ihrem Code übersetzen. Siehe [Grundbeispiel](#grundbeispiel).

## Grundbeispiel

Angenommen, Sie haben die folgende Tabelle:

```sql
CREATE TABLE benutzer (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	passwort TEXT 
);
```

Jetzt können Sie eine neue Klasse einrichten, um diese Tabelle darzustellen:

```php
/**
 * Eine ActiveRecord-Klasse ist normalerweise im Singular
 * 
 * Es wird dringend empfohlen, die Eigenschaften der Tabelle hier als Kommentare hinzuzufügen
 * 
 * @property int    $id
 * @property string $name
 * @property string $passwort
 */ 
class Benutzer erstreckt Flug\ActiveRecord {
	public function __construct($datenbankverbindung)
	{
		// Sie können es so festlegen
		parent::__construct($datenbankverbindung, 'benutzer');
		// oder so
		parent::__construct($datenbankverbindung, null, [ 'tabelle' => 'benutzer']);
	}
}
```

Schauen Sie jetzt, wie die Magie passiert!

```php
// für sqlite
$datenbankverbindung = new PDO('sqlite:test.db'); // das dient nur als Beispiel, Sie würden wahrscheinlich eine echte Datenbankverbindung verwenden

// für mysql
$datenbankverbindung = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'benutzername', 'passwort');

// oder mysqli
$datenbankverbindung = new mysqli('localhost', 'benutzername', 'passwort', 'test_db');
// oder mysqli mit nicht-objektbasierter Erstellung
$datenbankverbindung = mysqli_connect('localhost', 'benutzername', 'passwort', 'test_db');

$benutzer = new Benutzer($datenbankverbindung);
$benutzer->name = 'Bobby Tables';
$benutzer->passwort = password_hash('ein cooles Passwort');
$benutzer->einfügen();
// oder $benutzer->speichern();

echo $benutzer->id; // 1

$benutzer->name = 'Joseph Mamma';
$benutzer->passwort = password_hash('nochmal ein cooles Passwort!!!');
$benutzer->einfügen();
// $benutzer->speichern() kann hier nicht verwendet werden, sonst denkt es, dass es ein Update ist!

echo $benutzer->id; // 2
```

Und es war so einfach, einen neuen Benutzer hinzuzufügen! Jetzt, da es eine Benutzerzeile in der Datenbank gibt, wie holen Sie sie heraus?

```php
$benutzer->find(1); // Finde id = 1 in der Datenbank und gib sie zurück.
echo $benutzer->name; // 'Bobby Tables'
```

Und wenn Sie alle Benutzer finden möchten?

```php
$benutzer = $benutzer->findAll();
```

Was ist mit einer bestimmten Bedingung?

```php
$benutzer = $benutzer->like('name', '%mamma%')->findAll();
```

Sehen Sie, wie viel Spaß das macht? Lass uns das installieren und loslegen!

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
$pdo_verbindung = new PDO('sqlite:test.db'); // das dient nur als Beispiel, Sie würden wahrscheinlich eine echte Datenbankverbindung verwenden

$Benutzer = new Benutzer($pdo_verbindung);
```

### Flight PHP Framework
Wenn Sie das Flight PHP Framework verwenden, können Sie die ActiveRecord-Klasse als Dienst registrieren (aber Sie müssen es wirklich nicht).

```php
Flight::register('benutzer', 'Benutzer', [ $pdo_verbindung ]);

// dann können Sie es in einem Controller, einer Funktion usw. so verwenden

Flight::benutzer()->find(1);
```

## CRUD-Funktionen

#### `find($id = null) : boolean|ActiveRecord`

Finden Sie einen Datensatz und weisen Sie ihn dem aktuellen Objekt zu. Wenn Sie eine Art `$id` übergeben, wird eine Abfrage zum Primärschlüssel mit diesem Wert ausgeführt. Wenn nichts übergeben wird, wird einfach der erste Datensatz in der Tabelle gefunden.

Zusätzlich können Sie ihm andere Hilfsmethoden übergeben, um Ihre Tabelle abzufragen.

```php
// Finden eines Datensatzes mit bestimmten Bedingungen im Vorfeld
$benutzer->notNull('passwort')->orderBy('id DESC')->find();

// Finden eines Datensatzes nach einer bestimmten ID
$id = 123;
$benutzer->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Findet alle Datensätze in der von Ihnen angegebenen Tabelle.

```php
$benutzer->findAll();
```

#### `einfügen(): boolean|ActiveRecord`

Fügt den aktuellen Datensatz in die Datenbank ein.

```php
$benutzer = new Benutzer($pdo_verbindung);
$benutzer->name = 'demo';
$benutzer->passwort = md5('demo');
$benutzer->einfügen();
```

#### `aktualisieren(): boolean|ActiveRecord`

Aktualisiert den aktuellen Datensatz in der Datenbank.

```php
$benutzer->greaterThan('id', 0)->orderBy('id desc')->find();
$benutzer->email = 'test@example.com';
$benutzer->aktualisieren();
```

#### `löschen(): boolean`

Löscht den aktuellen Datensatz aus der Datenbank.

```php
$benutzer->gt('id', 0)->orderBy('id desc')->find();
$benutzer->löschen();
```

Sie können auch mehrere Datensätze löschen, indem Sie eine Suche im Vorfeld durchführen.

```php
$benutzer->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Schmutzige Daten beziehen sich auf Daten, die in einem Datensatz geändert wurden.

```php
$benutzer->greaterThan('id', 0)->orderBy('id desc')->find();

// Zu diesem Zeitpunkt gibt es nichts "schmutziges".

$benutzer->email = 'test@example.com'; // jetzt wird die E-Mail als "schmutzig" betrachtet, da sie geändert wurde.
$benutzer->aktualisieren();
// jetzt gibt es keine schmutzigen Daten, da sie aktualisiert und in der Datenbank gespeichert wurden

$benutzer->passwort = password_hash()'neues passwort'); // jetzt ist das schmutzig
$benutzer->schmutzig(); // wenn Sie nichts übergeben, werden alle schmutzigen Einträge gelöscht.
$benutzer->aktualisieren(); // es wird nichts aktualisiert, da nichts als schmutzig erfasst wurde.

$benutzer->schmutzig([ 'name' => 'etwas', 'passwort' => password_hash('ein anderes passwort') ]);
$benutzer->aktualisieren(); // sowohl Name als auch Passwort werden aktualisiert.
```

#### `reset(bool $include_query_data = true): ActiveRecord`

Setzt den aktuellen Datensatz auf seinen ursprünglichen Zustand zurück. Dies ist wirklich nützlich bei Schleifenverhalten.
Wenn Sie `true` übergeben, werden auch die Abfragedaten zurückgesetzt, die verwendet wurden, um das aktuelle Objekt zu finden (Standardverhalten).

```php
$benutzer = $benutzer->greaterThan('id', 0)->orderBy('id desc')->find();
$benutzer_firma = new BenutzerFirma($pdo_verbindung);

foreach($benutzer as $benutzer) {
	$benutzer_firma->reset(); // mit einem sauberen Blatt beginnen
	$benutzer_firma->benutzer_id = $benutzer->id;
	$benutzer_firma->firmen_id = $some_company_id;
	$benutzer_firma->einfügen();
}
```

## SQL-Abfragemethoden
#### `select(string $field1 [, string $field2 ... ])`

Sie können nur einige der Spalten in einer Tabelle auswählen, wenn Sie möchten (es ist leistungsfähiger bei sehr breiten Tabellen mit vielen Spalten)

```php
$benutzer->select('id', 'name')->find();
```

#### `from(string $table)`

Sie können technisch gesehen auch eine andere Tabelle wählen! Warum zum Teufel auch nicht?!

```php
$benutzer->select('id', 'name')->from('benutzer')->find();
```

#### `join(string $table_name, string $join_condition)`

Sie können sogar zu einer anderen Tabelle in der Datenbank gehen.

```php
$benutzer->join('kontakte', 'kontakte.benutzer_id = benutzer.id')->find();
```

#### `where(string $where_conditions)`

Sie können benutzerdefinierte WHERE-Argumente festlegen (Sie können keine Parameter in dieser WHERE-Anweisung festlegen)

```php
$benutzer->where('id=1 AND name="demo"')->find();
```

**Sicherheitshinweis** - Sie könnten versucht sein, etwas wie `$benutzer->where("id = '{$id}' AND name = '{$name}'")->find()` zu tun. Bitte TUN SIE DIES NICHT!!! Dies ist anfällig für sogenannte SQL-Injektionsangriffe. Es gibt viele Artikel online, bitte googeln Sie "SQL-Injektionsangriffe php" und Sie finden viele Artikel zu diesem Thema. Der richtige Umgang damit mit dieser Bibliothek besteht darin, anstelle dieser `where()`-Methode etwas wie `$benutzer->eq('id', $id)->eq('name', $name)->find()` zu tun.

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

Begrenzen Sie die Anzahl der zurückgegebenen Datensätze. Wenn ein zweiter Int übergeben wird, wird er wie in SQL versetzt, um ein Limit zu setzen.

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

## Beziehungen
Sie können mit dieser Bibliothek mehrere Arten von Beziehungen festlegen. Sie können Ein-zu-viele- und Ein-zu-eine Beziehungen zwischen Tabellen festlegen. Dies erfordert jedoch etwas zusätzliche Einrichtung in der Klasse im Voraus.

Das Festlegen des `$relations`-Arrays ist nicht schwer, aber das richtige Syntax zu erraten kann verwirrend sein.

```php
geschütztes Array $relations = [
	// Sie können den Schlüssel beliebig nennen. Der Name der ActiveRecord ist wahrscheinlich gut. Beispiel: benutzer, kontakt, kunde
	'benutzer' => [
		// erforderlich
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // das ist der Typ der Beziehung

		// erforderlich
		'Some_Class', // das ist die "andere" ActiveRecord-Klasse, auf die verwiesen wird

		// erforderlich
		// je nach Beziehungstyp
		// self::HAS_ONE = der Fremdschlüssel, der das Join referenziert
		// self::HAS_MANY = der Fremdschlüssel, der das Join referenziert
		// self::BELONGS_TO = der lokale Schlüssel, der das Join referenziert
		'lokaler_oder_fremder_schlüssel',
		// nur zur Information, das wird auch nur an den Primärschlüssel des "anderen" Modells angehängt

		// optional
		[ 'eq' => [ 'benutzer_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // zusätzliche Bedingungen, die Sie bei der Verbindung der Beziehung möchten
		// $datensatz->eq('benutzer_id', 5)->select('COUNT(*) as count')->limit(5))

		// optional
		'back_reference_name' // das ist, wenn Sie diese Beziehung rückwärts auf sich selbst beziehen möchten, z.B. $benutzer->kontakt->benutzer;
	];
]
```

```php
class Benutzer erstreckt ActiveRecord{
	geschütztes Array $relations = [
		'kontakte' => [ self::HAS_MANY, Kontakt::class, 'benutzer_id' ],
		'kontakt' => [ self::HAS_ONE, Kontakt::class, 'benutzer_id' ],
	];

	public function __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'benutzer');
	}
}

class Kontakt erstreckt ActiveRecord{
	geschütztes Array $relations = [
		'benutzer' => [ self::BELONGS_TO, Benutzer::class, 'benutzer_id' ],
		'benutzer_mit_backref' => [ self::BELONGS_TO, Benutzer::class, 'benutzer_id', [], 'kontakt' ],
	];
	public function __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'kontakte');
	}
}
```

Jetzt haben wir die Verweise eingerichtet, so dass wir sie sehr einfach verwenden können!

```php
$benutzer = new Benutzer($pdo_verbindung);

// den neuesten benutzer finden.
$benutzer->notNull('id')->orderBy('id desc')->find();

// kontakte finden, indem Sie die Beziehung verwenden:
foreach($benutzer->kontakte as $kontakt) {
	echo $kontakt->id;
}

// oder wir können den anderen Weg gehen.
$kontakt = new Kontakt();

// einen Kontakt finden
$kontakt->find();

// benutzer finden, indem Sie die Beziehung verwenden:
echo $kontakt->benutzer->name; // das ist der Benutzername
```

Ziemlich cool, oder?

## Festlegen benutzerdefinierter Daten
Manchmal müssen Sie Ihrem ActiveRecord etwas Einzigartiges anhängen, z.B. eine benutzerdefinierte Berechnung, die möglicherweise einfacher wäre, einfach an das Objekt anzuhängen und dann an ein Template übergeben zu werden.

#### `setCustomData(string $field, mixed $value)`
Sie hängen die benutzerdefinierten Daten mit der `setCustomData()`-Methode an.
```php
$benutzer->setCustomData('seitenaufrufszahl', $seitenaufrufszahl);
```

Und dann verweisen Sie einfach darauf wie auf eine normale Objekteigenschaft.

```php
echo $benutzer->seitenaufrufszahl;
```

## Ereignisse

Ein weiteres super geniales Feature dieser Bibliothekist die Möglichkeit zur Verwendung von Ereignissen. Ereignisse werden zu bestimmten Zeiten basierend auf bestimmten Methoden, die Sie aufrufen, ausgelöst. Sie sind sehr hilfreich, um Daten automatisch für Sie einzurichten.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Dies ist sehr hilfreich, wenn Sie beispielsweise eine Standardverbindung einrichten müssen.

```php
// index.php oder bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	protected function onConstruct(self $self, array &$config) { // Don't forget the & reference
		// Sie könnten das tun, um die Verbindung automatisch zu setzen
		$config['connection'] = Flight::db();
		// oder so
		$self->transformAndPersistConnection(Flight::db());
		
		// Sie können auf diese Weise auch den Tabellennamen setzen.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Dies ist wahrscheinlich nur nützlich, wenn Sie jedes Mal eine Abfrageänderung benötigen.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'users');
	}

	protected function beforeFind(self $self) {
		// Immer id >= 0 ausführen, wenn das Ihnen gefällt
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Dies ist wahrscheinlich nützlicher, wenn Sie jedes Mal eine Logik ausführen müssen, wenn dieser Datensatz abgerufen wird. Müssen Sie etwas entschlüsseln? Müssen Sie eine benutzerdefinierte Abfrage jedes Mal durchführen (nicht performant, aber egal)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'users');
	}

	protected function afterFind(self $self) {
		// Etwas entschlüsseln
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// Vielleicht speichern Sie etwas Benutzerdefiniertes wie eine Abfrage???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Dies ist wahrscheinlich nur nützlich, wenn Sie jedes Mal eine Abfrageänderung benötigen.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'users');
	}

	protected function beforeFindAll(self $self) {
		// Immer id >= 0 ausführen, wenn das Ihnen gefällt
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Ähnlich wie `afterFind()`, aber Sie können es auf alle Datensätze anwenden!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'users');
	}

	protected function afterFindAll(array $results) {

		foreach($results as $self) {
			// Mach etwas Cooles wie nach afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Wirklich hilfreich, wenn Sie jedes Mal einige Standardwerte haben müssen.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'users');
	}

	protected function beforeInsert(self $self) {
		// Setze einige feste Standardeinstellungen
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
	
	public function __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'users');
	}

	protected function afterInsert(self $self) {
		// machen Sie, was Sie wollen
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// oder was auch immer....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Wirklich hilfreich, wenn Sie jedes Mal einige Standardwerte für ein Update haben müssen.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'users');
	}

	protected function beforeInsert(self $self) {
		// Setze einige feste Standardeinstellungen
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Vielleicht haben Sie einen Anwendungsfall zum Ändern von Daten nach einem Update?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'users');
	}

	protected function afterInsert(self $self) {
		// machen Sie, was Sie wollen
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// oder was auch immer....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Dies ist nützlich, wenn Sie Ereignisse möchten, die sowohl bei Einfügungen als auch bei Updates ausgeführt werden. Ich erspare Ihnen die lange Erklärung, aber ich bin sicher, Sie können erraten, was es ist.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'users');
	}

	protected function beforeSave(self $self) {
		$self->last_updated = gmdate('Y-m-d H:i:s');
	} 
}
```

#### `beforeDelete(ActiveRecord $ActiveRecord)/afterDelete(ActiveRecord $ActiveRecord)`

Ich bin mir nicht sicher, was Sie hier tun möchten, aber keine Urteile hier! Go for it!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'users');
	}

	protected function beforeDelete(self $self) {
		echo 'Er war ein tapferer Soldat... :cry-face:';
	} 
}
```

## Beitrag

Bitte gerne.

## Einrichtung

Wenn Sie beitragen, stellen Sie sicher, dass Sie `composer test-coverage` ausführen, um eine Testabdeckung von 100% zu erreichen (dies ist keine echte Testabdeckung, eher wie Integrationstests).

Stellen Sie auch sicher, dass Sie `composer beautify` und `composer phpcs` ausführen, um etwaige Formatfehler zu beheben.

## Lizenz

MIT