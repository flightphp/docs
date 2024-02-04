# Was ist Flug?

Flug ist ein schnelles, einfaches, erweiterbares Framework für PHP.
Flug ermöglicht es Ihnen, schnell und einfach RESTful Webanwendungen zu erstellen.

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

Einfach genug, oder? [Erfahren Sie mehr über Flug!](learn)

## Skelettanwendung
Es gibt eine Beispielanwendung, die Ihnen den Einstieg in das Flug-Framework erleichtern kann. Gehen Sie zu [flightphp/skeleton](https://github.com/flightphp/skeleton), um Anweisungen zum Einstieg zu erhalten!

# Gemeinschaft

Wir sind auf Matrix! Chatten Sie mit uns unter [#flight-php-framework:matrix.org](https://matrix.to/#/#flight-php-framework:matrix.org).

# Mitarbeit

Diese Website wird auf [Github](https://github.com/flightphp/docs) gehostet. Wenn Sie einen Fehler bemerken, zögern Sie nicht, ihn zu korrigieren und einen Pull-Request einzureichen!
Wir versuchen, auf dem neuesten Stand zu bleiben, aber Aktualisierungen und Sprachübersetzungen sind willkommen.

# Anforderungen

Flug erfordert PHP 7.4 oder höher.

**Hinweis:** PHP 7.4 wird unterstützt, weil zum aktuellen Zeitpunkt (2024) PHP 7.4 die Standardversion für einige LTS-Linux-Distributionen ist. Ein Umstieg auf PHP >8 würde bei diesen Benutzern viele Kopfschmerzen verursachen. Das Framework unterstützt auch PHP >8.

# Lizenz

Flug wird unter der [MIT](https://github.com/flightphp/core/blob/master/LICENSE) Lizenz veröffentlicht.