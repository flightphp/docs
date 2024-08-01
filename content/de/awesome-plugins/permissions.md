```de
# FlightPHP/Berechtigungen

Dies ist ein Berechtigungsmodul, das in Ihren Projekten verwendet werden kann, wenn Sie mehrere Rollen in Ihrer App haben und jede Rolle eine etwas andere Funktionalität hat. Mit diesem Modul können Sie Berechtigungen für jede Rolle definieren und dann überprüfen, ob der aktuelle Benutzer die Berechtigung hat, auf eine bestimmte Seite zuzugreifen oder eine bestimmte Aktion auszuführen.

Installation
-------
Führen Sie `composer require flightphp/permissions` aus und los geht's!

Verwendung
-------
Zuerst müssen Sie Ihre Berechtigungen einrichten und dann Ihrer App mitteilen, was die Berechtigungen bedeuten. Letztendlich überprüfen Sie Ihre Berechtigungen mit `$Permissions->has()`, `->can()` oder `is()`. `has()` und `can()` haben die gleiche Funktionalität, sind jedoch unterschiedlich benannt, um Ihren Code besser lesbar zu machen.

## Grundbeispiel

Nehmen wir an, Sie haben in Ihrer Anwendung eine Funktion, die überprüft, ob ein Benutzer angemeldet ist. Sie können ein Berechtigungsobjekt wie folgt erstellen:

```php
// index.php
require 'vendor/autoload.php';

// etwas Code

// dann haben Sie wahrscheinlich etwas, das Ihnen sagt, welche die aktuelle Rolle der Person ist
// wahrscheinlich haben Sie etwas, wo Sie die aktuelle Rolle abrufen
// von einer Session-Variablen, die dies definiert
// nachdem sich jemand angemeldet hat, sonst haben sie eine "Gast" oder "öffentliche" Rolle.
$current_role = 'admin';

// Berechtigungen einrichten
$permission = new \flight\Permission($current_role);
$permission->defineRule('angemeldet', function($current_role) {
	return $current_role !== 'guest';
});

// Sie möchten dieses Objekt wahrscheinlich irgendwo in Flight behalten
Flight::set('permission', $permission);
```

Dann in einem Controller irgendwo, könnten Sie so etwas haben.

```php
<?php

// etwas Controller
class SomeController {
	public function someAction() {
		$permission = Flight::get('permission');
		if ($permission->has('angemeldet')) {
			// etwas tun
		} else {
			// etwas anderes tun
		}
	}
}
```

Sie können dies auch verwenden, um zu überprüfen, ob sie die Berechtigung haben, in Ihrer Anwendung etwas zu tun.
Beispielsweise, wenn Sie eine Möglichkeit haben, dass Benutzer mit dem Posten in Ihrer Software interagieren können, können Sie überprüfen, ob sie die Berechtigung haben, bestimmte Aktionen auszuführen.

```php
$current_role = 'admin';

// Berechtigungen einrichten
$permission = new \flight\Permission($current_role);
$permission->defineRule('post', function($current_role) {
	if($current_role === 'admin') {
		$permissions = ['erstellen', 'lesen', 'aktualisieren', 'löschen'];
	} else if($current_role === 'editor') {
		$permissions = ['erstellen', 'lesen', 'aktualisieren'];
	} else if($current_role === 'autor') {
		$permissions = ['erstellen', 'lesen'];
	} else if($current_role === 'beitragender') {
		$permissions = ['erstellen'];
	} else {
		$permissions = [];
	}
	return $permissions;
});
Flight::set('permission', $permission);
```

Dann in einem Controller irgendwo...

```php
class PostController {
	public function create() {
		$permission = Flight::get('permission');
		if ($permission->can('post.erstellen')) {
			// etwas tun
		} else {
			// etwas anderes tun
		}
	}
}
```

## Abhängigkeiten einspritzen
Sie können Abhängigkeiten in den Closure einspritzen, die die Berechtigungen definieren. Dies ist nützlich, wenn Sie einen Schalter, eine ID oder einen anderen Datenpunkt haben, gegen den Sie prüfen möchten. Das Gleiche funktioniert auch für Klassen->Methoden-Aufrufe, außer dass Sie die Argumente in der Methode definieren.

### Closures

```php
$Permission->defineRule('bestellung', function(string $current_role, MyDependency $MyDependency = null) {
	// ... code
});

// in Ihrer Controllerdatei
public function createOrder() {
	$MyDependency = Flight::myDependency();
	$permission = Flight::get('permission');
	if ($permission->can('order.erstellen', $MyDependency)) {
		// etwas tun
	} else {
		// etwas anderes tun
	}
}
```

### Klassen

```php
namespace MyApp;

