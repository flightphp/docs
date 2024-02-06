# FlightPHP Aktives Rekord

Ein aktiver Datensatz entspricht der Zuordnung einer Datenbankentität zu einem PHP-Objekt. Einfach ausgedrückt können Sie, wenn Sie eine Benutzer-Tabelle in Ihrer Datenbank haben, eine Zeile in dieser Tabelle in eine `User`-Klasse und ein `$user`-Objekt in Ihrer Codebasis "übersetzen". Siehe [Grundbeispiel](#grundbeispiel).

## Grundbeispiel

Angenommen, Sie haben folgende Tabelle:

```sql
CREATE TABLE users (
	id INTEGER PRIMARY KEY, 
	name TEXT, 
	password TEXT 
);
```

Nun können Sie eine neue Klasse erstellen, um diese Tabelle darzustellen:

```php
/**
 * Eine ActiveRecord-Klasse ist normalerweise im Singular
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
		// Sie können es auf diese Weise festlegen
		parent::__construct($database_connection, 'users');
		// oder so
		parent::__construct($database_connection, null, [ 'table' => 'users']);
	}
}
```

Schauen Sie sich jetzt an, wie die Magie passiert!

```php
// für sqlite
$database_connection = new PDO('sqlite:test.db'); // dies dient nur als Beispiel, Sie würden wahrscheinlich eine echte Datenbankverbindung verwenden

// für mysql
$database_connection = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'username', 'password');

// oder mysqli
$database_connection = new mysqli('localhost', 'username', 'password', 'test_db');
// oder mysqli mit nicht objektbasierter Erstellung
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
// Sie können $user->save() hier nicht verwenden, da es ansonsten als Update betrachtet wird!

echo $user->id; // 2
```

Und es war wirklich leicht, einen neuen Benutzer hinzuzufügen! Jetzt, da es eine Benutzerzeile in der Datenbank gibt, wie können Sie sie herausholen?

```php
$user->find(1); // finde id = 1 in der Datenbank und gib sie zurück.
echo $user->name; // 'Bobby Tables'
```

Und wenn Sie alle Benutzer finden möchten?

```php
$users = $user->findAll();
```

Was ist mit einer bestimmten Bedingung?

```php
$users = $user->like('name', '%mamma%')->findAll();
```

Sehen Sie, wie viel Spaß das macht? Lassen Sie es uns installieren und loslegen!

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
$pdo_verbindung = new PDO('sqlite:test.db'); // dies dient nur als Beispiel, Sie würden wahrscheinlich eine echte Datenbankverbindung verwenden

$User = new User($pdo_verbindung);
```

### Flight PHP Framework
Wenn Sie das Flight PHP Framework verwenden, können Sie die ActiveRecord-Klasse als Dienst registrieren (aber das müssen Sie wirklich nicht).

```php
Flight::register('benutzer', 'User', [ $pdo_verbindung ]);

// dann können Sie es so in einem Controller, einer Funktion usw. verwenden.

Flight::user()->find(1);
```

## API-Referenz
### CRUD-Funktionen

#### `find($id = null) : boolean|ActiveRecord`

Findet einen Datensatz und weist ihn dem aktuellen Objekt zu. Wenn Sie eine `$id` von irgendeiner Art übergeben, wird eine Abfrage zum Primärschlüssel mit diesem Wert durchgeführt. Wenn nichts übergeben wird, wird einfach der erste Datensatz in der Tabelle gefunden.

Zusätzlich können Sie andere Hilfsmethoden übergeben, um Ihre Tabelle abzufragen.

```php
// einen Datensatz mit bestimmten Bedingungen vorab finden
$user->notNull('password')->orderBy('id DESC')->find();

// einen Datensatz über eine spezifische ID finden
$id = 123;
$user->find($id);
```

#### `findAll(): array<int,ActiveRecord>`

Findet alle Datensätze in der von Ihnen angegebenen Tabelle.

```php
$user->findAll();
```

#### `insert(): boolean|ActiveRecord`

Fügt den aktuellen Datensatz in die Datenbank ein.

```php
$user = new User($pdo_verbindung);
$user->name = 'demo';
$user->password = md5('demo');
$user->insert();
```

#### `update(): boolean|ActiveRecord`

Aktualisiert den aktuellen Datensatz in der Datenbank.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();
$user->email = 'test@example.com';
$user->update();
```

#### `delete(): boolean`

Löscht den aktuellen Datensatz aus der Datenbank.

```php
$user->gt('id', 0)->orderBy('id desc')->find();
$user->delete();
```

#### `dirty(array  $dirty = []): ActiveRecord`

Schmutzige Daten beziehen sich auf Daten, die in einem Datensatz geändert wurden.

```php
$user->greaterThan('id', 0)->orderBy('id desc')->find();

// bis zu diesem Zeitpunkt ist nichts "schmutzig".
$user->email = 'test@example.com'; // jetzt wird die E-Mail als "schmutzig" betrachtet, da sie geändert wurde.
$user->update();
// jetzt gibt es keine schmutzigen Daten mehr, da sie aktualisiert und in der Datenbank festgehalten wurden.

$user->password = password_hash()'newpassword'); // jetzt ist dies schmutzig
$user->dirty(); // das Übergeben von nichts wird alle schmutzigen Einträge löschen.
$user->update(); // nichts wird aktualisiert, da nichts als schmutzig erkannt wurde.

$user->dirty([ 'name' => 'something', 'password' => password_hash('a different password') ]);
$user->update(); // sowohl Name als auch Passwort werden aktualisiert.
```

### SQL-Abfragemethoden
#### `select(string $field1 [, string $field2 ... ])`

Sie können nur einige der Spalten in einer Tabelle auswählen, falls gewünscht (es ist effizienter bei sehr breiten Tabellen mit vielen Spalten)

```php
$user->select('id', 'name')->find();
```

#### `from(string $table)`

Sie können technisch gesehen auch eine andere Tabelle auswählen! Warum nicht?!

```php
$user->select('id', 'name')->from('user')->find();
```

#### `join(string $table_name, string $join_condition)`

Sie können sogar zu einer anderen Tabelle in der Datenbank verbinden.

```php
$user->join('kontakte', 'kontakte.user_id = benutzer.id')->find();
```

#### `where(string $where_conditions)`

Sie können benutzerdefinierte WHERE-Argumente festlegen (Sie können keine Parameter in dieser WHERE-Klausel festlegen)

```php
$user->where('id=1 AND name="demo"')->find();
```

**Sicherheitshinweis** - Sie könnten versucht sein, etwas wie `$user->where("id = '{$id}' AND name = '{$name}'")->find();` zu tun. Bitte TUN SIE DAS NICHT!!! Dies ist anfällig für sogenannte SQL-Injektionsangriffe. Es gibt viele Artikel online, bitte suchen Sie nach "SQL-Injektionsangriffe php" und Sie finden viele Artikel zu diesem Thema. Der richtige Umgang damit in dieser Bibliothek ist anstelle dieser `where()`-Methode etwas wie `$user->eq('id', $id)->eq('name', $name)->find();` zu verwenden.

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

Beschränken Sie die Anzahl der zurückgegebenen Datensätze. Wenn ein zweites int-Wert übergeben wird, wird es offsetiert, genau wie in SQL limit.

```php
$user->orderby('name DESC')->limit(0, 10)->findAll();
```

### WHERE-Bedingungen
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

Where `field LIKE $value` or `field NOT LIKE $value`

```php
$user->like('name', 'de')->find();
```

#### `in(string $field, array $values) / notIn(string $field, array $values)`

Where `field IN($value)` or `field NOT IN($value)`

```php
$user->in('id', [1, 2])->find();
```

#### `between(string $field, array $values)`

Where `field BETWEEN $value AND $value1`

```php
$user->between('id', [1, 2])->find();
```

### Beziehungen
Sie können mit dieser Bibliothek verschiedene Arten von Beziehungen festlegen. Sie können Eine->Viele und Eine->Eine-Beziehungen zwischen Tabellen festlegen. Dies erfordert eine etwas zusätzliche Einrichtung in der Klasse im Voraus.

Das Festlegen des `$relations`-Arrays ist nicht schwer, aber das korrekte Syntaxieren kann verwirrend sein.

```php
geschützte Array $relations = [
	// Sie können den Schlüssel nach Belieben benennen. Der Name der Active Record ist wahrscheinlich gut. Beispiel: benutzer, kontakt, klient
	'whatever_active_record' => [
		// erforderlich
		self::HAS_ONE, // dies ist der Beziehungstyp

		// erforderlich
		'Some_Class', // dies ist die "andere" ActiveRecord-Klasse, auf die hier verwiesen wird

		// erforderlich
		'local_key', // dies ist der lokale Schlüssel, der auf den Join verweist.
		// Nur zur Information, dies verknüpft auch nur mit dem Primärschlüssel des "anderen" Modells

		// optional
		[ 'eq' => 1, 'select' => 'COUNT(*) als Anzahl', 'limit' 5 ], // benutzerdefinierte Methoden, die Sie ausführen möchten. [] wenn Sie nichts wünschen.

		// optional
		'back_reference_name' // das ist, wenn Sie diese Beziehung auf sich selbst zurückverweisen möchten. Beispiel: $benutzer->kontakt->benutzer;
	];
]
```

```php
Klasse Benutzer erweitert ActiveRecord{
	geschützte Array $relations = [
		'kontakte' => [ self::HAS_MANY, Kontakt::class, 'benutzer_id' ],
		'kontakt' => [ self::HAS_ONE, Kontakt::class, 'benutzer_id' ],
	];

	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'benutzer');
	}
}

Klasse Kontakt erweitert ActiveRecord{
	geschützte Array $relations = [
		'benutzer' => [ self::BELONGS_TO, Benutzer::class, 'benutzer_id' ],
		'user_with_backref' => [ self::BELONGS_TO, Benutzer::class, 'benutzer_id', [], 'kontakt' ],
	];
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'kontakte');
	}
}
```

Jetzt haben wir die Verweise eingerichtet, so dass wir sie sehr einfach verwenden können!

```php
$benutzer = new Benutzer($pdo_verbindung);

// finde den neuesten Benutzer.
$user->notNull('id')->orderBy('id desc')->find();

// erhalte Kontakte, indem du die Beziehung verwendest:
foreach($benutzer->kontakte as $kontakt) {
	echo $kontakt->id;
}

// oder wir können den anderen Weg gehen.
$kontakt = new Kontakt();

// finde einen Kontakt
$kontakt->find();

// erhalte den Benutzer, indem du die Beziehung verwendest:
echo $kontakt->user->name; // dies ist der Benutzername
```

Ziemlich cool, oder?

### Anpassen von Daten
Manchmal müssen Sie Ihrem ActiveRecord etwas Einzigartiges anhängen, wie eine benutzerdefinierte Berechnung, die möglicherweise einfacher ist, einfach an das Objekt anzuhängen, das dann an ein Template übergeben wird.

#### `setCustomData(string $field, mixed $value)`
Sie hängen benutzerdefinierte Daten mit der `setCustomData()`-Methode an.
```php
$user->setCustomData('page_view_count', $page_view_count);
```

Und dann verweisen Sie einfach darauf wie auf eine normale Objekteigenschaft.

```php
echo $user->page_view_count;
```

### Ereignisse

Ein weiteres super tolles Feature dieser Bibliothek sind Ereignisse. Ereignisse werden zu bestimmten Zeiten basierend auf bestimmten Methoden ausgelöst, die Sie aufrufen. Sie sind sehr sehr hilfreich, um Daten automatisch für Sie einrichten.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Dies ist wirklich hilfreich, wenn Sie z.B. eine Standardverbindung einrichten müssen.

```php
// index.php oder bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// Benutzer.php
Klasse Benutzer erweitert flight\ActiveRecord {

	geschützte Funktion onConstruct(self $self, array &$config) { // vergessen Sie die Referenz & nicht
		// Sie könnten dies tun, um automatisch die Verbindung einzurichten
		$config['connection'] = Flight::db();
		// oder das
		$self->transformAndPersistConnection(Flight::db());
		
		// Sie können auch den Tabellennamen so festlegen.
		$config['table'] = 'benutzer';
	} 
}
```

#### `beforeFind(ActiveRecord $ActiveRecord)`

Dies ist wahrscheinlich nur dann nützlich, wenn Sie jedes Mal eine Abfrageänderung benötigen.

```php
Klasse Benutzer erweitert flight\ActiveRecord {
	
	public Funktion __construct($database_connection)
	{
		parent::__construct($database_connection, 'benutzer');
	}

	geschützte Funktion beforeFind(self $self) {
		// führen Sie immer id >= 0 aus, wenn das Ihre Vorliebe ist
		$self->gte('id', 0); 
	} 
}
```

#### `afterFind(ActiveRecord $ActiveRecord)`

Diese Methode ist wahrscheinlich nützlicher, wenn Sie jedes Mal eine Logik ausführen müssen, wenn dieser Datensatz abgerufen wird. Müssen Sie etwas entschlüsseln? Müssen Sie jedes Mal eine benutzerdefinierte Zählabfrage ausführen (nicht performant, aber egal)?

```php
Klasse Benutzer erweitert flight\ActiveRecord {
	
	public Funktion __construct($database_connection)
	{
		parent::__construct($database_connection, 'benutzer');
	}

	geschützte Funktion afterFind(self $self) {
		// Etwas entschlüsseln
		$self->secret = yourDecryptFunction($self->secret, $some_key);

		// vielleicht etwas Benutzerdefiniertes speichern wie eine Abfrage???
		$self->setCustomData('view_count', $self->select('COUNT(*) count')->from('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

###### `beforeFindAll(ActiveRecord $ActiveRecord)`

Das ist wahrscheinlich nur nützlich, wenn Sie jedes Mal eine Abfrageänderung benötigen.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

protected function beforeFindAll(self $self) {
    // immer id >= 0 ausführen, wenn das Ihre Präferenz ist
    $self->gte('id', 0); 
} 
}
```

## `afterFindAll(array<int,ActiveRecord> $results)`

Ähnlich wie `afterFind()`, aber Sie können es auf alle Datensätze anwenden!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($database_connection)
	{
		parent::__construct($database_connection, 'users');
	}

protected function afterFindAll(array $results) {

    foreach($results as $self) {
        // etwas Cooles tun wie nach afterFind()
    }
} 
}
```