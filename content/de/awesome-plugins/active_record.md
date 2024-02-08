# FlightPHP Active Record 

Ein aktiver Datensatz mappt eine Datenbankentität auf ein PHP-Objekt. Einfach ausgedrückt: Wenn Sie eine Benutzertabelle in Ihrer Datenbank haben, können Sie eine Zeile in dieser Tabelle in eine `User`-Klasse und ein `$user`-Objekt in Ihrem Code umwandeln. Siehe [Grundbeispiel](#grundbeispiel).

## Grundbeispiel

Nehmen wir an, Sie haben die folgende Tabelle:

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
 * Eine ActiveRecord-Klasse ist in der Regel im Singular
 * 
 * Es wird dringend empfohlen, die Eigenschaften der Tabelle hier als Kommentare hinzuzufügen
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class Benutzer erweitert flight\ActiveRecord {
	public function __construct($datenbankverbindung)
	{
		// Sie können es auf diese Weise einstellen
		parent::__construct($datenbankverbindung, 'Benutzer');
		// oder so
		parent::__construct($datenbankverbindung, null, [ 'table' => 'users']);
	}
}
```

Schauen Sie jetzt zu, wie die Magie passiert!

```php
// für sqlite
$datenbankverbindung = new PDO('sqlite:test.db'); // Dies dient nur als Beispiel, Sie würden wahrscheinlich eine echte Datenbankverbindung verwenden

// für mysql
$datenbankverbindung = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'benutzername', 'passwort');

// oder mysqli
$datenbankverbindung = new mysqli('localhost', 'benutzername', 'passwort', 'test_db');
// oder mysqli mit nicht-objektbasierter Erstellung
$datenbankverbindung = mysqli_connect('localhost', 'benutzername', 'passwort', 'test_db');

$benutzer = new Benutzer($datenbankverbindung);
$benutzer->name = 'Bobby Tables';
$benutzer->password = password_hash('ein cooles Passwort');
$benutzer->insert();
// oder $benutzer->save();

echo $benutzer->id; // 1

$benutzer->name = 'Joseph Mamma';
$benutzer->password = password_hash('noch ein cooles Passwort!!!');
$benutzer->insert();
// Hier kann $benutzer->save() nicht verwendet werden, da es denkt, dass es ein Update ist!

echo $benutzer->id; // 2
```

Und es war so einfach, einen neuen Benutzer hinzuzufügen! Da jetzt eine Benutzerzeile in der Datenbank vorhanden ist, wie holen Sie sie heraus?

```php
$benutzer->find(1); // Finde id = 1 in der Datenbank und gebe sie zurück.
echo $benutzer->name; // 'Bobby Tables'
```

Und was ist, wenn Sie alle Benutzer finden möchten?

```php
$benutzer = $benutzer->findAll();
```

Was ist, wenn Sie eine bestimmte Bedingung haben möchten?

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

Dies kann als eigenständige Bibliothek oder mit dem Flight PHP Framework verwendet werden. Es liegt ganz bei Ihnen.

### Eigenständig
Stellen Sie einfach sicher, dass Sie eine PDO-Verbindung an den Konstruktor übergeben.

```php
$pdo_verbindung = new PDO('sqlite:test.db'); // Dies dient nur als Beispiel, Sie würden wahrscheinlich eine echte Datenbankverbindung verwenden

$Benutzer = new Benutzer($pdo_verbindung);
```

### Flight PHP Framework
Wenn Sie das Flight PHP Framework verwenden, können Sie die ActiveRecord-Klasse als Service registrieren (aber Sie müssen es ehrlich gesagt nicht).

```php
Flight::register('benutzer', 'Benutzer', [ $pdo_verbindung ]);

// dann können Sie es in einem Controller, einer Funktion usw. wie folgt verwenden.

Flight::benutzer()->find(1);
```

## API-Referenz
### CRUD-Funktionen

#### `find($id = null) : boolean|ActiveRecord`

Suchen Sie einen Datensatz und weisen Sie ihn dem aktuellen Objekt zu. Wenn Sie eine `$id` irgendwelcher Art übergeben, wird ein Lookup am Primärschlüssel mit diesem Wert durchgeführt. Wenn nichts übergeben wird, wird einfach der erste Datensatz in der Tabelle gefunden.

Zusätzlich können Sie ihm andere Hilfsmethoden übergeben, um Ihre Tabelle abzufragen.

```php
// suchen Sie vorab nach einem Datensatz mit bestimmten Bedingungen
$benutzer->notNull('password')->orderBy('id DESC')->find();

