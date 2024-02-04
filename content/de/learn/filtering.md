# Filterung

Flight ermöglicht es Ihnen, Methoden vor und nach ihrem Aufruf zu filtern. Es gibt keine vordefinierten Hooks, die Sie auswendig lernen müssen. Sie können jede der Standard-Framework-Methoden sowie benutzerdefinierte Methoden filtern, die Sie zugeordnet haben.

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
  // Etwas tun
});
```

Sie können einen Filter nach einer Methode ausführen, indem Sie dies tun:

```php
Flight::after('start', function (array &$params, string &$output): bool {
  // Etwas tun
});
```

Sie können beliebig viele Filter zu einer Methode hinzufügen. Sie werden in der Reihenfolge aufgerufen, in der sie deklariert sind.

Hier ist ein Beispiel des Filtervorgangs:

```php
// Eine benutzerdefinierte Methode zuordnen
Flight::map('hello', function (string $name) {
  return "Hallo, $name!";
});

// Einen vorherigen Filter hinzufügen
Flight::before('hello', function (array &$params, string &$output): bool {
  // Parameter manipulieren
  $params[0] = 'Fred';
  return true;
});

// Einen nachherigen Filter hinzufügen
Flight::after('hello', function (array &$params, string &$output): bool {
  // Ausgabe manipulieren
  $output .= " Einen schönen Tag!";
  return true;
});

// Die benutzerdefinierte Methode aufrufen
echo Flight::hello('Bob');
```

Dies sollte anzeigen:

```
Hallo Fred! Einen schönen Tag!
```

Wenn Sie mehrere Filter definiert haben, können Sie die Kette durch die Rückgabe von `false` in einer Ihrer Filterfunktionen unterbrechen:

```php
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'one';
  return true;
});

Flight::before('start', function (array &$params, string &$output): bool {
  echo 'two';

  // Dies wird die Kette beenden
  return false;
});

// Dies wird nicht aufgerufen werden
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'three';
  return true;
});
```

Hinweis: Kernmethoden wie `map` und `registrieren` können nicht gefiltert werden, da sie direkt aufgerufen und nicht dynamisch aufgerufen werden.