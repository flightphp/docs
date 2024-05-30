# Flight Aktivdatensatz

Ein aktivdatensatz ordnet eine Datenbankeinheit einem PHP-Objekt zu. Einfach ausgedrückt, wenn Sie eine Benutzertabelle in Ihrer Datenbank haben, können Sie eine Zeile in dieser Tabelle in eine `User`-Klasse und ein `$user`-Objekt in Ihrem Code umwandeln. Siehe [grundlegendes Beispiel](#grundlegendes-beispiel).

## Grundlegendes Beispiel

Angenommen, Sie haben die folgende Tabelle:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Sie können jetzt eine neue Klasse einrichten, um diese Tabelle zu repräsentieren:

```php
/**
 * Eine Aktivdatensatzklasse ist in der Regel im Singular
 * 
 * Es wird dringend empfohlen, die Eigenschaften der Tabelle als Kommentare hier hinzuzufügen
 * 
 * @property int    $id
 * @property string $name
 * @property string $password
 */ 
class Benutzer erstreckt sich flight\ActiveRecord {
	public Funktion __construct($datenbank_verbindung)
	{
		// Sie können es auf diese Weise einstellen
		Elternteil::__construct($datenbank_verbindung, 'users');
		// oder auf diese Weise
		Elternteil::__construct($datenbank_verbindung, null, [ 'table' => 'users']);
	}
}
```

Nun lass die Magie passieren!

```php
// für sqlite
$database_verbindung = neue PDO('sqlite:test.db'); // das dient nur als Beispiel, wahrscheinlich würden Sie eine echte Datenbankverbindung verwenden

// für mysql
$database_verbindung = neue PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'benutzername', 'passwort');

// oder mysqli
$database_verbindung = neue mysqli('localhost', 'benutzername', 'passwort', 'test_db');
// oder mysqli mit nicht objektbasierter Erstellung
$database_verbindung = mysqli_connect('localhost', 'benutzername', 'passwort', 'test_db');

$benutzer = neue Benutzer($database_verbindung);
$benutzer->name = 'Bobby Tables';
$benutzer->password = password_hash('ein cooles Passwort');
$benutzer->einfügen();
// oder $benutzer->speichern();

echo $benutzer->id; // 1

$benutzer->name = 'Joseph Mamma';
$benutzer->password = password_hash('nochmal ein cooles Passwort!!!');
$benutzer->einfügen();
// Hier kann $benutzer->speichern() nicht verwendet werden, da es denkt, es handle sich um ein Update!

echo $benutzer->id; // 2
```

Und es war so einfach, einen neuen Benutzer hinzuzufügen! Jetzt, da es eine Benutzerzeile in der Datenbank gibt, wie holen Sie sie heraus?

```php
$benutzer->find(1); // Suche nach id = 1 in der Datenbank und gib sie zurück.
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

Siehst du, wie viel Spaß das macht? Lass uns es installieren und loslegen!

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
$pdo_verbindung = neue PDO('sqlite:test.db'); // das ist nur ein Beispiel, Sie würden wahrscheinlich eine echte Datenbankverbindung verwenden

$Benutzer = neue Benutzer($pdo_verbindung);
```

### Flight PHP Framework
Wenn Sie das Flight PHP Framework verwenden, können Sie die ActiveRecord-Klasse als Dienst registrieren (müssen Sie jedoch ehrlich gesagt nicht).

```php
Flight::register('benutzer', 'Benutzer', [ $pdo_verbindung ]);

// dann können Sie es in einem Controller, einer Funktion usw. so verwenden

Flight::benutzer()->find(1);
```

## CRUD-Funktionen

#### `find($id = null) : boolean|ActiveRecord`

Suchen Sie einen Datensatz und weisen Sie ihn dem aktuellen Objekt zu. Wenn Sie eine `$id` von irgendeiner Art übergeben, wird eine Abfrage auf dem Primärschlüssel mit diesem Wert durchgeführt. Wenn nichts übergeben wird, wird einfach der erste Datensatz in der Tabelle gefunden.

Zusätzlich können Sie ihm andere Hilfsmethoden übergeben, um Ihre Tabelle abzufragen.

```php
// suchen Sie einen Datensatz mit einigen Bedingungen im Voraus
$benutzer->notNull('password')->orderBy('id DESC')->find();

// finden Sie einen Datensatz mit einer bestimmten id
$id = 123;
$benutzer->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Finden Sie alle Datensätze in der von Ihnen angegebenen Tabelle.

```php
$benutzer->findAll();
```

#### `isHydrated(): boolean` (v0.4.0)

Gibt `true` zurück, wenn der aktuelle Datensatz geholt wurde (aus der Datenbank abgerufen).

```php
$benutzer->find(1);
// wenn ein Datensatz mit Daten gefunden wird...
$benutzer->isHydrated(); // true
```

#### `insert(): boolean|ActiveRecord`

Fügt den aktuellen Datensatz in die Datenbank ein.

```php
$benutzer = neue Benutzer($pdo_verbindung);
$benutzer->name = 'demo';
$benutzer->password = md5('demo');
$benutzer->einfügen();
```

##### Textbasierte Primärschlüssel

Wenn Sie einen textbasierten Primärschlüssel haben (wie z.B. eine UUID), können Sie den Primärschlüsselwert vor dem Einfügen auf zwei Arten festlegen.

```php
$benutzer = neue Benutzer($pdo_verbindung, [ 'primaryKey' => 'uuid' ]);
$benutzer->uuid = 'some-uuid';
$benutzer->name = 'demo';
$benutzer->password = md5('demo');
$benutzer->einfügen(); // oder $benutzer->speichern();
```

oder Sie können den Primärschlüssel automatisch für Sie generieren lassen durch Ereignisse.

```php
class Benutzer erstreckt flight\ActiveRecord {
	public Funktion __construct($datenbank_verbindung)
	{
		Elternteil::__construct($datenbank_verbindung, 'users', [ 'primaryKey' => 'uuid' ]);
		// Sie können den Primärschlüssel auch auf diese Weise setzen, anstatt des obigen Arrays.
		$this->primaryKey = 'uuid';
	}

	protected Funktion beforeInsert(self $self) {
		$self->uuid = uniqid(); // oder wie auch immer Sie Ihre eindeutigen IDs generieren müssen
	}
}
```

Wenn Sie den Primärschlüssel vor dem Einfügen nicht setzen, wird der `rowid` eingestellt und die Datenbank generiert ihn für Sie, aber er wird nicht persistiert, weil dieses Feld in Ihrer Tabelle möglicherweise nicht existiert. Deshalb wird empfohlen, das Ereignis zu verwenden, um dies automatisch für Sie zu übernehmen.

#### `update(): boolean|ActiveRecord`

Aktualisiert den aktuellen Datensatz in der Datenbank.

```php
$benutzer->greaterThan('id', 0)->orderBy('id desc')->find();
$benutzer->email = 'test@example.com';
$benutzer->update();
```

#### `save(): boolean|ActiveRecord`

Fügt den aktuellen Datensatz in die Datenbank ein oder aktualisiert ihn. Wenn der Datensatz eine id hat, wird er aktualisiert, sonst wird er eingefügt.

```php
$benutzer = neue Benutzer($pdo_verbindung);
$benutzer->name = 'demo';
$benutzer->password = md5('demo');
$benutzer->save();
```

**Hinweis:** Wenn Sie Beziehungen in der Klasse definiert haben, werden diese Beziehungen rekursiv gespeichert, wenn sie definiert, instanziert und schmutzige Daten zum Aktualisieren haben. (v0.4.0 und höher)

#### `delete(): boolean`

Löscht den aktuellen Datensatz aus der Datenbank.

```php
$benutzer->gt('id', 0)->orderBy('id desc')->find();
$benutzer->delete();
```

Sie können auch mehrere Datensätze löschen, indem Sie zuerst eine Abfrage ausführen.

```php
$benutzer->like('name', 'Bob%')->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Schmutzige Daten beziehen sich auf die Daten, die in einem Datensatz geändert wurden.

```php
$benutzer->greaterThan('id', 0)->orderBy('id desc')->find();

// bisher ist nichts "schmutzig".

$benutzer->email = 'test@example.com'; // jetzt wird die E-Mail-Adresse als "schmutzig" betrachtet, da sie geändert wurde.
$benutzer->update();
// jetzt gibt es keine schmutzigen Daten mehr, weil sie aktualisiert und in der Datenbank gespeichert wurden

$benutzer->password = password_hash()'neuespasswort'); // jetzt ist dies schmutzig
$benutzer->schmutzig(); // Wenn nichts übergeben wird, werden alle schmutzigen Einträge gelöscht.
$benutzer->update(); // nichts wird aktualisiert, da nichts als schmutzig erfasst wurde.

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

Setzt den aktuellen Datensatz auf seinen Ausgangszustand zurück. Dies ist sehr nützlich bei Schleifenverhalten.
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

Nachdem Sie eine Methode `find()`, `findAll()`, `insert()`, `update()` oder `save()` ausgeführt haben, können Sie den generierten SQL abrufen und ihn zu Debugging-Zwecken verwenden.

## SQL-Abfragemethoden
#### `select(string $field1 [, string $field2 ... ])`

Sie können nur einige der Spalten in einer Tabelle auswählen, wenn Sie möchten (es ist leistungsfähiger bei wirklich breiten Tabellen mit vielen Spalten)

```php
$benutzer->select('id', 'name')->find();
```

#### `from(string $table)`

Sie können technisch auch eine andere Tabelle wählen! Warum zum Teufel nicht?!

```php
$benutzer->select('id', 'name')->from('benutzer')->find();
```

#### `join(string $table_name, string $join_condition)`

Sie können auch zu einer anderen Tabelle in der Datenbank verknüpfen.

```php
$benutzer->join('kontakte', 'kontakte.user_id = benutzer.id')->find();
```

#### `where(string $where_conditions)`

Sie können benutzerdefinierte WHERE-Argumente festlegen (Sie können in dieser WHERE-Anweisung keine Parameter festlegen)

```php
$benutzer->where('id=1 UND name="demo"')->find();
```

**Sicherheitshinweis** - Sie könnten versucht sein, etwas wie `$benutzer->where("id = '{$id}' UND name = '{$name}'")->find();` zu tun. Bitte TUN SIE DAS NICHT!!! Dies ist anfällig für sogenannte SQL-Injection-Angriffe. Es gibt viele Artikel online, bitte googeln Sie "SQL-Injection-Angriffe php" und Sie werden viele Artikel zu diesem Thema finden. Der richtige Umgang damit in dieser Bibliothek besteht darin, anstelle dieser `where()`-Methode etwas wie `$benutzer->eq('id', $id)->eq('name', $name)->find();` zu verwenden. Wenn Sie dies unbedingt tun müssen, verfügt die `PDO`-Bibliothek über `$pdo->quote($var)`, um dies für Sie zu escapen. Erst nach Verwendung von `quote()` können Sie es in einer `where()`-Anweisung verwenden.

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

Beschränken Sie die Anzahl der zurückgegebenen Datensätze. Wenn ein zweites Int übergeben wird, wird es wie in SQL versetzt, limitiert.

```php
$benutzer->orderby('name DESC')->limit(0, 10)->findAll();
```

## WHERE-Bedingungen
#### `equal(string $field, mixed $value) / eq(string $field, mixed $value)`

Dort `field = $value`

```php
$benutzer->eq('id', 1)->find();
```

#### `notEqual(string $field, mixed $value) / ne(string $field, mixed $value)`

Dort `field <> $value`

```php
$benutzer->ne('id', 1)->find();
```

#### `isNull(string $field)`

Dort `field IS NULL`

```php
$benutzer->isNull('id')->find();
```
#### `isNotNull(string $field) / notNull(string $field)`

Dort `field IS NOT NULL`

```php
$benutzer->isNotNull('id')->find();
```

#### `greaterThan(string $field, mixed $value) / gt(string $field, mixed $value)`

Dort `field > $value`

```php
$benutzer->gt('id', 1)->find();
```

#### `lessThan(string $field, mixed $value) / lt(string $field, mixed $value)`

Dort `field < $value`

```php
$benutzer->lt('id', 1)->find();
```
#### `greaterThanOrEqual(string $field, mixed $value) / ge(string $field, mixed $value) / gte(string $field, mixed $value)`

Dort `field >= $value`

```php
$benutzer->ge('id', 1)->find();
```
#### `lessThanOrEqual(string $field, mixed $value) / le(string $field, mixed $value) / lte(string $field, mixed $value)`

Dort `field <= $value`

```php
$benutzer->le('id', 1)->find();
```

#### `like(string $field, mixed $value) / notLike(string $field, mixed $value)`

Dort `field LIKE $value` oder `field NOT LIKE $value`

```php
$benutzer->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

Dort `field IN($value)` oder `field NOT IN($value)`

```php
$benutzer->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Dort `field BETWEEN $value AND $value1`

```php
$benutzer->between('id', [1, 2])->find();
```

## Beziehungen
Sie können mehrere Arten von Beziehungen mit dieser Bibliothek festlegen. Sie können Eins-zu-viele- und Eins-zu-eins-Beziehungen zwischen Tabellen festlegen. Dafür ist eine kleine Vorbereitung in der Klasse erforderlich.

Das Festlegen des `$relations`-Arrays ist nicht schwer, aber das korrekte Syntaxieren kann verwirrend sein.

```php
geschütztes Array $relations = [
	// Sie können den Schlüssel nach Belieben benennen. Der Name des ActiveRecords ist wahrscheinlich gut. Z.B.: user, contact, client
	'benutzer' => [
		// erforderlich
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // dies ist der Beziehungstyp

		// erforderlich
	'Some_Class', // dies ist die "andere" ActiveRecord-Klasse, auf die verwiesen wird

		// erforderlich
		// je nach Beziehungstyp
		// self::HAS_ONE = der Fremdschlüssel, der auf den Verbund verweist
		// self::HAS_MANY = der Fremdschlüssel, der auf den Verbund verweist
		// self::BELONGS_TO = der lokale Schlüssel, der auf den Verbund verweist
		'lokal_oder_fremd_schlüssel',
		// nur zur Info, das verbindet sich auch nur mit dem Primärschlüssel des "anderen" Modells

		// optional
		[ 'eq' => [ 'client_id', 5 ], 'select' => 'COUNT(*) as count', 'limit' 5 ], // zusätzliche Bedingungen, die Sie bei der Verbindung der Beziehung möchten
		// $record->eq('client_id', 5)->select('COUNT(*) as count')->limit(5))

		// optional
		'rück_reference_name' // dies ist, wenn Sie diese Beziehung zurück auf sich selbst beziehen möchten, z.B. $benutzer->contact->user;
	];
]
```

```php
class User extends ActiveRecord{
	geschütztes Array $relations = [
		'kontakte' => [ self::HAS_MANY, Kontakt::class, 'user_id' ],
		'contact' => [ self::HAS_ONE, Kontakt::class, 'user_id' ],
	];

	public Funktion __construct($datenbank_verbindung)
	{
		Elternteil::__construct($datenbank_verbindung, 'benutzer');
	}
}

class Contact extends ActiveRecord{
	geschütztes Array $relations = [
		'user' => [ self::BELONGS_TO, User::class, 'user_id' ],
		'user_with_backref' => [ self::BELONGS_TO, User::class, 'user_id', [], 'contact' ],
	];
	public Funktion __construct($datenbank_verbindung)
	{
		Elternteil::__construct($datenbank_verbindung, 'kontakte');
	}
}
```

Jetzt haben wir die Verweise eingerichtet, damit wir sie sehr einfach verwenden können!

```php
$benutzer = neue Benutzer($pdo_verbindung);

// finde den neuesten Benutzer.
$benutzer->notNull('id')->orderBy('id desc')->finde();

// erhalte Kontakte, indem du die Beziehung verwendest:
foreach($benutzer->kontakte as $kontakt) {
	echo $kontakt->id;
}

// oder wir können den umgekehrten Weg gehen.
$kontakt = neue Kontakt();

// finde einen Kontakt
$contact->finde();

// erhalte den Benutzer durch Verwendung der Beziehung:
echo $kontakt->user->name; // das ist der Benutzername
```

Ziemlich cool, oder?

## Einrichten benutzerdefinierter Daten
Manchmal müssen Sie Ihrem ActiveRecord etwas Einzigartiges wie eine benutzerdefinierte Berechnung anhängen, die möglicherweise einfacher wäre, diese an das Objekt anzuhängen, das dann an ein Template übergeben wird.

#### `setCustomData(string $field, mixed $value)`
Sie hängen die benutzerdefinierten Daten mit der Methode `setCustomData()` an.
```php
$benutzer->setCustomData('page_view_count', $page_view_count);
```

Und dann verweisen Sie einfach darauf wie auf eine normale Objekteigenschaft.

```php
echo $benutzer->page_view_count;
```

## Ereignisse

Eine weitere super coole Funktion dieser Bibliothek sind Ereignisse. Ereignisse werden zu bestimmten Zeiten basierend auf bestimmten von Ihnen aufgerufenen Methoden ausgelöst. Sie sind sehr, sehr hilfreich, um Daten automatisch für Sie einzurichten.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Dies ist wirklich hilfreich, wenn Sie eine Standardverbindung oder so etwas setzen müssen.

```php
// index.php oder bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	geschützte Funktion onConstruct(self $self, array &$config) { // vergessen Sie nicht die & Referenz
		// Sie könnten dies tun, um die Verbindung automatisch einzustellen
		$config['connection'] = Flight::db();
		// oder dies
		$self->transformAndPersistConnection(Flight::db());
		
		// Sie können auch den Tabellennamen auf diese Weise festlegen.
		$config['table'] = 'users';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Dies ist wahrscheinlich nur nützlich, wenn Sie jedes Mal eine Abfragebearbeitung benötigen.

```php
class User extends flight\ActiveRecord {
	
	publik Funktion __construct($datenbank_verbindung)
	{
		Elternteil::__construct($datenbank_verbindung, 'benutzer');
	}

	geschützte Funktion beforeFind(self $self) {
		// immer id >= 0 ausführen, wenn das Ihr Ding ist
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Dies ist wahrscheinlich nützlicher, wenn Sie jedes Mal eine Logik ausführen müssen, wenn dieser Datensatz abgerufen wird. Müssen Sie etwas entschlüsseln? Müssen Sie jedes Mal eine benutzerdefinierte Zählabfrage ausführen (nicht leistungsfähig, aber was auch immer)?

```php
class User extends flight\ActiveRecord {
	
	publik Funktion __construct($datenbank_verbindung)
	{
		Elternteil::__construct($datenbank_verbindung, 'benutzer');
	}

	geschützte Funktion afterFind(self $self) {
		// Etwas entschlüsseln
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// Speichern von etwas Benutzerdefiniertem wie einer Abfrage???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `beforeFindAll(ActiveRecord $ActiveRecord)`

Dies ist wahrscheinlich nur nützlich, wenn Sie jedes Mal eine Abfragebearbeitung benötigen.

```php
class User extends flight\ActiveRecord {
	
	publik Funktion __construct($datenbank_verbindung)
	{
		Elternteil::__construct($datenbank_verbindung, 'benutzer');
	}

	geschützte Funktion beforeFindAll(self $self) {
		// immer id >= 0 ausführen, wenn das Ihr Ding ist
		$self->gte('id', 0); 
	} 
}
```

#### `afterFindAll(array<int,ActiveRecord> $results)`

Ähnlich wie `afterFind()` können Sie dies nun für alle Datensätze ausführen!

```php
class User extends flight\ActiveRecord {
	
	publik Funktion __construct($datenbank_verbindung)
	{
		Elternteil::__construct($datenbank_verbindung, 'benutzer');
	}

	geschützte Funktion afterFindAll(Array $results) {

		foreach($results as $self) {
			// Mach coole Sachen wie nachFind()
		}
	} 
}
```

#### `beforeInsert(ActiveRecord $ActiveRecord)`

Wirklich hilfreich, wenn Sie jedes Mal Standardwerte setzen müssen.

```php
class User extends flight\ActiveRecord {
	
	publik Funktion __construct($datenbank_verbindung)
	{
		Elternteil::__construct($datenbank_verbindung, 'benutzer');
	}

	geschützte Funktion beforeInsert(self $self) {
		// Setze einige vernünftige Standardwerte
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

Vielleicht haben Sie einen Use-Case zum Ändern von Daten, nachdem sie eingefügt wurden?

```php
class User extends flight\ActiveRecord {
	
	publik Funktion __construct($datenbank_verbindung)
	{
		Elternteil::__construct($datenbank_verbindung, 'benutzer');
	}

	geschützte Funktion afterInsert(self $self) {
		// Sie können...
		Flight::cache()->set('most_recent_insert_id', $self->id);
		// oder was auch immer....
	} 
}
```

#### `beforeUpdate(ActiveRecord $ActiveRecord)`

Wirklich hilfreich, wenn Sie jedes Mal Standardwerte beim Aktualisieren setzen müssen.

```php
class User extends flight\ActiveRecord {
	
	publik Funktion __construct($datenbank_verbindung)
	{
		Elternteil::__construct($datenbank_verbindung, 'benutzer');
	}

	geschützte Funktion beforeInsert(self $self) {
		// Setzen Sie einige vernünftige Standardwerte
		if(!$self->updated_date) {
			$self->updated_date = gmdate('Y-m-d');
		}
	} 
}
```

#### `afterUpdate(ActiveRecord $ActiveRecord)`

Vielleicht haben Sie einen Use-Case für die Änderung von Daten, nachdem sie aktualisiert wurden?

```php
class User extends flight\ActiveRecord {
	
	publik Funktion __construct($datenbank_verbindung)
	{
		Elternteil::__construct($datenbank_verbindung, 'benutzer');
	}

	geschützte Funktion afterInsert(self $self) {
		// Machen Sie, was Sie wollen
		Flight::cache()->set('most_recently_updated_user_id', $self->id);
		// oder so weiter....
	} 
}
```

#### `beforeSave(ActiveRecord $ActiveRecord)/afterSave(ActiveRecord $ActiveRecord)`

Dies ist nützlich, wenn Sie Ereignisse auslösen möchten, wenn Einfügungen oder Aktualisierungen erfolgen. Ich erspare Ihnen die lange Erklärung, aber ich bin mir sicher, Sie können erraten, was es ist.

```php
class User extends flight\ActiveRecord {
	
	publik Funktion __construct($datenbank_verbindung)
	{
		Elternteil::__construct($datenbank_verbindung, 'benutzer');
	}

	geschützte Funktion beforeSave(self $self) {
		$self->last_updated = gmdate('Y-m-d H:i:s');
	} 
}
```

#### `beforeDelete(ActiveRecord $ActiveRecord)/afterDelete(ActiveRecord $ActiveRecord)`

Nicht sicher, was Sie hier tun möchten, aber keine Urteile hier! Los geht's!

```php
class User extends flight\ActiveRecord {
	
	publik Funktion __construct($datenbank_verbindung)
	{
		Elternteil::__construct($datenbank_verbindung, 'benutzer');
	}

	geschützte Funktion beforeDelete(self $self) {
		echo 'Er war ein tapferer Soldat... :cry-face:';
	} 
}
```

## Datenbankverbindungsverwaltung

Wenn Sie diese Bibliothek verwenden, können Sie die Datenbankverbindung auf mehrere Arten festlegen. Sie können die Verbindung im Konstruktor festlegen, Sie können sie über eine Konfigurationsvariable `$config['connection']` festlegen oder Sie können sie über `setDatabaseConnection()` festlegen (v0.4.1).

```php
$pdo_verbindung = new PDO('sqlite:test.db'); // zum Beispiel
$benutzer = neue Benutzer($pdo_verbindung);
// oder
$benutzer = neue Benutzer(null, [ 'connection' => $pdo_verbindung ]);
// oder
$benutzer = neue Benutzer();
$benutzer->setDatabaseConnection($pdo_verbindung);
```

Wenn Sie die Datenbankverbindung aktualisieren müssen, zum Beispiel, wenn Sie ein lang laufendes CLI-Skript ausführen und die Verbindung von Zeit zu Zeit aktualisieren müssen, können Sie die Verbindung mit `$your_record->setDatabaseConnection($pdo_verbindung)` erneut setzen.

## Beitragender

Bitte tun Sie das. :D

## Setup

Wenn Sie beitragen, stellen Sie sicher, dass Sie `composer test-coverage` ausführen, um eine Testabdeckung von 100% zu erhalten (dies ist keine echte Einheitstestabdeckung, sondern eher Integrationstests).

Stellen Sie außerdem sicher, dass Sie `composer beautify` und `composer phpcs` ausführen, um eventuelle Linting-Fehler zu beheben.

## Lizenz

MIT