// einen Datensatz mit einer bestimmten ID finden
$id = 123;
$benutzer->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Findet alle Datensätze in der von Ihnen angegebenen Tabelle.

```php
$benutzer->findAll();
```

#### `insert(): boolean|ActiveRecord`

Fügt den aktuellen Datensatz in die Datenbank ein.

```php
$benutzer = new Benutzer($pdo_verbindung);
$benutzer->name = 'demo';
$benutzer->password = md5('demo');
$benutzer->insert();
```

#### `update(): boolean|ActiveRecord`

Aktualisiert den aktuellen Datensatz in der Datenbank.

```php
$benutzer->greaterThan('id', 0)->orderBy('id desc')->find();
$benutzer->email = 'test@example.com';
$benutzer->update();
```

#### `delete(): boolean`

Löscht den aktuellen Datensatz aus der Datenbank.

```php
$benutzer->gt('id', 0)->orderBy('id desc')->find();
$benutzer->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Schmutzige Daten beziehen sich auf die Daten, die in einem Datensatz geändert wurden.

```php
$benutzer->greaterThan('id', 0)->orderBy('id desc')->find();

// Bisher ist nichts "schmutzig".

$benutzer->email = 'test@example.com'; // Jetzt wird die E-Mail als "schmutzig" betrachtet, da sie geändert wurde.
$benutzer->update();
// Jetzt gibt es keine schmutzigen Daten mehr, weil sie aktualisiert und in der Datenbank gespeichert wurden.

$benutzer->password = password_hash()'neuespasswort'); // Jetzt ist dies schmutzig
$benutzer->dirty(); // Wenn nichts übergeben wird, werden alle schmutzigen Einträge gelöscht.
$benutzer->update(); // Nichts wird aktualisiert, da nichts als schmutzig erfasst wurde.

$benutzer->dirty([ 'name' => 'etwas', 'password' => password_hash('ein anderes Passwort') ]);
$benutzer->update(); // sowohl Name als auch Passwort werden aktualisiert.
```

### SQL-Abfragemethoden
#### `select(string $field1 [, string $field2 ... ])`

Sie können nur einige der Spalten in einer Tabelle auswählen, wenn Sie möchten (das ist performanter bei sehr breiten Tabellen mit vielen Spalten)

```php
$benutzer->select('id', 'name')->find();
```

#### `from(string $table)`

Sie können auch eine andere Tabelle auswählen! Warum nicht?!

```php
$benutzer->select('id', 'name')->from('benutzer')->find();
```

#### `join(string $table_name, string $join_condition)`

Sie können sogar mit einer anderen Tabelle in der Datenbank verbinden.

```php
$benutzer->join('kontakte', 'kontakte.user_id = benutzer.id')->find();
```

#### `where(string $where_conditions)`

Sie können einige benutzerdefinierte WHERE-Argumente festlegen (Sie können in dieser WHERE-Anweisung keine Parameter festlegen)

```php
$benutzer->where('id=1 AND name="demo"')->find();
```

**Sicherheitshinweis** - Sie könnten versucht sein, etwas wie `$benutzer->where("id = '{$id}' AND name = '{$name}'")->find();` zu machen. BITTE TUN SIE DAS NICHT!!! Dies ist anfällig für sogenannte SQL-Injection-Angriffe. Es gibt viele Artikel online, bitte googeln Sie "SQL-Injections-Angriffe php" und Sie finden viele Artikel zu diesem Thema. Der richtige Umgang damit in dieser Bibliothek ist anstelle dieser `where()`-Methode etwas Ähnliches wie `$benutzer->eq('id', $id)->eq('name', $name)->find();`

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

Begrenzen Sie die Anzahl der zurückgegebenen Datensätze. Wenn eine zweite Zahl übergeben wird, handelt es sich um Offset und Limit wie in SQL.

```php
$benutzer->orderby('name DESC')->limit(0, 10)->findAll();
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

Wo `field BETWEEN $value UND $value1`

```php
$benutzer->between('id', [1, 2])->find();
```

### Beziehungen
Mit dieser Bibliothek können Sie verschiedene Arten von Beziehungen festlegen. Sie können Beziehungen zwischen Tabellen wie eins-zu-viele und eins-zu-eins festlegen. Dies erfordert ein wenig zusätzliche Einrichtung in der Klasse im Voraus.

Das Setzen des `$relations`-Arrays ist nicht schwer, aber das richtige Syntax zu erraten kann verwirrend sein.

