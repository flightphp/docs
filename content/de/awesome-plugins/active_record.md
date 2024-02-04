# FlightPHP Active Record 

Eine aktive Aufzeichnung ist die Zuordnung einer Datenbankeinheit zu einem PHP-Objekt. Ganz einfach ausgedrückt, wenn Sie eine Benutzertabelle in Ihrer Datenbank haben, können Sie eine Zeile in dieser Tabelle in eine `Benutzer`-Klasse und ein `$benutzer`-Objekt in Ihrem Code übersetzen. Siehe [Grundbeispiel](#grundbeispiel).

## Grundbeispiel

Angenommen, Sie haben die folgende Tabelle:

```sql
CREATE TABLE benutzer (
	id INTEGER PRIMÄRSCHLÜSSEL, 
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
 * @property string $password
 */ 
class User extends flight\ActiveRecord {
	public function __construct($datenbankverbindung)
	{
		// Sie können es auf diese Weise festlegen
		parent::__construct($datenbankverbindung, 'benutzer');
		// oder so
		parent::__construct($datenbankverbindung, null, [ 'table' => 'benutzer']);
	}
}
```

Schauen Sie jetzt zu, wie die Magie passiert!

```php
// für sqlite
$datenbankverbindung = new PDO('sqlite:test.db'); // das ist nur ein Beispiel, Sie würden wahrscheinlich eine echte Datenbankverbindung nutzen

// für mysql
$datenbankverbindung = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'benutzername', 'passwort');

// oder mysqli
$datenbankverbindung = new mysqli('localhost', 'benutzername', 'passwort', 'test_db');
// oder mysqli mit nicht objektbasierter Erstellung
$datenbankverbindung = mysqli_connect('localhost', 'benutzername', 'passwort', 'test_db');

$benutzer = new Benutzer($datenbankverbindung);
$benutzer->name = 'Bobby Tables';
$benutzer->password = password_hash('Ein cooles Passwort');
$benutzer->einfügen();
// oder $benutzer->speichern();

echo $benutzer->id; // 1

$benutzer->name = 'Joseph Mamma';
$benutzer->password = password_hash('Ein cooles Passwort noch einmal!!!');
$benutzer->einfügen();
// kann hier nicht $benutzer->speichern() verwenden, da es sich um ein Update handeln würde!

echo $benutzer->id; // 2
```

Und es war so einfach, einen neuen Benutzer hinzuzufügen! Jetzt, da es eine Benutzerzeile in der Datenbank gibt, wie können Sie sie herausziehen?

```php
$benutzer->find(1); // finde id = 1 in der Datenbank und gebe sie zurück.
echo $benutzer->name; // 'Bobby Tables'
```

Und wenn Sie alle Benutzer finden möchten?

```php
$benutzer = $benutzer->findeAlle();
```

Was ist mit einer bestimmten Bedingung?

```php
$benutzer = $benutzer->wie('name', '%mamma%')->findeAlle();
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
Vergewissern Sie sich einfach, dass Sie eine PDO-Verbindung an den Konstruktor übergeben.

```php
$pdo_verbindung = new PDO('sqlite:test.db'); // das ist nur ein Beispiel, Sie würden wahrscheinlich eine echte Datenbankverbindung nutzen

$Benutzer = new Benutzer($pdo_verbindung);
```

### Flight PHP Framework
Wenn Sie das Flight PHP Framework verwenden, können Sie die ActiveRecord-Klasse als Dienst registrieren (aber Sie müssen es ehrlich gesagt nicht).

```php
Flight::register('benutzer', 'Benutzer', [ $pdo_verbindung ]);

// dann können Sie es in einem Controller, einer Funktion usw. so verwenden

Flight::benutzer()->find(1);
```

## API Referenz
### CRUD-Funktionen

#### `find($id = null) : boolean|ActiveRecord`

Findet einen Datensatz und weist ihn dem aktuellen Objekt zu. Wenn Sie eine Art `$id` übergeben, wird eine Suche nach dem Primärschlüssel mit diesem Wert durchgeführt. Wenn nichts übergeben wird, wird einfach der erste Datensatz in der Tabelle gefunden.

Zusätzlich können Sie andere Hilfsmethoden übergeben, um Ihre Tabelle abzufragen.

```php
// finde einen Datensatz mit bestimmten vorherigen Bedingungen
$benutzer->nichtNull('passwort')->orderBy('id DESC')->finde();

// finde einen Datensatz anhand einer bestimmten ID
$id = 123;
$benutzer->finde($id);
```

#### `findeAlle(): array<int,ActiveRecord>`

Findet alle Datensätze in der von Ihnen angegebenen Tabelle.

```php
$benutzer->findeAlle();
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
$benutzer->größerAls('id', 0)->orderBy('id desc')->finde();
$benutzer->email = 'test@example.com';
$benutzer->aktualisieren();
```

#### `löschen(): boolean`

Löscht den aktuellen Datensatz aus der Datenbank.

```php
$benutzer->gr('id', 0)->orderBy('id desc')->finde();
$benutzer->löschen();
```

#### `verschmutzt(array  $schmutzig = []): ActiveRecord`

Verschmutzte Daten beziehen sich auf die Daten, die in einem Datensatz geändert wurden.

```php
$benutzer->größerAls('id', 0)->orderBy('id desc')->finde();

// Es ist zu diesem Zeitpunkt nichts "schmutzig".
$benutzer->email = 'test@example.com'; // jetzt wird die E-Mail als "schmutzig" angesehen, da sie geändert wurde.
$benutzer->aktualisieren();
// jetzt gibt es keine schmutzigen Daten mehr, da sie aktualisiert und in der Datenbank gespeichert wurden.

$benutzer->passwort = password_hash()'neues passwort'); // jetzt ist das schmutzig
$benutzer->schmutzig(); // Ohne Argumente zu übergeben, werden alle schmutzigen Einträge gelöscht.
$benutzer->aktualisieren(); // Es wird nichts aktualisiert, da nichts als schmutzig erfasst wurde.

