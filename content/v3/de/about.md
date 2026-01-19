# Flight PHP Framework

Flight ist ein schnelles, einfaches, erweiterbares Framework für PHP – gebaut für Entwickler, die Dinge schnell erledigen wollen, ohne Aufhebens. Egal, ob Sie eine klassische Web-App, eine ultraschnelle API oder mit den neuesten KI-gestützten Tools experimentieren, Flights geringer Footprint und unkompliziertes Design machen es zur perfekten Wahl. Flight ist schlank konzipiert, kann aber auch Anforderungen an Enterprise-Architekturen erfüllen.

## Warum Flight wählen?

- **Anfängerfreundlich:** Flight ist ein toller Einstieg für neue PHP-Entwickler. Seine klare Struktur und einfache Syntax helfen Ihnen, Web-Entwicklung zu lernen, ohne sich in Boilerplate-Code zu verlieren.
- **Geliebt von Profis:** Erfahrene Entwickler lieben Flight für seine Flexibilität und Kontrolle. Sie können von einem kleinen Prototyp zu einer voll ausgestatteten App skalieren, ohne das Framework zu wechseln.
- **Rückwärtskompatibel:** Wir schätzen Ihre Zeit. Flight v3 ist eine Erweiterung von v2 und behält fast das gesamte API bei. Wir glauben an Evolution, nicht an Revolution – keine weiteren „Weltuntergänge“ bei jedem Major-Release.
- **Keine Abhängigkeiten:** Der Kern von Flight ist vollständig abhängigkeitsfrei – keine Polyfills, keine externen Pakete, nicht einmal PSR-Schnittstellen. Das bedeutet weniger Angriffsvektoren, einen kleineren Footprint und keine überraschenden Breaking Changes von Upstream-Abhängigkeiten. Optionale Plugins können Abhängigkeiten enthalten, aber der Kern bleibt immer schlank und sicher.
- **KI-fokussiert:** Flights minimale Overhead und saubere Architektur machen es ideal für die Integration von KI-Tools und APIs. Egal, ob Sie smarte Chatbots, KI-gesteuerte Dashboards bauen oder einfach experimentieren wollen, Flight tritt beiseite, damit Sie sich auf das Wesentliche konzentrieren können. Die [Skeleton-App](https://github.com/flightphp/skeleton) kommt mit vorgefertigten Anweisungsdateien für die großen KI-Coding-Assistenten direkt aus der Box! [Mehr über die Nutzung von KI mit Flight](/learn/ai)

## Video-Übersicht

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 fs-5 text-center mt-5 pt-5">
      <span class="flight-title-video">Einfach genug, oder?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Erfahren Sie mehr</a> über Flight in der Dokumentation!
    </div>
  </div>
</div>

## Schneller Einstieg

Für eine schnelle, basische Installation installieren Sie es mit Composer:

```bash
composer require flightphp/core
```

Oder Sie laden ein Zip des Repos [hier](https://github.com/flightphp/core) herunter. Dann hätten Sie eine grundlegende `index.php`-Datei wie die folgende:

```php
<?php

// if installed with composer
require 'vendor/autoload.php';
// or if installed manually by zip file
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

Das war's! Sie haben eine basische Flight-Anwendung. Führen Sie diese Datei jetzt mit `php -S localhost:8000` aus und besuchen Sie `http://localhost:8000` in Ihrem Browser, um die Ausgabe zu sehen.

## Skeleton/Boilerplate-App

Es gibt eine Beispiel-App, um Ihnen den Einstieg in Ihr Projekt mit Flight zu erleichtern. Sie hat eine strukturierte Layout, grundlegende Konfigurationen sind voreingestellt und Composer-Skripte werden direkt unterstützt! Schauen Sie sich [flightphp/skeleton](https://github.com/flightphp/skeleton) für ein sofort einsatzbereites Projekt an oder besuchen Sie die [Beispiele](examples)-Seite für Inspiration. Wollen Sie sehen, wie KI passt? [Erkunden Sie KI-gestützte Beispiele](/learn/ai).

## Installation der Skeleton-App

Einfach genug!

```bash
# Create the new project
composer create-project flightphp/skeleton my-project/
# Enter your new project directory
cd my-project/
# Bring up the local dev-server to get started right away!
composer start
```

Es wird die Projektstruktur erstellen, die benötigten Dateien einrichten, und Sie sind bereit!

## Hohe Performance

Flight ist eines der schnellsten PHP-Frameworks da draußen. Sein leichtgewichtiger Kern bedeutet weniger Overhead und mehr Geschwindigkeit – perfekt für traditionelle Apps und moderne KI-gestützte Projekte. Sie können alle Benchmarks bei [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks) sehen.

Sehen Sie das Benchmark unten mit einigen anderen populären PHP-Frameworks.

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

Neugierig, wie es mit KI umgeht? [Entdecken](/learn/ai) Sie, wie Flight die Arbeit mit Ihrem Lieblings-Coding-LLM einfach macht!

## Stabilität und Rückwärtskompatibilität

Wir schätzen Ihre Zeit. Wir alle haben Frameworks gesehen, die sich alle paar Jahre komplett neu erfinden und Entwickler mit kaputtem Code und teuren Migrationen zurücklassen. Flight ist anders. Flight v3 wurde als Erweiterung von v2 konzipiert, was bedeutet, dass das API, das Sie kennen und lieben, nicht entfernt wurde. Tatsächlich werden die meisten v2-Projekte ohne Änderungen in v3 funktionieren. 

Wir sind bestrebt, Flight stabil zu halten, damit Sie sich auf den Bau Ihrer App konzentrieren können, nicht auf die Reparatur Ihres Frameworks.

# Community

Wir sind im Matrix Chat

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

Und Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Beitrag

Es gibt zwei Wege, wie Sie zu Flight beitragen können:

1. Tragen Sie zum Kern-Framework bei, indem Sie das [Core-Repository](https://github.com/flightphp/core) besuchen.
2. Helfen Sie, die Docs besser zu machen! Diese Dokumentations-Website wird auf [Github](https://github.com/flightphp/docs) gehostet. Wenn Sie einen Fehler entdecken oder etwas verbessern möchten, fühlen Sie sich frei, einen Pull Request einzureichen. Wir lieben Updates und neue Ideen – besonders rund um KI und neue Technologien!

# Anforderungen

Flight erfordert PHP 7.4 oder höher.

**Hinweis:** PHP 7.4 wird unterstützt, weil zum Zeitpunkt des Schreibens (2024) PHP 7.4 die Standardversion für einige LTS-Linux-Distributionen ist. Eine Zwangsmigration zu PHP >8 würde vielen Nutzern Kopfschmerzen bereiten. Das Framework unterstützt auch PHP >8.

# Lizenz

Flight wird unter der [MIT](https://github.com/flightphp/core/blob/master/LICENSE)-Lizenz veröffentlicht.