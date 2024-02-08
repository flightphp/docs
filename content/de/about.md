# Was ist Flight?

Flight ist ein schnelles, einfaches und erweiterbares Framework für PHP. Es ist ziemlich vielseitig und kann für den Aufbau jeder Art von Webanwendung verwendet werden. Es wurde mit Einfachheit im Hinterkopf entwickelt und ist auf eine einfache Verständlichkeit und Verwendung ausgelegt.

Flight ist ein großartiges Anfänger-Framework für diejenigen, die neu in PHP sind und lernen möchten, wie man Webanwendungen erstellt. Es ist auch ein großartiges Framework für erfahrene Entwickler, die schnell und einfach Webanwendungen erstellen möchten. Es wurde entwickelt, um leicht eine RESTful-API, eine einfache Webanwendung oder eine komplexe Webanwendung zu erstellen.

```php
<?php

// wenn mit Composer installiert
require 'vendor/autoload.php';
// oder wenn manuell per Zip-Datei installiert
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'Hallo Welt!';
});

Flight::start();
```

Ganz einfach, oder? [Erfahren Sie mehr über Flight!](learn)

## Schnellstart
Es gibt eine Beispiel-App, die Ihnen helfen kann, mit dem Flight Framework zu beginnen. Gehen Sie zu [flightphp/skeleton](https://github.com/flightphp/skeleton), um Anweisungen zum Einstieg zu erhalten! Sie können auch die Seite [Beispiele](examples) besuchen, um sich inspirieren zu lassen, was Sie mit Flight machen können.

# Community

Wir sind auf Matrix! Chatten Sie mit uns unter [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Mitwirken

Es gibt zwei Möglichkeiten, wie Sie zu Flight beitragen können:

1. Sie können zum Kern-Framework beitragen, indem Sie das [Kern-Repository](https://github.com/flightphp/core) besuchen.
1. Sie können zur Dokumentation beitragen. Diese Dokumentationswebsite wird auf [Github](https://github.com/flightphp/docs) gehostet. Wenn Sie einen Fehler bemerken oder etwas besser ausarbeiten möchten, fühlen Sie sich frei, es zu korrigieren und einen Pull-Request einzureichen! Wir versuchen, auf dem Laufenden zu bleiben, aber Aktualisierungen und Sprachübersetzungen sind willkommen.

# Anforderungen

Flight erfordert PHP 7.4 oder höher.

**Hinweis:** PHP 7.4 wird unterstützt, da zum aktuellen Zeitpunkt des Schreibens (2024) PHP 7.4 die Standardversion für einige LTS-Linux-Distributionen ist. Ein Wechsel zu PHP >8 würde bei diesen Benutzern viel Unruhe verursachen. Das Framework unterstützt auch PHP >8.

# Lizenz

Flight wird unter der [MIT](https://github.com/flightphp/core/blob/master/LICENSE)-Lizenz veröffentlicht.