```php
geschütztes Array $relations = [
	// Sie können den Schlüssel nach Belieben benennen. Der Name des ActiveRecord ist wahrscheinlich gut. z.B. benutzer, kontakt, klient
	'was_auch_immer_active-record' => [
		// erforderlich
		self::HAS_ONE, // dies ist der Beziehungstyp

		// erforderlich
		'Einige_Klasse', // Dies ist die "andere" ActiveRecord-Klasse, auf die dies verweisen wird

		// erforderlich
		'lokaler_schlüssel', // Dies ist der lokale Schlüssel, der sich auf den Join bezieht.
		// nur zur Information, dies verbindet auch nur mit dem Primärschlüssel des "anderen" Modells

		// optional
		[ 'eq' => 1, 'select' => 'COUNT(*) as count', 'limit' 5 ], // benutzerdefinierte Methoden, die Sie ausführen möchten. [] wenn Sie keine möchten.

		// optional
		'back_reference_name' // Dies ist, wenn Sie diese Beziehung selbst referenzieren möchten. z.B. $benutzer->kontakt->benutzer;
	];
]
```

```php
class Benutzer erweitert ActiveRecord{
	geschütztes Array $relations = [
		'kontakte' => [ self::HAS_MANY, Kontakt::class, 'benutzer_id' ],
		'kontakt' => [ self::HAS_ONE, Kontakt::class, 'benutzer_id' ],
	];

	public function __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'Benutzer');
	}
}

class Kontakt erweitert ActiveRecord{
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

Nun sind die Verweise eingerichtet, sodass sie sehr einfach verwendet werden können!

```php
$benutzer = new Benutzer($pdo_verbindung);

// finde den neuesten Benutzer.
$benutzer->notNull('id')->orderBy('id desc')->find();

// Holen Sie sich Kontakte, indem Sie die Beziehung verwenden:
foreach($benutzer->kontakte as $kontakt) {
	echo $kontakt->id;
}

// oder wir können auch andersherum gehen.
$kontakt = new Kontakt();

// finde einen Kontakt
$kontakt->find();

// Holen Sie sich den Benutzer mit der Beziehung:
echo $kontakt->benutzer->name; // dies ist der Benutzername
```

Ziemlich cool, oder?

### Anpassen von Daten
Manchmal müssen Sie etwas Einzigartiges an Ihren ActiveRecord anhängen, z. B. eine benutzerdefinierte Berechnung, die möglicherweise einfacher ist, einfach an das Objekt anzuhängen, das dann an eine Vorlage übergeben wird.

#### `setCustomData(string $field, mixed $value)`
Sie hängen die benutzerdefinierten Daten mit der `setCustomData()`-Methode an.
```php
$benutzer->setCustomData('seitenaufrufanzahl', $seitenaufrufanzahl);
```

Und dann verweisen Sie einfach darauf wie auf eine normale Objekteigenschaft.

```php
echo $benutzer->page_view_count;
```

### Ereignisse

Ein weiteres wirklich tolles Feature dieser Bibliothek sind Ereignisse. Ereignisse werden zu bestimmten Zeiten basierend auf bestimmten Methoden, die Sie aufrufen, ausgelöst. Sie sind sehr, sehr hilfreich, um Daten automatisch für Sie einzurichten.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Dies ist wirklich hilfreich, wenn Sie jedes Mal eine Standardverbindung oder so etwas einstellen müssen.

```php
// index.php oder bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// Benutzer.php
class Benutzer erweitert flight\ActiveRecord {

	geschützte Funktion onConstruct(self $self, array &$config) { // vergessen Sie nicht die &-Referenz
		// Sie könnten dies tun, um automatisch die Verbindung festzulegen
		$config['connection'] = Flight::db();
		// oder so
		$self->transformAndPersistConnection(Flight::db());
		
		// Sie können auch den Tabellennamen auf diese Weise festlegen.
		$config['table'] = 'benutzer';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Dies ist wahrscheinlich nur nützlich, wenn Sie jedes Mal eine Abfrageänderung benötigen.

```php
class Benutzer erweitert flight\ActiveRecord {
	
	public Funktion __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'benutzer');
	}

	geschützte Funktion beforeFind(self $self) {
		// immer id >= 0 ausführen, wenn das Ihr Ding ist
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Diese ist wahrscheinlich nützlicher, wenn Sie jedes Mal, wenn dieser DatDatensatz abgerufen wird, eine Logik ausführen müssen. Müssen Sie etwas entschlüsseln? Müssen Sie jedes Mal eine benutzerdefinierte Zählabfrage ausführen (nicht performant, aber egal)?

```php
class Benutzer erweitert flight\ActiveRecord {
	
	public Funktion __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'benutzer');
	}