$benutzer->schmutzig([ 'name' => 'etwas', 'passwort' => password_hash('ein anderes Passwort') ]);
$benutzer->aktualisieren(); // sowohl Name als auch Passwort werden aktualisiert.
```

### SQL-Abfrage-Methoden
#### `auswählen(string $feld1 [, string $feld2 ... ])`

Sie können nur einige Spalten in einer Tabelle auswählen, wenn Sie möchten (es ist leistungsfähiger bei sehr breiten Tabellen mit vielen Spalten).

```php
$benutzer->auswählen('id', 'name')->finde();
```

#### `aus(string $tabelle)`

Sie können technisch gesehen auch eine andere Tabelle auswählen! Warum zum Teufel nicht?!

```php
$benutzer->auswählen('id', 'name')->aus('benutzer')->finde();
```

#### `beitreten(string $tabellenname, string $join_bedingung)`

Sie können sich sogar mit einer anderen Tabelle in der Datenbank verbinden.

```php
$benutzer->beitreten('kontakte', 'kontakte.benutzer_id = benutzer.id')->finde();
```

#### `wo(string $where_bedingungen)`

Sie können benutzerdefinierte WHERE-Argumente festlegen (Sie können keine Parameter in dieser WHERE-Anweisung festlegen)

```php
$benutzer->wo('id=1 UND name="demo"')->finde();
```

**Sicherheitshinweis** - Sie könnten versucht sein, so etwas zu tun wie `$benutzer->wo("id = '{$id}' UND name = '{$name}'")->finde();`. Bitte TUN SIE DAS NICHT!!! Dies ist anfällig für sogenannte SQL-Injection-Angriffe. Es gibt viele Artikel online, suchen Sie bitte nach "SQL-Injections-Angriffe PHP" und Sie werden viele Artikel zu diesem Thema finden. Der richtige Umgang damit mit dieser Bibliothek ist anstelle dieser `wo()`-Methode etwas wie `$benutzer->eq('id', $id)->eq('name', $name)->finde();` zu machen.

#### `gruppe(string $group_by_statement)/groupBy(string $group_by_statement)`

Gruppieren Sie Ihre Ergebnisse nach einer bestimmten Bedingung.

```php
$benutzer->auswählen('COUNT(*) as count')->groupBy('name')->findeAlle();
```

#### `bestellen(string $order_by_statement)/orderBy(string $order_by_statement)`

Sortieren Sie die zurückgegebene Abfrage auf eine bestimmte Weise.

```php
$benutzer->orderBy('name DESC')->finde();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Begrenzen Sie die Anzahl der zurückgegebenen Datensätze. Wenn eine zweite Ganzzahl übergeben wird, handelt es sich um Offset, Limit genau wie in SQL.

