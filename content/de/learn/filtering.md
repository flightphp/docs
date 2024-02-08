# Filtern

Flight ermöglicht es Ihnen, Methoden vor und nach ihrem Aufruf zu filtern. Es gibt keine vordefinierten Hooks, die Sie sich merken müssen. Sie können beliebige der Standardframework-Methoden sowie benutzerdefinierte Methoden filtern, die Sie zugeordnet haben.

Eine Filterfunktion sieht so aus:

```php
function (array &$params, string &$output): bool {
  // Filtercode
}
```

Unter Verwendung der übergebenen Variablen können Sie die Eingabeparameter und/oder die Ausgabe manipulieren.

Sie können einen Filter vor einer Methode ausführen, indem Sie dies tun:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  // Etwas machen
});
```

Sie können einen Filter nach einer Methode ausführen, indem Sie dies tun:

```php
Flight::after('start', function (array &$params, string &$output): bool {
  // Etwas machen
});
```

Sie können beliebig viele Filter zu einer Methode hinzufügen. Sie werden in der Reihenfolge aufgerufen, in der sie deklariert sind.

Hier ist ein Beispiel des Filtervorgangs:

```php
// Eine benutzerdefinierte Methode zuordnen
Flight::map('hallo', function (string $name) {
  return "Hallo, $name!";
});

// Einen Vorfiler hinzufügen
Flight::before('hallo', function (array &$params, string &$output): bool {
  // Den Parameter manipulieren
  $params[0] = 'Fred';
  return true;
});

// Einen Nachfilter hinzufügen
Flight::after('hallo', function (array &$params, string &$output): bool {
  // Die Ausgabe manipulieren
  $output .= " Einen schönen Tag!";
  return true;
});

// Die benutzerdefinierte Methode aufrufen
echo Flight::hallo('Bob');
```

Dies sollte anzeigen:

```
Hallo Fred! Einen schönen Tag!
```

Wenn Sie mehrere Filter definiert haben, können Sie die Kette unterbrechen, indem Sie in einer Ihrer Filterfunktionen `false` zurückgeben:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'eins';
  return true;
});

Flight::before('start', function (array &$params, string &$output): bool {
  echo 'zwei';

  // Dies wird die Kette beenden
  return false;
});

// Dies wird nicht aufgerufen
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'drei';
  return true;
});
```

Hinweis: Kernmethoden wie `map` und `register` können nicht gefiltert werden, da sie direkt aufgerufen und nicht dynamisch aufgerufen werden.