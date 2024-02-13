# Was ist Flight?

Flight ist ein schnelles, einfaches und erweiterbares Framework für PHP. Es ist ziemlich vielseitig und kann für den Aufbau jeder Art von Webanwendung verwendet werden. Es wurde mit Einfachheit im Sinn entwickelt und ist so geschrieben, dass es einfach zu verstehen und zu verwenden ist.

Flight ist ein großartiges Anfänger-Framework für diejenigen, die neu in PHP sind und lernen möchten, wie man Webanwendungen erstellt. Es ist auch ein großartiges Framework für erfahrene Entwickler, die mehr Kontrolle über ihre Webanwendungen haben möchten. Es ist konzipiert, um einfach eine RESTful API, eine einfache Webanwendung oder eine komplexe Webanwendung zu erstellen.

## Schnellstart

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

Einfach genug, oder? [Erfahren Sie mehr über Flight in der Dokumentation!](learn)

### Skelett/Boilerplate-App

Es gibt eine Beispielanwendung, die Ihnen helfen kann, mit dem Flight Framework zu beginnen. Gehen Sie zu [flightphp/skeleton](https://github.com/flightphp/skeleton) für Anweisungen, wie Sie starten können! Sie können auch die [Beispiele](examples) Seite besuchen, um sich inspirieren zu lassen, was Sie mit Flight tun können.

# Gemeinschaft

Wir sind auf Matrix! Chatten Sie mit uns unter [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Mitwirken

Es gibt zwei Möglichkeiten, wie Sie zu Flight beitragen können:

1. Sie können zum Kernframework beitragen, indem Sie das [Kernrepository](https://github.com/flightphp/core) besuchen.
1. Sie können zur Dokumentation beitragen. Diese Dokumentationswebsite wird auf [Github](https://github.com/flightphp/docs) gehostet. Wenn Sie einen Fehler bemerken oder etwas besser ausarbeiten möchten, können Sie dies gerne korrigieren und einen Pull Request einreichen! Wir versuchen, mit den Dingen Schritt zu halten, aber Aktualisierungen und Sprachübersetzungen sind willkommen.

# Anforderungen

Flight erfordert PHP 7.4 oder höher.

**Hinweis:** PHP 7.4 wird unterstützt, weil zum aktuellen Zeitpunkt des Verfassens (2024) PHP 7.4 die Standardversion für einige LTS Linux-Distributionen ist. Ein Wechsel zu PHP >8 würde für diese Benutzer viele Probleme verursachen. Das Framework unterstützt auch PHP >8.

# Lizenz

Flight wird unter der [MIT](https://github.com/flightphp/core/blob/master/LICENSE)-Lizenz veröffentlicht.