	geschützte Funktion afterFind(self $self) {
		// Etwas entschlüsseln
		$self->geheim = IhreEntschlüsselungsfunktion($self->geheim, $einSchlüssel);

		// Möglicherweise speichern Sie etwas Benutzerdefiniertes wie eine Abfrage???
		$self->setCustomData('aufrufanzahl', $self->select('COUNT(*) count')->from('benutzeraufrufe')->eq('benutzer_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Dies ist wahrscheinlich nur nützlich, wenn Sie jedes Mal eine Abfrageänderung benötigen.

```php
class Benutzer erweitert flight\ActiveRecord {
	
	public Funktion __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'benutzer');
	}

	geschützte Funktion beforeFindAll(self $self) {
		// immer id >= 0 ausführen, wenn das Ihr Ding ist
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Ähnlich wie `afterFind()`, aber Sie können dies für alle Datensätze tun!

```php
class Benutzer erweitert flight\ActiveRecord {
	
	public Funktion __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'benutzer');
	}

	geschützte Funktion afterFindAll(array $results) {

		foreach($results as $self) {
			// Machen Sie etwas Cooles wie nach afterFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Wirklich hilfreich, wenn Sie jedes Mal einige Standardwerte setzen müssen.

```php
class Benutzer erweitert flight\ActiveRecord {
	
	public Funktion __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'benutzer');
	}

	geschützte Funktion beforeInsert(self $self) {
		// setzen Sie einige sinnvolle Standardeinstellungen
		if(!$self->erstellungsdatum) {
			$self->erstellungsdatum = gmdate('Y-m-d');
		}

		if(!$self->password) {
			$self->password = password_hash((string) microtime(true));
		}
	} 
}
```

#### `afterInsert(ActiveRecord $ActiveRecord)`

Vielleicht gibt es einen Anwendungsfall für die Änderung von Daten, nachdem sie eingefügt wurden?

```php
class Benutzer erweitert flight\ActiveRecord {
	
	public Funktion __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'benutzer');
	}

	geschützte Funktion afterInsert(self $self) {
		// machen Sie, was Sie wollen
		Flight::cache()->set('neueste_einfüge_id', $self->id);
		// oder was auch immer....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Wirklich hilfreich, wenn Sie jedes Mal einige Standardwerte bei einem Update setzen müssen.

```php
class Benutzer erweitert flight\ActiveRecord {
	
	public Funktion __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'benutzer');
	}

	geschützte Funktion beforeInsert(self $self) {
		// setzen Sie einige sinnvolle Standardeinstellungen
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Vielleicht haben Sie einen Anwendungsfall für die Änderung von Daten, nachdem sie aktualisiert wurden?

```php
class Benutzer erweitert flight\ActiveRecord {
	
	public Funktion __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'benutzer');
	}

	geschützte Funktion afterInsert(self $self) {
		// Sie tun, was Sie wollen
		Flight::cache()->set('zuletzt_aktualisierte_benutzer_id', $self->id);
		// oder was auch immer....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Dies ist nützlich, wenn Sie möchten, dass Ereignisse sowohl bei Einfügungen als auch bei Aktualisierungen auftreten. Ich erspare Ihnen die lange Erklärung, aber ich bin sicher, Sie können erraten, was es ist.

```php
class Benutzer erweitert flight\ActiveRecord {
	
	public Funktion __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'benutzer');
	}

	geschützte Funktion beforeSave(self $self) {
		$self->zuletzt_aktualisiert = gmdate('Y-m-d H:i:s');
	} 
}
```

#### `beforeDelete(ActiveRecord $ActiveRecord)/afterDelete(ActiveRecord $ActiveRecord)`

Ich bin mir nicht sicher, was Sie hier tun möchten, aber hier gibt es kein Urteil! Geben Sie Gas!

```php
class Benutzer erweitert flight\ActiveRecord {
	
	public Funktion __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'benutzer');
	}

	geschützte Funktion beforeDelete(self $self) {
		echo 'Er war ein tapferer Soldat... :cry-face:';
	} 
}
```

## Mitwirken

Bitte gern.

### Einrichtung

Wenn Sie beitragen, stellen Sie sicher, dass Sie `composer test-coverage` ausführen, um eine Testabdeckung von 100% beizubehalten (dies ist keine echte Unit-Testabdeckung, sondern eher Integrationstests).

Stellen Sie außerdem sicher, dass Sie `composer beautify` und `composer phpcs` ausführen, um Fehler in der Formatierung zu beheben.

## Lizenz

MIT