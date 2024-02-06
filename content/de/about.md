# Was ist Flight?

Flight ist ein schnelles, einfaches, erweiterbares Framework für PHP. Es ist ziemlich vielseitig und kann für den Aufbau jeder Art von Webanwendung verwendet werden. Es ist auf Einfachheit ausgelegt und so geschrieben, dass es einfach zu verstehen und zu verwenden ist.

Flight ist ein großartiges Einsteiger-Framework für diejenigen, die neu in PHP sind und lernen möchten, wie man Webanwendungen erstellt. Es ist auch ein großartiges Framework für erfahrene Entwickler, die Webanwendungen schnell und einfach erstellen möchten. Es ist darauf ausgelegt, einfach eine RESTful-API, eine einfache Webanwendung oder eine komplexe Webanwendung zu erstellen.

```php
<?php

// wenn mit Composer installiert
require 'vendor/autoload.php';
// oder wenn manuell über Zip-Datei installiert
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'Hallo Welt!';
});

Flight::start();
```

Ganz einfach, oder? [Erfahren Sie mehr über Flight!](learn)

## Schnellstart
Es gibt eine Beispiel-App, die Ihnen den Einstieg in das Flight Framework erleichtern kann. Gehen Sie zu [flightphp/skeleton](https://github.com/flightphp/skeleton) für Anweisungen zum Einstieg! Sie können auch die [Beispiele](examples) Seite besuchen, um sich inspirieren zu lassen, was Sie mit Flight tun können.

# Gemeinschaft

Wir sind auf Matrix! Unterhalten Sie sich mit uns unter [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Beitrag

Es gibt zwei Möglichkeiten, wie Sie zu Flight beitragen können:

1. Sie können zum Kernframework beitragen, indem Sie das [Kern-Repository](https://github.com/flightphp/core) besuchen.
1. Sie können zur Dokumentation beitragen. Diese Dokumentationswebsite wird auf [Github](https://github.com/flightphp/docs) gehostet. Wenn Sie einen Fehler bemerken oder etwas besser ausarbeiten möchten, können Sie es gerne korrigieren und einen Pull-Request senden! Wir versuchen, auf dem neuesten Stand zu bleiben, aber Updates und Sprachübersetzungen sind willkommen.

# Anforderungen

Flight erfordert PHP 7.4 oder höher.

**Hinweis:** PHP 7.4 wird unterstützt, da zum Zeitpunkt des Verfassens (2024) PHP 7.4 die Standardversion für einige LTS-Linux-Distributionen ist. Ein Wechsel zu PHP >8 würde bei diesen Benutzern viele Probleme verursachen. Das Framework unterstützt außerdem PHP >8.

# Lizenz

Flight wird unter der [MIT](https://github.com/flightphp/core/blob/master/LICENSE)-Lizenz veröffentlicht.