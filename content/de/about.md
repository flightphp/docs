# Was ist Flight?

Flight ist ein schnelles, einfaches, erweiterbares Framework für PHP. Es ist ziemlich vielseitig und kann für den Aufbau aller Arten von Webanwendungen verwendet werden. Es ist mit Einfachheit im Sinn entwickelt und auf eine Art und Weise geschrieben, die einfach zu verstehen und zu nutzen ist.

Flight ist ein großartiges Einsteiger-Framework für diejenigen, die neu in PHP sind und lernen möchten, wie man Webanwendungen erstellt. Es ist auch ein großartiges Framework für erfahrene Entwickler, die mehr Kontrolle über ihre Webanwendungen haben möchten. Es ist konzipiert, um leicht eine RESTful API, eine einfache Webanwendung oder eine komplexe Webanwendung zu erstellen.

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

Flight::route('/json', function() {
  Flight::json(['hallo' => 'Welt']);
});

Flight::start();
```

<div class="video-container">
	<iframe width="100vw" height="315" src="https://www.youtube.com/embed/VCztp1QLC2c?si=W3fSWEKmoCIlC7Z5" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
</div>

Einfach genug, oder? [Erfahren Sie mehr über Flight in der Dokumentation!](learn)

### Skelett/Grundgerüstanwendung

Es gibt eine Beispielanwendung, die Ihnen hilft, mit dem Flight Framework zu beginnen. Gehen Sie zu [flightphp/skeleton](https://github.com/flightphp/skeleton), um Anweisungen zum Einstieg zu erhalten! Sie können auch die [Beispiele](examples) Seite besuchen, um sich von einigen der Dinge inspirieren zu lassen, die Sie mit Flight machen können.

# Community

Wir sind im Matrix-Chat mit uns unter [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Beitragende

Es gibt zwei Möglichkeiten, wie Sie zu Flight beitragen können: 

1. Sie können zum Kernframework beitragen, indem Sie das [Kern-Repository](https://github.com/flightphp/core) besuchen. 
1. Sie können zur Dokumentation beitragen. Diese Dokumentationswebsite wird auf [Github](https://github.com/flightphp/docs) gehostet. Wenn Sie einen Fehler bemerken oder etwas besser ausarbeiten möchten, können Sie es gerne korrigieren und einen Pull-Request einreichen! Wir versuchen, auf dem neuesten Stand zu bleiben, aber Aktualisierungen und Sprachübersetzungen sind willkommen.

# Anforderungen

Flight erfordert PHP 7.4 oder höher.

**Hinweis:** PHP 7.4 wird unterstützt, da zum aktuellen Zeitpunkt der Erstellung (2024) PHP 7.4 die Standardversion für einige LTS-Linux-Distributionen ist. Ein Wechsel zu PHP >8 würde für diese Benutzer viele Kopfschmerzen verursachen. Das Framework unterstützt auch PHP >8.

# Lizenz

Flight wird unter der [MIT](https://github.com/flightphp/core/blob/master/LICENSE) Lizenz veröffentlicht.