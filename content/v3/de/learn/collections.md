# Collections

## Überblick

Die `Collection`-Klasse in Flight ist ein nützliches Hilfsprogramm zum Verwalten von Datensätzen. Sie ermöglicht den Zugriff und die Manipulation von Daten mit Array- und Objekt-Notation, was Ihren Code sauberer und flexibler macht.

## Verständnis

Eine `Collection` ist im Wesentlichen eine Umhüllung um ein Array, aber mit zusätzlichen Fähigkeiten. Sie können sie wie ein Array verwenden, darüber iterieren, die Anzahl ihrer Elemente zählen und sogar auf Elemente zugreifen, als wären sie Objekteigenschaften. Dies ist besonders nützlich, wenn Sie strukturierte Daten in Ihrer App weitergeben möchten oder Ihren Code lesbarer gestalten wollen.

Collections implementieren mehrere PHP-Schnittstellen:
- `ArrayAccess` (damit Sie Array-Syntax verwenden können)
- `Iterator` (damit Sie mit `foreach` iterieren können)
- `Countable` (damit Sie `count()` verwenden können)
- `JsonSerializable` (damit Sie einfach in JSON umwandeln können)

## Grundlegende Verwendung

### Erstellen einer Collection

Sie können eine Collection erstellen, indem Sie einfach ein Array an ihren Konstruktor übergeben:

```php
use flight\util\Collection;

$data = [
  'name' => 'Flight',
  'version' => 3,
  'features' => ['routing', 'views', 'extending']
];

$collection = new Collection($data);
```

### Zugriff auf Elemente

Sie können auf Elemente mit Array- oder Objekt-Notation zugreifen:

```php
// Array-Notation
echo $collection['name']; // Ausgabe: FlightPHP

// Objekt-Notation
echo $collection->version; // Ausgabe: 3
```

Wenn Sie versuchen, auf einen Schlüssel zuzugreifen, der nicht existiert, erhalten Sie `null` anstelle eines Fehlers.

### Setzen von Elementen

Sie können Elemente mit beiden Notationen setzen:

```php
// Array-Notation
$collection['author'] = 'Mike Cao';

// Objekt-Notation
$collection->license = 'MIT';
```

### Überprüfen und Entfernen von Elementen

Überprüfen Sie, ob ein Element existiert:

```php
if (isset($collection['name'])) {
  // Etwas tun
}

if (isset($collection->version)) {
  // Etwas tun
}
```

Entfernen Sie ein Element:

```php
unset($collection['author']);
unset($collection->license);
```

### Iterieren über eine Collection

Collections sind iterierbar, sodass Sie sie in einer `foreach`-Schleife verwenden können:

```php
foreach ($collection as $key => $value) {
  echo "$key: $value\n";
}
```

### Zählen von Elementen

Sie können die Anzahl der Elemente in einer Collection zählen:

```php
echo count($collection); // Ausgabe: 4
```

### Alle Schlüssel oder Daten abrufen

Alle Schlüssel abrufen:

```php
$keys = $collection->keys(); // ['name', 'version', 'features', 'license']
```

Alle Daten als Array abrufen:

```php
$data = $collection->getData();
```

### Collection leeren

Alle Elemente entfernen:

```php
$collection->clear();
```

### JSON-Serialisierung

Collections können einfach in JSON umgewandelt werden:

```php
echo json_encode($collection);
// Ausgabe: {"name":"FlightPHP","version":3,"features":["routing","views","extending"],"license":"MIT"}
```

## Erweiterte Verwendung

Sie können das interne Daten-Array vollständig ersetzen, falls benötigt:

```php
$collection->setData(['foo' => 'bar']);
```

Collections sind besonders nützlich, wenn Sie strukturierte Daten zwischen Komponenten weitergeben möchten oder eine objektorientiertere Schnittstelle für Array-Daten bereitstellen wollen.

## Siehe auch

- [Requests](/learn/requests) - Erfahren Sie, wie Sie HTTP-Anfragen handhaben und wie Collections zur Verwaltung von Anfragedaten verwendet werden können.
- [PDO Wrapper](/learn/pdo-wrapper) - Erfahren Sie, wie Sie den PDO-Wrapper in Flight verwenden und wie Collections zur Verwaltung von Datenbankergebnissen genutzt werden können.

## Fehlerbehebung

- Wenn Sie versuchen, auf einen nicht existierenden Schlüssel zuzugreifen, erhalten Sie `null` anstelle eines Fehlers.
- Denken Sie daran, dass Collections nicht rekursiv sind: Verschachtelte Arrays werden nicht automatisch in Collections umgewandelt.
- Wenn Sie die Collection zurücksetzen müssen, verwenden Sie `$collection->clear()` oder `$collection->setData([])`.

## Änderungsprotokoll

- v3.0 - Verbesserte Typ-Hinweise und Unterstützung für PHP 8+.
- v1.0 - Erste Veröffentlichung der Collection-Klasse.