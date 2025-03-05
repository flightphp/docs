# Was ist Flight?

Flight ist ein schnelles, einfaches, erweiterbares Framework für PHP. Es ist ziemlich vielseitig und kann zum Erstellen jeder Art von Webanwendung verwendet werden. Es wurde mit dem Fokus auf Einfachheit entwickelt und ist so geschrieben, dass es leicht zu verstehen und zu verwenden ist.

Flight ist ein großartiges Einsteiger-Framework für diejenigen, die neu in PHP sind und lernen möchten, wie man Webanwendungen erstellt. Es ist auch ein tolles Framework für erfahrene Entwickler, die mehr Kontrolle über ihre Webanwendungen wünschen. Es wurde entwickelt, um einfach eine RESTful API, eine einfache Webanwendung oder eine komplexe Webanwendung zu erstellen.

## Schnellstart

Zuerst installieren Sie es mit Composer

```bash
composer require flightphp/core
```

oder Sie können ein Zip der Repo [hier](https://github.com/flightphp/core) herunterladen. Dann hätten Sie eine grundlegende `index.php`-Datei wie die folgende:

```php
<?php

// wenn mit Composer installiert
require 'vendor/autoload.php';
// oder wenn manuell mit Zip-Datei installiert
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'Hallo Welt!';
});

Flight::route('/json', function() {
  Flight::json(['hello' => 'world']);
});

Flight::start();
```

Das ist es! Sie haben eine grundlegende Flight-Anwendung. Sie können diese Datei jetzt mit `php -S localhost:8000` ausführen und `http://localhost:8000` in Ihrem Browser besuchen, um die Ausgabe zu sehen.

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

## Ist es schnell?

Ja! Flight ist schnell. Es ist eines der schnellsten PHP-Frameworks, die verfügbar sind. Sie können alle Benchmarks bei [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks) sehen.

Sehen Sie sich den Benchmark unten mit einigen anderen beliebten PHP-Frameworks an.

| Framework | Plaintext Reqs/sec | JSON Reqs/sec |
| --------- | ------------ | ------------ |
| Flight      | 190,421    | 182,491 |
| Yii         | 145,749    | 131,434 |
| Fat-Free    | 139,238	   | 133,952 |
| Slim        | 89,588     | 87,348  |
| Phalcon     | 95,911     | 87,675  |
| Symfony     | 65,053     | 63,237  |
| Lumen	      | 40,572     | 39,700  |
| Laravel     | 26,657     | 26,901  |
| CodeIgniter | 20,628     | 19,901  |

## Skeleton/Boilerplate App

Es gibt eine Beispielanwendung, die Ihnen helfen kann, mit dem Flight Framework zu beginnen. Gehen Sie zu [flightphp/skeleton](https://github.com/flightphp/skeleton) für Anweisungen, wie Sie starten können! Sie können auch die [Beispiele](examples)-Seite besuchen, um Inspiration für einige der Dinge zu erhalten, die Sie mit Flight tun können.

# Community

Wir sind im Matrix Chat

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

Und Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Mitwirken

Es gibt zwei Möglichkeiten, wie Sie zu Flight beitragen können:

1. Sie können zum Kern-Framework beitragen, indem Sie das [Kern-Repository](https://github.com/flightphp/core) besuchen.
1. Sie können zur Dokumentation beitragen. Diese Dokumentationswebsite wird auf [Github](https://github.com/flightphp/docs) gehostet. Wenn Sie einen Fehler bemerken oder etwas besser ausarbeiten möchten, können Sie es gerne korrigieren und einen Pull-Request einreichen! Wir versuchen, bei den Dingen auf dem Laufenden zu bleiben, aber Updates und Übersetzungen sind willkommen.

# Anforderungen

Flight erfordert PHP 7.4 oder höher.

**Hinweis:** PHP 7.4 wird unterstützt, weil zum aktuellen Zeitpunkt des Schreibens (2024) PHP 7.4 die Standardversion für einige LTS-Linux-Distributionen ist. Ein Zwangswechsel auf PHP >8 würde vielen Nutzern Kopfschmerzen bereiten. Das Framework unterstützt auch PHP >8.

# Lizenz

Flight wird unter der [MIT](https://github.com/flightphp/core/blob/master/LICENSE)-Lizenz veröffentlicht.