# JSON Wrapper

## Übersicht

Die `Json`-Klasse in Flight bietet eine einfache, konsistente Möglichkeit, JSON-Daten in Ihrer Anwendung zu kodieren und zu dekodieren. Sie umhüllt die nativen JSON-Funktionen von PHP mit besserer Fehlerbehandlung und einigen hilfreichen Standardeinstellungen, was die Arbeit mit JSON einfacher und sicherer macht.

## Verständnis

Die Arbeit mit JSON ist in modernen PHP-Apps sehr üblich, insbesondere beim Aufbau von APIs oder der Behandlung von AJAX-Anfragen. Die `Json`-Klasse zentralisiert alle Ihre JSON-Kodierungen und -Dekodierungen, sodass Sie sich keine Gedanken über seltsame Randfälle oder kryptische Fehler aus den integrierten Funktionen von PHP machen müssen.

Wichtige Funktionen:
- Konsistente Fehlerbehandlung (wirft Ausnahmen bei Fehlern)
- Standardoptionen für Kodierung/Dekodierung (wie unentfesselte Schrägstriche)
- Hilfsmethoden für schöne Ausgabe und Validierung

## Grundlegende Verwendung

### Daten zu JSON kodieren

Um PHP-Daten in einen JSON-String umzuwandeln, verwenden Sie `Json::encode()`:

```php
use flight\util\Json;

$data = [
  'framework' => 'Flight',
  'version' => 3,
  'features' => ['routing', 'views', 'extending']
];

$json = Json::encode($data);
echo $json;
// Ausgabe: {"framework":"Flight","version":3,"features":["routing","views","extending"]}
```

Falls die Kodierung fehlschlägt, erhalten Sie eine Ausnahme mit einer hilfreichen Fehlermeldung.

### Schöne Ausgabe

Möchten Sie, dass Ihr JSON lesbar für Menschen ist? Verwenden Sie `prettyPrint()`:

```php
echo Json::prettyPrint($data);
/*
{
  "framework": "Flight",
  "version": 3,
  "features": [
    "routing",
    "views",
    "extending"
  ]
}
*/
```

### JSON-Strings dekodieren

Um einen JSON-String zurück in PHP-Daten umzuwandeln, verwenden Sie `Json::decode()`:

```php
$json = '{"framework":"Flight","version":3}';
$data = Json::decode($json);
echo $data->framework; // Ausgabe: Flight
```

Wenn Sie ein assoziatives Array anstelle eines Objekts möchten, übergeben Sie `true` als zweiten Argument:

```php
$data = Json::decode($json, true);
echo $data['framework']; // Ausgabe: Flight
```

Falls die Dekodierung fehlschlägt, erhalten Sie eine Ausnahme mit einer klaren Fehlermeldung.

### JSON validieren

Überprüfen Sie, ob ein String gültiges JSON ist:

```php
if (Json::isValid($json)) {
  // Es ist gültig!
} else {
  // Kein gültiges JSON
}
```

### Letzten Fehler abrufen

Wenn Sie die letzte JSON-Fehlermeldung überprüfen möchten (aus den nativen PHP-Funktionen):

```php
$error = Json::getLastError();
if ($error !== '') {
  echo "Letzter JSON-Fehler: $error";
}
```

## Erweiterte Verwendung

Sie können Kodierungs- und Dekodierungsoptionen anpassen, wenn Sie mehr Kontrolle benötigen (siehe [PHP's json_encode-Optionen](https://www.php.net/manual/en/json.constants.php)):

```php
// Kodieren mit HEX_TAG-Option
$json = Json::encode($data, JSON_HEX_TAG);

// Dekodieren mit benutzerdefinierter Tiefe
$data = Json::decode($json, false, 1024);
```

## Siehe auch

- [Collections](/learn/collections) - Für die Arbeit mit strukturierten Daten, die leicht in JSON umgewandelt werden können.
- [Configuration](/learn/configuration) - Wie Sie Ihre Flight-App konfigurieren.
- [Extending](/learn/extending) - Wie Sie eigene Hilfsmethoden hinzufügen oder Kernklassen überschreiben.

## Fehlerbehebung

- Wenn die Kodierung oder Dekodierung fehlschlägt, wird eine Ausnahme geworfen – umschließen Sie Ihre Aufrufe mit try/catch, wenn Sie Fehler elegant handhaben möchten.
- Wenn Sie unerwartete Ergebnisse erhalten, überprüfen Sie Ihre Daten auf zirkuläre Referenzen oder Nicht-UTF8-Zeichen.
- Verwenden Sie `Json::isValid()`, um zu überprüfen, ob ein String gültiges JSON ist, bevor Sie dekodieren.

## Änderungsprotokoll

- v3.16.0 - JSON-Wrapper-Hilfsklasse hinzugefügt.