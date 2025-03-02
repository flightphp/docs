# Was ist Flight?

Flight ist ein schnelles, einfaches, erweiterbares Framework für PHP. Es ist recht vielseitig und kann für den Aufbau jeder Art von Webanwendung verwendet werden. Es wurde mit Einfachheit im Hinterkopf entwickelt und ist so geschrieben, dass es leicht zu verstehen und zu verwenden ist.

Flight ist ein großartiges Anfänger-Framework für diejenigen, die neu in PHP sind und lernen möchten, wie man Webanwendungen erstellt. Es ist auch ein großartiges Framework für erfahrene Entwickler, die mehr Kontrolle über ihre Webanwendungen haben möchten. Es ist so konzipiert, dass mühelos eine RESTful API, eine einfache Webanwendung oder eine komplexe Webanwendung erstellt werden kann.

## Schnellstart

```php
<?php

// wenn mit Composer installiert
require 'vendor/autoload.php';
// oder wenn manuell mit zip-Datei installiert
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'Hallo Welt!';
});

Flight::route('/json', function() {
  Flight::json(['hallo' => 'Welt']);
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

Es gibt eine Beispiel-App, die Ihnen helfen kann, mit dem Flight Framework zu beginnen. Gehen Sie zu [flightphp/skeleton](https://github.com/flightphp/skeleton) für Anweisungen, wie Sie anfangen können! Sie können auch die Seite [examples](examples) besuchen, um Inspiration für einige der Dinge zu erhalten, die Sie mit Flight tun können.

# Gemeinschaft

Wir sind auf Matrix Chat, chatten Sie mit uns unter [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Mitwirken

Es gibt zwei Möglichkeiten, wie Sie zu Flight beitragen können: 

1. Sie können zum Kernframework beitragen, indem Sie das [Kern-Repository](https://github.com/flightphp/core) besuchen. 
1. Sie können zur Dokumentation beitragen. Diese Dokumentationswebsite wird auf [Github](https://github.com/flightphp/docs) gehostet. Wenn Sie einen Fehler bemerken oder etwas verbessern möchten, zögern Sie nicht, es zu korrigieren und einen Pull-Request einzureichen! Wir versuchen, auf dem Laufenden zu bleiben, aber Aktualisierungen und Übersetzungen sind willkommen.

# Anforderungen

Flight erfordert PHP 7.4 oder höher.

**Hinweis:** PHP 7.4 wird unterstützt, weil zum aktuellen Zeitpunkt des Schreibens (2024) PHP 7.4 die Standardversion für einige LTS-Linux-Distributionen ist. Einen Wechsel zu PHP >8 zu erzwingen, würde für diese Benutzer viele Probleme verursachen. Das Framework unterstützt auch PHP >8.

# Lizenz

Flight wird unter der [MIT](https://github.com/flightphp/core/blob/master/LICENSE) Lizenz veröffentlicht.