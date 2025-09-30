# Flight PHP-Framework

Flight ist ein schnelles, simples, erweiterbares Framework für PHP – entwickelt für Entwickler, die Dinge schnell erledigen wollen, ohne Aufwand. Ob Sie eine klassische Web-App, eine blitzschnelle API oder mit den neuesten KI-gestützten Tools experimentieren, Flights geringer Fußabdruck und geradliniges Design machen es zur perfekten Wahl. Flight ist darauf ausgelegt, schlank zu sein, kann aber auch Anforderungen an eine Enterprise-Architektur erfüllen.

## Warum Flight wählen?

- **Anfängerfreundlich:** Flight ist ein toller Einstiegspunkt für neue PHP-Entwickler. Seine klare Struktur und einfache Syntax helfen Ihnen, Web-Entwicklung zu lernen, ohne in Boilerplate-Code zu versinken.
- **Geliebt von Profis:** Erfahrene Entwickler lieben Flight für seine Flexibilität und Kontrolle. Sie können von einem kleinen Prototypen zu einer vollwertigen App skalieren, ohne das Framework zu wechseln.
- **KI-Freundlich:** Flights minimale Overhead und saubere Architektur machen es ideal für die Integration von KI-Tools und APIs. Ob Sie smarte Chatbots, KI-gesteuerte Dashboards bauen oder einfach experimentieren wollen, Flight hält sich zurück, damit Sie sich auf das Wesentliche konzentrieren können. Die [skeleton app](https://github.com/flightphp/skeleton) enthält vorkonfigurierte Anweisungsdateien für die großen KI-Coding-Assistenten! [Mehr erfahren über die Nutzung von KI mit Flight](/learn/ai)

## Videoubersicht

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 fs-5 text-center mt-5 pt-5">
      <span class="flight-title-video">Einfach genug, oder?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Mehr erfahren</a> über Flight in der Dokumentation!
    </div>
  </div>
</div>

## Schnellstart

Für eine schnelle, grundlegende Installation installieren Sie es mit Composer:

```bash
composer require flightphp/core
```

Oder laden Sie ein ZIP des Repos [hier](https://github.com/flightphp/core) herunter. Dann haben Sie eine grundlegende `index.php`-Datei wie folgt:

```php
<?php

// wenn mit Composer installiert
require 'vendor/autoload.php';
// oder wenn manuell per ZIP-Datei installiert
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'hello world!';
});

Flight::route('/json', function() {
  Flight::json([
	'hello' => 'world'
  ]);
});

Flight::start();
```

Das war's! Sie haben eine grundlegende Flight-Anwendung. Führen Sie diese Datei mit `php -S localhost:8000` aus und besuchen Sie `http://localhost:8000` in Ihrem Browser, um die Ausgabe zu sehen.

## Skeleton/Boilerplate-App

Es gibt ein Beispiel-App, um Ihr Projekt mit Flight zu starten. Sie hat eine strukturierte Layout, grundlegende Konfigurationen und behandelt Composer-Skripte direkt ab dem Start! Schauen Sie sich [flightphp/skeleton](https://github.com/flightphp/skeleton) für ein fertiges Projekt an, oder besuchen Sie die [examples](examples)-Seite für Inspiration. Wollen Sie sehen, wie KI passt? [Erkunden Sie KI-gestützte Beispiele](/learn/ai).

## Installation der Skeleton-App

Sehr einfach!

```bash
# Erstellen Sie das neue Projekt
composer create-project flightphp/skeleton my-project/
# Gehen Sie in Ihr neues Projektverzeichnis
cd my-project/
# Starten Sie den lokalen Dev-Server, um sofort loszulegen!
composer start
```

Es erstellt die Projektstruktur, richtet die Dateien ein, und Sie sind bereit!

## Hohe Leistung

Flight ist eines der schnellsten PHP-Frameworks da draußen. Sein leichtes Kern bedeutet weniger Overhead und mehr Geschwindigkeit – perfekt für traditionelle Apps und moderne KI-gestützte Projekte. Sie können alle Benchmarks auf [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks) sehen.

Sehen Sie sich den Benchmark unten mit einigen anderen beliebten PHP-Frameworks an.

| Framework | Plaintext Reqs/sec | JSON Reqs/sec |
| --------- | ------------ | ------------ |
| Flight      | 190,421    | 182,491 |
| Yii         | 145,749    | 131,434 |
| Fat-Free    | 139,238    | 133,952 |
| Slim        | 89,588     | 87,348  |
| Phalcon     | 95,911     | 87,675  |
| Symfony     | 65,053     | 63,237  |
| Lumen       | 40,572     | 39,700  |
| Laravel     | 26,657     | 26,901  |
| CodeIgniter | 20,628     | 19,901  |


## Flight und KI

Neugierig, wie es mit KI umgeht? [Entdecken](/learn/ai) Sie, wie Flight die Arbeit mit Ihrem favorisierten Coding-LLM einfach macht!

# Community

Wir sind im Matrix Chat

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

Und Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Beitrag

Es gibt zwei Wege, wie Sie zu Flight beitragen können:

1. Tragen Sie zum Kern-Framework bei, indem Sie das [core repository](https://github.com/flightphp/core) besuchen.
2. Helfen Sie, die Docs zu verbessern! Diese Dokumentations-Website ist auf [Github](https://github.com/flightphp/docs) gehostet. Wenn Sie einen Fehler entdecken oder etwas verbessern wollen, reichen Sie gerne einen Pull-Request ein. Wir lieben Updates und neue Ideen – besonders rund um KI und neue Technologien!

# Anforderungen

Flight erfordert PHP 7.4 oder höher.

**Hinweis:** PHP 7.4 wird unterstützt, weil zum Zeitpunkt der Erstellung (2024) PHP 7.4 die Standardversion für einige LTS-Linux-Distributionen ist. Eine Zwangsumstellung auf PHP >8 würde für diese Benutzer Probleme verursachen. Das Framework unterstützt auch PHP >8.

# Lizenz

Flight wird unter der [MIT](https://github.com/flightphp/core/blob/master/LICENSE)-Lizenz veröffentlicht.