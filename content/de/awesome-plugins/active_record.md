# Flight Active Record

Eine aktive Aufzeichnung mappt eine Datenbank-Entität auf ein PHP-Objekt. Einfach ausgedrückt, wenn Sie eine Benutzertabelle in Ihrer Datenbank haben, können Sie eine Zeile in dieser Tabelle in eine `Benutzer`-Klasse und ein `$benutzer`-Objekt in Ihrer Codebasis "übersetzen". Siehe [Beispiel](#basisk-example).

## Basisbeispiel

Nehmen wir an, Sie haben die folgende Tabelle:

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
 * @property string $password
 */ 
class Benutzer extends flight\ActiveRecord {
	public function __construct($datenbankverbindung)
	{
		// Sie können es auf diese Weise festlegen
		parent::__construct($datenbankverbindung, 'benutzer');
		// oder so
		parent::__construct($datenbankverbindung, null, [ 'tabelle' => 'benutzer']);
	}
}
```

Jetzt schauen Sie zu, wie das Magie geschieht!

```php
// für sqlite
$datenbankverbindung = new PDO('sqlite:test.db'); // dies dient nur als Beispiel, wahrscheinlich würden Sie eine echte Datenbankverbindung verwenden

// für mysql
$datenbankverbindung = new PDO('mysql:host=localhost;dbname=test_db&charset=utf8bm4', 'benutzername', 'passwort');

// oder mysqli
$datenbankverbindung = new mysqli('localhost', 'benutzername', 'passwort', 'test_db');
// oder mysqli mit nicht objektbasierter Erstellung
$datenbankverbindung = mysqli_connect('localhost', 'benutzername', 'passwort', 'test_db');

$benutzer = new Benutzer($datenbankverbindung);
$benutzer->name = 'Bobby Tables';
$benutzer->password = password_hash('ein cooles passwort');
$benutzer->einfügen();
// oder $benutzer->speichern();

echo $benutzer->id; // 1

$benutzer->name = 'Joseph Mamma';
$benutzer->password = password_hash('nochmal ein cooles passwort!!!');
$benutzer->einfügen();
// hier würde $benutzer->speichern() nicht funktionieren, es würde annehmen, dass es ein Update ist!

echo $benutzer->id; // 2
```

Und es war so einfach, einen neuen Benutzer hinzuzufügen! Jetzt, da es eine Benutzerzeile in der Datenbank gibt, wie holen Sie sie heraus?

```php
$benutzer->find(1); // finde id = 1 in der Datenbank und gib sie zurück.
echo $benutzer->name; // 'Bobby Tables'
```

Und wenn Sie alle Benutzer finden möchten?

```php
$benutzer = $benutzer->alleFinden();
```

Was ist mit einer bestimmten Bedingung?

```php
$benutzer = $benutzer->wie('name', '%mamma%')->alleFinden();
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
Stellen Sie sicher, dass Sie einfach eine PDO-Verbindung an den Konstruktor übergeben.

```php
$pdo_verbindung = new PDO('sqlite:test.db'); // dies dient nur als Beispiel, wahrscheinlich würden Sie eine echte Datenbankverbindung verwenden

$Benutzer = new Benutzer($pdo_verbindung);
```

### Flight PHP Framework
Wenn Sie das Flight PHP Framework verwenden, können Sie die ActiveRecord-Klasse als Dienst registrieren (aber das müssen Sie ehrlich gesagt nicht).

```php
Flight::register('benutzer', 'Benutzer', [ $pdo_verbindung ]);

// dann können Sie es in einem Controller, einer Funktion usw. so verwenden

Flight::benutzer()->find(1);
```

## CRUD-Funktionen

#### `find($id = null) : boolean|ActiveRecord`

Findet einen Datensatz und weist ihn dem aktuellen Objekt zu. Wenn Sie eine Art `$id` übergeben, wird es eine Abfrage über den Primärschlüssel mit diesem Wert durchführen. Wenn nichts übergeben wird, wird es einfach den ersten Datensatz in der Tabelle finden.

Zusätzlich können Sie ihm andere Hilfsmethoden übergeben, um Ihre Tabelle abzufragen.

```php
// finde einen Datensatz mit bestimmten Bedingungen im Voraus
$benutzer->nichtNull('passwort')->orderBy('id DESC')->find();

// finde einen Datensatz anhand einer bestimmten ID
$id = 123;
$benutzer->find($id);
```

#### `alleFinden(): array<int,ActiveRecord>`

Findet alle Datensätze in der von Ihnen angegebenen Tabelle.

```php
$benutzer->alleFinden();
```

#### `isHydrated(): boolean` (v0.4.0)

Gibt `true` zurück, wenn der aktuelle Datensatz hydratisiert wurde (aus der Datenbank abgerufen).

```php
$benutzer->find(1);
// wenn ein Datensatz mit Daten gefunden wird...
$benutzer->isHydrated(); // true
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
$benutzer->größerAls('id', 0)->orderBy('id desc')->find();
$benutzer->email = 'test@example.com';
$benutzer->aktualisieren();
```

#### `speichern(): boolean|ActiveRecord`

Fügt den aktuellen Datensatz in die Datenbank ein oder aktualisiert ihn. Wenn der Datensatz eine ID hat, wird er aktualisiert, andernfalls wird er eingefügt.

```php
$benutzer = new Benutzer($pdo_verbindung);
$benutzer->name = 'demo';
$benutzer->passwort = md5('demo');
$benutzer->speichern();
```

**Hinweis:** Wenn Sie Beziehungen in der Klasse definiert haben, wird es diese Beziehungen auch rekursiv speichern, wenn sie definiert, instanziiert und schmutzige Daten zum Aktualisieren haben. (v0.4.0 und höher)

#### `löschen(): boolean`

Löscht den aktuellen Datensatz aus der Datenbank.

```php
$benutzer->gt('id', 0)->orderBy('id desc')->find();
$benutzer->löschen();
```

Sie können auch mehrere Datensätze löschen, indem Sie zuerst eine Abfrage ausführen.

```php
$benutzer->wie('name', 'Bob%')->löschen();
```

#### `schmutzig(array  $schmutzig = []): ActiveRecord`

Schmutzige Daten beziehen sich auf die Daten, die in einem Datensatz geändert wurden.

```php
$benutzer->größerAls('id', 0)->orderBy('id desc')->find();

// bis zu diesem Zeitpunkt ist nichts "schmutzig".

$benutzer->email = 'test@example.com'; // jetzt wird die E-Mail als "schmutzig" betrachtet, da sie geändert wurde.
$benutzer->aktualisieren();
// jetzt gibt es keine schmutzigen Daten, weil sie aktualisiert und in der Datenbank gespeichert wurden

$benutzer->passwort = password_hash()'neuespasswort'); // jetzt ist dies schmutzig
$benutzer->schmutzig(); // ohne Parameter zu übergeben werden alle schmutzigen Einträge gelöscht.
$benutzer->aktualisieren(); // nichts wird aktualisiert, da nichts als schmutzig erfasst wurde.

$benutzer->schmutzig([ 'name' => 'etwas', 'passwort' => password_hash('ein anderes passwort') ]);
$benutzer->aktualisieren(); // sowohl name als auch passwort werden aktualisiert.
```

#### `kopierenVon(array $daten): ActiveRecord` (v0.4.0)

Dies ist ein Alias für die `schmutzig()`-Methode. Es ist etwas klarer, was Sie tun.

```php
$benutzer->kopierenVon([ 'name' => 'etwas', 'passwort' => password_hash('ein anderes passwort') ]);
$benutzer->aktualisieren(); // sowohl name als auch passwort werden aktualisiert.
```

#### `istSchmutzig(): boolean` (v0.4.0)

Gibt `true` zurück, wenn der aktuelle Datensatz geändert wurde.

```php
$benutzer->größerAls('id', 0)->orderBy('id desc')->find();
$benutzer->email = 'test@e-mail.com';
$benutzer->istSchmutzig(); // true
```

#### `zurücksetzen(bool $querydaten_einschließen = true): ActiveRecord`

Setzt den aktuellen Datensatz auf seinen ursprünglichen Zustand zurück. Dies ist wirklich nützlich, um es in Schleifenverhalten zu verwenden.
Wenn Sie `true` übergeben, werden auch die Abfragedaten zurückgesetzt, die verwendet wurden, um das aktuelle Objekt zu finden (Standardverhalten).

```php
$benutzer = $benutzer->größerAls('id', 0)->orderBy('id desc')->find();
$benutzer_firma = new BenutzerFirma($pdo_verbindung);

foreach($benutzer as $benutzer) {
	$benutzer_firma->zurücksetzen(); // mit einem sauberen Blatt beginnen
	$benutzer_firma->benutzer_id = $benutzer->id;
	$benutzer_firma->firmen_id = $einige_firmen_id;
	$benutzer_firma->einfügen();
}
```

#### `getErstelltesSql(): string` (v0.4.1)

Nachdem Sie eine `find()`, `alleFinden()`, `einfügen()`, `aktualisieren()` oder `speichern()`-Methode ausgeführt haben, können Sie das erstellte SQL abrufen und für Debugging-Zwecke verwenden.

## SQL-Abfragemethoden
#### `auswählen(string $feld1 [, string $feld2 ... ])`

Sie können nur einige der Spalten in einer Tabelle auswählen, wenn Sie möchten (es ist effizienter bei sehr breiten Tabellen mit vielen Spalten)

```php
$benutzer->auswählen('id', 'name')->find();
```

#### `von(string $tabelle)`

Sie können technisch gesehen auch eine andere Tabelle wählen! Warum auch nicht?!

```php
$benutzer->auswählen('id', 'name')->von('benutzer')->find();
```

#### `beitreten(string $tabellenname, string $beitrittsbedingung)`

Sie können sogar zu einer anderen Tabelle in der Datenbank verbinden.

```php
$benutzer->beitreten('kontakte', 'kontakte.benutzer_id = benutzer.id')->find();
```

#### `wo(string $wo_bedingungen)`

Sie können benutzerdefinierte where-Argumente festlegen (Sie können keine Parameter in dieser where-Anweisung festlegen)

```php
$benutzer->wo('id=1 AND name="demo"')->find();
```

**Sicherheitshinweis** - Sie könnten versucht sein, etwas wie `$benutzer->wo("id = '{$id}' AND name = '{$name}'")->find();` zu tun. BITTE NICHT TUN! Dies ist anfällig für sogenannte SQL-Injection-Angriffe. Es gibt viele Artikel online, bitte googeln Sie "sql injection attacks php" und Sie werden viele Artikel zu diesem Thema finden. Der richtige Umgang damit dieser Bibliothek ist anstelle dieser `wo()`-Methode, würden Sie etwas wie `$benutzer->eq('id', $id)->eq('name', $name)->find();` verwenden. Wenn Sie dies unbedingt tun müssen, hat die `PDO`-Bibliothek `$pdo->quote($var)` zum Escapen für Sie. Erst nach Verwendung von `quote()` können Sie es in einer `wo()`-Anweisung verwenden.

#### `gruppe(string $group_by_statement)/groupBy(string $group_by_statement)`

Gruppieren Sie Ihre Ergebnisse nach einer bestimmten Bedingung.

```php
$benutzer->auswählen('COUNT(*) as anzahl')->groupBy('name')->alleFinden();
```

#### `ordnen(string $order_by_statement)/orderBy(string $order_by_statement)`

Sortieren Sie die zurückgegebene Abfrage auf bestimmte Weise.

```php
$benutzer->orderBy('name DESC')->find();
```

#### `limit(string $limit)/limit(int $offset, int $limit)`

Begrenzen Sie die Anzahl der zurückgegebenen Datensätze. Wenn Sie eine zweite Zahl übergeben, wird diese als Offset verwendet, `limit` genau wie in SQL.

```php
$benutzer->orderby('name DESC')->limit(0, 10)->alleFinden();
```

## WHERE-Bedingungen
#### `gleich(string $feld, mixed $wert) / eq(string $feld, mixed $wert)`

Wo `field = $value`

```php
$benutzer->eq('id', 1)->find();
```

#### `ungleich(string $feld, mixed $wert) / ne(string $feld, mixed $wert)`

Wo `field <> $value`

```php
$benutzer->ne('id', 1)->find();
```

#### `isNull(string $feld)`

Wo `field IS NULL`

```php
$benutzer->isNull('id')->find();
```

#### `isNotNull(string $feld) / notNull(string $feld)`

Wo `field IS NOT NULL`

```php
$benutzer->isNotNull('id')->find();
```

#### `größerAls(string $feld, mixed $wert) / gt(string $feld, mixed $wert)`

Wo `field > $value`

```php
$benutzer->gt('id', 1)->find();
```

#### `kleinerAls(string $feld, mixed $wert) / lt(string $feld, mixed $wert)`

Wo `field < $value`

```php
$benutzer->lt('id', 1)->find();
```

#### `größerGleich(string $feld, mixed $wert) / ge(string $feld, mixed $wert) / gte(string $feld, mixed $wert)`

Wo `field >= $value`

```php
$benutzer->ge('id', 1)->find();
```

#### `kleinerGleich(string $feld, mixed $wert) / le(string $feld, mixed $wert) / lte(string $feld, mixed $wert)`

Wo `field <= $value`

```php
$benutzer->le('id', 1)->find();
```

#### `wie(string $feld, mixed $wert) / notLike(string $feld, mixed $wert)`

Wo `field LIKE $value` oder `field NOT LIKE $value`

```php
$benutzer->wie('name', 'de')->find();
```

#### `in(string $feld, array $werte) / notIn(string $feld, array $werte)`

Wo `field IN($value)` oder `field NOT IN($value)`

```php
$benutzer->in('id', [1, 2])->find();
```

#### `zwischen(string $feld, array $werte)`

Wo `field BETWEEN $value AND $value1`

```php
$benutzer->zwischen('id', [1, 2])->find();
```

## Beziehungen
Sie können mehrere Arten von Beziehungen mit dieser Bibliothek festlegen. Sie können eins-zu-viele- und eins-zu-eins-Beziehungen zwischen Tabellen festlegen. Dies erfordert eine kleine zusätzliche Einrichtung in der Klasse im Voraus.

Das Einstellen des `$relations`-Arrays ist nicht schwierig, aber die richtige Syntax zu erraten, kann verwirrend sein.

```php
geschütztes array $relations = [
	// Sie können den Schlüssel beliebig benennen. Der Name des ActiveRecord ist vielleicht gut. Bsp.: benutzer, kontakt, klient
	'benutzer' => [
		// erforderlich
		// self::HAS_MANY, self::HAS_ONE, self::BELONGS_TO
		self::HAS_ONE, // dies ist der Beziehungstyp

		// erforderlich
		'Einige_Klasse', // das ist die "andere" ActiveRecord-Klasse, auf die hier verwiesen wird

		// erforderlich
		// je nach Beziehungstyp
		// self::HAS_ONE = der Fremdschlüssel, der das Join referenziert
		// self::HAS_MANY = der Fremdschlüssel, der das Join referenziert
		// self::BELONGS_TO = der lokale Schlüssel, der das Join referenziert
		'lokaler_oder_fremder_schlüssel',
		// nur zur Information, das verbindet auch nur zum Primärschlüssel des "anderen" Modells

		// optional
		[ 'eq' => [ 'kunden_id', 5 ], 'auswählen' => 'COUNT(*) as anzahl', 'limit' 5 ], // zusätzliche Bedingungen, die Sie beim Zusammenfügen der Beziehung'beziehungen' => [ self::BELONGS_TO, Benutzer::class, 'benutzer_id' ],
 		'benutzer_mit_backref' => [ self::BELONGS_TO, Benutzer::class, 'benutzer_id', [], 'kontakt' ],
 	];
 	public function __construct($datenbankverbindung)
 	{
 		parent::__construct($datenbankverbindung, 'kontakte');
 	}
 }
```

Nun sind die Verweise eingerichtet, sodass Sie sie sehr einfach verwenden können!

```php
$benutzer = new Benutzer($pdo_verbindung);

// finde den neuesten Benutzer.
$benutzer->nichtNull('id')->orderBy('id desc')->find();

// erhalte Kontakte, indem Sie die Beziehung verwenden:
foreach($benutzer->kontakte as $kontakt) {
	echo $kontakt->id;
}

// oder wir können auch den anderen Weg gehen.
$kontakt = new Kontakt();

// finde einen Kontakt
$kontakt->find();

// erhalte den Benutzer, indem Sie die Beziehung verwenden:
echo $kontakt->benutzer->name; // dies ist der Benutzername
```

Ziemlich cool, oder?

## Festlegung benutzerdefinierter Daten
Manchmal müssen Sie Ihrem ActiveRecord etwas Einzigartiges anhängen, wie z.B. eine benutzerdefinierte Berechnung, die möglicherweise einfacher ist, einfach dem Objekt anzuhängen, das dann an eine Vorlage übergeben werden könnte.

#### `setBenutzerdefinierteDaten(string $feld, mixed $wert)`
Sie hängen die benutzerdefinierten Daten mit der `setBenutzerdefinierteDaten()`-Methode an.
```php
$benutzer->setBenutzerdefinierteDaten('seitenaufruf_anzahl', $seitenaufruf_anzahl);
```

Und dann verweisen Sie einfach darauf wie auf eine normale Objekteigenschaft.

```php
echo $benutzer->seitenaufruf_anzahl;
```

## Ereignisse

Ein weiteres wirklich tolles Feature dieser Bibliothek sind die Ereignisse. Ereignisse werden zu bestimmten Zeiten basierend auf bestimmten Methoden, die Sie aufrufen, ausgelöst. Sie sind sehr, sehr hilfreich, um Daten automatisch für Sie einzurichten.

#### `onConstruct(ActiveRecord $ActiveRecord, array &config)`

Dies ist wirklich hilfreich, wenn Sie eine Standardverbindung oder so etwas einrichten müssen.

```php
// index.php oder bootstrap.php
Flight::register('db', 'PDO', [ 'sqlite:test.db' ]);

//
//
//

// Benutzer.php
class Benutzer extends flight\ActiveRecord {

	geschützte Funktion onConstruct(self $self, array &$config) { // vergessen Sie nicht die & Referenz
		// Sie könnten das tun, um automatisch die Verbindung einzurichten
		$config['verbindung'] = Flight::db();
		// oder das
		$self->verbindungTransformierenUndPersistieren(Flight::db());
		
		// Sie können den Tabellennamen auch auf diese Weise festlegen.
		$config['tabelle'] = 'benutzer';
	} 
}
```

#### `vorFinden(ActiveRecord $ActiveRecord)`

Das ist wahrscheinlich nur nützlich, wenn Sie eine Abfrage-Manipulation jedes Mal benötigen.

```php
class Benutzer extends flight\ActiveRecord {
	
	Öffentliche Funktion __konstruieren($datenbankverbindung)
	{
		parent::__konstruieren($datenbankverbindung, 'benutzer');
	}

	geschützte Funktion vorFinden(self $self) {
		// führen Sie immer id >= 0 aus, wenn das Ihr Ding ist
		$self->gte('id', 0); 
	} 
}
```

#### `nachFinden(ActiveRecord $ActiveRecord)`

Dies ist wahrscheinlich nützlicher, wenn Sie jedes Mal, wenn dieser Datensatz abgerufen wird, einige Logik ausführen müssen. Müssen Sie etwas entschlüsseln? Müssen Sie jedes Mal eine benutzerdefinierte Anzahlabfrage ausführen (nicht performant, aber egal)?

```php
class Benutzer extends flight\ActiveRecord {
	
	Öffentliche Funktion __konstruieren($datenbankverbindung)
	{
		parent::__konstruieren($datenbankverbindung, 'benutzer');
	}

	geschützte Funktion nachFinden(self $self) {
		// etwas entschlüsseln
		$self->geheimnis = ihreEntschlüsselungsfunktion($self->geheimnis, $einige_schlüssel);

		// vielleicht etwas benutzerdefiniertes wie eine Abfrage speichern???
		$self->setBenutzerdefinierteDaten('anzahl_ansichten', $self->auswählen('COUNT(*) count')->von('benutzeraufrufe')->eq('benutzer_id', $self->id)['count']; 
	} 
}
```

#### `vorFindenAlles(ActiveRecord $ActiveRecord)`

Dies ist wahrscheinlich nur nützlich, wenn Sie eine Abfrage-Manipulation jedes Mal benötigen.

```php
class Benutzer extends flight\ActiveRecord {
	
	Öffentliche Funktion __konstruieren($datenbankverbindung)
	{
		parent::__konstruieren($datenbankverbindung, 'benutzer');
	}

	geschützte Funktion vorFindenAlles(self $self) {
		// immer id >= 0 ausführen, wenn das Ihr Ding ist
		$self->gte('id', 0); 
	} 
}
```

#### `nachFindenAlles(array<int,ActiveRecord> $ergebnisse)`

Ähnlich wie `nachFinden()` können Sie dies an allen Datensätzen durchführen!

```php
class Benutzer extends flight\ActiveRecord {
	
	Öffentliche Funktion __konstruieren($datenbankverbindung)
	{
		parent::__konstruieren($datenbankverbindung, 'benutzer');
	}

	geschützte Funktion nachFindenAlles(array $ergebnisse) {

		Fürjeden($ergebnisse als $self) {
			// mache etwas cooles wie nachFinden()
		}
	} 
}
```

#### `vorEinfügen(ActiveRecord $ActiveRecord)`

Wirklich hilfreich, wenn Sie jedes Mal einige Standardwerte setzen müssen.

```php
class Benutzer extends flight\ActiveRecord {
	
	Öffentliche Funktion __konstruieren($datenbankverbindung)
	{
		parent::__konstruieren($datenbankverbindung, 'benutzer');
	}

	geschützte Funktion vorEinfügen(self $self) {
		// setze einige solide Standardwerte
		if(!$self->erstelltes_datum) {
			$self->erstelltes_datum = gmdate('Y-m-d');
		}

		if(!$self->passwort) {
			$self->passwort = password_hash((string) microtime(true));
		}
	} 
}
```

#### `nachEinfügen(ActiveRecord $ActiveRecord)`

Vielleicht hast du einen Anwendungsfall, bei dem du Daten ändern musst, nachdem sie eingefügt wurden?

```php
class Benutzer extends flight\ActiveRecord {
	
	Öffentliche Funktion __konstruieren($datenbankverbindung)
	{
		parent::__konstruieren($datenbankverbindung, 'benutzer');
	}

	geschützte Funktion nachEinfügen(self $self) {
		// du machst deine Sache
		Flight::cache()->set('zuletzt_eingefügte_id', $self->id);
		// oder so weiter....
```