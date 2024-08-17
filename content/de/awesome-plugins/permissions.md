# FlightPHP/Berechtigungen

Dies ist ein Berechtigungsmodul, das in Ihren Projekten verwendet werden kann, wenn Sie mehrere Rollen in Ihrer App haben und jede Rolle eine etwas andere Funktionalität hat. Mit diesem Modul können Sie Berechtigungen für jede Rolle definieren und dann überprüfen, ob der aktuelle Benutzer die Berechtigung hat, auf eine bestimmte Seite zuzugreifen oder eine bestimmte Aktion auszuführen.

Klicken Sie [hier](https://github.com/flightphp/permissions) für das Repository auf GitHub.

Installation
-------
Führen Sie `composer require flightphp/permissions` aus und los geht's!

Verwendung
-------
Zuerst müssen Sie Ihre Berechtigungen einrichten, dann teilen Sie Ihrer App mit, was die Berechtigungen bedeuten. Letztendlich prüfen Sie Ihre Berechtigungen mit `$Permissions->has()`, `->can()` oder `is()`. `has()` und `can()` haben die gleiche Funktionalität, sind jedoch unterschiedlich benannt, um Ihren Code leichter lesbar zu machen.

## Grundbeispiel

Angenommen, Sie haben in Ihrer Anwendung eine Funktion, die überprüft, ob ein Benutzer angemeldet ist. Sie können ein Berechtigungsobjekt wie folgt erstellen:

```php
// index.php
require 'vendor/autoload.php';

// etwas Code

// dann haben Sie wahrscheinlich etwas, das Ihnen mitteilt, was die aktuelle Rolle der Person ist
// wahrscheinlich haben Sie etwas, bei dem Sie die aktuelle Rolle abrufen
// aus einer Session-Variablen, die dies definiert
// nachdem sich jemand angemeldet hat, andernfalls haben sie die Rolle 'Gast' oder 'Öffentlich'.
$current_role = 'admin';

// Berechtigungen einrichten
$permission = new \flight\Permission($current_role);
$permission->defineRule('eingeloggt', function($current_role) {
	return $current_role !== 'gast';
});

// Sie werden dieses Objekt wahrscheinlich in Flight persistieren wollen
Flight::set('berechtigung', $permission);
```

Dann in einem Controller irgendwo haben Sie möglicherweise so etwas.

```php
<?php

// irgendein Controller
class EinController {
	public function eineAktion() {
		$permission = Flight::get('berechtigung');
		if ($permission->has('eingeloggt')) {
			// etwas machen
		} else {
			// etwas anderes machen
		}
	}
}
```

Sie können dies auch verwenden, um zu verfolgen, ob sie Berechtigungen haben, um in Ihrer Anwendung etwas zu tun.
Zum Beispiel, wenn Sie eine Möglichkeit haben, dass Benutzer Beiträge in Ihrer Software interagieren können, können Sie überprüfen, ob sie Berechtigungen haben, bestimmte Aktionen auszuführen.

```php
$current_role = 'admin';

// Berechtigungen einrichten
$permission = new \flight\Permission($current_role);
$permission->defineRule('beitrag', function($current_role) {
	if($current_role === 'admin') {
		$permissions = ['erstellen', 'lesen', 'aktualisieren', 'löschen'];
	} else if($current_role === 'editor') {
		$permissions = ['erstellen', 'lesen', 'aktualisieren'];
	} else if($current_role === 'autor') {
		$permissions = ['erstellen', 'lesen'];
	} else if($current_role === 'mitwirkender') {
		$permissions = ['erstellen'];
	} else {
		$permissions = [];
	}
	return $permissions;
});
Flight::set('berechtigung', $permission);
```

Dann irgendwo in einem Controller...

```php
class BeitragController {
	public function erstellen() {
		$permission = Flight::get('berechtigung');
		if ($permission->can('beitrag.erstellen')) {
			// etwas machen
		} else {
			// etwas anderes machen
		}
	}
}
```

## Abhängigkeiten injizieren
Sie können Abhängigkeiten in den Closure einfügen, die die Berechtigungen definieren. Dies ist nützlich, wenn Sie eine Art Schalter, ID oder einen anderen Datenpunkt haben, gegen den Sie prüfen möchten. Das Gleiche gilt für Klassen->Methoden-Aufrufe, außer dass Sie die Argumente in der Methode definieren.

### Closures

```php
$Permission->defineRule('bestellung', function(string $current_role, MyDependency $MyDependency = null) {
	// ... code
});

// in Ihrer Controllerdatei
public function bestellungErstellen() {
	$MyDependency = Flight::myDependency();
	$permission = Flight::get('berechtigung');
	if ($permission->can('bestellung.erstellen', $MyDependency)) {
		// etwas machen
	} else {
		// etwas anderes machen
	}
}
```

### Klassen

```php
namespace MeinApp;

class Berechtigungen {

	public function bestellung(string $current_role, MyDependency $MyDependency = null) {
		// ... code
	}
}
```

## Verknüpfung zum Setzen von Berechtigungen mit Klassen
Sie können auch Klassen verwenden, um Ihre Berechtigungen zu definieren. Dies ist nützlich, wenn Sie viele Berechtigungen haben und Ihren Code sauber halten möchten. Sie können etwas Ähnliches wie folgt tun:
```php
<?php

// Startcode
$Berechtigungen = new \flight\Permission($current_role);
$Berechtigungen->defineRule('bestellung', 'MeinApp\Berechtigungen->bestellung');

// myapp/Berechtigungen.php
namespace MeinApp;

class Berechtigungen {

	public function bestellung(string $current_role, int $benutzer_id) {
		// Annehmen, dass Sie dies im Voraus eingerichtet haben
		/** @var \flight\database\PdoWrapper $db */
		$db = Flight::db();
		$erlaubte_berechtigungen = [ 'lesen' ]; // jeder kann eine Bestellung einsehen
		if($current_role === 'manager') {
			$erlaubte_berechtigungen[] = 'erstellen'; // Manager können Bestellungen erstellen
		}
		$ein_anderer_spezieller_schalter_aus_db = $db->fetchField('SELECT ein_anderer_spezieller_schalter FROM einstellungen WHERE id = ?', [ $benutzer_id ]);
		if($ein_anderer_spezieller_schalter_aus_db) {
			$erlaubte_berechtigungen[] = 'aktualisieren'; // Wenn der Benutzer einen speziellen Schalter hat, kann er Bestellungen aktualisieren
		}
		if($current_role === 'admin') {
			$erlaubte_berechtigungen[] = 'löschen'; // Admins können Bestellungen löschen
		}
		return $erlaubte_berechtigungen;
	}
}
```
Der interessante Teil ist, dass es auch eine Abkürzung gibt, die Sie verwenden können (die auch zwischengespeichert werden kann!!!), bei der Sie der Berechtigungsklasse einfach sagen, alle Methoden in einer Klasse in Berechtigungen zu kartieren. Also, wenn Sie eine Methode namens `bestellung()` und eine Methode namens `unternehmen()` haben, werden diese automatisch zugeordnet, sodass Sie einfach `$Berechtigungen->has('bestellung.lesen')` oder `$Berechtigungen->has('unternehmen.lesen')` ausführen können und es funktioniert. Das Definieren davon ist sehr schwierig, bleiben Sie also bei mir hier. Sie müssen nur dies tun:

Erstellen Sie die Berechtigungsklasse, die Sie zusammenfassen möchten.
```php
class MeineBerechtigungen {
	public function bestellung(string $current_role, int $bestellungs_id = 0): array {
		// Code zur Bestimmung von Berechtigungen
		return $berechtigungen_array;
	}

	public function unternehmen(string $current_role, int $unternehmen_id): array {
		// Code zur Bestimmung von Berechtigungen
		return $berechtigungen_array;
	}
}
```

Dann machen Sie die Berechtigungen mit Hilfe dieser Bibliothek auffindbar.

```php
$Berechtigungen = new \flight\Permission($current_role);
$Berechtigungen->defineRulesFromClassMethods(MeineApp\Berechtigungen::class);
Flight::set('berechtigungen', $Berechtigungen);
```

Rufen Sie schließlich die Berechtigung in Ihrem Code auf, um zu überprüfen, ob der Benutzer berechtigt ist, eine bestimmte Berechtigung auszuführen.

```php
class EinController {
	public function bestellungErstellen() {
		if(Flight::get('berechtigungen')->can('bestellung.erstellen') === false) {
			die('Sie können keine Bestellung erstellen. Entschuldigung!');
		}
	}
}
```

### Zwischenspeicherung

Um die Zwischenspeicherung zu aktivieren, sehen Sie sich die einfache [wruczak/phpfilecache](https://docs.flightphp.com/awesome-plugins/php-file-cache) Bibliothek an. Ein Beispiel zur Aktivierung finden Sie unten.
```php

// dieses $app kann Teil Ihres Codes sein oder
// Sie können einfach null übergeben und es wird
// aus Flight::app() im Konstruktor abgerufen
$app = Flight::app();

// Derzeit akzeptiert es dies als Dateipuffer. Andere können in Zukunft leicht hinzugefügt werden.
$Cache = new Wruczek\PhpFileCache\PhpFileCache;

$Berechtigungen = new \flight\Permission($current_role, $app, $Cache);
$Berechtigungen->defineRulesFromClassMethods(MeineApp\Berechtigungen::class, 3600); // 3600 gibt an, wie viele Sekunden diese Zwischenspeicherung gültig ist. Lassen Sie dies weg, um die Zwischenspeicherung nicht zu verwenden
```