class Permissions {

	public function order(string $current_role, MyDependency $MyDependency = null) {
		// ... code
	}
}
```

## Abkürzung zur Festlegung von Berechtigungen mit Klassen
Sie können auch Klassen verwenden, um Ihre Berechtigungen zu definieren. Dies ist nützlich, wenn Sie viele Berechtigungen haben und Ihren Code sauber halten möchten. Sie können so etwas tun wie:

```php
<?php

// Bootstrap-Code
$Permissions = new \flight\Permission($current_role);
$Permissions->defineRule('bestellung', 'MyApp\Permissions->order');

// myapp/Permissions.php
namespace MyApp;

class Permissions {

	public function order(string $current_role, int $user_id) {
		// Angenommen, Sie haben dies zuvor eingerichtet
		/** @var \flight\database\PdoWrapper $db */
		$db = Flight::db();
		$allowed_permissions = [ 'lesen' ]; // jeder kann eine Bestellung anzeigen
		if($current_role === 'manager') {
			$allowed_permissions[] = 'erstellen'; // Manager können Bestellungen erstellen
		}
		$some_special_toggle_from_db = $db->fetchField('SELECT some_special_toggle FROM settings WHERE id = ?', [ $user_id ]);
		if($some_special_toggle_from_db) {
			$allowed_permissions[] = 'aktualisieren'; // Wenn der Benutzer einen speziellen Schalter hat, kann er Bestellungen aktualisieren
		}
		if($current_role === 'admin') {
			$allowed_permissions[] = 'löschen'; // Admins können Bestellungen löschen
		}
		return $allowed_permissions;
	}
}
```
Der coole Teil ist, dass es auch eine Abkürzung gibt, die Sie verwenden können (die auch gecached werden kann!!!), bei der Sie der Berechtigungsklasse einfach mitteilen, alle Methoden in einer Klasse in Berechtigungen zu überführen. Wenn Sie also eine Methode mit dem Namen `bestellung()` und eine Methode mit dem Namen `firma()` haben, werden diese automatisch zugeordnet, sodass Sie einfach `$Permissions->has('bestellung.lesen')` oder `$Permissions->has('firma.lesen')` ausführen können und es funktioniert. Die Definition davon ist sehr kompliziert, also bleiben Sie dran. Sie müssen einfach Folgendes tun:

Erstellen Sie die Berechtigungsklasse, die Sie zusammenfassen möchten.
```php
class MyPermissions {
	public function order(string $current_role, int $order_id = 0): array {
		// Code zur Bestimmung von Berechtigungen
		return $permissions_array;
	}

	public function company(string $current_role, int $company_id): array {
		// Code zur Bestimmung von Berechtigungen
		return $permissions_array;
	}
}
```

Machen Sie dann die Berechtigungen mithilfe dieser Bibliothek auffindbar.

```php
$Permissions = new \flight\Permission($current_role);
$Permissions->defineRulesFromClassMethods(MyApp\Permissions::class);
Flight::set('permissions', $Permissions);
```

Rufen Sie schließlich die Berechtigung in Ihrem Code auf, um zu überprüfen, ob der Benutzer berechtigt ist, eine bestimmte Berechtigung auszuführen.

```php
class SomeController {
	public function createOrder() {
		if(Flight::get('permissions')->can('order.erstellen') === false) {
			die('Sie können keine Bestellung erstellen. Entschuldigung!');
		}
	}
}
```

### Caching

Um das Caching zu aktivieren, sehen Sie sich die einfache [wruczak/phpfilecache](https://docs.flightphp.com/awesome-plugins/php-file-cache) Bibliothek an. Ein Beispiel zur Aktivierung finden Sie unten.
```php

// dieses $app kann Teil Ihres Codes sein, oder
// Sie können einfach null übergeben und es wird
// aus Flight::app() im Konstruktor abgerufen
$app = Flight::app();

// Derzeit akzeptiert dies dies als File-Cache. Andere können in Zukunft
// leicht hinzugefügt werden.
$Cache = new Wruczek\PhpFileCache\PhpFileCache;

$Permissions = new \flight\Permission($current_role, $app, $Cache);
$Permissions->defineRulesFromClassMethods(MyApp\Permissions::class, 3600); // 3600 ist die Anzahl der Sekunden, für die dies zwischengespeichert wird. Lassen Sie dies aus, um kein Caching zu verwenden
```

Und los geht's!
```