```php
$benutzer->orderBy('name DESC')->limit(0, 10)->findeAlle();
```

### WO-Bedingungen
#### `gleich(string $feld, mixed $wert) / eq(string $feld, mixed $wert)`

Wobei `Feld = $Wert`

```php
$benutzer->eq('id', 1)->finde();
```

#### `nicht_gleich(string $feld, mixed $wert) / ne(string $feld, mixed $wert)`

Wobei `Feld <> $Wert`

```php
$benutzer->ne('id', 1)->finde();
```

#### `istNull(string $feld)`

Wobei `Feld IS NULL`

```php
$benutzer->istNull('id')->finde();
```
#### `istNichtNull(string $feld) / notNull(string $feld)`

Wobei `Feld IS NOT NULL`

```php
$benutzer->istNichtNull('id')->finde();
```

#### `größerAls(string $feld, mixed $wert) / gt(string $feld, mixed $wert)`

Wobei `Feld > $Wert`

```php
$benutzer->gt('id', 1)->finde();
```

#### `kleinerAls(string $feld, mixed $wert) / lt(string $feld, mixed $wert)`

Wobei `Feld < $Wert`

```php
$benutzer->lt('id', 1)->finde();
```
#### `größerOderGleich(string $feld, mixed $wert) / ge(string $feld, mixed $wert) / gte(string $feld, mixed $wert)`

Wobei `Feld >= $Wert`

```php
$benutzer->ge('id', 1)->finde();
```
#### `kleinerOderGleich(string $feld, mixed $wert) / le(string $feld, mixed $wert) / lte(string $feld, mixed $wert)`

Wobei `Feld <= $Wert`

```php
$benutzer->le('id', 1)->finde();
```

#### `wie(string $feld, mixed $wert) / notLike(string $feld, mixed $wert)`

Wobei `Feld LIKE $Wert` oder `Feld NOT LIKE $Wert`

```php
$benutzer->wie('name', 'de')->finde();
```

#### `in(string $feld, array $werte) / notIn(string $feld, array $werte)`

Wobei `Feld IN($Wert)` oder `Feld NOT IN($Wert)`

```php
$benutzer->in('id', [1, 2])->finde();
```

#### `between(string $feld, array $werte)`

Wobei `Feld BETWEEN $Wert UND $Wert1`

```php
$benutzer->between('id', [1, 2])->finde();
```

### Beziehungen
Mit dieser Bibliothek können Sie verschiedene Arten von Beziehungen einrichten. Sie können ein-zu-viele- und eins-zu-eins-Beziehungen zwischen Tabellen festlegen. Hierfür ist eine kleine zusätzliche Einrichtung in der Klasse erforderlich.

Das Setzen des `$relations`-Arrays ist nicht schwer, aber das richtige Syntax zu erraten kann verwirrend sein.

```php
geschütztes array $relations = [
	// Sie können den Schlüssel beliebig benennen. Der Name der ActiveRecord ist wahrscheinlich gut. Beispiel: benutzer, kontakt, klient
	'was_auch_immer_active_record' => [
		// erforderlich
		self::HAS_ONE, // dies ist der Beziehungstyp

		// erforderlich
		'Einige_Klasse', // dies ist die "andere" ActiveRecord-Klasse, auf die sich dies bezieht

		// erforderlich
		'lokaler_schlüssel', // dies ist der lokale Schlüssel, der auf das Join verweist.
		// Nur zur Info, dies verbindet auch nur mit dem Primärschlüssel des "anderen" Modells

		// optional
		[ 'eq' => 1, 'auswählen' => 'COUNT(*) as count', 'limitierung' 5 ], // benutzerdefinierte Methoden, die Sie ausführen möchten. [] wenn Sie keine möchten.

		// optional
		'zurück_reference_name' // dies ist, wenn Sie diese Beziehung zurück auf sich selbst referenzieren möchten, z.B. $benutzer->kontakt->benutzer;
	];
]
```

