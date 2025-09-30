# Filtering

## Überblick

Flight ermöglicht es Ihnen, [gemappte Methoden](/learn/extending) vor und nach ihrem Aufruf zu filtern.

## Verständnis
Es gibt keine vordefinierten Hooks, die Sie merken müssen. Sie können alle Standard-Framework-Methoden sowie alle benutzerdefinierten Methoden filtern, die Sie gemappt haben.

Eine Filterfunktion sieht so aus:

```php
/**
 * @param array $params Die an die gefilterte Methode übergebenen Parameter.
 * @param string $output (nur v2 Output Buffering) Die Ausgabe der gefilterten Methode.
 * @return bool Geben Sie true/void zurück oder geben Sie nichts zurück, um die Kette fortzusetzen, false, um die Kette zu unterbrechen.
 */
function (array &$params, string &$output): bool {
  // Filtercode
}
```

Mit den übergebenen Variablen können Sie die Eingabeparameter und/oder die Ausgabe manipulieren.

Sie können einen Filter vor einer Methode ausführen, indem Sie Folgendes tun:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  // Etwas tun
});
```

Sie können einen Filter nach einer Methode ausführen, indem Sie Folgendes tun:

```php
Flight::after('start', function (array &$params, string &$output): bool {
  // Etwas tun
});
```

Sie können so viele Filter wie gewünscht zu jeder Methode hinzufügen. Sie werden in der Reihenfolge aufgerufen, in der sie deklariert wurden.

Hier ist ein Beispiel für den Filterprozess:

```php
// Eine benutzerdefinierte Methode mappen
Flight::map('hello', function (string $name) {
  return "Hello, $name!";
});

// Einen Before-Filter hinzufügen
Flight::before('hello', function (array &$params, string &$output): bool {
  // Den Parameter manipulieren
  $params[0] = 'Fred';
  return true;
});

// Einen After-Filter hinzufügen
Flight::after('hello', function (array &$params, string &$output): bool {
  // Die Ausgabe manipulieren
  $output .= " Have a nice day!";
  return true;
});

// Die benutzerdefinierte Methode aufrufen
echo Flight::hello('Bob');
```

Dies sollte anzeigen:

```
Hello Fred! Have a nice day!
```

Wenn Sie mehrere Filter definiert haben, können Sie die Kette unterbrechen, indem Sie `false` in einer Ihrer Filterfunktionen zurückgeben:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'one';
  return true;
});

Flight::before('start', function (array &$params, string &$output): bool {
  echo 'two';

  // Dies beendet die Kette
  return false;
});

// Dies wird nicht aufgerufen
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'three';
  return true;
});
```

> **Hinweis:** Kernmethoden wie `map` und `register` können nicht gefiltert werden, da sie direkt aufgerufen und nicht dynamisch aufgerufen werden. Siehe [Erweiterung von Flight](/learn/extending) für weitere Informationen.

## Siehe auch
- [Erweiterung von Flight](/learn/extending)

## Fehlerbehebung
- Stellen Sie sicher, dass Sie `false` aus Ihren Filterfunktionen zurückgeben, wenn Sie möchten, dass die Kette stoppt. Wenn Sie nichts zurückgeben, wird die Kette fortgesetzt.

## Changelog
- v2.0 - Erste Veröffentlichung.