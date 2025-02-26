# Was ist Flight?

Flight ist ein schnelles, einfaches, erweiterbares Framework für PHP. Es ist recht vielseitig und kann zum Erstellen aller Arten von Webanwendungen verwendet werden. Es wurde mit dem Ziel der Einfachheit entwickelt und ist so geschrieben, dass es leicht zu verstehen und zu verwenden ist.

Flight ist ein großartiges Einsteiger-Framework für diejenigen, die neu in PHP sind und lernen möchten, wie man Webanwendungen erstellt. Es ist auch ein großartiges Framework für erfahrene Entwickler, die mehr Kontrolle über ihre Webanwendungen wünschen. Es wurde entwickelt, um einfach eine RESTful API, eine einfache Webanwendung oder eine komplexe Webanwendung zu erstellen.

## Schnellstart

```php
<?php

// wenn mit composer installiert
require 'vendor/autoload.php';
// oder wenn manuell über Zip-Datei installiert
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'Hallo Welt!';
});

Flight::route('/json', function() {
  Flight::json(['Hallo' => 'Welt']);
});

Flight::start();
```

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube-Video-Player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">Einfach genug, oder?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Erfahren Sie mehr über Flight in der Dokumentation!</a>
    </div>
  </div>
</div>

### Skeleton/Boilerplate App

Es gibt eine Beispielanwendung, die Ihnen helfen kann, mit dem Flight Framework zu beginnen. Gehen Sie zu [flightphp/skeleton](https://github.com/flightphp/skeleton) für Anweisungen, wie Sie loslegen können! Sie können auch die [Beispiele](examples) Seite besuchen, um Inspiration für einige der Dinge zu erhalten, die Sie mit Flight tun können.

# Community

Wir sind auf Matrix Chat, sprechen Sie mit uns unter [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Mitwirken

Es gibt zwei Möglichkeiten, wie Sie zu Flight beitragen können:

1. Sie können zum Kern-Framework beitragen, indem Sie das [Kern-Repository](https://github.com/flightphp/core) besuchen.
2. Sie können zur Dokumentation beitragen. Diese Dokumentationswebsite wird auf [Github](https://github.com/flightphp/docs) gehostet. Wenn Sie einen Fehler bemerken oder etwas besser ausarbeiten möchten, können Sie es gerne korrigieren und einen Pull-Request einreichen! Wir versuchen, alles im Blick zu behalten, aber Updates und Übersetzungen sind willkommen.

# Anforderungen

Flight erfordert PHP 7.4 oder höher.

**Hinweis:** PHP 7.4 wird unterstützt, da es zur aktuellen Zeit des Schreibens (2024) die standardmäßige Version für einige LTS-Linux-Distributionen ist. Ein Umstieg auf PHP >8 würde bei diesen Benutzern zu viel Unmut führen. Das Framework unterstützt auch PHP >8.

# Lizenz

Flight wird unter der [MIT](https://github.com/flightphp/core/blob/master/LICENSE) Lizenz veröffentlicht.