```php
class User extends ActiveRecord{
	geschütztes array $relations = [
		'kontakte' => [ self::HAS_MANY, Kontakt::class, 'benutzer_id' ],
		'kontakt' => [ self::HAS_ONE, Kontakt::class, 'benutzer_id' ],
	];

	public function __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'benutzer');
	}
}

class Kontakt extends ActiveRecord{
	geschütztes array $relations = [
		'benutzer' => [ self::BELONGS_TO, User::class, 'benutzer_id' ],
		'benutzer_mit_zurückref' => [ self::BELONGS_TO, User::class, 'benutzer_id', [], 'kontakt' ],
	];
	public function __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'kontakte');
	}
}
```

Jetzt haben wir die Referenzen eingerichtet, damit wir sie sehr einfach verwenden können!

```php
$benutzer = new Benutzer($pdo_verbindung);

// finde den neuesten Benutzer.
$benutzer->nichtNull('id')->orderBy('id desc')->finde();

// erhalten Sie Kontakte, indem Sie die Beziehung verwenden:
foreach($benutzer->kontakte as $kontakt) {
	echo $kontakt->id;
}

// oder wir können auch den anderen Weg gehen.
$kontakt = new Kontakt();

// finde einen Kontakt
$kontakt->finde();

// erhalte den Benutzer, indem du die Beziehung verwendest:
echo $kontakt->benutzer->name; // das ist der Benutzername
```

Cool, oder?

### Festlegung benutzerdefinierter Daten
Manchmal müssen Sie Ihrem ActiveRecord etwas Einzigartiges anhängen, wie z.B. eine benutzerdefinierte Berechnung, die möglicherweise einfacher ist, das Objekt anzuhängen, das dann beispielsweise an eine Vorlage übergeben wird.

#### `setzeBenutzerdefinierteDaten(string $feld, mixed $wert)`
Sie hängen die benutzerdefinierten Daten mit der `setCustomData()`-Methode an.
```php
$benutzer->setCustomData('page_view_count', $page_view_count);
```

Und dann verweisen Sie einfach darauf wie auf eine normale Objekteigenschaft.

```php
echo $benutzer->page_view_count;
```

### Ereignisse

Eine weitere super tolle Funktion dieser Bibliothek sind Ereignisse. Ereignisse werden zu bestimmten Zeiten basierend auf bestimmten von Ihnen aufgerufenen Methoden ausgelöst. Sie sind sehr sehr hilfreich, um Daten für Sie automatisch einzurichten.

#### `beiKonstruktion(ActiveRecord $ActiveRecord, array &konfiguration)`

Das ist wirklich hilfreich, wenn Sie eine Standardverbindung oder so etwas einrichten müssen.

```php
// index.php oder bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// User.php
class User extends flight\ActiveRecord {

	geschützte Funktion beiKonstruktion(self $self, array &$konfiguration) { // vergessen Sie nicht die & Referenz
		// Sie könnten dies tun, um die Verbindung automatisch einzurichten
		$konfiguration['verbindung'] = Flight::db();
		// oder dies
		$self->verbindungTransformierenUndPersistieren(continued)

```php
		(Flight::db());
		
		// Sie können auch auf diese Weise den Tabellennamen festlegen.
		$konfiguration['tabelle'] = 'benutzer';
	} 
}
```

#### `vorFinden(ActiveRecord $ActiveRecord)`

Dies ist wahrscheinlich nur nützlich, wenn Sie jedes Mal eine Abfrage manipulation benötigen.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'benutzer');
	}

	geschützte Funktion vorFinden(self $self) {
		// führen Sie immer id >= 0 aus, wenn das Ihr Ding ist
		$self->gte('id', 0); 
	} 
}
```

#### `nachFinden(ActiveRecord $ActiveRecord)`

Dies ist wahrscheinlich nützlicher, wenn Sie jedes Mal eine Logik ausführen müssen, wenn dieser Datensatz abgerufen wird. Möchten Sie etwas entschlüsseln? Möchten Sie jedes Mal eine benutzerdefinierte Abfrage zählen lassen (leistungsfähig, aber wen stört's)?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'benutzer');
	}

	geschützte Funktion nachFinden(self $self) {
		// Etwas entschlüsseln
		$self->geheimnis = yourDecryptFunction($self->geheimnis, $some_key);

		// speichern Sie möglicherweise etwas benutzerdefiniertes wie eine Abfrage???
		$self->setCustomData('view_count', $self->auswählen('COUNT(*) count')->aus('user_views')->eq('user_id', $self->id)['count']; 
	} 
}
```

#### `vorFindenAlle(ActiveRecord $ActiveRecord)`

Dies ist wahrscheinlich nur nützlich, wenn Sie jedes Mal eine Abfrage manipulation benötigen.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'benutzer');
	}

	geschützte Funktion beforeFindAll(self $self) {
		// führen Sie immer id >= 0 aus, wenn das Ihr Ding ist
		$self->gte('id', 0); 
	} 
}
```

