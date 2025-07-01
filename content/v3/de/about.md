# Was ist Flight?

Flight ist ein schnelles, einfaches, erweiterbares Framework für PHP – entwickelt für Entwickler, die Dinge schnell erledigen wollen, ohne unnötigen Aufwand. Ob Sie eine klassische Web-App, eine blitzschnelle API oder Experimente mit den neuesten KI-gestützten Tools bauen, Flights geringer Fußabdruck und geradliniger Design machen es zur perfekten Wahl.

## Warum Flight wählen?

- **Anfängerfreundlich:** Flight ist ein toller Einstiegspunkt für neue PHP-Entwickler. Seine klare Struktur und einfache Syntax helfen Ihnen, Web-Entwicklung zu lernen, ohne sich in Boilerplate-Code zu verlieren.
- **Geliebt von Profis:** Erfahrene Entwickler lieben Flight für seine Flexibilität und Kontrolle. Sie können von einem kleinen Prototypen zu einer vollständigen App skalieren, ohne das Framework zu wechseln.
- **KI-Freundlich:** Flights minimale Overhead und saubere Architektur machen es ideal für die Integration von KI-Tools und APIs. Ob Sie smarte Chatbots, KI-gesteuerte Dashboards bauen oder einfach experimentieren möchten, Flight hält sich zurück, damit Sie sich auf das Wesentliche konzentrieren können. [Learn more about using AI with Flight](/learn/ai)

## Schnellstart

Zuerst installieren Sie es mit Composer:

```bash
composer require flightphp/core
```

Oder Sie laden ein Zip des Repos [here](https://github.com/flightphp/core) herunter. Dann hätten Sie eine grundlegende `index.php`-Datei wie folgt:

```php
<?php

// wenn mit Composer installiert
require 'vendor/autoload.php';
// oder wenn manuell mit Zip-Datei installiert
// require 'flight/Flight.php';

Flight::route('/', function() {
  echo 'hello world!';
});

Flight::route('/json', function() {
  Flight::json(['hello' => 'world']);
});

Flight::start();
```

Das war's! Sie haben eine grundlegende Flight-Anwendung. Sie können diese Datei jetzt mit `php -S localhost:8000` ausführen und `http://localhost:8000` in Ihrem Browser besuchen, um die Ausgabe zu sehen.

<div class="flight-block-video">
  <div class="row">
    <div class="col-12 col-md-6 position-relative video-wrapper">
      <iframe class="video-bg" width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <div class="col-12 col-md-6 text-center mt-5 pt-5">
      <span class="fligth-title-video">Einfach genug, oder?</span>
      <br>
      <a href="https://docs.flightphp.com/learn">Erfahren Sie mehr über Flight in der Dokumentation!</a>
      <br>
      <a href="/learn/ai" class="btn btn-primary mt-3">Entdecken Sie, wie Flight KI einfach macht</a>
    </div>
  </div>
</div>

## Ist es schnell?

Absolut! Flight ist eines der schnellsten PHP-Frameworks da draußen. Sein leichtes Kern bedeutet weniger Overhead und mehr Geschwindigkeit – perfekt für traditionelle Apps und moderne KI-gestützte Projekte. Sie können alle Benchmarks auf [TechEmpower](https://www.techempower.com/benchmarks/#section=data-r18&hw=ph&test=frameworks) sehen.

Sehen Sie sich das Benchmark unten mit einigen anderen beliebten PHP-Frameworks an.

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

## Skeleton/Boilerplate App

Es gibt eine Beispiel-App, um Ihnen den Einstieg in Flight zu erleichtern. Schauen Sie sich [flightphp/skeleton](https://github.com/flightphp/skeleton) für ein fertiges Projekt an, oder besuchen Sie die [examples](examples) Seite für Inspiration. Möchten Sie sehen, wie KI passt? [Explore AI-powered examples](/learn/ai).

# Community

Wir sind im Matrix Chat

[![Matrix](https://img.shields.io/matrix/flight-php-framework%3Amatrix.org?server_fqdn=matrix.org&style=social&logo=matrix)](https://matrix.to/#/#flight-php-framework:matrix.org)

Und Discord

[![](https://dcbadge.limes.pink/api/server/https://discord.gg/Ysr4zqHfbX)](https://discord.gg/Ysr4zqHfbX)

# Beitrag leisten

Es gibt zwei Wege, wie Sie zu Flight beitragen können:

1. Tragen Sie zum Kern-Framework bei, indem Sie das [core repository](https://github.com/flightphp/core) besuchen.
2. Helfen Sie, die Dokumentation zu verbessern! Diese Dokumentations-Website ist auf [Github](https://github.com/flightphp/docs) gehostet. Wenn Sie einen Fehler entdecken oder etwas verbessern möchten, zögern Sie nicht, einen Pull-Request einzureichen. Wir lieben Updates und neue Ideen – besonders rund um KI und neue Technologien!

# Anforderungen

Flight erfordert PHP 7.4 oder höher.

**Hinweis:** PHP 7.4 wird unterstützt, weil zum Zeitpunkt der Erstellung (2024) PHP 7.4 die Standardversion für einige LTS-Linux-Distributionen ist. Eine Zwangsumstellung auf PHP >8 würde für viele Benutzer Probleme verursachen. Das Framework unterstützt auch PHP >8.

# Lizenz

Flight wird unter der [MIT](https://github.com/flightphp/core/blob/master/LICENSE) Lizenz veröffentlicht.