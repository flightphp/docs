# Filterung

Flight ermöglicht es Ihnen, Methoden vor und nach deren Aufruf zu filtern. Es sind keine vordefinierten Hooks erforderlich, die Sie auswendig lernen müssen. Sie können alle standardmäßigen Framework-Methoden sowie benutzerdefinierte Methoden filtern, die Sie zugeordnet haben.

Eine Filterfunktion sieht so aus:

```php
function (array &$params, string &$output): bool {
  // Filtercode
}
```

Unter Verwendung der übergebenen Variablen können Sie die Eingabeparameter und/oder die Ausgabe manipulieren.

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

Sie können beliebig viele Filter zu einer Methode hinzufügen. Sie werden in der Reihenfolge aufgerufen, in der sie deklariert sind.

Hier ist ein Beispiel des Filterungsprozesses:

```php
// Weise eine benutzerdefinierte Methode zu
Flight::map('hello', function (string $name) {
  return "Hallo, $name!";
});

// Fügen Sie einen vorherigen Filter hinzu
Flight::before('hello', function (array &$params, string &$output): bool {
  // Parameter manipulieren
  $params[0] = 'Fred';
  return true;
});

// Fügen Sie einen nachfolgenden Filter hinzu
Flight::after('hello', function (array &$params, string &$output): bool {
  // Ausgabe manipulieren
  $output .= " Einen schönen Tag!";
  return true;
});

// Rufen Sie die benutzerdefinierte Methode auf
echo Flight::hello('Bob');
```

Dies sollte angezeigt werden:

```
Hallo Fred! Einen schönen Tag!
```

Wenn Sie mehrere Filter definiert haben, können Sie die Kette unterbrechen, indem Sie in einer Ihrer Filterfunktionen `false` zurückgeben:

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

// Dies wird nicht aufgerufen
Flight::before('start', function (array &$params, string &$output): bool {
  echo 'three';
  return true;
});
```

Hinweis: Kernmethoden wie `map` und `register` können nicht gefiltert werden, da sie direkt aufgerufen und nicht dynamisch aufgerufen werden.