#### `nachFindenAlle(array<int,ActiveRecord> $ergebnisse)`

Ähnlich wie `nachFinden()` wird es jedoch auf alle Datensätze angewendet!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'benutzer');
	}

	geschützte Funktion nachFindAll(array $ergebnisse) {

		foreach($ergebnisse as $self) {
			// Mach etwas Cooles wie nachFinden()
		}
	} 
}
```

#### `vorEinfügen(ActiveRecord $ActiveRecord)`

Wirklich hilfreich, wenn Sie jedes Mal einige Standardwerte setzen müssen.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'benutzer');
	}

	geschützte Funktion vorEinfügen(self $self) {
		// setze einige sinnvolle Standardwerte
		if(!$self->erstellungsdatum) {
			$self->erstellungsdatum = gmdate('Y-m-d');
		}

		if(!$self->passwort) {
			$self->passwort = password_hash((string) microtime(true));
		}
	} 
}
```

#### `nachEinfügen(ActiveRecord $ActiveRecord)`

Vielleicht haben Sie einen Anwendungsfall zum Ändern von Daten nach dem Einfügen?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'benutzer');
	}

	geschützte Funktion nachEinfügen(self $self) {
		// tun Sie, was Sie wollen
		Flight::cache()->set('zuletzt_eingefügte_id', $self->id);
		// oder was auch immer....
	} 
}
```

#### `vorAktualisieren(ActiveRecord $ActiveRecord)`

Wirklich hilfreich, wenn Sie jedes Mal einige Standardwerte bei einem Update setzen müssen.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'benutzer');
	}

	geschützte Funktion vorAktualisieren(self $self) {
		// setze einige sinnvolle Standardwerte
		if(!$self->aktualisierungsdatum) {
			$self->aktualisierungsdatum = gmdate('Y-m-d');
		}
	} 
}
```

#### `nachAktualisieren(ActiveRecord $ActiveRecord)`

Vielleicht haben Sie einen Anwendungsfall zum Ändern von Daten nach dem Update?

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'benutzer');
	}

	geschützte Funktion nachAktualisieren(self $self) {
		// Sie tun, was Sie tun
		Flight::cache()->set('zuletzt_aktualisierte_benutzer_id', $self->id);
		// oder was auch immer....
	} 
}
```

#### `vorSpeichern(ActiveRecord $ActiveRecord)/nachSpeichern(ActiveRecord $ActiveRecord)`

Dies ist nützlich, wenn Sie möchten, dass Ereignisse sowohl bei Einfügungen als auch bei Aktualisierungen auftreten. Ich erspare Ihnen die lange Erklärung, aber ich bin sicher, Sie können erraten, was es ist.

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'benutzer');
	}

	geschützte Funktion vorSpeichern(self $self) {
		$self->letzte_aktualisierung = gmdate('Y-m-d H:i:s');
	} 
}
```

#### `vorLöschen(ActiveRecord $ActiveRecord)/nachLöschen(ActiveRecord $ActiveRecord)`

Ich bin mir nicht sicher, was Sie hier tun möchten, aber hier werden keine Urteile gefällt! Legen Sie einfach los!

```php
class User extends flight\ActiveRecord {
	
	public function __construct($datenbankverbindung)
	{
		parent::__construct($datenbankverbindung, 'benutzer');
	}

	geschützte Funktion vorLöschen(self $self) {
		echo 'Er war ein tapferer Soldat... :cry-face:';
	} 
}
```

## Mitwirken

Bitte gerne.

### Einrichtung

Wenn Sie dazu beitragen, stellen Sie sicher, dass Sie `composer test-coverage` ausführen, um eine 100%ige Testabdeckung aufrechtzuerhalten (das ist keine echte unit-testabdeckung, eher wie Integrationstests).

Stellen Sie außerdem sicher, dass Sie `composer beautify` und `composer phpcs` ausführen, um etwaige Formatierungsfehler zu beheben.

## Lizenz

MIT